<?php

namespace Gzhegow\Lib\Modules\Php\Loop;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Timer\TimerItem;
use Gzhegow\Lib\Modules\Php\Timer\IntervalItem;


class Loop
{
    public static function addInterval(IntervalItem $interval) : void
    {
        static::getInstance()->addInterval($interval);
    }

    public static function clearInterval(IntervalItem $interval) : void
    {
        static::getInstance()->clearInterval($interval);
    }


    public static function addTimer(TimerItem $timer) : void
    {
        static::getInstance()->addTimer($timer);
    }

    public static function clearTimer(TimerItem $timer) : void
    {
        static::getInstance()->clearTimer($timer);
    }


    /**
     * @param callable $microtask
     *
     * @return void
     */
    public static function addMicrotask($microtask) : void
    {
        static::getInstance()->addMicrotask($microtask);
    }

    /**
     * @param callable $macrotask
     *
     * @return void
     */
    public static function addMacrotask($macrotask) : void
    {
        static::getInstance()->addMacrotask($macrotask);
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
