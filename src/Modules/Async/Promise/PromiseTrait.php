<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;


/**
 * @mixin Promise
 */
trait PromiseTrait
{
    /**
     * @var bool
     */
    public static $debug = false;


    /**
     * @param Ret $ret
     *
     * @return Promise|bool|null
     */
    public static function from($from, $ret = null)
    {
        $p = static::getInstance()->from($from, $ret);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @param Ret $ret
     *
     * @return Promise|bool|null
     */
    public static function fromValue($from, $ret = null)
    {
        $p = static::getInstance()->fromValue($from, $ret);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @param callable $from
     * @param Ret      $ret
     *
     * @return Promise|bool|null
     */
    public static function fromCallable($from, $ret = null)
    {
        $p = static::getInstance()->fromCallable($from, $ret);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
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

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @return Promise
     */
    public static function resolved($value = null)
    {
        $p = static::getInstance()->resolved($value);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }

    /**
     * @return Promise
     */
    public static function rejected($reason = null)
    {
        $p = static::getInstance()->rejected($reason);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
        }

        return $p;
    }


    /**
     * @return Promise
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
     * @param \Closure $refFnResolve
     * @param \Closure $refFnReject
     *
     * @return Promise
     */
    public static function defer(&$refFnResolve = null, &$refFnReject = null)
    {
        $p = static::getInstance()->defer($refFnResolve, $refFnReject);

        if (static::$debug) {
            $p->{'debug'} = Lib::debug()->file_line();
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
     * @return Promise
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
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
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
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
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
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
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
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
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
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
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
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
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
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
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
     * @param Promise[] $ps
     * @param bool|null $rejectIfEmpty
     *
     * @return Promise
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
     * @param Promise $promise
     * @param int     $timeoutMs
     *
     * @return Promise
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
     * @return Promise
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
