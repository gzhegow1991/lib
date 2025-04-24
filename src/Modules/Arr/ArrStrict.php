<?php

namespace Gzhegow\Lib\Modules\Arr;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
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
    public static function fromValid($from, array $refs = [])
    {
        $withErrors = array_key_exists(0, $refs);

        $refs[ 0 ] = $refs[ 0 ] ?? null;

        $instance = null
            ?? static::fromStatic($from, $refs)
            ?? static::fromValidArray($from, $refs);

        if (! $withErrors) {
            if (null === $instance) {
                throw $refs[ 0 ];
            }
        }

        return $instance;
    }

    /**
     * @return static|null|bool
     */
    public static function fromStatic($from, array $refs = [])
    {
        if ($from instanceof static) {
            return Lib::refsResult($refs, $from);
        }

        return Lib::refsError(
            $refs,
            new LogicException(
                [ 'The `from` must be instance of: ' . static::class, $from ]
            )
        );
    }

    /**
     * @return static|null|bool
     */
    public static function fromValidArray($from, array $refs = [])
    {
        if (is_array($from)) {
            $instance = new static();
            $instance->array = $from;

            return Lib::refsResult($refs, $instance);
        }

        return Lib::refsError(
            $refs,
            new LogicException(
                [ 'The `from` must be array', $from ]
            )
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
    public function get($key, array $fallback = []) // : mixed
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
