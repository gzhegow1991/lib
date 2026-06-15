<?php

/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules\Fs\SocketSafe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class SocketSafe
{
    /**
     * @var SocketSafeContext
     */
    protected $context;


    public function __construct()
    {
        if ( ! extension_loaded('sockets') ) {
            throw new ExtensionException(
                [ 'The extension is missing: sockets' ]
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

        if ( strlen($header) !== 4 ) {
            throw new RuntimeException(
                [ 'First 4 bytes should be a length of the data', $header ]
            );
        }

        $len = unpack("N", $header)[1];

        $buff = '';

        while ( strlen($buff) < $len ) {
            $chunk = socket_read($socket, $len - strlen($buff));

            if ( false
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
    public function call_safe(\Closure $fn, array $fnArgs = [])
    {
        $fnSafe = Lib::fn($fn)->setSafe()->make();

        $currentCtx = new SocketSafeContext();
        $previousCtx = $this->setContext($currentCtx);

        try {
            array_unshift($fnArgs, $currentCtx);

            $result = call_user_func_array($fnSafe, $fnArgs);
        }
        finally {
            $currentCtx->handleOnFinally();

            $this->setContext($previousCtx);
        }

        return $result;
    }
}
