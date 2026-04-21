<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Traits\HasMessageListTrait;
use Gzhegow\Lib\Exception\Traits\HasTraceOverrideTrait;
use Gzhegow\Lib\Exception\Traits\HasPreviousOverrideTrait;


/**
 * @mixin \Throwable
 */
trait ExceptionTrait
{
    use HasPreviousOverrideTrait;
    use HasTraceOverrideTrait;

    use HasMessageListTrait;


    public static function fromExcept(ExceptInterface $e)
    {
        $ex = new static(
            $e->getMessage(),
            $e->getCode(),
            //
            $e->getPreviousOverride(),
        );

        $ex->fileOverride = $e->getFileOverride() ?? $e->getFile();
        $ex->lineOverride = $e->getLineOverride() ?? $e->getLine();

        $ex->traceOverride = $e->getTraceOverride() ?? $e->getTrace();

        $ex->messageList = $e->getMessageList();
        $ex->messageObjectList = $e->getMessageObjectList();

        return $ex;
    }
}
