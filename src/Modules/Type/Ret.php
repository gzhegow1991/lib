<?php

namespace Gzhegow\Lib\Modules\Type;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\Except;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\AggregateExcept;
use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @template T of mixed
 */
class Ret
{
    /**
     * @var array{ 0?: T }
     */
    protected $value = [];

    /**
     * @var array{
     *     throwable_args: array,
     *     file_line: array{ 0: string, 1: int },
     *     trace: array[],
     * }[][]
     */
    protected $errors = [];
    /**
     * @var \stdClass[][]
     */
    protected $errorsStd = [];


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
     * @param static|array{ 0: mixed }|string|null $fb
     * @param T                                    $value
     *
     * @return T|static<T>
     */
    public static function ok($fb, $value)
    {
        if ( $value instanceof self ) {
            throw new LogicException(
                [ 'The `value` should not be instance of: ' . self::class, $value ]
            );
        }

        if ( null === $fb ) {
            // > ret object

            $theRet = static::new();
            $theRet->value = [ $value ];

            $result = $theRet;

        } elseif ( is_array($fb) ) {
            // > value

            $result = $value;

        } elseif ( $fb instanceof self ) {
            // > boolean

            $theRet = static::new();
            $theRet->value = [ $value ];

            $fb->mergeFrom($theRet);

            $result = $fb->getStatus();

        } else {
            throw new LogicException(
                [ 'The `fb` should be null, array or instance of: ' . self::class, $fb ]
            );
        }

        return $result;
    }

    /**
     * @param static|array{ 0: mixed }|string|null $fb
     * @param static|mixed                         $err
     *
     * @return T|static<T>
     */
    public static function throw($fb, $err, ?array $fileLine = null, ...$errArgs)
    {
        $theRet = static::new();

        if ( $err instanceof self ) {
            $theRet->mergeFrom($err);

            $hasFileLine = (null !== $fileLine);
            $hasArgs = ([] !== $errArgs);

            if ( $hasArgs ) {
                $theDebug = Lib::debug();

                $eTrace = null;
                $eFileLine = $fileLine;
                if ( $theDebug->stateShouldTrace() ) {
                    $refs = [];

                    $eTrace = Lib::trace($refs);
                    $eFileLine = $fileLine ?? Lib::file_line($refs);
                }

                $theRet->_addLayer();
                $theRet->_addError($eTrace, $eFileLine, ...$errArgs);

            } elseif ( $hasFileLine ) {
                $hasErrors = ([] !== $theRet->errors);

                if ( $hasErrors ) {
                    $theDebug = Lib::debug();

                    $idx = array_key_last($theRet->errors);

                    $errorLast = end($theRet->errors[$idx]);

                    $eTrace = null;
                    $eFileLine = $fileLine;
                    if ( $theDebug->stateShouldTrace() ) {
                        $refs = [];

                        $eTrace = Lib::trace($refs);
                        $eFileLine = $fileLine ?? Lib::file_line($refs);
                    }

                    $theRet->_addLayer();
                    $theRet->_addError($eTrace, $eFileLine, ...$errorLast['throwable_args']);
                }
            }

        } else {
            $theDebug = Lib::debug();

            $eTrace = null;
            $eFileLine = $fileLine;
            if ( $theDebug->stateShouldTrace() ) {
                $refs = [];

                $eTrace = Lib::trace($refs);
                $eFileLine = $fileLine ?? Lib::file_line($refs);
            }

            $theRet->_addError($eTrace, $eFileLine, $err, $errArgs);
        }

        $result = [];

        if ( null === $fb ) {
            // > ret object
            $result = [ $theRet ];

        } elseif ( is_array($fb) ) {
            // > default/throw

            if ( array_key_exists(0, $fb) ) {
                $result = [ $fb[0] ];
            }

        } elseif ( $fb instanceof self ) {
            // > boolean

            $fb->mergeFrom($theRet);

            $result = [ $fb->getStatus() ];

        } else {
            throw new LogicException(
                [ 'The `fb` should be null, array or instance of: ' . self::class, $fb ]
            );
        }

        if ( [] !== $result ) {
            return $result[0];
        }

        $theRet->throwErrors();
    }


    public function getStatus() : bool
    {
        return [] !== $this->value;
    }

    public function isTrue() : bool
    {
        return [] !== $this->value;
    }

    public function isFalse() : bool
    {
        return [] === $this->value;
    }


    /**
     * @return bool
     */
    public function hasValue(array $refs = [])
    {
        $refValue =& $refs[0];
        $refValue = null;

        if ( [] === $this->value ) {
            return false;
        }

        $refValue = $this->value[0];

        return true;
    }

    /**
     * @return T
     */
    public function getValue()
    {
        if ( [] === $this->value ) {
            throw new RuntimeException(
                [ 'The `value` is undefined', $this ]
            );
        }

        return $this->value[0];
    }


    /**
     * @return \stdClass[]|array[]
     */
    public function getErrors(?bool $isAssociative = null) : array
    {
        return $this->_getErrors($isAssociative);
    }

    /**
     * @return \stdClass[]|array[]
     */
    protected function _getErrors(?bool $isAssociative = null) : array
    {
        $isAssociative = $isAssociative ?? false;

        $this->prepareErrors();

        $result = [];

        if ( $isAssociative ) {
            foreach ( $this->errorsStd as $layer ) {
                foreach ( $layer as $stdClasses ) {
                    foreach ( $stdClasses as $stdClass ) {
                        $result[] = (array) $stdClass;
                    }
                }
            }

        } else {
            foreach ( $this->errorsStd as $layer ) {
                foreach ( $layer as $stdClasses ) {
                    foreach ( $stdClasses as $stdClass ) {
                        $result[] = $stdClass;
                    }
                }
            }
        }

        return $result;
    }


    /**
     * @return static
     */
    public function addError($errArg, ?array $fileLine = null, ...$errArgs)
    {
        $theDebug = Lib::debug();

        $eTrace = null;
        $eFileLine = $fileLine;
        if ( $theDebug->stateShouldTrace() ) {
            $refs = [];

            $eTrace = Lib::trace($refs);
            $eFileLine = $fileLine ?? Lib::file_line($refs);
        }

        $this->_addError($eTrace, $eFileLine, $errArg, $errArgs);

        return $this;
    }

    /**
     * @return static
     */
    protected function _addError(?array $trace, ?array $fileLine, $errArg, ...$errArgs)
    {
        $eTrace = $trace ?: null;
        $eFileLine = $fileLine ?: null;

        if ( null !== $eFileLine ) {
            $theDebug = Lib::debug();

            $eFileLine = [
                'file' => $theDebug->file_for_trace($eFileLine['file'] ?? $eFileLine[0] ?? null, ''),
                'line' => $theDebug->line_for_trace($eFileLine['line'] ?? $eFileLine[1] ?? null),
            ];
        }

        foreach ( $errArgs as $i => $t ) {
            if ( null === $t ) {
                unset($errArgs[$i]);
            }
        }

        $eThrowableArgs = array_values($errArgs);

        if ( null !== $errArg ) {
            array_unshift($eThrowableArgs, $errArg);
        }

        if ( [] === $this->errors ) {
            $this->_addLayer();
        }

        $idx = array_key_last($this->errors);

        $this->errors[$idx][] = [
            'throwable_args' => $eThrowableArgs,
            //
            'file_line'      => $eFileLine,
            'trace'          => $eTrace,
        ];

        return $this;
    }

    protected function _addLayer()
    {
        $this->errors[] = [];

        return $this;
    }


    /**
     * @return static
     */
    public function mergeFrom(self $retFrom)
    {
        if ( [] !== $retFrom->value ) {
            if ( [] === $this->value ) {
                $this->value = $retFrom->value;
            }
        }

        if ( [] !== $retFrom->errors ) {
            if ( [] === $this->errors ) {
                $this->_addLayer();
            }

            $idx = array_key_last($this->errors);
            $idxFrom = array_key_last($retFrom->errors);

            $this->errors[$idx] = array_merge(
                $this->errors[$idx],
                $retFrom->errors[$idxFrom]
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
            if ( [] === $retTo->value ) {
                $retTo->value = $this->value;
            }
        }

        if ( [] !== $this->errors ) {
            if ( [] === $retTo->errors ) {
                $retTo->_addLayer();
            }

            $idxTo = array_key_last($retTo->errors);
            $idx = array_key_last($this->errors);

            $retTo->errors[$idxTo] = array_merge(
                $retTo->errors[$idxTo],
                $this->errors[$idx]
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
     * @return T
     */
    public function orThrow($err = null, ?array $fileLine = null, ...$errArgs)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( null !== $err ) {
            $theDebug = Lib::debug();

            $eTrace = null;
            $eFileLine = $fileLine;
            if ( $theDebug->stateShouldTrace() ) {
                $refs = [];

                $eTrace = Lib::trace($refs);
                $eFileLine = $fileLine ?? Lib::file_line($refs);
            }

            $this->_addLayer();
            $this->_addError($eTrace, $eFileLine, $err, ...$errArgs);
        }

        $this->throwErrors();
    }

    /**
     * @param array{ 0?: mixed } $fb
     *
     * @return T|mixed
     */
    public function orFallback(array $fb, $err = null, ?array $fileLine = null, ...$errArgs)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( [] !== $fb ) {
            return $fb[0];
        }

        if ( null !== $err ) {
            $theDebug = Lib::debug();

            $eTrace = null;
            $eFileLine = $fileLine;
            if ( $theDebug->stateShouldTrace() ) {
                $refs = [];

                $eTrace = Lib::trace($refs);
                $eFileLine = $fileLine ?? Lib::file_line($refs);
            }

            $this->_addLayer();
            $this->_addError($eTrace, $eFileLine, $err, ...$errArgs);
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


    protected function prepareErrors()
    {
        $thePhp = Lib::php();

        if ( [] !== $this->errors ) {
            foreach ( $this->errors as $i => $errors ) {
                foreach ( $errors as $ii => $err ) {
                    if ( isset($this->errorsStd[$i][$ii]) ) {
                        continue;
                    }

                    $eFileLine = $err['file_line'];
                    $eArgs = $err['throwable_args'];

                    $throwableArgsArray = $thePhp->throwable_args($eFileLine, ...$eArgs);

                    $this->errorsStd[$i][$ii] = $throwableArgsArray['messageObjectList'];
                }
            }
        }

        return $this;
    }

    protected function throwErrors()
    {
        $current = null;

        foreach ( $this->errors as $layer ) {
            if ( [] === $layer ) {
                continue;
            }

            $previous = $current;

            if ( count($layer) > 1 ) {
                $previousList = [];

                foreach ( $layer as $err ) {
                    $eArgs = $err['throwable_args'];
                    $eFileLine = $err['file_line'];
                    $eTrace = $err['trace'];

                    $eFile = $eFileLine['file'] ?? null;
                    $eLine = $eFileLine['line'] ?? null;

                    $previous = new Except(...$eArgs);

                    $previous->setFileOverride($eFile);
                    $previous->setLineOverride($eLine);
                    $previous->setTraceOverride($eTrace);

                    $previousList[] = $previous;
                }

                $current = new AggregateExcept($previousList);

            } else {
                $err = reset($layer);

                $eArgs = $err['throwable_args'];
                $eFileLine = $err['file_line'];
                $eTrace = $err['trace'];

                $eFile = $eFileLine['file'] ?? null;
                $eLine = $eFileLine['line'] ?? null;

                $current = new Except(...$eArgs);

                $current->setTraceOverride($eTrace);
                $current->setFileOverride($eFile);
                $current->setLineOverride($eLine);
            }

            $current->setPreviousOverride($previous);
        }

        $ex = RuntimeException::fromExcept($current);

        throw $ex;
    }
}
