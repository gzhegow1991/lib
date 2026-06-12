<?php

namespace Gzhegow\Lib\Modules\Async\Loop;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Async\Clock\AsyncTimeout;
use Gzhegow\Lib\Modules\Async\Clock\AsyncInterval;


class AsyncLoop
{
    public static function addInterval(AsyncInterval $interval) : AsyncLoopManagerInterface
    {
        return static::getInstance()->addInterval($interval);
    }

    public static function clearInterval(AsyncInterval $interval) : AsyncLoopManagerInterface
    {
        return static::getInstance()->clearInterval($interval);
    }


    public static function addTimeout(AsyncTimeout $timer) : AsyncLoopManagerInterface
    {
        return static::getInstance()->addTimeout($timer);
    }

    public static function clearTimeout(AsyncTimeout $timer) : AsyncLoopManagerInterface
    {
        return static::getInstance()->clearTimeout($timer);
    }


    /**
     * @param callable $fnMicrotask
     */
    public static function addMicrotask($fnMicrotask) : AsyncLoopManagerInterface
    {
        return static::getInstance()->addMicrotask($fnMicrotask);
    }

    /**
     * @param callable $fnMacrotask
     */
    public static function addMacrotask($fnMacrotask) : AsyncLoopManagerInterface
    {
        return static::getInstance()->addMacrotask($fnMacrotask);
    }


    /**
     * @param callable $fn
     */
    public static function requestAnimationFrame($fn) : AsyncLoopManagerInterface
    {
        return static::getInstance()->requestAnimationFrame($fn);
    }


    public static function runLoop() : AsyncLoopManagerInterface
    {
        return static::getInstance()->runLoop();
    }


    public static function getInstance() : AsyncLoopManagerInterface
    {
        return Lib::async()->loopManager();
    }
}
