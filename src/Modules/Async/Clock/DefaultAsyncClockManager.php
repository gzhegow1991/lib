<?php

namespace Gzhegow\Lib\Modules\Async\Clock;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Async\Loop\AsyncLoopManagerInterface;


class DefaultAsyncClockManager implements AsyncClockManagerInterface
{
    /**
     * @var AsyncLoopManagerInterface
     */
    protected $loopManager;


    public function __construct(AsyncLoopManagerInterface $loopManager)
    {
        $this->loopManager = $loopManager;
    }


    public function isTimeout($value) : bool
    {
        return $value instanceof AsyncTimeout;
    }

    public function setTimeout(int $waitMs, callable $fn) : AsyncTimeout
    {
        $theType = Lib::type();

        $waitMsInt = $theType->int_non_negative($waitMs)->orThrow();

        $timer = new AsyncTimeout();
        $timer->fnHandler = $fn;
        $timer->waitMs = $waitMsInt;

        $timer->timeoutMicrotime = microtime(true) + ($waitMsInt / 1000);

        $this->loopManager->addTimeout($timer);

        return $timer;
    }

    public function clearTimeout(AsyncTimeout $timer) : void
    {
        $this->loopManager->clearTimeout($timer);
    }


    public function isInterval($value) : bool
    {
        return $value instanceof AsyncInterval;
    }

    public function setInterval(int $waitMs, callable $fn) : AsyncInterval
    {
        $theType = Lib::type();

        $waitMsInt = $theType->int_non_negative($waitMs)->orThrow();

        $interval = new AsyncInterval();
        $interval->fnHandler = $fn;
        $interval->waitMs = $waitMsInt;

        $interval->timeoutMicrotime = microtime(true) + ($waitMsInt / 1000);

        $this->loopManager->addInterval($interval);

        return $interval;
    }

    public function clearInterval(AsyncInterval $interval) : void
    {
        $this->loopManager->clearInterval($interval);
    }
}
