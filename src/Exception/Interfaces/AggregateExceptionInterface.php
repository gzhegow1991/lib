<?php

namespace Gzhegow\Lib\Exception\Interfaces;

use Gzhegow\Lib\Exception\ExceptionInterface;


interface AggregateExceptionInterface extends ExceptionInterface
{
    /**
     * @return \Throwable[]
     */
    public function getPreviousList() : array;
}
