<?php

namespace Gzhegow\Lib\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Traits\CanTraitConstruct;
use Gzhegow\Lib\Exception\RuntimeException;


abstract class AbstractGenericObject
{
    use CanTraitConstruct;


    public function __construct()
    {
        call_user_func_array([ $this, '__traitConstruct' ], func_get_args());
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
        $this->set($name, $value);
    }

    public function __unset($name)
    {
        $this->clear($name);
    }


    /**
     * @return static
     */
    public function toCamelCase() // : static
    {
        $instance = new static();

        $theStr = Lib::str();

        foreach ( Lib::php()->get_object_vars($this) as $key => $value ) {
            $keyCamelCase = $theStr->camel($key);

            $instance->set($keyCamelCase, $value);
        }

        return $instance;
    }

    /**
     * @return static
     */
    public function toSnakeCase() // : static
    {
        $instance = new static();

        $theStr = Lib::str();

        foreach ( Lib::php()->get_object_vars($this) as $key => $value ) {
            $keySnakeCase = $theStr->snake_lower($key);

            $instance->set($keySnakeCase, $value);
        }

        return $instance;
    }


    public function isset(string $name) : bool
    {
        $status = true;

        if (! empty(static::$__filters[ $k = __FUNCTION__ ][ $kk = static::class ])) {
            foreach ( static::$__filters[ $k ][ $kk ] as $fn ) {
                if (! ($status = $fn($name))) {
                    break;
                }
            }

        } else {
            $status = isset($this->{$name});
        }

        return $status;
    }

    public function exists(string $name) : bool
    {
        $status = true;

        if (! empty(static::$__filters[ $k = __FUNCTION__ ][ $kk = static::class ])) {
            foreach ( static::$__filters[ $k ][ $kk ] as $fn ) {
                if (! ($status = $fn($name))) {
                    break;
                }
            }

        } else {
            $status = property_exists($this, $name);
        }

        return $status;
    }


    public function has(string $name, &$result = null) : bool
    {
        $result = null;

        $status = true;

        if (! empty(static::$__filters[ $k = __FUNCTION__ ][ $kk = static::class ])) {
            foreach ( static::$__filters[ $k ][ $kk ] as $fn ) {
                if (! ($status = $fn($name))) {
                    break;
                }
            }

        } else {
            $status = $this->exists($name);
        }

        if ($status) {
            $result = $this->{$name};

            return true;
        }

        return false;
    }

    public function get(string $name, array $fallback = [])
    {
        $status = true;

        if (! empty(static::$__filters[ $k = __FUNCTION__ ][ $kk = static::class ])) {
            foreach ( static::$__filters[ $k ][ $kk ] as $fn ) {
                if (! ($status = $fn($name))) {
                    break;
                }
            }

        } else {
            $status = $this->exists($name);
        }

        if (! $status) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                'Missing property: ' . $name
            );
        }

        return $this->{$name};
    }


    public function set(string $name, $value) : void
    {
        $status = true;

        if (! empty(static::$__filters[ $k = __FUNCTION__ ][ $kk = static::class ])) {
            foreach ( static::$__filters[ $k ][ $kk ] as $fn ) {
                if (! ($status = $fn($name, $value))) {
                    break;
                }
            }
        }

        if (! $status) {
            throw new RuntimeException(
                'Unable to set()'
            );
        }

        if ('' === $name) {
            throw new LogicException(
                'Empty `name` is not supported'
            );
        }

        if (extension_loaded('ctype')) {
            if (! ctype_alpha($name[ 0 ])) {
                throw new LogicException(
                    'Keys have to start from letter: ' . $name
                );
            }

        } else {
            if (! preg_match('/[a-zA-Z]/', $name[ 0 ])) {
                throw new LogicException(
                    'Keys have to start from letter: ' . $name
                );
            }
        }

        $this->{$name} = $value;
    }

    public function unset(string $name) : void
    {
        $status = true;

        if (! empty(static::$__filters[ $k = __FUNCTION__ ][ $kk = static::class ])) {
            foreach ( static::$__filters[ $k ][ $kk ] as $fn ) {
                if (! ($status = $fn($name))) {
                    break;
                }
            }
        }

        if (! $status) {
            throw new RuntimeException(
                'Unable to unset()'
            );
        }

        unset($this->{$name});
    }

    public function clear(string $name) : void
    {
        $status = true;

        if (! empty(static::$__filters[ $k = __FUNCTION__ ][ $kk = static::class ])) {
            foreach ( static::$__filters[ $k ][ $kk ] as $fn ) {
                if (! ($status = $fn($name))) {
                    break;
                }
            }
        }

        if (! $status) {
            throw new RuntimeException(
                'Unable to clear()'
            );
        }

        $this->{$name} = null;
    }


    public function keys() : array
    {
        $vars = Lib::php()->get_object_vars($this);

        $keys = array_keys($vars);

        return $keys;
    }

    public function values() : array
    {
        $vars = Lib::php()->get_object_vars($this);

        $values = array_values($vars);

        return $values;
    }

    public function entries() : array
    {
        $entries = [];

        foreach ( Lib::php()->get_object_vars($this) as $key => $value ) {
            $entries[] = [ $key, $value ];
        }

        return $entries;
    }


    public function reset(array $data = null) : void
    {
        $this->unfill();

        if (null !== $data) {
            $this->replace($data);
        }
    }

    public function replace(array $data) : void
    {
        foreach ( $data as $key => $value ) {
            $this->set($key, $value);
        }
    }

    public function fill(array $data) : void
    {
        foreach ( $data as $key => $value ) {
            if ($this->exists($key)) {
                $this->set($key, $value);
            }
        }
    }

    public function unfill(array $keys = null) : void
    {
        $keys = $keys ?? $this->keys();

        foreach ( $keys as $key ) {
            $this->clear($key);
        }
    }

    public function append(array $data) : void
    {
        foreach ( $data as $key => $value ) {
            if (! $this->isset($key)) {
                $this->set($key, $value);
            }
        }
    }


    /**
     * @param callable $fn
     */
    protected static function __filter(string $type, $fn) : void
    {
        if (! isset(static::$__filters[ $type ])) {
            throw new LogicException(
                'Missing filter: ' . $type
            );
        }

        static::$__filters[ $type ][ static::class ][] = $fn;
    }

    protected static $__filters = [
        'isset'  => [],
        'exists' => [],
        'has'    => [],
        'get'    => [],
        'set'    => [],
        'unset'  => [],
        'clear'  => [],
    ];
}
