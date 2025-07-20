<?php

namespace Gzhegow\Lib\Modules\Fs\StreamSafe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class StreamSafe
{
    /**
     * @var StreamSafeContext
     */
    protected $context;


    public function __construct()
    {
        if (! extension_loaded('fileinfo')) {
            throw new ExtensionException(
                'Missing PHP extension: fileinfo'
            );
        }
    }


    public function setContext(?StreamSafeContext $context) : ?StreamSafeContext
    {
        $last = $this->context;

        $this->context = $context;

        return $last;
    }


    /**
     * @param resource $resource
     *
     * @return string|null
     */
    public function read_packet_resource($resource) : ?string
    {
        $header = fread($resource, 4);

        if (strlen($header) !== 4) {
            throw new RuntimeException(
                [ 'First 4 bytes should be a length of the data', $header ]
            );
        }

        $len = unpack("N", $header)[ 1 ];

        $buff = '';

        while ( strlen($buff) < $len ) {
            $chunk = fread($resource, $len - strlen($buff));

            if (false
                || ($chunk === false)
                || ($chunk === '')
            ) {
                return null;
            }

            $buff .= $chunk;
        }

        return $buff;
    }

    /**
     * @param resource $resource
     * @param string   $payload
     *
     * @return void
     */
    public function write_packet_resource($resource, string $payload) : void
    {
        $len = strlen($payload);

        $header = pack("N", $len);

        fwrite($resource, $header . $payload);
        fflush($resource);
    }


    /**
     * @return mixed
     */
    public function call_safe(\Closure $fn, array $args = [])
    {
        $theFunc = Lib::$func;

        $beforeErrorReporting = error_reporting(E_ALL | E_DEPRECATED | E_USER_DEPRECATED);
        $beforeErrorHandler = set_error_handler([ $theFunc, 'safe_call_error_handler' ]);

        $previousCtx = $this->setContext($currentCtx = new StreamSafeContext());

        try {
            array_unshift($args, $currentCtx);

            $result = call_user_func_array($fn, $args);
        }
        finally {
            $currentCtx->handleOnFinally();

            $this->setContext($previousCtx);
        }

        set_error_handler($beforeErrorHandler);
        error_reporting($beforeErrorReporting);

        return $result;
    }
}
