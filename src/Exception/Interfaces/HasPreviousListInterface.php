<?php

namespace Gzhegow\Lib\Exception\Interfaces;


interface HasPreviousListInterface
{
    /**
     * @return \Throwable[]
     */
    public function getPreviousList() : array;

    /**
     * @return static
     */
    public function addPrevious(\Throwable $e);
}
