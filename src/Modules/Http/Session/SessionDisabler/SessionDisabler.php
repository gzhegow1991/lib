<?php

namespace Gzhegow\Lib\Modules\Http\Session\SessionDisabler;

class SessionDisabler
{
    public static function new()
    {
        return (PHP_VERSION_ID >= 80000)
            ? new PHP8\SessionDisabler()
            : new PHP7\SessionDisabler();
    }
}
