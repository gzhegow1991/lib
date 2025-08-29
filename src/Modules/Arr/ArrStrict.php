<?php

namespace Gzhegow\Lib\Modules\Arr;

use Gzhegow\Lib\Modules\Type\Ret;
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
     * @return static|Ret<static>
     */
    public static function fromValid($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromValidArray($from)->orNull($ret);

        if ( $ret->isFail() ) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ( $from instanceof static ) {
            return Ret::ok($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromValidArray($from, ?array $fallback = null)
    {
        if ( is_array($from) ) {
            $instance = new static();
            $instance->array = $from;

            return Ret::ok($fallback, $instance);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an array', $from ],
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


    public function exists($key, &$refValue = null) : bool
    {
        $refValue = null;

        if ( array_key_exists($key, $this->array) ) {
            if ( is_array($this->array[$key]) ) {
                $refValue = static::fromValidArray($this->array[$key])->orThrow();

            } else {
                $refValue = $this->array[$key];
            }

            return true;
        }

        return false;
    }

    public function isset($key, &$refValue = null) : bool
    {
        $refValue = null;

        if ( isset($this->array[$key]) ) {
            if ( is_array($this->array[$key]) ) {
                $refValue = static::fromValidArray($this->array[$key])->orThrow();

            } else {
                $refValue = $this->array[$key];
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
        if ( array_key_exists($key, $this->array) ) {
            return $this->array[$key];
        }

        if ( [] !== $fallback ) {
            return $fallback[0];
        }

        throw new RuntimeException(
            [ 'The `key` is missing in array', $key ]
        );
    }
}
