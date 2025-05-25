<?php

namespace Gzhegow\Lib\Modules\Fs\SocketSafe;

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
                [ 'First 4 bytes should be length', $header ]
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
}
