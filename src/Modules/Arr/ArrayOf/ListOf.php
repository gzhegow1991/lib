<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ListOf
{
    /**
     * @param string $valueType
     */
    public static function new($valueType)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ListOf($valueType)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ListOf($valueType);
    }
}
