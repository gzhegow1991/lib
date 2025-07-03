<?php

namespace Gzhegow\Lib\Exception\Traits;

use Gzhegow\Lib\Exception\Interfaces\HasPreviousListInterface;


/**
 * @mixin \Throwable
 *
 * @mixin HasPreviousListInterface
 */
trait HasPreviousListTrait
{
    /**
     * @var \Throwable[]
     */
    protected $previousList = [];
    /**
     * @var array
     */
    protected $previousMessageList = [];


    /**
     * @return \Throwable[]
     */
    public function getPreviousList() : array
    {
        return $this->previousList;
    }

    /**
     * @return string[]
     */
    public function getPreviousMessageList() : array
    {
        return $this->previousMessageList;
    }
}
