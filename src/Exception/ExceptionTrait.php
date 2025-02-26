<?php

namespace Gzhegow\Lib\Exception;


use Gzhegow\Lib\Exception\Traits\HasTraceOverrideTrait;


/**
 * @mixin \Throwable
 */
trait ExceptionTrait
{
    use HasTraceOverrideTrait;
}
