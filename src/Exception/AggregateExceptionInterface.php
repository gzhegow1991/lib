<?php

namespace Gzhegow\Lib\Exception;

interface AggregateExceptionInterface extends ExceptionInterface
{
    /**
     * @return \Throwable[]
     */
    public function getPreviousList() : array;
}
