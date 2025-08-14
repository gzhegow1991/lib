<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class DictOfResourceType
{
    /**
     * @param string $type
     */
    public static function new($type) : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOfResourceType($type)
            : new \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOfResourceType($type);
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\DictOfResourceType)
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\DictOfResourceType);
    }
}
