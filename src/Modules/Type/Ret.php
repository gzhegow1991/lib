<?php

namespace Gzhegow\Lib\Modules\Type;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\Except;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @template T of mixed
 */
class Ret
{
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
     *     args: array,
     *     file_line: array{ 0: string, 1: int },
     *     trace: array[],
     * }[]
     */
    protected $errorsRaw = [];


    /**
     * @var bool
     */
    protected static $shouldTrace = false;

    /**
     * @param int|false|null $shouldTrace
     */
    public static function staticShouldTrace(?bool $shouldTrace = null) : bool
    {
        $last = static::$shouldTrace;

        if ( null !== $shouldTrace ) {
            if ( false === $shouldTrace ) {
                static::$shouldTrace = false;

            } else {
                static::$shouldTrace = (bool) $shouldTrace;
            }
        }

        static::$shouldTrace = static::$shouldTrace ?? false;

        return $last;
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
     * @param static|mixed $fallback
     * @param T            $value
     *
     * @return T|static<T>
     */
    public static function ok($fallback, $value)
    {
        if ( $value instanceof self ) {
            throw new LogicException(
                [ 'The `value` should not be instance of: ' . self::class, $value ]
            );
        }

        $theRet = static::new();
        $theRet->value = [ $value ];

        if ( null === $fallback ) {
            return $theRet;

        } elseif ( $fallback instanceof self ) {
            $fallback->mergeFrom($theRet);

            return $fallback->isOk();
        }

        return $value;
    }

    /**
     * @param static|mixed $fallback
     * @param static|mixed $errArg
     *
     * @return T|static<T>
     */
    public static function throw($fallback, $errArg, array $fileLine = [], ...$errArgs)
    {
        $theRet = static::new();

        if ( $errArg instanceof self ) {
            $theRet->mergeFrom($errArg);

            if ( [] !== $errArgs ) {
                $theRet->_addError(null, $fileLine, ...$errArgs);

            } elseif ( ([] !== $fileLine) && ([] !== $errArg->errorsRaw) ) {
                $errorLast = end($errArg->errorsRaw);

                $theRet->_addError($errorLast['trace'], $fileLine, ...$errorLast['throwable_args']);
            }

        } else {
            $theRet->_addError(null, $fileLine, $errArg, $errArgs);
        }

        if ( null === $fallback ) {
            return $theRet;

        } elseif ( is_array($fallback) ) {
            if ( array_key_exists(0, $fallback) ) {
                return $fallback[0];
            }

        } elseif ( $fallback instanceof self ) {
            $fallback->mergeFrom($theRet);

            return $fallback->isOk();
        }

        $theRet->throwErrors();
    }


    public function getStatus() : bool
    {
        return [] !== $this->value;
    }

    /**
     * @return T
     */
    public function getValue()
    {
        if ( [] === $this->value ) {
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
        return $this->_getErrors($isAssociative);
    }

    protected function _getErrors(?bool $isAssociative = null) : array
    {
        $isAssociative = $isAssociative ?? false;

        $this->prepareErrors();

        $result = [];

        if ( $isAssociative ) {
            foreach ( $this->errors as $stdClasses ) {
                foreach ( $stdClasses as $stdClass ) {
                    $result[] = (array) $stdClass;
                }
            }

        } else {
            foreach ( $this->errors as $stdClasses ) {
                foreach ( $stdClasses as $stdClass ) {
                    $result[] = $stdClass;
                }
            }
        }

        return $result;
    }


    /**
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return static
     */
    public function addError($errArg, array $fileLine = [], ...$errArgs)
    {
        $this->_addError(null, $fileLine, $errArg, $errArgs);

        return $this;
    }

    /**
     * @param null|array{ 0: string, 1: int } $fileLine
     *
     * @return static
     */
    protected function _addError(?array $trace, ?array $fileLine, $errArg, ...$errArgs)
    {
        $eTrace = ($trace ?: null);
        $eFileLine = ($fileLine ?: null);

        if ( (null === $eTrace) && static::staticShouldTrace() ) {
            $traceArray = $this->getTrace(2);

            $eTrace = $traceArray['trace'];

            if ( null === $eFileLine ) {
                $eFileLine = [];
                $eFileLine['file'] = $traceArray['file_line']['file'];
                $eFileLine['line'] = $traceArray['file_line']['line'];
            }
        }

        foreach ( $errArgs as $i => $t ) {
            if ( null === $t ) {
                unset($errArgs[$i]);
            }
        }

        $eArgs = array_values($errArgs);

        if ( null !== $errArg ) {
            array_unshift($eArgs, $errArg);
        }

        $this->errorsRaw[] = [
            'throwable_args' => $eArgs,
            'file_line'      => $eFileLine,
            'trace'          => $eTrace,
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

        if ( [] === $this->value ) {
            return false;
        }

        $refValue = $this->value[0];

        return true;
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

        if ( [] !== $this->value ) {
            return false;
        }

        foreach ( $this->_getErrors() as $error ) {
            $refErrors[] = $error;
        }

        return true;
    }


    /**
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return T
     */
    public function orThrow($errArg = null, array $fileLine = [], ...$errArgs)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( null !== $errArg ) {
            $this->_addError(null, $fileLine, $errArg, ...$errArgs);
        }

        $this->throwErrors();
    }

    /**
     * @param array{ 0?: mixed }         $fallback
     *
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return T|mixed
     */
    public function orFallback(array $fallback = [], $errArg = null, array $fileLine = [], ...$errArgs)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( [] !== $fallback ) {
            return $fallback[0];
        }

        if ( null !== $errArg ) {
            $this->_addError(null, $fileLine, $errArg, ...$errArgs);
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


    protected function prepareErrors() : void
    {
        $thePhp = Lib::php();

        $errorsRaw = $this->errorsRaw;

        if ( [] !== $errorsRaw ) {
            foreach ( $errorsRaw as $i => $err ) {
                if ( isset($this->errors[$i]) ) {
                    continue;
                }

                [
                    'throwable_args' => $eArgs,
                    'file_line'      => $eFileLine,
                ] = $err;

                $throwableArgsArray = $thePhp->throwable_args($eFileLine, ...$eArgs);

                $this->errors[$i] = $throwableArgsArray['messageObjectList'];
            }
        }
    }

    protected function throwErrors() : void
    {
        $errorsRaw = $this->errorsRaw;

        $previousList = [];

        while ( [] !== $errorsRaw ) {
            [
                'throwable_args' => $eArgs,
                'file_line'      => $eFileLine,
                'trace'          => $eTrace,
            ] = array_pop($errorsRaw);

            $previous = new Except(...$eArgs);
            $previous->setFile($eFileLine[0]);
            $previous->setLine($eFileLine[1]);
            $previous->setTrace($eTrace);

            $previousList[] = $previous;
        }

        throw new LogicException(...$previousList);
    }


    protected function getTrace(int $shift = 0, ?array $trace = null) : array
    {
        $exTrace = $trace;
        if ( null === $trace ) {
            $ex = new \Exception();
            $exTrace = $ex->getTrace();
        }

        while ( $shift > 0 ) {
            next($exTrace);

            $shift--;
        }

        $eTrace = [];
        for ( $i = key($exTrace); $i < count($exTrace); $i++ ) {
            $eTrace[] = $exTrace[$i];
        }

        $eFile = $eTrace[0]['file'] ?? '{file}';
        $eLine = $eTrace[0]['line'] ?? -1;

        $eFileLine = [
            'file' => $eFile,
            'line' => $eLine,
        ];

        return [
            'file_line' => $eFileLine,
            'trace'     => $eTrace,
        ];
    }
}
