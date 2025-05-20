<?php

namespace Gzhegow\Lib\Modules\Php\Result;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\ErrorBag\Error;
use Gzhegow\Lib\Modules\Php\ErrorBag\ErrorBag;


/**
 * @template T
 */
class Res
{
    const MODE_RESULT_NULL  = 1;
    const MODE_RESULT_SELF  = 2;
    const MODE_RESULT_TRUE  = 3;
    const MODE_RESULT_VALUE = 4;

    const MODE_ERROR_FALSE = 1;
    const MODE_ERROR_NULL  = 2;
    const MODE_ERROR_SELF  = 3;
    const MODE_ERROR_THROW = 4;

    const LIST_MODE_RESULT = [
        self::MODE_RESULT_TRUE  => true,
        self::MODE_RESULT_NULL  => true,
        self::MODE_RESULT_SELF  => true,
        self::MODE_RESULT_VALUE => true,
    ];

    const LIST_MODE_ERROR = [
        self::MODE_ERROR_FALSE => true,
        self::MODE_ERROR_NULL  => true,
        self::MODE_ERROR_SELF  => true,
        self::MODE_ERROR_THROW => true,
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
    protected $result = [];
    /**
     * @var ErrorBag
     */
    protected $errors;


    private function __construct()
    {
    }


    public static function fromMode(int $modeResult, int $modeError)
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

        return $instance;
    }


    /**
     * @return static
     */
    public function merge(Res $item)
    {
        if (null !== $item->errors) {
            $this->getErrors()->merge($item->errors);
        }

        return $this;
    }


    /**
     * @param T &$result
     *
     * @return bool
     */
    public function isOk(&$result = null) : bool
    {
        $result = null;

        if ([] !== $this->result) {
            $result = $this->result[ 0 ];

            return true;
        }

        return false;
    }

    /**
     * @param T $result
     *
     * @return static
     */
    public function setResult($result)
    {
        if ($result instanceof Res) {
            $this->result = [ $result->getResult() ];

        } else {
            $this->result = [ $result ];
        }

        return $this;
    }

    /**
     * @param array{ 0?: T } $fallback
     *
     * @return T
     */
    public function getResult(array $fallback = [])
    {
        if ([] === $this->result) {
            if ([] === $fallback) {
                throw new RuntimeException(
                    [ 'The result is empty', $this ]
                );
            }

            return $fallback[ 0 ];
        }

        return $this->result[ 0 ];
    }


    /**
     * @param Error[]|null &$errors
     *
     * @return bool
     */
    public function isErr(array &$errors = null) : bool
    {
        $errors = null;

        if ([] !== $this->result) {
            return false;
        }

        if (null !== $this->errors) {
            $errors = $this->getErrors();
        }

        return true;
    }

    /**
     * @return static
     */
    public function addError($error, array $tags = [], array $trace = [])
    {
        if ($error instanceof Res) {
            $this->merge($error);

        } else {
            if (null === $this->errors) {
                $this->errors = new ErrorBag();
            }

            $this->errors->error($error, $tags, $trace);
        }

        return $this;
    }


    public function getErrors() : ErrorBag
    {
        return $this->errors = $this->errors ?? new ErrorBag();
    }

    public function getErrorsByTags(array $andTags, array ...$orAndTags) : ErrorBag
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


    public function errors() : array
    {
        $list = [];

        if (null !== $this->errors) {
            $errorBag = $this->errors->getErrors();

            foreach ( $errorBag as $errorObject ) {
                $list[] = $errorObject->error;
            }
        }

        return $list;
    }

    public function errorsByTags(array $andTags, array ...$orAndTags) : array
    {
        $list = [];

        if (null !== $this->errors) {
            $errorBag = $this->errors->getErrorsByTags($andTags, ...$orAndTags);

            foreach ( $errorBag as $errorObject ) {
                $list[] = $errorObject->error;
            }
        }

        return $list;
    }
}
