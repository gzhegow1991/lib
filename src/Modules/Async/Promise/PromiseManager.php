<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Async\Loop\LoopManagerInterface;
use Gzhegow\Lib\Modules\Async\FetchApi\FetchApiInterface;
use Gzhegow\Lib\Modules\Async\Clock\ClockManagerInterface;


class PromiseManager implements PromiseManagerInterface
{
    /**
     * @var LoopManagerInterface
     */
    protected $loop;

    /**
     * @var ClockManagerInterface
     */
    protected $clock;
    /**
     * @var FetchApiInterface
     */
    protected $fetchApi;

    /**
     * @var bool
     */
    protected $useFetchApiWakeup = true;
    /**
     * @var ADeferred
     */
    protected $fetchApiWakeupDeferred;


    public function __construct(
        LoopManagerInterface $loop,
        //
        ?ClockManagerInterface $clock = null,
        ?FetchApiInterface $fetchApi = null
    )
    {
        $this->loop = $loop;

        $this->clock = $clock;
        $this->fetchApi = $fetchApi;
    }


    protected function getClock() : ClockManagerInterface
    {
        return $this->clock = $this->clock ?? Lib::async()->clockManager();
    }

    protected function getFetchApi() : FetchApiInterface
    {
        return $this->fetchApi = $this->fetchApi ?? Lib::async()->fetchApi();
    }


    /**
     * @return APromise|bool|null
     */
    public function from($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? $this->fromInstance($from, $cur)
            ?? $this->fromCallable($from, $cur)
            ?? $this->fromValueResolved($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return APromise|bool|null
     */
    public function fromValue($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? $this->fromInstance($from, $cur)
            ?? $this->fromValueResolved($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return APromise|bool|null
     */
    public function fromCallable($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? $this->fromInstance($from, $cur)
            ?? $this->fromCallableExecutor($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }


    /**
     * @return APromise|bool|null
     */
    protected function fromInstance($from, $ctx = null)
    {
        if ($from instanceof AbstractPromise) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` should be instance of: ' . AbstractPromise::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return APromise|bool|null
     */
    protected function fromValueResolved($from, $ctx = null)
    {
        try {
            $instance = APromise::newResolved($this, $this->loop, $from);
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
     * @return APromise|bool|null
     */
    protected function fromCallableExecutor($from, $ctx = null)
    {
        try {
            $instance = APromise::newPromise($this, $this->loop, $from);
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
     * @return static
     */
    public function useFetchApiWakeup(?bool $useFetchApiWakeup = null)
    {
        $useFetchApiWakeup = $useFetchApiWakeup ?? false;

        $this->useFetchApiWakeup = $useFetchApiWakeup;

        return $this;
    }


    public function isPromise($value) : bool
    {
        return $value instanceof AbstractPromise;
    }

    public function isTheDeferred($value) : bool
    {
        return $value instanceof ADeferred;
    }

    public function isThePromise($value) : bool
    {
        return $value instanceof APromise;
    }


    public function new($fnExecutor) : APromise
    {
        $promise = APromise::newPromise($this, $this->loop, $fnExecutor);

        return $promise;
    }

    public function resolve($value = null) : APromise
    {
        $promise = APromise::newResolved($this, $this->loop, $value);

        return $promise;
    }

    public function reject($reason = null) : APromise
    {
        $promise = APromise::newRejected($this, $this->loop, $reason);

        return $promise;
    }


    public function never() : ADeferred
    {
        $promise = ADeferred::newNever($this, $this->loop);

        return $promise;
    }

    public function defer(\Closure &$fnResolve = null, \Closure &$fnReject = null) : ADeferred
    {
        $promise = ADeferred::newDefer(
            $this, $this->loop,
            $fnResolve, $fnReject
        );

        return $promise;
    }


    public function delay(int $waitMs) : ADeferred
    {
        $clock = $this->getClock();

        $defer = $this->defer($fnResolve);

        $clock->setTimeout($waitMs, $fnResolve);

        return $defer;
    }

    public function pooling(int $tickMs, int $timeoutMs, $fnPooling) : ADeferred
    {
        Lib::type($tt);

        $clock = $this->getClock();

        $tt->int_positive($tickMsInt, $tickMs);
        $tt->int_positive($timeoutMsInt, $timeoutMs);

        $defer = $this->defer($fnResolve, $fnReject);

        $timeoutMt = microtime(true) + ($timeoutMsInt / 1000);

        $fnTick = static function () use (
            $timeoutMt, $timeoutMs,
            $fnPooling, $fnResolve, $fnReject
        ) {
            if (microtime(true) > $timeoutMt) {
                $fnReject("Timeout: {$timeoutMs}");

                return;
            }

            call_user_func($fnPooling, $fnResolve, $fnReject);
        };

        $interval = $clock->setInterval($tickMsInt, $fnTick);

        $defer
            ->finally(static function () use ($clock, $interval) {
                $clock->clearInterval($interval);
            })
        ;

        return $defer;
    }


    /**
     * @param AbstractPromise[] $ps
     */
    public function firstOf(array $ps, ?bool $rejectIfEmpty = null) : AbstractPromise
    {
        $rejectIfEmpty = $rejectIfEmpty ?? true;

        if ([] === $ps) {
            if ($rejectIfEmpty) {
                return $this->reject(
                    new LogicException('The `ps` should be non-empty array')
                );
            }

            return $this->never();
        }

        $defer = $this->defer($fnResolveParent, $fnRejectParent);

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

        return $defer;
    }

    /**
     * @param AbstractPromise[] $ps
     */
    public function firstResolvedOf(array $ps, ?bool $rejectIfEmpty = null) : AbstractPromise
    {
        $rejectIfEmpty = $rejectIfEmpty ?? true;

        if ([] === $ps) {
            if ($rejectIfEmpty) {
                return $this->reject(
                    new LogicException('The `ps` should be non-empty array')
                );
            }

            return $this->never();
        }

        $defer = $this->defer($fnResolveParent, $fnRejectParent);

        $psLeft = count($ps);

        $isSettled = false;
        $report = [];

        foreach ( $ps as $i => $v ) {
            $fnOnResolvedChild = static function ($value) use (
                &$isSettled,
                //
                &$report,
                &$psLeft,
                //
                $fnResolveParent, $i
            ) {
                if (false !== $report) {
                    $report[ $i ] = [
                        'status' => AbstractPromise::STATE_RESOLVED,
                        'value'  => $value,
                    ];
                }

                $psLeft--;

                if (! $isSettled) {
                    call_user_func($fnResolveParent, $value);

                    $report = false;

                    $isSettled = true;
                }

                return $value;
            };

            $fnOnRejectedChild = static function ($reason) use (
                &$isSettled,
                //
                &$report,
                &$psLeft,
                //
                $fnRejectParent, $i
            ) {
                if (false !== $report) {
                    $report[ $i ] = [
                        'status' => AbstractPromise::STATE_REJECTED,
                        'reason' => $reason,
                    ];
                }

                $psLeft--;

                if (! $isSettled) {
                    $isLast = ($psLeft === 0);

                    if ($isLast) {
                        call_user_func($fnRejectParent, $report);

                        $isSettled = true;
                    }
                }
            };

            $p = $this->isPromise($v) ? $v : $this->resolve($v);

            $p->then($fnOnResolvedChild, $fnOnRejectedChild);
        }

        return $defer;
    }


    /**
     * @param AbstractPromise[] $ps
     */
    public function allOf(array $ps, ?bool $rejectIfEmpty = null) : AbstractPromise
    {
        $rejectIfEmpty = $rejectIfEmpty ?? true;

        if ([] === $ps) {
            if ($rejectIfEmpty) {
                return $this->reject(
                    new LogicException('The `ps` should be non-empty array')
                );
            }

            return $this->resolve(
                []
            );
        }

        $defer = $this->defer($fnResolveParent, $fnRejectParent);

        $psLeft = count($ps);

        $isSettled = false;
        $report = [];

        foreach ( $ps as $i => $v ) {
            $fnOnResolvedChild = static function ($value) use (
                &$isSettled,
                //
                &$report,
                &$psLeft,
                //
                $fnResolveParent, $i
            ) {
                $report[ $i ] = [
                    'status' => AbstractPromise::STATE_RESOLVED,
                    'value'  => $value,
                ];

                $psLeft--;

                if (! $isSettled) {
                    $isLast = ($psLeft === 0);
                    if ($isLast) {
                        call_user_func($fnResolveParent, $report);
                    }
                }
            };

            $fnOnRejectedChild = static function ($reason) use (
                &$isSettled,
                //
                &$report,
                &$psLeft,
                //
                $fnResolveParent, $i
            ) {
                $report[ $i ] = [
                    'status' => AbstractPromise::STATE_REJECTED,
                    'reason' => $reason,
                ];

                $psLeft--;

                if (! $isSettled) {
                    $isLast = ($psLeft === 0);
                    if ($isLast) {
                        call_user_func($fnResolveParent, $report);
                    }
                }
            };

            $p = $this->isPromise($v) ? $v : $this->resolve($v);

            $p->then($fnOnResolvedChild, $fnOnRejectedChild);
        }

        return $defer;
    }

    /**
     * @param AbstractPromise[] $ps
     */
    public function allResolvedOf(array $ps, ?bool $rejectIfEmpty = null) : AbstractPromise
    {
        $rejectIfEmpty = $rejectIfEmpty ?? true;

        if ([] === $ps) {
            if ($rejectIfEmpty) {
                return $this->reject(
                    new LogicException('The `ps` should be non-empty array')
                );
            }

            return $this->resolve(
                []
            );
        }

        $defer = $this->defer($fnResolveParent, $fnRejectParent);

        $psLeft = count($ps);

        $isSettled = false;
        $results = [];
        $report = [];

        foreach ( $ps as $i => $v ) {
            $results[ $i ] = null;

            $fnOnResolvedChild = static function ($value) use (
                &$isSettled,
                //
                &$results,
                &$report,
                &$psLeft,
                //
                $fnResolveParent, $i
            ) {
                if (false !== $results) {
                    $results[ $i ] = $value;
                }

                if (false !== $report) {
                    $report[ $i ] = [
                        'status' => AbstractPromise::STATE_RESOLVED,
                        'value'  => $value,
                    ];
                }

                $psLeft--;

                if (! $isSettled) {
                    $isLast = ($psLeft === 0);

                    if ($isLast) {
                        call_user_func($fnResolveParent, $results);

                        $report = false;

                        $isSettled = true;
                    }
                }

                return $value;
            };

            $fnOnRejectedChild = static function ($reason) use (
                &$isSettled,
                //
                &$results,
                &$report,
                &$psLeft,
                //
                $fnRejectParent, $i
            ) {
                if (false !== $report) {
                    $report[ $i ] = [
                        'status' => AbstractPromise::STATE_REJECTED,
                        'reason' => $reason,
                    ];
                }

                $psLeft--;

                if (! $isSettled) {
                    call_user_func($fnRejectParent, $report);

                    $results = false;

                    $isSettled = true;
                }
            };

            $p = $this->isPromise($v) ? $v : $this->resolve($v);

            $p->then($fnOnResolvedChild, $fnOnRejectedChild);
        }

        return $defer;
    }


    public function timeout(AbstractPromise $promise, int $timeoutMs, $reason = null) : AbstractPromise
    {
        $clock = $this->getClock();

        $promiseTimeout = $this->defer($fnResolveTimeout, $fnRejectTimeout);

        $timer = $clock->setTimeout(
            $timeoutMs,
            static function () use ($timeoutMs, $reason, $fnRejectTimeout) {
                if ($reason instanceof \Throwable) {
                    $reasonThrowable = $reason;

                } elseif (is_array($reason) && ([] !== $reason)) {
                    $reason[] = $timeoutMs;

                    $reasonThrowable = new RuntimeException($reason);

                } elseif (Lib::type()->string_not_empty($reasonString, $reason)) {
                    $reasonThrowable = new RuntimeException([ $reasonString, $timeoutMs ]);

                } else {
                    $reasonThrowable = new RuntimeException("Timeout: {$timeoutMs}ms");
                }

                call_user_func($fnRejectTimeout, $reasonThrowable);
            }
        );

        $promiseRace = $this
            ->firstOf([ $promise, $promiseTimeout ])
            ->finally(static function () use ($clock, $timer) {
                $clock->clearTimeout($timer);
            })
        ;

        return $promiseRace;
    }


    /**
     * @param array<int, mixed> $curlOptions
     */
    public function fetchCurl(string $url, array $curlOptions = [], ?int $timeoutMs = null) : ADeferred
    {
        Lib::type($tt);

        $tt->url($urlString, $url);
        $tt->list($curlOptionsList, $curlOptions);

        is_null($timeoutMsInt = $timeoutMs)
        || $tt->int_positive($timeoutMsInt, $timeoutMs);

        $urlString = $urlString ?? '';
        $curlOptionsList = $curlOptionsList ?? [];
        $timeoutMsInt = $timeoutMsInt ?? 10000;

        $promise = $this
            ->fetchApiAwait()
            ->then(function () use ($urlString, $curlOptionsList) {
                return $this->fetchApiPushTask($urlString, $curlOptionsList);
            })
            ->then(function ($taskId) use ($timeoutMsInt) {
                return $this->fetchApiTaskGetResult($taskId, $timeoutMsInt);
            })
        ;

        return $promise;
    }

    /**
     * @return AbstractPromise
     */
    protected function fetchApiAwait()
    {
        $fetchApi = $this->getFetchApi();

        if ($fetchApi->daemonIsAwake()) {
            return Promise::resolve();

        } elseif ($this->useFetchApiWakeup) {
            return $this->fetchApiWakeup();

        } else {
            return Promise::reject('Daemon is sleeping');
        }
    }

    /**
     * @return ADeferred
     */
    protected function fetchApiWakeup()
    {
        $promise = $this->fetchApiWakeupDeferred;

        if ((null === $promise) || $promise->isSettled()) {
            $fetchApi = $this->getFetchApi();

            $fetchApi->daemonWakeup(10000, 1000);

            $fnPooling = static function ($fnResolve) use ($fetchApi) {
                $statusGet = $fetchApi->daemonIsAwake();
                if (! $statusGet) {
                    return;
                }

                $fnResolve();
            };

            $promise = $this->pooling(100, 10000, $fnPooling);

            $this->fetchApiWakeupDeferred = $promise;
        }

        return $this->fetchApiWakeupDeferred;
    }

    /**
     * @return string
     */
    protected function fetchApiPushTask($url, $curlOptions = [])
    {
        $fetchApi = $this->getFetchApi();

        $statusPush = $fetchApi->pushTask(
            $url, $curlOptions, 1000,
            $taskId
        );

        if (! $statusPush) {
            throw new RuntimeException(
                [ 'Unable to ' . __METHOD__, func_get_args() ]
            );
        }

        return $taskId;
    }

    /**
     * @return ADeferred
     */
    protected function fetchApiTaskGetResult(
        string $taskId,
        ?int $timeoutMs = null
    )
    {
        $fetchApi = $this->getFetchApi();

        $fnPooling = static function ($fnResolve) use ($fetchApi, $taskId) {
            $status = $fetchApi->taskFlushResult($taskId, $taskResult);

            if ($status) {
                $fnResolve($taskResult);
            }
        };

        $promise = $this->pooling(100, $timeoutMs, $fnPooling);

        return $promise;
    }
}
