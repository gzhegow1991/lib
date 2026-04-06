<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Interfaces\HasMessageListInterface;
use Gzhegow\Lib\Exception\Interfaces\HasPreviousListInterface;
use Gzhegow\Lib\Exception\Interfaces\HasTraceOverrideInterface;
use Gzhegow\Lib\Exception\Interfaces\HasPreviousOverrideInterface;


/**
 * @mixin \Throwable
 */
interface ExceptionInterface extends
    \Throwable,
    //
    HasPreviousOverrideInterface,
    HasTraceOverrideInterface,
    //
    HasMessageListInterface,
    HasPreviousListInterface
{
}
