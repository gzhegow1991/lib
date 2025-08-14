<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Modules\Http\Session\SessionDisabler;

class SessionDisabler
{
    public static function new() : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Modules\Http\Session\SessionDisabler\PHP8\SessionDisabler()
            : new \Gzhegow\Lib\Modules\Http\Session\SessionDisabler\PHP7\SessionDisabler();
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Modules\Http\Session\SessionDisabler\PHP8\SessionDisabler)
            || ($instance instanceof \Gzhegow\Lib\Modules\Http\Session\SessionDisabler\PHP7\SessionDisabler);
    }
}
