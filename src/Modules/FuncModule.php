<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Func\Pipe\Pipe;
use Gzhegow\Lib\Exception\LogicException;


class FuncModule
{
    public function pipe(?Pipe &$p = null) : Pipe
    {
        return $p = new Pipe();
    }


    /**
     * > подготавливает аргументы для вызова callable, заполняет пустоты в list с помощью NULL
     */
    public function func_args(array ...$arrays) : array
    {
        if ([] === $arrays) {
            return [];
        }

        $args = [];

        for ( $i = 0; $i < count($arrays); $i++ ) {
            $args += $arrays[ $i ];
        }

        if ([] === $args) {
            return [];
        }

        $iArgs = [];

        $max = -1;
        $hasInt = false;
        $hasString = false;
        foreach ( $args as $i => $arg ) {
            if (! is_int($i)) {
                if (PHP_VERSION_ID < 80000) {
                    throw new LogicException(
                        [ 'PHP does not support string keys', $args, $i ]
                    );
                }

                $hasString = true;

                continue;
            }

            if ($i > $max) {
                $max = $i;
            }

            $hasInt = true;
        }

        if ($hasInt && $hasString) {
            throw new LogicException(
                [
                    'The `args` should contain arguments of single type: string or int',
                    $args,
                ]
            );
        }

        if ($max >= 0) {
            for ( $i = 0; $i <= $max; $i++ ) {
                $iArgs[ $i ] = $args[ $i ] ?? null;
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
        if ([] === $arrays) {
            return [];
        }

        $args = [];

        for ( $i = 0; $i < count($arrays); $i++ ) {
            $argsCnt = count($args);
            $args += $arrays[ $i ];

            if (count($args) !== ($argsCnt + count($arrays[ $i ]))) {
                throw new LogicException(
                    [ 'Key intersection detected', $arrays, $i ]
                );
            }
        }

        if ([] === $args) {
            return [];
        }

        $iArgs = [];

        $max = -1;
        $hasInt = false;
        $hasString = false;
        foreach ( $args as $i => $arg ) {
            if (! is_int($i)) {
                if (PHP_VERSION_ID < 80000) {
                    throw new LogicException(
                        [ 'PHP does not support string keys', $args, $i ]
                    );
                }

                $hasString = true;

                continue;
            }

            if ($i > $max) {
                $max = $i;
            }

            $hasInt = true;
        }

        if ($hasInt && $hasString) {
            throw new LogicException(
                [
                    'The `args` should contain arguments of single type: string or int',
                    $args,
                ]
            );
        }

        if ($max >= 0) {
            for ( $i = 0; $i <= $max; $i++ ) {
                $iArgs[ $i ] = $args[ $i ] ?? null;
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

        if ($hasNewThis || $hasNewScope) {
            $fn = \Closure::bind($fn, $newThis, $newScope);
        }

        [ $fnArgs ] = $this->func_args_unique($args);

        return function (...$arguments) use ($fn, $fnArgs) {
            $total = $arguments + $fnArgs;

            return call_user_func_array($fn, $total);
        };
    }

    /**
     * > совмещает аргументы по числовым индексам, заполняет недостающие NULL
     *
     * @param callable $fn
     */
    public function func_not($fn, array $args = [], $newThis = null, $newScope = null) : \Closure
    {
        $hasNewThis = (null !== $newThis);
        $hasNewScope = (null !== $newScope);

        if ($hasNewThis || $hasNewScope) {
            $fn = \Closure::bind($fn, $newThis, $newScope);
        }

        [ $fnArgs ] = $this->func_args_unique($args);

        return function (...$arguments) use ($fn, $fnArgs) {
            $total = $arguments + $fnArgs;

            return ! call_user_func_array($fn, $total);
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

        if ($hasNewThis || $hasNewScope) {
            $fn = \Closure::bind($fn, $newThis, $newScope);
        }

        [ $fnArgs, $fnIArgs ] = $this->func_args_unique($args);

        return function (...$arguments) use ($fn, $fnArgs, $fnIArgs) {
            $total = array_merge($fnIArgs, $arguments) + $fnArgs;

            return call_user_func_array($fn, $total);
        };
    }

    /**
     * > добавляет аргументы в начало, заполняет недостающие NULL
     *
     * @param callable $fn
     */
    public function bind_not($fn, array $args = [], $newThis = null, $newScope = null) : \Closure
    {
        $hasNewThis = (null !== $newThis);
        $hasNewScope = (null !== $newScope);

        if ($hasNewThis || $hasNewScope) {
            $fn = \Closure::bind($fn, $newThis, $newScope);
        }

        [ $fnArgs, $fnIArgs ] = $this->func_args_unique($args);

        return function (...$arguments) use ($fn, $fnArgs, $fnIArgs) {
            $total = array_merge($fnIArgs, $arguments) + $fnArgs;

            return ! call_user_func_array($fn, $total);
        };
    }


    /**
     * > встроенные функции в php такие как strlen() требуют строгое число аргументов
     * > стоит передать туда больше аргументов - сразу throw/trigger_error и это хорошо
     * > но как только array_filter/array_map, то это плохо
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
     * > стоит передать туда больше аргументов - сразу throw/trigger_error и это хорошо
     * > но как только array_filter/array_map, то это плохо
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     * @throws \RuntimeException
     */
    public function call_user_func_array($fn, array $args, ?array &$argsNew = null)
    {
        $argsNew = null;

        $isMaybeInternalFunction = is_string($fn) && function_exists($fn);

        [ $fnArgs ] = $this->func_args_unique($args);

        if (! $isMaybeInternalFunction) {
            $result = call_user_func_array($fn, $fnArgs);

        } elseif (! ($isIntKeys = array_key_exists(0, $fnArgs))) {
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

                    array_splice($fnArgs, $max);

                    $result = call_user_func_array($fn, $fnArgs);
                }
            }
        }

        $argsNew = $fnArgs;

        return $result;
    }


    /**
     * @param callable $fn
     */
    public function throttle(int $throttleMs, $fn) : \Closure
    {
        if ($throttleMs <= 0) {
            throw new LogicException(
                [ 'The `delayMs` should be positive integer', $throttleMs ]
            );
        }

        $lastCall = 0;

        $throttleSeconds = ($throttleMs / 1000);

        return static function (...$args) use (
            &$lastCall,
            $throttleSeconds, $fn
        ) : array {
            $now = microtime(true);

            if (($now - $lastCall) >= $throttleSeconds) {
                $lastCall = $now;

                $result = call_user_func_array($fn, $args);

                return [ $result ];
            }

            return [];
        };
    }
}
