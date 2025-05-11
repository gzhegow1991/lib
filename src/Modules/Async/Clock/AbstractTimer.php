<?php

namespace Gzhegow\Lib\Modules\Async\Clock;


abstract class AbstractTimer
{
    // // > uncomment if you set Clock::$debug = true
    // /**
    //  * @var array
    //  */
    // public $debug;


    /**
     * @var callable
     */
    public $fnHandler;
    /**
     * @var int
     */
    public $waitMs;
    /**
     * @var float
     */
    public $timeoutMicrotime;
}
