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
     * @var array{ 0?: T }
     */
    protected $value = [];
    /**
     * @var array{
     *     args: array,
     *     file: array{ 0: string, 1: int },
     * }[]
     */
    protected $errors = [];


    private function __construct()
    {
    }

    /**
     * @param array{ 0?: mixed } $fallback
     *
     * @return T|mixed
     */
    public function __invoke(
        array $fallback = [],
        array $fileLine = [], ...$throwableArgs
    )
    {
        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        if ([] !== $fallback) {
            return $fallback[ 0 ];
        }

        $errorList = $this->errors;

        $previousList = [];
        while ( [] !== $errorList ) {
            [
                'args' => $throwableArgsItem,
                'file' => $fileLineItem,
            ] = array_pop($errorList);

            $previousList[] = new LogicException($fileLineItem, ...$throwableArgsItem);
        }

        $fileLine = $fileLine ?: Lib::debug()->file_line();

        if ([] !== $throwableArgs) {
            throw new LogicException(
                $fileLine,
                ...$previousList
            );
        }

        throw new LogicException(
            $fileLine,
            ...$throwableArgs,
            ...$previousList
        );
    }


    /**
     * @return static
     */
    public static function new()
    {
        return new static();
    }


    /**
     * @param T $value
     *
     * @return static<T>
     */
    public static function val($value)
    {
        if ($value instanceof static) {
            throw new LogicException(
                [ 'The `value` should not be instance of: ' . static::class, $value ]
            );
        }

        $instance = new static();

        $instance->value = [ $value ];

        return $instance;
    }

    /**
     * @param Ret|mixed                  $throwableArg
     *
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return static<T>
     */
    public static function err($throwableArg, array $fileLine = [], ...$throwableArgs)
    {
        $instance = new static();

        if ($throwableArg instanceof static) {
            $instance->mergeFrom(
                $retFrom = $throwableArg,
                null, $fileLine, ...$throwableArgs
            );

        } else {
            $fileLine = $fileLine ?: Lib::debug()->file_line();

            $instance->addError($throwableArg, $fileLine, ...$throwableArgs);
        }

        return $instance;
    }


    /**
     * @param T $value
     *
     * @return T|static<T>
     */
    public static function ok(
        ?array $fallback,
        $value
    )
    {
        if ($value instanceof static) {
            throw new LogicException(
                [ 'The `value` should not be instance of: ' . static::class, $value ]
            );
        }

        if (null === $fallback) {
            $instance = new static();

            $instance->value = [ $value ];

            return $instance;
        }

        return $value;
    }

    /**
     * @param Ret|mixed                  $throwableArg
     *
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return T|static<T>
     */
    public static function throw(
        ?array $fallback,
        $throwableArg, array $fileLine = [], ...$throwableArgs
    )
    {
        $instance = new static();

        if ($throwableArg instanceof static) {
            $instance->mergeFrom(
                $retFrom = $throwableArg,
                null, $fileLine, ...$throwableArgs
            );

        } else {
            $fileLine = $fileLine ?: Lib::debug()->file_line();

            $instance->addError($throwableArg, $fileLine, ...$throwableArgs);
        }

        if (null === $fallback) {
            return $instance;
        }

        if ([] !== $fallback) {
            return $fallback[ 0 ];
        }

        $errorList = $instance->errors;

        $previousList = [];

        while ( [] !== $errorList ) {
            [
                'args' => $throwableArgsItem,
                'file' => $fileLineItem,
            ] = array_pop($errorList);

            $previousList[] = new LogicException($fileLineItem, ...$throwableArgsItem);
        }

        if ([] !== $throwableArgs) {
            throw new LogicException(
                $fileLine,
                ...$previousList
            );
        }

        throw new LogicException(
            $fileLine,
            ...$throwableArgs,
            ...$previousList
        );
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
        return $this->prepareErrors($isAssociative);
    }

    /**
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return static
     */
    public function addError($throwableArg, array $fileLine, ...$throwableArgs)
    {
        array_unshift($throwableArgs, $throwableArg);

        $fileLine = $fileLine ?: Lib::debug()->file_line();

        $this->errors[] = [
            'file' => $fileLine,
            'args' => $throwableArgs,
        ];

        return $this;
    }


    /**
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return static
     */
    public function mergeFrom(
        self $retFrom,
        $throwableArg = null, array $fileLine = [], ...$throwableArgs
    )
    {
        if ([] !== $retFrom->value) {
            $this->value = $retFrom->value;
        }

        if ([] !== $retFrom->errors) {
            $this->errors = array_merge(
                $this->errors,
                $retFrom->errors
            );
        }

        if (null !== $throwableArg) {
            $fileLine = $fileLine ?: Lib::debug()->file_line();

            $this->addError($throwableArg, $fileLine, ...$throwableArgs);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function mergeTo(
        self $retTo,
        $throwableArg = null, array $fileLine = [], ...$throwableArgs
    )
    {
        if ([] !== $this->value) {
            $retTo->value = $this->value;
        }

        if ([] !== $this->errors) {
            $retTo->errors = array_merge(
                $retTo->errors,
                $this->errors
            );
        }

        if (null !== $throwableArg) {
            $fileLine = $fileLine ?: Lib::debug()->file_line();

            $retTo->addError($throwableArg, $fileLine, ...$throwableArgs);
        }

        return $retTo;
    }


    /**
     * @param array{
     *     0: T,
     *     1: Ret<T>,
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
     *     1: Ret<T>,
     * } $refs
     */
    public function isFail(array $refs = []) : bool
    {
        if (array_key_exists(0, $refs)) $refErrors =& $refs[ 0 ];
        if (array_key_exists(1, $refs)) $refRet =& $refs[ 1 ];
        $refErrors = [];
        $refRet = $this;

        if ([] !== $this->errors) {
            $refErrors = $this->prepareErrors();
        }

        if ([] === $this->value) {
            return true;
        }

        return false;
    }

    /**
     * @param array{
     *     0: \stdClass[],
     *     1: Ret<T>,
     * } $refs
     */
    public function isErr(array $refs = []) : bool
    {
        if (array_key_exists(0, $refs)) $refErrors =& $refs[ 0 ];
        if (array_key_exists(1, $refs)) $refRet =& $refs[ 1 ];
        $refErrors = [];
        $refRet = $this;

        if ([] !== $this->errors) {
            $refErrors = $this->prepareErrors();

            return true;
        }

        return false;
    }


    /**
     * @return T
     */
    public function orThrow(array $fileLine = [], ...$throwableArgs)
    {
        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        $errorList = $this->errors;

        $previousList = [];
        while ( [] !== $errorList ) {
            [
                'file' => $fileLineItem,
                'args' => $throwableArgsItem,
            ] = array_pop($errorList);

            $previousList[] = new LogicException($fileLineItem, ...$throwableArgsItem);
        }

        $fileLine = $fileLine ?: Lib::debug()->file_line();

        if ([] !== $throwableArgs) {
            throw new LogicException(
                $fileLine,
                ...$previousList
            );
        }

        throw new LogicException(
            $fileLine,
            ...$throwableArgs,
            ...$previousList
        );
    }

    /**
     * @param array{ 0?: mixed } $fallback
     *
     * @return T|mixed
     */
    public function orFallback(
        array $fallback = [],
        array $fileLine = [], ...$throwableArgs
    )
    {
        if ([] !== $this->value) {
            return $this->value[ 0 ];
        }

        if ([] !== $fallback) {
            return $fallback[ 0 ];
        }

        $errorList = $this->errors;

        $previousList = [];
        while ( [] !== $errorList ) {
            [
                'file' => $fileLineItem,
                'args' => $throwableArgsItem,
            ] = array_pop($errorList);

            $previousList[] = new LogicException($fileLineItem, ...$throwableArgsItem);
        }

        $fileLine = $fileLine ?: Lib::debug()->file_line();

        if ([] !== $throwableArgs) {
            throw new LogicException(
                $fileLine,
                ...$previousList
            );
        }

        throw new LogicException(
            $fileLine,
            ...$throwableArgs,
            ...$previousList
        );
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


    protected function prepareErrors(?bool $isAssociative = null) : array
    {
        $isAssociative = $isAssociative ?? false;

        $thePhp = Lib::php();

        $errorsCurrent = $this->errors;

        $errorList = [];
        while ( [] !== $errorsCurrent ) {
            [
                'file' => $fileLineItem,
                'args' => $throwableArgsItem,
            ] = array_shift($errorsCurrent);

            $throwableArgsArray = $thePhp->throwable_args($fileLineItem, ...$throwableArgsItem);

            foreach ( $throwableArgsArray[ 'messageObjectList' ] as $messageObject ) {
                if ($isAssociative) {
                    $errorList[] = (array) $messageObject;

                } else {
                    $errorList[] = $messageObject;
                }
            }
        }

        return $errorList;
    }
}
