<?php

namespace Gzhegow\Lib\Modules\Php\Timer;

interface TimerManagerInterface
{
    public function isTimer($value) : bool;

    public function timer(float $ms, callable $fn) : TimerItem;

    public function clearTimer(TimerItem $timer) : void;


    public function isInterval($value) : bool;

    public function interval(float $ms, callable $fn) : IntervalItem;

    public function clearInterval(IntervalItem $interval) : void;
}
