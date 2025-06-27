<?php

namespace Gzhegow\Lib\Modules\Php\Result;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\ErrorBag\Error;


class Result
{
    /**
     * @see static::typeBool()
     * @see static::asBool()
     */
    public static function type() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_TRUE,
            Ret::MODE_ERROR_FALSE,
        );
    }

    /**
     * @see static::asBool()
     */
    public static function typeBool() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_TRUE,
            Ret::MODE_ERROR_FALSE,
        );
    }

    public static function typeThrow() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_TRUE,
            Ret::MODE_ERROR_THROW,
        );
    }


    /**
     * @see static::parseNull()
     * @see static::asValue()
     */
    public static function parse() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_VALUE,
            Ret::MODE_ERROR_NULL,
        );
    }

    /**
     * @see static::asValue()
     */
    public static function parseNull() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_VALUE,
            Ret::MODE_ERROR_NULL,
        );
    }

    /**
     * @see static::asValueThrow()
     */
    public static function parseThrow() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_VALUE,
            Ret::MODE_ERROR_THROW,
        );
    }


    public static function ignore() : Ret
    {
        return $ref = Ret::fromMode(
            Ret::MODE_RESULT_NULL,
            Ret::MODE_ERROR_NULL,
        );
    }

    public static function ignoreThrow() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_NULL,
            Ret::MODE_ERROR_THROW,
        );
    }


    public static function asBool() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_TRUE,
            Ret::MODE_ERROR_FALSE,
        );
    }

    public static function asValue(array $fallback = []) : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_VALUE,
            (([] === $fallback) ? Ret::MODE_ERROR_NULL : Ret::MODE_ERROR_FALLBACK),
            $fallback
        );
    }

    public static function asRet() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_SELF,
            Ret::MODE_ERROR_SELF,
        );
    }


    public static function asValueFallback($fallback) : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_VALUE,
            Ret::MODE_ERROR_FALLBACK,
            [ $fallback ]
        );
    }

    public static function asValueFalse() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_VALUE,
            Ret::MODE_ERROR_FALSE,
            [ false ]
        );
    }

    public static function asValueNull() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_VALUE,
            Ret::MODE_ERROR_NULL,
            [ null ]
        );
    }

    public static function asValueRet() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_VALUE,
            Ret::MODE_ERROR_SELF,
        );
    }

    public static function asValueThrow() : Ret
    {
        return Ret::fromMode(
            Ret::MODE_RESULT_VALUE,
            Ret::MODE_ERROR_THROW,
        );
    }


    /**
     * @param Ret $ret
     *
     * @return Ret|mixed|bool
     */
    public static function pass(&$ret, ...$results)
    {
        if ($ret->isErr()) {
            return Result::err($ret, $ret);
        }

        return [] === $results
            ? Result::ok($ret, $ret)
            : Result::ok($ret, ...$results);
    }

    /**
     * @param Ret   $ret
     * @param mixed $result
     *
     * @return Ret|mixed|true
     */
    public static function ok(&$ret, $result, ...$results)
    {
        $ret = null
            ?? $ret
            ?? static::$currentRet
            ?? static::parseThrow();

        $ret->setResult($result, ...$results);

        if (Ret::MODE_RESULT_NULL === $ret->modeResult) {
            return null;

        } elseif (Ret::MODE_RESULT_SELF === $ret->modeResult) {
            return $ret;

        } elseif (Ret::MODE_RESULT_TRUE === $ret->modeResult) {
            return true;

        } elseif (Ret::MODE_RESULT_VALUE === $ret->modeResult) {
            return $result;
        }

        throw new RuntimeException([ 'Mode `modeResult` is unknown', $ret ]);
    }

    /**
     * @param Ret   $ret
     * @param mixed $error
     *
     * @return Ret|null|false
     */
    public static function err(&$ret, $error, array $trace = [], array $tags = [])
    {
        $ret = null
            ?? $ret
            ?? static::$currentRet
            ?? static::parseThrow();

        $ret->addError($error, $tags, $trace);

        if (Ret::MODE_ERROR_FALLBACK === $ret->modeError) {
            return $ret->getFallback();

        } elseif (Ret::MODE_ERROR_FALSE === $ret->modeError) {
            return false;

        } elseif (Ret::MODE_ERROR_NULL === $ret->modeError) {
            return null;

        } elseif (Ret::MODE_ERROR_SELF === $ret->modeError) {
            return $ret;

        } elseif (Ret::MODE_ERROR_THROW === $ret->modeError) {
            $errorList = $ret->getErrorList();

            if ([] === $trace) {
                $errorObjectList = $ret->getErrors();

                if ([] !== $errorObjectList) {
                    $errorObject = end($errorObjectList);

                    if (false !== $errorObject) {
                        $trace = $errorObject->trace;
                    }
                }
            }

            $e = new LogicException(...$errorList);

            if ([] !== $trace) {
                $traceFile = $trace[ 'file' ] ?? $trace[ 0 ] ?? null;
                $traceLine = $trace[ 'line' ] ?? $trace[ 1 ] ?? null;

                $e->setFile($traceFile);
                $e->setLine($traceLine);
            }

            throw $e;
        }

        throw new RuntimeException([ 'Mode `modeError` is unknown', $ret ]);
    }


    /**
     * @template T
     *
     * @param T      $refResult
     *
     * @param Ret<T> $res
     */
    public static function isOk($res, &$refResult = null) : bool
    {
        return $res->isOk($refResult);
    }

    /**
     * @param Error[] $refErrors
     *
     * @param Ret     $res
     */
    public static function isErr($res, &$refErrors = null) : bool
    {
        return $res->isErr($refErrors);
    }


    /**
     * @template T
     *
     * @param Ret<T>            $res
     * @param array{ 0?: bool } $fallback
     * @param T                 $refResult
     */
    public static function bool(Ret $res, array $fallback = [], &$refResult = null) : bool
    {
        if ($res->isOk($refResult)) {
            return true;
        }

        if ($res->isErr($errors)) {
            if ([] !== $fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }
        }

        return false;
    }

    /**
     * @param array{ 0?: mixed } $fallback
     * @param Error[]            $refErrors
     *
     * @return mixed|null
     */
    public static function val(Ret $res, array $fallback = [], &$refErrors = null)
    {
        if ($res->isOk($result)) {
            return $result;
        }

        if ($res->isErr($refErrors)) {
            if ([] !== $fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }
        }

        return null;
    }


    /**
     * @return mixed
     */
    public static function call(Ret $res, \Closure $fn, array $args = [])
    {
        [ $previousRes, static::$currentRet ] = [ static::$currentRet, $res ];

        try {
            $result = call_user_func_array($fn, $args);
        }
        finally {
            static::$currentRet = $previousRes;
        }

        return $result;
    }

    /**
     * @var Ret
     */
    protected static $currentRet;
}
