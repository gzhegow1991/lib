<?php

namespace Gzhegow\Lib\Modules\Fs\StreamSafe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @method string|false stream_get_contents(resource $resource, int|null $length, int|null $offset = null)
 *
 * @method string|false read_packet_resource(resource $resource)
 * @method string|false write_packet_resource(resource $resource, string $payload)
 */
class StreamSafeProxy
{
    /**
     * @var StreamSafe
     */
    protected $inner;


    public function __construct(StreamSafe $inner)
    {
        $this->inner = $inner;
    }


    /**
     * @return mixed
     */
    public function __call($name, $args)
    {
        /**
         * @var array<string, callable> $map
         */
        static $map;

        if (null === $map) {
            $map = [
                'stream_get_contents'   => 'stream_get_contents',
                //
                'read_packet_resource'  => [ '@inner', 'read_packet_resource' ],
                'write_packet_resource' => [ '@inner', 'write_packet_resource' ],
            ];
        }

        if (empty($map[ $name ])) {
            throw new RuntimeException('Method is not exists: ' . $name);
        }

        $fn = $map[ $name ];

        if (is_array($fn)) {
            if ('@inner' === $fn[ 0 ]) {
                $fn[ 0 ] = $this->inner;
            }
        }

        $result = Lib::func()->safe_call($fn, $args);

        return $result;
    }


    /**
     * @return mixed
     */
    public function callSafe(\Closure $fn, array $args = [])
    {
        $beforeErrorReporting = error_reporting(E_ALL | E_DEPRECATED | E_USER_DEPRECATED);
        $beforeErrorHandler = set_error_handler([ Lib::func(), 'safe_call_error_handler' ]);

        $previousCtx = $this->inner->setContext($currentCtx = new StreamSafeContext());

        try {
            array_unshift($args, $currentCtx);

            $result = call_user_func_array($fn, $args);
        }
        finally {
            $currentCtx->handleOnFinally();

            $this->inner->setContext($previousCtx);
        }

        set_error_handler($beforeErrorHandler);
        error_reporting($beforeErrorReporting);

        return $result;
    }
}
