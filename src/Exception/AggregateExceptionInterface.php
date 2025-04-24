<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Interfaces\HasPreviousListInterface;


interface AggregateExceptionInterface extends
    ExceptionInterface,
    //
    HasPreviousListInterface
{
}
