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


    public function static_pooling_factory(?PromisePoolingFactoryInterface $poolingFactory = null) : PromisePoolingFactoryInterface
    {
        return $this->poolingFactory = null
            ?? $poolingFactory
            ?? $this->poolingFactory
            ?? new DefaultPromisePoolingFactory();
    }


    public function static_clock_manager(?ClockManagerInterface $clockManager = null) : ClockManagerInterface
    {
        return $this->clockManager = null
            ?? $clockManager
            ?? $this->clockManager
            ?? new ClockManager(
                $this->static_loop_manager()
            );
    }

    public function static_loop_manager(?LoopManagerInterface $loopManager = null) : LoopManagerInterface
    {
        return $this->loopManager = null
            ?? $loopManager
            ?? $this->loopManager
            ?? new LoopManager();
    }

    public function static_promise_manager(?PromiseManagerInterface $promiseFactory = null) : PromiseManagerInterface
    {
        return $this->promiseManager = null
            ?? $promiseFactory
            ?? $this->promiseManager
            ?? new PromiseManager(
                $this->static_pooling_factory(),
                //
                $this->static_clock_manager(),
                $this->static_loop_manager(),
                //
                $this->static_fetch_api()
            );
    }


    public function static_fetch_api(?FetchApiInterface $fetchApi = null) : FetchApiInterface
    {
        return $this->fetchApi = null
            ?? $fetchApi
            ?? $this->fetchApi
            ?? new FilesystemFetchApi();
    }
}
