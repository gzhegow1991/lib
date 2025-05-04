<?php

namespace Gzhegow\Lib\Modules\Php\Timer;

use Gzhegow\Lib\Lib;


class Timer
{
    public static function isTimer($value) : bool
    {
        return static::getInstance()->isTimer($value);
    }

    public static function isInterval($value) : bool
    {
        return static::getInstance()->isInterval($value);
    }


    public static function timer(float $ms, $fn) : TimerItem
    {
        return static::getInstance()->timer($ms, $fn);
    }

    public static function interval(float $ms, $fn) : IntervalItem
    {
        return static::getInstance()->interval($ms, $fn);
    }


    public static function startTimer(TimerItem $timer) : void
    {
        static::getInstance()->startTimer($timer);
    }

    public static function cancelTimer(TimerItem $timer) : void
    {
        static::getInstance()->cancelTimer($timer);
    }


    public static function startInterval(IntervalItem $interval) : void
    {
        static::getInstance()->startInterval($interval);
    }

    public static function cancelInterval(IntervalItem $interval) : void
    {
        static::getInstance()->cancelInterval($interval);
    }

    public static function restartInterval(IntervalItem $interval) : void
    {
        static::getInstance()->restartInterval($interval);
    }


    public static function getInstance() : TimerManagerInterface
    {
        return Lib::php()->timerManager();
    }
}
