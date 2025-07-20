<?php

namespace Gzhegow\Lib\Modules\Async\Loop;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Async\Clock\Timeout;
use Gzhegow\Lib\Modules\Async\Clock\Interval;


class Loop
{
    public static function addInterval(Interval $interval) : LoopManagerInterface
    {
        return static::getInstance()->addInterval($interval);
    }

    public static function clearInterval(Interval $interval) : LoopManagerInterface
    {
        return static::getInstance()->clearInterval($interval);
    }


    public static function addTimeout(Timeout $timer) : LoopManagerInterface
    {
        return static::getInstance()->addTimeout($timer);
    }

    public static function clearTimeout(Timeout $timer) : LoopManagerInterface
    {
        return static::getInstance()->clearTimeout($timer);
    }


    /**
     * @param callable $fnMicrotask
     */
    public static function addMicrotask($fnMicrotask) : LoopManagerInterface
    {
        return static::getInstance()->addMicrotask($fnMicrotask);
    }

    /**
     * @param callable $fnMacrotask
     */
    public static function addMacrotask($fnMacrotask) : LoopManagerInterface
    {
        return static::getInstance()->addMacrotask($fnMacrotask);
    }


    /**
     * @param callable $fn
     */
    public static function requestAnimationFrame($fn) : LoopManagerInterface
    {
        return static::getInstance()->requestAnimationFrame($fn);
    }


    public static function runLoop() : LoopManagerInterface
    {
        return static::getInstance()->runLoop();
    }

    public static function registerLoop() : LoopManagerInterface
    {
        return static::getInstance()->registerLoop();
    }


    public static function getInstance() : LoopManagerInterface
    {
        $theAsync = Lib::$async;

        return $theAsync->static_loop_manager();
    }
}
