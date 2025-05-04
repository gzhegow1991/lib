<?php

namespace Gzhegow\Lib\Modules\Php\Promise;

interface PromiseManagerInterface
{
    public function isPromise($value) : bool;


    public function new($fnExecutor) : PromiseItem;


    public function resolve($value = null) : PromiseItem;

    public function reject($reason = null) : PromiseItem;


    public function all(array $ps) : PromiseItem;

    public function allSettled(array $ps) : PromiseItem;

    public function race(array $ps) : PromiseItem;

    public function any(array $ps) : PromiseItem;


    public function never() : PromiseItem;

    public function defer(\Closure &$fnResolve = null, \Closure &$fnReject = null) : PromiseItem;


    public function delay(float $ms) : PromiseItem;

    public function pooling(float $ms, $fnExecutor) : PromiseItem;


    public function timeout(PromiseItem $promise, float $ms, $reason = null) : PromiseItem;
}
