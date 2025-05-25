<?php

namespace Gzhegow\Lib\Modules\Arr;

use Gzhegow\Lib\Modules\Php\Result\Ret;
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
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromValid($from, $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? static::fromStatic($from, $retCur)
            ?? static::fromValidArray($from, $retCur);

        if ($retCur->isErr()) {
            return Result::err($ret, $retCur);
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromStatic($from, $ret = null)
    {
        if ($from instanceof static) {
            return Result::ok($ret, $from);
        }

        return Result::err(
            $ret,
            [ 'The `from` must be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromValidArray($from, $ret = null)
    {
        if (is_array($from)) {
            $instance = new static();
            $instance->array = $from;

            return Result::ok($ret, $instance);
        }

        return Result::err(
            $ret,
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


    public function exists($key, &$refValue = null) : bool
    {
        $refValue = null;

        if (array_key_exists($key, $this->array)) {
            if (is_array($this->array[ $key ])) {
                $refValue = static::fromValidArray($this->array[ $key ]);

            } else {
                $refValue = $this->array[ $key ];
            }

            return true;
        }

        return false;
    }

    public function isset($key, &$refValue = null) : bool
    {
        $refValue = null;

        if (isset($this->array[ $key ])) {
            if (is_array($this->array[ $key ])) {
                $refValue = static::fromValidArray($this->array[ $key ]);

            } else {
                $refValue = $this->array[ $key ];
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
