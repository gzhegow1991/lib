<?php

namespace Gzhegow\Lib\Modules\Php\Timer;

interface TimerManagerInterface
{
    public function isTimer($value) : bool;

    public function setTimeout(float $ms, callable $fn) : TimerItem;

    public function clearTimeout(TimerItem $timer) : void;


    public function isInterval($value) : bool;

    public function setInterval(float $ms, callable $fn) : IntervalItem;

    public function clearInterval(IntervalItem $interval) : void;
}
