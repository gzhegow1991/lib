<?php

namespace Gzhegow\Lib\Delegate;

trait DelegateTrait
{
    protected $delegate;


    public function __isset($name)
    {
        return (function ($name) {
            return isset($this->{$name});
        })->call($this->delegate, $name);
    }

    public function __get($name)
    {
        return (function ($name) {
            return $this->{$name};
        })->call($this->delegate, $name);
    }

    public function __set($name, $value)
    {
        (function ($name, $value) {
            $this->{$name} = $value;
        })->call($this->delegate, $name, $value);
    }

    public function __unset($name)
    {
        (function ($name) {
            unset($this->{$name});
        })->call($this->delegate, $name);
    }


    public function __call($name, $arguments)
    {
        return (function ($name, $arguments) {
            return call_user_func_array([ $this, $name ], $arguments);
        })->call($this->delegate, $name, $arguments);
    }
}
