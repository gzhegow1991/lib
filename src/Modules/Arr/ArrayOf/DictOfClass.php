<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class DictOfClass
{
    /**
     * @param string $className
     */
    public static function new($className) : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOfClass($className)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOfClass($className);
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOfClass)
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOfClass);
    }
}
