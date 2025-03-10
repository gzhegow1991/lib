<?php

namespace Gzhegow\Lib\Delegate;

/**
 * @template T of object
 */
class Delegate
{
    /**
     * @var T
     */
    protected $object;


    /**
     * @param T $object
     */
    public function __construct(object $object)
    {
        $this->object = $object;
    }


    /**
     * @return T
     */
    public function getObject() : object
    {
        return $this->object;
    }


    public function __isset($name)
    {
        return (function ($name) {
            return isset($this->{$name});
        })->call($this->object, $name);
    }

    public function __get($name)
    {
        return (function ($name) {
            return $this->{$name};
        })->call($this->object, $name);
    }

    public function __set($name, $value)
    {
        (function ($name, $value) {
            $this->{$name} = $value;
        })->call($this->object, $name, $value);
    }

    public function __unset($name)
    {
        (function ($name) {
            unset($this->{$name});
        })->call($this->object, $name);
    }


    public function __call($name, $arguments)
    {
        return (function ($name, $arguments) {
            return call_user_func_array([ $this, $name ], $arguments);
        })->call($this->object, $name, $arguments);
    }
}
