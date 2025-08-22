<?php

namespace Gzhegow\Lib\Config;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
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
     * @var bool|null
     */
    protected $__valid;


    public function __construct()
    {
        $thePhp = Lib::php();

        $publicVars = $thePhp->get_object_vars($this, null);

        if ([] !== $publicVars) {
            throw new LogicException(
                [ 'The configuration must not have any public properties', $this ]
            );
        }

        $__ignore = [
            '__keys'     => true,
            '__children' => true,
            '__valid'    => true,
        ];

        foreach ( get_object_vars($this) as $key => $value ) {
            if (isset($__ignore[ $key ])) {
                continue;
            }

            $this->__keys[ $key ] = true;

            if ($value instanceof self) {
                $this->__children[ $key ] = $value;
            }
        }
    }


    /**
     * @return static|Ret<static>
     */
    public static function from($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromArray($from)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
            return Ret::ok($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromArray($from, ?array $fallback = null)
    {
        if (! is_array($from)) {
            return Ret::throw(
                $fallback,
                [],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();

        try {
            $instance->load($from);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fallback,
                $e->getMessage(),
                [ $e->getFile(), $e->getLine() ]
            );
        }

        return Ret::ok($fallback, $instance);
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
        $this->unset($name);
    }


    public function toArray(array $options = []) : array
    {
        $result = [];

        foreach ( array_keys($this->__keys) as $key ) {
            if (isset($this->__children[ $key ])) {
                $result[ $key ] = $this->{$key}->toArray();

            } else {
                $result[ $key ] = $this->{$key};
            }
        }

        return $result;
    }


    /**
     * @param array{ 0?: mixed } $context
     */
    public function configure(?\Closure $fn = null, array $context = []) : void
    {
        if (null !== $fn) {
            $this->invalidate();

            $fnBound = $fn->bindTo($this, $this);

            call_user_func_array($fnBound, [ $this, $context ]);
        }

        $this->validate($context);
    }


    public function invalidate() : void
    {
        $this->__valid = null;
    }

    /**
     * @param array{ 0?: mixed, 1: array } $context
     */
    public function validate(array $context = []) : void
    {
        if (null === $this->__valid) {
            $refContext =& $context[ 1 ];
            $refContext = $refContext ?? [];

            $refContext[ '__path' ] = [];
            $refContext[ '__key' ] = null;
            $refContext[ '__parent' ] = null;
            $refContext[ '__root' ] = $this;

            $this->__valid = $this->validationRecursive($context);

            unset($refContext[ '__path' ]);
            unset($refContext[ '__key' ]);
            unset($refContext[ '__parent' ]);
            unset($refContext[ '__root' ]);
        }

        if (false === $this->__valid) {
            throw new RuntimeException(
                [ 'Configuration is invalid', $this ]
            );
        }
    }


    public function exists($name, &$value = null) : bool
    {
        $value = null;

        if (! isset($this->__keys[ $name ])) {
            return false;
        }

        $value = $this->{$name};

        return true;
    }

    public function isset($name, &$value = null) : bool
    {
        $value = null;

        if (! isset($this->__keys[ $name ])) {
            return false;
        }

        if (null === $this->{$name}) {
            return false;
        }

        $value = $this->{$name};

        return true;
    }

    public function get($name, array $fallback = [])
    {
        $error = null;

        if (! isset($this->__keys[ $name ])) {
            $error = [
                [ 'Missing property: ' . $name ],
                [ __FILE__, __LINE__ ],
            ];
        }

        if (null === $error) {
            try {
                $this->validate();
            }
            catch ( \Throwable $e ) {
                $error = [
                    [ $e->getMessage() ],
                    [ $e->getFile(), $e->getLine() ],
                ];
            }

            if (false === $this->__valid) {
                $error = [
                    [ 'The config is invalid', $this ],
                    [ __FILE__, __LINE__ ],
                ];
            }
        }

        if (null !== $error) {
            if ([] !== $fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new LogicException(...$error);
        }

        return $this->{$name};
    }

    /**
     * @return static
     */
    public function set($name, $value)
    {
        if (! isset($this->__keys[ $name ])) {
            throw new LogicException(
                'Missing property: ' . $name
            );
        }

        $this->invalidate();

        if (isset($this->__children[ $name ])) {
            /** @var self $child */

            $child = $this->{$name};
            $child->fill($value);

        } else {
            $this->{$name} = $value;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function unset($name)
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

            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function fill($config)
    {
        if (! (true
            && is_object($config)
            && (static::class === get_class($config))
        )) {
            throw new LogicException(
                [ 'The `config` should be an instance of: ' . static::class, $config ]
            );
        }

        foreach ( $this->__keys as $key => $bool ) {
            $this->set($key, $config->{$key});
        }

        return $this;
    }


    /**
     * @param array{ 0?: mixed, 1: array } $context
     */
    protected function validationRecursive(array $context = []) : bool
    {
        $refContext =& $context[ 1 ];
        $refContext = $refContext ?? [];

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

        $status = $this->validation($context);

        return $status;
    }

    /**
     * @param array{ 0?: mixed, 1: array } $context
     */
    protected function validation(array $context = []) : bool
    {
        return true;
    }
}
