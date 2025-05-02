<?php

namespace Gzhegow\Lib\Modules\Php\Promise;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;


class PromiseItem
{
    const STATE_PENDING  = 'pending';
    const STATE_POOLING  = 'pooling';
    const STATE_RESOLVED = 'resolved';
    const STATE_REJECTED = 'rejected';

    const LIST_STATE = [
        self::STATE_PENDING  => true,
        self::STATE_POOLING  => true,
        self::STATE_RESOLVED => true,
        self::STATE_REJECTED => true,
    ];


    /**
     * @var callable
     */
    protected $fnExecutor;
    /**
     * @var callable
     */
    protected $fnTicker;
    /**
     * @var callable
     */
    protected $fnSettler;

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
     * @var PromiseSettle[]
     */
    protected $settles = [];


    private function __construct()
    {
    }


    /**
     * @return static|bool|null
     */
    public static function from($from = null, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? static::fromObjectStatic($from, $cur)
            ?? static::fromCallableExecute($from, $cur)
            ?? static::fromValueResolved($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        $id = Promise::add($instance);

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromValue($from = null, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? static::fromObjectStatic($from, $cur)
            ?? static::fromValueResolved($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        $id = Promise::add($instance);

        return Result::ok($ctx, $instance);
    }


    /**
     * @return static|bool|null
     */
    public static function fromResolved($from = null, $ctx = null)
    {
        Result::parse($cur);

        $instance = static::fromValueResolved($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        $id = Promise::add($instance);

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromRejected($from = null, $ctx = null)
    {
        Result::parse($cur);

        $instance = static::fromValueRejected($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        $id = Promise::add($instance);

        return Result::ok($ctx, $instance);
    }

    /**
     * @param callable $from
     *
     * @return static|bool|null
     */
    public static function fromCallable($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = static::fromCallableExecute($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        $id = Promise::add($instance);

        return Result::ok($ctx, $instance);
    }


    /**
     * @return static
     */
    public static function never() : PromiseItem
    {
        $instance = new static();
        $instance->state = static::STATE_PENDING;

        $id = Promise::add($instance);

        return $instance;
    }

    /**
     * @return static
     */
    public static function defer(\Closure &$fnResolve = null, \Closure &$fnReject = null) : PromiseItem
    {
        $fnResolve = null;
        $fnReject = null;

        $instance = new static();
        $instance->state = static::STATE_POOLING;

        $fnResolve = static::fnResolve($instance);
        $fnReject = static::fnReject($instance);

        $id = Promise::add($instance);

        return $instance;
    }

    /**
     * @param callable $fnTicker
     *
     * @return static
     */
    public static function pooling($fnTicker) : PromiseItem
    {
        $instance = static::fromCallableTick($fnTicker);

        $id = Promise::add($instance);

        return $instance;
    }

    public static function delay(float $ms) : PromiseItem
    {
        $msTarget = microtime(true) + ($ms / 1000);

        $fnTicker = static function ($fnOk) use ($msTarget) {
            if (microtime(true) >= $msTarget) {
                $fnOk();
            }
        };

        $instance = static::fromCallableTick($fnTicker);

        $id = Promise::add($instance);

        return $instance;
    }


    /**
     * @return static|bool|null
     */
    protected static function fromObjectStatic($from, $ctx = null)
    {
        if ($from instanceof static) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    protected static function fromValueResolved($from = null, $ctx = null)
    {
        $instance = new static();
        $instance->state = static::STATE_RESOLVED;
        $instance->resolvedValue = $from;

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    protected static function fromValueRejected($from = null, $ctx = null)
    {
        $instance = new static();
        $instance->state = static::STATE_REJECTED;
        $instance->rejectedReason = $from;

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    protected static function fromCallableExecute($from, $ctx = null)
    {
        if (! is_callable($from)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be callable', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();
        $instance->fnExecutor = static::fnExecutor($instance, $from);

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    protected static function fromCallableTick($from, $ctx = null)
    {
        if (! is_callable($from)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be callable', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();
        $instance->fnTicker = static::fnTicker($instance, $from);

        return Result::ok($ctx, $instance);
    }


    public function hasFn(\Closure &$fn = null) : bool
    {
        $fn = null;

        $status =
            $this->hasFnSettler($fn)
            || $this->hasFnTicker($fn)
            || $this->hasFnExecutor($fn);

        return $status;
    }

    public function getFn() : \Closure
    {
        $this->hasFn($fn);

        return $fn;
    }


    protected function hasFnExecutor(\Closure &$fn = null) : bool
    {
        $fn = null;

        if (null !== $this->fnExecutor) {
            $fn = $this->fnExecutor;

            return true;
        }

        return false;
    }

    protected function hasFnTicker(\Closure &$fn = null) : bool
    {
        $fn = null;

        if (null !== $this->fnTicker) {
            $fn = $this->fnTicker;

            return true;
        }

        return false;
    }

    protected function hasFnSettler(\Closure &$fn = null) : bool
    {
        $fn = null;

        if (null !== $this->fnSettler) {
            $fn = $this->fnSettler;

            return true;
        }

        return false;
    }


    public function getState() : string
    {
        return $this->state;
    }


    public function isAwaiting() : bool
    {
        return false
            || (static::STATE_PENDING === $this->state)
            || (static::STATE_POOLING === $this->state);
    }

    public function isSettled() : bool
    {
        return true
            && (static::STATE_PENDING !== $this->state)
            && (static::STATE_POOLING !== $this->state);
    }


    public function isPending() : bool
    {
        return static::STATE_PENDING === $this->state;
    }

    public function isPooling() : bool
    {
        return static::STATE_POOLING === $this->state;
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
     *
     * @return static
     */
    public function then($fnOnResolved = null, $fnOnRejected = null) : PromiseItem
    {
        if (null !== $fnOnResolved) {
            if (! is_callable($fnOnResolved)) {
                throw new LogicException(
                    [ 'The `fnOnResolved` should be callable', $fnOnResolved ]
                );
            }
        }

        if (null !== $fnOnRejected) {
            if (! is_callable($fnOnRejected)) {
                throw new LogicException(
                    [ 'The `fnOnRejected` should be callable', $fnOnRejected ]
                );
            }
        }

        $promise = new static();

        $settle = new PromiseSettle();
        $settle->type = PromiseSettle::TYPE_THEN;

        $settle->fnOnResolved = $fnOnResolved;
        $settle->fnOnRejected = $fnOnRejected;

        $settle->promiseParent = $this;
        $settle->promise = $promise;

        $this->settles[] = $settle;

        return $promise;
    }

    /**
     * @param callable|null $fnOnRejected
     *
     * @return static
     */
    public function catch($fnOnRejected = null) : PromiseItem
    {
        if (null !== $fnOnRejected) {
            if (! is_callable($fnOnRejected)) {
                throw new LogicException(
                    [ 'The `fnOnRejected` should be callable', $fnOnRejected ]
                );
            }
        }

        $promise = new static();

        $settle = new PromiseSettle();
        $settle->type = PromiseSettle::TYPE_CATCH;

        $settle->fnOnRejected = $fnOnRejected;

        $settle->promiseParent = $this;
        $settle->promise = $promise;

        $this->settles[] = $settle;

        return $promise;
    }

    /**
     * @param callable|null $fnOnFinally
     *
     * @return static
     */
    public function finally($fnOnFinally = null) : PromiseItem
    {
        if (null !== $fnOnFinally) {
            if (! is_callable($fnOnFinally)) {
                throw new LogicException(
                    [ 'The `fnOnFinally` should be callable', $fnOnFinally ]
                );
            }
        }

        $promise = new static();

        $settle = new PromiseSettle();
        $settle->type = PromiseSettle::TYPE_FINALLY;

        $settle->fnOnFinally = $fnOnFinally;

        $settle->promiseParent = $this;
        $settle->promise = $promise;

        $this->settles[] = $settle;

        return $promise;
    }


    public function onSettled() : void
    {
        if (! $this->isSettled()) {
            throw new RuntimeException(
                [ 'The promise must be `settled` to call `onSettled`', $this ]
            );
        }

        $this->fnExecutor = null;
        $this->fnSettler = null;

        if ([] !== $this->settles) {
            $promiseSettles = $this->settles;

            $this->settles = [];

            foreach ( $promiseSettles as $i => $promiseSettle ) {
                $promise = $promiseSettle->promise;

                if (PromiseSettle::TYPE_THEN === $promiseSettle->type) {
                    if ($this->isRejected($reason)) {
                        $fnOnRejected = $promiseSettle->fnOnRejected;

                        if ($fnOnRejected) {
                            $promise->fnSettler = static::fnSettlerCatch($promiseSettle, $reason);

                        } else {
                            static::reject($promise, $reason);
                        }

                    } elseif ($this->isResolved($value)) {
                        $fnOnResolved = $promiseSettle->fnOnResolved;

                        if ($fnOnResolved) {
                            $promise->fnSettler = static::fnSettlerThen($promiseSettle, $value);

                        } else {
                            static::resolve($promise, $value);
                        }
                    }

                } elseif (PromiseSettle::TYPE_CATCH === $promiseSettle->type) {
                    if ($this->isRejected($reason)) {
                        $fnOnRejected = $promiseSettle->fnOnRejected;

                        if ($fnOnRejected) {
                            $promise->fnSettler = static::fnSettlerCatch($promiseSettle, $reason);

                        } else {
                            static::reject($promise, $reason);
                        }

                    } elseif ($this->isResolved($value)) {
                        static::resolve($promise, $value);
                    }

                } elseif (PromiseSettle::TYPE_FINALLY === $promiseSettle->type) {
                    $fnOnFinally = $promiseSettle->fnOnFinally;

                    $isResolved = $this->isResolved($value);
                    $isRejected = $this->isRejected($reason);

                    if ($isResolved || $isRejected) {
                        if ($fnOnFinally) {
                            $promise->fnSettler = static::fnSettlerFinally($promiseSettle, $value);

                        } else {
                            if ($isResolved) {
                                static::resolve($promise, $value);

                            } elseif ($isRejected) {
                                static::reject($promise, $reason);
                            }
                        }
                    }
                }

                Promise::add($promise);
            }
        }
    }


    /**
     * @param PromiseItem $promise
     */
    protected static function resolve($promise, $value) : void
    {
        if ($promise->isSettled()) {
            throw new RuntimeException(
                [ 'Promise is already settled', $promise ]
            );
        }

        $promise->state = static::STATE_RESOLVED;
        $promise->resolvedValue = $value;
    }

    /**
     * @param PromiseItem $promise
     */
    protected static function reject($promise, $reason) : void
    {
        if ($promise->isSettled()) {
            throw new RuntimeException(
                [ 'Promise is already settled', $promise ]
            );
        }

        $promise->state = static::STATE_REJECTED;
        $promise->rejectedReason = $reason;
    }


    /**
     * @param PromiseItem $promise
     *
     * @return \Closure
     */
    protected static function fnResolve($promise)
    {
        return static function ($value = null) use ($promise) {
            static::resolve($promise, $value);
        };
    }

    /**
     * @param PromiseItem $promise
     *
     * @return \Closure
     */
    protected static function fnReject($promise)
    {
        return static function ($reason = null) use ($promise) {
            static::reject($promise, $reason);
        };
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
            try {
                $fnResolve = static::fnResolve($promise);
                $fnReject = static::fnReject($promise);

                call_user_func($fn, $fnResolve, $fnReject);
            }
            catch ( \Throwable $throwable ) {
                static::reject($promise, $throwable);
            }
        };
    }

    /**
     * @param PromiseItem $promise
     * @param \Closure    $fn
     *
     * @return \Closure
     */
    protected static function fnTicker($promise, $fn)
    {
        return static function () use ($promise, $fn) {
            $promise->state = static::STATE_POOLING;

            try {
                $fnResolve = static::fnResolve($promise);
                $fnReject = static::fnReject($promise);

                call_user_func($fn, $fnResolve, $fnReject);
            }
            catch ( \Throwable $throwable ) {
                static::reject($promise, $throwable);
            }
        };
    }


    /**
     * @param PromiseSettle $promiseSettle
     *
     * @return \Closure
     */
    protected static function fnSettlerThen($promiseSettle, $value)
    {
        return static function () use ($promiseSettle, $value) {
            $promise = $promiseSettle->promise;

            $result = null;
            $throwable = null;

            if (null !== $promiseSettle->fnOnResolved) {
                try {
                    $result = call_user_func($promiseSettle->fnOnResolved, $value);
                }
                catch ( \Throwable $throwable ) {
                }
            }

            if (null !== $throwable) {
                if (null !== $promiseSettle->fnOnRejected) {
                    try {
                        $result = call_user_func($promiseSettle->fnOnRejected, $throwable);
                    }
                    catch ( \Throwable $throwable ) {
                    }
                }
            }

            if (null !== $throwable) {
                static::reject($promise, $throwable);

                return;
            }

            if ($result instanceof \Throwable) {
                static::reject($promise, $result);

            } elseif ($result instanceof static) {
                if ($promise === $result) {
                    throw new RuntimeException(
                        [ 'The `result` of `fnOnResolved` should not be promise itself', $result, $promise ]
                    );
                }

                $fnResolve = static::fnResolve($promise);
                $fnReject = static::fnReject($promise);

                $result
                    ->then($fnResolve)
                    ->catch($fnReject)
                ;

            } else {
                static::resolve($promise, $result);
            }
        };
    }

    /**
     * @param PromiseSettle $promiseSettle
     *
     * @return \Closure
     */
    protected static function fnSettlerCatch($promiseSettle, $reason)
    {
        return static function () use ($promiseSettle, $reason) {
            $promise = $promiseSettle->promise;

            $result = null;

            try {
                $result = call_user_func($promiseSettle->fnOnRejected, $reason);
            }
            catch ( \Throwable $throwable ) {
                static::reject($promise, $result);

                return;
            }

            if (null === $result) {
                static::reject($promise, $reason);

                return;
            }

            if ($result instanceof \Throwable) {
                static::reject($promise, $result);

            } elseif ($result instanceof static) {
                if ($promise === $result) {
                    throw new RuntimeException(
                        [ 'The `result` of `fnOnResolved` should not be promise itself', $result, $promise ]
                    );
                }

                $fnResolve = static::fnResolve($promise);
                $fnReject = static::fnReject($promise);

                $result
                    ->then($fnResolve)
                    ->catch($fnReject)
                ;

            } else {
                static::resolve($promise, $result);
            }
        };
    }

    /**
     * @param PromiseSettle $promiseSettle
     *
     * @return \Closure
     */
    protected static function fnSettlerFinally($promiseSettle, $value)
    {
        return static function () use ($promiseSettle, $value) {
            $promiseParent = $promiseSettle->promiseParent;
            $promise = $promiseSettle->promise;

            try {
                call_user_func($promiseSettle->fnOnFinally);
            }
            catch ( \Throwable $throwable ) {
                static::reject($promise, $throwable);
            }

            if ($promiseParent->isResolved($value)) {
                static::resolve($promise, $value);

            } elseif ($promiseParent->isRejected($reason)) {
                static::reject($promise, $reason);
            }
        };
    }
}
