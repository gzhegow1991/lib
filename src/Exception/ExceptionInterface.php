<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Interfaces\HasTraceOverrideInterface;


/**
 * @mixin \Throwable
 */
interface ExceptionInterface extends
    HasTraceOverrideInterface
{
}
