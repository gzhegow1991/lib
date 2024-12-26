<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\LogicException;


trait PhpTrait
{
    public static function php_count($value) : ?int
    {
        if (is_array($value)) {
            return count($value);
        }

        if (static::parse_countable($value)) {
            return count($value);
        }

        return null;
    }


    public static function php_debug_backtrace($options = null, $limit = null) : array
    {
        $options = $options ?? DEBUG_BACKTRACE_IGNORE_ARGS;
        if ($options < 0) $options = DEBUG_BACKTRACE_IGNORE_ARGS;

        $limit = $limit ?? 0;
        if ($limit < 0) $limit = 1;

        $result = debug_backtrace($options, $limit);

        return $result;
    }


    public static function php_dirname(?string $path, string $separator = null, int $levels = null) : ?string
    {
        $separator = $separator ?? DIRECTORY_SEPARATOR;
        $levels = $levels ?? 1;

        if (null === $path) return null;
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
            $_value = preg_replace('~/+~', '/', $_value);

            $_value = dirname($_value, $levels);
            $_value = str_replace('/', $separator, $_value);
        }

        return $_value;
    }


    public static function php_get_defined_functions() : array
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
    public static function php_get_error_handler() // : ?callable
    {
        $handler = set_error_handler(static function () { });
        restore_error_handler();

        return $handler;
    }

    /**
     * @return callable|null
     */
    public static function php_get_exception_handler() // : ?callable
    {
        $handler = set_exception_handler(static function () { });
        restore_exception_handler();

        return $handler;
    }


    /**
     * @return object{ stack: array }
     */
    public static function php_errors() : object
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
    public static function php_errors_current() : ?object
    {
        $stack = static::php_errors();

        $errors = count($stack->stack)
            ? end($stack->stack)
            : null;

        return $errors;
    }

    /**
     * @return object{ list: array }
     */
    public static function php_errors_new() : object
    {
        $errors = new class {
            public $list = [];
        };

        return $errors;
    }

    /**
     * @return object{ list: array }
     */
    public static function php_errors_start(object &$errors = null) : object
    {
        $stack = static::php_errors();

        $errors = static::php_errors_new();

        $stack->stack[] = $errors;

        return $errors;
    }

    public static function php_errors_end(?object $until) : array
    {
        $stack = static::php_errors();

        $errors = static::php_errors_new();

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

    public static function php_error($error, $result = null) // : mixed
    {
        $current = static::php_errors_current();

        if (null !== $current) {
            $current->list[] = $error;
        }

        return $result;
    }


    /**
     * @param class-string<\Exception|\LogicException|\RuntimeException>|null $throwableClass
     *
     * @return class-string<\Exception|\LogicException|\RuntimeException>
     */
    public static function php_throwable_static(string $throwableClass = null) : string
    {
        static $current;

        $current = $current ?? LogicException::class;

        if (null !== $throwableClass) {
            if (! (false
                || is_a($current, \Exception::class, true)
                || is_a($current, \LogicException::class, true)
                || is_a($current, \RuntimeException::class, true)
            )) {
                throw new LogicException(
                    [
                        'The `throwableClass` should be class that extends one of: '
                        . implode('|', [
                            \Exception::class,
                            \LogicException::class,
                            \RuntimeException::class,
                        ]),
                        $current,
                    ]
                );
            }

            $last = $current;

            $current = $throwableClass;

            return $last;
        }

        return $current;
    }

    public static function php_throwable_args(...$args) : array
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

    public static function php_throw(...$throwableArgs)
    {
        $throwableClass = static::php_throwable_static();

        $trace = property_exists($throwableClass, 'trace')
            ? debug_backtrace()
            : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        static::php_throw_trace($trace, ...$throwableArgs);
    }

    public static function php_throw_trace(array $trace = null, ...$throwableArgs)
    {
        $throwableClass = static::php_throwable_static();

        if (null === $trace) {
            $trace = property_exists($throwableClass, 'trace')
                ? debug_backtrace()
                : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        }

        $throwableArgs = static::php_throwable_args(...$throwableArgs);
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


    public static function php_microtime(\DateTimeInterface $date = null) : float
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
    public static function php_function_exists($function) : ?string
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
    public static function php_method_exists(
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

                $resultArray = [ $class, $_method ];
                $resultString = $class . '::' . $_method;

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


    public static function php_fn($fn, array $args = []) : \Closure
    {
        return function (...$arguments) use ($fn, $args) {
            $_args = array_merge($arguments, $args);

            return call_user_func_array($fn, $_args);
        };
    }

    public static function php_fn_not($fn, array $args = []) : \Closure
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
    public static function php_cmp($a, $b, array $results = [ 0 ], $fnCmp = null) : ?int
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
    public static function php_serialize($data) : ?string
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
    public static function php_unserialize(string $data) // : mixed|null
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


    public static function php_array($value) : array
    {
        if (is_object($value) && (! ($value instanceof \stdClass))) {
            throw new LogicException(
                [ 'The `value` being the object should be instance of: ' . \stdClass::class, $value ]
            );
        }

        return (array) $value;
    }


    /**
     * @param object|class-string $objectOrClass
     *
     * @return class-string[]
     */
    public static function php_class_uses_with_parents($objectOrClass, bool $recursive = null)
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
            $uses += static::php_class_uses($sourceClassName, $recursive);
        }

        $uses = array_unique($uses);

        return $uses;
    }

    /**
     * @param object|class-string $objectOrClass
     *
     * @return class-string[]
     */
    public static function php_class_uses($objectOrClass, bool $recursive = null)
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
                $uses += static::php_class_uses($usesItem);
            }
        }

        return $uses;
    }
}
