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
    public static function setTimeout($ms, $fn) : TimerItem
    {
        return static::getInstance()->setTimeout($ms, $fn);
    }

    /**
     * @param TimerItem $timer
     */
    public static function clearTimeout($timer) : void
    {
        static::getInstance()->clearTimeout($timer);
    }



    public static function isInterval($value) : bool
    {
        return static::getInstance()->isInterval($value);
    }

    /**
     * @param float    $ms
     * @param callable $fn
     */
    public static function setInterval($ms, $fn) : IntervalItem
    {
        return static::getInstance()->setInterval($ms, $fn);
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
