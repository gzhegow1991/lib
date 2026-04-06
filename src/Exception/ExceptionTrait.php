<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Traits\HasMessageListTrait;
use Gzhegow\Lib\Exception\Traits\HasPreviousListTrait;
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
    use HasPreviousListTrait;
}
