<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Interfaces\HasMessageListInterface;
use Gzhegow\Lib\Exception\Interfaces\HasTraceOverrideInterface;


/**
 * @mixin \Throwable
 */
interface ExceptionInterface extends
    AggregateExceptionInterface,
    //
    HasMessageListInterface,
    HasTraceOverrideInterface
{
}
