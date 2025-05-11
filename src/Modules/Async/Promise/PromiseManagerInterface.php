<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

interface PromiseManagerInterface
{
    /**
     * @return APromise|bool|null
     */
    public function from($from, $ctx = null);

    /**
     * @return APromise|bool|null
     */
    public function fromValue($from, $ctx = null);

    /**
     * @param callable $from
     *
     * @return APromise|bool|null
     */
    public function fromCallable($from, $ctx = null);


    /**
     * @return static
     */
    public function useFetchApiWakeup(?bool $useFetchApiWakeup = null);


    public function isPromise($value) : bool;

    public function isTheDeferred($value) : bool;

    public function isThePromise($value) : bool;


    public function new($fnExecutor) : APromise;

    public function resolve($value = null) : APromise;

    public function reject($reason = null) : APromise;


    public function never() : ADeferred;

    public function defer(\Closure &$fnResolve = null, \Closure &$fnReject = null) : ADeferred;


    public function delay(int $waitMs) : ADeferred;

    public function pooling(int $tickMs, int $timeoutMs, $fnPooling) : ADeferred;


    /**
     * @param AbstractPromise[] $ps
     */
    public function firstOf(array $ps, ?bool $rejectIfEmpty = null) : AbstractPromise;

    /**
     * @param AbstractPromise[] $ps
     */
    public function firstResolvedOf(array $ps, ?bool $rejectIfEmpty = null) : AbstractPromise;


    /**
     * @param AbstractPromise[] $ps
     */
    public function allOf(array $ps, ?bool $rejectIfEmpty = null) : AbstractPromise;

    /**
     * @param AbstractPromise[] $ps
     */
    public function allResolvedOf(array $ps, ?bool $rejectIfEmpty = null) : AbstractPromise;


    public function timeout(AbstractPromise $promise, int $timeoutMs, $reason = null) : AbstractPromise;


    /**
     * @param array<int, mixed> $curlOptions
     */
    public function fetchCurl(string $url, array $curlOptions = []) : ADeferred;
}
