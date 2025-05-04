<?php

namespace Gzhegow\Lib\Modules\Php\Timer;


abstract class AbstractTimerItem
{
    /**
     * @var TimerManagerInterface
     */
    protected $timer;


    public function __construct(TimerManagerInterface $timer)
    {
        $this->timer = $timer;
    }


    /**
     * @var callable
     */
    public $fnHandler;
    /**
     * @var float
     */
    public $waitMilliseconds;
    /**
     * @var float
     */
    public $timeoutMicrotime;


    abstract public function cancel() : void;
}
