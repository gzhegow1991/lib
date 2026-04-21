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
            $theRet = static::new();
            $theRet->value = [ $value ];

            return $theRet;

        } elseif ( is_bool($fb) ) {
            return $fb->getStatus();

        } elseif ( is_array($fb) ) {
            return $value;

        } elseif ( $fb instanceof self ) {
            $theRet = static::new();
            $theRet->value = [ $value ];

            $fb->mergeFrom($theRet);

            return $fb->getStatus();
        }

        return $value;
    }

    /**
     * @param static|array{ 0: mixed }|string|null $fb
     * @param static|mixed                         $err
     *
     * @return T|static<T>
     */
    public static function throw($fb, $err, array $fileLine = [], ...$errArgs)
    {
        $theRet = static::new();

        if ( $err instanceof self ) {
            $theRet->mergeFrom($err);

            $hasErrors = ([] !== $theRet->errors);
            $hasArgs = ([] !== $errArgs);
            $hasFileLine = ([] !== $fileLine);

            if ( $hasArgs ) {
                $theRet->_addLayer();
                $theRet->_addError(null, $fileLine, ...$errArgs);

            } elseif ( $hasErrors && $hasFileLine ) {
                $idx = array_key_last($theRet->errors);

                $errorLast = end($theRet->errors[$idx]);

                $theRet->_addLayer();
                $theRet->_addError($errorLast['trace'], $fileLine, ...$errorLast['throwable_args']);
            }

        } else {
            $theRet->_addError(null, $fileLine, $err, $errArgs);
        }

        if ( null === $fb ) {
            return $theRet;

        } elseif ( is_bool($fb) ) {
            return $fb->getStatus();

        } elseif ( is_array($fb) ) {
            if ( array_key_exists(0, $fb) ) {
                return $fb[0];
            }

            return $theRet->throwErrors();

        } elseif ( $fb instanceof self ) {
            $fb->mergeFrom($theRet);

            return $fb->getStatus();
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
     * @param null|array[]                    $trace
     * @param null|array{ 0: string, 1: int } $fileLine
     *
     * @return static
     */
    protected function _addError(?array $trace, ?array $fileLine, $errArg, ...$errArgs)
    {
        $eTrace = ($trace ?: null);
        $eFileLine = ($fileLine ?: null);

        $getTrace = null;
        if ( static::staticShouldTrace() ) {
            if ( null === $eTrace ) {
                $getTrace = $getTrace ?? Lib::trace(2);

                $eTrace = $getTrace['trace'];
            }

            if ( null === $eFileLine ) {
                $getTrace = $getTrace ?? Lib::trace(2);

                $eFileLine = [];
                $eFileLine['file'] = $getTrace['file_line']['file'];
                $eFileLine['line'] = $getTrace['file_line']['line'];
            }
        }

        if ( null !== $eFileLine ) {
            $eFileLine['file'] = $eFileLine['file'] ?? $eFileLine[0] ?? '{{file}}';
            $eFileLine['line'] = $eFileLine['line'] ?? $eFileLine[1] ?? -1;

            $eFileLine = [
                'file' => $eFileLine['file'],
                'line' => $eFileLine['line'],
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
            $this->value = $retFrom->value;
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
            $retTo->value = $this->value;
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
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return T
     */
    public function orThrow($err = null, array $fileLine = [], ...$errArgs)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( null !== $err ) {
            $this->_addLayer();
            $this->_addError(null, $fileLine, $err, ...$errArgs);
        }

        $this->throwErrors();
    }

    /**
     * @param array{ 0?: mixed }         $fb
     *
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return T|mixed
     */
    public function orFallback(array $fb, $err = null, array $fileLine = [], ...$errArgs)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( [] !== $fb ) {
            return $fb[0];
        }

        if ( null !== $err ) {
            $this->_addLayer();
            $this->_addError(null, $fileLine, $err, ...$errArgs);
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
            $previousList = [];

            foreach ( $layer as $err ) {
                $eArgs = $err['throwable_args'];
                $eFileLine = $err['file_line'];
                $eTrace = $err['trace'];

                $previous = new Except(...$eArgs);
                $previous->setFile($eFileLine['file']);
                $previous->setLine($eFileLine['line']);
                $previous->setTrace($eTrace);

                $previousList[] = $previous;
            }

            $current = new Except($current, ...$previousList);
        }

        throw new LogicException($current);
    }
}
