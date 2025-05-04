<?php

namespace Gzhegow\Lib\Modules\Php\Timer;

use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Loop\LoopManagerInterface;


class TimerManager implements TimerManagerInterface
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
        return $value instanceof TimerItem;
    }

    public function timer(float $ms, callable $fn) : TimerItem
    {
        if ($ms < 0) {
            throw new RuntimeException(
                [ 'The `ms` should be greater or equal to 0', $ms ]
            );
        }

        $timer = new TimerItem($this);
        $timer->waitMilliseconds = $ms;
        $timer->fnHandler = $fn;
        $timer->timeoutMicrotime = microtime(true) + ($ms / 1000);

        $this->loop->addTimer($timer);

        return $timer;
    }

    public function clearTimer(TimerItem $timer) : void
    {
        $this->loop->clearTimer($timer);
    }


    public function isInterval($value) : bool
    {
        return $value instanceof IntervalItem;
    }

    public function interval(float $ms, callable $fn) : IntervalItem
    {
        if ($ms < 0) {
            throw new RuntimeException(
                [ 'The `ms` should be greater or equal to 0', $ms ]
            );
        }

        $interval = new IntervalItem($this);
        $interval->waitMilliseconds = $ms;
        $interval->fnHandler = $fn;
        $interval->timeoutMicrotime = microtime(true) + ($ms / 1000);

        $this->loop->addInterval($interval);

        return $interval;
    }

    public function clearInterval(IntervalItem $interval) : void
    {
        $this->loop->clearInterval($interval);
    }
}
