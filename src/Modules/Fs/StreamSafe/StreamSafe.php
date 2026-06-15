<?php

namespace Gzhegow\Lib\Modules\Fs\StreamSafe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class StreamSafe
{
    /**
     * @var StreamSafeContext
     */
    protected $context;


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

        if ( strlen($header) !== 4 ) {
            throw new RuntimeException(
                [ 'First 4 bytes should be a length of the data', $header ]
            );
        }

        $len = unpack("N", $header)[1];

        $buff = '';

        while ( strlen($buff) < $len ) {
            $chunk = fread($resource, $len - strlen($buff));

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
    public function call_safe(\Closure $fn, array $fnArgs = [])
    {
        $fnSafe = Lib::fn($fn)->setSafe()->make();

        $currentCtx = new StreamSafeContext();
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
