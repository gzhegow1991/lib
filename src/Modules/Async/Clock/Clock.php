<?php

namespace Gzhegow\Lib\Modules\Async\Clock;

use Gzhegow\Lib\Lib;


class Clock
{
    /**
     * @var bool
     */
    public static $debug = false;


    public function isTimer($value) : bool
    {
        return static::getInstance()->isTimer($value);
    }


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
        $t = static::getInstance()->setTimeout($waitMs, $fn);

        if (static::$debug) {
            $t->{'debug'} = Lib::debug()->file_line();
        }

        return $t;
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
        $t = static::getInstance()->setInterval($waitMs, $fn);

        if (static::$debug) {
            $t->{'debug'} = Lib::debug()->file_line();
        }

        return $t;
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
        return Lib::async()->static_clock_manager();
    }
}
