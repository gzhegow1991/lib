<?php

namespace Gzhegow\Lib\Modules\Type\Ret\PHP7;

use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Type\Ret as RetBase;


class Ret extends RetBase implements \ArrayAccess
{
    public function __construct()
    {
    }


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


    public function offsetExists($offset)
    {
        if (! in_array($offset, [ 0, 1, 2 ], true)) {
            return false;
        }

        if (0 === $offset) {
            return true;

        } elseif (1 === $offset) {
            return [] !== $this->value;

        } elseif (2 === $offset) {
            return [] != $this->errors;

        } else {
            return false;
        }
    }

    public function offsetGet($offset)
    {
        if (0 === $offset) {
            return $this->getStatus();

        } elseif (1 === $offset) {
            return ([] === $this->value) ? null : $this->value[ 0 ];

        } elseif (2 === $offset) {
            return $this->errors;

        } else {
            throw new RuntimeException(
                [ 'Unable to get `offset` from the instance of: ' . static::class, $offset ]
            );
        }
    }

    public function offsetSet($offset, $value)
    {
        throw new RuntimeException(
            [ 'Unable to set `offset` in the instance of: ' . static::class, $offset ]
        );
    }

    public function offsetUnset($offset)
    {
        throw new RuntimeException(
            [ 'Unable to unset `offset` from the instance of: ' . static::class, $offset ]
        );
    }
}
