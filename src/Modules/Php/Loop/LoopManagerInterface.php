<?php

namespace Gzhegow\Lib\Modules\Php\Loop;

use Gzhegow\Lib\Modules\Php\Timer\TimerItem;
use Gzhegow\Lib\Modules\Php\Timer\IntervalItem;
use Gzhegow\Lib\Modules\Php\Promise\PromiseItem;


interface LoopManagerInterface
{
    public function isInterval($value) : bool;

    public function isPromise($value) : bool;

    public function isTimer($value) : bool;


    public function addInterval(IntervalItem $interval) : string;

    public function addPromise(PromiseItem $promise) : string;

    public function addTimer(TimerItem $timer) : string;


    public function runLoop() : void;

    public function registerLoop() : void;
}
