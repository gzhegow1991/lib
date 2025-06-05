<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Async\Loop\LoopManager;
use Gzhegow\Lib\Modules\Async\Clock\ClockManager;
use Gzhegow\Lib\Modules\Async\Promise\PromiseManager;
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
     * @var LoopManagerInterface
     */
    protected $loopManager;
    /**
     * @var PromisePoolingFactoryInterface
     */
    protected $poolingFactory;
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
            ?? new DefaultPromisePoolingFactory();
    }


    public function newPromiseManager() : PromiseManagerInterface
    {
        return new PromiseManager(
            $this->loopManager(),
            $this->poolingFactory()
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
            ?? new PromiseManager(
                $this->loopManager(),
                $this->poolingFactory()
            );
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
}
