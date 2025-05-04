<?php

namespace Gzhegow\Lib\Modules\Php\Promise;

use Gzhegow\Lib\Lib;


class Promise
{
    public static function isPromise($value) : bool
    {
        return static::getInstance()->isPromise($value);
    }


    public static function new($fnExecutor) : PromiseItem
    {
        return static::getInstance()->new($fnExecutor);
    }


    public static function resolve($value = null) : PromiseItem
    {
        return static::getInstance()->resolve($value);
    }

    public static function reject($reason = null) : PromiseItem
    {
        return static::getInstance()->reject($reason);
    }


    public static function all(array $ps) : PromiseItem
    {
        return static::getInstance()->all($ps);
    }

    public static function allSettled(array $ps) : PromiseItem
    {
        return static::getInstance()->allSettled($ps);
    }

    public static function race(array $ps) : PromiseItem
    {
        return static::getInstance()->race($ps);
    }

    public static function any(array $ps) : PromiseItem
    {
        return static::getInstance()->any($ps);
    }


    public static function never() : PromiseItem
    {
        return static::getInstance()->never();
    }

    public static function defer(\Closure &$fnResolve = null, \Closure &$fnReject = null) : PromiseItem
    {
        return static::getInstance()->defer($fnResolve, $fnReject);
    }


    public static function delay(float $ms) : PromiseItem
    {
        return static::getInstance()->delay($ms);
    }

    public static function pooling(float $ms, $fnExecutor) : PromiseItem
    {
        return static::getInstance()->pooling($ms, $fnExecutor);
    }


    public static function timeout(PromiseItem $promise, float $ms, $reason = null) : PromiseItem
    {
        return static::getInstance()->timeout($promise, $ms, $reason);
    }


    public static function getInstance() : PromiseManagerInterface
    {
        return Lib::php()->promiseManager();
    }
}
