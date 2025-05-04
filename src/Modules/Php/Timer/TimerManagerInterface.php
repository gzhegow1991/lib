<?php

namespace Gzhegow\Lib\Modules\Php\Timer;

interface TimerManagerInterface
{
    public function isTimer($value) : bool;

    public function isInterval($value) : bool;


    public function timer(float $ms, $fn) : TimerItem;

    public function interval(float $ms, $fn) : IntervalItem;


    public function startTimer(TimerItem $timer) : void;

    public function cancelTimer(TimerItem $timer) : void;


    public function startInterval(IntervalItem $interval) : void;

    public function cancelInterval(IntervalItem $interval) : void;

    public function restartInterval(IntervalItem $interval) : void;
}
