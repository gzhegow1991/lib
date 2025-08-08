<?php

namespace Gzhegow\Lib\Modules\Type\Ret\Base;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @template T of mixed
 */
abstract class AbstractRet
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
     *     file_line: array{ 0: string, 1: int },
     *     throwable_args: array
     * }[]
     */
    protected $errorsRaw = [];


    public function getStatus() : bool
    {
        return [] !== $this->value;
    }

    /**
     * @return T
     */
    public function getValue()
    {
        if ([] === $this->value) {
            throw new RuntimeException(
                [ 'The `value` should exists', $this ]
            );
        }

        return $this->value[ 0 ];
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
        array_unshift($throwableArgs, $throwableArg);

        $fileLine = $fileLine ?: Lib::debug()->file_line();

        $this->errorsRaw[] = [
            'file_line'      => $fileLine,
            'throwable_args' => $throwableArgs,
        ];

        return $this;
    }


    /**
     * @return static
     */
    public function mergeFrom(self $retFrom)
    {
        if ([] !== $retFrom->value) {
            $this->value = $retFrom->value;
        }

        if ([] !== $retFrom->errorsRaw) {
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
        if ([] !== $this->value) {
            $retTo->value = $this->value;
        }

        if ([] !== $this->errorsRaw) {
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
        if (array_key_exists(0, $refs)) $refValue =& $refs[ 0 ];
        if (array_key_exists(1, $refs)) $refRet =& $refs[ 1 ];
        $refValue = null;
        $refRet = $this;

        if ([] !== $this->value) {
            $refValue = $this->value[ 0 ];

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
        if (array_key_exists(0, $refs)) $refErrors =& $refs[ 0 ];
        if (array_key_exists(1, $refs)) $refRet =& $refs[ 1 ];
        $refErrors = [];
        $refRet = $this;

        if ([] !== $this->errorsRaw) {
            $refErrors = $this->fetchErrors();
        }

        if ([] === $this->value) {
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
    public function isErr(array $refs = []) : bool
    {
        if (array_key_exists(0, $refs)) $refErrors =& $refs[ 0 ];
        if (array_key_exists(1, $refs)) $refRet =& $refs[ 1 ];
        $refErrors = [];
        $refRet = $this;

        if ([] !== $this->errorsRaw) {
            $refErrors = $this->fetchErrors();

            return true;
        }

        return false;
    }


    /**
     * @return T
     */
    public function orThrow(...$throwableArgs)
    {
        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        if ([] !== $throwableArgs) {
            $this->errorsRaw[] = [
                'file_line'      => Lib::debug()->file_line(),
                'throwable_args' => $throwableArgs,
            ];
        }

        $this->throwErrors();
    }

    /**
     * @return T
     */
    public function orThrowAt($throwableArg = null, array $fileLine = [], ...$throwableArgs)
    {
        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        if (null !== $throwableArg) {
            array_unshift($throwableArgs, $throwableArg);

            $fileLine = $fileLine ?? Lib::debug()->file_line();

            $this->errorsRaw[] = [
                'file_line'      => $fileLine,
                'throwable_args' => $throwableArgs,
            ];
        }

        $this->throwErrors();
    }


    /**
     * @param array{ 0?: mixed } $fallback
     *
     * @return T|mixed
     */
    public function orFallback(array $fallback = [], ...$throwableArgs)
    {
        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        if ([] !== $fallback) {
            return $fallback[ 0 ];
        }

        if ([] !== $throwableArgs) {
            $this->errorsRaw[] = [
                'file_line'      => Lib::debug()->file_line(),
                'throwable_args' => $throwableArgs,
            ];
        }

        $this->throwErrors();
    }

    /**
     * @param array{ 0?: mixed } $fallback
     *
     * @return T|mixed
     */
    public function orFallbackAt(array $fallback, $throwableArg = null, array $fileLine = [], ...$throwableArgs)
    {
        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        if ([] !== $fallback) {
            return $fallback[ 0 ];
        }

        if (null !== $throwableArg) {
            array_unshift($throwableArgs, $throwableArg);

            $fileLine = $fileLine ?? Lib::debug()->file_line();

            $this->errorsRaw[] = [
                'file_line'      => $fileLine,
                'throwable_args' => $throwableArgs,
            ];
        }

        $this->throwErrors();
    }


    /**
     * @return T|null
     */
    public function orNull(?self &$refRetTo = null)
    {
        if (null === $refRetTo) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        return null;
    }

    /**
     * @return T|false
     */
    public function orFalse(?self &$refRetTo = null)
    {
        if (null === $refRetTo) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        return false;
    }

    /**
     * @return T|float
     */
    public function orNan(?self &$refRetTo = null)
    {
        if (null === $refRetTo) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        return NAN;
    }

    /**
     * @return T|string
     */
    public function orEmptyString(?self &$refRetTo = null)
    {
        if (null === $refRetTo) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        return '';
    }

    /**
     * @return T|array
     */
    public function orEmptyArray(?self &$refRetTo = null)
    {
        if (null === $refRetTo) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        return [];
    }

    /**
     * @return T|\stdClass
     */
    public function orEmptyStdclass(?self &$refRetTo = null)
    {
        if (null === $refRetTo) {
            $refRetTo = $this;

        } else {
            $this->mergeTo($refRetTo);
        }

        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        return new \stdClass();
    }


    protected function fetchErrors(?bool $isAssociative = null) : array
    {
        $isAssociative = $isAssociative ?? false;

        $thePhp = Lib::php();

        $errorsQueue = $this->errorsRaw;

        if ([] !== $errorsQueue) {
            reset($errorsQueue);

            while ( null !== ($i = key($errorsQueue)) ) {
                if (! isset($this->errors[ $i ])) {
                    [
                        'file_line'      => $fileLine,
                        'throwable_args' => $throwableArgs,
                    ] = current($errorsQueue);

                    $throwableArgsArray = $thePhp->throwable_args($fileLine, ...$throwableArgs);

                    $this->errors[ $i ] = $throwableArgsArray[ 'messageObjectList' ];
                }

                next($errorsQueue);
            }
        }

        $result = [];

        if ($isAssociative) {
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
            ] = array_pop($errorsQueue);

            $previousList[] = new LogicException($fileLine, ...$throwableArgs);
        }

        throw new LogicException(...$previousList);
    }
}
