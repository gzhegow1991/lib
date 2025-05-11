<?php

namespace Gzhegow\Lib\Modules\Async\Loop;

use Gzhegow\Lib\Modules\Async\Clock\Timeout;
use Gzhegow\Lib\Modules\Async\Clock\Interval;


interface LoopManagerInterface
{
    public function addInterval(Interval $interval) : void;

    public function clearInterval(Interval $interval) : void;


    public function addTimeout(Timeout $timer) : void;

    public function clearTimeout(Timeout $timer) : void;


    /**
     * @param callable $microtask
     *
     * @return void
     */
    public function addMicrotask($microtask) : void;

    /**
     * @param callable $macrotask
     *
     * @return void
     */
    public function addMacrotask($macrotask) : void;


    public function runLoop() : void;

    public function registerLoop() : void;
}
