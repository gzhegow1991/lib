<?php

namespace Gzhegow\Lib\Modules\Async\Clock;


abstract class AbstractTimer
{
    /**
     * @var array
     */
    public $debugInfo;

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
