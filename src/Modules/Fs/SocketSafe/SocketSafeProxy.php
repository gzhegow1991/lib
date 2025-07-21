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
 *
 * @method mixed call_safe(\Closure $fn, array $args = [])
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
                //
                'call_safe'           => [ '@inner', 'call_safe' ],
            ];
        }

        if (empty($map[ $name ])) {
            throw new RuntimeException('Method is not exists: ' . $name);
        }

        $theFunc = Lib::func();

        $fn = $map[ $name ];

        if (is_array($fn)) {
            if ('@inner' === $fn[ 0 ]) {
                $fn[ 0 ] = $this->inner;
            }
        }

        $result = $theFunc->safe_call($fn, $args);

        return $result;
    }
}
