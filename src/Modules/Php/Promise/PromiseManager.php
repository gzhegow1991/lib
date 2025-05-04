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
            ?? static::fromStatic($from, $cur)
            ?? static::fromExecutor($from, $cur)
            ?? static::fromResolved($from, $cur);

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
            ?? static::fromStatic($from, $cur)
            ?? static::fromResolved($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @param callable $from
     *
     * @return PromiseItem|bool|null
     */
    public function fromCallable($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? static::fromStatic($from, $cur)
            ?? static::fromExecutor($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }


    /**
     * @return PromiseItem|bool|null
     */
    public function fromStatic($from, $ctx = null)
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
     * @return PromiseItem|bool|null
     */
    public function fromResolved($from, $ctx = null)
    {
        $instance = PromiseItem::newResolved($this, $this->loop, $from);

        return Result::ok($ctx, $instance);
    }

    /**
     * @return PromiseItem|bool|null
     */
    public function fromRejected($from, $ctx = null)
    {
        $instance = PromiseItem::newRejected($this, $this->loop, $from);

        return Result::ok($ctx, $instance);
    }

    /**
     * @return PromiseItem|bool|null
     */
    public function fromExecutor($from, $ctx = null)
    {
        if (! is_callable($from)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be callable', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = PromiseItem::newPromise($this, $this->loop, $from);

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
        $promise = PromiseItem::new($this, $this->loop);

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

        $fnExecutor = function ($fnOk, $fnFail) use ($ps) {
            $psLeft = count($ps);

            $results = [];

            foreach ( $ps as $i => $v ) {
                $results[ $i ] = null;

                $fnThenChild = static function ($value) use (
                    &$psLeft,
                    &$results,
                    //
                    $fnOk, $i
                ) {
                    $results[ $i ] = $value;

                    if (--$psLeft === 0) {
                        call_user_func($fnOk, $results);
                    }

                    return $value;
                };

                $p = $this->isPromise($v) ? $v : $this->resolve($v);

                $p->then($fnThenChild, $fnFail);
            }
        };

        $promise = $this->new($fnExecutor);

        return $promise;
    }

    public function allSettled(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this
                ->resolve([])
            ;
        }

        $fnExecutor = function ($fnOk) use ($ps) {
            $psLeft = count($ps);

            $results = [];

            foreach ( $ps as $i => $v ) {
                $fnOnResolvedChild = static function ($val) use (
                    &$psLeft,
                    &$results,
                    //
                    $fnOk, $i
                ) {
                    $results[ $i ] = [
                        'status' => 'fulfilled',
                        'value'  => $val,
                    ];

                    if (--$psLeft === 0) {
                        call_user_func($fnOk, $results);
                    }
                };

                $fnOnRejectedChild = static function ($err) use (
                    &$psLeft,
                    &$results,
                    //
                    $fnOk, $i
                ) {
                    $results[ $i ] = [
                        'status' => 'rejected',
                        'reason' => $err,
                    ];

                    if (--$psLeft === 0) {
                        call_user_func($fnOk, $results);
                    }
                };

                $p = $this->isPromise($v) ? $v : $this->resolve($v);

                $p->then($fnOnResolvedChild, $fnOnRejectedChild);
            }
        };

        $promise = $this->new($fnExecutor);

        return $promise;
    }

    public function race(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this
                ->never()
            ;
        }

        $fnExecutor = function ($fnOk, $fnFail) use ($ps) {
            $isSettled = false;

            $fnOnResolvedChild = static function ($value) use (
                &$isSettled,
                //
                $fnOk
            ) {
                if (! $isSettled) {
                    $isSettled = true;

                    call_user_func($fnOk, $value);
                }
            };

            $fnOnRejectedChild = static function ($reason) use (
                &$isSettled,
                //
                $fnFail
            ) {
                if (! $isSettled) {
                    $isSettled = true;

                    call_user_func($fnFail, $reason);
                }
            };

            foreach ( $ps as $v ) {
                $p = $this->isPromise($v) ? $v : $this->resolve($v);

                $p->then($fnOnResolvedChild, $fnOnRejectedChild);
            }
        };

        $promise = $this->new($fnExecutor);

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

        $fnExecutor = function ($fnOk, $fnFail) use ($ps) {
            $psLeft = count($ps);

            $errors = [];

            $fnOnResolvedChild = static function ($value) use ($fnOk) {
                call_user_func($fnOk, $value);
            };

            foreach ( $ps as $i => $v ) {
                $fnOnRejectedChild = static function ($reason) use (
                    &$psLeft,
                    &$errors,
                    //
                    $fnFail, $i
                ) {
                    $errors[ $i ] = $reason;

                    if (--$psLeft === 0) {
                        call_user_func($fnFail, $errors);
                    }
                };

                $p = $this->isPromise($v) ? $v : $this->resolve($v);

                $p->then($fnOnResolvedChild, $fnOnRejectedChild);
            }
        };

        $promise = $this->new($fnExecutor);

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
