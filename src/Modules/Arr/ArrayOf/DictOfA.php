<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class DictOfA
{
    /**
     * @param string $className
     */
    public static function new($className) : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOfA($className)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOfA($className);
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOfA)
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOfA);
    }
}
