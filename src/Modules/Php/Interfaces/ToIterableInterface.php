<?php

namespace Gzhegow\Lib\Modules\Php\Interfaces;

interface ToIterableInterface
{
    public function toIterable(array $options = []) : iterable;
}
