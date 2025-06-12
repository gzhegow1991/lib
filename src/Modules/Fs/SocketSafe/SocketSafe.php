<?php

namespace Gzhegow\Lib\Modules\Fs\SocketSafe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class SocketSafe
{
    /**
     * @var SocketSafeContext
     */
    protected $context;


    public function __construct()
    {
        if (! extension_loaded('sockets')) {
            throw new RuntimeException(
                'Missing PHP extension: sockets'
            );
        }
    }


    public function setContext(?SocketSafeContext $context) : ?SocketSafeContext
    {
        $last = $this->context;

        $this->context = $context;

        return $last;
    }


    /**
     * @param \Socket|resource $socket
     *
     * @return string|null
     */
    public function read_packet_socket($socket) : ?string
    {
        $header = socket_read($socket, 4);

        if (strlen($header) !== 4) {
            throw new RuntimeException(
                [ 'First 4 bytes should be a length of the data', $header ]
            );
        }

        $len = unpack("N", $header)[ 1 ];

        $buff = '';

        while ( strlen($buff) < $len ) {
            $chunk = socket_read($socket, $len - strlen($buff));

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
     * @param \Socket|resource $socket
     * @param string           $payload
     *
     * @return void
     */
    public function write_packet_socket($socket, string $payload) : void
    {
        $len = strlen($payload);

        $header = pack("N", $len);

        socket_write($socket, $header . $payload);
    }


    /**
     * @return mixed
     */
    public function call_safe(\Closure $fn, array $args = [])
    {
        $beforeErrorReporting = error_reporting(E_ALL | E_DEPRECATED | E_USER_DEPRECATED);
        $beforeErrorHandler = set_error_handler([ Lib::func(), 'safe_call_error_handler' ]);

        $previousCtx = $this->setContext($currentCtx = new SocketSafeContext());

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
