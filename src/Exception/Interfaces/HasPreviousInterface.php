<?php

namespace Gzhegow\Lib\Exception\Interfaces;

use Gzhegow\Lib\Exception\ExceptInterface;


interface HasPreviousInterface
{
    /**
     * @return null|\Throwable|ExceptInterface
     */
    public function getPrevious() : ?object;
}
