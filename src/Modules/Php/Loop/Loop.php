<?php

namespace Gzhegow\Lib\Modules\Php\Loop;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Timer\TimerItem;
use Gzhegow\Lib\Modules\Php\Timer\IntervalItem;
use Gzhegow\Lib\Modules\Php\Promise\PromiseItem;


class Loop
{
    public static function isPromise($value) : bool
    {
        return static::getInstance()->isPromise($value);
    }

    public static function isTimer($value) : bool
    {
        return static::getInstance()->isTimer($value);
    }

    public static function isInterval($value) : bool
    {
        return static::getInstance()->isInterval($value);
    }


    public static function addPromise(PromiseItem $promise) : string
    {
        return static::getInstance()->addPromise($promise);
    }

    public static function addTimer(TimerItem $timer) : string
    {
        return static::getInstance()->addTimer($timer);
    }

    public static function addInterval(IntervalItem $interval) : string
    {
        return static::getInstance()->addInterval($interval);
    }


    public static function runLoop() : void
    {
        static::getInstance()->runLoop();
    }

    public static function registerLoop() : void
    {
        static::getInstance()->registerLoop();
    }


    public static function getInstance() : LoopManagerInterface
    {
        return Lib::php()->loopManager();
    }
}
