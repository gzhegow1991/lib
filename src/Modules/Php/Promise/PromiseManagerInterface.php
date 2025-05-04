<?php

namespace Gzhegow\Lib\Modules\Php\Promise;

interface PromiseManagerInterface
{
    public function isPromise($value) : bool;


    /**
     * @return PromiseItem|bool|null
     */
    public function from($from, $ctx = null);

    /**
     * @return PromiseItem|bool|null
     */
    public function fromValue($from, $ctx = null);

    /**
     * @param callable $from
     *
     * @return PromiseItem|bool|null
     */
    public function fromCallable($from, $ctx = null);


    /**
     * @return PromiseItem|bool|null
     */
    public function fromStatic($from, $ctx = null);

    /**
     * @return PromiseItem|bool|null
     */
    public function fromResolved($from, $ctx = null);

    /**
     * @return PromiseItem|bool|null
     */
    public function fromRejected($from, $ctx = null);

    /**
     * @return PromiseItem|bool|null
     */
    public function fromExecutor($from, $ctx = null);


    public function new($fnExecutor) : PromiseItem;

    public function resolve($value = null) : PromiseItem;

    public function reject($reason = null) : PromiseItem;


    public function never() : PromiseItem;

    public function defer(\Closure &$fnResolve = null, \Closure &$fnReject = null) : PromiseItem;


    public function delay(float $ms) : PromiseItem;

    public function pooling(float $ms, $fnExecutor) : PromiseItem;


    public function all(array $ps) : PromiseItem;

    public function allSettled(array $ps) : PromiseItem;

    public function race(array $ps) : PromiseItem;

    public function any(array $ps) : PromiseItem;


    public function timeout(PromiseItem $promise, float $ms, $reason = null) : PromiseItem;
}
