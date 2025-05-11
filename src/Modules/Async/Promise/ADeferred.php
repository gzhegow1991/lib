<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

use Gzhegow\Lib\Modules\Async\Loop\LoopManagerInterface;


class ADeferred extends AbstractPromise
{
    /**
     * @return static
     */
    public static function newNever(
        PromiseManagerInterface $factory,
        //
        LoopManagerInterface $loop
    )
    {
        $instance = new static($factory, $loop);
        $instance->state = static::STATE_PENDING;

        return $instance;
    }

    /**
     * @return static
     */
    public static function newDefer(
        PromiseManagerInterface $factory,
        //
        LoopManagerInterface $loop,
        //
        \Closure &$fnResolve = null, \Closure &$fnReject = null
    )
    {
        $fnResolve = null;
        $fnReject = null;

        $instance = new static($factory, $loop);

        $fnResolve = static::fnResolve($instance);
        $fnReject = static::fnReject($instance);

        return $instance;
    }
}
