<?php

namespace Gzhegow\Lib\Exception\Interfaces;


use Gzhegow\Lib\Exception\ExceptInterface;


interface HasPreviousInterface
{
    public function hasPrevious() : bool;

    /**
     * @return null|\Throwable|ExceptInterface
     */
    public function getPrevious() : ?object;

    public function setPrevious(object $previous);
}
