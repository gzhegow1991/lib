<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ListOfResourceType
{
    public static function new(string $type)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ListOfResourceType($type)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ListOfResourceType($type);
    }
}
