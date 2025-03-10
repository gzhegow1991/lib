<?php

namespace Gzhegow\Lib\Config;

use Gzhegow\Lib\Exception\LogicException;


abstract class AbstractConfig
{
    /**
     * @var array<string, bool>
     */
    protected $__keys = [];
    /**
     * @var array<string, self>
     */
    protected $__children = [];


    public function __construct()
    {
        foreach ( get_object_vars($this) as $key => $value ) {
            $this->__keys[ $key ] = true;

            if ($value instanceof self) {
                $this->__children[ $key ] = $value;
            }
        }
    }


    public function __isset($name)
    {
        if (! isset($this->__keys[ $name ])) {
            return false;
        }

        return true;
    }

    public function __get($name)
    {
        if (! isset($this->__keys[ $name ])) {
            throw new LogicException(
                'Missing property: ' . $name
            );
        }

        return $this->{$name};
    }

    public function __set($name, $value)
    {
        if (! isset($this->__keys[ $name ])) {
            throw new LogicException(
                'Missing property: ' . $name
            );
        }

        if (isset($this->__children[ $name ])) {
            $this->{$name}->fill($value);

        } else {
            $this->{$name} = $value;
        }
    }

    public function __unset($name)
    {
        if (! isset($this->__keys[ $name ])) {
            throw new LogicException(
                'Missing property: ' . $name
            );
        }

        $valueDefault = (new static())->{$name};

        if (isset($this->__children[ $name ])) {
            $this->{$name}->fill($valueDefault);

        } else {
            $this->{$name} = $valueDefault;
        }
    }


    /**
     * @return static
     */
    public function reset()
    {
        $this->fill(new static());

        return $this;
    }

    /**
     * @return static
     */
    public function fill(self $config) // : static
    {
        if (static::class !== get_class($config)) {
            throw new LogicException(
                [ 'The `config` should be instance of: ' . static::class, $config ]
            );
        }

        foreach ( $this->__keys as $key => $bool ) {
            $this->__set($key, $config->{$key});
        }

        return $this;
    }

    /**
     * @return static
     */
    public function load(array $config) // : static
    {
        foreach ( $this->__keys as $key => $bool ) {
            if (! array_key_exists($key, $config)) {
                continue;
            }

            $value = $config[ $key ];

            if (isset($this->__children[ $key ])) {
                $theClass = get_class($this->__children[ $key ]);

                $instance = new $theClass();
                $instance->load($value);

                $value = $instance;
            }

            $this->__set($key, $value);
        }

        return $this;
    }


    public function toArray(array $options = []) : array
    {
        $result = [];

        foreach ( $this->__keys as $key => $bool ) {
            if (isset($this->__children[ $key ])) {
                $result[ $key ] = $this->{$key}->toArray();

            } else {
                $result[ $key ] = $this->{$key};
            }
        }

        return $result;
    }


    public function validate() : bool
    {
        foreach ( $this->__keys as $key => $bool ) {
            if (! $this->validateKey($key)) {
                return false;
            }
        }

        return true;
    }

    public function validateKey(string $key) : bool
    {
        if (isset($this->__children[ $key ])) {
            // ! recursion
            return $this->__children[ $key ]->validate();
        }

        return true;
    }
}
