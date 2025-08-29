<?php

namespace Gzhegow\Lib\Modules\Async\Clock;

use Gzhegow\Lib\Lib;


class Clock
{
    /**
     * @var bool
     */
    public static $isDebug = false;


    public static function isTimeout($value) : bool
    {
        return static::getInstance()->isTimeout($value);
    }

    /**
     * @param int      $waitMs
     * @param callable $fn
     */
    public static function setTimeout($waitMs, $fn) : Timeout
    {
        $timeout = static::getInstance()->setTimeout($waitMs, $fn);

        if ( Clock::$isDebug ) {
            $theDebug = Lib::debug();

            $timeout->debugInfo = $theDebug->file_line();
        }

        return $timeout;
    }

    /**
     * @param Timeout $timer
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
     * @param int      $waitMs
     * @param callable $fn
     */
    public static function setInterval($waitMs, $fn) : Interval
    {
        $interval = static::getInstance()->setInterval($waitMs, $fn);

        if ( Clock::$isDebug ) {
            $theDebug = Lib::debug();

            $interval->debugInfo = $theDebug->file_line();
        }

        return $interval;
    }

    /**
     * @param Interval $interval
     */
    public static function clearInterval($interval) : void
    {
        static::getInstance()->clearInterval($interval);
    }


    public static function getInstance() : ClockManagerInterface
    {
        return Lib::async()->clockManager();
    }
}
