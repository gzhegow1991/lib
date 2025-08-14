<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ArrayOfType
{
    /**
     * @param string $valueType
     */
    public static function new($valueType) : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOfType($valueType)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfType($valueType);
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOfType)
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfType);
    }
}
