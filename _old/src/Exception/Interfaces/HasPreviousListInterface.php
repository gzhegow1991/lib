<?php

namespace Gzhegow\Lib\Exception\Interfaces;


interface HasPreviousListInterface
{
    /**
     * @return \Throwable[]
     */
    public function getPreviousList() : array;
}
