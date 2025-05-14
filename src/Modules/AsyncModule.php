<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Async\Loop\LoopManager;
use Gzhegow\Lib\Modules\Async\Clock\ClockManager;
use Gzhegow\Lib\Modules\Async\Promise\PromiseManager;
use Gzhegow\Lib\Modules\Async\FetchApi\FetchApiInterface;
use Gzhegow\Lib\Modules\Async\Loop\LoopManagerInterface;
use Gzhegow\Lib\Modules\Async\FetchApi\FilesystemFetchApi;
use Gzhegow\Lib\Modules\Async\Clock\ClockManagerInterface;
use Gzhegow\Lib\Modules\Async\Promise\PromiseManagerInterface;


class AsyncModule
{
    /**
     * @var LoopManagerInterface
     */
    protected $loopManager;
    /**
     * @var PromiseManagerInterface
     */
    protected $promiseManager;
    /**
     * @var ClockManagerInterface
     */
    protected $clockManager;

    /**
     * @var FetchApiInterface
     */
    protected $fetchApi;


    public function newLoopManager() : LoopManagerInterface
    {
        return new LoopManager();
    }

    public function cloneLoopManager() : LoopManagerInterface
    {
        return clone $this->loopManager();
    }

    public function loopManager(?LoopManagerInterface $loopManager = null) : LoopManagerInterface
    {
        return $this->loopManager = null
            ?? $loopManager
            ?? $this->loopManager
            ?? new LoopManager();
    }


    public function newPromiseManager() : PromiseManagerInterface
    {
        return new PromiseManager($this->loopManager());
    }

    public function clonePromiseManager() : PromiseManagerInterface
    {
        return clone $this->promiseManager();
    }

    public function promiseManager(?PromiseManagerInterface $promiseFactory = null) : PromiseManagerInterface
    {
        return $this->promiseManager = null
            ?? $promiseFactory
            ?? $this->promiseManager
            ?? new PromiseManager($this->loopManager());
    }


    public function newClockManager() : ClockManagerInterface
    {
        return new ClockManager(
            $this->loopManager()
        );
    }

    public function cloneClockManager() : ClockManagerInterface
    {
        return clone $this->clockManager();
    }

    public function clockManager(?ClockManagerInterface $clockManager = null) : ClockManagerInterface
    {
        return $this->clockManager = null
            ?? $clockManager
            ?? $this->clockManager
            ?? new ClockManager(
                $this->loopManager()
            );
    }


    public function newFetchApi() : FetchApiInterface
    {
        return new FilesystemFetchApi();
    }

    public function cloneFetchApi() : FetchApiInterface
    {
        return clone $this->fetchApi();
    }

    public function fetchApi(?FetchApiInterface $fetchApi = null) : FetchApiInterface
    {
        return $this->fetchApi = null
            ?? $fetchApi
            ?? $this->fetchApi
            ?? new FilesystemFetchApi();
    }


    /**
     * @param \Socket $socket
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

            if ($chunk === false || $chunk === '') {
                return null;
            }

            $buff .= $chunk;
        }

        return $buff;
    }

    /**
     * @param \Socket $socket
     * @param string  $payload
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
     * @param resource $stream
     *
     * @return string|null
     */
    public function read_packet_resource($stream) : ?string
    {
        $header = fread($stream, 4);

        if (strlen($header) !== 4) {
            throw new RuntimeException(
                [ 'First 4 bytes should be length', $header ]
            );
        }

        $len = unpack("N", $header)[ 1 ];

        $buff = '';

        while ( strlen($buff) < $len ) {
            $chunk = fread($stream, $len - strlen($buff));

            if ($chunk === false || $chunk === '') {
                return null;
            }

            $buff .= $chunk;
        }

        return $buff;
    }

    /**
     * @param resource $socket
     * @param string   $payload
     *
     * @return void
     */
    public function write_packet_resource($socket, string $payload) : void
    {
        $len = strlen($payload);

        $header = pack("N", $len);

        fwrite($socket, $header . $payload);
    }
}
