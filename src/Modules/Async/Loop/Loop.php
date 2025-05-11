<?php

namespace Gzhegow\Lib\Modules\Async\Loop;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Async\Clock\Timeout;
use Gzhegow\Lib\Modules\Async\Clock\Interval;


class Loop
{
    public static function addInterval(Interval $interval) : void
    {
        static::getInstance()->addInterval($interval);
    }

    public static function clearInterval(Interval $interval) : void
    {
        static::getInstance()->clearInterval($interval);
    }


    public static function addTimeout(Timeout $timer) : void
    {
        static::getInstance()->addTimeout($timer);
    }

    public static function clearTimeout(Timeout $timer) : void
    {
        static::getInstance()->clearTimeout($timer);
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
        return Lib::async()->loopManager();
    }
}
