<?php

namespace Gzhegow\Lib\Modules\Php\Result;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class Result
{
    /**
     * @see static::typeBool()
     */
    public static function type(?Res &$ref = null) : Res
    {
        return $ref = Res::fromMode(
            Res::MODE_RETURN_BOOLEAN,
            Res::MODE_THROW_OFF,
        );
    }

    /**
     * @see static::parseNull()
     */
    public static function parse(?Res &$ref = null) : Res
    {
        return $ref = Res::fromMode(
            Res::MODE_RETURN_VALUE,
            Res::MODE_THROW_OFF,
        );
    }

    /**
     * @see static::ignoreNull()
     */
    public static function ignore(?Res &$ref = null) : Res
    {
        return $ref = Res::fromMode(
            Res::MODE_RETURN_NULL,
            Res::MODE_THROW_OFF,
        );
    }


    public static function typeBool(?Res &$ref = null) : Res
    {
        return $ref = Res::fromMode(
            Res::MODE_RETURN_BOOLEAN,
            Res::MODE_THROW_OFF,
        );
    }

    public static function typeThrow(?Res &$ref = null) : Res
    {
        return $ref = Res::fromMode(
            Res::MODE_RETURN_BOOLEAN,
            Res::MODE_THROW_ON,
        );
    }


    public static function parseNull(?Res &$ref = null) : Res
    {
        return $ref = Res::fromMode(
            Res::MODE_RETURN_VALUE,
            Res::MODE_THROW_OFF,
        );
    }

    public static function parseThrow(?Res &$ref = null) : Res
    {
        return $ref = Res::fromMode(
            Res::MODE_RETURN_VALUE,
            Res::MODE_THROW_ON,
        );
    }


    public static function ignoreNull(?Res &$ref = null) : Res
    {
        return $ref = Res::fromMode(
            Res::MODE_RETURN_NULL,
            Res::MODE_THROW_OFF,
        );
    }

    public static function ignoreThrow(?Res &$ref = null) : Res
    {
        return $ref = Res::fromMode(
            Res::MODE_RETURN_NULL,
            Res::MODE_THROW_ON,
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

        $res->ok($value);

        if (Res::MODE_RETURN_VALUE === $res->modeReturn) {
            return $value;

        } elseif (Res::MODE_RETURN_BOOLEAN === $res->modeReturn) {
            return true;

        } elseif (Res::MODE_RETURN_NULL === $res->modeReturn) {
            return null;

        } elseif (Res::MODE_RETURN_CONTEXT === $res->modeReturn) {
            return $res;
        }

        throw new RuntimeException([ 'Mode is unknown', $res ]);
    }

    /**
     * @param Res   $res
     * @param mixed $error
     *
     * @return Res|null|false
     */
    public static function err($res, $error, array $trace = [], array $tags = [])
    {
        $res = $res ?? static::parseThrow();

        $res->err($error, $tags, $trace);

        if (Res::MODE_THROW_ON === $res->modeThrow) {
            throw new LogicException(...$res->errors());
        }

        if (Res::MODE_RETURN_VALUE === $res->modeReturn) {
            return null;

        } elseif (Res::MODE_RETURN_BOOLEAN === $res->modeReturn) {
            return false;

        } elseif (Res::MODE_RETURN_NULL === $res->modeReturn) {
            return null;

        } elseif (Res::MODE_RETURN_CONTEXT === $res->modeReturn) {
            return $res;
        }

        throw new RuntimeException([ 'Mode is unknown', $res ]);
    }
}
