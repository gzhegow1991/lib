<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

use Gzhegow\Lib\Modules\Type\Ret;


interface PromiseManagerInterface
{
    /**
     * @return Promise|Ret<Promise>
     */
    public function from($from, ?array $fallback = null);

    /**
     * @return Promise|Ret<Promise>
     */
    public function fromValue($from, ?array $fallback = null);

    /**
     * @param callable $from
     *
     * @return Promise|Ret<Promise>
     */
    public function fromCallable($from, ?array $fallback = null);


    /**
     * @return static
     */
    public function useFetchApiWakeup(?bool $useFetchApiWakeup = null);


    public function isPromise($value) : bool;


    /**
     * @param callable $fnExecutor
     */
    public function new($fnExecutor) : Promise;

    public function resolved($value = null) : Promise;

    public function rejected($reason = null) : Promise;


    public function never() : Promise;

    public function defer(?\Closure &$refFnResolve = null, ?\Closure &$refFnReject = null) : Promise;


    public function delay(int $waitMs) : Promise;

    /**
     * @param callable $fnPooling
     */
    public function pooling(int $tickMs, ?int $timeoutMs, $fnPooling) : Promise;


    /**
     * @param Promise[] $ps
     */
    public function firstOf(array $ps, ?bool $rejectIfEmpty = null) : Promise;

    /**
     * @param Promise[] $ps
     */
    public function firstResolvedOf(array $ps, ?bool $rejectIfEmpty = null) : Promise;


    /**
     * @param Promise[] $ps
     */
    public function allOf(array $ps, ?bool $rejectIfEmpty = null) : Promise;

    /**
     * @param Promise[] $ps
     */
    public function allResolvedOf(array $ps, ?bool $rejectIfEmpty = null) : Promise;


    public function timeout(Promise $promise, int $timeoutMs, $rejectReason = null) : Promise;


    /**
     * @param array<int, mixed> $curlOptions
     */
    public function fetchCurl(string $url, array $curlOptions = []) : Promise;
}
