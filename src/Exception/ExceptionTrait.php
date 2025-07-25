<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Traits\HasMessageListTrait;
use Gzhegow\Lib\Exception\Traits\HasTraceOverrideTrait;


/**
 * @mixin \Throwable
 */
trait ExceptionTrait
{
    use AggregateExceptionTrait;

    use HasMessageListTrait;
    use HasTraceOverrideTrait;
}
