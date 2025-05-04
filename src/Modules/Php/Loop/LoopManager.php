<?php

namespace Gzhegow\Lib\Modules\Php\Loop;

use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Timer\TimerItem;
use Gzhegow\Lib\Modules\Php\Timer\IntervalItem;
use Gzhegow\Lib\Modules\Php\Promise\PromiseItem;


class LoopManager implements LoopManagerInterface
{
    /**
     * @var bool
     */
    protected $signalBreak = false;

    /**
     * @var string
     */
    protected $queueId = 0;

    /**
     * @var array<PromiseItem|TimerItem|IntervalItem>
     */
    protected $queue = [];
    /**
     * @var array<PromiseItem|TimerItem|IntervalItem>
     */
    protected $queueBuffer = [];

    /**
     * @var IntervalItem[]
     */
    protected $queueInterval = [];
    /**
     * @var PromiseItem[]
     */
    protected $queuePromise = [];
    /**
     * @var TimerItem[]
     */
    protected $queueTimer = [];

    /**
     * @var bool
     */
    protected $isLoopRegistered = false;


    public function isInterval($value) : bool
    {
        return $value instanceof IntervalItem;
    }

    public function isPromise($value) : bool
    {
        return $value instanceof PromiseItem;
    }

    public function isTimer($value) : bool
    {
        return $value instanceof TimerItem;
    }


    protected function reset() : void
    {
        $this->queueId = 0;

        $this->queue = [];
        $this->queueBuffer = [];

        $this->queueInterval = [];
        $this->queuePromise = [];
        $this->queueTimer = [];

        $this->signalBreak = false;
    }


    public function addInterval(IntervalItem $interval) : string
    {
        $id = ++$this->queueId;

        $this->queueBuffer[ $id ] = $interval;
        $this->queueInterval[ $id ] = true;

        if (! $this->isLoopRegistered) {
            register_shutdown_function([ $this, 'runLoop' ]);

            $this->isLoopRegistered = true;
        }

        return $id;
    }

    public function addPromise(PromiseItem $promise) : string
    {
        $id = ++$this->queueId;

        $this->queueBuffer[ $id ] = $promise;
        $this->queuePromise[ $id ] = true;

        if (! $this->isLoopRegistered) {
            register_shutdown_function([ $this, 'runLoop' ]);

            $this->isLoopRegistered = true;
        }

        return $id;
    }

    public function addTimer(TimerItem $timer) : string
    {
        $id = ++$this->queueId;

        $this->queueBuffer[ $id ] = $timer;
        $this->queueTimer[ $id ] = true;

        if (! $this->isLoopRegistered) {
            register_shutdown_function([ $this, 'runLoop' ]);

            $this->isLoopRegistered = true;
        }

        return $id;
    }


    public function runLoop() : void
    {
        do {
            $this->queue += $this->queueBuffer;
            $this->queueBuffer = [];

            $this->loopStep($this->signalBreak);
        } while ( ! $this->signalBreak );

        $this->reset();
    }

    public function registerLoop() : void
    {
        if (! $this->isLoopRegistered) {
            register_shutdown_function([ $this, 'runLoop' ]);

            $this->isLoopRegistered = true;
        }
    }

    /**
     * @param bool $signalBreak
     */
    protected function loopStep(&$signalBreak) : void
    {
        $signalBreak = true;

        if ([] === $this->queue) {
            return;
        }

        $lastSettledPromise = null;

        foreach ( $this->queue as $id => $item ) {

            if (isset($this->queuePromise[ $id ])) {
                $promise = $item;
                $promiseState = $item->getState();

                if (PromiseItem::STATE_POOLING === $promiseState) {
                    if ($promise->hasFnSettler($fn)) {
                        call_user_func($fn);
                    }

                    $signalBreak = false;

                    continue;
                }

                if (PromiseItem::STATE_PENDING === $promiseState) {
                    if ($promise->hasFn($fn)) {
                        call_user_func($fn);

                        if ($promiseState !== $promise->getState()) {
                            $signalBreak = false;
                        }
                    }

                    continue;
                }

                if (
                    (PromiseItem::STATE_REJECTED === $promiseState)
                    || (PromiseItem::STATE_RESOLVED === $promiseState)
                ) {
                    $promise->onSettled($this);

                    $lastSettledPromise = $promise;

                    unset($this->queue[ $id ]);
                    unset($this->queuePromise[ $id ]);

                    $signalBreak = false;

                    continue;
                }
            }

            if (isset($this->queueTimer[ $id ])) {
                $timer = $item;
                $timerState = $item->getState();

                if (TimerItem::STATE_POOLING === $timerState) {
                    $timer->tick();

                    $signalBreak = false;

                    continue;
                }

                if (TimerItem::STATE_PENDING === $timerState) {
                    $timer->start();

                    $signalBreak = false;

                    continue;
                }

                if (TimerItem::STATE_TIMEOUT === $timerState) {
                    $timer->onTimeout($this);

                    unset($this->queue[ $id ]);
                    unset($this->queueTimer[ $id ]);

                    $signalBreak = false;

                    continue;
                }

                if (TimerItem::STATE_CANCELLED === $timerState) {
                    unset($this->queue[ $id ]);
                    unset($this->queueTimer[ $id ]);

                    $signalBreak = false;

                    continue;
                }
            }

            if (isset($this->queueInterval[ $id ])) {
                $interval = $item;
                $intervalState = $item->getState();

                if (IntervalItem::STATE_POOLING === $intervalState) {
                    $interval->tick();

                    $signalBreak = false;

                    continue;
                }

                if (IntervalItem::STATE_PENDING === $intervalState) {
                    $interval->start();

                    $signalBreak = false;

                    continue;
                }

                if (IntervalItem::STATE_TIMEOUT === $intervalState) {
                    $interval->onTimeout($this);

                    $interval->restart();

                    $signalBreak = false;

                    continue;
                }

                if (IntervalItem::STATE_CANCELLED === $intervalState) {
                    unset($this->queue[ $id ]);
                    unset($this->queueInterval[ $id ]);

                    $signalBreak = false;

                    continue;
                }
            }

            unset(
                $promise, $promiseState,
                $timer, $timerState,
                $interval, $intervalState
            );

            usleep(1000);

        }

        if (
            (null !== $lastSettledPromise)
            && ([] === $this->queue)
            && ([] === $this->queueBuffer)
        ) {
            if ($lastSettledPromise->isRejected($reason)) {
                throw new RuntimeException(
                    [ 'Unhandled rejection in Promise', $reason ]
                );
            }
        }
    }
}
