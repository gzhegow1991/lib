<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Arr\Map;


class Map
{
    public static function new() : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\Map\PHP8\Map()
            : new \Gzhegow\Lib\Modules\Arr\Map\PHP7\Map();
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\Map\PHP8\Map)
            || ($instance instanceof \Gzhegow\Lib\Modules\Arr\Map\PHP7\Map);
    }
}
