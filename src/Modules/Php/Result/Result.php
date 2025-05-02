<?php

namespace Gzhegow\Lib\Modules\Php\Result;

use Gzhegow\Lib\Lib;


class Result
{
    public static function type(?ResultContext &$ref = null) : ?ResultContext
    {
        return static::getInstance()->type($ref);
    }

    public static function parse(?ResultContext &$ref = null) : ?ResultContext
    {
        return static::getInstance()->parse($ref);
    }


    public static function assert(?ResultContext &$ref = null) : ?ResultContext
    {
        return static::getInstance()->assert($ref);
    }


    public static function chainType(?ResultContext &$ref = null) : ?ResultContext
    {
        return static::getInstance()->chainType($ref);
    }

    public static function chainParse(?ResultContext &$ref = null) : ?ResultContext
    {
        return static::getInstance()->chainParse($ref);
    }


    /**
     * @param ResultContext $ctx
     * @param mixed         $value
     *
     * @return ResultContext|mixed|true
     */
    public static function ok($ctx, $value)
    {
        return static::getInstance()->ok($ctx, $value);
    }

    /**
     * @param ResultContext $ctx
     * @param mixed         $error
     *
     * @return ResultContext|null|false
     */
    public static function err($ctx, $error, array $trace = [], array $tags = [])
    {
        return static::getInstance()->err($ctx, $error, $trace, $tags);
    }


    public static function getInstance() : ResultManagerInterface
    {
        return Lib::php()->resultManager();
    }
}
