<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class DictOf
{
    /**
     * @param string $valueType
     */
    public static function new($valueType)
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOf($valueType)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOf($valueType);
    }
}
