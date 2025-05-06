<?php

namespace Gzhegow\Lib\Modules\Php\Loop;

use Gzhegow\Lib\Modules\Php\Timer\TimerItem;
use Gzhegow\Lib\Modules\Php\Timer\IntervalItem;


interface LoopManagerInterface
{
    public function addInterval(IntervalItem $interval) : void;

    public function clearInterval(IntervalItem $interval) : void;


    public function addTimeout(TimerItem $timer) : void;

    public function clearTimeout(TimerItem $timer) : void;


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
