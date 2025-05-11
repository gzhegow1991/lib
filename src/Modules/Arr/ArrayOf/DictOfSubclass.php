<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class DictOfSubclass
{
    public static function new(string $className)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOfSubclass($className)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOfSubclass($className);
    }
}
