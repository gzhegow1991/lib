<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Nil;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\ErrorBag\ErrorBag;
use Gzhegow\Lib\Modules\Php\Interfaces\ToListInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToBoolInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToFloatInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToObjectInterface;
use Gzhegow\Lib\Modules\Php\Pooling\DefaultPoolingFactory;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIntegerInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIterableInterface;
use Gzhegow\Lib\Modules\Php\Pooling\PoolingFactoryInterface;
use Gzhegow\Lib\Modules\Php\CallableParser\DefaultCallableParser;
use Gzhegow\Lib\Modules\Php\CallableParser\CallableParserInterface;


class PhpModule
{
    /**
     * @var CallableParserInterface
     */
    protected $callableParser;
    /**
     * @var PoolingFactoryInterface
     */
    protected $poolingFactory;

    /**
     * @var class-string<\LogicException|\RuntimeException>
     */
    protected $throwableClass = RuntimeException::class;
    /**
     * @var int
     */
    protected $poolingTickUsleep = 1000;


    public function newCallableParser() : CallableParserInterface
    {
        return new DefaultCallableParser();
    }

    public function cloneCallableParser() : CallableParserInterface
    {
        return clone $this->callableParser();
    }

    public function callableParser(?CallableParserInterface $callableParser = null) : CallableParserInterface
    {
        return $this->callableParser = null
            ?? $callableParser
            ?? $this->callableParser
            ?? new DefaultCallableParser();
    }


    public function newPoolingFactory() : PoolingFactoryInterface
    {
        return new DefaultPoolingFactory();
    }

    public function clonePoolingFactory() : PoolingFactoryInterface
    {
        return clone $this->poolingFactory();
    }

    public function poolingFactory(?PoolingFactoryInterface $poolingFactory = null) : PoolingFactoryInterface
    {
        return $this->poolingFactory = null
            ?? $poolingFactory
            ?? $this->poolingFactory
            ?? new DefaultPoolingFactory();
    }


    /**
     * @param ErrorBag $ref
     *
     * @return ErrorBag
     */
    public function newErrorBag(&$ref = null)
    {
        return $ref = new ErrorBag();
    }


    public function the_nil() : Nil
    {
        return new Nil();
    }

    public function the_timezone_nil() : \DateTimeZone
    {
        return new \DateTimeZone('+1234');
    }


    /**
     * @param class-string<\LogicException|\RuntimeException>|null $throwable_class
     *
     * @return class-string<\LogicException|\RuntimeException>
     */
    public function static_throwable_class(?string $throwable_class = null) : string
    {
        if (null !== $throwable_class) {
            if (! (false
                || is_subclass_of($throwable_class, \LogicException::class)
                || is_subclass_of($throwable_class, \RuntimeException::class)
            )) {
                throw new LogicException(
                    [
                        ''
                        . 'The `throwableClass` should be class-string that is subclass one of: '
                        . implode('|', [
                            \LogicException::class,
                            \RuntimeException::class,
                        ]),
                        //
                        $throwable_class,
                    ]
                );
            }

            $last = $this->throwableClass;

            $this->throwableClass = $throwable_class;

            $result = $last;
        }

        $result = $result ?? $this->throwableClass ?? RuntimeException::class;

        return $result;
    }

    public function static_pooling_tick_usleep(?int $pooling_tick_usleep = null) : int
    {
        if (null !== $pooling_tick_usleep) {
            if ($pooling_tick_usleep < 1) {
                throw new LogicException(
                    [ 'The `pooling_tick_usleep` should be positive integer', $pooling_tick_usleep ]
                );
            }

            $last = $this->poolingTickUsleep;

            $this->poolingTickUsleep = $pooling_tick_usleep;

            $result = $last;
        }

        $result = $result ?? $this->poolingTickUsleep ?? 1000;

        return $result;
    }


    /**
     * @param array|\Countable|null $r
     */
    public function type_countable(&$r, $value) : bool
    {
        $r = null;

        if (PHP_VERSION_ID >= 70300) {
            if (is_countable($value)) {
                $r = $value;

                return true;
            }

            return false;
        }

        if (is_array($value)) {
            $r = $value;

            return true;
        }

        if ($value instanceof \Countable) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param \Countable|null $r
     */
    public function type_countable_object(&$r, $value) : bool
    {
        $r = null;

        if (PHP_VERSION_ID >= 70300) {
            if (is_object($value) && is_countable($value)) {
                $r = $value;

                return true;
            }

            return false;
        }

        if ($value instanceof \Countable) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param array|\Countable|null $r
     */
    public function type_sizeable(&$r, $value) : bool
    {
        $r = null;

        if ($this->type_countable($countable, $value)) {
            $r = $value;

            return true;
        }

        if (Lib::str()->type_string($string, $value)) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $r
     * @param class-string<T>|T|mixed $value
     */
    public function type_struct_exists(&$r, $value, ?int $flags = null)
    {
        $r = null;

        $_flags = $flags ?? _PHP_STRUCT_TYPE_ALL;

        $isObject = is_object($value);

        if ($isObject) {
            $class = get_class($value);

        } elseif (Lib::type()->string_not_empty($valueString, $value)) {
            $class = ltrim($valueString, '\\');

            if ('' === $class) {
                return false;
            }

        } else {
            return false;
        }

        if ($class === '__PHP_Incomplete_Class') {
            return false;
        }

        if ($_flags & _PHP_STRUCT_TYPE_CLASS) {
            if (PHP_VERSION_ID >= 80100) {
                if (class_exists($class) && ! enum_exists($class)) {
                    $r = $class;

                    return true;
                }

            } else {
                if (class_exists($class)) {
                    $r = $class;

                    return true;
                }
            }
        }

        if ($_flags & _PHP_STRUCT_TYPE_ENUM) {
            if (PHP_VERSION_ID >= 80100) {
                if (enum_exists($class)) {
                    $r = $class;

                    return true;
                }
            }
        }

        if (! $isObject) {
            if ($_flags & _PHP_STRUCT_TYPE_INTERFACE) {
                if (interface_exists($class)) {
                    $r = $class;

                    return true;
                }
            }

            if ($_flags & _PHP_STRUCT_TYPE_TRAIT) {
                if (trait_exists($class)) {
                    $r = $class;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $r
     * @param class-string<T>|T|mixed $value
     */
    public function type_struct(&$r, $value, ?int $flags = null) : bool
    {
        $r = null;

        $_flags = $flags ?? (
            _PHP_STRUCT_TYPE_ALL
            | _PHP_STRUCT_EXISTS_TRUE
        );

        $sum = 0;
        $sum += (($_flags & _PHP_STRUCT_EXISTS_TRUE) ? 1 : 0);
        $sum += (($_flags & _PHP_STRUCT_EXISTS_FALSE) ? 1 : 0);
        $sum += (($_flags & _PHP_STRUCT_EXISTS_IGNORE) ? 1 : 0);
        if (1 !== $sum) {
            $_flags &= ~(
                _PHP_STRUCT_EXISTS_TRUE
                | _PHP_STRUCT_EXISTS_FALSE
                | _PHP_STRUCT_EXISTS_IGNORE
            );

            $_flags |= _PHP_STRUCT_EXISTS_TRUE;
        }
        unset($sum);

        $isExistsTrue = (bool) ($_flags & _PHP_STRUCT_EXISTS_TRUE);
        $isExistsFalse = (bool) ($_flags & _PHP_STRUCT_EXISTS_FALSE);
        $isExistsIgnore = (bool) ($_flags & _PHP_STRUCT_EXISTS_IGNORE);

        $isExists = null;

        if (is_object($value)) {
            $class = get_class($value);

            $isEnum = is_a($value, '\UnitEnum');
            $isClass = ! $isEnum;

            if ($isEnum && ($_flags & _PHP_STRUCT_TYPE_ENUM)) {
                $isExists = true;

            } elseif ($isClass && ($_flags & _PHP_STRUCT_TYPE_CLASS)) {
                $isExists = true;
            }

        } else {
            if (! Lib::type()->string_not_empty($valueString, $value)) {
                return false;
            }

            $class = ltrim($valueString, '\\');

            if ('' === $class) {
                return false;
            }
        }

        if ('__PHP_Incomplete_Class' === $class) {
            return false;
        }

        if ($isExistsTrue || $isExistsFalse) {
            $isExists = $isExists ?? $this->type_struct_exists($class, $class, $_flags);

            if ($isExists && $isExistsFalse) {
                return false;
            }
            if ((! $isExists) && $isExistsTrue) {
                return false;
            }

            if ($isExists && $isExistsTrue) {
                $r = $class;

                return true;
            }
        }

        if ($isExistsFalse || $isExistsIgnore) {
            $isValid = (bool) preg_match(
                '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/',
                $class
            );

            if ($isValid) {
                $r = $class;

                return true;
            }
        }

        return false;
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $r
     * @param class-string<T>|T|mixed $value
     */
    public function type_struct_class(&$r, $value, ?int $flags = null) : bool
    {
        $_flags = $flags;

        if (null === $_flags) {
            $_flags = (
                _PHP_STRUCT_TYPE_CLASS
                | _PHP_STRUCT_EXISTS_TRUE
            );

        } else {
            $_flags &= ~_PHP_STRUCT_TYPE_ALL;
            $_flags |= _PHP_STRUCT_TYPE_CLASS;
        }

        return $this->type_struct($r, $value, $_flags);
    }

    /**
     * @param class-string|null $r
     */
    public function type_struct_interface(&$r, $value, ?int $flags = null) : bool
    {
        $_flags = $flags;

        if (null === $_flags) {
            $_flags = (
                _PHP_STRUCT_TYPE_INTERFACE
                | _PHP_STRUCT_EXISTS_TRUE
            );

        } else {
            $_flags &= ~_PHP_STRUCT_TYPE_ALL;
            $_flags |= _PHP_STRUCT_TYPE_INTERFACE;
        }

        return $this->type_struct($r, $value, $_flags);
    }

    /**
     * @param class-string|null $r
     */
    public function type_struct_trait(&$r, $value, ?int $flags = null) : bool
    {
        $_flags = $flags;

        if (null === $_flags) {
            $_flags = (
                _PHP_STRUCT_TYPE_TRAIT
                | _PHP_STRUCT_EXISTS_TRUE
            );

        } else {
            $_flags &= ~_PHP_STRUCT_TYPE_ALL;
            $_flags |= _PHP_STRUCT_TYPE_TRAIT;
        }

        return $this->type_struct($r, $value, $_flags);
    }

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param class-string<T>|null    $r
     * @param class-string<T>|T|mixed $value
     */
    public function type_struct_enum(&$r, $value, ?int $flags = null) : bool
    {
        $_flags = $flags;

        if (null === $_flags) {
            $_flags = (
                _PHP_STRUCT_TYPE_ENUM
                | _PHP_STRUCT_EXISTS_TRUE
            );

        } else {
            $_flags &= ~_PHP_STRUCT_TYPE_ALL;
            $_flags |= _PHP_STRUCT_TYPE_ENUM;
        }

        return $this->type_struct($r, $value, $_flags);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $r
     * @param class-string<T>|T|mixed $value
     */
    public function type_struct_fqcn(&$r, $value, ?int $flags = null) : bool
    {
        $r = null;

        if (! $this->type_struct($_value, $value, $flags)) {
            return false;
        }

        $_value = '\\' . $_value;

        $r = $_value;

        return true;
    }

    /**
     * @param string|null $r
     */
    public function type_struct_namespace(&$r, $value, ?int $flags = null) : bool
    {
        $r = null;

        if (! $this->type_struct($_value, $value, $flags)) {
            return false;
        }

        $_value = $this->dirname($_value, '\\');
        if (null === $_value) {
            return false;
        }

        $r = $value;

        return true;
    }

    /**
     * @param string|null $r
     */
    public function type_struct_basename(&$r, $value, ?int $flags = null) : bool
    {
        $r = null;

        if (! $this->type_struct($_value, $value, $flags)) {
            return false;
        }

        $_value = $this->basename($_value, '\\');

        if (null !== $_value) {
            $r = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param resource|null $r
     */
    public function type_resource(&$r, $value, ?string $resourceType = null) : bool
    {
        $r = null;

        if (is_resource($value)) {
            if (null === $resourceType) {
                $r = $value;

                return true;

            } else {
                if ($resourceType === get_resource_type($value)) {
                    $r = $value;

                    return true;
                }
            }
        }

        if ('resource (closed)' === gettype($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param resource|null $r
     */
    public function type_resource_opened(&$r, $value, ?string $resourceType = null) : bool
    {
        $r = null;

        if (is_resource($value)) {
            if (null === $resourceType) {
                $r = $value;

                return true;

            } else {
                if ($resourceType === get_resource_type($value)) {
                    $r = $value;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param resource|null $r
     */
    public function type_resource_closed(&$r, $value) : bool
    {
        $r = null;

        if ('resource (closed)' === gettype($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param resource|null $r
     */
    public function type_any_not_resource(&$r, $value) : bool
    {
        $r = null;

        if (! (false
            || is_resource($value)
            || ('resource (closed)' === gettype($value))
        )) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param resource|\CurlHandle|null $r
     */
    public function type_curl(&$r, $value) : bool
    {
        $r = null;

        if (false
            || is_a($value, '\CurlHandle')
            || $this->type_resource_opened($var, $value, 'curl')
        ) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param resource|\Socket|null $r
     */
    public function type_socket(&$r, $value) : bool
    {
        $r = null;

        if (false
            || is_a($value, '\Socket')
            || $this->type_resource_opened($var, $value, 'socket')
        ) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @template-covariant T of \UnitEnum
     *
     * @param T|null               $r
     * @param T|int|string         $value
     * @param class-string<T>|null $enumClass
     *
     * @return class-string|null
     */
    public function type_enum_case(&$r, $value, ?string $enumClass = null) : bool
    {
        $r = null;

        $hasEnumClass = false;
        if (null !== $enumClass) {
            if (! is_subclass_of($enumClass, '\UnitEnum')) {
                return false;
            }

            $hasEnumClass = true;
        }

        if (is_object($value)) {
            $status = $hasEnumClass
                ? is_a($value, $enumClass)
                : is_subclass_of($value, '\UnitEnum');

            if ($status) {
                $r = $value;

                return true;
            }
        }

        if (! $hasEnumClass) {
            return false;
        }

        if (! (false
            || is_int($value)
            || is_string($value)
        )) {
            return false;
        }

        $enumCase = null;
        try {
            $enumCase = $enumClass::tryFrom($value);
        }
        catch ( \Throwable $e ) {
        }

        if (null !== $enumCase) {
            $r = $enumCase;

            return true;
        }

        return false;
    }


    /**
     * > метод не всегда возвращает callable, поскольку массив [ 'class', 'method' ] не является callable, если метод публичный
     * > используйте type_callable_array, если собираетесь вызывать метод
     *
     * @param array{ 0: class-string, 1: string }|null $r
     */
    public function type_method_array(&$r, $value) : bool
    {
        return $this->callableParser()->typeMethodArray($r, $value);
    }

    /**
     * > метод не всегда возвращает callable, поскольку строка 'class->method' не является callable
     * > используйте type_callable_string, если собираетесь вызывать метод
     *
     * @param string|null $r
     */
    public function type_method_string(&$r, $value, array $refs = []) : bool
    {
        return $this->callableParser()->typeMethodString($r, $value, $refs);
    }


    /**
     * > в версиях PHP до 8.0.0 публичный метод считался callable, если его проверить даже на имени класса
     * > при этом вызвать MyClass::publicMethod было нельзя, т.к. вызываемым является только MyClass::publicStaticMethod
     *
     * @param callable|null $r
     * @param string|object $newScope
     */
    public function type_callable(&$r, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallable($r, $value, $newScope);
    }


    /**
     * @param callable|\Closure|object|null $r
     */
    public function type_callable_object(&$r, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableObject($r, $value, $newScope);
    }

    /**
     * @param callable|object|null $r
     */
    public function type_callable_object_closure(&$r, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableObjectClosure($r, $value, $newScope);
    }

    /**
     * @param callable|object|null $r
     */
    public function type_callable_object_invokable(&$r, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableObjectInvokable($r, $value, $newScope);
    }


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $r
     * @param string|object                                            $newScope
     */
    public function type_callable_array(&$r, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableArray($r, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $r
     * @param string|object                                            $newScope
     */
    public function type_callable_array_method(&$r, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableArrayMethod($r, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $r
     * @param string|object                                     $newScope
     */
    public function type_callable_array_method_static(&$r, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableArrayMethodStatic($r, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object, 1: string }|null $r
     * @param string|object                               $newScope
     */
    public function type_callable_array_method_non_static(&$r, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableArrayMethodNonStatic($r, $value, $newScope);
    }


    /**
     * @param callable-string|null $r
     */
    public function type_callable_string(&$r, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableString($r, $value, $newScope);
    }

    /**
     * @param callable-string|null $r
     */
    public function type_callable_string_function(&$r, $value) : bool
    {
        return $this->callableParser()->typeCallableStringFunction($r, $value);
    }

    /**
     * @param callable-string|null $r
     */
    public function type_callable_string_function_internal(&$r, $value) : bool
    {
        return $this->callableParser()->typeCallableStringFunctionInternal($r, $value);
    }

    /**
     * @param callable-string|null $r
     */
    public function type_callable_string_function_non_internal(&$r, $value) : bool
    {
        return $this->callableParser()->typeCallableStringFunctionNonInternal($r, $value);
    }

    /**
     * @param callable-string|null $r
     */
    public function type_callable_string_method_static(&$r, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableStringMethodStatic($r, $value, $newScope);
    }


    /**
     * > получает ссылку из массива ссылок или создает новую переменную - если в функцию передали ссылки и нужно убедится, что значение по ссылке требовалось снаружи
     *
     * @template T
     *
     * @param mixed|T        $r
     * @param int|string     $key
     * @param array{ 0?: T } $set
     */
    public function type_ref(&$r, $key, array $refs = [], array $set = []) : bool
    {
        $status = array_key_exists($key, $refs);

        if ($status) {
            $r =& $refs[ $key ];

        } else {
            $r = null;
        }

        if ([] !== $set) {
            $r = $set[ 0 ];
        }

        return $status;
    }


    public function is_windows() : bool
    {
        static $current;

        return $current = $current ?? ('WIN' === strtoupper(substr(PHP_OS, 0, 3)));
    }

    public function is_terminal() : bool
    {
        static $current;

        return $current = $current ?? in_array(\PHP_SAPI, [ 'cli', 'phpdbg' ]);
    }


    public function to_bool($value, array $options = []) : bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value instanceof ToBoolInterface) {
            return $value->toBool($options);
        }

        if (false
            || (null === $value)
            || (is_float($value) && is_nan($value))
            || (Lib::type()->nil($var, $value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to boolean',
                    $value,
                ]
            );
        }

        if (! Lib::type()->bool($_value, $value)) {
            throw new LogicException(
                [
                    'Unable to convert value to boolean',
                    $value,
                ]
            );
        }

        return $_value;
    }

    public function to_int($value, array $options = []) : int
    {
        if (is_int($value)) {
            return $value;
        }

        if ($value instanceof ToIntegerInterface) {
            return $value->toInteger($options);
        }

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->nil($var, $value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to integer',
                    $value,
                ]
            );
        }

        if (! Lib::type()->int($_value, $value)) {
            throw new LogicException(
                [
                    'Unable to convert value to integer',
                    $value,
                ]
            );
        }

        return $_value;
    }

    public function to_float($value, array $options = []) : float
    {
        if (is_float($value)) {
            if (! is_finite($value)) {
                throw new LogicException(
                    [
                        'Unable to parse value while converting to float',
                        $value,
                    ]
                );
            }

            return $value;
        }

        if ($value instanceof ToFloatInterface) {
            return $value->toFloat($options);
        }

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            // || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->nil($var, $value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to float',
                    $value,
                ]
            );
        }

        if (! Lib::type()->float($_value, $value)) {
            throw new LogicException(
                [
                    'Unable to convert value to float',
                    $value,
                ]
            );
        }

        return $_value;
    }

    public function to_string($value, array $options = []) : string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof ToStringInterface) {
            return $value->toString($options);
        }

        if (false
            || (null === $value)
            // || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->nil($var, $value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to string',
                    $value,
                ]
            );
        }

        if (! Lib::type()->string($_value, $value)) {
            throw new LogicException(
                [
                    'Unable to convert value to string',
                    $value,
                ]
            );
        }

        return $_value;
    }


    public function to_array($value, array $options = []) : array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            if ($value instanceof ToArrayInterface) {
                return $value->toArray($options);
            }

            if ($value instanceof ToObjectInterface) {
                return (array) $value->toObject($options);
            }

            $isStdClass = (get_class($value) === \stdClass::class);

            if (! $isStdClass) {
                throw new LogicException(
                    [
                        'The `value` (if object) should be instance of: ' . \stdClass::class,
                        $value,
                    ]
                );
            }
        }

        if (false
            || (null === $value)
            // || ('' === $value)
            // || (is_bool($value))
            // || (is_array($value))
            || (is_float($value) && (! is_nan($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->nil($var, $value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to array',
                    $value,
                ]
            );
        }

        $_value = (array) $value;

        return $_value;
    }

    public function to_object($value, array $options = []) : \stdClass
    {
        if (is_object($value)) {
            if ($value instanceof ToObjectInterface) {
                return $value->toObject($options);
            }

            if ($value instanceof ToArrayInterface) {
                return (object) $value->toArray($options);
            }

            $isStdClass = (get_class($value) === \stdClass::class);

            if (! $isStdClass) {
                throw new LogicException(
                    [
                        'The `value` (if object) should be instance of: ' . \stdClass::class,
                        $value,
                    ]
                );
            }

            return $value;
        }

        if (false
            || (null === $value)
            // || ('' === $value)
            // || (is_bool($value))
            // || (is_array($value))
            || (is_float($value) && (! is_nan($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->nil($var, $value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to string',
                    $value,
                ]
            );
        }

        $_value = (object) (array) $value;

        return $_value;
    }

    public function to_iterable($value, array $options = []) : iterable
    {
        if (null === $value) {
            return [];
        }

        if (is_object($value)) {
            if ($value instanceof ToIterableInterface) {
                return $value->toIterable($options);
            }

            if ($value instanceof \Traversable) {
                return $value;
            }
        }

        if (is_array($value)) {
            return $value;
        }

        return [ $value ];
    }


    /**
     * @param callable $fnAssert
     */
    public function to_list(
        $value, array $options = [],
        $fnAssert = null, array $fnAssertArgs = [], $fnAssertValueKey = 0
    ) : array
    {
        if (null === $value) {
            return [];
        }

        $hasAssert = (null !== $fnAssert);

        $fnArgs = [];
        if ($hasAssert) {
            [ $fnArgs ] = Lib::func()->func_args_unique([ $fnAssertValueKey => null ], $fnAssertArgs);
        }

        $listValid = null;

        if ($value instanceof ToListInterface) {
            $list = $value->toList($options);

        } elseif (is_array($value)) {
            if ($hasAssert) {
                $fnArgs[ $fnAssertValueKey ] = $value;

                $status = (bool) call_user_func_array($fnAssert, $fnArgs);

                if ($status) {
                    $listValid = [ $value ];
                }
            }

            if (null === $listValid) {
                if (Lib::arr()->type_list($var, $value)) {
                    $list = $var;

                } else {
                    $list = [ $value ];
                }
            }

        } else {
            $list = [ $value ];
        }

        if (null !== $listValid) {
            return $listValid;
        }

        if ([] !== $list) {
            if ($hasAssert) {
                foreach ( $list as $i => $v ) {
                    $fnArgs[ $fnAssertValueKey ] = $v;

                    $status = (bool) call_user_func_array($fnAssert, $fnArgs);

                    if (! $status) {
                        throw new LogicException(
                            [
                                'Each of `value` (if array) should pass `fnAssert` check',
                                $v,
                                $i,
                            ]
                        );
                    }
                }
            }
        }

        return $list;
    }

    public function to_list_it($value, array $options = []) : \Generator
    {
        if (null === $value) {
            return true;
        }

        if ($value instanceof ToListInterface) {
            $list = $value->toList($options);

            foreach ( $list as $v ) {
                yield $v;
            }

        } elseif (is_array($value)) {
            yield $value;

            if (Lib::arr()->type_list($list, $value)) {
                foreach ( $list as $v ) {
                    yield $v;
                }
            }

        } else {
            yield $value;
        }

        return true;
    }


    /**
     * @return int|float
     */
    public function count($value) // : int|NAN
    {
        if ($this->type_countable($countable, $value)) {
            return count($countable);
        }

        return NAN;
    }

    /**
     * @return int|float
     */
    public function size($value) // : int|NAN
    {
        if ($this->type_countable($countable, $value)) {
            return count($countable);
        }

        if (Lib::str()->type_string($string, $value)) {
            return strlen($string);
        }

        return NAN;
    }

    /**
     * @return int|float
     */
    public function length($value) // : int|NAN
    {
        if ($this->type_countable($countable, $value)) {
            return count($countable);
        }

        $theStr = Lib::str();

        if ($theStr->type_string($string, $value)) {
            return $theStr->strlen($string);
        }

        return NAN;
    }


    /**
     * @return array{
     *     internal: array<string, bool>,
     *     user: array<string, bool>,
     * }
     */
    public function get_defined_functions() : array
    {
        $getDefinedFunctions = get_defined_functions();

        $flipInternal = array_fill_keys($getDefinedFunctions[ 'internal' ] ?? [], true);
        $flipUser = array_fill_keys($getDefinedFunctions[ 'user' ] ?? [], true);

        ksort($flipInternal);
        ksort($flipUser);

        $result = [];
        $result[ 'internal' ] += $flipInternal;
        $result[ 'user' ] += $flipUser;

        return $result;
    }

    /**
     * @param object|class-string $objectOrClass
     *
     * @return class-string[]
     */
    public function class_uses($objectOrClass, ?bool $isRecursive = null)
    {
        $isRecursive = $isRecursive ?? false;

        $className = $objectOrClass;
        if (is_object($objectOrClass)) {
            $className = get_class($objectOrClass);
        }

        $uses = class_uses($className) ?: [];

        if ($isRecursive) {
            foreach ( $uses as $usesItem ) {
                // > ! recursion
                $uses += $this->class_uses($usesItem);
            }
        }

        return $uses;
    }

    /**
     * @param object|class-string $objectOrClass
     *
     * @return class-string[]
     */
    public function class_uses_with_parents($objectOrClass, ?bool $recursive = null)
    {
        $recursive = $recursive ?? false;

        $className = $objectOrClass;
        if (is_object($objectOrClass)) {
            $className = get_class($objectOrClass);
        }

        $uses = [];

        $sources = []
            + array_reverse(class_parents($className))
            + [ $className => $className ];

        foreach ( $sources as $sourceClassName ) {
            $uses += $this->class_uses($sourceClassName, $recursive);
        }

        $uses = array_unique($uses);

        return $uses;
    }


    /**
     * > функция property_exists() возвращает true для любых свойств, в том числе protected/private и вне зависимости от static
     * > эта используется, чтобы проверить публичные и/или статические свойства
     *
     * @param class-string|object $object_or_class
     */
    public function property_exists(
        $object_or_class, string $property,
        ?bool $public = null, ?bool $static = null
    ) : bool
    {
        $isObject = false;
        $isClass = false;
        if (! (false
            || ($isObject = (is_object($object_or_class)))
            || ($isClass = (is_string($object_or_class) && class_exists($object_or_class)))
        )) {
            return false;
        }

        $theObject = null;
        $theClass = null;
        if ($isObject) {
            $theObject = $object_or_class;
            $theClass = get_class($object_or_class);

        } elseif ($isClass) {
            $theClass = $object_or_class;
        }

        $isPublic = $public === true;
        $isNotPublic = $public === false;
        $isMaybePublic = ! $isNotPublic;

        $isStatic = $static === true;
        $isNotStatic = $static === false;
        $isMaybeStatic = ! $isNotStatic;
        $isNotStaticOrDoesntMatter = ! $isStatic;

        if ($isMaybePublic) {
            if ($isMaybeStatic) {
                if (isset($object_or_class::${$property})) {
                    return true;
                }
            }

            if ($theObject) {
                if ($isNotStaticOrDoesntMatter) {
                    if (isset($theObject->{$property})) {
                        return true;
                    }

                    $vars = get_object_vars($theObject);
                    if ($vars) {
                        if (array_key_exists($property, $vars)) {
                            return true;
                        }
                    }
                }
            }
        }

        if (! property_exists($object_or_class, $property)) {
            return false;
        }

        $isMattersPublic = $public !== null;
        $isMattersStatic = $static !== null;

        if (! $isMattersPublic && ! $isMattersStatic) {
            return true;
        }

        try {
            $rp = new \ReflectionProperty($theClass, $property);

            $isPublicProp = $rp->isPublic();
            $isStaticProp = $rp->isStatic();

            if (! $isPublicProp && $isPublic) {
                return false;
            }

            if (! $isStaticProp && $isStatic) {
                return false;
            }

            if ($isPublicProp && $isNotPublic) {
                return false;
            }

            if ($isStaticProp && $isNotStatic) {
                return false;
            }
        }
        catch ( \Throwable $e ) {
            return false;
        }

        return true;
    }

    /**
     * > функция method_exists() возвращает true для любых методов, в том числе protected/private и вне зависимости от static
     * > эта используется, чтобы проверить публичные и/или статические методы
     *
     * @param class-string|object $object_or_class
     */
    public function method_exists(
        $object_or_class, string $method,
        ?bool $public = null, ?bool $static = null
    ) : bool
    {
        $isObject = false;
        $isClass = false;
        if (! (false
            || ($isObject = (is_object($object_or_class)))
            || ($isClass = (is_string($object_or_class) && class_exists($object_or_class)))
        )) {
            return false;
        }

        $theObject = null;
        $theClass = null;
        if ($isObject) {
            $theObject = $object_or_class;
            $theClass = get_class($object_or_class);

        } elseif ($isClass) {
            $theClass = $object_or_class;
        }

        if (! method_exists($object_or_class, $method)) {
            return false;
        }

        $isMattersPublic = $public !== null;
        $isMattersStatic = $static !== null;

        if (! $isMattersPublic && ! $isMattersStatic) {
            return true;
        }

        $isPublic = $public === true;
        $isStatic = $static === true;
        $isNotPublic = $public === false;
        $isNotStatic = $static === false;

        try {
            $rm = new \ReflectionMethod($theClass, $method);

            $isPublicMethod = $rm->isPublic();
            $isStaticMethod = $rm->isStatic();

            if (! $isPublicMethod && $isPublic) {
                return false;
            }

            if (! $isStaticMethod && $isStatic) {
                return false;
            }

            if ($isPublicMethod && $isNotPublic) {
                return false;
            }

            if ($isStaticMethod && $isNotStatic) {
                return false;
            }
        }
        catch ( \Throwable $e ) {
            return false;
        }

        return true;
    }


    /**
     * > функция get_object_vars() возвращает только публичные свойства для $this
     * > чтобы получить доступ ко всем свойствам, её нужно вызвать в обертке
     *
     * @param string|object $newScope
     */
    public function get_object_vars(object $object, $newScope = 'static') : array
    {
        if ('static' === $newScope) {
            // > if you need `static` scope you may call the existing php function
            throw new RuntimeException(
                'You should pass constant __CLASS__ to second argument to keep scope `static`'
            );
        }

        $fnGetObjectVars = null;
        if (null !== $newScope) {
            $fnGetObjectVars = (static function ($object) {
                return get_object_vars($object);
            })->bindTo(null, $newScope);
        }

        $vars = $fnGetObjectVars
            ? $fnGetObjectVars($object)
            : get_object_vars($object);

        return $vars;
    }

    /**
     * > функция get_class_vars() возвращает только публичные (и статические публичные) свойства для $object_or_class
     * > чтобы получить доступ ко всем свойствам, её нужно вызвать в обертке
     *
     * @param string|object $newScope
     */
    public function get_class_vars($object_or_class, $newScope = 'static') : array
    {
        if ('static' === $newScope) {
            // > if you need `static` scope you may call the existing php function
            throw new RuntimeException(
                'You should pass constant __CLASS__ to second argument to keep scope `static`'
            );
        }

        $fnGetClassVars = null;
        if (null !== $newScope) {
            $fnGetClassVars = (static function ($class) {
                return get_class_vars($class);
            })->bindTo(null, $newScope);
        }

        $class = is_object($object_or_class)
            ? get_class($object_or_class)
            : $object_or_class;

        $vars = $fnGetClassVars
            ? $fnGetClassVars($class)
            : get_class_vars($class);

        return $vars;
    }

    /**
     * > функция get_class_methods() возвращает только публичные (и статические публичные) методы для $object_or_class
     * > чтобы получить доступ ко всем методам, её нужно вызвать в обертке
     *
     * @param string|object $newScope
     */
    public function get_class_methods($object_or_class, $newScope = 'static') : array
    {
        if ('static' === $newScope) {
            // > if you need `static` scope you may call the existing php function
            throw new RuntimeException(
                'You should pass constant __CLASS__ to second argument to keep scope `static`'
            );
        }

        $fnGetClassMethods = null;
        if (null !== $newScope) {
            $fnGetClassMethods = (static function ($object_or_class) {
                return get_class_methods($object_or_class);
            })->bindTo(null, $newScope);
        }

        $vars = $fnGetClassMethods
            ? $fnGetClassMethods($object_or_class)
            : get_class_vars($object_or_class);

        return $vars;
    }


    /**
     * > is_callable является контекстно-зависимой функцией
     * > будучи вызванной снаружи класса она не покажет методы protected/private
     * > если её вызвать в обертке с указанием $newScope - это сработает
     *
     * @param string|object $newScope
     */
    public function is_callable($value, $newScope = 'static') : bool
    {
        $result = null;

        if ('static' === $newScope) {
            // > if you need `static` scope you may call the existing php function
            throw new RuntimeException(
                'You should pass constant __CLASS__ to second argument to keep scope `static`'
            );
        }

        $fnIsCallable = null;
        if (null !== $newScope) {
            $fnIsCallable = (static function ($callable) {
                return is_callable($callable);
            })->bindTo(null, $newScope);
        }

        $status = $fnIsCallable
            ? $fnIsCallable($value)
            : is_callable($value);

        if ($status) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * > во встроенной функции pathinfo() для двойного расширения возвращается только последнее, `image.min.jpg` -> 'jpg`
     * > + поддерживает предварительную замену $separator на '/'
     * > + при указанных значениях возвращает все ключи, то есть отсутствие расширения при требовании его вернуть - ключ будет и будет NULL
     *
     * @return array{
     *     dirname?: string|null,
     *     basename?: string|null,
     *     filename?: string|null,
     *     extension?: string|null,
     *     file?: string|null,
     *     extensions?: string|null,
     * }
     */
    public function pathinfo(
        string $path, ?string $separator = null, ?string $dot = null,
        ?int $flags = null
    ) : array
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        $_flags = $flags ?? _PHP_PATHINFO_ALL;

        $theType = Lib::type();

        if (! $theType->char($separatorString, $separator ?? '/')) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        if (! $theType->char($dotString, $dot ?? '.')) {
            throw new LogicException(
                [ 'The `dot` should be char', $dot ]
            );
        }

        if ('/' === $dotString) {
            throw new LogicException(
                [ 'The `dot` should not be `/` sign' ]
            );
        }

        $normalized = $this->path_normalize($path, '/');

        $dirname = ltrim($normalized, '/');
        $basename = basename($normalized);

        $pi = [];

        if ($_flags & PATHINFO_DIRNAME) {
            if (false === strpos($dirname, '/')) {
                $dirname = null;

            } else {
                $dirname = dirname($dirname);

                $dirname = str_replace('/', $separatorString, $dirname);

                $dirname = ('.' !== $dirname) ? $dirname : null;
            }

            $pi[ 'dirname' ] = $dirname;
        }

        if ($_flags & _PHP_PATHINFO_BASENAME) {
            $pi[ 'basename' ] = ('' !== $basename) ? $basename : null;
        }

        if (false
            || ($_flags & _PHP_PATHINFO_FILENAME)
            || ($_flags & _PHP_PATHINFO_EXTENSION)
            || ($_flags & _PHP_PATHINFO_FILE)
            || ($_flags & _PHP_PATHINFO_EXTENSIONS)
        ) {
            $filename = $basename;

            $split = explode($dotString, $basename) + [ '', '' ];

            $file = array_shift($split);

            if ($_flags & _PHP_PATHINFO_EXTENSION) {
                $extension = end($split);

                if ('' === $extension) {
                    $pi[ 'extension' ] = null;

                } else {
                    $pi[ 'extension' ] = $extension;

                    $filename = basename($basename, "{$dotString}{$extension}");
                }
            }

            if ($_flags & _PHP_PATHINFO_FILENAME) {
                $pi[ 'filename' ] = ('' !== $filename) ? $filename : null;
            }

            if ($_flags & _PHP_PATHINFO_FILE) {
                $pi[ 'file' ] = ('' !== $file) ? $file : null;
            }

            if ($_flags & _PHP_PATHINFO_EXTENSIONS) {
                $extensions = null;
                if ([] !== $split) {
                    $extensions = implode($dotString, $split);
                }

                $pi[ 'extensions' ] = $extensions;
            }
        }

        return $pi;
    }

    public function dirname(
        string $path, ?string $separator = null,
        ?int $levels = null
    ) : ?string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        $theType = Lib::type();

        if (! $theType->char($separatorString, $separator ?? '/')) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        if (! $theType->int_positive($levelsInt, $levels ?? 1)) {
            throw new LogicException(
                [ 'The `levels` should be positive integer', $levels ]
            );
        }

        $normalized = $this->path_normalize($path, '/');

        $dirname = ltrim($normalized, '/');

        if (false === strpos($dirname, '/')) {
            $dirname = null;

        } else {
            $dirname = dirname($dirname, $levelsInt);

            $dirname = str_replace('/', $separatorString, $dirname);
        }

        return ('.' !== $dirname) ? $dirname : null;
    }

    public function basename(string $path, ?string $extension = null) : ?string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        $normalized = $this->path_normalize($path, '/');

        $basename = basename($normalized, $extension);

        return ('' !== $basename) ? $basename : null;
    }

    public function filename(string $path, ?string $dot = null) : ?string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        if (! Lib::type()->char($dotString, $dot ?? '.')) {
            throw new LogicException(
                [ 'The `dot` should be char', $dot ]
            );
        }

        $normalized = $this->path_normalize($path, '/');

        $basename = basename($normalized);

        $split = explode($dotString, $basename) + [ '', '' ];

        $extension = end($split);

        $filename = basename($basename, "{$dotString}{$extension}");

        return ('' !== $filename) ? $filename : null;
    }

    public function fname(string $path, ?string $dot = null) : ?string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        if (! Lib::type()->char($dotString, $dot ?? '.')) {
            throw new LogicException(
                [ 'The `dot` should be char', $dot ]
            );
        }

        $normalized = $this->path_normalize($path, '/');

        $basename = basename($normalized);

        [ $file ] = explode($dotString, $basename, 2);

        return ('' !== $file) ? $file : null;
    }

    public function extension(string $path, ?string $dot = null) : ?string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        if (! Lib::type()->char($dotString, $dot ?? '.')) {
            throw new LogicException(
                [ 'The `dot` should be char', $dot ]
            );
        }

        $normalized = $this->path_normalize($path, '/');

        $basename = basename($normalized);

        $split = explode($dotString, $basename) + [ '', '' ];

        $extension = end($split);

        return ('' !== $extension) ? $extension : null;
    }

    public function extensions(string $path, ?string $dot = null) : ?string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        if (! Lib::type()->char($dotString, $dot ?? '.')) {
            throw new LogicException(
                [ 'The `dot` should be char', $dot ]
            );
        }

        if ('/' === $dotString) {
            throw new LogicException(
                [ 'The `dot` should not be `/` sign' ]
            );
        }

        $normalized = $this->path_normalize($path, '/');

        $basename = basename($normalized);

        $split = explode($dotString, $basename) + [ 1 => '' ];

        array_shift($split);

        $extensions = null;
        if ([] !== $split) {
            $extensions = implode($dotString, $split);
        }

        return $extensions;
    }


    /**
     * > заменяет слеши в пути на указанные
     */
    public function path_normalize(string $path, ?string $separator = null) : string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        if (! Lib::type()->char($separatorString, $separator ?? '/')) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        $separators = [
            '\\'                => true,
            DIRECTORY_SEPARATOR => true,
            $separatorString    => true,
        ];
        $separators = array_keys($separators);

        $normalized = str_replace($separators, $separatorString, $path);

        return $normalized;
    }

    /**
     * > разбирает последовательности `./path` и `../path` и возвращает нормализованный путь
     */
    public function path_resolve(string $path, ?string $separator = null, ?string $dot = null) : string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        $theType = Lib::type();

        if (! $theType->char($separatorString, $separator ?? '/')) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        if (! $theType->char($dotString, $dot ?? '.')) {
            throw new LogicException(
                [ 'The `dot` should be char', $dot ]
            );
        }

        $pathNormalized = $this->path_normalize($path, '/');

        $root = ($pathNormalized[ 0 ] === '/')
            ? $separatorString
            : '';

        $segments = trim($pathNormalized, '/');
        $segments = explode('/', $segments);

        $segmentsNew = [];
        foreach ( $segments as $segment ) {
            if (false
                || ('' === $segment)
                || ($dotString === $segment)
            ) {
                continue;
            }

            if ($segment === "{$dotString}{$dotString}") {
                if ([] === $segmentsNew) {
                    throw new RuntimeException(
                        [
                            'The `path` is invalid to parse `..` segments',
                            $path,
                        ]
                    );
                }

                array_pop($segmentsNew);

                continue;
            }

            $segmentsNew[] = $segment;
        }

        $pathResolved = $root . implode($separatorString, $segmentsNew);

        if ('' === $pathResolved) {
            throw new RuntimeException(
                [
                    'Result path should be non-empty string',
                    $path,
                    $separatorString,
                    $dotString,
                ]
            );
        }

        return $pathResolved;
    }


    /**
     * > возвращает относительный нормализованный путь, отрезая у него $root
     */
    public function path_relative(
        string $path, string $root,
        ?string $separator = null, ?string $dot = null
    ) : string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `absolute` should be non-empty string' ]
            );
        }

        if ('' === $root) {
            throw new LogicException(
                [ 'The `root` should be non-empty string' ]
            );
        }

        if (! Lib::type()->char($separatorString, $separator ?? '/')) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        $pathResolved = $this->path_resolve($path, $separatorString, $dot);

        $rootNormalized = $this->path_normalize($root, $separatorString);
        $rootNormalized = rtrim($rootNormalized, $separatorString);

        $status = Lib::str()->str_starts(
            $pathResolved, ($rootNormalized . $separatorString),
            false,
            [ &$pathRelative ]
        );

        if (! $status) {
            throw new RuntimeException(
                [ 'The `absolute` is not a part of the `root`', $root ]
            );
        }

        if ('' === $pathRelative) {
            throw new RuntimeException(
                [
                    'Result path should be non-empty string',
                    $path,
                    $separatorString,
                    $dot,
                ]
            );
        }

        return $pathRelative;
    }

    /**
     * > возвращает абсолютный нормализованный путь, с поддержкой `./path` и `../path`
     */
    public function path_absolute(
        string $relative, string $current,
        ?string $separator = null, ?string $dot = null
    ) : string
    {
        if ('' === $relative) {
            throw new LogicException(
                [ 'The `relative` should be non-empty string' ]
            );
        }

        if ('' === $current) {
            throw new LogicException(
                [ 'The `current` should be non-empty string' ]
            );
        }

        if (! Lib::type()->char($separatorString, $separator ?? '/')) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        $relativeNormalized = $this->path_normalize($relative, $separatorString);

        $isRoot = ($separatorString === $relativeNormalized[ 0 ]);

        if ($isRoot) {
            $absoluteNormalized = $relativeNormalized;

        } else {
            $currentNormalized = $this->path_normalize($current, $separatorString);

            $absoluteNormalized = $currentNormalized . $separatorString . $relativeNormalized;
        }

        $absoluteResolved = $this->path_resolve($absoluteNormalized, $separatorString, $dot);

        return $absoluteResolved;
    }

    /**
     * > возвращает абсолютный нормализованный путь, с поддержкой `./path` и `../path`, но только если путь начинается с `.`
     */
    public function path_or_absolute(
        string $path, string $current,
        ?string $separator = null, ?string $dot = null
    ) : string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `relative` should be non-empty string' ]
            );
        }

        if ('' === $current) {
            throw new LogicException(
                [ 'The `current` should be non-empty string' ]
            );
        }

        if (! Lib::type()->char($dotString, $dot ?? '.')) {
            throw new LogicException(
                [ 'The `dot` should be char', $separator ]
            );
        }

        $isDot = ($dotString === $path[ 0 ]);

        if ($isDot) {
            $pathResolved = $this->path_absolute($path, $current, $separator, $dotString);

        } else {
            $pathResolved = $this->path_normalize($path, $separator);
        }

        return $pathResolved;
    }


    /**
     * @param mixed $data
     */
    public function serialize($data) : ?string
    {
        try {
            $result = Lib::func()->safe_call(
                'serialize',
                [ $data ]
            );
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        return $result;
    }

    /**
     * @return mixed|null
     */
    public function unserialize(string $data)
    {
        try {
            $result = Lib::func()->safe_call(
                'unserialize',
                [ $data ]
            );
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        if (is_object($result) && (get_class($result) === '__PHP_Incomplete_Class')) {
            $result = null;
        }

        return $result;
    }


    public function throwable_args(...$throwableArgs) : array
    {
        $len = count($throwableArgs);

        $messageList = [];
        $messageDataList = [];
        $messageObjectList = [];
        $codeIntegerList = [];
        $codeStringList = [];
        $previousList = [];

        $__unresolved = [];

        for ( $i = 0; $i < $len; $i++ ) {
            $arg = $throwableArgs[ $i ];

            if (is_int($arg)) {
                $codeIntegerList[ $i ] = $arg;

                continue;
            }

            if (is_string($arg) && ('' !== $arg)) {
                $messageList[ $i ] = $arg;

                continue;
            }

            if (false
                || is_array($arg)
                || $arg instanceof \stdClass
            ) {
                $messageData = (array) $arg;

                $messageString = (string) ($messageData[ 0 ] ?? null);

                if ('' !== $messageString) {
                    unset($messageData[ 0 ]);

                    $messageList[ $i ] = $messageString;
                }

                $messageDataList[ $i ] = $messageData;

                continue;
            }

            if ($arg instanceof \Throwable) {
                $previousList[ $i ] = $arg;

                continue;
            }

            $__unresolved[ $i ] = $arg;
        }

        if (([] === $messageList) && ([] !== $previousList)) {
            foreach ( $previousList as $i => $previous ) {
                $messageList[ $i ] = $previous->getMessage();
            }
        }

        for ( $i = 0; $i < $len; $i++ ) {
            if (isset($messageList[ $i ])) {
                if (! preg_match('/[^a-z0-9_]/i', $messageList[ $i ])) {
                    $codeStringList[ $i ] = strtoupper($messageList[ $i ]);
                }
            }
        }

        foreach ( $messageList as $i => $messageString ) {
            $messageData = $messageDataList[ $i ] ?? [];

            $messageObjectList[ $i ] = (object) ([ $messageString ] + $messageData);
        }

        $result = [];

        $result[ 'messageList' ] = $messageList;
        $result[ 'messageDataList' ] = $messageDataList;
        $result[ 'messageObjectList' ] = $messageObjectList;
        $result[ 'codeIntegerList' ] = $codeIntegerList;
        $result[ 'codeStringList' ] = $codeStringList;
        $result[ 'previousList' ] = $previousList;

        $result += [
            'message'       => (([] !== $messageList) ? reset($messageList) : ''),
            'messageData'   => (([] !== $messageDataList) ? reset($messageDataList) : []),
            'messageObject' => (([] !== $messageObjectList) ? reset($messageObjectList) : null),
            'code'          => (([] !== $codeIntegerList) ? reset($codeIntegerList) : -1),
            'codeString'    => (([] !== $codeStringList) ? reset($codeStringList) : ''),
            'previous'      => (([] !== $previousList) ? reset($previousList) : null),
        ];

        $result[ '__unresolved' ] = $__unresolved;

        return $result;
    }

    /**
     * @return null
     *
     * @throws \LogicException|\RuntimeException
     *
     * @noinspection PhpUnnecessaryStopStatementInspection
     */
    public function throw($throwableOrArg, ...$throwableArgs)
    {
        $throwableClass = $this->static_throwable_class();

        $trace = property_exists($throwableClass, 'trace')
            ? debug_backtrace()
            : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->throw_trace($trace, $throwableOrArg, ...$throwableArgs);

        return;
    }

    /**
     * @return null
     *
     * @throws \LogicException|\RuntimeException
     *
     * @noinspection PhpUnnecessaryStopStatementInspection
     */
    public function throw_new(...$throwableArgs)
    {
        $throwableClass = $this->static_throwable_class();

        $trace = property_exists($throwableClass, 'trace')
            ? debug_backtrace()
            : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->throw_new_trace($trace, ...$throwableArgs);

        return;
    }

    /**
     * @return null
     *
     * @throws \LogicException|\RuntimeException
     *
     * @noinspection PhpUnnecessaryStopStatementInspection
     */
    public function throw_trace(array $trace, $throwableOrArg, ...$throwableArgs)
    {
        if (false
            || ($throwableOrArg instanceof \LogicException)
            || ($throwableOrArg instanceof \RuntimeException)
        ) {
            throw $throwableOrArg;
        }

        array_unshift($throwableArgs, $throwableOrArg);

        $this->throw_new_trace($trace, ...$throwableArgs);

        return;
    }

    /**
     * @return null
     *
     * @throws \LogicException|\RuntimeException
     *
     * @noinspection PhpUnnecessaryStopStatementInspection
     */
    public function throw_new_trace(array $trace, ...$throwableArgs)
    {
        $throwableClass = $this->static_throwable_class();

        $throwableArgsNew = $this->throwable_args(...$throwableArgs);
        $throwableArgsNew[ 'file' ] = $trace[ 0 ][ 'file' ] ?? '{file}';
        $throwableArgsNew[ 'line' ] = $trace[ 0 ][ 'line' ] ?? 0;
        $throwableArgsNew[ 'trace' ] = $trace;

        $exceptionArgs = [];
        $exceptionArgs[] = $throwableArgsNew[ 'message' ] ?? null;
        $exceptionArgs[] = $throwableArgsNew[ 'code' ] ?? null;
        $exceptionArgs[] = $throwableArgsNew[ 'previous' ] ?? null;

        $e = new $throwableClass(...$exceptionArgs);

        foreach ( $throwableArgsNew as $key => $value ) {
            if (! property_exists($e, $key)) {
                unset($throwableArgsNew[ $key ]);
            }
        }

        if ([] !== $throwableArgsNew) {
            $fn = (function () use (&$throwableArgsNew) {
                foreach ( $throwableArgsNew as $key => $value ) {
                    $this->{$key} = $value;
                }
            })->bindTo($e, $e);

            call_user_func($fn);
        }

        if ($e) {
            // > phpstorm `void` supression for chaining
            throw $e;
        }

        return;
    }


    /**
     * @param callable      $fnPooling
     * @param callable|null $fnCatch
     *
     * @return mixed|false
     */
    public function poolingSync(
        ?int $tickUsleep, ?int $timeoutMs,
        $fnPooling, $fnCatch = null
    )
    {
        $hasFnCatch = (null !== $fnCatch);

        $tickUsleep = $tickUsleep ?? $this->static_pooling_tick_usleep();

        if ($tickUsleep <= 0) {
            throw new LogicException(
                [ 'The `tickUsleep` should be integer positive', $tickUsleep ]
            );
        }

        if (! (false
            || (null === $timeoutMs)
            || ($timeoutMs >= 0)
        )) {
            throw new LogicException(
                [ 'The `timeoutMs` should be integer non-negative or be null', $timeoutMs ]
            );
        }

        $ctx = $this->poolingFactory()->newContext();

        $ctx->resetTimeoutMs($timeoutMs);

        do {
            $ctx->updateNowMicrotime();

            if ($hasFnCatch) {
                try {
                    call_user_func_array($fnPooling, [ $ctx ]);
                }
                catch ( \Throwable $e ) {
                    call_user_func_array($fnCatch, [ $e, $ctx ]);
                }

            } else {
                call_user_func_array($fnPooling, [ $ctx ]);
            }

            if ($ctx->hasResult($refResult)) {
                return $refResult;

            } elseif ($ctx->hasError($refError)) {
                throw new RuntimeException(
                    [ 'Pooling function returned error', $refError ]
                );
            }

            if (null !== ($timeoutMicrotime = $ctx->hasTimeoutMicrotime())) {
                if (microtime(true) > $timeoutMicrotime) {
                    break;
                }
            }

            usleep($tickUsleep);
        } while ( true );

        return false;
    }
}
