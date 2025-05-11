<?php

namespace Gzhegow\Lib\Context;

class GenericContext
{
    public static function new()
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Context\PHP8\GenericContext()
            : new \Gzhegow\Lib\Context\PHP7\GenericContext();
    }
}
