<?php

namespace Gzhegow\Lib\Modules\Http\Session;

class SessionDisabler
{
    public static function new()
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Http\Session\PHP8\SessionDisabler()
            : new \Gzhegow\Lib\Modules\Http\Session\PHP7\SessionDisabler();
    }
}
