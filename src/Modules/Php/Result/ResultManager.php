<?php

namespace Gzhegow\Lib\Modules\Php\Result;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class ResultManager implements ResultManagerInterface
{
    public function type(?ResultContext &$ref = null) : ?ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_BOOLEAN,
            ResultContext::MODE_THROW_OFF,
        );
    }

    public function parse(?ResultContext &$ref = null) : ?ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_VALUE,
            ResultContext::MODE_THROW_OFF,
        );
    }


    public function assert(?ResultContext &$ref = null) : ?ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_VALUE,
            ResultContext::MODE_THROW_ON,
        );
    }


    public function chainType(?ResultContext &$ref = null) : ?ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_BOOLEAN,
            ResultContext::MODE_THROW_ON,
        );
    }

    public function chainParse(?ResultContext &$ref = null) : ?ResultContext
    {
        return $ref = ResultContext::fromMode(
            ResultContext::MODE_RETURN_NULL,
            ResultContext::MODE_THROW_ON,
        );
    }


    /**
     * @param mixed $value
     *
     * @return ResultContext|mixed|true
     */
    public function ok(?ResultContext $ctx, $value)
    {
        $ctx = $ctx ?? $this->assert();

        $ctx->ok($value);

        if ($ctx->isModeReturn(ResultContext::MODE_RETURN_VALUE)) {
            return $value;

        } elseif ($ctx->isModeReturn(ResultContext::MODE_RETURN_BOOLEAN)) {
            return true;

        } elseif ($ctx->isModeReturn(ResultContext::MODE_RETURN_NULL)) {
            return null;

        } elseif ($ctx->isModeReturn(ResultContext::MODE_RETURN_CONTEXT)) {
            return $ctx;
        }

        throw new RuntimeException([ 'Mode is unknown', $ctx ]);
    }

    /**
     * @param mixed $error
     *
     * @return ResultContext|null|false
     */
    public function err(?ResultContext $ctx, $error, array $trace = [], array $tags = [])
    {
        $ctx = $ctx ?? $this->assert();

        $ctx->err($error, $tags, $trace);

        if ($ctx->isModeThrow(ResultContext::MODE_THROW_ON)) {
            throw new LogicException(...$ctx->errors());
        }

        if ($ctx->isModeReturn(ResultContext::MODE_RETURN_VALUE)) {
            return null;

        } elseif ($ctx->isModeReturn(ResultContext::MODE_RETURN_BOOLEAN)) {
            return false;

        } elseif ($ctx->isModeReturn(ResultContext::MODE_RETURN_NULL)) {
            return null;

        } elseif ($ctx->isModeReturn(ResultContext::MODE_RETURN_CONTEXT)) {
            return $ctx;
        }

        throw new RuntimeException([ 'Mode is unknown', $ctx ]);
    }
}
