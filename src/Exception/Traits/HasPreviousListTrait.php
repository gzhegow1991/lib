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
     * @return \Throwable[]
     */
    public function getPreviousList() : array
    {
        return $this->previousList;
    }

    /**
     * @return static
     */
    public function addPrevious(\Throwable $e)
    {
        $this->previousList[] = $e;

        return $this;
    }
}
