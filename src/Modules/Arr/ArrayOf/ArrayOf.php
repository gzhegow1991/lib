<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ArrayOf
{
    public static function new(string $valueType)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOf($valueType)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOf($valueType);
    }
}
