<?php

namespace Gzhegow\Lib\Modules\Arr;

use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;


class ArrStrict implements
    ToArrayInterface
{
    /**
     * @var array
     */
    protected $array = [];


    private function __construct()
    {
    }


    /**
     * @return static|bool|null
     */
    public static function fromValid($from, $ctx = null)
    {
        $ctxCur = Result::nullchain();

        $instance = null
            ?? static::fromStatic($from, $ctxCur)
            ?? static::fromValidArray($from, $ctxCur);

        if ($ctxCur->isErr()) {
            return Result::err($ctx, $ctxCur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, $ctx = null)
    {
        if ($from instanceof static) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` must be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromValidArray($from, $ctx = null)
    {
        if (is_array($from)) {
            $instance = new static();
            $instance->path = $from;

            return Result::ok($ctx, $instance);
        }

        return Result::err(
            $ctx,
            [ 'The `from` must be array', $from ],
            [ __FILE__, __LINE__ ]
        );
    }


    public function __isset($name)
    {
        return $this->exists($name);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        throw new RuntimeException('This value object cannot be used to modify array');
    }

    public function __unset($name)
    {
        throw new RuntimeException('This value object cannot be used to modify array');
    }


    public function toArray(array $options = []) : array
    {
        return $this->array;
    }


    public function exists($key, &$value = null) : bool
    {
        $value = null;

        if (array_key_exists($key, $this->array)) {
            if (is_array($this->array[ $key ])) {
                $value = static::fromValidArray($this->array[ $key ]);

            } else {
                $value = $this->array[ $key ];
            }

            return true;
        }

        return false;
    }

    public function isset($key, &$value = null) : bool
    {
        $value = null;

        if (isset($this->array[ $key ])) {
            if (is_array($this->array[ $key ])) {
                $value = static::fromValidArray($this->array[ $key ]);

            } else {
                $value = $this->array[ $key ];
            }

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function get($key, array $fallback = [])
    {
        if (array_key_exists($key, $this->array)) {
            return $this->array[ $key ];
        }

        if ([] !== $fallback) {
            return $fallback[ 0 ];
        }

        throw new RuntimeException(
            [ 'The `key` is missing in array', $key ]
        );
    }
}
