<?php

namespace Gzhegow\Lib\Delegate;

trait DelegateTrait
{
    public function delegateIsset(object $delegate, $name)
    {
        return (function ($name) {
            return isset($this->{$name});
        })->call($delegate, $name);
    }

    public function delegateGet(object $delegate, $name)
    {
        return (function ($name) {
            return $this->{$name};
        })->call($delegate, $name);
    }

    public function delegateSet(object $delegate, $name, $value)
    {
        (function ($name, $value) {
            $this->{$name} = $value;
        })->call($delegate, $name, $value);
    }

    public function delegateUnset(object $delegate, $name)
    {
        (function ($name) {
            unset($this->{$name});
        })->call($delegate, $name);
    }

    public function delegateCall(object $delegate, $name, $arguments)
    {
        return (function ($name, $arguments) {
            return call_user_func_array([ $this, $name ], $arguments);
        })->call($delegate, $name, $arguments);
    }
}
