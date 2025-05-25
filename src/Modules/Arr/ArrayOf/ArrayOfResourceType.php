<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ArrayOfResourceType
{
    /**
     * @param string $type
     */
    public static function new($type)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOfResourceType($type)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfResourceType($type);
    }
}
