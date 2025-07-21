<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;


/**
 * @mixin Promise
 */
trait PromiseTrait
{
    /**
     * @return Promise|Ret<Promise>
     */
    public static function from($from, ?array $fallback = null)
    {
        $ret = static::getInstance()->from($from);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        $p = $ret->getValue();

        if (Promise::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return Ret::val($fallback, $ret);
    }

    /**
     * @return Promise|Ret<Promise>
     */
    public static function fromValue($from, ?array $fallback = null)
    {
        $ret = static::getInstance()->fromValue($from);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        $p = $ret->getValue();

        if (Promise::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return Ret::val($fallback, $ret);
    }

    /**
     * @param callable $from
     *
     * @return Promise|Ret<Promise>
     */
    public static function fromCallable($from, ?array $fallback = null)
    {
        $ret = static::getInstance()->fromCallable($from);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        $p = $ret->getValue();

        if (Promise::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return Ret::val($fallback, $ret);
    }


    public static function isPromise($value) : bool
    {
        return static::getInstance()->isPromise($value);
    }


    /**
     * @param callable $fnExecutor
     *
     * @return Promise
     */
    public static function new($fnExecutor)
    {
        $p = static::getInstance()->new($fnExecutor);

        if (Promise::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }

    /**
     * @return Promise
     */
    public static function resolved($value = null)
    {
        $p = static::getInstance()->resolved($value);

        if (Promise::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }

    /**
     * @return Promise
     */
    public static function rejected($reason = null)
    {
        $p = static::getInstance()->rejected($reason);

        if (Promise::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }


    /**
     * @return Promise
     */
    public static function never()
    {
        $p = static::getInstance()->never();

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }

    /**
     * @param \Closure $refFnResolve
     * @param \Closure $refFnReject
     *
     * @return Promise
     */
    public static function defer(&$refFnResolve = null, &$refFnReject = null)
    {
        $p = static::getInstance()->defer($refFnResolve, $refFnReject);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }


    /**
     * @param int $waitMs
     *
     * @return Promise
     */
    public static function delay($waitMs)
    {
        $p = static::getInstance()->delay($waitMs);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }

    /**
     * @param int      $tickMs
     * @param int      $timeoutMs
     * @param callable $fnExecutor
     *
     * @return Promise
     */
    public static function pooling($tickMs, $timeoutMs, $fnExecutor)
    {
        $p = static::getInstance()->pooling($tickMs, $timeoutMs, $fnExecutor);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }


    /**
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
     */
    public static function race($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->firstOf($ps, $rejectIfEmpty);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }

    /**
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
     */
    public static function firstOf($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->firstOf($ps, $rejectIfEmpty);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }


    /**
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
     */
    public static function any($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->firstResolvedOf($ps, $rejectIfEmpty);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }

    /**
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
     */
    public static function firstResolvedOf($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->firstResolvedOf($ps, $rejectIfEmpty);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }


    /**
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
     */
    public static function allSettled($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->allOf($ps, $rejectIfEmpty);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }

    /**
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
     */
    public static function allOf($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->allOf($ps, $rejectIfEmpty);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }


    /**
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
     */
    public static function all($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->allResolvedOf($ps, $rejectIfEmpty);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }

    /**
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
     */
    public static function allResolvedOf($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->allResolvedOf($ps, $rejectIfEmpty);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }


    /**
     * @param Promise $promise
     * @param int     $timeoutMs
     *
     * @return Promise
     */
    public static function timeout($promise, $timeoutMs, $reason = null)
    {
        $p = static::getInstance()->timeout($promise, $timeoutMs, $reason);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }


    /**
     * @param string            $url
     * @param array<int, mixed> $curlOptions
     *
     * @return Promise
     */
    public static function fetchCurl($url, $curlOptions = [])
    {
        $p = static::getInstance()->fetchCurl($url, $curlOptions);

        if (static::$isDebug) {
            $theDebug = Lib::debug();

            $p->debugInfo = $theDebug->file_line();
        }

        return $p;
    }


    public static function getInstance() : PromiseManagerInterface
    {
        return Lib::async()->promiseManager();
    }
}
