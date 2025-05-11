<?php

namespace Gzhegow\Lib\Modules\Async\Clock;

interface ClockManagerInterface
{
    public function isTimer($value) : bool;


    public function isTimeout($value) : bool;

    public function setTimeout(int $waitMs, callable $fn) : Timeout;

    public function clearTimeout(Timeout $timer) : void;


    public function isInterval($value) : bool;

    public function setInterval(int $waitMs, callable $fn) : Interval;

    public function clearInterval(Interval $interval) : void;
}
