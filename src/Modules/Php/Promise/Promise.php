<?php

namespace Gzhegow\Lib\Modules\Php\Promise;

use Gzhegow\Lib\Lib;


class Promise
{
    public static function isPromise($value) : bool
    {
        return static::getInstance()->isPromise($value);
    }


    /**
     * @return PromiseItem|bool|null
     */
    public static function from($from, $ctx = null)
    {
        return static::getInstance()->from($from, $ctx);
    }

    /**
     * @return PromiseItem|bool|null
     */
    public static function fromValue($from, $ctx = null)
    {
        return static::getInstance()->fromValue($from, $ctx);
    }

    /**
     * @param callable $from
     *
     * @return PromiseItem|bool|null
     */
    public static function fromCallable($from, $ctx = null)
    {
        return static::getInstance()->fromCallable($from, $ctx);
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


    public static function never() : PromiseItem
    {
        return static::getInstance()->never();
    }

    /**
     * @param \Closure $fnResolve
     * @param \Closure $fnReject
     */
    public static function defer(&$fnResolve = null, &$fnReject = null) : PromiseItem
    {
        return static::getInstance()->defer($fnResolve, $fnReject);
    }


    /**
     * @param float $ms
     */
    public static function delay($ms) : PromiseItem
    {
        return static::getInstance()->delay($ms);
    }

    /**
     * @param float $ms
     */
    public static function pooling($ms, $fnExecutor) : PromiseItem
    {
        return static::getInstance()->pooling($ms, $fnExecutor);
    }


    /**
     * @param array $ps
     */
    public static function all($ps) : PromiseItem
    {
        return static::getInstance()->all($ps);
    }

    /**
     * @param array $ps
     */
    public static function allSettled($ps) : PromiseItem
    {
        return static::getInstance()->allSettled($ps);
    }

    /**
     * @param array $ps
     */
    public static function race($ps) : PromiseItem
    {
        return static::getInstance()->race($ps);
    }

    /**
     * @param array $ps
     */
    public static function any($ps) : PromiseItem
    {
        return static::getInstance()->any($ps);
    }


    /**
     * @param PromiseItem $promise
     * @param float       $ms
     */
    public static function timeout($promise, $ms, $reason = null) : PromiseItem
    {
        return static::getInstance()->timeout($promise, $ms, $reason);
    }


    public static function getInstance() : PromiseManagerInterface
    {
        return Lib::php()->promiseManager();
    }
}
