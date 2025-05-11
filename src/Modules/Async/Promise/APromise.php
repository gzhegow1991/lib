<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

use Gzhegow\Lib\Modules\Async\Loop\LoopManagerInterface;


class APromise extends AbstractPromise
{
    /**
     * @return static
     */
    public static function newPromise(
        PromiseManagerInterface $manager,
        //
        LoopManagerInterface $loopManager,
        //
        callable $fnExecutor
    )
    {
        $instance = new APromise($manager, $loopManager);
        $instance->state = static::STATE_PENDING;

        $fn = static::fnExecutor($instance, $fnExecutor);

        $loopManager->addMicrotask($fn);

        return $instance;
    }

    /**
     * @return static
     */
    public static function newResolved(
        PromiseManagerInterface $factory,
        //
        LoopManagerInterface $loop,
        //
        $resolvedValue = null
    )
    {
        $instance = new static($factory, $loop);
        $instance->state = static::STATE_RESOLVED;
        $instance->resolvedValue = $resolvedValue;

        return $instance;
    }

    /**
     * @return static
     */
    public static function newRejected(
        PromiseManagerInterface $factory,
        //
        LoopManagerInterface $loop,
        //
        $rejectedReason = null
    )
    {
        $instance = new static($factory, $loop);
        $instance->state = static::STATE_REJECTED;
        $instance->rejectedReason = $rejectedReason;

        $fn = static::fnThrowIfUnhandledRejectionInSecondStep($loop, $instance);

        $loop->addMacrotask($fn);

        return $instance;
    }
}
