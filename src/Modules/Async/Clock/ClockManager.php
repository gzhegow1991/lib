<?php

namespace Gzhegow\Lib\Modules\Async\Clock;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
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
        if (! Lib::type()->int_non_negative($waitMsInt, $waitMs)) {
            throw new LogicException(
                [ 'The `waitMs` should be an integer non-negative', $waitMs ]
            );
        }

        $timer = new Timeout();
        $timer->fnHandler = $fn;
        $timer->waitMs = $waitMsInt;

        $timer->timeoutMicrotime = microtime(true) + ($waitMsInt / 1000);

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
        if (! Lib::type()->int_non_negative($waitMsInt, $waitMs)) {
            throw new LogicException(
                [ 'The `waitMs` should be an integer non-negative', $waitMs ]
            );
        }

        $interval = new Interval();
        $interval->fnHandler = $fn;
        $interval->waitMs = $waitMsInt;

        $interval->timeoutMicrotime = microtime(true) + ($waitMsInt / 1000);

        $this->loop->addInterval($interval);

        return $interval;
    }

    public function clearInterval(Interval $interval) : void
    {
        $this->loop->clearInterval($interval);
    }
}
