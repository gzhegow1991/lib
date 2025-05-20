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
    public static function type(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_TRUE,
            Res::MODE_ERROR_FALSE,
        );
    }

    public static function typeBool(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_TRUE,
            Res::MODE_ERROR_FALSE,
        );
    }

    public static function typeThrow(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_TRUE,
            Res::MODE_ERROR_THROW,
        );
    }


    /**
     * @see static::parseNull()
     * @see static::asValue()
     */
    public static function parse(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_VALUE,
            Res::MODE_ERROR_NULL,
        );
    }

    public static function parseNull(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_VALUE,
            Res::MODE_ERROR_NULL,
        );
    }

    /**
     * @see static::asStrict()
     */
    public static function parseThrow(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_VALUE,
            Res::MODE_ERROR_THROW,
        );
    }


    public static function ignore(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_NULL,
            Res::MODE_ERROR_NULL,
        );
    }

    public static function ignoreThrow(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_NULL,
            Res::MODE_ERROR_THROW,
        );
    }


    public static function asBool(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_TRUE,
            Res::MODE_ERROR_FALSE,
        );
    }

    public static function asValue(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_VALUE,
            Res::MODE_ERROR_NULL,
        );
    }

    public static function asObject(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_SELF,
            Res::MODE_ERROR_SELF,
        );
    }

    public static function asStrict(?Res &$res = null) : Res
    {
        return $res = Res::fromMode(
            Res::MODE_RESULT_VALUE,
            Res::MODE_ERROR_THROW,
        );
    }


    /**
     * @param Res   $res
     * @param mixed $value
     *
     * @return Res|mixed|true
     */
    public static function ok($res, $value)
    {
        $res = $res ?? static::parseThrow();

        $res->setResult($value);

        // if (false) {
        // } elseif (Res::MODE_RESULT_NULL === $res->modeResult) {
        if (Res::MODE_RESULT_NULL === $res->modeResult) {
            return null;

        } elseif (Res::MODE_RESULT_SELF === $res->modeResult) {
            return $res;

        } elseif (Res::MODE_RESULT_TRUE === $res->modeResult) {
            return true;

        } elseif (Res::MODE_RESULT_VALUE === $res->modeResult) {
            return $value;
        }

        throw new RuntimeException([ 'Mode `modeResult` is unknown', $res ]);
    }

    /**
     * @template T
     *
     * @param T $value
     *
     * @return Res<T>
     */
    public static function resOk($value)
    {
        $res = static::asObject();

        return static::ok($res, $value);
    }


    /**
     * @param Res|string $res
     * @param mixed      $error
     *
     * @return Res|null|false
     */
    public static function err($res, $error, array $trace = [], array $tags = [])
    {
        $res = $res ?? static::parseThrow();

        $res->addError($error, $tags, $trace);

        if (Res::MODE_ERROR_FALSE === $res->modeError) {
            return false;

        } elseif (Res::MODE_ERROR_NULL === $res->modeError) {
            return null;

        } elseif (Res::MODE_ERROR_SELF === $res->modeError) {
            return $res;

        } elseif (Res::MODE_ERROR_THROW === $res->modeError) {
            throw new LogicException(...$res->errors());
        }

        throw new RuntimeException([ 'Mode `modeError` is unknown', $res ]);
    }

    /**
     * @param mixed $error
     *
     * @return Res
     */
    public static function resErr($error, array $trace = [], array $tags = [])
    {
        $res = static::asObject();

        return static::err($res, $error, $trace, $tags);
    }


    /**
     * @param Res $res
     */
    public static function isOk($res, &$result = null) : bool
    {
        return $res->isOk($result);
    }

    /**
     * @param Res $res
     */
    public static function isErr($res, &$errors = null) : bool
    {
        return $res->isErr($errors);
    }


    /**
     * @param array{ 0?: bool } $fallback
     * @param mixed             $result
     */
    public static function bool(Res $res, array $fallback = [], &$result = null) : bool
    {
        if ($res->isOk($result)) {
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
     * @param Error[]            $errors
     *
     * @return mixed|null
     */
    public static function val(Res $res, array $fallback = [], &$errors = null)
    {
        if ($res->isOk($result)) {
            return $result;
        }

        if ($res->isErr($errors)) {
            if ([] !== $fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }
        }

        return null;
    }
}
