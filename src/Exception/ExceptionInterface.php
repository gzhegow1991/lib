<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Interfaces\HasTraceOverrideInterface;


interface ExceptionInterface extends
    \Throwable,
    //
    HasTraceOverrideInterface
{
}
