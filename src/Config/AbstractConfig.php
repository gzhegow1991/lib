<?php

namespace Gzhegow\Lib\Config;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;


abstract class AbstractConfig implements
    ToArrayInterface
{
    /**
     * @var array<string, bool>
     */
    protected $__keys = [];
    /**
     * @var array<string, self>
     */
    protected $__children = [];
    /**
     * @var bool
     */
    protected $__valid;


    public function __construct()
    {
        $publicVars = Lib::php()->get_object_vars($this, null);

        if ([] !== $publicVars) {
            throw new LogicException(
                [ 'The configuration must not have any public properties', $this ]
            );
        }

        $__keys = [
            '__keys'     => true,
            '__children' => true,
            '__valid'    => true,
        ];

        foreach ( get_object_vars($this) as $key => $value ) {
            if (isset($__keys[ $key ])) {
                continue;
            }

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

        $this->validate();

        return $this->{$name};
    }

    public function __set($name, $value)
    {
        if (! isset($this->__keys[ $name ])) {
            throw new LogicException(
                'Missing property: ' . $name
            );
        }

        $this->invalidate();

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

        $this->invalidate();

        $valueDefault = (new static())->{$name};

        if (isset($this->__children[ $name ])) {
            $this->{$name}->fill($valueDefault);

        } else {
            $this->{$name} = $valueDefault;
        }
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


    public function configure(?\Closure $fn = null, array $context = []) : void
    {
        if (null !== $fn) {
            $this->invalidate();

            $fnBound = $fn->bindTo($this, $this);

            $fnBound($this, $context);
        }

        $this->validate($context);
    }


    public function invalidate() : void
    {
        $this->__valid = null;
    }

    public function validate(array &$context = []) : void
    {
        if (null === $this->__valid) {
            $context[ '__path' ] = [];
            $context[ '__key' ] = null;
            $context[ '__parent' ] = null;
            $context[ '__root' ] = $this;

            $this->__valid = $this->validationRecursive($context);

            unset($context[ '__path' ]);
            unset($context[ '__key' ]);
            unset($context[ '__parent' ]);
            unset($context[ '__root' ]);
        }

        if (! $this->__valid) {
            throw new RuntimeException(
                [ 'Configuration is invalid', $this ]
            );
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


    protected function validationRecursive(array &$context = []) : bool
    {
        $status = null;

        $path = $context[ '__path' ] ?? [];
        $key = $context[ '__key' ] ?? null;
        $parent = $context[ '__parent' ] ?? null;

        foreach ( $this->__children as $childKey => $child ) {
            $fullpath = $path;
            $fullpath[] = $childKey;

            $context[ '__path' ] = $fullpath;
            $context[ '__key' ] = $childKey;
            $context[ '__parent' ] = $this;

            // ! recursion
            $status = $child->validationRecursive($context);

            if (! $status) {
                break;
            }
        }

        if (null === $status) {
            $context[ '__path' ] = $path;
            $context[ '__key' ] = $key;
            $context[ '__parent' ] = $parent;

            $status = $this->validation($context);
        }

        return $status;
    }

    protected function validation(array &$context = []) : bool
    {
        return true;
    }
}
