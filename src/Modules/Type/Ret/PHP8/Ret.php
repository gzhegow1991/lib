<?php

namespace Gzhegow\Lib\Modules\Type\Ret\PHP8;

use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Type\Ret as RetBase;


class Ret extends RetBase
{
    public function __set(string $name, $value) : void
    {
        throw new RuntimeException(
            [ 'Unable to set property `name` in the instance of: ' . static::class, $name ]
        );
    }

    public function __unset(string $name) : void
    {
        throw new RuntimeException(
            [ 'Unable to unset property `name` in the instance of: ' . static::class, $name ]
        );
    }
}
