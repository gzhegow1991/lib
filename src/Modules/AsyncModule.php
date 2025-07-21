<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Async\Promise\DefaultPromiseManager;
use Gzhegow\Lib\Modules\Async\Loop\DefaultLoopManager;
use Gzhegow\Lib\Modules\Async\Clock\DefaultClockManager;
use Gzhegow\Lib\Modules\Async\Loop\LoopManagerInterface;
use Gzhegow\Lib\Modules\Async\FetchApi\FetchApiInterface;
use Gzhegow\Lib\Modules\Async\FetchApi\FilesystemFetchApi;
use Gzhegow\Lib\Modules\Async\Clock\ClockManagerInterface;
use Gzhegow\Lib\Modules\Async\Promise\PromiseManagerInterface;
use Gzhegow\Lib\Modules\Async\Promise\Pooling\DefaultPromisePoolingFactory;
use Gzhegow\Lib\Modules\Async\Promise\Pooling\PromisePoolingFactoryInterface;


class AsyncModule
{
    /**
     * @var PromisePoolingFactoryInterface
     */
    protected $poolingFactory;

    /**
     * @var ClockManagerInterface
     */
    protected $clockManager;
    /**
     * @var LoopManagerInterface
     */
    protected $loopManager;
    /**
     * @var PromiseManagerInterface
     */
    protected $promiseManager;

    /**
     * @var FetchApiInterface
     */
    protected $fetchApi;


    public function newPoolingFactory() : PromisePoolingFactoryInterface
    {
        return new DefaultPromisePoolingFactory();
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


    public function newClockManager() : ClockManagerInterface
    {
        return new DefaultClockManager(
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
            ?? $this->newClockManager();
    }


    public function newLoopManager() : LoopManagerInterface
    {
        return new DefaultLoopManager();
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
            ?? $this->newLoopManager();
    }


    public function newPromiseManager() : PromiseManagerInterface
    {
        return new DefaultPromiseManager(
            $this->poolingFactory(),
            //
            $this->clockManager(),
            $this->loopManager(),
            //
            $this->fetchApi()
        );
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
            ?? $this->newPromiseManager();
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
            ?? $this->newFetchApi();
    }
}
