<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ListOfClass
{
    public static function new(string $className)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ListOfClass($className)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ListOfClass($className);
    }
}
