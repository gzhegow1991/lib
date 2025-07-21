<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Traits\HasPreviousListTrait;


/**
 * @mixin \Throwable
 */
trait AggregateExceptionTrait
{
    use HasPreviousListTrait;
}
