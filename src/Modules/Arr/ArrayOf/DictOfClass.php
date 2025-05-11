<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class DictOfClass
{
    public static function new(string $className)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOfClass($className)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOfClass($className);
    }
}
