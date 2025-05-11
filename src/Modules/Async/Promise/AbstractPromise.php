<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\PromiseException;
use Gzhegow\Lib\Modules\Async\Loop\LoopManagerInterface;


abstract class AbstractPromise
{
    const STATE_PENDING  = 'pending';
    const STATE_RESOLVED = 'resolved';
    const STATE_REJECTED = 'rejected';

    const LIST_STATE = [
        self::STATE_PENDING  => true,
        self::STATE_RESOLVED => true,
        self::STATE_REJECTED => true,
    ];


    // // > uncomment if you set Promise::$debug = true
    // /**
    //  * @var array
    //  */
    // public $debug;


    /**
     * @var PromiseManagerInterface
     */
    protected $manager;
    /**
     * @var LoopManagerInterface
     */
    protected $loopManager;

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
     * @var bool
     */
    protected $isRejectionDelegated = false;

    /**
     * @var PromiseSettler[]
     */
    protected $settlers = [];


    public function __construct(
        PromiseManagerInterface $manager,
        //
        LoopManagerInterface $loop
    )
    {
        $this->manager = $manager;

        $this->loopManager = $loop;
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
    public function then($fnOnResolved = null, $fnOnRejected = null) : ADeferred
    {
        $this->isRejectionDelegated = true;

        $promise = $this->manager->never();

        if (Promise::$debug) {
            $promise->{'debug'} = Lib::debug()->file_line();
        }

        if (AbstractPromise::STATE_RESOLVED === $this->state) {
            if (null === $fnOnResolved) {
                $promise->resolve(
                    $this->resolvedValue
                );

            } else {
                $fn = static::fnSettlerThen(
                    $fnOnResolved,
                    $promise,
                    $this->resolvedValue
                );

                $this->loopManager->addMicrotask($fn);
            }

        } elseif (AbstractPromise::STATE_REJECTED === $this->state) {
            if (null === $fnOnRejected) {
                $promise->reject(
                    $this->rejectedReason
                );

            } else {
                $fn = static::fnSettlerCatch(
                    $fnOnRejected,
                    $promise,
                    $this->rejectedReason
                );

                $this->loopManager->addMicrotask($fn);
            }

        } else {
            $settler = new PromiseSettler();
            $settler->type = PromiseSettler::TYPE_THEN;

            $settler->fnOnResolved = $fnOnResolved;
            $settler->fnOnRejected = $fnOnRejected;

            $settler->promise = $promise;
            $settler->promiseParent = $this;

            $this->settlers[] = $settler;
        }

        return $promise;
    }

    /**
     * @param callable|null $fnOnRejected
     */
    public function catch($fnOnRejected = null) : ADeferred
    {
        $this->isRejectionDelegated = true;

        $promise = $this->manager->never();

        if (Promise::$debug) {
            $promise->{'debug'} = Lib::debug()->file_line();
        }

        if (AbstractPromise::STATE_RESOLVED === $this->state) {
            $promise->resolve(
                $this->resolvedValue
            );

        } elseif (AbstractPromise::STATE_REJECTED === $this->state) {
            if (null === $fnOnRejected) {
                $promise->reject(
                    $this->rejectedReason
                );

            } else {
                $fn = static::fnSettlerCatch(
                    $fnOnRejected,
                    $promise,
                    $this->rejectedReason
                );

                $this->loopManager->addMicrotask($fn);
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
    public function finally($fnOnFinally = null) : ADeferred
    {
        $this->isRejectionDelegated = true;

        $promise = $this->manager->never();

        if (Promise::$debug) {
            $promise->{'debug'} = Lib::debug()->file_line();
        }

        if (AbstractPromise::STATE_RESOLVED === $this->state) {
            if (null === $fnOnFinally) {
                $promise->resolve(
                    $this->resolvedValue
                );

            } else {
                $fn = static::fnSettlerFinally(
                    $fnOnFinally,
                    $promise,
                    $this
                );

                $this->loopManager->addMicrotask($fn);
            }

        } elseif (AbstractPromise::STATE_REJECTED === $this->state) {
            if (null === $fnOnFinally) {
                $promise->reject(
                    $this->rejectedReason
                );

            } else {
                $fn = static::fnSettlerFinally(
                    $fnOnFinally,
                    $promise,
                    $this
                );

                $this->loopManager->addMicrotask($fn);
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
                    $promise->resolve(
                        $this->resolvedValue
                    );

                } else {
                    $fn = static::fnSettlerThen(
                        $fnOnResolved,
                        $promise,
                        $this->resolvedValue
                    );

                    $this->loopManager->addMicrotask($fn);
                }

            } elseif (PromiseSettler::TYPE_CATCH === $settler->type) {
                $promise->resolve(
                    $this->resolvedValue
                );

            } elseif (PromiseSettler::TYPE_FINALLY === $settler->type) {
                $fnOnFinally = $settler->fnOnFinally;

                if (null === $fnOnFinally) {
                    $promise->resolve(
                        $this->resolvedValue
                    );

                } else {
                    $fn = static::fnSettlerFinally(
                        $fnOnFinally,
                        $promise,
                        $this
                    );

                    $this->loopManager->addMicrotask($fn);
                }
            }
        }
    }

    /**
     * @param AbstractPromise $promise
     *
     * @return \Closure
     */
    protected static function fnResolve($promise)
    {
        return static function ($value = null) use ($promise) {
            $promise->resolve(
                $value
            );
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
                    $promise->reject(
                        $this->rejectedReason
                    );

                } else {
                    $fn = static::fnSettlerCatch(
                        $fnOnRejected,
                        $promise,
                        $this->rejectedReason
                    );

                    $this->loopManager->addMicrotask($fn);
                }

            } elseif (PromiseSettler::TYPE_CATCH === $settler->type) {
                $fnOnRejected = $settler->fnOnRejected;

                $fn = static::fnSettlerCatch(
                    $fnOnRejected,
                    $promise,
                    $this->rejectedReason
                );

                $this->loopManager->addMicrotask($fn);

            } elseif (PromiseSettler::TYPE_FINALLY === $settler->type) {
                $fnOnFinally = $settler->fnOnFinally;

                if (null === $fnOnFinally) {
                    $promise->reject(
                        $this->rejectedReason
                    );

                } else {
                    $fn = static::fnSettlerFinally(
                        $fnOnFinally,
                        $promise,
                        $this
                    );

                    $this->loopManager->addMicrotask($fn);
                }
            }
        }

        if (! $this->isRejectionDelegated) {
            $fn = static::fnThrowIfUnhandledRejection($this);

            $this->loopManager->addMicrotask($fn);
        }

        $this->settlers = [];
    }

    /**
     * @param AbstractPromise $promise
     *
     * @return \Closure
     */
    protected static function fnReject($promise)
    {
        return static function ($reason = null) use ($promise) {
            $promise->reject(
                $reason
            );
        };
    }


    /**
     * @param callable $fn
     */
    protected function executor($fn) : void
    {
        $result = null;
        $throwable = null;

        try {
            $fnResolve = static::fnResolve($this);
            $fnReject = static::fnReject($this);

            $result = call_user_func($fn, $fnResolve, $fnReject);
        }
        catch ( \Throwable $throwable ) {
        }

        if (null !== $throwable) {
            $this->reject(
                $throwable
            );

            return;
        }

        if ($result instanceof \Generator) {
            static::awaitGen($result, $this, null);
        }
    }

    /**
     * @param AbstractPromise $promise
     * @param \Closure        $fn
     *
     * @return \Closure
     */
    protected static function fnExecutor($promise, $fn)
    {
        return static function () use ($promise, $fn) {
            $promise->executor($fn);
        };
    }


    /**
     * @param callable|null   $fnOnResolved
     * @param AbstractPromise $promise
     * @param mixed           $value
     *
     * @return \Closure
     */
    protected static function fnSettlerThen($fnOnResolved, $promise, $value)
    {
        return static function () use ($fnOnResolved, $promise, $value) {
            if (null === $fnOnResolved) {
                $promise->resolve($value);

                return;
            }

            $result = null;
            $throwable = null;

            try {
                $result = call_user_func($fnOnResolved, $value);
            }
            catch ( \Throwable $throwable ) {
            }

            if (null !== $throwable) {
                $promise->reject(
                    $throwable
                );

                return;
            }

            if ($result instanceof \Generator) {
                static::awaitGen($result, $promise, $promise);

                return;
            }

            if ($result instanceof self) {
                if ($result === $promise) {
                    $promise->reject(
                        new RuntimeException(
                            [
                                'Returning parent promise as a result of the promise forces infinite recursion',
                                $result,
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

            $promise->resolve(
                $result
            );
        };
    }

    /**
     * @param callable|null   $fnOnRejected
     * @param AbstractPromise $promise
     * @param mixed           $reason
     *
     * @return \Closure
     */
    protected static function fnSettlerCatch($fnOnRejected, $promise, $reason)
    {
        return static function () use ($fnOnRejected, $promise, $reason) {
            if (null === $fnOnRejected) {
                $promise->reject($reason);

                return;
            }

            $result = null;
            $throwable = null;

            try {
                $result = call_user_func($fnOnRejected, $reason);
            }
            catch ( \Throwable $throwable ) {
            }

            if (null !== $throwable) {
                $promise->reject(
                    $throwable
                );

                return;
            }

            if ($result instanceof \Generator) {
                static::awaitGen($result, $promise, $promise);

                return;
            }

            if ($result instanceof self) {
                if ($result === $promise) {
                    $promise->reject(
                        new RuntimeException(
                            [
                                'Returning parent promise as a result of the promise forces infinite recursion',
                                $result,
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

            $promise->resolve(
                $result
            );
        };
    }

    /**
     * @param callable|null   $fnOnFinally
     * @param AbstractPromise $promise
     * @param AbstractPromise $promiseParent
     *
     * @return \Closure
     */
    protected static function fnSettlerFinally($fnOnFinally, $promise, $promiseParent)
    {
        return static function () use ($fnOnFinally, $promise, $promiseParent) {
            if (null !== $fnOnFinally) {
                $result = null;
                $throwable = null;

                try {
                    $result = call_user_func($fnOnFinally);
                }
                catch ( \Throwable $throwable ) {
                }

                if (null !== $throwable) {
                    $promise->reject(
                        $throwable
                    );

                    return;
                }

                if ($result instanceof \Generator) {
                    static::awaitGen($result, $promise, null);

                    return;
                }

                if ($result instanceof self) {
                    if ($result === $promise) {
                        $promise->reject(
                            new RuntimeException(
                                [
                                    'Returning parent promise as a result of the promise forces infinite recursion',
                                    $result,
                                ]
                            )
                        );

                        return;
                    }

                    $result->catch(
                        [ $promise, 'reject' ]
                    );

                    return;
                }
            }

            if (static::STATE_RESOLVED === $promiseParent->state) {
                $promise->resolve(
                    $promiseParent->resolvedValue
                );

            } elseif (static::STATE_REJECTED === $promiseParent->state) {
                $promise->reject(
                    $promiseParent->rejectedReason
                );
            }
        };
    }


    /**
     * @param static $promise
     *
     * @return \Closure
     */
    protected static function fnThrowIfUnhandledRejectionInSecondStep($loop, $promise)
    {
        return static function () use ($loop, $promise) {
            if (! $promise->isRejectionDelegated) {
                $fn = static::fnThrowIfUnhandledRejection($promise);

                $loop->addMicrotask($fn);
            }
        };
    }

    /**
     * @param static $promise
     *
     * @return \Closure
     */
    protected static function fnThrowIfUnhandledRejection($promise)
    {
        return static function () use ($promise) {
            if ($promise->isRejectionDelegated) {
                return;
            }

            $reason = $promise->rejectedReason;
            $reasonThrowable = ($reason instanceof \Throwable) ? $reason : null;

            throw new PromiseException(
                [ 'Unhandled rejection in Promise', $reason, $promise ], $reasonThrowable
            );
        };
    }


    /**
     * @param \Generator  $gen
     * @param static      $promiseToReject
     * @param static|null $promiseToResolve
     */
    protected static function awaitGen($gen, $promiseToReject, $promiseToResolve) : void
    {
        $hasPromiseToResolve = (null !== $promiseToResolve);

        $isValid = $gen->valid();

        $result = null;
        $throwable = null;

        if (! $isValid) {
            try {
                $result = $gen->getReturn();
            }
            catch ( \Throwable $throwable ) {
            }

            if (null !== $throwable) {
                $promiseToReject->reject(
                    $throwable
                );

                return;
            }

            if ($result instanceof \Generator) {
                $promiseToReject->reject(
                    new RuntimeException(
                        [
                            'Returning another \Generator from generator function is not supported, you should use `yield from` operator',
                            $result,
                            $gen,
                        ]
                    )
                );

                return;
            }

            if ($result instanceof self) {
                if (($result === $promiseToReject) || ($result === $promiseToResolve)) {
                    $promiseToReject->reject(
                        new RuntimeException(
                            [
                                'Returning parent promise as a result of the promise forces infinite recursion',
                                $result,
                            ]
                        )
                    );

                    return;
                }

                $fnOnResolved = $hasPromiseToResolve
                    ? [ $promiseToResolve, 'resolve' ]
                    : null;

                $fnOnRejected = [ $promiseToReject, 'reject' ];

                $result->then(
                    $fnOnResolved,
                    $fnOnRejected
                );

                return;
            }

            if ($hasPromiseToResolve) {
                $promiseToResolve->resolve(
                    $result
                );
            }

            return;
        }

        try {
            $current = $gen->current();
        }
        catch ( \Throwable $throwable ) {
        }

        if (null !== $throwable) {
            $promiseToReject->reject(
                $throwable
            );

            return;
        }

        if ($current instanceof \Generator) {
            $promiseToReject->reject(
                new RuntimeException(
                    [
                        'Yielding another \Generator from generator function is not supported, you should use `yield from operator`',
                        $current,
                        $gen,
                    ]
                )
            );

            return;
        }

        if ($current instanceof AbstractPromise) {
            $fnGenSend = static::fnGenSend($gen, $promiseToReject, $promiseToResolve);
            $fnGenThrow = static::fnGenThrow($gen, $promiseToReject, $promiseToResolve);

            $current->then(
                $fnGenSend,
                $fnGenThrow
            );

            return;
        }

        try {
            $gen->send($current);
        }
        catch ( \Throwable $throwable ) {
        }

        if (null !== $throwable) {
            $promiseToReject->reject(
                $throwable
            );

            return;
        }

        // ! recursion
        static::awaitGen($gen, $promiseToReject, $promiseToResolve);
    }

    /**
     * @param \Generator  $gen
     * @param static      $promiseToReject
     * @param static|null $promiseToResolve
     *
     * @return \Closure
     */
    protected static function fnGenSend($gen, $promiseToReject, $promiseToResolve)
    {
        return static function ($value = null) use (
            $gen,
            //
            $promiseToReject,
            $promiseToResolve
        ) {
            $throwable = null;

            try {
                $gen->send($value);
            }
            catch ( \Throwable $throwable ) {
            }

            if (null !== $throwable) {
                $promiseToReject->reject(
                    $throwable
                );

                return;
            }

            // ! recursion
            static::awaitGen($gen, $promiseToReject, $promiseToResolve);
        };
    }

    /**
     * @param \Generator  $gen
     * @param static      $promiseToReject
     * @param static|null $promiseToResolve
     *
     * @return \Closure
     */
    protected static function fnGenThrow($gen, $promiseToReject, $promiseToResolve)
    {
        return static function ($reason) use (
            $gen,
            //
            $promiseToReject
        ) {
            $throwable = null;

            try {
                $gen->throw($reason);
            }
            catch ( \Throwable $throwable ) {
            }

            if (null !== $throwable) {
                $promiseToReject->reject(
                    $throwable
                );
            }
        };
    }
}
