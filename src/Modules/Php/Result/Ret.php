<?php

namespace Gzhegow\Lib\Modules\Php\Result;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\ErrorBag\Error;
use Gzhegow\Lib\Modules\Php\ErrorBag\ErrorBag;


/**
 * @template T
 */
class Ret
{
    const MODE_RESULT_NULL  = 1;
    const MODE_RESULT_SELF  = 2;
    const MODE_RESULT_TRUE  = 3;
    const MODE_RESULT_VALUE = 4;

    const MODE_ERROR_FALLBACK = 1;
    const MODE_ERROR_FALSE    = 2;
    const MODE_ERROR_NULL     = 3;
    const MODE_ERROR_SELF     = 4;
    const MODE_ERROR_THROW    = 5;

    const LIST_MODE_RESULT = [
        self::MODE_RESULT_TRUE  => true,
        self::MODE_RESULT_NULL  => true,
        self::MODE_RESULT_SELF  => true,
        self::MODE_RESULT_VALUE => true,
    ];

    const LIST_MODE_ERROR = [
        self::MODE_ERROR_FALLBACK => true,
        self::MODE_ERROR_FALSE    => true,
        self::MODE_ERROR_NULL     => true,
        self::MODE_ERROR_SELF     => true,
        self::MODE_ERROR_THROW    => true,
    ];


    /**
     * @var int
     */
    public $modeResult = self::MODE_RESULT_VALUE;
    /**
     * @var int
     */
    public $modeError = self::MODE_ERROR_THROW;

    /**
     * @var array{ 0?: T }
     */
    protected $results;
    /**
     * @var ErrorBag
     */
    protected $errors;
    /**
     * @var array{ 0: mixed }
     */
    protected $fallback;


    private function __construct()
    {
    }


    public static function fromMode(
        int $modeResult,
        int $modeError,
        array $fallback = []
    )
    {
        if (! isset(static::LIST_MODE_RESULT[ $modeResult ])) {
            throw new LogicException(
                [
                    ''
                    . 'The `modeReturn` should be one of: '
                    . '[ ' . implode(' ][ ', array_keys(static::LIST_MODE_RESULT)) . ' ]',
                    //
                    $modeResult,
                ]
            );
        }

        if (! isset(static::LIST_MODE_ERROR[ $modeError ])) {
            throw new LogicException(
                [
                    ''
                    . 'The `modeThrow` should be one of: '
                    . '[ ' . implode(' ][ ', array_keys(static::LIST_MODE_ERROR)) . ' ]',
                    //
                    $modeError,
                ]
            );
        }

        $instance = new static();

        $instance->modeResult = $modeResult;
        $instance->modeError = $modeError;

        if ([] !== $fallback) {
            $instance->fallback = $fallback;
        }

        return $instance;
    }


    /**
     * @return static
     */
    public function merge(Ret $res)
    {
        if ($res === $this) {
            return $this;
        }

        if (null !== $res->results) {
            $this->results = (null === $this->results)
                ? $res->getResults()
                : array_merge($this->results, $res->getResults());
        }

        if (null !== $res->errors) {
            $this->getErrorBag()->merge($res->errors);
        }

        return $this;
    }


    /**
     * @param T $refResult
     */
    public function isOk(&$refResult = null, array $refsResults = []) : bool
    {
        $refResult = null;

        if (null !== $this->results) {
            $results = $this->results;

            $refResult = $results[ 0 ];

            $len = count($results);

            for ( $i = 0; $i < $len; $i++ ) {
                $ref =& $refsResults[ $i ];
                $ref = $results[ $i ];

                unset($ref);
            }

            return true;
        }

        return false;
    }

    /**
     * @param Error[] $refErrors
     */
    public function isErr(&$refErrors = null) : bool
    {
        $refErrors = null;

        if (null !== $this->results) {
            return false;
        }

        if (null !== $this->errors) {
            $refErrors = $this->getErrors();
        }

        return true;
    }


    /**
     * @return mixed
     */
    public function getFallback()
    {
        $fallback = $this->fallback ?? [];

        if ([] === $fallback) {
            throw new RuntimeException(
                [ 'The `fallback` is missing for given result', $this ]
            );
        }

        return $this->fallback[ 0 ];
    }


    /**
     * @param T $result
     *
     * @return static
     */
    public function setResult($result, ...$results)
    {
        if ($result === $this) {
            return $this;

        } elseif ($result instanceof Ret) {
            $resultsArray = ([] === $results)
                ? $result->getResults()
                : array_merge($result->getResults(), $results);

        } else {
            $resultsArray = ([] === $results)
                ? [ $result ]
                : array_merge([ $result ], $results);
        }

        $this->results = $resultsArray;

        return $this;
    }

    /**
     * @param array{ 0?: T }|null $fallback
     *
     * @return T
     */
    public function getResult(?array $fallback = null)
    {
        if (null === $this->results) {
            $fallback = null
                ?? $fallback
                ?? $this->fallback
                ?? [];

            if ([] === $fallback) {
                throw new RuntimeException(
                    [ 'The result is empty and no fallback given', $this ]
                );
            }

            return $fallback[ 0 ];
        }

        return $this->results[ 0 ];
    }

    public function getResults() : array
    {
        return $this->results;
    }


    /**
     * @return static
     */
    public function addError($error, array $tags = [], array $trace = [])
    {
        if ($error === $this) {
            return $this;

        } elseif ($error instanceof Ret) {
            $this->merge($error);

        } else {
            $this->errors = $this->errors ?? new ErrorBag();
            $this->errors->error($error, $tags, $trace);
        }

        return $this;
    }


    public function getErrorBag() : ErrorBag
    {
        return $this->errors = $this->errors ?? new ErrorBag();
    }

    public function getErrorBagByTags(array $andTags, array ...$orAndTags) : ErrorBag
    {
        $errorBag = new ErrorBag();

        if (null !== $this->errors) {
            $list = $this->errors->getErrorsByTags($andTags, ...$orAndTags);

            if ([] !== $list) {
                foreach ( $list as $e ) {
                    $errorBag->addError($e);
                }
            }
        }

        return $errorBag;
    }


    /**
     * @return Error[]
     */
    public function getErrors() : array
    {
        $list = [];

        if (null !== $this->errors) {
            $list = $this->errors->getErrors();
        }

        return $list;
    }

    /**
     * @return Error[]
     */
    public function getErrorsByTags(array $andTags, array ...$orAndTags) : array
    {
        $list = [];

        if (null !== $this->errors) {
            $list = $this->errors->getErrorsByTags($andTags, ...$orAndTags);
        }

        return $list;
    }


    public function getErrorList() : array
    {
        $list = [];

        if (null !== $this->errors) {
            $errorObjectList = $this->errors->getErrors();

            foreach ( $errorObjectList as $errorObj ) {
                $list[] = $errorObj->error;
            }
        }

        return $list;
    }

    public function getErrorListByTags(array $andTags, array ...$orAndTags) : array
    {
        $list = [];

        if (null !== $this->errors) {
            $errorBag = $this->errors->getErrorsByTags($andTags, ...$orAndTags);

            foreach ( $errorBag as $eObj ) {
                $list[] = $eObj->error;
            }
        }

        return $list;
    }
}
