<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

use Gzhegow\Lib\Lib;


class Promise
{
    /**
     * @var bool
     */
    public static $debug = false;


    /**
     * @return APromise|bool|null
     */
    public static function from($from, $ctx = null)
    {
        $p = static::getInstance()->from($from, $ctx);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @return APromise|bool|null
     */
    public static function fromValue($from, $ctx = null)
    {
        $p = static::getInstance()->fromValue($from, $ctx);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @param callable $from
     *
     * @return APromise|bool|null
     */
    public static function fromCallable($from, $ctx = null)
    {
        $p = static::getInstance()->fromCallable($from, $ctx);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    public static function isPromise($value) : bool
    {
        return static::getInstance()->isPromise($value);
    }

    public static function isThePromise($value) : bool
    {
        return static::getInstance()->isThePromise($value);
    }

    public static function isTheDeferred($value) : bool
    {
        return static::getInstance()->isTheDeferred($value);
    }


    /**
     * @param callable $fnExecutor
     *
     * @return APromise
     */
    public static function new($fnExecutor)
    {
        $p = static::getInstance()->new($fnExecutor);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @return APromise
     */
    public static function resolve($value = null)
    {
        $p = static::getInstance()->resolve($value);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @return APromise
     */
    public static function reject($reason = null)
    {
        $p = static::getInstance()->reject($reason);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    /**
     * @return ADeferred
     */
    public static function never()
    {
        $p = static::getInstance()->never();

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @param \Closure $fnResolve
     * @param \Closure $fnReject
     *
     * @return ADeferred
     */
    public static function defer(&$fnResolve = null, &$fnReject = null)
    {
        $p = static::getInstance()->defer($fnResolve, $fnReject);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    /**
     * @param int $waitMs
     *
     * @return ADeferred
     */
    public static function delay($waitMs)
    {
        $p = static::getInstance()->delay($waitMs);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @param int      $tickMs
     * @param int      $timeoutMs
     * @param callable $fnExecutor
     *
     * @return ADeferred
     */
    public static function pooling($tickMs, $timeoutMs, $fnExecutor)
    {
        $p = static::getInstance()->pooling($tickMs, $timeoutMs, $fnExecutor);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    /**
     * @param AbstractPromise[] $ps
     * @param bool|null         $rejectIfEmpty
     *
     * @return AbstractPromise
     */
    public static function race($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->firstOf($ps, $rejectIfEmpty);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @param AbstractPromise[] $ps
     * @param bool|null         $rejectIfEmpty
     *
     * @return AbstractPromise
     */
    public static function firstOf($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->firstOf($ps, $rejectIfEmpty);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    /**
     * @param AbstractPromise[] $ps
     * @param bool|null         $rejectIfEmpty
     *
     * @return AbstractPromise
     */
    public static function any($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->firstResolvedOf($ps, $rejectIfEmpty);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @param AbstractPromise[] $ps
     * @param bool|null         $rejectIfEmpty
     *
     * @return AbstractPromise
     */
    public static function firstResolvedOf($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->firstResolvedOf($ps, $rejectIfEmpty);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    /**
     * @param AbstractPromise[] $ps
     * @param bool|null         $rejectIfEmpty
     *
     * @return AbstractPromise
     */
    public static function allSettled($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->allOf($ps, $rejectIfEmpty);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @param AbstractPromise[] $ps
     * @param bool|null         $rejectIfEmpty
     *
     * @return AbstractPromise
     */
    public static function allOf($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->allOf($ps, $rejectIfEmpty);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    /**
     * @param AbstractPromise[] $ps
     * @param bool|null         $rejectIfEmpty
     *
     * @return AbstractPromise
     */
    public static function all($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->allResolvedOf($ps, $rejectIfEmpty);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @param AbstractPromise[] $ps
     * @param bool|null         $rejectIfEmpty
     *
     * @return AbstractPromise
     */
    public static function allResolvedOf($ps, $rejectIfEmpty = null)
    {
        $p = static::getInstance()->allResolvedOf($ps, $rejectIfEmpty);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    /**
     * @param AbstractPromise $promise
     * @param int             $timeoutMs
     *
     * @return AbstractPromise
     */
    public static function timeout($promise, $timeoutMs, $reason = null)
    {
        $p = static::getInstance()->timeout($promise, $timeoutMs, $reason);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    /**
     * @param string            $url
     * @param array<int, mixed> $curlOptions
     *
     * @return ADeferred
     */
    public static function fetchCurl($url, $curlOptions = [])
    {
        $p = static::getInstance()->fetchCurl($url, $curlOptions);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    public static function getInstance() : PromiseManagerInterface
    {
        return Lib::async()->promiseManager();
    }
}
