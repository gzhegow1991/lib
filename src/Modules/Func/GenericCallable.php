<?php

namespace Gzhegow\Lib\Modules\Func;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Func\Invoker\InvokerInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\CanIsSameInterface;


class GenericCallable implements
    \Serializable,
    //
    CanIsSameInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var bool
     */
    protected $isClosure = false;
    /**
     * @var \Closure
     */
    protected $closureObject;

    /**
     * @var bool
     */
    protected $isMethod = false;
    /**
     * @var class-string
     */
    protected $methodClass;
    /**
     * @var object
     */
    protected $methodObject;
    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var bool
     */
    protected $isInvokable = false;
    /**
     * @var callable|object
     */
    protected $invokableObject;
    /**
     * @var class-string
     */
    protected $invokableClass;

    /**
     * @var bool
     */
    protected $isFunction = false;
    /**
     * @var callable|string
     */
    protected $functionStringInternal;
    /**
     * @var callable|string
     */
    protected $functionStringNonInternal;


    private function __construct()
    {
    }

    public function __invoke(InvokerInterface $invoker, ...$values)
    {
        return $invoker->callUserFuncArray($this, $values);
    }


    public function __serialize() : array
    {
        $vars = get_object_vars($this);

        return array_filter($vars);
    }

    public function __unserialize(array $data) : void
    {
        foreach ( $data as $key => $val ) {
            $this->{$key} = $val;
        }
    }

    public function serialize()
    {
        $array = $this->__serialize();

        return serialize($array);
    }

    public function unserialize($data)
    {
        $array = unserialize($data);

        $this->__unserialize($array);
    }


    /**
     * @return static|Ret<static>
     */
    public static function from($from, array $context = [], ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromInstance($from)->orNull($ret)
            ?? static::fromFunction($from, $context)->orNull($ret)
            ?? static::fromMethod($from, $context)->orNull($ret)
            ?? static::fromClosure($from, $context)->orNull($ret)
            ?? static::fromInvokableObject($from, $context)->orNull($ret)
            ?? static::fromInvokableClass($from, $context)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromInstance($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
            return Ret::ok($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromClosure($from, array $context = [], ?array $fallback = null)
    {
        if (! ($from instanceof \Closure)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be an instance of \Closure', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $arguments = $context[ 'arguments' ] ?? [];

        $instance = new static();
        $instance->args = $arguments;

        $instance->isClosure = true;
        $instance->closureObject = $from;

        $phpId = spl_object_id($from);

        $instance->key = "{ object # \Closure # {$phpId} }";

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromMethod($from, array $context = [], ?array $fallback = null)
    {
        $theType = Lib::type();

        if (! $theType->method_string($from, [ &$methodArray ])->isOk([ &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        $arguments = $context[ 'arguments' ] ?? [];

        [ $objectOrClass, $methodName ] = $methodArray;

        $instance = new static();
        $instance->args = $arguments;

        $instance->isMethod = true;

        if (is_object($objectOrClass)) {
            $object = $objectOrClass;

            $phpClass = get_class($object);
            $phpId = spl_object_id($object);

            $key0 = "\"{ object # {$phpClass} # {$phpId} }\"";

            $instance->methodObject = $object;

        } else {
            $objectClass = $objectOrClass;

            $key0 = '"' . $objectClass . '"';

            $instance->methodClass = $objectClass;
        }

        $key1 = "\"{$methodName}\"";

        $instance->methodName = $methodName;

        $instance->key = "[ {$key0}, {$key1} ]";

        return Ret::throw($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromInvokableObject($from, array $context = [], ?array $fallback = null)
    {
        if (! is_object($from)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be an object', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! method_exists($from, '__invoke')) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be an invokable object', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $arguments = $context[ 'arguments' ] ?? [];

        $instance = new static();
        $instance->args = $arguments;

        $instance->isInvokable = true;
        $instance->invokableObject = $from;

        $phpClass = get_class($from);
        $phpId = spl_object_id($from);

        $instance->key = "\"{ object # {$phpClass} # {$phpId} }\"";

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromInvokableClass($from, array $context = [], ?array $fallback = null)
    {
        $theType = Lib::type();

        if (! $theType->string_not_empty($from)->isOk([ &$fromStringNotEmpty, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        $invokableClass = $fromStringNotEmpty;

        if (! class_exists($invokableClass)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be an existing class', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! method_exists($invokableClass, '__invoke')) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be an invokable class', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $arguments = $context[ 'arguments' ] ?? [];

        $instance = new static();
        $instance->args = $arguments;

        $instance->isInvokable = true;
        $instance->invokableClass = $invokableClass;

        $instance->key = "\"{$invokableClass}\"";

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromFunction($function, array $context = [], ?array $fallback = null)
    {
        $theType = Lib::type();

        if (! $theType->string_not_empty($function)->isOk([ &$functionStringNotEmpty, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        if (! function_exists($functionStringNotEmpty)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be an existing function name', $function ],
                [ __FILE__, __LINE__ ]
            );
        }

        $arguments = $context[ 'arguments' ] ?? [];

        $instance = new static();
        $instance->args = $arguments;

        $instance->isFunction = true;

        if ($theType->callable_string_function_internal($functionStringNotEmpty)->isOk()) {
            $instance->functionStringInternal = $functionStringNotEmpty;

        } else {
            $instance->functionStringNonInternal = $functionStringNotEmpty;
        }

        $instance->key = "\"{$functionStringNotEmpty}\"";

        return Ret::ok($fallback, $instance);
    }


    /**
     * @param static $object
     */
    public function isSame($object, array $options = []) : bool
    {
        return $this->key === $object->key;
    }


    public function getKey() : string
    {
        return $this->key;
    }


    public function getArgs() : array
    {
        return $this->args;
    }


    public function isClosure() : bool
    {
        return $this->isClosure;
    }

    /**
     * @param \Closure|null $refFn
     */
    public function hasClosureObject(&$refFn = null) : bool
    {
        $refFn = null;

        if (null !== $this->closureObject) {
            $refFn = $this->closureObject;

            return true;
        }

        return false;
    }

    public function getClosureObject() : \Closure
    {
        return $this->closureObject;
    }


    public function isMethod() : bool
    {
        return $this->isMethod;
    }

    /**
     * @param class-string|null $refMethodClass
     */
    public function hasMethodClass(&$refMethodClass = null) : ?string
    {
        $refMethodClass = null;

        if (null !== $this->methodClass) {
            $refMethodClass = $this->methodClass;

            return true;
        }

        return false;
    }

    /**
     * @return class-string
     */
    public function getMethodClass() : string
    {
        return $this->methodClass;
    }

    /**
     * @param string|null $refMethodObject
     */
    public function hasMethodObject(&$refMethodObject = null) : bool
    {
        $refMethodObject = null;

        if (null !== $this->methodObject) {
            $refMethodObject = $this->methodObject;

            return true;
        }

        return false;
    }

    public function getMethodObject() : object
    {
        return $this->methodObject;
    }

    /**
     * @param string|null $refMethodName
     */
    public function hasMethodName(&$refMethodName = null) : bool
    {
        $refMethodName = null;

        if (null !== $this->methodName) {
            $refMethodName = $this->methodName;

            return true;
        }

        return false;
    }

    public function getMethodName() : string
    {
        return $this->methodName;
    }


    public function isInvokable() : bool
    {
        return $this->isInvokable;
    }

    /**
     * @param callable|object|null $refInvokableObject
     */
    public function hasInvokableObject(&$refInvokableObject = null) : bool
    {
        $refInvokableObject = null;

        if (null !== $this->invokableObject) {
            $refInvokableObject = $this->invokableObject;

            return true;
        }

        return false;
    }

    /**
     * @return callable|object
     */
    public function getInvokableObject() : object
    {
        return $this->invokableObject;
    }

    /**
     * @param class-string|null $refInvokableClass
     */
    public function hasInvokableClass(&$refInvokableClass = null) : bool
    {
        $refInvokableClass = null;

        if (null !== $this->invokableClass) {
            $refInvokableClass = $this->invokableClass;

            return true;
        }

        return false;
    }

    /**
     * @return class-string
     */
    public function getInvokableClass() : string
    {
        return $this->invokableClass;
    }


    public function isFunction() : bool
    {
        return $this->isFunction;
    }

    /**
     * @param callable|string|null $refFunctionStringInternal
     */
    public function hasFunctionStringInternal(&$refFunctionStringInternal = null) : bool
    {
        $refFunctionStringInternal = null;

        if (null !== $this->functionStringInternal) {
            $refFunctionStringInternal = $this->functionStringInternal;

            return true;
        }

        return false;
    }

    /**
     * @return callable|string
     */
    public function getFunctionStringInternal() : string
    {
        return $this->functionStringInternal;
    }

    /**
     * @param callable|string|null $refFunctionStringNonInternal
     */
    public function hasFunctionStringNonInternal(&$refFunctionStringNonInternal = null) : bool
    {
        $refFunctionStringNonInternal = null;

        if (null !== $this->functionStringNonInternal) {
            $refFunctionStringNonInternal = $this->functionStringNonInternal;

            return true;
        }

        return false;
    }

    /**
     * @return callable|string
     */
    public function getFunctionStringNonInternal() : string
    {
        return $this->functionStringNonInternal;
    }
}
