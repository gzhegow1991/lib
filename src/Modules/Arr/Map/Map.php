<?php

namespace Gzhegow\Lib\Modules\Arr\Map;


class Map
{
    public static function new()
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Arr\Map\PHP8\Map()
            : new \Gzhegow\Lib\Modules\Arr\Map\PHP7\Map();
    }
}
