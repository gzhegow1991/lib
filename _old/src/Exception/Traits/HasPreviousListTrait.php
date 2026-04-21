<?php

namespace Gzhegow\Lib\Exception\Traits;

use Gzhegow\Lib\Exception\ExceptionInterface;


/**
 * @see HasPreviousListInterface
 */
trait HasPreviousListTrait
{
    /**
     * @var (\Throwable|ExceptionInterface)[]
     */
    protected $previousList = [];


    /**
     * @return (\Throwable|ExceptionInterface)[]
     */
    public function getPreviousList() : array
    {
        return $this->previousList;
    }
}
