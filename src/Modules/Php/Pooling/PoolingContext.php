<?php

namespace Gzhegow\Lib\Modules\Php\Pooling;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class PoolingContext
{
    /**
     * @var array{ 0?: mixed }
     */
    protected $error = [];
    /**
     * @var array{ 0?: mixed }
     */
    protected $result = [];

    /**
     * @var float
     */
    protected $nowMicrotime;

    /**
     * @var int
     */
    protected $timeoutMs;
    /**
     * @var float
     */
    protected $timeoutMicrotime;


    /**
     * @param mixed $refError
     */
    public function hasError(&$refError = null) : bool
    {
        $refError = null;

        if ( [] !== $this->error ) {
            $refError = $this->error[0];

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        if ( [] === $this->error ) {
            throw new RuntimeException(
                [ 'The `error` should be a non-empty' ]
            );
        }

        return $this->error[0];
    }

    /**
     * @return static
     */
    public function setError($error)
    {
        $this->error = [ $error ];

        return $this;
    }


    /**
     * @param mixed $refResult
     */
    public function hasResult(&$refResult = null) : bool
    {
        $refResult = null;

        if ( [] !== $this->result ) {
            $refResult = $this->result[0];

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        if ( [] === $this->result ) {
            throw new RuntimeException(
                [ 'The `result[0]` should exists', $this ]
            );
        }

        return $this->result[0];
    }

    /**
     * @return static
     */
    public function setResult($value)
    {
        $this->result = [ $value ];

        return $this;
    }


    public function getNowMicrotime() : float
    {
        if ( null === $this->nowMicrotime ) {
            $this->updateNowMicrotime();
        }

        return $this->nowMicrotime;
    }

    public function updateNowMicrotime() : float
    {
        $this->nowMicrotime = microtime(true);

        return $this->nowMicrotime;
    }


    public function hasTimeoutMs() : ?int
    {
        return $this->timeoutMs;
    }

    public function getTimeoutMs() : int
    {
        return $this->timeoutMs;
    }

    /**
     * @return static
     */
    public function resetTimeoutMs(?int $timeoutMs)
    {
        if ( null !== $timeoutMs ) {
            if ( $timeoutMs < 0 ) {
                throw new LogicException(
                    [ 'The `timeoutMs` should be a non-negative integer', $timeoutMs ]
                );
            }
        }

        $this->timeoutMs = $timeoutMs;

        if ( null === $timeoutMs ) {
            $this->timeoutMicrotime = null;

        } else {
            $this->timeoutMicrotime = $this->getNowMicrotime() + ($timeoutMs / 1000);
        }

        return $this;
    }


    public function hasTimeoutMicrotime() : ?float
    {
        return $this->timeoutMicrotime;
    }

    public function getTimeoutMicrotime() : float
    {
        return $this->timeoutMicrotime;
    }

    public function updateTimeoutMicrotime() : ?float
    {
        if ( null === $this->timeoutMs ) {
            $this->timeoutMicrotime = null;

        } else {
            $this->timeoutMicrotime = $this->getNowMicrotime() + ($this->timeoutMs / 1000);
        }

        return $this->timeoutMicrotime;
    }
}
