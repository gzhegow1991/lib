<?php

namespace Gzhegow\Lib\Modules\Type;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @template T of mixed
 */
class Ret
{
    /**
     * @var bool
     */
    protected static $isCollectTrace = false;

    /**
     * @param int|false|null $isCollectTrace
     */
    public static function staticIsCollectTrace(?bool $isCollectTrace = null) : bool
    {
        $last = static::$isCollectTrace;

        if ( null !== $isCollectTrace ) {
            if ( false === $isCollectTrace ) {
                static::$isCollectTrace = false;

            } else {
                static::$isCollectTrace = (bool) $isCollectTrace;
            }
        }

        static::$isCollectTrace = static::$isCollectTrace ?? false;

        return $last;
    }


    /**
     * @var array{ 0?: T }
     */
    public $value = [];

    /**
     * @var \stdClass[]
     */
    public $errors = [];
    /**
     * @var array{
     *     file_line: array{ 0: string, 1: int },
     *     throwable_args: array,
     *     trace: array[],
     * }[]
     */
    protected $errorsRaw = [];


    /**
     * @param array{ 0?: mixed } $fallback
     *
     * @return T|mixed
     */
    public function __invoke(array $fallback, $throwableArg = null, array $fileLine = [], ...$throwableArgs)
    {
        return $this->orFallback($fallback, $throwableArg, $fileLine, ...$throwableArgs);
    }


    /**
     * @return static
     */
    public static function new()
    {
        $className = (PHP_VERSION_ID >= 80000)
            ? '\Gzhegow\Lib\Modules\Type\Ret\PHP8\Ret'
            : '\Gzhegow\Lib\Modules\Type\Ret\PHP7\Ret';

        return new $className();
    }


    /**
     * @param T $value
     *
     * @return static<T>
     */
    public static function val($value)
    {
        if ( $value instanceof self ) {
            throw new LogicException(
                [ 'The `value` should not be instance of: ' . self::class, $value ]
            );
        }

        $className = (PHP_VERSION_ID >= 80000)
            ? '\Gzhegow\Lib\Modules\Type\Ret\PHP8\Ret'
            : '\Gzhegow\Lib\Modules\Type\Ret\PHP7\Ret';

        $instance = new $className();

        $instance->value = [ $value ];

        return $instance;
    }

    /**
     * @param static|mixed               $throwableArg
     *
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return static<T>
     */
    public static function err($throwableArg, array $fileLine = [], ...$throwableArgs)
    {
        $className = (PHP_VERSION_ID >= 80000)
            ? '\Gzhegow\Lib\Modules\Type\Ret\PHP8\Ret'
            : '\Gzhegow\Lib\Modules\Type\Ret\PHP7\Ret';

        $instance = new $className();

        if ( $throwableArg instanceof self ) {
            $instance->mergeFrom($throwableArg);

            if ( [] !== $throwableArgs ) {
                $fileLine = $fileLine ?: Lib::debug()->file_line();

                $instance->doAddError(null, $fileLine, ...$throwableArgs);

            } elseif ( ([] !== $fileLine) && ([] !== $throwableArg->errorsRaw) ) {
                $errorLast = end($throwableArg->errorsRaw);

                $instance->doAddError($errorLast['trace'], $fileLine, ...$errorLast['throwable_args']);
            }

        } else {
            $fileLine = $fileLine ?: Lib::debug()->file_line();

            $instance->doAddError(null, $fileLine, $throwableArg, ...$throwableArgs);
        }

        return $instance;
    }


    /**
     * @param T $value
     *
     * @return T|static<T>
     */
    public static function ok(?array $fallback, $value)
    {
        if ( $value instanceof self ) {
            throw new LogicException(
                [ 'The `value` should not be instance of: ' . self::class, $value ]
            );
        }

        if ( null === $fallback ) {
            $className = (PHP_VERSION_ID >= 80000)
                ? '\Gzhegow\Lib\Modules\Type\Ret\PHP8\Ret'
                : '\Gzhegow\Lib\Modules\Type\Ret\PHP7\Ret';

            $instance = new $className();

            $instance->value = [ $value ];

            return $instance;
        }

        return $value;
    }

    /**
     * @param static|mixed               $throwableArg
     *
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return T|static<T>
     */
    public static function throw(?array $fallback, $throwableArg, array $fileLine = [], ...$throwableArgs)
    {
        $className = (PHP_VERSION_ID >= 80000)
            ? '\Gzhegow\Lib\Modules\Type\Ret\PHP8\Ret'
            : '\Gzhegow\Lib\Modules\Type\Ret\PHP7\Ret';

        $instance = new $className();

        if ( $throwableArg instanceof self ) {
            $instance->mergeFrom($throwableArg);

            if ( [] !== $throwableArgs ) {
                $instance->doAddError(null, $fileLine, ...$throwableArgs);

            } elseif ( ([] !== $fileLine) && ([] !== $throwableArg->errorsRaw) ) {
                $errorLast = end($throwableArg->errorsRaw);

                $instance->doAddError($errorLast['trace'], $fileLine, ...$errorLast['throwable_args']);
            }

        } else {
            $instance->doAddError(null, $fileLine, $throwableArg, ...$throwableArgs);
        }

        if ( null === $fallback ) {
            return $instance;
        }

        if ( [] !== $fallback ) {
            return $fallback[0];
        }

        return $instance->orThrow();
    }


    public function getStatus() : bool
    {
        return [] !== $this->value;
    }

    /**
     * @return T
     */
    public function getValue(array $fallback = [])
    {
        if ( [] === $this->value ) {
            if ( [] !== $fallback ) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                [ 'The `value` should exists', $this ]
            );
        }

        return $this->value[0];
    }


    /**
     * @return \stdClass[]
     */
    public function getErrors(?bool $isAssociative = null) : array
    {
        return $this->fetchErrors($isAssociative);
    }

    /**
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return static
     */
    public function addError($throwableArg, array $fileLine = [], ...$throwableArgs)
    {
        $this->doAddError(null, $fileLine, $throwableArg, ...$throwableArgs);

        return $this;
    }

    /**
     * @return static
     */
    protected function doAddError(?array $trace, ?array $fileLine, ...$throwableArgs)
    {
        $theDebug = Lib::debug();

        $traceValid = null
            ?? ($trace ?: null)
            ?? (static::staticIsCollectTrace() ? $theDebug->trace(2) : null);

        $fileLineValid = null
            ?? ($fileLine ?: null)
            ?? $theDebug->file_line(2, $traceValid);

        foreach ( $throwableArgs as $i => $throwableArg ) {
            if ( null === $throwableArg ) {
                unset($throwableArgs[$i]);
            }
        }

        $throwableArgsValid = array_values($throwableArgs);

        $this->errorsRaw[] = [
            'file_line'      => $fileLineValid,
            'throwable_args' => $throwableArgsValid,
            'trace'          => $traceValid,
        ];

        return $this;
    }


    /**
     * @return static
     */
    public function mergeFrom(self $retFrom)
    {
        if ( [] !== $retFrom->value ) {
            $this->value = $retFrom->value;
        }

        if ( [] !== $retFrom->errorsRaw ) {
            $this->errorsRaw = array_merge(
                $this->errorsRaw,
                $retFrom->errorsRaw
            );
        }

        return $this;
    }

    /**
     * @return static
     */
    public function mergeTo(self $retTo)
    {
        if ( [] !== $this->value ) {
            $retTo->value = $this->value;
        }

        if ( [] !== $this->errorsRaw ) {
            $retTo->errorsRaw = array_merge(
                $retTo->errorsRaw,
                $this->errorsRaw
            );
        }

        return $retTo;
    }


    /**
     * @param array{
     *     0: \stdClass[],
     *     1: static<T>,
     * } $refs
     */
    public function isErr(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refErrors =& $refs[0];
        if ( array_key_exists(1, $refs) ) $refRet =& $refs[1];
        $refErrors = [];
        $refRet = $this;

        if ( [] !== $this->errorsRaw ) {
            $refErrors = $this->fetchErrors();

            return true;
        }

        return false;
    }

    /**
     * @param array{
     *     0: T,
     *     1: static<T>,
     * } $refs
     */
    public function isOk(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refValue =& $refs[0];
        if ( array_key_exists(1, $refs) ) $refRet =& $refs[1];
        $refValue = null;
        $refRet = $this;

        if ( [] !== $this->value ) {
            $refValue = $this->value[0];

            return true;
        }

        return false;
    }


    /**
     * @param array{
     *     0: \stdClass[],
     *     1: static<T>,
     * } $refs
     */
    public function isFail(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refErrors =& $refs[0];
        if ( array_key_exists(1, $refs) ) $refRet =& $refs[1];
        $refErrors = [];
        $refRet = $this;

        if ( [] === $this->errorsRaw ) {
            return false;
        }

        if ( [] !== $this->value ) {
            return false;
        }

        $refErrors = $this->fetchErrors();

        return true;
    }

    /**
     * @param array{
     *     0: T,
     *     1: \stdClass[],
     *     2: static<T>,
     * } $refs
     */
    public function isWarn(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refValue =& $refs[0];
        if ( array_key_exists(1, $refs) ) $refErrors =& $refs[1];
        if ( array_key_exists(2, $refs) ) $refRet =& $refs[2];
        $refErrors = [];
        $refRet = $this;

        if ( [] === $this->errorsRaw ) {
            return false;
        }

        if ( [] === $this->value ) {
            return false;
        }

        $refValue = $this->value[0];
        $refErrors = $this->fetchErrors();

        return true;
    }


    /**
     * @return T
     */
    public function orThrow($throwableArg = null, array $fileLine = [], ...$throwableArgs)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( null !== $throwableArg ) {
            $this->doAddError(null, $fileLine, $throwableArg, ...$throwableArgs);
        }

        $this->throwErrors();
    }

    /**
     * @param array{ 0?: mixed } $fallback
     *
     * @return T|mixed
     */
    public function orFallback(array $fallback = [], $throwableArg = null, array $fileLine = [], ...$throwableArgs)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( [] !== $fallback ) {
            return $fallback[0];
        }

        if ( null !== $throwableArg ) {
            $this->doAddError(null, $fileLine, $throwableArg, ...$throwableArgs);
        }

        $this->throwErrors();
    }


    /**
     * @return T|null
     */
    public function orNull(?self &$refRetTo = null)
    {
        if ( null === $refRetTo ) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        return null;
    }

    /**
     * @return T|false
     */
    public function orFalse(?self &$refRetTo = null)
    {
        if ( null === $refRetTo ) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        return false;
    }

    /**
     * @return T|float
     */
    public function orNan(?self &$refRetTo = null)
    {
        if ( null === $refRetTo ) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        return NAN;
    }

    /**
     * @return T|string
     */
    public function orEmptyString(?self &$refRetTo = null)
    {
        if ( null === $refRetTo ) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        return '';
    }

    /**
     * @return T|array
     */
    public function orEmptyArray(?self &$refRetTo = null)
    {
        if ( null === $refRetTo ) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        return [];
    }

    /**
     * @return T|\stdClass
     */
    public function orEmptyStdclass(?self &$refRetTo = null)
    {
        if ( null === $refRetTo ) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        return new \stdClass();
    }


    protected function fetchErrors(?bool $isAssociative = null) : array
    {
        $isAssociative = $isAssociative ?? false;

        $thePhp = Lib::php();

        $errorsQueue = $this->errorsRaw;

        if ( [] !== $errorsQueue ) {
            reset($errorsQueue);

            while ( null !== ($i = key($errorsQueue)) ) {
                if ( ! isset($this->errors[$i]) ) {
                    [
                        'file_line'      => $fileLine,
                        'throwable_args' => $throwableArgs,
                        // 'trace'          => $trace,
                    ] = current($errorsQueue);

                    $throwableArgsArray = $thePhp->throwable_args($fileLine, ...$throwableArgs);

                    $this->errors[$i] = $throwableArgsArray['messageObjectList'];
                }

                next($errorsQueue);
            }
        }

        $result = [];

        if ( $isAssociative ) {
            foreach ( $this->errors as $stdClasses ) {
                foreach ( $stdClasses as $stdClass ) {
                    $result[] = (array) $stdClass;
                }
            }

        } else {
            $result = array_merge(...$this->errors);
        }

        return $result;
    }

    protected function throwErrors() : void
    {
        $errorsQueue = $this->errorsRaw;

        $previousList = [];
        while ( [] !== $errorsQueue ) {
            [
                'file_line'      => $fileLine,
                'throwable_args' => $throwableArgs,
                'trace'          => $trace,
            ] = array_pop($errorsQueue);

            $previous = new LogicException(...$throwableArgs);
            $previous->setFile($fileLine[0]);
            $previous->setLine($fileLine[1]);
            $previous->setTrace($trace);

            $previousList[] = $previous;
        }

        throw new LogicException(...$previousList);
    }
}
