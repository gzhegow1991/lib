<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ArrayOfSubclass
{
    /**
     * @param string $className
     */
    public static function new($className)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOfSubclass($className)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfSubclass($className);
    }
}
