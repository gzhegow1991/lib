<?php

/**
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */

namespace Gzhegow\Lib\Context;


class GenericContext
{
    public static function new() : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Context\PHP8\GenericContext()
            : new \Gzhegow\Lib\Context\PHP7\GenericContext();
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Context\PHP8\GenericContext)
            || ($instance instanceof \Gzhegow\Lib\Context\PHP7\GenericContext);
    }
}
