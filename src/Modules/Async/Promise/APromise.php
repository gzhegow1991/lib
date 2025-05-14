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
        LoopManagerInterface $loop,
        //
        callable $fnExecutor
    )
    {
        $instance = new APromise($manager, $loop);
        $instance->state = static::STATE_PENDING;

        $fn = static::fnExecutor($instance, $fnExecutor);

        $loop->addMicrotask($fn);
        $loop->registerLoop();

        return $instance;
    }


    /**
     * @return static
     */
    public static function newResolved(
        PromiseManagerInterface $manager,
        //
        LoopManagerInterface $loop,
        //
        $resolvedValue = null
    )
    {
        $instance = new static($manager, $loop);
        $instance->state = static::STATE_RESOLVED;
        $instance->resolvedValue = $resolvedValue;

        return $instance;
    }

    /**
     * @return static
     */
    public static function newRejected(
        PromiseManagerInterface $manager,
        //
        LoopManagerInterface $loop,
        //
        $rejectedReason = null
    )
    {
        $instance = new static($manager, $loop);
        $instance->state = static::STATE_REJECTED;
        $instance->rejectedReason = $rejectedReason;

        $fn = static::fnThrowIfUnhandledRejectionInSecondStep($loop, $instance);

        $loop->addMacrotask($fn);
        $loop->registerLoop();

        return $instance;
    }
}
