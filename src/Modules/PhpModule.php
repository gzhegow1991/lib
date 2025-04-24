<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToListInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToBoolInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToFloatInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIndexInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToObjectInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIntegerInterface;
use Gzhegow\Lib\Modules\Php\CallableParser\CallableParser;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIterableInterface;
use Gzhegow\Lib\Modules\Php\DebugBacktracer\DebugBacktracer;
use Gzhegow\Lib\Modules\Php\CallableParser\CallableParserInterface;
use Gzhegow\Lib\Modules\Php\DebugBacktracer\DebugBacktracerInterface;


class PhpModule
{
    /**
     * @var CallableParserInterface
     */
    protected $callableParser;
    /**
     * @var DebugBacktracerInterface
     */
    protected $debugBacktracer;

    /**
     * @var class-string<\LogicException|\RuntimeException>
     */
    protected $throwableClass = RuntimeException::class;


    public function newCallableParser() : CallableParserInterface
    {
        return new CallableParser();
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
            ?? new CallableParser();
    }


    public function newDebugBacktracer() : DebugBacktracerInterface
    {
        return new DebugBacktracer();
    }

    public function cloneDebugBacktracer() : DebugBacktracerInterface
    {
        return clone $this->debugBacktracer();
    }

    public function debugBacktracer(?DebugBacktracerInterface $debugBacktracer = null) : DebugBacktracerInterface
    {
        return $this->debugBacktracer = null
            ?? $debugBacktracer
            ?? $this->debugBacktracer
            ?? new DebugBacktracer();
    }


    /**
     * @param array|\Countable|null $result
     */
    public function type_countable(&$result, $value) : bool
    {
        $result = null;

        if (PHP_VERSION_ID >= 70300) {
            if (is_countable($value)) {
                $result = $value;

                return true;
            }

            return false;
        }

        if (is_array($value)) {
            $result = $value;

            return true;
        }

        if ($value instanceof \Countable) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param \Countable|null $result
     */
    public function type_countable_object(&$result, $value) : bool
    {
        $result = null;

        if (PHP_VERSION_ID >= 70300) {
            if (is_object($value) && is_countable($value)) {
                $result = $value;

                return true;
            }

            return false;
        }

        if ($value instanceof \Countable) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param array|\Countable|null $result
     */
    public function type_sizeable(&$result, $value) : bool
    {
        $result = null;

        if ($this->type_countable($countable, $value)) {
            $result = $value;

            return true;
        }

        if (Lib::str()->type_string($string, $value)) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function type_struct_exists(&$result, $value, ?int $flags = null)
    {
        $result = null;

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
                    $result = $class;

                    return true;
                }

            } else {
                if (class_exists($class)) {
                    $result = $class;

                    return true;
                }
            }
        }

        if ($_flags & _PHP_STRUCT_TYPE_ENUM) {
            if (PHP_VERSION_ID >= 80100) {
                if (enum_exists($class)) {
                    $result = $class;

                    return true;
                }
            }
        }

        if (! $isObject) {
            if ($_flags & _PHP_STRUCT_TYPE_INTERFACE) {
                if (interface_exists($class)) {
                    $result = $class;

                    return true;
                }
            }

            if ($_flags & _PHP_STRUCT_TYPE_TRAIT) {
                if (trait_exists($class)) {
                    $result = $class;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function type_struct(&$result, $value, ?int $flags = null) : bool
    {
        $result = null;

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

        $class = null;
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
                $result = $class;

                return true;
            }
        }

        if ($isExistsFalse || $isExistsIgnore) {
            $isValid = (bool) preg_match(
                '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/',
                $class
            );

            if ($isValid) {
                $result = $class;

                return true;
            }
        }

        return false;
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function type_struct_class(&$result, $value, ?int $flags = null) : bool
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

        return $this->type_struct($result, $value, $_flags);
    }

    /**
     * @param class-string|null $result
     */
    public function type_struct_interface(&$result, $value, ?int $flags = null) : bool
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

        return $this->type_struct($result, $value, $_flags);
    }

    /**
     * @param class-string|null $result
     */
    public function type_struct_trait(&$result, $value, ?int $flags = null) : bool
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

        return $this->type_struct($result, $value, $_flags);
    }

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function type_struct_enum(&$result, $value, ?int $flags = null) : bool
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

        return $this->type_struct($result, $value, $_flags);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function type_struct_fqcn(&$result, $value, ?int $flags = null) : bool
    {
        $result = null;

        if (! $this->type_struct($_value, $value, $flags)) {
            return false;
        }

        $_value = '\\' . $_value;

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_struct_namespace(&$result, $value, ?int $flags = null) : bool
    {
        $result = null;

        if (! $this->type_struct($_value, $value, $flags)) {
            return false;
        }

        $_value = $this->dirname($_value, '\\');
        if (null === $_value) {
            return false;
        }

        $result = $value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_struct_basename(&$result, $value, ?int $flags = null) : bool
    {
        $result = null;

        if (! $this->type_struct($_value, $value, $flags)) {
            return false;
        }

        $_value = $this->basename($_value, '\\');

        if (null !== $_value) {
            $result = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param resource|null $result
     */
    public function type_resource(&$result, $value) : bool
    {
        $result = null;

        if (
            is_resource($value)
            || ('resource (closed)' === gettype($value))
        ) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param resource|null $result
     */
    public function type_resource_opened(&$result, $value) : bool
    {
        $result = null;

        if (is_resource($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param resource|null $result
     */
    public function type_resource_closed(&$result, $value) : bool
    {
        $result = null;

        if ('resource (closed)' === gettype($value)) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @template-covariant T of \UnitEnum
     *
     * @param T|null               $result
     * @param T|int|string         $value
     * @param class-string<T>|null $enumClass
     *
     * @return class-string|null
     */
    public function type_enum_case(&$result, $value, ?string $enumClass = null) : bool
    {
        $result = null;

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
                $result = $value;

                return true;
            }
        }

        if (! $hasEnumClass) {
            return false;
        }

        if (! (is_int($value) || is_string($value))) {
            return false;
        }

        $enumCase = null;
        try {
            $enumCase = $enumClass::tryFrom($value);
        }
        catch ( \Throwable $e ) {
        }

        if (null !== $enumCase) {
            $result = $enumCase;

            return true;
        }

        return false;
    }


    /**
     * > метод не всегда возвращает callable, поскольку массив [ 'class', 'method' ] не является callable, если метод публичный
     * > используйте type_callable_array, если собираетесь вызывать метод
     *
     * @param array{ 0: class-string, 1: string }|null $result
     */
    public function type_method_array(&$result, $value) : bool
    {
        return $this->callableParser()->typeMethodArray($result, $value);
    }

    /**
     * > метод не всегда возвращает callable, поскольку строка 'class->method' не является callable
     * > используйте type_callable_string, если собираетесь вызывать метод
     *
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_method_string(&$result, $value, array $refs = []) : bool
    {
        return $this->callableParser()->typeMethodString($result, $value, $refs);
    }


    /**
     * > в версиях PHP до 8.0.0 публичный метод считался callable, если его проверить даже на имени класса
     * > при этом вызвать MyClass::publicMethod было нельзя, т.к. вызываемым является только MyClass::publicStaticMethod
     *
     * @param callable|null $result
     * @param string|object $newScope
     */
    public function type_callable(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallable($result, $value, $newScope);
    }


    /**
     * @param callable|\Closure|object|null $result
     */
    public function type_callable_object(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableObject($result, $value, $newScope);
    }

    /**
     * @param callable|object|null $result
     */
    public function type_callable_object_closure(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableObjectClosure($result, $value, $newScope);
    }

    /**
     * @param callable|object|null $result
     */
    public function type_callable_object_invokable(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableObjectInvokable($result, $value, $newScope);
    }


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function type_callable_array(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableArray($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function type_callable_array_method(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableArrayMethod($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $result
     * @param string|object                                     $newScope
     */
    public function type_callable_array_method_static(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableArrayMethodStatic($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object, 1: string }|null $result
     * @param string|object                               $newScope
     */
    public function type_callable_array_method_non_static(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableArrayMethodNonStatic($result, $value, $newScope);
    }


    /**
     * @param callable-string|null $result
     */
    public function type_callable_string(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableString($result, $value, $newScope);
    }

    /**
     * @param callable-string|null $result
     */
    public function type_callable_string_function(&$result, $value) : bool
    {
        return $this->callableParser()->typeCallableStringFunction($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function type_callable_string_function_internal(&$result, $value) : bool
    {
        return $this->callableParser()->typeCallableStringFunctionInternal($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function type_callable_string_function_non_internal(&$result, $value) : bool
    {
        return $this->callableParser()->typeCallableStringFunctionNonInternal($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function type_callable_string_method_static(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callableParser()->typeCallableStringMethodStatic($result, $value, $newScope);
    }


    public function is_windows() : bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public function is_terminal() : bool
    {
        return in_array(\PHP_SAPI, [ 'cli', 'phpdbg' ]);
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
     * @return callable|null
     */
    public function get_error_handler() // : ?callable
    {
        $handler = set_error_handler(static function () { });
        restore_error_handler();

        return $handler;
    }

    /**
     * @return callable|null
     */
    public function get_exception_handler() // : ?callable
    {
        $handler = set_exception_handler(static function () { });
        restore_exception_handler();

        return $handler;
    }


    public function to_bool($value, array $options = []) : bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value instanceof ToBoolInterface) {
            return $value->toBool($options);
        }

        if (
            (null === $value)
            || (is_float($value) && is_nan($value))
            || (Lib::type()->is_nil($value))
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

        if (
            (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->is_nil($value))
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

        if (
            (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            // || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->is_nil($value))
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

        if (
            (null === $value)
            // || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->is_nil($value))
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

        if (
            (null === $value)
            // || ('' === $value)
            // || (is_bool($value))
            // || (is_array($value))
            || (is_float($value) && (! is_nan($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->is_nil($value))
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

        if (
            (null === $value)
            // || ('' === $value)
            // || (is_bool($value))
            // || (is_array($value))
            || (is_float($value) && (! is_nan($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->is_nil($value))
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

        $fnArgs = $hasAssert
            ? $this->function_args([ $fnAssertValueKey => null ], $fnAssertArgs)
            : [];

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


    public function debug_backtrace(
        ?int $options = null, ?int $limit = null,
        ?string $fileRoot = null
    ) : DebugBacktracerInterface
    {
        $theDebugBacktracer = $this->cloneDebugBacktracer();

        if (null !== $options) $theDebugBacktracer->options($options);
        if (null !== $limit) $theDebugBacktracer->limit($limit);
        if (null !== $fileRoot) $theDebugBacktracer->rootDirectory($fileRoot);

        return $theDebugBacktracer;
    }


    public function microtime($date = null) : string
    {
        $decimalPoint = Lib::type()->the_decimal_point();

        if (null === $date) {
            $mt = microtime();

            [ $msec, $sec ] = explode(' ', $mt, 2);

            $msec = substr($msec, 2, 6);
            $msec = str_pad($msec, 6, '0');

        } elseif (is_a($date, '\DateTimeInterface')) {
            $sec = $date->format('s');

            $msec = $date->format('u');
            $msec = substr($msec, 0, 6);
            $msec = str_pad($msec, 6, '0');

        } else {
            throw new LogicException(
                [ 'The `date` must be instance of \DateTimeInterface', $date ]
            );
        }

        $result = ''
            . $sec
            . $decimalPoint
            . $msec;

        return $result;
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
     * @param mixed $data
     */
    public function serialize($data) : ?string
    {
        error_clear_last();

        try {
            $result = serialize($data);
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        if (error_get_last()) {
            $result = null;
        }

        return $result;
    }

    /**
     * @return mixed|null
     */
    public function unserialize(string $data) // : mixed|null
    {
        error_clear_last();

        try {
            $result = unserialize($data);
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        if (error_get_last()) {
            $result = null;
        }

        if (is_object($result) && (get_class($result) === '__PHP_Incomplete_Class')) {
            $result = null;
        }

        return $result;
    }


    /**
     * @param callable $fn
     */
    public function fn($fn, array $args = []) : \Closure
    {
        return function (...$arguments) use ($fn, $args) {
            $_args = array_merge($arguments, $args);

            return call_user_func_array($fn, $_args);
        };
    }

    /**
     * @param callable $fn
     */
    public function fn_not($fn, array $args = []) : \Closure
    {
        return function (...$arguments) use ($fn, $args) {
            $_args = array_merge($arguments, $args);

            return ! call_user_func_array($fn, $_args);
        };
    }


    public function function_args(array ...$arrays) : array
    {
        $args = [];

        $hasInt = false;
        $hasString = false;
        $argsSeenIndex = [];
        for ( $i = 0; $i < count($arrays); $i++ ) {
            if ([] === $arrays[ $i ]) {
                continue;
            }

            foreach ( array_keys($arrays[ $i ]) as $ii ) {
                if (isset($argsSeenIndex[ $ii ])) {
                    throw new LogicException(
                        [
                            'Arguments intersection detected',
                            $ii,
                            $arrays[ $i ],
                            $arrays,
                        ]
                    );
                }

                if ((! $hasString) && (is_string($ii))) {
                    $hasString = true;

                } elseif ((! $hasInt) && is_int($ii)) {
                    $hasInt = true;
                }

                if ($hasString && $hasInt) {
                    throw new LogicException(
                        [
                            'The `args` should contain arguments of single type: string or int',
                            $args,
                        ]
                    );
                }

                $argsSeenIndex[ $ii ] = true;
            }

            $args += $arrays[ $i ];
        }

        if ([] !== $args) {
            if ($hasInt) {
                $args[] = null;

                $max = array_key_last($args);

                unset($args[ $max ]);

                for ( $i = 0; $i < $max; $i++ ) {
                    if (! array_key_exists($i, $args)) {
                        $args[ $i ] = null;
                    }
                }

                ksort($args);

            } elseif ($hasString) {
                if (PHP_VERSION_ID < 80000) {
                    throw new RuntimeException(
                        [ 'PHP does not support arguments with string keys', $args ]
                    );
                }
            }
        }

        return $args;
    }

    /**
     * > встроенные функции в php такие как strlen() требуют строгое число аргументов
     * > стоит передать туда больше аргументов - сразу throw/trigger_error
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     * @throws \RuntimeException
     */
    public function call_user_func($fn, ...$args)
    {
        $isMaybeInternalFunction = is_string($fn) && function_exists($fn);

        if (! $isMaybeInternalFunction) {
            $result = call_user_func($fn, ...$args);

        } else {
            $ex = null;
            $eMsg = null;

            $before = error_reporting(0);
            error_clear_last();

            try {
                $result = call_user_func($fn, ...$args);
            }
            catch ( \Throwable $ex ) {
                $eMsg = $ex->getMessage();
            }

            $err = error_get_last();
            error_reporting($before);

            if (null !== $err) {
                $eMsg = $err[ 'message' ];
            }

            if (null !== $eMsg) {
                $eMsgKnownList = [
                    '() expects exactly '  => 19,
                    '() expects at most '  => 19,
                    '() expects at least ' => 20,
                ];

                $isKnown = false;
                foreach ( $eMsgKnownList as $eSubstr => $eSubstrLen ) {
                    if (false !== ($pos = strpos($eMsg, $eSubstr))) {
                        $isKnown = true;

                        break;
                    }
                }

                if ($isKnown) {
                    $max = (int) substr($eMsg, $pos + $eSubstrLen);

                    array_splice($args, $max);

                    $result = call_user_func($fn, ...$args);
                }

                if ($ex && ! $isKnown) {
                    throw $ex;
                }
            }
        }

        return $result;
    }

    /**
     * > встроенные функции в php такие как strlen() требуют строгое число аргументов
     * > стоит передать туда больше аргументов - сразу throw/trigger_error
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     * @throws \RuntimeException
     */
    public function call_user_func_array($fn, array $args, ?array &$argsNew = null)
    {
        $argsNew = null;

        $isMaybeInternalFunction = is_string($fn) && function_exists($fn);

        $_args = $this->function_args($args);

        if (! $isMaybeInternalFunction) {
            $result = call_user_func_array($fn, $_args);

        } elseif (! ($isIntKeys = array_key_exists(0, $_args))) {
            $result = call_user_func_array($fn, $_args);

        } else {
            $ex = null;
            $eMsg = null;

            $before = error_reporting(0);
            error_clear_last();

            try {
                $result = call_user_func_array($fn, $_args);
            }
            catch ( \Throwable $ex ) {
                $eMsg = $ex->getMessage();
            }

            $err = error_get_last();
            error_reporting($before);

            if (null !== $err) {
                $eMsg = $err[ 'message' ];
            }

            if (null !== $eMsg) {
                $eMsgKnownList = [
                    '() expects exactly '  => 19,
                    '() expects at most '  => 19,
                    '() expects at least ' => 20,
                ];

                $isKnown = false;
                foreach ( $eMsgKnownList as $eSubstr => $eSubstrLen ) {
                    if (false !== ($pos = strpos($eMsg, $eSubstr))) {
                        $isKnown = true;

                        break;
                    }
                }

                if ($ex && ! $isKnown) {
                    throw $ex;
                }

                if ($isKnown) {
                    $max = (int) substr($eMsg, $pos + $eSubstrLen);

                    array_splice($_args, $max);

                    $result = call_user_func_array($fn, $_args);
                }
            }
        }

        $argsNew = $_args;

        return $result;
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

        $separator = Lib::parse()->char($separator) ?? '/';
        $dot = Lib::parse()->char($dot) ?? '.';

        if ('/' === $dot) {
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

                $dirname = str_replace('/', $separator, $dirname);

                $dirname = ('' !== $dirname) ? $dirname : null;
            }

            $pi[ 'dirname' ] = $dirname;
        }

        if ($_flags & _PHP_PATHINFO_BASENAME) {
            $pi[ 'basename' ] = ('' !== $basename) ? $basename : null;
        }

        if (
            ($_flags & _PHP_PATHINFO_FILENAME)
            || ($_flags & _PHP_PATHINFO_EXTENSION)
            || ($_flags & _PHP_PATHINFO_FILE)
            || ($_flags & _PHP_PATHINFO_EXTENSIONS)
        ) {
            $filename = $basename;

            $split = explode($dot, $basename) + [ '', '' ];

            $file = array_shift($split);

            if ($_flags & _PHP_PATHINFO_EXTENSION) {
                $extension = end($split);

                if ('' === $extension) {
                    $pi[ 'extension' ] = null;

                } else {
                    $pi[ 'extension' ] = $extension;

                    $filename = basename($basename, "{$dot}{$extension}");
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
                    $extensions = implode($dot, $split);
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

        $levels = $levels ?? 1;

        $separator = Lib::parse()->char($separator) ?? '/';

        if ($levels < 1) {
            throw new LogicException(
                [ 'The `levels` should be GTE 1', $levels ]
            );
        }

        $normalized = $this->path_normalize($path, '/');

        $dirname = ltrim($normalized, '/');

        if (false === strpos($dirname, '/')) {
            $dirname = null;

        } else {
            $dirname = dirname($dirname, $levels);

            $dirname = str_replace('/', $separator, $dirname);
        }

        return ('' !== $dirname) ? $dirname : null;
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

        $dot = Lib::parse()->char($dot) ?? '.';

        $normalized = $this->path_normalize($path, '/');

        $basename = basename($normalized);

        $split = explode($dot, $basename) + [ '', '' ];

        $extension = end($split);

        $filename = basename($basename, "{$dot}{$extension}");

        return ('' !== $filename) ? $filename : null;
    }

    public function extension(string $path, ?string $dot = null) : ?string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        $dot = Lib::parse()->char($dot) ?? '.';

        $normalized = $this->path_normalize($path, '/');

        $basename = basename($normalized);

        $split = explode($dot, $basename) + [ '', '' ];

        $extension = end($split);

        return ('' !== $extension) ? $extension : null;
    }

    public function file(string $path, ?string $dot = null) : ?string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        $dot = Lib::parse()->char($dot) ?? '.';

        $normalized = $this->path_normalize($path, '/');

        $basename = basename($normalized);

        [ $file ] = explode($dot, $basename, 2);

        return ('' !== $file) ? $file : null;
    }

    public function extensions(string $path, ?string $dot = null) : ?string
    {
        if ('' === $path) {
            throw new LogicException(
                [ 'The `path` should be non-empty string' ]
            );
        }

        $dot = Lib::parse()->char($dot) ?? '.';

        if ('/' === $dot) {
            throw new LogicException(
                [ 'The `dot` should not be `/` sign' ]
            );
        }

        $normalized = $this->path_normalize($path, '/');

        $basename = basename($normalized);

        $split = explode($dot, $basename) + [ 1 => '' ];

        array_shift($split);

        $extensions = null;
        if ([] !== $split) {
            $extensions = implode($dot, $split);
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

        $separator = Lib::parse()->char($separator) ?? '/';

        $normalized = $path;

        $separators = [
            '\\'                => true,
            DIRECTORY_SEPARATOR => true,
            $separator          => true,
        ];
        $separators = array_keys($separators);

        $normalized = str_replace($separators, $separator, $path);

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

        $separator = Lib::parse()->char($separator) ?? '/';
        $dot = Lib::parse()->char($dot) ?? '.';

        $pathNormalized = $this->path_normalize($path, '/');

        $root = ($pathNormalized[ 0 ] === '/')
            ? $separator
            : '';

        $segments = trim($pathNormalized, '/');
        $segments = explode('/', $segments);

        $segmentsNew = [];
        foreach ( $segments as $segment ) {
            if (
                ('' === $segment)
                || ($dot === $segment)
            ) {
                continue;
            }

            if ($segment === "{$dot}{$dot}") {
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

        $pathResolved = $root . implode($separator, $segmentsNew);

        if ('' === $pathResolved) {
            throw new RuntimeException(
                [
                    'Result path should be non-empty string',
                    $path,
                    $separator,
                    $dot,
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

        $separator = Lib::parse()->char($separator) ?? '/';

        $pathResolved = $this->path_resolve($path, $separator, $dot);

        $rootNormalized = $this->path_normalize($root, $separator);
        $rootNormalized = rtrim($rootNormalized, $separator);

        $status = Lib::str()->str_starts(
            $pathResolved, ($rootNormalized . $separator),
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
                    $separator,
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

        $separator = Lib::parse()->char($separator) ?? '/';

        $relativeNormalized = $this->path_normalize($relative, $separator);

        $isRoot = ($separator === $relativeNormalized[ 0 ]);

        if ($isRoot) {
            $absoluteNormalized = $relativeNormalized;

        } else {
            $currentNormalized = $this->path_normalize($current, $separator);

            $absoluteNormalized = $currentNormalized . $separator . $relativeNormalized;
        }

        $absoluteResolved = $this->path_resolve($absoluteNormalized, $separator, $dot);

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

        $dot = Lib::parse()->char($dot) ?? '.';

        $isDot = ($dot === $path[ 0 ]);

        if ($isDot) {
            $pathResolved = $this->path_absolute($path, $current, $separator, $dot);

        } else {
            $pathResolved = $this->path_normalize($path, $separator);
        }

        return $pathResolved;
    }


    /**
     * @param class-string<\LogicException|\RuntimeException>|null $throwableClass
     *
     * @return class-string<\LogicException|\RuntimeException>
     */
    public function static_throwable_class(?string $throwableClass = null) : string
    {
        if (null !== $throwableClass) {
            if (! (false
                || is_subclass_of($throwableClass, \LogicException::class)
                || is_subclass_of($throwableClass, \RuntimeException::class)
            )) {
                throw new LogicException(
                    [
                        'The `throwableClass` should be class-string that is subclass one of: '
                        . implode('|', [
                            \LogicException::class,
                            \RuntimeException::class,
                        ]),
                        $throwableClass,
                    ]
                );
            }

            $last = $this->throwableClass;

            $current = $throwableClass;

            $result = $last;
        }

        $result = $result ?? $this->throwableClass;

        return $result;
    }

    public function throwable_args(...$throwableArgs) : array
    {
        $len = count($throwableArgs);

        $messageList = [];
        $messageDataList = [];
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

                $messageString = isset($messageData[ 0 ])
                    ? (string) $messageData[ 0 ]
                    : '';

                if ('' !== $messageString) {
                    unset($messageData[ 0 ]);

                    $messageList[ $i ] = $messageString;
                }

                $messageDataList[ $i ] = $messageData;

                continue;
            }

            if ($arg instanceof \Throwable) {
                $previousList[ $i ] = $arg;

                $messageList[ $i ] = $arg->getMessage();
                $codeIntegerList[ $i ] = $arg->getCode();

                continue;
            }

            $__unresolved[ $i ] = $arg;
        }

        for ( $i = 0; $i < $len; $i++ ) {
            if (isset($messageList[ $i ])) {
                if (preg_match('/^[a-z](?!.*\s)/i', $messageList[ $i ])) {
                    $codeStringList[ $i ] = strtoupper($messageList[ $i ]);
                }
            }
        }

        $result = [];

        $result[ 'messageList' ] = $messageList;
        $result[ 'messageDataList' ] = $messageDataList;

        $result[ 'codeIntegerList' ] = $codeIntegerList;
        $result[ 'codeStringList' ] = $codeStringList;

        $result[ 'previousList' ] = $previousList;

        $messageDataList = $messageDataList ?? [];

        $message = $messageList ? reset($messageList) : null;
        $code = $codeIntegerList ? reset($codeIntegerList) : null;
        $codeString = $codeStringList ? reset($codeStringList) : null;
        $previous = $previousList ? reset($previousList) : null;

        $messageData = $messageDataList
            ? array_replace(...$messageDataList)
            : [];

        $messageObject = (object) ([ $message ] + $messageData);

        $result[ 'message' ] = $message ?? '';
        $result[ 'messageData' ] = $messageData;
        $result[ 'messageObject' ] = $messageObject;

        $result[ 'code' ] = $code ?? -1;
        $result[ 'codeString' ] = $codeString;

        $result[ 'previous' ] = $previous;

        $result[ '__unresolved' ] = $__unresolved;

        return $result;
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function throw(?array $trace, $throwableOrArg, ...$throwableArgs)
    {
        if (
            ($throwableOrArg instanceof \LogicException)
            || ($throwableOrArg instanceof \RuntimeException)
        ) {
            throw $throwableOrArg;
        }

        array_unshift($throwableArgs, $throwableOrArg);

        $throwableClass = $this->static_throwable_class();

        if (null === $trace) {
            $trace = property_exists($throwableClass, 'trace')
                ? debug_backtrace()
                : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        }

        $_throwableArgs = $this->throwable_args(...$throwableArgs);
        $_throwableArgs[ 'file' ] = $trace[ 0 ][ 'file' ] ?? '{file}';
        $_throwableArgs[ 'line' ] = $trace[ 0 ][ 'line' ] ?? 0;
        $_throwableArgs[ 'trace' ] = $trace;

        $exceptionArgs = [];
        $exceptionArgs[] = $_throwableArgs[ 'message' ] ?? null;
        $exceptionArgs[] = $_throwableArgs[ 'code' ] ?? null;
        $exceptionArgs[] = $_throwableArgs[ 'previous' ] ?? null;

        $e = new $throwableClass(...$exceptionArgs);

        foreach ( $_throwableArgs as $key => $value ) {
            if (! property_exists($e, $key)) {
                unset($_throwableArgs[ $key ]);
            }
        }

        $fn = (function () use (&$_throwableArgs) {
            foreach ( $_throwableArgs as $key => $value ) {
                $this->{$key} = $value;
            }
        })->bindTo($e, $e);

        $fn();

        throw $e;
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function throw_new(?array $trace, ...$throwableArgs)
    {
        $throwableClass = $this->static_throwable_class();

        if (null === $trace) {
            $trace = property_exists($throwableClass, 'trace')
                ? debug_backtrace()
                : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        }

        $_throwableArgs = $this->throwable_args(...$throwableArgs);
        $_throwableArgs[ 'file' ] = $trace[ 0 ][ 'file' ] ?? '{file}';
        $_throwableArgs[ 'line' ] = $trace[ 0 ][ 'line' ] ?? 0;
        $_throwableArgs[ 'trace' ] = $trace;

        $exceptionArgs = [];
        $exceptionArgs[] = $_throwableArgs[ 'message' ] ?? null;
        $exceptionArgs[] = $_throwableArgs[ 'code' ] ?? null;
        $exceptionArgs[] = $_throwableArgs[ 'previous' ] ?? null;

        $e = new $throwableClass(...$exceptionArgs);

        foreach ( $_throwableArgs as $key => $value ) {
            if (! property_exists($e, $key)) {
                unset($_throwableArgs[ $key ]);
            }
        }

        $fn = (function () use (&$_throwableArgs) {
            foreach ( $_throwableArgs as $key => $value ) {
                $this->{$key} = $value;
            }
        })->bindTo($e, $e);

        $fn();

        throw $e;
    }


    /**
     * @return object{ stack: array }
     */
    public function errors() : object
    {
        static $current;

        $current = $current
            ?? new class {
                /**
                 * @var object[]
                 */
                public $stack = [];
            };

        return $current;
    }

    /**
     * @return object{ list: array }
     */
    public function errors_current() : ?object
    {
        $stack = $this->errors();

        $errors = ([] !== $stack->stack)
            ? end($stack->stack)
            : null;

        return $errors;
    }

    /**
     * @return object{ list: array }
     */
    public function errors_new() : object
    {
        $errors = new class {
            /**
             * @var array
             */
            public $list = [];
        };

        return $errors;
    }

    /**
     * @return object{ list: array }
     */
    public function errors_start(?object &$errors = null) : object
    {
        $stack = $this->errors();

        $errors = $this->errors_new();

        $stack->stack[] = $errors;

        return $errors;
    }

    public function errors_end(?object $until) : array
    {
        $stack = $this->errors();

        $errors = $this->errors_new();

        while ( count($stack->stack) ) {
            $current = array_pop($stack->stack);

            foreach ( $current->list as $error ) {
                $errors->list[] = $error;
            }

            if ($current === $until) {
                break;
            }
        }

        return $errors->list;
    }

    public function error($error, $result = null) // : mixed
    {
        $current = $this->errors_current();

        if (null !== $current) {
            $current->list[] = $error;
        }

        return $result;
    }
}
