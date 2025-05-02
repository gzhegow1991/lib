<?php

namespace Gzhegow\Lib\Modules\Php\Promise;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class PromiseManager implements PromiseManagerInterface
{
    /**
     * @var bool
     */
    protected $signalBreak = false;

    /**
     * @var int
     */
    protected $queueId = 0;
    /**
     * @var PromiseItem[]
     */
    protected $queue = [];


    public function new($fnExecute) : PromiseItem
    {
        return PromiseItem::fromCallable($fnExecute);
    }


    public function resolve($value = null) : PromiseItem
    {
        return PromiseItem::fromResolved($value);
    }

    public function reject($reason = null) : PromiseItem
    {
        return PromiseItem::fromRejected($reason);
    }


    public function all(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this->resolve([]);
        }

        $fnExecute = static function ($fnOk, $fnFail) use ($ps) {
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

                PromiseItem::fromValue($v)
                    ->then($fnThenChild, $fnFail)
                ;
            }
        };

        $promise = $this->new($fnExecute);

        return $promise;
    }

    public function allSettled(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this->resolve([]);
        }

        $fnExecute = static function ($fnOk) use ($ps) {
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

                PromiseItem::fromValue($v)
                    ->then($fnOnResolvedChild, $fnOnRejectedChild)
                ;
            }
        };

        $promise = $this->new($fnExecute);

        return $promise;
    }

    public function race(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this->never();
        }

        $fnExecute = static function ($fnOk, $fnFail) use ($ps) {
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
                PromiseItem::fromValue($v)
                    ->then($fnOnResolvedChild, $fnOnRejectedChild)
                ;
            }
        };

        $promise = $this->new($fnExecute);

        return $promise;
    }

    public function any(array $ps) : PromiseItem
    {
        if ([] === $ps) {
            return $this->reject(
                new RuntimeException('The `ps` should be non-empty array')
            );
        }

        $fnExecute = static function ($fnOk, $fnFail) use ($ps) {
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

                PromiseItem::fromValue($v)
                    ->then($fnOnResolvedChild, $fnOnRejectedChild)
                ;
            }
        };

        $promise = $this->new($fnExecute);

        return $promise;
    }


    public function never() : PromiseItem
    {
        return PromiseItem::never();
    }

    public function defer(\Closure &$fnResolve = null, \Closure &$fnReject = null) : PromiseItem
    {
        return PromiseItem::defer($fnResolve, $fnReject);
    }


    public function pooling($fnTick) : PromiseItem
    {
        return PromiseItem::pooling($fnTick);
    }

    public function delay(float $ms) : PromiseItem
    {
        return PromiseItem::delay($ms);
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

        $timeoutPromise = $this->delay($ms)
            ->then($fnOnResolvedTimeout)
        ;

        $promiseRace = $this->race([ $promise, $timeoutPromise ]);

        return $promiseRace;
    }


    public function isPromise($value) : bool
    {
        return $value instanceof PromiseItem;
    }


    protected function reset() : void
    {
        $this->queue = [];

        $this->signalBreak = false;
    }


    public function add(PromiseItem $promise) : int
    {
        $id = ++$this->queueId;

        $this->queue[ $id ] = $promise;

        return $id;
    }


    public function loop() : void
    {
        while ( true ) {
            if ($this->signalBreak) {
                $this->reset();

                break;
            }

            $this->loopStep($this->signalBreak);
        }
    }

    /**
     * @param bool|null $signalBreak
     *
     * @return static
     */
    protected function loopStep(bool &$signalBreak = null)
    {
        /**
         * @var PromiseItem[] $settledList
         */

        $signalBreak = true;

        if ([] === $this->queue) {
            return $this;
        }

        $settledList = [];

        reset($this->queue);

        while ( null !== ($id = key($this->queue)) ) {
            $promise = $this->queue[ $id ];

            if ($this->loopStepTickSettled($promise)) {
                $settledList[ $id ] = $promise;

                if ($signalBreak) {
                    $signalBreak = false;
                }

            } elseif ($this->loopStepTickAwaiting($promise)) {
                if ($signalBreak) {
                    $signalBreak = false;
                }
            }

            next($this->queue);

            usleep(1000);
        }

        if ([] !== $settledList) {
            foreach ( array_keys($settledList) as $id ) {
                unset($this->queue[ $id ]);
            }

            if ([] === $this->queue) {
                if ($settledList[ $id ]->isRejected($reason)) {
                    throw new RuntimeException(
                        [ 'Unhandled rejection in Promise', $reason ]
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @param PromiseItem $promise
     *
     * @return bool
     */
    protected function loopStepTickSettled($promise) : bool
    {
        if (! $promise->isSettled()) {
            return false;
        }

        $promise->onSettled();

        return true;
    }

    /**
     * @param PromiseItem $promise
     *
     * @return bool
     */
    protected function loopStepTickAwaiting($promise) : bool
    {
        if (! $promise->isAwaiting()) {
            return false;
        }

        $isKeepWaiting = false;

        if ($promise->hasFn($fn)) {
            $state = $promise->getState();

            call_user_func($fn);

            $isKeepWaiting = false
                || ($isPooling = ($promise->isPooling()))
                || ($isStateChanged = ($state !== $promise->getState()));
        }

        return $isKeepWaiting;
    }
}
