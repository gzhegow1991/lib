<?php

namespace Gzhegow\Lib\Exception\Interfaces;


interface HasMessageListInterface
{
    /**
     * @return string[]
     */
    public function getMessageList() : array;

    /**
     * @return object[]
     */
    public function getMessageObjectList() : array;
}
