<?php

namespace Gzhegow\Lib\Modules\Type;

use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;


final class Nil implements ToStringInterface
{
    public function __toString() : string
    {
        return _TYPE_NIL;
    }

    public function toString(array $options = []) : string
    {
        return _TYPE_NIL;
    }
}
