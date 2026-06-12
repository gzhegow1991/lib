<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Async\Loop\DefaultAsyncLoopManager;
use Gzhegow\Lib\Modules\Async\Clock\DefaultAsyncClockManager;
use Gzhegow\Lib\Modules\Async\Loop\AsyncLoopManagerInterface;
use Gzhegow\Lib\Modules\Async\FetchApi\AsyncFetchApiInterface;
use Gzhegow\Lib\Modules\Async\FetchApi\FilesystemAsyncFetchApi;
use Gzhegow\Lib\Modules\Async\Clock\AsyncClockManagerInterface;
use Gzhegow\Lib\Modules\Async\Promise\DefaultAsyncPromiseManager;
use Gzhegow\Lib\Modules\Async\Promise\AsyncPromiseManagerInterface;
use Gzhegow\Lib\Modules\Async\Promise\Pooling\DefaultPromisePoolingFactory;
use Gzhegow\Lib\Modules\Async\Promise\Pooling\PromisePoolingFactoryInterface;


class AsyncModule
{
    /**
     * @var PromisePoolingFactoryInterface
     */
    protected $poolingFactory;

    /**
     * @var AsyncClockManagerInterface
     */
    protected $clockManager;
    /**
     * @var AsyncFetchApiInterface
     */
    protected $fetchApi;
    /**
     * @var AsyncLoopManagerInterface
     */
    protected $loopManager;
    /**
     * @var AsyncPromiseManagerInterface
     */
    protected $promiseManager;


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    public function newPoolingFactory() : PromisePoolingFactoryInterface
    {
        $instance = new DefaultPromisePoolingFactory();

        return $instance;
    }

    public function clonePoolingFactory() : PromisePoolingFactoryInterface
    {
        return clone $this->poolingFactory();
    }

    public function poolingFactory(?PromisePoolingFactoryInterface $poolingFactory = null) : PromisePoolingFactoryInterface
    {
        return $this->poolingFactory = null
            ?? $poolingFactory
            ?? $this->poolingFactory
            ?? $this->newPoolingFactory();
    }


    public function newClockManager() : AsyncClockManagerInterface
    {
        $theLoopManager = $this->loopManager();

        $instance = new DefaultAsyncClockManager(
            $theLoopManager
        );

        return $instance;
    }

    public function cloneClockManager() : AsyncClockManagerInterface
    {
        return clone $this->clockManager();
    }

    public function clockManager(?AsyncClockManagerInterface $clockManager = null) : AsyncClockManagerInterface
    {
        return $this->clockManager = null
            ?? $clockManager
            ?? $this->clockManager
            ?? $this->newClockManager();
    }


    public function newFetchApi() : AsyncFetchApiInterface
    {
        $instance = new FilesystemAsyncFetchApi();

        return $instance;
    }

    public function cloneFetchApi() : AsyncFetchApiInterface
    {
        return clone $this->fetchApi();
    }

    public function fetchApi(?AsyncFetchApiInterface $fetchApi = null) : AsyncFetchApiInterface
    {
        return $this->fetchApi = null
            ?? $fetchApi
            ?? $this->fetchApi
            ?? $this->newFetchApi();
    }


    public function newLoopManager() : AsyncLoopManagerInterface
    {
        $instance = new DefaultAsyncLoopManager();

        return $instance;
    }

    public function cloneLoopManager() : AsyncLoopManagerInterface
    {
        return clone $this->loopManager();
    }

    public function loopManager(?AsyncLoopManagerInterface $loopManager = null) : AsyncLoopManagerInterface
    {
        return $this->loopManager = null
            ?? $loopManager
            ?? $this->loopManager
            ?? $this->newLoopManager();
    }


    public function newPromiseManager() : AsyncPromiseManagerInterface
    {
        $thePoolingFactory = $this->poolingFactory();
        $theClockManager = $this->clockManager();
        $theLoopManager = $this->loopManager();
        $theFetchApi = $this->fetchApi();

        $instance = new DefaultAsyncPromiseManager(
            $thePoolingFactory,
            //
            $theClockManager,
            $theLoopManager,
            //
            $theFetchApi
        );

        return $instance;
    }

    public function clonePromiseManager() : AsyncPromiseManagerInterface
    {
        return clone $this->promiseManager();
    }

    public function promiseManager(?AsyncPromiseManagerInterface $promiseFactory = null) : AsyncPromiseManagerInterface
    {
        return $this->promiseManager = null
            ?? $promiseFactory
            ?? $this->promiseManager
            ?? $this->newPromiseManager();
    }
}
