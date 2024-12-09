<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\LogicException;


trait PhpTrait
{
    public static function php_get_error_handler() : ?callable
    {
        $handler = set_error_handler(static function () { });
        restore_error_handler();

        return $handler;
    }

    public static function php_get_exception_handler() : ?callable
    {
        $handler = set_exception_handler(static function () { });
        restore_exception_handler();

        return $handler;
    }


    function php_debug_backtrace($options = null, $limit = null) : array
    {
        $options = $options ?? DEBUG_BACKTRACE_IGNORE_ARGS;
        if ($options < 0) $options = DEBUG_BACKTRACE_IGNORE_ARGS;

        $limit = $limit ?? 0;
        if ($limit < 0) $limit = 1;

        $result = debug_backtrace($options, $limit);

        return $result;
    }


    /**
     * @return object{ errors: array }
     */
    public static function php_errors() : object
    {
        static $stack;

        $stack = $stack
            ?? new class {
                public $errors = [];
            };

        return $stack;
    }

    /**
     * @return object{ list: array }
     */
    public static function php_errors_current() : object
    {
        $stack = static::php_errors();

        $errors = end($stack->errors);

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
        $stack->errors[] = $errors;

        return $errors;
    }

    public static function php_errors_end(?object $until) : array
    {
        $stack = static::php_errors();

        $errors = static::php_errors_new();

        while ( count($stack->errors) ) {
            $current = array_pop($stack->errors);

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

        $current->list[] = $error;

        return $result;
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
     * @param class-string<\Exception|\LogicException|\RuntimeException>|null $throwable
     *
     * @return class-string<\Exception|\LogicException|\RuntimeException>
     */
    public static function php_throwable(string $throwable = null) : string
    {
        static $current;

        $current = $current ?? $throwable;

        if (! (false
            || is_subclass_of($current, \Exception::class)
            || is_subclass_of($current, \LogicException::class)
            || is_subclass_of($current, \RuntimeException::class)
        )) {
            throw new LogicException(
                [
                    'The `throwable` should be class that extends one of: '
                    . implode('|', [
                        \Exception::class,
                        \LogicException::class,
                        \RuntimeException::class,
                    ]),
                    $current,
                ]
            );
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

            if (is_a($arg, \Throwable::class)) {
                $previousList[ $i ] = $arg;

                continue;
            }

            if (
                is_array($arg)
                || is_a($arg, \stdClass::class)
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

            if (is_int($arg)) {
                $codeList[ $i ] = $arg;

                continue;
            }

            if ('' !== ($vString = (string) $arg)) {
                $messageList[ $i ] = $vString;

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

    public static function php_throw(array $trace = null, ...$args)
    {
        $trace = $trace ?? debug_backtrace();

        $throwableClass = static::php_throwable();

        $e = new $throwableClass();

        $throwableArgs = static::php_throwable_args(...$args);

        (function () use ($trace, $throwableArgs) {
            $this->trace = $trace;
            $this->file = $trace[ 0 ][ 'file' ] ?? '{file}';
            $this->line = $trace[ 0 ][ 'line' ] ?? '{line}';

            foreach ( $throwableArgs as $key => $value ) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        })->call($e);

        throw $e;
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
}
