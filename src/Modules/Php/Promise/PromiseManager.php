<?php

namespace Gzhegow\Lib\Modules\Php\Promise;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Loop\LoopManagerInterface;
use Gzhegow\Lib\Modules\Php\Timer\TimerManagerInterface;


class PromiseManager implements PromiseManagerInterface
{
    /**
     * @var LoopManagerInterface
     */
    protected $loop;
    /**
     * @var TimerManagerInterface
     */
    protected $timer;


    public function __construct(
        LoopManagerInterface $loop,
        TimerManagerInterface $timer
    )
    {
        $this->loop = $loop;
        $this->timer = $timer;
    }


    public function isPromise($value) : bool
    {
        return $value instanceof PromiseItem;
    }


    /**
     * @return PromiseItem|bool|null
     */
    public function from($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? $this->fromInstance($from, $cur)
            ?? $this->fromCallable($from, $cur)
            ?? $this->fromResolved($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return PromiseItem|bool|null
     */
    public function fromValue($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? $this->fromInstance($from, $cur)
            ?? $this->fromResolved($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return PromiseItem|bool|null
     */
    public function fromCallable($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? $this->fromInstance($from, $cur)
            ?? $this->fromCallable($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }


    /**
     * @return PromiseItem|bool|null
     */
    protected function fromInstance($from, $ctx = null)
    {
        if ($from instanceof PromiseItem) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` should be instance of: ' . PromiseItem::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return PromiseItem|bool|null
     */
    protected function fromResolved($from, $ctx = null)
    {
        try {
            $instance = PromiseItem::newResolved($this, $this->loop, $from);
        }
        catch ( \Throwable $e ) {
            return Result::err(
                $ctx,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return PromiseItem|bool|null
     */
    protected function fromRejected($from, $ctx = null)
    {
        try {
            $instance = PromiseItem::newRejected($this, $this->loop, $from);
        }
        catch ( \Throwable $e ) {
            return Result::err(
                $ctx,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return PromiseItem|bool|null
     */
    protected function fromExecutor($from, $ctx = null)
    {
        try {
            $instance = PromiseItem::newPromise($this, $this->loop, $from);
        }
        catch ( \Throwable $e ) {
            return Result::err(
                $ctx,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Result::ok($ctx, $instance);
    }


    public function new($fnExecutor) : PromiseItem
    {
        $promise = PromiseItem::newPromise($this, $this->loop, $fnExecutor);

        return $promise;
    }

    public function resolve($value = null) : PromiseItem
    {
        $promise = PromiseItem::newResolved($this, $this->loop, $value);

        return $promise;
    }

    public function reject($reason = null) : PromiseItem
    {
        $promise = PromiseItem::newRejected($this, $this->loop, $reason);

        return $promise;
    }


    public function never() : PromiseItem
    {
        $promise = PromiseItem::newNever($this, $this->loop);

        return $promise;
    }

    public function defer(\Closure &$fnResolve = null, \Closure &$fnReject = null) : PromiseItem
    {
        $promise = PromiseItem::newDefer(
            $this, $this->loop,
            $fnResolve, $fnReject
        );

        return $promise;
    }


    public function delay(float $ms) : PromiseItem
    {
        $promise = PromiseItem::newDefer(
            $this, $this->loop,
            $fnResolve
        );

        $this->timer->timer($ms, $fnResolve);

        return $promise;
    }

    public function pooling(float $ms, $fnExecutor) : PromiseItem
    {
        $promise = PromiseItem::newDefer(
            $this, $this->loop,
            $fnResolve, $fnReject
        );

        $interval = $this->timer->interval(
            $ms,
            static function () use ($fnExecutor, $fnResolve, $fnReject) {
                call_user_func($fnExecutor, $fnResolve, $fnReject);
            }
        );

        $promise
            ->finally(static function () use ($interval) {
                $interval->cancel();
            })
        ;

        return $promise;
    }


    public function all(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this
                ->resolve([])
            ;
        }

        $promise = $this->defer($fnResolveParent, $fnRejectParent);

        $psLeft = count($ps);

        $results = [];

        foreach ( $ps as $i => $v ) {
            $results[ $i ] = null;

            $fnOnResolvedChild = static function ($value) use (
                &$psLeft,
                &$results,
                //
                $fnResolveParent, $i
            ) {
                $results[ $i ] = $value;

                if (--$psLeft === 0) {
                    call_user_func($fnResolveParent, $results);
                }

                return $value;
            };

            $p = $this->isPromise($v) ? $v : $this->resolve($v);

            $p->then($fnOnResolvedChild, $fnRejectParent);
        }

        return $promise;
    }

    public function allSettled(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this
                ->resolve([])
            ;
        }

        $promise = $this->defer($fnResolveParent, $fnRejectParent);

        $psLeft = count($ps);

        $results = [];

        foreach ( $ps as $i => $v ) {
            $fnOnResolvedChild = static function ($val) use (
                &$psLeft,
                &$results,
                //
                $fnResolveParent, $i
            ) {
                $results[ $i ] = [
                    'status' => 'fulfilled',
                    'value'  => $val,
                ];

                if (--$psLeft === 0) {
                    call_user_func($fnResolveParent, $results);
                }
            };

            $fnOnRejectedChild = static function ($err) use (
                &$psLeft,
                &$results,
                //
                $fnResolveParent, $i
            ) {
                $results[ $i ] = [
                    'status' => 'rejected',
                    'reason' => $err,
                ];

                if (--$psLeft === 0) {
                    call_user_func($fnResolveParent, $results);
                }
            };

            $p = $this->isPromise($v) ? $v : $this->resolve($v);

            $p->then($fnOnResolvedChild, $fnOnRejectedChild);
        }

        return $promise;
    }

    public function race(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this
                ->never()
            ;
        }

        $promise = $this->defer($fnResolveParent, $fnRejectParent);

        $isSettled = false;

        $fnOnResolvedChild = static function ($value) use (
            &$isSettled,
            //
            $fnResolveParent
        ) {
            if (! $isSettled) {
                $isSettled = true;

                call_user_func($fnResolveParent, $value);
            }
        };

        $fnOnRejectedChild = static function ($reason) use (
            &$isSettled,
            //
            $fnRejectParent
        ) {
            if (! $isSettled) {
                $isSettled = true;

                call_user_func($fnRejectParent, $reason);
            }
        };

        foreach ( $ps as $v ) {
            $p = $this->isPromise($v) ? $v : $this->resolve($v);

            $p->then($fnOnResolvedChild, $fnOnRejectedChild);
        }

        return $promise;
    }

    public function any(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this
                ->reject(
                    new RuntimeException('The `ps` should be non-empty array')
                )
            ;
        }

        $promise = $this->defer($fnResolveParent, $fnRejectParent);

        $psLeft = count($ps);

        $errors = [];

        $fnOnResolvedChild = static function ($value) use ($fnResolveParent) {
            call_user_func($fnResolveParent, $value);
        };

        foreach ( $ps as $i => $v ) {
            $fnOnRejectedChild = static function ($reason) use (
                &$psLeft,
                &$errors,
                //
                $fnRejectParent, $i
            ) {
                $errors[ $i ] = $reason;

                if (--$psLeft === 0) {
                    call_user_func($fnRejectParent, $errors);
                }
            };

            $p = $this->isPromise($v) ? $v : $this->resolve($v);

            $p->then($fnOnResolvedChild, $fnOnRejectedChild);
        }

        return $promise;
    }


    public function timeout(PromiseItem $promise, float $ms, $reason = null) : PromiseItem
    {
        $fnOnResolvedTimeout = static function () use ($ms, $reason) {
            if ($reason instanceof \Throwable) {
                throw $reason;

            } elseif (is_array($reason) && ([] !== $reason)) {
                $reason[] = $ms;

                throw new RuntimeException($reason);

            } elseif (Lib::type()->string_not_empty($reasonString, $reason)) {
                throw new RuntimeException([ $reasonString, $ms ]);

            } else {
                throw new RuntimeException("Timeout: {$ms}ms");
            }
        };

        $promiseTimeout = PromiseItem::newDefer(
            $this, $this->loop,
            $fnResolve
        );

        $this->timer->timer($ms, $fnResolve);

        $promiseTimeout->then($fnOnResolvedTimeout);

        $promiseRace = $this->race([ $promise, $promiseTimeout ]);

        return $promiseRace;
    }
}
