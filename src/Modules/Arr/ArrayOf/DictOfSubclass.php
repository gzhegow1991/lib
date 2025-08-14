<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class DictOfSubclass
{
    /**
     * @param string $className
     */
    public static function new($className) : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOfSubclass($className)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOfSubclass($className);
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOfSubclass)
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOfSubclass);
    }
}
