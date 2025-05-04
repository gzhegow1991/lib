<?php

namespace Gzhegow\Lib\Modules\Php\Timer;

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

    public function isInterval($value) : bool
    {
        return $value instanceof IntervalItem;
    }


    public function timer(float $ms, $fn) : TimerItem
    {
        $timer = TimerItem::new($fn, $ms);

        $this->loop->addTimer($timer);

        return $timer;
    }

    public function interval(float $ms, $fn) : IntervalItem
    {
        $interval = IntervalItem::new($fn, $ms);

        $this->loop->addInterval($interval);

        return $interval;
    }


    public function startTimer(TimerItem $timer) : void
    {
        $timer->start();
    }

    public function cancelTimer(TimerItem $timer) : void
    {
        $timer->cancel();
    }


    public function startInterval(IntervalItem $interval) : void
    {
        $interval->start();
    }

    public function cancelInterval(IntervalItem $interval) : void
    {
        $interval->cancel();
    }

    public function restartInterval(IntervalItem $interval) : void
    {
        $interval->restart();
    }
}
