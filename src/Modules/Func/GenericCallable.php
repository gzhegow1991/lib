<?php

namespace Gzhegow\Lib\Modules\Func;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Func\Invoker\InvokerInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\CanIsSameInterface;


class GenericCallable implements
    \Serializable,
    //
    CanIsSameInterface
{
    /**
     * @var InvokerInterface
     */
    protected $invoker;

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

    public function __invoke(...$values)
    {
        if (null !== $this->invoker) {
            $result = $this->invoker->callUserFuncArray($this, $values);

        } else {
            if ($this->isClosure) {
                $fn = $this->closureObject;

            } elseif ($this->isMethod) {
                if (null !== $this->methodObject) {
                    $fn = [ $this->methodObject, $this->methodName ];

                } else {
                    // > ! static
                    $fn = [ $this->methodClass, $this->methodName ];
                }

            } elseif ($this->isInvokable) {
                if (null !== $this->invokableObject) {
                    $fn = $this->invokableObject;

                } else {
                    $className = $this->invokableClass;

                    // > ! factory
                    $fn = new $className();
                }

            } elseif ($this->isFunction) {
                if (null !== $this->functionStringNonInternal) {
                    $fn = $this->functionStringNonInternal;

                } else {
                    $fnString = $this->functionStringInternal;

                    $fn = static function (...$args) use ($fnString) {
                        return Lib::func()->call_user_func_array($fnString, $args);
                    };
                }

            } else {
                throw new RuntimeException(
                    [ 'Unable to extract callable from: ' . GenericCallable::class, $this ]
                );
            }

            $result = call_user_func_array($fn, $values);
        }

        return $result;
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
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function from($from, array $context = [], $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? static::fromInstance($from, $retCur)
            ?? static::fromFunction($from, $context, $retCur)
            ?? static::fromMethod($from, $context, $retCur)
            ?? static::fromClosure($from, $context, $retCur)
            ?? static::fromInvokableObject($from, $context, $retCur)
            ?? static::fromInvokableClass($from, $context, $retCur);

        if ($retCur->isErr()) {
            return Result::err($ret, $retCur);
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromInstance($from, $ret = null)
    {
        if ($from instanceof static) {
            return Result::ok($ret, $from);
        }

        return Result::err(
            $ret,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromClosure($from, array $context = [], $ret = null)
    {
        if (! ($from instanceof \Closure)) {
            return Result::err(
                $ret,
                [ 'The `from` should be instance of \Closure', $from ],
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

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromMethod($from, array $context = [], $ret = null)
    {
        if (! Lib::php()->type_method_string($methodString, $from, [ &$methodArray ])) {
            return Result::err(
                $ret,
                [ 'The `from` should be existing method', $from ],
                [ __FILE__, __LINE__ ]
            );
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

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromInvokableObject($from, array $context = [], $ret = null)
    {
        if (! is_object($from)) {
            return Result::err(
                $ret,
                [ 'The `from` should be object', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! method_exists($from, '__invoke')) {
            return Result::err(
                $ret,
                [ 'The `from` should be invokable object', $from ],
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

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromInvokableClass($from, array $context = [], $ret = null)
    {
        if (! Lib::type()->string_not_empty($invokableClass, $from)) {
            return Result::err(
                $ret,
                [ 'The `from` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! class_exists($invokableClass)) {
            return Result::err(
                $ret,
                [ 'The `from` should be existing class', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! method_exists($invokableClass, '__invoke')) {
            return Result::err(
                $ret,
                [ 'The `from` should be invokable class', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $arguments = $context[ 'arguments' ] ?? [];

        $instance = new static();
        $instance->args = $arguments;

        $instance->isInvokable = true;
        $instance->invokableClass = $invokableClass;

        $instance->key = "\"{$invokableClass}\"";

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromFunction($function, array $context = [], $ret = null)
    {
        $thePhp = Lib::php();

        if (! Lib::type()->string_not_empty($_function, $function)) {
            return Result::err(
                $ret,
                [ 'The `from` should be existing function name', $function ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! function_exists($_function)) {
            return Result::err(
                $ret,
                [ 'The `from` should be existing function name', $_function ],
                [ __FILE__, __LINE__ ]
            );
        }

        $arguments = $context[ 'arguments' ] ?? [];

        $instance = new static();
        $instance->args = $arguments;

        $instance->isFunction = true;

        $isInternal = $thePhp->type_callable_string_function_internal($_functionInternal, $_function);

        if ($isInternal) {
            $instance->functionStringInternal = $_function;

        } else {
            $instance->functionStringNonInternal = $_function;
        }

        $instance->key = "\"{$_function}\"";

        return Result::ok($ret, $instance);
    }


    public function setInvoker(?InvokerInterface $invoker) : ?InvokerInterface
    {
        $last = $this->invoker;

        $this->invoker = $invoker;

        return $last;
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
