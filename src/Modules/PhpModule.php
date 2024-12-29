<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToFloatInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIntegerInterface;


class PhpModule
{
    /**
     * @var class-string<\LogicException|\RuntimeException>
     */
    protected $throwableClass = LogicException::class;


    public function __construct()
    {
        if (! extension_loaded('date')) {
            throw new RuntimeException(
                'Missing PHP extension: date'
            );
        }
    }


    public function toInt($value, array $options = []) : int
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
                    [ 'The `value` should not be array, object or resource', $value ]
                );
            }

            $_value = (int) $value;
        }

        return $_value;
    }

    public function toFloat($value, array $options = []) : float
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
                    [ 'The `value` should not be array, object or resource', $value ]
                );
            }

            $_value = (float) $value;
        }

        return $_value;
    }

    public function toString($value, array $options = []) : string
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
                    [ 'The `value` should not be array, object or resource', $value ]
                );
            }

            $_value = (string) $value;
        }

        return $_value;
    }

    public function toArray($value, array $options = []) : array
    {
        if ($value instanceof ToArrayInterface) {
            $_value = $value->toArray($options);

        } else {
            if (is_object($value) && ! ($value instanceof \stdClass)) {
                throw new LogicException(
                    [ 'The `value` being the object should be instance of: ' . \stdClass::class, $value ]
                );
            }

            $_value = (array) $value;
        }

        return $_value;
    }


    public function count($value) : ?int
    {
        if (is_array($value)) {
            return count($value);
        }

        if (Lib::parse()->countable($value)) {
            return count($value);
        }

        return null;
    }


    public function debug_backtrace($options = null, $limit = null) : array
    {
        $options = $options ?? DEBUG_BACKTRACE_IGNORE_ARGS;
        if ($options < 0) $options = DEBUG_BACKTRACE_IGNORE_ARGS;

        $limit = $limit ?? 0;
        if ($limit < 0) $limit = 1;

        $result = debug_backtrace($options, $limit);

        return $result;
    }


    public function dirname(string $path, string $separator = null, int $levels = null) : ?string
    {
        $separator = $separator ?? DIRECTORY_SEPARATOR;
        $levels = $levels ?? 1;

        if ('' === $path) return null;

        $_value = $path;

        $hasSeparator = (false !== strpos($_value, $separator));

        $_value = $hasSeparator
            ? str_replace([ '\\', DIRECTORY_SEPARATOR, $separator ], '/', $_value)
            : str_replace([ '\\', DIRECTORY_SEPARATOR ], '/', $_value);

        $_value = ltrim($_value, '/');

        if (false === strpos($_value, '/')) {
            $_value = null;

        } else {
            $_value = preg_replace('~[/]+~', '/', $_value);

            $_value = dirname($_value, $levels);
            $_value = str_replace('/', $separator, $_value);
        }

        return $_value;
    }


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


    /**
     * @param class-string<\LogicException|\RuntimeException>|null $throwableClass
     *
     * @return class-string<\LogicException|\RuntimeException>
     */
    public function throwable_class_static(string $throwableClass = null) : string
    {
        if (null !== $throwableClass) {
            if (! (false
                || is_a($throwableClass, \LogicException::class, true)
                || is_a($throwableClass, \RuntimeException::class, true)
            )) {
                throw new LogicException(
                    [
                        'The `throwableClass` should be class that extends one of: '
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

    public function throwable_args(...$args) : array
    {
        $len = count($args);

        $messageList = null;
        $codeList = null;
        $previousList = null;
        $messageCodeList = null;
        $messageDataList = null;

        $__unresolved = [];

        for ( $i = 0; $i < $len; $i++ ) {
            $arg = $args[ $i ];

            if (is_int($arg)) {
                $codeList[ $i ] = $arg;

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
                    $messageCodeList[ $i ] = strtoupper($messageList[ $i ]);
                }
            }
        }

        $result = [];

        $result[ 'messageList' ] = $messageList;
        $result[ 'codeList' ] = $codeList;
        $result[ 'previousList' ] = $previousList;
        $result[ 'messageCodeList' ] = $messageCodeList;
        $result[ 'messageDataList' ] = $messageDataList;

        $messageDataList = $messageDataList ?? [];

        $message = $messageList ? end($messageList) : null;
        $code = $codeList ? end($codeList) : null;
        $previous = $previousList ? end($previousList) : null;
        $messageCode = $messageCodeList ? end($messageCodeList) : null;

        $messageData = $messageDataList
            ? array_replace(...$messageDataList)
            : [];

        $messageObject = (object) ([ $message ] + $messageData);

        $result[ 'message' ] = $message;
        $result[ 'code' ] = $code;
        $result[ 'previous' ] = $previous;

        $result[ 'messageCode' ] = $messageCode;
        $result[ 'messageData' ] = $messageData;

        $result[ 'messageObject' ] = $messageObject;

        $result[ '__unresolved' ] = $__unresolved;

        return $result;
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function throw(...$throwableArgs)
    {
        $throwableClass = $this->throwable_class_static();

        $trace = property_exists($throwableClass, 'trace')
            ? debug_backtrace()
            : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->throw_trace($trace, ...$throwableArgs);
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function throw_trace(array $trace = null, ...$throwableArgs)
    {
        $throwableClass = $this->throwable_class_static();

        if (null === $trace) {
            $trace = property_exists($throwableClass, 'trace')
                ? debug_backtrace()
                : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        }

        $throwableArgs = $this->throwable_args(...$throwableArgs);
        $throwableArgs[ 'file' ] = $trace[ 0 ][ 'file' ] ?? '{file}';
        $throwableArgs[ 'line' ] = $trace[ 0 ][ 'line' ] ?? '{line}';
        $throwableArgs[ 'trace' ] = $trace;

        $e = new $throwableClass();

        (function () use ($throwableArgs) {
            foreach ( $throwableArgs as $key => $value ) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        })->call($e);

        throw $e;
    }


    public function microtime(\DateTimeInterface $date = null) : float
    {
        $date = $date ?? new \DateTime();

        return floatval(
            $date->format('U')
            . '.'
            . str_pad($date->format('u'), 6, '0')
        );
    }


    /**
     * @param callable|string $function
     */
    public function function_exists($function) : ?string
    {
        if (! is_string($function)) return null;

        if (function_exists($function)) {
            return $function;
        }

        return null;
    }

    /**
     * @param callable|array|object|class-string     $mixed
     *
     * @param array{0: class-string, 1: string}|null $resultArray
     * @param callable|string|null                   $resultString
     *
     * @return array{0: class-string|object, 1: string}|null
     */
    public function method_exists(
        $mixed, $method = null,
        array &$resultArray = null, string &$resultString = null
    ) : ?array
    {
        $resultArray = null;
        $resultString = null;

        $method = $method ?? '';

        $_class = null;
        $_object = null;
        $_method = null;
        if (is_object($mixed)) {
            $_object = $mixed;

        } elseif (is_array($mixed)) {
            $list = array_values($mixed);

            [ $classOrObject, $_method ] = $list + [ '', '' ];

            is_object($classOrObject)
                ? ($_object = $classOrObject)
                : ($_class = $classOrObject);

        } elseif (is_string($mixed)) {
            [ $_class, $_method ] = explode('::', $mixed) + [ '', '' ];

            $_method = $_method ?? $method;
        }

        if (isset($_method) && ! is_string($_method)) {
            return null;
        }

        if ($_object) {
            if ($_object instanceof \Closure) {
                return null;
            }

            if (method_exists($_object, $_method)) {
                $class = get_class($_object);

                $resultArray = [ $_object, $_method ];

                return [ $_object, $_method ];
            }

        } elseif ($_class) {
            if (method_exists($_class, $_method)) {
                $resultArray = [ $_class, $_method ];
                $resultString = $_class . '::' . $_method;

                return [ $_class, $_method ];
            }
        }

        return null;
    }


    public function fn($fn, array $args = []) : \Closure
    {
        return function (...$arguments) use ($fn, $args) {
            $_args = array_merge($arguments, $args);

            return call_user_func_array($fn, $_args);
        };
    }

    public function fn_not($fn, array $args = []) : \Closure
    {
        return function (...$arguments) use ($fn, $args) {
            $_args = array_merge($arguments, $args);

            return ! call_user_func_array($fn, $_args);
        };
    }


    /**
     * @param int[]    $results
     * @param callable $fnCmp
     */
    public function cmp($a, $b, array $results = [ 0 ], $fnCmp = null) : ?int
    {
        $result = (null === $fnCmp)
            ? ($a <=> $b)
            : $fnCmp($a, $b);

        if (! in_array($result, $results, true)) {
            return null;
        }

        return $result;
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
     * > gzhegow, функция get_object_vars() возвращает все элементы для $this, в том числе protected/private
     * > чтобы получить доступ только к публичным свойствам, её нужно вызвать в обертке
     */
    public function get_object_vars(object $object, bool $publicOnly = null) : array
    {
        $publicOnly = $publicOnly ?? true;

        if ($publicOnly) {
            $fn = 'get_object_vars';

        } else {
            $fn = (function (object $object) {
                return get_object_vars($object);
            })->bindTo($object, $object);
        }

        $vars = $fn($object);

        return $vars;
    }

    /**
     * > gzhegow, функция property_exists() возвращает все свойства, в том числе protected/private
     * > чтобы получить доступ только к публичным свойствам, нужно прибегнуть к вот такой хитрости
     */
    public function property_exists(object $object_or_class, string $property, bool $publicOnly = null) : bool
    {
        $publicOnly = $publicOnly ?? true;

        $vars = $this->get_object_vars($object_or_class, $publicOnly);

        return array_key_exists($property, $vars);
    }
}
