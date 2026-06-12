<?php

namespace Gzhegow\Lib\Modules\Async\Clock;

use Gzhegow\Lib\Lib;


class AsyncClock
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
    public static function setTimeout($waitMs, $fn) : AsyncTimeout
    {
        $timeout = static::getInstance()->setTimeout($waitMs, $fn);

        if ( AsyncClock::$isDebug ) {
            $timeout->debugInfo = Lib::file_line();
        }

        return $timeout;
    }

    /**
     * @param AsyncTimeout $timer
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
    public static function setInterval($waitMs, $fn) : AsyncInterval
    {
        $interval = static::getInstance()->setInterval($waitMs, $fn);

        if ( AsyncClock::$isDebug ) {
            $interval->debugInfo = Lib::file_line();
        }

        return $interval;
    }

    /**
     * @param AsyncInterval $interval
     */
    public static function clearInterval($interval) : void
    {
        static::getInstance()->clearInterval($interval);
    }


    public static function getInstance() : AsyncClockManagerInterface
    {
        return Lib::async()->clockManager();
    }
}
