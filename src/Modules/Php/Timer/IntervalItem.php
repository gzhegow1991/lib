<?php

namespace Gzhegow\Lib\Modules\Php\Timer;


class IntervalItem extends AbstractTimerItem
{
    const STATE_PENDING   = 'pending';
    const STATE_POOLING   = 'pooling';
    const STATE_TIMEOUT   = 'timeout';
    const STATE_CANCELLED = 'cancelled';

    const LIST_STATE = [
        self::STATE_PENDING   => true,
        self::STATE_POOLING   => true,
        self::STATE_TIMEOUT   => true,
        self::STATE_CANCELLED => true,
    ];


    public function start() : void
    {
        $this->state = static::STATE_POOLING;
    }

    public function cancel() : void
    {
        $this->state = static::STATE_CANCELLED;
    }

    public function restart() : void
    {
        $this->state = static::STATE_POOLING;

        $this->timeoutMicrotime = microtime(true) + ($this->waitMs / 1000);
    }
}
