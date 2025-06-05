<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Async\Loop\LoopManagerInterface;
use Gzhegow\Lib\Modules\Async\FetchApi\FetchApiInterface;
use Gzhegow\Lib\Modules\Async\Clock\ClockManagerInterface;
use Gzhegow\Lib\Modules\Async\Promise\Pooling\PromisePoolingFactoryInterface;


class PromiseManager implements PromiseManagerInterface
{
    /**
     * @var LoopManagerInterface
     */
    protected $loop;
    /**
     * @var PromisePoolingFactoryInterface
     */
    protected $poolingFactory;

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
    protected $useFetchApiWakeup = false;
    /**
     * @var Promise
     */
    protected $fetchApiWakeupDeferred;


    public function __construct(
        LoopManagerInterface $loop,
        PromisePoolingFactoryInterface $poolingFactory,
        //
        ?ClockManagerInterface $clock = null,
        ?FetchApiInterface $fetchApi = null
    )
    {
        $this->loop = $loop;
        $this->poolingFactory = $poolingFactory;

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
     * @param Ret $ret
     *
     * @return Promise|bool|null
     */
    public function from($from, $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? $this->fromInstance($from, $retCur)
            ?? $this->fromCallable($from, $retCur)
            ?? $this->fromValueResolved($from, $retCur);

        if ($retCur->isErr()) {
            return Result::err($ret, $retCur);
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return Promise|bool|null
     */
    public function fromValue($from, $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? $this->fromInstance($from, $retCur)
            ?? $this->fromValueResolved($from, $retCur);

        if ($retCur->isErr()) {
            return Result::err($ret, $retCur);
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return Promise|bool|null
     */
    public function fromCallable($from, $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? $this->fromInstance($from, $retCur)
            ?? $this->fromCallableExecutor($from, $retCur);

        if ($retCur->isErr()) {
            return Result::err($ret, $retCur);
        }

        return Result::ok($ret, $instance);
    }


    /**
     * @param Ret $ret
     *
     * @return Promise|bool|null
     */
    protected function fromInstance($from, $ret = null)
    {
        if ($from instanceof Promise) {
            return Result::ok($ret, $from);
        }

        return Result::err(
            $ret,
            [ 'The `from` should be instance of: ' . Promise::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @param Ret $ret
     *
     * @return Promise|bool|null
     */
    protected function fromValueResolved($from, $ret = null)
    {
        try {
            $instance = Promise::newResolved($this, $this->loop, $from);
        }
        catch ( \Throwable $e ) {
            return Result::err(
                $ret,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return Promise|bool|null
     */
    protected function fromCallableExecutor($from, $ret = null)
    {
        try {
            $instance = Promise::newPromise($this, $this->loop, $from);
        }
        catch ( \Throwable $e ) {
            return Result::err(
                $ret,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Result::ok($ret, $instance);
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
        $promise = Promise::newPromise($this, $this->loop, $fnExecutor);

        return $promise;
    }

    public function resolved($value = null) : Promise
    {
        $promise = Promise::newResolved($this, $this->loop, $value);

        return $promise;
    }

    public function rejected($reason = null) : Promise
    {
        $promise = Promise::newRejected($this, $this->loop, $reason);

        return $promise;
    }


    public function never() : Promise
    {
        $promise = Promise::newNever(
            $this, $this->loop
        );

        return $promise;
    }

    public function defer(?\Closure &$refFnResolve = null, ?\Closure &$refFnReject = null) : Promise
    {
        $promise = Promise::newDefer(
            $this, $this->loop,
            $refFnResolve, $refFnReject
        );

        return $promise;
    }


    public function delay(int $waitMs) : Promise
    {
        $clock = $this->getClock();

        $defer = $this->defer($fnResolve);

        $clock->setTimeout($waitMs, $fnResolve);

        return $defer;
    }

    /**
     * @param callable $fnPooling
     */
    public function pooling(int $tickMs, ?int $timeoutMs, $fnPooling) : Promise
    {
        $theType = Lib::type();

        if (! $theType->int_positive($tickMsInt, $tickMs)) {
            throw new LogicException(
                [ 'The `tickMs` should be positive integer', $tickMs ]
            );
        }

        $clock = $this->getClock();

        $defer = $this->defer($fnResolve, $fnReject);

        $ctx = $this->poolingFactory->newContext();

        $ctx->resetTimeoutMs($timeoutMs);

        $fnTick = static function () use (
            $ctx,
            $fnPooling,
            $fnResolve, $fnReject
        ) {
            $ctx->updateNowMicrotime();

            call_user_func_array($fnPooling, [ $ctx ]);

            if ($ctx->hasResult($refResult)) {
                $fnResolve($refResult);

            } elseif ($ctx->hasError($refError)) {
                $fnReject($refError);

            } else {
                if (null !== ($timeoutMicrotime = $ctx->hasTimeoutMicrotime())) {
                    if (microtime(true) > $timeoutMicrotime) {
                        $fnReject("Timeout: " . $ctx->getTimeoutMs());
                    }
                }
            }
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
     * @param Promise[] $ps
     */
    public function firstOf(array $ps, ?bool $rejectIfEmpty = null) : Promise
    {
        $rejectIfEmpty = $rejectIfEmpty ?? true;

        if ([] === $ps) {
            if ($rejectIfEmpty) {
                return $this->rejected(
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

        if ([] === $ps) {
            if ($rejectIfEmpty) {
                return $this->rejected(
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
                        'status' => Promise::STATE_RESOLVED,
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
                        'status' => Promise::STATE_REJECTED,
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

        if ([] === $ps) {
            if ($rejectIfEmpty) {
                return $this->rejected(
                    new LogicException('The `ps` should be non-empty array')
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
                $report[ $i ] = [
                    'status' => Promise::STATE_RESOLVED,
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
                    'status' => Promise::STATE_REJECTED,
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

        if ([] === $ps) {
            if ($rejectIfEmpty) {
                return $this->rejected(
                    new LogicException('The `ps` should be non-empty array')
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
                        'status' => Promise::STATE_RESOLVED,
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
                        'status' => Promise::STATE_REJECTED,
                        'reason' => $reason,
                    ];
                }

                $psLeft--;

                if (! $isSettled) {
                    $isLast = ($psLeft === 0);

                    if ($isLast) {
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


    public function timeout(Promise $promise, int $timeoutMs, $reason = null) : Promise
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

        $promiseFirstOf = $this
            ->firstOf([ $promise, $promiseTimeout ])
            ->finally(static function () use ($clock, $timer) {
                $clock->clearTimeout($timer);
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

        if (! $theType->url($urlString, $url)) {
            throw new LogicException(
                [ 'The `url` should be valid url', $url ]
            );
        }

        if (! $theType->list($curlOptionsList, $curlOptions)) {
            throw new LogicException(
                [ 'The `curlOptions` should be list of CURL options', $curlOptions ]
            );
        }

        if (! is_null($timeoutMsInt = $timeoutMs)) {
            if (! $theType->int_positive($timeoutMsInt, $timeoutMs)) {
                throw new LogicException(
                    [ 'The `timeoutMs` should be positive integer', $timeoutMs ]
                );
            }
        }

        $urlString = $urlString ?? '';
        $curlOptionsList = $curlOptionsList ?? [];
        $timeoutMsInt = $timeoutMsInt ?? 10000;

        $taskId = $this->fetchApiPushTask($urlString, $curlOptionsList);

        $promise = $this->fetchApiAwait()
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
        $fetchApi = $this->getFetchApi();

        $statusPush = $fetchApi->pushTask(
            $taskId,
            $url, $curlOptions, 1000
        );

        if (! $statusPush) {
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
        $fetchApi = $this->getFetchApi();

        if ($fetchApi->daemonIsAwake()) {
            return Promise::resolved();

        } elseif ($this->useFetchApiWakeup) {
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
        $promise = $this->fetchApiWakeupDeferred;

        if (false
            || (null === $promise)
            || $promise->isSettled()
        ) {
            $fetchApi = $this->getFetchApi();

            $fetchApi->daemonWakeup(10000, 1000);

            $fnPooling = static function ($ctx) use ($fetchApi) {
                if (! $fetchApi->daemonIsAwake()) {
                    return;
                }

                $ctx->setResult(null);
            };

            $promise = $this->pooling(100, 10000, $fnPooling);

            $this->fetchApiWakeupDeferred = $promise;
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
        $fetchApi = $this->getFetchApi();

        $fnPooling = static function ($ctx) use (
            $fetchApi,
            //
            $taskId
        ) {
            $status = $fetchApi->taskFlushResult(
                $taskResult,
                $taskId
            );

            if (false === $status) {
                return;
            }

            $ctx->setResult($taskResult);
        };

        $promise = $this->pooling(100, $timeoutMs, $fnPooling);

        return $promise;
    }
}
