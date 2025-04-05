<?php

namespace Gzhegow\Lib\Modules\Type;

use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;


if (! defined('_TYPE_NIL')) define('_TYPE_NIL', '{N}');

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


    public static function is($value) : bool
    {
        if (_TYPE_NIL === $value) {
            return true;
        }

        if ($value instanceof self) {
            return true;
        }

        return false;
    }
}
