<?php

namespace Gzhegow\Lib\Exception\Interfaces;


interface HasPreviousListInterface
{
    /**
     * @return \Throwable[]
     */
    public function getPreviousList() : array;

    /**
     * @return string[]
     */
    public function getPreviousMessageList() : array;
}
