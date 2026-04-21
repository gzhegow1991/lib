<?php

namespace Gzhegow\Lib\Exception\Interfaces;

use Gzhegow\Lib\Exception\ExceptInterface;


interface HasPreviousOverrideInterface
{
    public function hasPreviousOverride() : bool;

    /**
     * @return \Throwable|ExceptInterface
     */
    public function getPreviousOverride() : object;

    public function setPreviousOverride(?object $previous);
}
