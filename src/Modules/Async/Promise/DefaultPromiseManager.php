<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Async\Loop\LoopManagerInterface;
use Gzhegow\Lib\Modules\Async\FetchApi\FetchApiInterface;
use Gzhegow\Lib\Modules\Async\Clock\ClockManagerInterface;
use Gzhegow\Lib\Modules\Async\Promise\Pooling\PromisePoolingContext;
use Gzhegow\Lib\Modules\Async\Promise\Pooling\PromisePoolingFactoryInterface;


class DefaultPromiseManager implements PromiseManagerInterface
{
    /**
     * @var PromisePoolingFactoryInterface
     */
    protected $poolingFactory;

    /**
     * @var ClockManagerInterface
     */
    protected $clockManager;
    /**
     * @var LoopManagerInterface
     */
    protected $loopManager;

    /**
     * @var FetchApiInterface
     */
    protected $fetchApi;

    /**
     * @var bool
     */
    protected $useFetchApiWakeup = false;
    /**
     * @var Promise
     */
    protected $fetchApiWakeupDeferred;


    public function __construct(
        PromisePoolingFactoryInterface $poolingFactory,
        //
        ?ClockManagerInterface $clockManager = null,
        ?LoopManagerInterface $loopManager = null,
        //
        ?FetchApiInterface $fetchApi = null
    )
    {
        $theAsync = Lib::async();

        $this->poolingFactory = $poolingFactory;

        $this->clockManager = $clockManager ?? $theAsync->clockManager();
        $this->loopManager = $loopManager ?? $theAsync->loopManager();

        $this->fetchApi = $fetchApi ?? $theAsync->fetchApi();
    }


    /**
     * @return Promise|Ret<Promise>
     */
    public function from($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? $this->fromInstance($from)->orNull($ret)
            ?? $this->fromCallable($from)->orNull($ret)
            ?? $this->fromValueResolved($from)->orNull($ret);

        if ( $ret->isFail() ) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return Promise|Ret<Promise>
     */
    public function fromValue($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? $this->fromInstance($from)->orNull($ret)
            ?? $this->fromValueResolved($from)->orNull($ret);

        if ( $ret->isFail() ) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return Promise|Ret<Promise>
     */
    public function fromCallable($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? $this->fromInstance($from)->orNull($ret)
            ?? $this->fromCallableExecutor($from)->orNull($ret);

        if ( $ret->isFail() ) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }


    /**
     * @return Promise|Ret<Promise>
     */
    protected function fromInstance($from, ?array $fallback = null)
    {
        if ( $from instanceof Promise ) {
            return Ret::ok($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an instance of: ' . Promise::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Promise|Ret<Promise>
     */
    protected function fromValueResolved($from, ?array $fallback = null)
    {
        try {
            $instance = Promise::newResolved($this, $this->loopManager, $from);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fallback,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return Promise|Ret<Promise>
     */
    protected function fromCallableExecutor($from, ?array $fallback = null)
    {
        try {
            $instance = Promise::newPromise($this, $this->loopManager, $from);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fallback,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, $instance);
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
        return $value instanceof Promise;
    }


    /**
     * @param callable $fnExecutor
     */
    public function new($fnExecutor) : Promise
    {
        return Promise::newPromise($this, $this->loopManager, $fnExecutor);
    }

    public function resolved($value = null) : Promise
    {
        return Promise::newResolved($this, $this->loopManager, $value);
    }

    public function rejected($reason = null) : Promise
    {
        return Promise::newRejected($this, $this->loopManager, $reason);
    }


    public function never() : Promise
    {
        return Promise::newNever($this, $this->loopManager);
    }

    public function defer(?\Closure &$refFnResolve = null, ?\Closure &$refFnReject = null) : Promise
    {
        return Promise::newDefer(
            $this, $this->loopManager,
            $refFnResolve, $refFnReject
        );
    }


    public function delay(int $waitMs) : Promise
    {
        $theClockManager = $this->clockManager;

        $defer = $this->defer($fnResolve);

        $theClockManager->setTimeout($waitMs, $fnResolve);

        return $defer;
    }

    /**
     * @param callable $fnPooling
     */
    public function pooling(int $tickMs, ?int $timeoutMs, $fnPooling) : Promise
    {
        $theClockManager = $this->clockManager;
        $theType = Lib::type();

        $tickMsInt = $theType->int_positive($tickMs)->orThrow();

        $defer = $this->defer($fnResolve, $fnReject);

        $ctx = $this->poolingFactory->newContext();

        $ctx->resetTimeoutMs($timeoutMs);

        $fnTick = static function () use (
            $ctx,
            $fnPooling, $fnResolve, $fnReject
        ) {
            $nowMicrotime = $ctx->updateNowMicrotime();

            call_user_func_array($fnPooling, [ $ctx ]);

            if ( $ctx->hasResult($refResult) ) {
                $fnResolve($refResult);

            } elseif ( $ctx->hasError($refError) ) {
                $fnReject($refError);

            } else {
                if ( null !== ($timeoutMicrotime = $ctx->hasTimeoutMicrotime()) ) {
                    if ( $nowMicrotime > $timeoutMicrotime ) {
                        $fnReject("Timeout: " . $ctx->getTimeoutMs());
                    }
                }
            }
        };

        $interval = $theClockManager->setInterval($tickMsInt, $fnTick);

        $defer
            ->finally(static function () use (
                $theClockManager,
                $interval,
                $defer
            ) {
                $theClockManager->clearInterval($interval);
            })
        ;

        return $defer;
    }


    /**
     * @param Promise[] $ps
     */
    public function firstOf(array $ps, ?bool $rejectIfEmpty = null) : Promise
    {
        $rejectIfEmpty = $rejectIfEmpty ?? true;

        if ( [] === $ps ) {
            if ( $rejectIfEmpty ) {
                return $this->rejected(
                    new LogicException('The `ps` should be a non-empty array')
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
            if ( ! $isSettled ) {
                $isSettled = true;

                call_user_func($fnResolveParent, $value);
            }
        };

        $fnOnRejectedChild = static function ($reason) use (
            &$isSettled,
            //
            $fnRejectParent
        ) {
            if ( ! $isSettled ) {
                $isSettled = true;

                call_user_func($fnRejectParent, $reason);
            }
        };

        foreach ( $ps as $v ) {
            $p = $this->isPromise($v) ? $v : $this->resolved($v);

            $p->then($fnOnResolvedChild, $fnOnRejectedChild);
        }

        return $defer;
    }

    /**
     * @param Promise[] $ps
     */
    public function firstResolvedOf(array $ps, ?bool $rejectIfEmpty = null) : Promise
    {
        $rejectIfEmpty = $rejectIfEmpty ?? true;

        if ( [] === $ps ) {
            if ( $rejectIfEmpty ) {
                return $this->rejected(
                    new LogicException('The `ps` should be a non-empty array')
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
                if ( false !== $report ) {
                    $report[$i] = [
                        'status' => Promise::STATE_RESOLVED,
                        'value'  => $value,
                    ];
                }

                $psLeft--;

                if ( ! $isSettled ) {
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
                if ( false !== $report ) {
                    $report[$i] = [
                        'status' => Promise::STATE_REJECTED,
                        'reason' => $reason,
                    ];
                }

                $psLeft--;

                if ( ! $isSettled ) {
                    $isLast = ($psLeft === 0);

                    if ( $isLast ) {
                        call_user_func($fnRejectParent, $report);

                        $isSettled = true;
                    }
                }
            };

            $p = $this->isPromise($v) ? $v : $this->resolved($v);

            $p->then($fnOnResolvedChild, $fnOnRejectedChild);
        }

        return $defer;
    }


    /**
     * @param Promise[] $ps
     */
    public function allOf(array $ps, ?bool $rejectIfEmpty = null) : Promise
    {
        $rejectIfEmpty = $rejectIfEmpty ?? true;

        if ( [] === $ps ) {
            if ( $rejectIfEmpty ) {
                return $this->rejected(
                    new LogicException('The `ps` should be a non-empty array')
                );
            }

            return $this->resolved(
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
                $report[$i] = [
                    'status' => Promise::STATE_RESOLVED,
                    'value'  => $value,
                ];

                $psLeft--;

                if ( ! $isSettled ) {
                    $isLast = ($psLeft === 0);
                    if ( $isLast ) {
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
                $report[$i] = [
                    'status' => Promise::STATE_REJECTED,
                    'reason' => $reason,
                ];

                $psLeft--;

                if ( ! $isSettled ) {
                    $isLast = ($psLeft === 0);
                    if ( $isLast ) {
                        call_user_func($fnResolveParent, $report);
                    }
                }
            };

            $p = $this->isPromise($v) ? $v : $this->resolved($v);

            $p->then($fnOnResolvedChild, $fnOnRejectedChild);
        }

        return $defer;
    }

    /**
     * @param Promise[] $ps
     */
    public function allResolvedOf(array $ps, ?bool $rejectIfEmpty = null) : Promise
    {
        $rejectIfEmpty = $rejectIfEmpty ?? true;

        if ( [] === $ps ) {
            if ( $rejectIfEmpty ) {
                return $this->rejected(
                    new LogicException('The `ps` should be a non-empty array')
                );
            }

            return $this->resolved(
                []
            );
        }

        $defer = $this->defer($fnResolveParent, $fnRejectParent);

        $psLeft = count($ps);

        $isSettled = false;
        $results = [];
        $report = [];

        foreach ( $ps as $i => $v ) {
            $results[$i] = null;

            $fnOnResolvedChild = static function ($value) use (
                &$isSettled,
                //
                &$results,
                &$report,
                &$psLeft,
                //
                $fnResolveParent, $i
            ) {
                if ( false !== $results ) {
                    $results[$i] = $value;
                }

                if ( false !== $report ) {
                    $report[$i] = [
                        'status' => Promise::STATE_RESOLVED,
                        'value'  => $value,
                    ];
                }

                $psLeft--;

                if ( ! $isSettled ) {
                    $isLast = ($psLeft === 0);

                    if ( $isLast ) {
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
                if ( false !== $report ) {
                    $report[$i] = [
                        'status' => Promise::STATE_REJECTED,
                        'reason' => $reason,
                    ];
                }

                $psLeft--;

                if ( ! $isSettled ) {
                    $isLast = ($psLeft === 0);

                    if ( $isLast ) {
                        call_user_func($fnRejectParent, $report);

                    } else {
                        call_user_func($fnRejectParent, $reason);

                        $report = false;
                    }

                    $results = false;

                    $isSettled = true;
                }
            };

            $p = $this->isPromise($v) ? $v : $this->resolved($v);

            $p->then($fnOnResolvedChild, $fnOnRejectedChild);
        }

        return $defer;
    }


    public function timeout(Promise $promise, int $timeoutMs, $rejectReason = null) : Promise
    {
        $theClockManager = $this->clockManager;
        $theType = Lib::type();

        $promiseTimeout = $this->defer($fnResolveTimeout, $fnRejectTimeout);

        $timer = $theClockManager->setTimeout(
            $timeoutMs,
            static function () use (
                $theType,
                $timeoutMs,
                $rejectReason, $fnRejectTimeout
            ) {
                if ( $rejectReason instanceof \Throwable ) {
                    $reasonThrowable = $rejectReason;

                } elseif ( is_array($rejectReason) && ([] !== $rejectReason) ) {
                    $rejectReason[] = $timeoutMs;

                    $reasonThrowable = new RuntimeException($rejectReason);

                } elseif ( $theType->string_not_empty($rejectReason)->isOk([ &$rejectReasonStringNotEmpty ]) ) {
                    $reasonThrowable = new RuntimeException(
                        [ $rejectReasonStringNotEmpty, $timeoutMs ]
                    );

                } else {
                    $reasonThrowable = new RuntimeException("Timeout: {$timeoutMs}ms");
                }

                call_user_func($fnRejectTimeout, $reasonThrowable);
            }
        );

        $promiseFirstOf = $this
            ->firstOf([ $promise, $promiseTimeout ])
            ->finally(static function () use ($theClockManager, $timer) {
                $theClockManager->clearTimeout($timer);
            })
        ;

        return $promiseFirstOf;
    }


    /**
     * @param array<int, mixed> $curlOptions
     */
    public function fetchCurl(string $url, array $curlOptions = [], ?int $timeoutMs = null) : Promise
    {
        $theType = Lib::type();

        $urlValid = $theType->url($url)->orThrow();
        $curlOptionsList = $theType->list($curlOptions)->orThrow();

        $timeoutMsInt = 10000;
        if ( ! is_null($timeoutMs) ) {
            $timeoutMsInt = $theType->int_positive($timeoutMs)->orThrow();
        }

        $taskId = $this->fetchApiPushTask($urlValid, $curlOptionsList);

        $promise = $this->fetchApiAwait();
        $promise = $promise
            ->then(function () use ($taskId, $timeoutMsInt) {
                return $this->fetchApiTaskGetResult($taskId, $timeoutMsInt);
            })
        ;

        return $promise;
    }

    /**
     * @return string
     */
    protected function fetchApiPushTask($url, $curlOptions = [])
    {
        $theFetchApi = $this->fetchApi;

        $statusPush = $theFetchApi->pushTask(
            $taskId,
            $url, $curlOptions, 1000
        );

        if ( ! $statusPush ) {
            throw new RuntimeException(
                [ 'Unable to ' . __METHOD__, func_get_args() ]
            );
        }

        return $taskId;
    }

    /**
     * @return Promise
     */
    protected function fetchApiAwait()
    {
        $theFetchApi = $this->fetchApi;

        if ( $theFetchApi->daemonIsAwake() ) {
            return Promise::resolved();

        } elseif ( $this->useFetchApiWakeup ) {
            return $this->fetchApiWakeup();

        } else {
            return Promise::rejected('Daemon is sleeping');
        }
    }

    /**
     * @return Promise
     */
    protected function fetchApiWakeup()
    {
        $theFetchApi = $this->fetchApi;

        $aPromise = $this->fetchApiWakeupDeferred;

        if ( false
            || (null === $aPromise)
            || ($aPromise->isSettled())
        ) {
            $theFetchApi->daemonWakeup(10000, 1000);

            $fnPooling = static function ($ctx) use ($theFetchApi) {
                /** @var PromisePoolingContext $ctx */

                if ( ! $theFetchApi->daemonIsAwake() ) {
                    return;
                }

                $ctx->setResult(null);
            };

            $aPromise = $this->pooling(100, 10000, $fnPooling);

            $this->fetchApiWakeupDeferred = $aPromise;
        }

        return $this->fetchApiWakeupDeferred;
    }

    /**
     * @return Promise
     */
    protected function fetchApiTaskGetResult(
        string $taskId,
        ?int $timeoutMs = null
    )
    {
        $theFetchApi = $this->fetchApi;

        $fnPooling = static function ($ctx) use (
            $theFetchApi,
            //
            $taskId
        ) {
            /** @var PromisePoolingContext $ctx */

            $status = $theFetchApi->taskFlushResult(
                $taskResult,
                $taskId
            );

            if ( false === $status ) {
                return;
            }

            $ctx->setResult($taskResult);
        };

        $promise = $this->pooling(100, $timeoutMs, $fnPooling);

        return $promise;
    }
}
