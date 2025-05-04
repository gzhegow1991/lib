<?php

namespace Gzhegow\Lib\Modules\Php\Timer;

use Gzhegow\Lib\Lib;


class Timer
{
    public static function isTimer($value) : bool
    {
        return static::getInstance()->isTimer($value);
    }

    /**
     * @param float    $ms
     * @param callable $fn
     */
    public static function timer($ms, $fn) : TimerItem
    {
        return static::getInstance()->timer($ms, $fn);
    }

    /**
     * @param TimerItem $timer
     */
    public static function clearTimer($timer) : void
    {
        static::getInstance()->clearTimer($timer);
    }



    public static function isInterval($value) : bool
    {
        return static::getInstance()->isInterval($value);
    }

    /**
     * @param float    $ms
     * @param callable $fn
     */
    public static function interval($ms, $fn) : IntervalItem
    {
        return static::getInstance()->interval($ms, $fn);
    }

    /**
     * @param IntervalItem $interval
     */
    public static function clearInterval($interval) : void
    {
        static::getInstance()->clearInterval($interval);
    }


    public static function getInstance() : TimerManagerInterface
    {
        return Lib::php()->timerManager();
    }
}
