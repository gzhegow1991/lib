<?php

namespace Gzhegow\Lib\Modules\Php\Timer;


class IntervalItem extends AbstractTimerItem
{
    public function cancel() : void
    {
        $this->timer->clearInterval($this);
    }
}
