<?php

namespace Gzhegow\Lib\Modules\Php\Timer;


class TimerItem extends AbstractTimerItem
{
    public function cancel() : void
    {
        $this->timer->clearTimer($this);
    }
}
