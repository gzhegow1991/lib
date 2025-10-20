<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Func\Pipe\Pipe;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Func\Invoker\DefaultInvoker;
use Gzhegow\Lib\Modules\Func\Invoker\InvokerInterface;


class FuncModule
{
    /**
     * @var InvokerInterface
     */
    protected $invoker;


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    public function newInvoker() : InvokerInterface
    {
        $instance = new DefaultInvoker();

        return $instance;
    }

    public function cloneInvoker() : InvokerInterface
    {
        return clone $this->invoker();
    }

    public function invoker(?InvokerInterface $invoker = null) : InvokerInterface
    {
        return $this->invoker = null
            ?? $invoker
            ?? $this->invoker
            ?? $this->newInvoker();
    }


    /**
     * @return Pipe
     */
    public function newPipe()
    {
        $instance = new Pipe();

        return $instance;
    }


    /**
     * > подготавливает аргументы для вызова callable, заполняет пустоты в list с помощью NULL
     */
    public function func_args(array ...$arrays) : array
    {
        if ( [] === $arrays ) {
            return [];
        }

        $args = [];

        for ( $i = 0; $i < count($arrays); $i++ ) {
            $args += $arrays[$i];
        }

        if ( [] === $args ) {
            return [];
        }

        $iArgs = [];

        $max = -1;
        $hasInt = false;
        $hasString = false;
        foreach ( $args as $i => $arg ) {
            if ( ! is_int($i) ) {
                if ( PHP_VERSION_ID < 80000 ) {
                    throw new LogicException(
                        [ 'PHP does not support string keys', $args, $i ]
                    );
                }

                $hasString = true;

                continue;
            }

            if ( $i > $max ) {
                $max = $i;
            }

            $hasInt = true;
        }

        if ( $hasInt && $hasString ) {
            throw new LogicException(
                [
                    'The `args` should contain arguments of single type: string or int',
                    $args,
                ]
            );
        }

        if ( $max >= 0 ) {
            for ( $i = 0; $i <= $max; $i++ ) {
                $iArgs[$i] = $args[$i] ?? null;
            }
        }

        $args = $iArgs + $args;

        return [ $args, $iArgs ];
    }

    /**
     * > подготавливает аргументы для вызова callable, заполняет пустоты в list с помощью NULL
     * > бросает исключение, если среди ключей есть пересечения
     */
    public function func_args_unique(array ...$arrays) : array
    {
        if ( [] === $arrays ) {
            return [];
        }

        $args = [];

        for ( $i = 0; $i < count($arrays); $i++ ) {
            $argsCnt = count($args);
            $args += $arrays[$i];

            if ( count($args) !== ($argsCnt + count($arrays[$i])) ) {
                throw new LogicException(
                    [ 'Key intersection detected', $arrays, $i ]
                );
            }
        }

        if ( [] === $args ) {
            return [];
        }

        $iArgs = [];

        $max = -1;
        $hasInt = false;
        $hasString = false;
        foreach ( $args as $i => $arg ) {
            if ( ! is_int($i) ) {
                if ( PHP_VERSION_ID < 80000 ) {
                    throw new LogicException(
                        [ 'PHP does not support string keys', $args, $i ]
                    );
                }

                $hasString = true;

                continue;
            }

            if ( $i > $max ) {
                $max = $i;
            }

            $hasInt = true;
        }

        if ( $hasInt && $hasString ) {
            throw new LogicException(
                [
                    'The `args` should contain arguments of single type: string or int',
                    $args,
                ]
            );
        }

        if ( $max >= 0 ) {
            for ( $i = 0; $i <= $max; $i++ ) {
                $iArgs[$i] = $args[$i] ?? null;
            }
        }

        $args = $iArgs + $args;

        return [ $args, $iArgs ];
    }


    /**
     * > совмещает аргументы по числовым индексам, заполняет недостающие NULL
     *
     * @param callable $fn
     */
    public function func($fn, array $args = [], $newThis = null, $newScope = null) : \Closure
    {
        $hasNewThis = (null !== $newThis);
        $hasNewScope = (null !== $newScope);

        if ( $hasNewThis || $hasNewScope ) {
            $fn = \Closure::bind($fn, $newThis, $newScope);
        }

        [ $fnArgs ] = $this->func_args_unique($args);

        return function (...$arguments) use ($fn, $fnArgs) {
            $total = $arguments + $fnArgs;

            return call_user_func_array($fn, $total);
        };
    }

    /**
     * > добавляет аргументы в начало, заполняет недостающие NULL
     *
     * @param callable $fn
     */
    public function bind($fn, array $args = [], $newThis = null, $newScope = null) : \Closure
    {
        $hasNewThis = (null !== $newThis);
        $hasNewScope = (null !== $newScope);

        if ( $hasNewThis || $hasNewScope ) {
            $fn = \Closure::bind($fn, $newThis, $newScope);
        }

        [ $fnArgs, $fnIArgs ] = $this->func_args_unique($args);

        return function (...$arguments) use ($fn, $fnArgs, $fnIArgs) {
            $total = array_merge($fnIArgs, $arguments) + $fnArgs;

            return call_user_func_array($fn, $total);
        };
    }


    /**
     * > встроенные функции в php такие как strlen() требуют строгое число аргументов
     * > стоит передать туда больше аргументов - сразу throw/trigger_error и это хорошо
     * > но как только array_filter/array_map, то это плохо
     */
    public function call_user_func($fn, ...$args)
    {
        $isMaybeInternalFunction = is_string($fn) && function_exists($fn);

        if ( ! $isMaybeInternalFunction ) {
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

            if ( null !== $err ) {
                $eMsg = $err['message'];
            }

            if ( null !== $eMsg ) {
                $eMsgKnownList = [
                    '() expects exactly '  => 19,
                    '() expects at most '  => 19,
                    '() expects at least ' => 20,
                ];

                $isKnown = false;
                foreach ( $eMsgKnownList as $eSubstr => $eSubstrLen ) {
                    if ( false !== ($pos = strpos($eMsg, $eSubstr)) ) {
                        $isKnown = true;

                        break;
                    }
                }

                if ( $isKnown ) {
                    $max = (int) substr($eMsg, $pos + $eSubstrLen);

                    array_splice($args, $max);

                    $result = call_user_func($fn, ...$args);
                }

                if ( $ex && ! $isKnown ) {
                    throw new RuntimeException($ex);
                }
            }
        }

        return $result;
    }

    /**
     * > встроенные функции в php такие как strlen() требуют строгое число аргументов
     * > стоит передать туда больше аргументов - сразу throw/trigger_error и это хорошо
     * > но как только array_filter/array_map, то это плохо
     */
    public function call_user_func_array($fn, array $args, ?array &$refArgsNew = null)
    {
        $refArgsNew = null;

        $isMaybeInternalFunction = is_string($fn) && function_exists($fn);

        [ $fnArgs ] = $this->func_args_unique($args);

        $isIntKeys = array_key_exists(0, $fnArgs);

        if ( ! ($isMaybeInternalFunction || ! $isIntKeys) ) {
            $result = call_user_func_array($fn, $fnArgs);

        } else {
            $ex = null;
            $eMsg = null;

            $before = error_reporting(0);
            error_clear_last();

            try {
                $result = call_user_func_array($fn, $fnArgs);
            }
            catch ( \Throwable $ex ) {
                $eMsg = $ex->getMessage();
            }

            $err = error_get_last();
            error_reporting($before);

            if ( null !== $err ) {
                $eMsg = $err['message'];
            }

            if ( null !== $eMsg ) {
                $eMsgKnownList = [
                    '() expects exactly '  => 19,
                    '() expects at most '  => 19,
                    '() expects at least ' => 20,
                ];

                $isKnown = false;
                foreach ( $eMsgKnownList as $eSubstr => $eSubstrLen ) {
                    if ( false !== ($pos = strpos($eMsg, $eSubstr)) ) {
                        $isKnown = true;

                        break;
                    }
                }

                if ( $ex && ! $isKnown ) {
                    throw new RuntimeException($ex);
                }

                if ( $isKnown ) {
                    $max = (int) substr($eMsg, $pos + $eSubstrLen);

                    array_splice($fnArgs, $max);

                    $result = call_user_func_array($fn, $fnArgs);
                }
            }
        }

        $refArgsNew = $fnArgs;

        return $result;
    }


    /**
     * @param callable $fn
     *
     * @return mixed
     */
    public function safe_call($fn, array $args = [])
    {
        $beforeErrorReporting = error_reporting(E_ALL | E_DEPRECATED | E_USER_DEPRECATED);
        $beforeErrorHandler = set_error_handler([ $this, 'safe_call_error_handler' ]);

        try {
            $result = call_user_func_array($fn, $args);
        }
        catch ( \Throwable $e ) {
            throw new RuntimeException($e);
        }

        set_error_handler($beforeErrorHandler);
        error_reporting($beforeErrorReporting);

        return $result;
    }

    /**
     * @throws \ErrorException
     */
    public function safe_call_error_handler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
    }


    /**
     * @param callable $fn
     */
    public function throttle(int $throttleMs, $fn) : \Closure
    {
        if ( $throttleMs <= 0 ) {
            throw new LogicException(
                [ 'The `delayMs` should be a positive integer', $throttleMs ]
            );
        }

        $lastCall = 0;

        $throttleSeconds = ($throttleMs / 1000);

        return static function (...$args) use (
            &$lastCall,
            $throttleSeconds, $fn
        ) : array {
            $now = microtime(true);

            if ( ($now - $lastCall) >= $throttleSeconds ) {
                $lastCall = $now;

                $result = call_user_func_array($fn, $args);

                return [ $result ];
            }

            return [];
        };
    }
}
