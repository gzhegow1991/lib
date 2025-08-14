<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ArrayOf
{
    /**
     * @param string $valueType
     */
    public static function new($valueType) : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOf($valueType)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOf($valueType);
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOf)
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOf);
    }
}
