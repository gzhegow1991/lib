<?php

namespace Gzhegow\Lib\Modules\Php\Result;

use Gzhegow\Lib\Lib;


class Result
{
    public static function manager() : ResultManagerInterface
    {
        return Lib::php()->resultManager();
    }


    public static function type(?ResultContext &$ref = null) : ?ResultContext
    {
        return static::manager()->type($ref);
    }

    public static function parse(?ResultContext &$ref = null) : ?ResultContext
    {
        return static::manager()->parse($ref);
    }

    public static function map(?ResultContext &$ref = null) : ?ResultContext
    {
        return static::manager()->map($ref);
    }


    public static function assertType(?ResultContext &$ref = null) : ?ResultContext
    {
        return static::manager()->assertType($ref);
    }

    public static function assertParse(?ResultContext &$ref = null) : ?ResultContext
    {
        return static::manager()->assertParse($ref);
    }


    /**
     * @param ResultContext $ctx
     * @param mixed         $value
     *
     * @return ResultContext|mixed|true
     */
    public static function ok($ctx, $value)
    {
        return static::manager()->ok($ctx, $value);
    }

    /**
     * @param ResultContext $ctx
     * @param mixed         $error
     *
     * @return ResultContext|null|false
     */
    public static function err($ctx, $error, array $trace = [], array $tags = [])
    {
        return static::manager()->err($ctx, $error, $trace, $tags);
    }
}
