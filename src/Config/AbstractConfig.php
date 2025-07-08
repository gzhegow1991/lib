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


    public function configure(?\Closure $fn = null, array &$refContext = []) : void
    {
        if (null !== $fn) {
            $this->invalidate();

            $fnBound = $fn->bindTo($this, $this);

            call_user_func_array($fnBound, [ $this, &$refContext ]);
        }

        $this->validate($refContext);
    }


    public function invalidate() : void
    {
        $this->__valid = null;
    }

    public function validate(array &$refContext = []) : void
    {
        if (null === $this->__valid) {
            $refContext[ '__path' ] = [];
            $refContext[ '__key' ] = null;
            $refContext[ '__parent' ] = null;
            $refContext[ '__root' ] = $this;

            $this->__valid = $this->validationRecursive($refContext);

            unset($refContext[ '__path' ]);
            unset($refContext[ '__key' ]);
            unset($refContext[ '__parent' ]);
            unset($refContext[ '__root' ]);
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
    public function load(array $config)
    {
        foreach ( $this->__keys as $key => $bool ) {
            if (! array_key_exists($key, $config)) {
                continue;
            }

            $value = $config[ $key ];

            if (isset($this->__children[ $key ])) {
                $configClass = get_class($this->__children[ $key ]);

                $instance = new $configClass();
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
    public function fill(self $config)
    {
        if (static::class !== get_class($config)) {
            throw new LogicException(
                [ 'The `config` should be an instance of: ' . static::class, $config ]
            );
        }

        foreach ( $this->__keys as $key => $bool ) {
            $this->__set($key, $config->{$key});
        }

        return $this;
    }


    protected function validationRecursive(array &$refContext = []) : bool
    {
        $path = $refContext[ '__path' ] ?? [];
        $key = $refContext[ '__key' ] ?? null;
        $parent = $refContext[ '__parent' ] ?? null;

        foreach ( $this->__children as $childKey => $child ) {
            $fullpath = $path;
            $fullpath[] = $childKey;

            $refContext[ '__path' ] = $fullpath;
            $refContext[ '__key' ] = $childKey;
            $refContext[ '__parent' ] = $this;

            // > ! recursion
            $statusChild = $child->validationRecursive($refContext);

            if (! $statusChild) {
                return false;
            }
        }

        $refContext[ '__path' ] = $path;
        $refContext[ '__key' ] = $key;
        $refContext[ '__parent' ] = $parent;

        $status = $this->validation($refContext);

        return $status;
    }

    protected function validation(array &$refContext = []) : bool
    {
        return true;
    }
}
