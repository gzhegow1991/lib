<?php

namespace Gzhegow\Lib\Modules\Async\Clock;


interface AsyncClockManagerInterface
{
    public function isTimeout($value) : bool;

    public function setTimeout(int $waitMs, callable $fn) : AsyncTimeout;

    public function clearTimeout(AsyncTimeout $timer) : void;


    public function isInterval($value) : bool;

    public function setInterval(int $waitMs, callable $fn) : AsyncInterval;

    public function clearInterval(AsyncInterval $interval) : void;
}
