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
     * @var array<int, array{ 0: string[], 1: mixed }>
     */
    protected $__errors;
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
        $publicVars = Lib::php()->get_object_vars($this, null);

        if (count($publicVars)) {
            throw new LogicException(
                [ 'The configuration must not have any public properties', $this ]
            );
        }

        $__keys = [
            '__errors'   => true,
            '__keys'     => true,
            '__children' => true,
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

        return $this->{$name};
    }

    public function __set($name, $value)
    {
        if (! isset($this->__keys[ $name ])) {
            throw new LogicException(
                'Missing property: ' . $name
            );
        }

        $this->__errors = null;

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

        $this->__errors = null;

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


    /**
     * @return static
     */
    public function configure(\Closure $fn = null, array $context = [])
    {
        if (null !== $fn) {
            $fn($this, $context);
        }

        $this->validate($context);

        return $this;
    }

    /**
     * @return static
     */
    public function validate(array $context = [])
    {
        if (null === $this->__errors) {
            $this->__errors = $this->validateConfig(
                $this,
                [], $context
            );
        }

        if (count($this->__errors)) {
            throw new RuntimeException(
                [ 'Configuration is invalid', $this->__errors ]
            );
        }

        return $this;
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


    public function getErrors() : array
    {
        return $this->__errors ?? [];
    }

    protected function validateValue($value, string $key, array $path = [], array $context = []) : array
    {
        // $errors = [];
        //
        // switch ($key):
        //     case 'mykey':
        //         if ($value === false) {
        //            $errors[] = [ $path, 'The `mykey` is wrong' ];
        //         }
        //         break;
        // endswitch;
        //
        // return $errors;

        return [];
    }

    protected function validateConfig(self $config, array $path = [], array $context = []) : array
    {
        $errors = [];

        foreach ( $config->__keys as $key => $bool ) {
            $fullpath = $path;
            $fullpath[] = $key;

            if (isset($config->__children[ $key ])) {
                $configChild = $config->__children[ $key ];

                // ! recursion
                $errorsKey = $configChild
                    ->validateConfig(
                        $configChild,
                        $fullpath, $context
                    )
                ;

            } else {
                $errorsKey = $config
                    ->validateValue(
                        $config->{$key}, $key,
                        $fullpath, $context
                    )
                ;
            }

            if (count($errorsKey)) {
                $errors = array_merge(
                    $errors,
                    array_values($errorsKey)
                );
            }
        }

        return $errors;
    }
}
