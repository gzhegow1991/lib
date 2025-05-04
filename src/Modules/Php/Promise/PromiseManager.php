<?php

namespace Gzhegow\Lib\Modules\Php\Promise;

use Gzhegow\Lib\Lib;
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


    public function new($fnExecutor) : PromiseItem
    {
        $promise = PromiseItem::new($fnExecutor);

        $this->loop->addPromise($promise);

        return $promise;
    }


    public function resolve($value = null) : PromiseItem
    {
        $promise = PromiseItem::fromResolved($value);

        $this->loop->addPromise($promise);

        return $promise;
    }

    public function reject($reason = null) : PromiseItem
    {
        $promise = PromiseItem::fromRejected($reason);

        $this->loop->addPromise($promise);

        return $promise;
    }


    public function all(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this
                ->resolve([])
            ;
        }

        $loop = $this->loop;

        $fnExecutor = static function ($fnOk, $fnFail) use ($loop, $ps) {
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

                $p = PromiseItem::fromValue($v);

                $p->then($fnThenChild, $fnFail);

                $loop->addPromise($p);
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

        $loop = $this->loop;

        $fnExecutor = static function ($fnOk) use ($loop, $ps) {
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

                $p = PromiseItem::fromValue($v);

                $p->then($fnOnResolvedChild, $fnOnRejectedChild);

                $loop->addPromise($p);
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

        $loop = $this->loop;

        $fnExecutor = static function ($fnOk, $fnFail) use ($loop, $ps) {
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
                $p = PromiseItem::fromValue($v);

                $p->then($fnOnResolvedChild, $fnOnRejectedChild);

                $loop->addPromise($p);
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

        $loop = $this->loop;

        $fnExecutor = static function ($fnOk, $fnFail) use ($loop, $ps) {
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

                $p = PromiseItem::fromValue($v);

                $p->then($fnOnResolvedChild, $fnOnRejectedChild);

                $loop->addPromise($p);
            }
        };

        $promise = $this->new($fnExecutor);

        return $promise;
    }


    public function never() : PromiseItem
    {
        $promise = PromiseItem::never();

        $this->loop->addPromise($promise);

        return $promise;
    }

    public function defer(\Closure &$fnResolve = null, \Closure &$fnReject = null) : PromiseItem
    {
        $promise = PromiseItem::defer($fnResolve, $fnReject);

        $this->loop->addPromise($promise);

        return $promise;
    }


    public function delay(float $ms) : PromiseItem
    {
        $promise = PromiseItem::defer($fnResolve);

        $this->timer->timer($ms, $fnResolve);

        $this->loop->addPromise($promise);

        return $promise;
    }

    public function pooling(float $ms, $fnExecutor) : PromiseItem
    {
        $promise = PromiseItem::defer($fnResolve, $fnReject);

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

        $this->loop->addPromise($promise);

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

        $promiseTimeout = $this
            ->delay($ms)
            ->then($fnOnResolvedTimeout)
        ;

        $promiseRace = $this->race([ $promise, $promiseTimeout ]);

        return $promiseRace;
    }
}
