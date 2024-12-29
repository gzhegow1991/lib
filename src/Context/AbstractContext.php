<?php

namespace Gzhegow\Lib\Context;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Traits\CanTraitBoot;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


abstract class AbstractContext
{
    use CanTraitBoot;


    public function __construct()
    {
        call_user_func_array([ static::class, '__traitBoot' ], func_get_args());
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

        $bool = $this->__tryExecuteFilters(__FUNCTION__, [ $name ]);

        if (null === $bool) {
            return isset($this->{$name});
        }

        return $bool;
    }

    public function exists(string $name) : bool
    {
        $status = true;

        $bool = $this->__tryExecuteFilters(__FUNCTION__, [ $name ]);

        if (null === $bool) {
            return property_exists($this, $name);
        }

        return $bool;
    }


    public function has(string $name, &$result = null) : bool
    {
        $result = null;

        $status = true;

        $bool = $this->__tryExecuteFilters(__FUNCTION__, [ $name ]);

        if (null === $bool) {
            $status = $this->exists($name);

        } else {
            $status = $bool;
        }

        if ($status) {
            $result = $this->{$name} ?? null;

            return true;
        }

        return false;
    }

    public function get(string $name, array $fallback = [])
    {
        $status = true;

        $bool = $this->__tryExecuteFilters(__FUNCTION__, [ $name ], $error);

        if (null === $bool) {
            $status = $this->exists($name);

        } else {
            $status = $bool;
        }

        if (! $status) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            if ($error) {
                throw new RuntimeException($error);

            } else {
                throw new RuntimeException(
                    'Missing property: ' . $name
                );
            }
        }

        return $this->{$name} ?? null;
    }


    public function set(string $name, $value) : void
    {
        $status = true;

        $this->__executeFilters(__FUNCTION__, [ $name ]);

        if ('' === $name) {
            throw new LogicException(
                'Empty `name` is not supported'
            );
        }

        // if (extension_loaded('ctype')) {
        //     if (! ctype_alpha($name[ 0 ])) {
        //         throw new LogicException(
        //             'Keys have to start from letter: ' . $name
        //         );
        //     }
        //
        // } else {
        //     if (! preg_match('/[a-zA-Z]/', $name[ 0 ])) {
        //         throw new LogicException(
        //             'Keys have to start from letter: ' . $name
        //         );
        //     }
        // }

        $this->{$name} = $value;
    }

    public function unset(string $name) : void
    {
        $status = true;

        $this->__executeFilters(__FUNCTION__, [ $name ]);

        unset($this->{$name});
    }

    public function clear(string $name) : void
    {
        $status = true;

        $this->__executeFilters(__FUNCTION__, [ $name ]);

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
     * @param callable-string $fn
     */
    protected static function __addFilter(string $filterType, string $fn) : void
    {
        if (! isset(static::$__filters[ $filterType ])) {
            throw new LogicException(
                'Unknown `filterType`: ' . $filterType
            );
        }

        if (! isset(static::$__filters[ $filterType ][ static::class ][ $fn ])) {
            static::$__filters[ $filterType ][ static::class ][ $fn ] = true;
        }
    }

    private function __executeFilters(string $filterType, array $arguments) : ?bool
    {
        if (empty(static::$__filters[ $filterType ][ $filterClass = static::class ])) {
            return null;
        }

        $fnList = static::$__filters[ $filterType ][ $filterClass ];

        $args = $arguments;
        array_unshift($args, $this);

        foreach ( $fnList as $fn => $bool ) {
            if (! ($status = call_user_func_array($fn, $args))) {
                $filterName = $fn;
                if (0 === strpos($fn, 'class@anonymous')) {
                    $filterName = explode('::', $fn)[ 1 ];
                }

                throw new RuntimeException(
                    [ "Unable to ->{$filterType}() due to failed filter: {$filterName}", $fn, $args ]
                );
            }
        }

        return true;
    }

    private function __tryExecuteFilters(string $filterType, array $arguments, &$error = null) : ?bool
    {
        if (empty(static::$__filters[ $filterType ][ $filterClass = static::class ])) {
            return null;
        }

        $fnList = static::$__filters[ $filterType ][ $filterClass ];

        $args = $arguments;
        array_unshift($args, $this);

        foreach ( $fnList as $fn => $bool ) {
            if (! ($status = call_user_func_array($fn, $args))) {
                $filterName = $fn;
                if (0 === strpos($fn, 'class@anonymous')) {
                    $filterName = explode('::', $fn)[ 1 ];
                }

                $error = [ "Unable to ->{$filterType}() due to failed filter: {$filterName}", $fn, $args ];

                return false;
            }
        }

        return true;
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
