<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ArrayOfSubclass
{
    public static function new(string $className)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOfSubclass($className)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfSubclass($className);
    }
}
