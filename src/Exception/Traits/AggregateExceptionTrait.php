<?php

namespace Gzhegow\Lib\Exception\Traits;

trait AggregateExceptionTrait
{
    /**
     * @var \Throwable[]
     */
    protected $previousList = [];


    /**
     * @return \Throwable[]
     */
    public function getPreviousList() : array
    {
        return $this->previousList;
    }
}
