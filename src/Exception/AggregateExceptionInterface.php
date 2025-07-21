<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Interfaces\HasPreviousListInterface;


/**
 * @mixin \Throwable
 */
interface AggregateExceptionInterface extends
    HasPreviousListInterface
{
}
