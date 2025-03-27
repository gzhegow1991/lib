<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToListInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToFloatInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToObjectInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIntegerInterface;
use Gzhegow\Lib\Modules\Php\CallableParser\CallableParser;
use Gzhegow\Lib\Modules\Php\DebugBacktracer\DebugBacktracer;
use Gzhegow\Lib\Modules\Php\CallableParser\CallableParserInterface;


class PhpModule
{
    /**
     * @var CallableParserInterface
     */
    protected $callableParser;

    /**
     * @var class-string<\LogicException|\RuntimeException>
     */
    protected $throwableClass = LogicException::class;


    public function callable_parser(CallableParserInterface $callableParser = null) : CallableParserInterface
    {
        return $this->callableParser = null
            ?? $callableParser
            ?? $this->callableParser
            ?? new CallableParser();
    }


    /**
     * @param \DateTimeInterface|null   $result
     *
     * @param string|\DateTimeZone|null $timezoneIfParsed
     * @param string|string[]|null      $formats
     */
    public function type_date_interface(&$result, $value, $timezoneIfParsed = null, $formats = null) : bool
    {
        $result = null;

        if ($value instanceof \DateTimeInterface) {
            $result = $value;

            return true;
        }

        if ($this->type_date($date, $value, $timezoneIfParsed, $formats)) {
            $result = $date;

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null            $result
     *
     * @param string|\DateTimeZone|null $timezoneIfParsed
     * @param string|string[]|null      $formats
     */
    public function type_date(&$result, $value, $timezoneIfParsed = null, $formats = null) : bool
    {
        $result = null;

        $hasTimezoneIfParsed = (null !== $timezoneIfParsed);
        $hasFormats = (null !== $formats);

        $_timezoneIfParsed = null;
        if ($hasTimezoneIfParsed) {
            if (! $this->type_timezone($_timezoneIfParsed, $timezoneIfParsed)) {
                throw new LogicException(
                    [ 'The `timezoneIfParsed` should be null or valid \DateTimeZone', $timezoneIfParsed ]
                );
            }
        }

        if ($value instanceof \DateTime) {
            $result = $value;

            return true;

        } elseif ($value instanceof \DateTimeImmutable) {
            $date = \DateTime::createFromImmutable($value);

            $result = $date;

            return true;
        }

        if ($hasFormats) {
            $_formats = $this->to_list($formats);

            foreach ( $_formats as $i => $format ) {
                if (! (is_string($format) && ('' !== $format))) {
                    throw new LogicException(
                        [
                            'Each of `formats` should be non-empty string',
                            $format,
                            $i,
                        ]
                    );
                }
            }
        }

        $date = null;

        if ($hasFormats) {
            $formatFirst = array_shift($_formats);

            foreach ( $formats as $format ) {
                try {
                    $date = \DateTime::createFromFormat(
                        $formatFirst,
                        $value,
                        $_timezoneIfParsed
                    );
                }
                catch ( \Throwable $e ) {
                }

                if ($date) {
                    $result = $date;

                    return true;
                }
            }
        }

        try {
            $date = new \DateTime($value, $_timezoneIfParsed);

            $result = $date;

            return true;
        }
        catch ( \Throwable $e ) {
        }

        if ($hasFormats && count($_formats)) {
            foreach ( $_formats as $format ) {
                try {
                    $date = \DateTime::createFromFormat(
                        $formatFirst,
                        $value,
                        $_timezoneIfParsed
                    );
                }
                catch ( \Throwable $e ) {
                }

                if ($date) {
                    $result = $date;

                    break;
                }
            }
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null   $result
     *
     * @param string|\DateTimeZone|null $timezoneIfParsed
     * @param string|string[]|null      $formats
     */
    public function type_date_immutable(&$result, $value, $timezoneIfParsed = null, $formats = null) : bool
    {
        $result = null;

        $hasTimezoneIfParsed = (null !== $timezoneIfParsed);
        $hasFormats = (null !== $formats);

        $_timezoneIfParsed = null;
        if ($hasTimezoneIfParsed) {
            if (! $this->type_timezone($_timezoneIfParsed, $timezoneIfParsed)) {
                throw new LogicException(
                    [ 'The `timezoneIfParsed` should be null or valid \DateTimeZone', $timezoneIfParsed ]
                );
            }
        }

        if ($value instanceof \DateTimeImmutable) {
            $result = $value;

            return true;

        } elseif ($value instanceof \DateTime) {
            $dateImmutable = \DateTimeImmutable::createFromMutable($value);

            $result = $dateImmutable;

            return true;
        }

        if ($hasFormats) {
            $_formats = $this->to_list($formats);

            foreach ( $_formats as $i => $format ) {
                if (! (is_string($format) && ('' !== $format))) {
                    throw new LogicException(
                        [
                            'Each of `formats` should be non-empty string',
                            $format,
                            $i,
                        ]
                    );
                }
            }
        }

        $dateImmutable = null;

        if ($hasFormats) {
            $formatFirst = array_shift($_formats);

            foreach ( $formats as $format ) {
                try {
                    $dateImmutable = \DateTimeImmutable::createFromFormat(
                        $formatFirst,
                        $value,
                        $_timezoneIfParsed
                    );
                }
                catch ( \Throwable $e ) {
                }

                if ($dateImmutable) {
                    $result = $dateImmutable;

                    return true;
                }
            }
        }

        try {
            $dateImmutable = new \DateTimeImmutable($value, $_timezoneIfParsed);

            $result = $dateImmutable;

            return true;
        }
        catch ( \Throwable $e ) {
        }

        if ($hasFormats && count($_formats)) {
            foreach ( $_formats as $format ) {
                try {
                    $dateImmutable = \DateTimeImmutable::createFromFormat(
                        $formatFirst,
                        $value,
                        $_timezoneIfParsed
                    );
                }
                catch ( \Throwable $e ) {
                }

                if ($dateImmutable) {
                    $result = $dateImmutable;

                    break;
                }
            }
        }

        return false;
    }

    /**
     * @param \DateTimeZone|null $result
     */
    public function type_timezone(&$result, $value) : bool
    {
        $result = null;

        if ($value instanceof \DateTimeZone) {
            $result = $value;

            return true;
        }

        try {
            $timezone = new \DateTimeZone($value);

            $result = $timezone;

            return true;
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }

    /**
     * @param \DateInterval|null $result
     */
    public function type_interval(&$result, $value) : bool
    {
        $result = null;

        if ($value instanceof \DateInterval) {
            $result = $value;

            return true;
        }

        try {
            $interval = new \DateInterval($value);

            $result = $interval;

            return true;
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }


    /**
     * @param array|\Countable|null $result
     */
    public function type_countable(&$result, $value) : bool
    {
        $result = null;

        if ($value instanceof \Countable) {
            $result = $value;

            return true;
        }

        if (PHP_VERSION_ID < 70300) {
            return false;
        }

        if (! is_countable($value)) {
            return false;
        }

        $result = $value;

        return true;
    }


    /**
     * @param class-string|null $result
     *
     * @param callable          ...$fnExistsList
     */
    public function type_struct(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        $result = null;

        $useRegex = $useRegex ?? false;
        $fnExistsList = $fnExistsList ?: [
            'class_exists',
            'interface_exists',
            'trait_exists',
        ];

        if (is_object($value)) {
            $result = ltrim(get_class($value), '\\');

            return true;
        }

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        $_value = ltrim($_value, '\\');

        foreach ( $fnExistsList as $fn ) {
            if (call_user_func($fn, $_value)) {
                $result = $_value;

                return $result;
            }
        }

        if ($useRegex) {
            if (! preg_match(
                '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/',
                $_value
            )) {
                return false;
            }
        }

        $result = $_value;

        return true;
    }

    /**
     * @param class-string|null $result
     *
     * @return class-string|null
     */
    public function type_struct_class(&$result, $value, bool $useRegex = null) : bool
    {
        return $this->type_struct($result, $value, $useRegex, 'class_exists');
    }

    /**
     * @param class-string|null $result
     *
     * @return class-string|null
     */
    public function type_struct_interface(&$result, $value, bool $useRegex = null) : bool
    {
        return $this->type_struct($result, $value, $useRegex, 'interface_exists');
    }

    /**
     * @param class-string|null $result
     *
     * @return class-string|null
     */
    public function type_struct_trait(&$result, $value, bool $useRegex = null) : bool
    {
        return $this->type_struct($result, $value, $useRegex, 'trait_exists');
    }


    /**
     * @param class-string|null $result
     *
     * @param callable          ...$fnExistsList
     */
    public function type_struct_fqcn(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        $result = null;

        if (! $this->type_struct($_value, $value, $useRegex, ...$fnExistsList)) {
            return false;
        }

        $_value = '\\' . $_value;

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     *
     * @param callable    ...$fnExistsList
     */
    public function type_struct_namespace(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        $result = null;

        if (! $this->type_struct($_value, $value, $useRegex, ...$fnExistsList)) {
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
     *
     * @param callable    ...$fnExistsList
     */
    public function type_struct_basename(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        $result = null;

        if (! $this->type_struct($_value, $value, $useRegex, ...$fnExistsList)) {
            return false;
        }

        $_value = $this->basename($_value, '\\');
        if (null === $_value) {
            return false;
        }

        $result = $_value;

        return $_value;
    }


    /**
     * @param resource|null $result
     */
    public function type_resource(&$result, $value) : bool
    {
        $result = null;

        if (false
            || is_resource($value)
            || (gettype($value) === 'resource (closed)')
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
     * > метод не всегда возвращает callable, поскольку массив [ 'class', 'method' ] не является callable, если метод публичный
     * > используйте type_callable_array, если собираетесь вызывать метод
     *
     * @param array{ 0: class-string, 1: string }|null $result
     */
    public function type_method_array(&$result, $value) : bool
    {
        return $this->callable_parser()->typeMethodArray($result, $value);
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
        return $this->callable_parser()->typeMethodString($result, $value, $refs);
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
        return $this->callable_parser()->typeCallable($result, $value, $newScope);
    }


    /**
     * @param callable|\Closure|object|null $result
     */
    public function type_callable_object(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableObject($result, $value, $newScope);
    }

    /**
     * @param callable|object|null $result
     */
    public function type_callable_object_closure(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableObjectClosure($result, $value, $newScope);
    }

    /**
     * @param callable|object|null $result
     */
    public function type_callable_object_invokable(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableObjectInvokable($result, $value, $newScope);
    }


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function type_callable_array(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableArray($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function type_callable_array_method(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableArrayMethod($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $result
     * @param string|object                                     $newScope
     */
    public function type_callable_array_method_static(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableArrayMethodStatic($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object, 1: string }|null $result
     * @param string|object                               $newScope
     */
    public function type_callable_array_method_non_static(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableArrayMethodNonStatic($result, $value, $newScope);
    }


    /**
     * @param callable-string|null $result
     */
    public function type_callable_string(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableString($result, $value, $newScope);
    }

    /**
     * @param callable-string|null $result
     */
    public function type_callable_string_function(&$result, $value) : bool
    {
        return $this->callable_parser()->typeCallableStringFunction($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function type_callable_string_function_internal(&$result, $value) : bool
    {
        return $this->callable_parser()->typeCallableStringFunctionInternal($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function type_callable_string_function_non_internal(&$result, $value) : bool
    {
        return $this->callable_parser()->typeCallableStringFunctionNonInternal($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function type_callable_string_method_static(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableStringMethodStatic($result, $value, $newScope);
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


    public function to_int($value, array $options = []) : int
    {
        if ($value instanceof ToIntegerInterface) {
            $_value = $value->toInteger($options);

        } else {
            if (false
                || is_array($value)
                || is_object($value)
                || is_resource($value)
                || (gettype($value) === 'resource (closed)')
            ) {
                throw new LogicException(
                    [
                        'The `value` should not be array, object or resource',
                        $value,
                    ]
                );
            }

            $_value = (int) $value;
        }

        return $_value;
    }

    public function to_float($value, array $options = []) : float
    {
        if ($value instanceof ToFloatInterface) {
            $_value = $value->toFloat($options);

        } else {
            if (false
                || is_array($value)
                || is_object($value)
                || is_resource($value)
                || (gettype($value) === 'resource (closed)')
            ) {
                throw new LogicException(
                    [
                        'The `value` should not be array, object or resource',
                        $value,
                    ]
                );
            }

            $_value = (float) $value;
        }

        return $_value;
    }

    public function to_string($value, array $options = []) : string
    {
        if ($value instanceof ToStringInterface) {
            $_value = $value->toString($options);

        } else {
            if (false
                || is_array($value)
                || is_object($value)
                || is_resource($value)
                || (gettype($value) === 'resource (closed)')
            ) {
                throw new LogicException(
                    [
                        'The `value` should not be array, object or resource',
                        $value,
                    ]
                );
            }

            $_value = (string) $value;
        }

        return $_value;
    }

    public function to_array($value, array $options = []) : array
    {
        if ($value instanceof ToArrayInterface) {
            $_value = $value->toArray($options);

        } else {
            $isObject = is_object($value);
            $isStdclass = $value instanceof \stdClass;

            if ($isObject && ! $isStdclass) {
                throw new LogicException(
                    [
                        'The `value` being the object should be instance of: ' . \stdClass::class,
                        $value,
                    ]
                );
            }

            $_value = (array) $value;
        }

        return $_value;
    }

    public function to_object($value, array $options = []) : \stdClass
    {
        if ($value instanceof ToObjectInterface) {
            $_value = $value->toObject($options);

        } else {
            $isObject = is_object($value);
            $isStdclass = $value instanceof \stdClass;

            if ($isObject && ! $isStdclass) {
                throw new LogicException(
                    [
                        'The `value` being the object should be instance of: ' . \stdClass::class,
                        $value,
                    ]
                );
            }

            $_value = $isStdclass
                ? $value
                : (object) (array) $value;
        }

        return $_value;
    }


    /**
     * @param callable $fnIsForceWrap
     */
    public function to_list($value, $fnIsForceWrap = null, array $options = []) : array
    {
        if (null === $value) {
            throw new LogicException('The `value` should be not null');
        }

        if (is_object($value)) {
            if ($value instanceof ToListInterface) {
                $_value = $value->toList($options);

            } else {
                $_value = [ $value ];
            }

        } elseif (is_array($value)) {
            if ($fnIsForceWrap) {
                $status = call_user_func_array($fnIsForceWrap, $value);

                $_value = $status
                    ? [ $value ]
                    : $value;

            } else {
                $_value = $value;
            }

        } else {
            $_value = (array) $value;
        }

        return $_value;
    }


    public function count($value) : ?int
    {
        if (is_array($value)) {
            return count($value);
        }

        if ($this->type_countable($_value, $value)) {
            return count($_value);
        }

        return null;
    }


    /**
     * @param int[]    $results
     * @param callable $fnCmp
     */
    public function cmp($a, $b, array $results = [ 0 ], $fnCmp = null) : ?int
    {
        $result = $fnCmp
            ? $fnCmp($a, $b)
            : ($a <=> $b);

        if (! in_array($result, $results, true)) {
            return null;
        }

        return $result;
    }


    public function debug_backtrace(int $options = null, int $limit = null) : DebugBacktracer
    {
        $instance = new DebugBacktracer();

        if (null !== $options) $instance->options($options);
        if (null !== $limit) $instance->limit($limit);

        return $instance;
    }


    public function microtime($date = null) : string
    {
        $mt = microtime();

        [ $sec, $msec ] = explode(' ', $mt);

        if (null === $date) {
            $result = ''
                . $sec
                . Lib::type()->the_decimal_point()
                . str_pad($msec, 6, '0');

        } elseif (is_a($date, '\DateTimeInterface')) {
            $result = ''
                . $date->format('s')
                . Lib::type()->the_decimal_point()
                . str_pad($date->format('u'), 6, '0');

        } else {
            throw new LogicException(
                [ 'The `date` must be instance of \DateTimeInterface', $date ]
            );
        }

        return $result;
    }


    /**
     * @param object|class-string $objectOrClass
     *
     * @return class-string[]
     */
    public function class_uses_with_parents($objectOrClass, bool $recursive = null)
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
    public function class_uses($objectOrClass, bool $recursive = null)
    {
        $recursive = $recursive ?? false;

        $className = $objectOrClass;
        if (is_object($objectOrClass)) {
            $className = get_class($objectOrClass);
        }

        $uses = class_uses($className) ?: [];

        if ($recursive) {
            foreach ( $uses as $usesItem ) {
                // ! recursion
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
        bool $public = null, bool $static = null
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
        bool $public = null, bool $static = null
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


    /**
     * > подготавливает аргументы функции согласно переданного массива
     */
    public function function_args(array $args, array $argsOriginal = null) : array
    {
        if (! $args) {
            return [];
        }

        $argsOriginal = $argsOriginal ?? $args;

        $_args = $args;

        // > gzhegow, удаляет из массива все строковые ключи, до 8.0 версии именованные аргументы не поддерживаются
        if (PHP_VERSION_ID < 80000) {
            $_args = array_filter($_args, 'is_int', ARRAY_FILTER_USE_KEY);
        }

        // > gzhegow, sort keys ascending
        ksort($_args);

        // > gzhegow, добавляет цифровой ключ, чтобы понять, до какого ключа нужно заполнять nulls
        end($_args);
        $key = key($_args);
        if (! is_int($key)) {
            $_args[] = null;

            end($_args);
            $key = $extraKey = key($_args);
        }

        // > gzhegow, заполняет nulls все цифровые ключи
        $_args += array_fill(0, $key, null);

        // > gzhegow, удаляет добавленный ранее цифровой ключ
        if (isset($extraKey)) {
            unset($_args[ $extraKey ]);
        }

        // > gzhegow, удаляет автоматические null, которые не были явно переданы пользователем
        foreach ( array_reverse($_args) as $k => $v ) {
            if (null !== $_args[ $k ]) {
                break;

            } elseif (! array_key_exists($k, $argsOriginal)) {
                unset($_args[ $k ]);
            }
        }

        return $_args;
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

            error_clear_last();

            try {
                $result = @call_user_func($fn, ...$args);
            }
            catch ( \Throwable $ex ) {
                $eMsg = $ex->getMessage();
            }

            if ($e = error_get_last()) {
                $eMsg = $e[ 'message' ];
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
    public function call_user_func_array($fn, array $args, array &$argsNew = null)
    {
        $argsNew = null;

        $isMaybeInternalFunction = is_string($fn) && function_exists($fn);

        $_args = $this->function_args($args, $args);

        if (! $isMaybeInternalFunction) {
            $result = call_user_func_array($fn, $_args);

        } else {
            $ex = null;
            $eMsg = null;

            error_clear_last();

            try {
                $result = @call_user_func_array($fn, $_args);
            }
            catch ( \Throwable $ex ) {
                $eMsg = $ex->getMessage();
            }

            if ($e = error_get_last()) {
                $eMsg = $e[ 'message' ];
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

                    $_args = $this->function_args($_args, $args);

                    $result = call_user_func_array($fn, $_args);
                }

                if ($ex && ! $isKnown) {
                    throw $ex;
                }
            }
        }

        $argsNew = $_args;

        return $result;
    }


    /**
     * > поддерживает предварительную замену $separator на '/'
     * > во встроенной функции pathinfo() для двойного расширения будет возвращено только последнее, `image.min.jpg` -> 'jpg`
     *
     * @return array{
     *     dirname?: string|null,
     *     basename?: string,
     *     filename?: string,
     *     extension?: string|null,
     * }
     */
    public function pathinfo(string $path, int $flags = null, string $separator = null, int $levels = null, string $dot = null) : array
    {
        $separator = $separator ?? DIRECTORY_SEPARATOR;
        $levels = $levels ?? 1;
        $flags = $flags ?? (PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_FILENAME | PATHINFO_EXTENSION);
        $dot = $dot ?? '.';

        if ($levels < 1) $levels = 1;

        $_separator = [ '\\', DIRECTORY_SEPARATOR ];
        if (is_string($separator)) {
            $_separator[] = $separator;
        }
        $_separator = array_unique($_separator);

        $_value = str_replace($_separator, '/', $path);

        $_value = ltrim($_value, '/');

        $pi = [];

        if ($flags & PATHINFO_DIRNAME) {
            $dirname = dirname($_value, $levels);

            if (strlen($dirname)) {
                $dirname = str_replace('/', $separator, $dirname);

            } else {
                $dirname = null;
            }

            $pi[ 'dirname' ] = $dirname;
        }

        if (false
            || $flags & PATHINFO_BASENAME
            || $flags & PATHINFO_FILENAME
            || $flags & PATHINFO_EXTENSION
        ) {
            $basename = basename($_value);

            $basenameNotEmpty = strlen($basename);

            if ($flags & PATHINFO_BASENAME) {
                $pi[ 'basename' ] = $basenameNotEmpty ? $basename : null;
            }

            $filename = '';
            $extension = '';
            if ($basenameNotEmpty) {
                if (false
                    || $flags & PATHINFO_FILENAME
                    || $flags & PATHINFO_EXTENSION
                ) {
                    [ $filename, $extension ] = explode($dot, $basename, 2) + [ '', '' ];
                }
            }

            if ($flags & PATHINFO_FILENAME) {
                $pi[ 'filename' ] = strlen($filename) ? $filename : null;
            }

            if ($flags & PATHINFO_EXTENSION) {
                $pi[ 'extension' ] = strlen($extension) ? $extension : null;
            }
        }

        return $pi;
    }

    /**
     * > поддерживает предварительную замену $separator на '/'
     */
    public function dirname(string $path, string $separator = null, int $levels = null) : ?string
    {
        if ('' === $path) return null;

        $separator = $separator ?? DIRECTORY_SEPARATOR;
        $levels = $levels ?? 1;

        if ($levels < 1) $levels = 1;

        $_separator = [ '\\', DIRECTORY_SEPARATOR ];
        if (is_string($separator)) {
            $_separator[] = $separator;
        }
        $_separator = array_unique($_separator);

        $_value = str_replace($_separator, '/', $path);

        $_value = ltrim($_value, '/');

        if (false === strpos($_value, '/')) {
            $_value = null;

        } else {
            $_value = dirname($_value, $levels);

            $_value = str_replace('/', $separator, $_value);
        }

        return $_value;
    }

    /**
     * > поддерживает предварительную замену $separator на '/'
     */
    public function basename(string $path, string $separator = null, string $extension = null) : ?string
    {
        if ('' === $path) return null;

        $_separator = [ '\\', DIRECTORY_SEPARATOR ];
        if (is_string($separator)) {
            $_separator[] = $separator;
        }
        $_separator = array_unique($_separator);

        $_value = str_replace($_separator, '/', $path);

        $_value = basename($_value, $extension);

        return $_value;
    }

    /**
     * > поддерживает предварительную замену $separator на '/'
     */
    public function filename(string $path, string $separator = null, string $dot = null) : ?string
    {
        if ('' === $path) return null;

        $dot = $dot ?? '.';

        $_separator = [ '\\', DIRECTORY_SEPARATOR ];
        if (is_string($separator)) {
            $_separator[] = $separator;
        }
        $_separator = array_unique($_separator);

        $_value = str_replace($_separator, '/', $path);

        $_value = basename($_value);

        [ $_value ] = explode($dot, $_value, 2) + [ '', '' ];

        $_value = strlen($_value) ? $_value : null;

        return $_value;
    }

    /**
     * > поддерживает предварительную замену $separator на '/'
     */
    public function extension(string $path, string $separator = null, string $dot = null) : ?string
    {
        if ('' === $path) return null;

        $dot = $dot ?? '.';

        $_separator = [ '\\', DIRECTORY_SEPARATOR ];
        if (is_string($separator)) {
            $_separator[] = $separator;
        }
        $_separator = array_unique($_separator);

        $_value = str_replace($_separator, '/', $path);

        $_value = basename($_value);

        [ , $_value ] = explode($dot, $_value, 2) + [ '', '' ];

        $_value = strlen($_value) ? $_value : null;

        return $_value;
    }


    /**
     * @param class-string<\LogicException|\RuntimeException>|null $throwableClass
     *
     * @return class-string<\LogicException|\RuntimeException>
     */
    public function static_throwable_class(string $throwableClass = null) : string
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

        $message = $messageList ? end($messageList) : null;
        $code = $codeIntegerList ? end($codeIntegerList) : null;
        $codeString = $codeStringList ? end($codeStringList) : null;
        $previous = $previousList ? end($previousList) : null;

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
        static $stack;

        $stack = $stack
            ?? new class {
                public $stack = [];
            };

        return $stack;
    }

    /**
     * @return object{ list: array }
     */
    public function errors_current() : ?object
    {
        $stack = $this->errors();

        $errors = count($stack->stack)
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
            public $list = [];
        };

        return $errors;
    }

    /**
     * @return object{ list: array }
     */
    public function errors_start(object &$errors = null) : object
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


    public function benchmark(bool $noCache = null, bool $clearCache = null) : object
    {
        static $cache;

        $noCache = $noCache ?? false;
        $clearCache = $clearCache ?? false;

        if ($noCache) {
            return (object) [
                'microtime' => null,
                'totals'    => null,
            ];
        }

        if ($clearCache) {
            $cache = null;
        }

        if (! isset($cache)) {
            $cache = (object) [
                'microtime' => null,
                'totals'    => null,
            ];
        }

        return $cache;
    }

    public function benchmark_start(string $tag) : object
    {
        $benchmark = $this->benchmark();

        if (isset($benchmark->microtime[ $tag ])) {
            throw new LogicException(
                [ 'The `tag` already exists: ' . $tag ]
            );
        }

        $benchmark->microtime[ $tag ] = $this->microtime();

        return $benchmark;
    }

    public function benchmark_end(string $tag) : object
    {
        $benchmark = $this->benchmark();

        if (! isset($benchmark->microtime[ $tag ])) {
            throw new LogicException(
                [ 'Missing `tag`: ' . $tag ]
            );
        }

        $benchmark->totals[ $tag ][] = $this->microtime() - $benchmark->microtime[ $tag ];
        unset($benchmark->microtime[ $tag ]);

        return $benchmark;
    }
}
