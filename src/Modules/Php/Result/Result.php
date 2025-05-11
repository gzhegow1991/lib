<?php

namespace Gzhegow\Lib\Modules\Php\Result;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class Result
{
    /**
     * @see static::typeBool()
     */
    public static function type(?ResultContext &$ref = null) : ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_BOOLEAN,
            ResultContext::MODE_THROW_OFF,
        );
    }

    /**
     * @see static::parseNull()
     */
    public static function parse(?ResultContext &$ref = null) : ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_VALUE,
            ResultContext::MODE_THROW_OFF,
        );
    }

    /**
     * @see static::ignoreNull()
     */
    public static function ignore(?ResultContext &$ref = null) : ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_NULL,
            ResultContext::MODE_THROW_OFF,
        );
    }


    public static function typeBool(?ResultContext &$ref = null) : ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_BOOLEAN,
            ResultContext::MODE_THROW_OFF,
        );
    }

    public static function typeThrow(?ResultContext &$ref = null) : ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_BOOLEAN,
            ResultContext::MODE_THROW_ON,
        );
    }


    public static function parseNull(?ResultContext &$ref = null) : ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_VALUE,
            ResultContext::MODE_THROW_OFF,
        );
    }

    public static function parseThrow(?ResultContext &$ref = null) : ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_VALUE,
            ResultContext::MODE_THROW_ON,
        );
    }


    public static function ignoreNull(?ResultContext &$ref = null) : ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_NULL,
            ResultContext::MODE_THROW_OFF,
        );
    }

    public static function ignoreThrow(?ResultContext &$ref = null) : ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_NULL,
            ResultContext::MODE_THROW_ON,
        );
    }


    /**
     * @param ResultContext $ctx
     * @param mixed         $value
     *
     * @return ResultContext|mixed|true
     */
    public static function ok($ctx, $value)
    {
        $ctx = $ctx ?? static::parseThrow();

        $ctx->ok($value);

        if (ResultContext::MODE_RETURN_VALUE === $ctx->modeReturn) {
            return $value;

        } elseif (ResultContext::MODE_RETURN_BOOLEAN === $ctx->modeReturn) {
            return true;

        } elseif (ResultContext::MODE_RETURN_NULL === $ctx->modeReturn) {
            return null;

        } elseif (ResultContext::MODE_RETURN_CONTEXT === $ctx->modeReturn) {
            return $ctx;
        }

        throw new RuntimeException([ 'Mode is unknown', $ctx ]);
    }

    /**
     * @param ResultContext $ctx
     * @param mixed         $error
     *
     * @return ResultContext|null|false
     */
    public static function err($ctx, $error, array $trace = [], array $tags = [])
    {
        $ctx = $ctx ?? static::parseThrow();

        $ctx->err($error, $tags, $trace);

        if (ResultContext::MODE_THROW_ON === $ctx->modeThrow) {
            throw new LogicException(...$ctx->errors());
        }

        if (ResultContext::MODE_RETURN_VALUE === $ctx->modeReturn) {
            return null;

        } elseif (ResultContext::MODE_RETURN_BOOLEAN === $ctx->modeReturn) {
            return false;

        } elseif (ResultContext::MODE_RETURN_NULL === $ctx->modeReturn) {
            return null;

        } elseif (ResultContext::MODE_RETURN_CONTEXT === $ctx->modeReturn) {
            return $ctx;
        }

        throw new RuntimeException([ 'Mode is unknown', $ctx ]);
    }
}
