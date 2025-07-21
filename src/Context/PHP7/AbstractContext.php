<?php

namespace Gzhegow\Lib\Context\PHP7;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;


abstract class AbstractContext implements
    ToArrayInterface,
    //
    \ArrayAccess
{
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


    public function toArray(array $options = []) : array
    {
        return $this->vars();
    }


    public function offsetExists($offset) : bool
    {
        return $this->exists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) : void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset) : void
    {
        $this->clear($offset);
    }


    public function exists(string $name) : bool
    {
        return property_exists($this, $name);
    }

    public function isset(string $name) : bool
    {
        return isset($this->{$name});
    }


    public function has(string $name, &$refResult = null) : bool
    {
        $refResult = null;

        $status = $this->exists($name);

        if ($status) {
            $refResult = $this->{$name} ?? null;

            return true;
        }

        return false;
    }

    public function get(string $name, array $fallback = [])
    {
        $status = $this->exists($name);

        if (! $status) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                'Missing property: ' . $name
            );
        }

        return $this->{$name} ?? null;
    }


    /**
     * @return static
     */
    public function set(string $name, $value)
    {
        if ('' === $name) {
            throw new LogicException(
                'Empty `name` is not supported'
            );
        }

        $this->{$name} = $value;

        return $this;
    }

    /**
     * @return static
     */
    public function clear(string $name)
    {
        if ($this->exists($name)) {
            $this->{$name} = null;
        }

        return $this;
    }


    /**
     * @return static
     */
    public function reset(string $name)
    {
        $instance = new static();

        if ($instance->exists($name)) {
            $this->{$name} = $instance->get($name);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function unset(string $name)
    {
        unset($this->{$name});

        return $this;
    }


    public function vars() : array
    {
        $vars = get_object_vars($this);

        return $vars;
    }

    public function keys() : array
    {
        $vars = $this->vars();

        $keys = array_keys($vars);

        return $keys;
    }

    public function values() : array
    {
        $vars = $this->vars();

        $values = array_values($vars);

        return $values;
    }

    public function entries() : array
    {
        $entries = [];

        foreach ( $this->vars() as $key => $value ) {
            $entries[] = [ $key, $value ];
        }

        return $entries;
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

    public function append(array $data) : void
    {
        foreach ( $data as $key => $value ) {
            if (! $this->exists($key)) {
                $this->set($key, $value);
            }
        }
    }


    /**
     * @return static
     */
    public function toCamelCase()
    {
        $theStr = Lib::str();

        $instance = new static();

        foreach ( $this->vars() as $key => $value ) {
            $keyCamelCase = $theStr->camel($key);

            $instance->{$keyCamelCase} = $value;
        }

        return $instance;
    }

    /**
     * @return static
     */
    public function toSnakeCase()
    {
        $theStr = Lib::str();

        $instance = new static();

        foreach ( $this->vars() as $key => $value ) {
            $keySnakeCase = $theStr->snake_lower($key);

            $instance->{$keySnakeCase} = $value;
        }

        return $instance;
    }
}
