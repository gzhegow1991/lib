<?php

namespace Gzhegow\Lib\Modules\Php\Interfaces;

interface ToObjectInterface
{
    public function toObject(array $options = []) : \stdClass;
}
