<?php

namespace Gzhegow\Lib\Modules\Php\Interfaces;

interface CanIsSameInterface
{
    /**
     * @param static $object
     */
    public function isSame($object, array $options = []) : bool;
}
