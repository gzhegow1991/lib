<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ArrayOfA
{
    /**
     * @param string $className
     */
    public static function new($className) : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOfA($className)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfA($className);
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOfA)
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfA);
    }
}
