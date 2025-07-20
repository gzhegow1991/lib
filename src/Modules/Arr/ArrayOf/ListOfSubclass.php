<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class ListOfSubclass
{
    /**
     * @param string $className
     */
    public static function new($className)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ListOfSubclass($className)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ListOfSubclass($className);
    }
}
