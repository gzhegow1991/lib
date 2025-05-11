<?php

namespace Gzhegow\Lib\Modules\Async\Clock;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Async\Loop\LoopManagerInterface;


class ClockManager implements ClockManagerInterface
{
    /**
     * @var LoopManagerInterface
     */
    protected $loop;


    public function __construct(LoopManagerInterface $loop)
    {
        $this->loop = $loop;
    }


    public function isTimer($value) : bool
    {
        return $value instanceof AbstractTimer;
    }


    public function isTimeout($value) : bool
    {
        return $value instanceof Timeout;
    }

    public function setTimeout(int $waitMs, callable $fn) : Timeout
    {
        Lib::type($tt);

        $tt->int_non_negative($waitMsFloat, $waitMs);

        $timer = new Timeout();
        $timer->fnHandler = $fn;
        $timer->waitMs = $waitMsFloat;

        $timer->timeoutMicrotime = microtime(true) + ($waitMsFloat / 1000);

        $this->loop->addTimeout($timer);

        return $timer;
    }

    public function clearTimeout(Timeout $timer) : void
    {
        $this->loop->clearTimeout($timer);
    }


    public function isInterval($value) : bool
    {
        return $value instanceof Interval;
    }

    public function setInterval(int $waitMs, callable $fn) : Interval
    {
        Lib::type($tt);

        $tt->int_non_negative($waitMsFloat, $waitMs);

        $interval = new Interval();
        $interval->fnHandler = $fn;
        $interval->waitMs = $waitMsFloat;

        $interval->timeoutMicrotime = microtime(true) + ($waitMsFloat / 1000);

        $this->loop->addInterval($interval);

        return $interval;
    }

    public function clearInterval(Interval $interval) : void
    {
        $this->loop->clearInterval($interval);
    }
}
