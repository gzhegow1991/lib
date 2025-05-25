<?php

/**
 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
 */

namespace Gzhegow\Lib\Modules\Fs\SocketSafe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @method string|null read_packet_socket(\Socket|resource $socket)
 * @method void write_packet_socket(\Socket|resource $socket, string $payload)
 */
class SocketSafeProxy
{
    /**
     * @var SocketSafe
     */
    protected $inner;


    public function __construct(SocketSafe $inner)
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
                'socket_read'         => 'socket_read',
                'socket_write'        => 'socket_write',
                //
                'read_packet_socket'  => [ '@inner', 'read_packet_socket' ],
                'write_packet_socket' => [ '@inner', 'write_packet_socket' ],
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

        $previousCtx = $this->inner->setContext($currentCtx = new SocketSafeContext());

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
