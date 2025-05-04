<?php

namespace Gzhegow\Lib\Modules\Php\Result;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\ErrorBag\Error;
use Gzhegow\Lib\Modules\Php\ErrorBag\ErrorBag;


class ResultContext
{
    const MODE_RETURN_BOOLEAN = 2;
    const MODE_RETURN_CONTEXT = 4;
    const MODE_RETURN_NULL    = 3;
    const MODE_RETURN_VALUE   = 1;

    const MODE_THROW_ON  = 1;
    const MODE_THROW_OFF = 2;

    const LIST_MODE_RETURN = [
        self::MODE_RETURN_BOOLEAN => true,
        self::MODE_RETURN_CONTEXT => true,
        self::MODE_RETURN_NULL    => true,
        self::MODE_RETURN_VALUE   => true,
    ];

    const LIST_MODE_THROW = [
        self::MODE_THROW_ON  => true,
        self::MODE_THROW_OFF => true,
    ];


    /**
     * @var int
     */
    protected $modeReturn = self::MODE_RETURN_VALUE;
    /**
     * @var int
     */
    protected $modeThrow = self::MODE_THROW_ON;

    /**
     * @var array{ 0?: mixed }
     */
    protected $result = [];
    /**
     * @var ErrorBag
     */
    protected $errors;


    private function __construct()
    {
    }


    public static function fromMode(int $modeReturn, int $modeThrow)
    {
        if (! isset(static::LIST_MODE_RETURN[ $modeReturn ])) {
            throw new LogicException(
                [
                    ''
                    . 'The `modeReturn` should be one of: '
                    . '[ ' . implode(' ][ ', array_keys(static::LIST_MODE_RETURN)) . ' ]',
                    //
                    $modeReturn,
                ]
            );
        }

        if (! isset(static::LIST_MODE_THROW[ $modeThrow ])) {
            throw new LogicException(
                [
                    ''
                    . 'The `modeThrow` should be one of: '
                    . '[ ' . implode(' ][ ', array_keys(static::LIST_MODE_THROW)) . ' ]',
                    //
                    $modeThrow,
                ]
            );
        }

        $instance = new static();
        $instance->modeReturn = $modeReturn;
        $instance->modeThrow = $modeThrow;

        return $instance;
    }


    /**
     * @return static
     */
    public function merge(ResultContext $item)
    {
        if (null !== $item->errors) {
            $this->getErrors()->merge($item->errors);
        }

        return $this;
    }


    public function isModeReturn(int $modeReturn) : bool
    {
        return $this->modeReturn === $modeReturn;
    }

    public function isModeThrow(int $modeThrow) : bool
    {
        return $this->modeThrow === $modeThrow;
    }


    /**
     * @param mixed &$result
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
            $errors = $this->errors();
        }

        return true;
    }


    /**
     * @return static
     */
    public function ok($result)
    {
        $this->result = [ $result ];

        return $this;
    }

    /**
     * @return static
     */
    public function err($error, array $tags = [], array $trace = [])
    {
        if ($error instanceof ResultContext) {
            $this->merge($error);

        } else {
            if (null === $this->errors) {
                $this->errors = new ErrorBag();
            }

            $this->errors->error($error, $tags, $trace);
        }

        return $this;
    }


    /**
     * @param array{ 0?: mixed } $fallback
     *
     * @return mixed
     */
    public function get(array $fallback = [])
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


    public function errors() : array
    {
        $list = [];

        if (null !== $this->errors) {
            $errors = $this->errors->getErrors();

            foreach ( $errors as $i => $errorObject ) {
                $list[] = $errorObject->error;
            }
        }

        return $list;
    }

    public function errorsByTags(array $andTags, array ...$orAndTags) : array
    {
        $list = [];

        if (null !== $this->errors) {
            $errors = $this->errors->getErrorsByTags($andTags, ...$orAndTags);

            foreach ( $errors as $i => $errorObject ) {
                $list[] = $errorObject->error;
            }
        }

        return $list;
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
}
