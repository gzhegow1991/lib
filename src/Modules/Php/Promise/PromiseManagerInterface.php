<?php

namespace Gzhegow\Lib\Modules\Php\Promise;

interface PromiseManagerInterface
{
    public function new($fnExecute) : PromiseItem;


    public function resolve($value = null) : PromiseItem;

    public function reject($reason = null) : PromiseItem;


    public function all(array $ps) : PromiseItem;

    public function allSettled(array $ps) : PromiseItem;

    public function race(array $ps) : PromiseItem;

    public function any(array $ps) : PromiseItem;


    public function never() : PromiseItem;

    public function defer(\Closure &$fnResolve = null, \Closure &$fnReject = null) : PromiseItem;


    public function pooling($fnTick) : PromiseItem;

    public function delay(float $ms) : PromiseItem;

    public function timeout(PromiseItem $promise, float $ms, $reason = null) : PromiseItem;


    public function isPromise($value) : bool;


    public function add(PromiseItem $promise) : int;


    public function loop();
}
