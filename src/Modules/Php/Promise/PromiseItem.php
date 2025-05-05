<?php

namespace Gzhegow\Lib\Modules\Php\Promise;

use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Loop\LoopManagerInterface;


class PromiseItem
{
    const STATE_PENDING  = 'pending';
    const STATE_RESOLVED = 'resolved';
    const STATE_REJECTED = 'rejected';

    const LIST_STATE = [
        self::STATE_PENDING  => true,
        self::STATE_RESOLVED => true,
        self::STATE_REJECTED => true,
    ];


    /**
     * @var PromiseManagerInterface
     */
    protected $factory;

    /**
     * @var LoopManagerInterface
     */
    protected $loop;

    /**
     * @var string
     */
    protected $state = self::STATE_PENDING;
    /**
     * @var mixed
     */
    protected $resolvedValue;
    /**
     * @var mixed
     */
    protected $rejectedReason;

    /**
     * @var PromiseSettler[]
     */
    protected $settlers = [];


    public function __construct(
        PromiseManagerInterface $factory,
        //
        LoopManagerInterface $loop
    )
    {
        $this->factory = $factory;

        $this->loop = $loop;
    }


    public static function newPromise(
        PromiseManagerInterface $factory,
        //
        LoopManagerInterface $loop,
        //
        callable $fnExecutor
    )
    {
        $instance = new PromiseItem($factory, $loop);
        $instance->state = static::STATE_PENDING;

        $fn = static::fnExecutor($instance, $fnExecutor);

        $loop->addMicrotask($fn);

        return $instance;
    }

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

    public static function newRejected(
        PromiseManagerInterface $factory,
        //
        LoopManagerInterface $loop,
        //
        $rejectedReason = null
    )
    {
        $instance = new PromiseItem($factory, $loop);
        $instance->state = static::STATE_REJECTED;
        $instance->rejectedReason = $rejectedReason;

        return $instance;
    }


    public static function newNever(
        PromiseManagerInterface $factory,
        //
        LoopManagerInterface $loop
    )
    {
        $instance = new PromiseItem($factory, $loop);
        $instance->state = static::STATE_PENDING;

        return $instance;
    }

    public static function newDefer(
        PromiseManagerInterface $factory,
        //
        LoopManagerInterface $loop,
        //
        \Closure &$fnResolve = null, \Closure &$fnReject = null
    ) : PromiseItem
    {
        $fnResolve = null;
        $fnReject = null;

        $instance = new PromiseItem($factory, $loop);

        $fnResolve = static::fnResolve($instance);
        $fnReject = static::fnReject($instance);

        return $instance;
    }


    public function getState() : string
    {
        return $this->state;
    }


    public function isAwaiting() : bool
    {
        return static::STATE_PENDING === $this->state;
    }

    public function isSettled() : bool
    {
        return static::STATE_PENDING !== $this->state;
    }


    public function isPending() : bool
    {
        return static::STATE_PENDING === $this->state;
    }

    public function isResolved(&$value = null) : bool
    {
        $value = null;

        if (static::STATE_RESOLVED === $this->state) {
            $value = $this->resolvedValue;

            return true;
        }

        return false;
    }

    public function isRejected(&$reason = null) : bool
    {
        $reason = null;

        if (static::STATE_REJECTED === $this->state) {
            $reason = $this->rejectedReason;

            return true;
        }

        return false;
    }


    /**
     * @param callable|null $fnOnResolved
     * @param callable|null $fnOnRejected
     */
    public function then($fnOnResolved = null, $fnOnRejected = null) : PromiseItem
    {
        $promise = $this->factory->never();

        if (PromiseItem::STATE_RESOLVED === $this->state) {
            if (null === $fnOnResolved) {
                $promise->resolve($this->resolvedValue);

            } else {
                $fn = static::fnSettlerThen(
                    $fnOnResolved,
                    $promise,
                    $this->resolvedValue
                );

                $this->loop->addMicrotask($fn);
            }

        } elseif (PromiseItem::STATE_REJECTED === $this->state) {
            if (null === $fnOnRejected) {
                $promise->reject($this->rejectedReason);

            } else {
                $fn = static::fnSettlerCatch(
                    $fnOnRejected,
                    $promise,
                    $this->rejectedReason
                );

                $this->loop->addMicrotask($fn);
            }

        } else {
            $settler = new PromiseSettler();
            $settler->type = PromiseSettler::TYPE_THEN;

            $settler->fnOnResolved = $fnOnResolved;
            $settler->fnOnRejected = $fnOnResolved;

            $settler->promise = $promise;
            $settler->promiseParent = $this;

            $this->settlers[] = $settler;
        }

        return $promise;
    }

    /**
     * @param callable|null $fnOnRejected
     */
    public function catch($fnOnRejected = null) : PromiseItem
    {
        $promise = $this->factory->never();

        if (PromiseItem::STATE_RESOLVED === $this->state) {
            $promise->resolve($this->resolvedValue);

        } elseif (PromiseItem::STATE_REJECTED === $this->state) {
            if (null === $fnOnRejected) {
                $promise->reject($this->rejectedReason);

            } else {
                $fn = static::fnSettlerCatch(
                    $fnOnRejected,
                    $promise,
                    $this->rejectedReason
                );

                $this->loop->addMicrotask($fn);
            }

        } else {
            $settler = new PromiseSettler();
            $settler->type = PromiseSettler::TYPE_CATCH;

            $settler->fnOnRejected = $fnOnRejected;

            $settler->promiseParent = $this;
            $settler->promise = $promise;

            $this->settlers[] = $settler;
        }

        return $promise;
    }

    /**
     * @param callable|null $fnOnFinally
     */
    public function finally($fnOnFinally = null) : PromiseItem
    {
        $promise = $this->factory->never();

        if (PromiseItem::STATE_RESOLVED === $this->state) {
            if (null === $fnOnFinally) {
                $promise->resolve($this->resolvedValue);

            } else {
                $fn = static::fnSettlerFinally(
                    $fnOnFinally,
                    $promise,
                    $this
                );

                $this->loop->addMicrotask($fn);
            }

        } elseif (PromiseItem::STATE_REJECTED === $this->state) {
            if (null === $fnOnFinally) {
                $promise->reject($this->rejectedReason);

            } else {
                $fn = static::fnSettlerFinally(
                    $fnOnFinally,
                    $promise,
                    $this->rejectedReason
                );

                $this->loop->addMicrotask($fn);
            }

        } else {
            $settler = new PromiseSettler();
            $settler->type = PromiseSettler::TYPE_FINALLY;

            $settler->fnOnFinally = $fnOnFinally;

            $settler->promiseParent = $this;
            $settler->promise = $promise;

            $this->settlers[] = $settler;
        }

        return $promise;
    }


    /**
     * @param callable $fn
     */
    protected function executor($fn) : void
    {
        try {
            $fnResolve = static::fnResolve($this);
            $fnReject = static::fnReject($this);

            call_user_func($fn, $fnResolve, $fnReject);
        }
        catch ( \Throwable $throwable ) {
            $this->reject($throwable);
        }
    }

    /**
     * @param PromiseItem $promise
     * @param \Closure    $fn
     *
     * @return \Closure
     */
    protected static function fnExecutor($promise, $fn)
    {
        return static function () use ($promise, $fn) {
            $promise->executor($fn);
        };
    }


    protected function resolve($value = null) : void
    {
        if (static::STATE_PENDING !== $this->state) {
            throw new RuntimeException(
                [ 'Promise is already `settled`', $this ]
            );
        }

        $this->state = static::STATE_RESOLVED;
        $this->resolvedValue = $value;

        foreach ( $this->settlers as $settler ) {
            $promise = $settler->promise;

            if (PromiseSettler::TYPE_THEN === $settler->type) {
                $fnOnResolved = $settler->fnOnResolved;

                if (null === $fnOnResolved) {
                    $promise->resolve($this->resolvedValue);

                } else {
                    $fn = static::fnSettlerThen(
                        $fnOnResolved,
                        $promise,
                        $this->resolvedValue
                    );

                    $this->loop->addMicrotask($fn);
                }

            } elseif (PromiseSettler::TYPE_CATCH === $settler->type) {
                $promise->resolve($this->resolvedValue);

            } elseif (PromiseSettler::TYPE_FINALLY === $settler->type) {
                $fnOnFinally = $settler->fnOnFinally;

                if (null === $fnOnFinally) {
                    $promise->resolve($this->resolvedValue);

                } else {
                    $fn = static::fnSettlerFinally(
                        $fnOnFinally,
                        $promise,
                        $this
                    );

                    $this->loop->addMicrotask($fn);
                }
            }
        }
    }

    /**
     * @param PromiseItem $promise
     *
     * @return \Closure
     */
    protected static function fnResolve($promise)
    {
        return static function ($value = null) use ($promise) {
            $promise->resolve($value);
        };
    }


    protected function reject($reason = null) : void
    {
        if (static::STATE_PENDING !== $this->state) {
            throw new RuntimeException(
                [ 'Promise is already `settled`', $this ]
            );
        }

        $this->state = static::STATE_REJECTED;
        $this->rejectedReason = $reason;

        foreach ( $this->settlers as $settler ) {
            $promise = $settler->promise;

            if (PromiseSettler::TYPE_THEN === $settler->type) {
                $fnOnRejected = $settler->fnOnRejected;

                if (null === $fnOnRejected) {
                    $promise->reject($this->rejectedReason);

                } else {
                    $fn = static::fnSettlerCatch(
                        $fnOnRejected,
                        $promise,
                        $this->rejectedReason
                    );

                    $this->loop->addMicrotask($fn);
                }

            } elseif (PromiseSettler::TYPE_CATCH === $settler->type) {
                $fnOnRejected = $settler->fnOnRejected;

                $fn = static::fnSettlerCatch(
                    $fnOnRejected,
                    $promise,
                    $this->rejectedReason
                );

                $this->loop->addMicrotask($fn);

            } elseif (PromiseSettler::TYPE_FINALLY === $settler->type) {
                $fnOnFinally = $settler->fnOnFinally;

                if (null === $fnOnFinally) {
                    $promise->reject($this->rejectedReason);

                } else {
                    $fn = static::fnSettlerFinally(
                        $fnOnFinally,
                        $promise,
                        $this
                    );

                    $this->loop->addMicrotask($fn);
                }
            }
        }
    }

    /**
     * @param PromiseItem $promise
     *
     * @return \Closure
     */
    protected static function fnReject($promise)
    {
        return static function ($reason = null) use ($promise) {
            $promise->reject($reason);
        };
    }


    /**
     * @param callable    $fnOnResolved
     * @param PromiseItem $promise
     * @param mixed       $value
     *
     * @return \Closure
     */
    protected static function fnSettlerThen($fnOnResolved, $promise, $value)
    {
        return static function () use ($fnOnResolved, $promise, $value) {
            $result = null;
            $throwable = null;

            if (null !== $fnOnResolved) {
                try {
                    $result = $fnOnResolved($value);
                }
                catch ( \Throwable $e ) {
                    $throwable = $e;
                }

            } else {
                $result = $value;
            }

            if (null !== $throwable) {
                $promise->reject($throwable);

                return;
            }

            if ($result instanceof \Throwable) {
                $promise->reject($result);

                return;
            }

            if ($result instanceof static) {
                if ($result === $promise) {
                    $promise->reject(
                        new RuntimeException(
                            [
                                'The `result` of `fnOnResolved`/`fnOnRejected` should not be promise itself',
                                $result,
                                $promise,
                            ]
                        )
                    );

                    return;
                }

                $result->then(
                    [ $promise, 'resolve' ],
                    [ $promise, 'reject' ]
                );

                return;
            }

            $promise->resolve($result);
        };
    }

    /**
     * @param callable    $fnOnRejected
     * @param PromiseItem $promise
     * @param mixed       $reason
     *
     * @return \Closure
     */
    protected static function fnSettlerCatch($fnOnRejected, $promise, $reason)
    {
        return static function () use ($fnOnRejected, $promise, $reason) {
            $result = null;
            $throwable = null;

            if (null !== $fnOnRejected) {
                try {
                    $result = $fnOnRejected($reason);
                }
                catch ( \Throwable $e ) {
                    $throwable = $e;
                }

            } else {
                $result = $reason;
            }

            if (null !== $throwable) {
                $promise->reject($throwable);

                return;
            }

            if ($result instanceof \Throwable) {
                $promise->reject($result);

                return;
            }

            if ($result instanceof static) {
                if ($result === $promise) {
                    $promise->reject(
                        new RuntimeException(
                            [
                                'The `result` of `fnOnResolved`/`fnOnRejected` should not be promise itself',
                                $result,
                                $promise,
                            ]
                        )
                    );

                    return;
                }

                $result->then(
                    [ $promise, 'resolve' ],
                    [ $promise, 'reject' ]
                );

                return;
            }

            $promise->resolve($result);
        };
    }

    /**
     * @param callable    $fnOnFinally
     * @param PromiseItem $promise
     * @param PromiseItem $promiseParent
     *
     * @return \Closure
     */
    protected static function fnSettlerFinally($fnOnFinally, $promise, $promiseParent)
    {
        return static function () use ($fnOnFinally, $promise, $promiseParent) {
            try {
                call_user_func($fnOnFinally);
            }
            catch ( \Throwable $throwable ) {
                $promise->reject($throwable);

                return;
            }

            if (static::STATE_RESOLVED === $promiseParent->state) {
                $promise->resolve($promiseParent->resolvedValue);

            } elseif (static::STATE_REJECTED === $promiseParent->state) {
                $promise->reject($promiseParent->rejectedReason);
            }
        };
    }
}
