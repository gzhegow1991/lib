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
        if ([] === $this->error) {
            return false;
        }

        $refError = $this->error[ 0 ];

        return true;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        if ([] === $this->error) {
            throw new RuntimeException(
                [ 'The `error` should be non-empty' ]
            );
        }

        return $this->error[ 0 ];
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
        if ([] === $this->result) {
            return false;
        }

        $refResult = $this->result[ 0 ];

        return true;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        if ([] === $this->result) {
            throw new RuntimeException(
                [ 'The `result` should be non-empty' ]
            );
        }

        return $this->result[ 0 ];
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
        if (null === $this->nowMicrotime) {
            $this->updateNowMicrotime();
        }

        return $this->nowMicrotime;
    }

    /**
     * @return static
     */
    public function updateNowMicrotime()
    {
        $this->nowMicrotime = microtime(true);

        return $this;
    }


    public function hasTimeoutMs() : ?int
    {
        return $this->timeoutMs;
    }

    public function getTimeoutMs() : int
    {
        return $this->timeoutMs;
    }

    public function hasTimeoutMicrotime() : ?float
    {
        return $this->timeoutMicrotime;
    }

    public function getTimeoutMicrotime() : float
    {
        return $this->timeoutMicrotime;
    }

    /**
     * @return static
     */
    public function setTimeoutMs(?int $timeoutMs)
    {
        if (null !== $timeoutMs) {
            if ($timeoutMs < 0) {
                throw new LogicException(
                    [ 'The `timeoutMs` should be non-negative integer', $timeoutMs ]
                );
            }
        }

        $this->timeoutMs = $timeoutMs;

        if (null === $timeoutMs) {
            $this->timeoutMicrotime = null;

        } else {
            $this->timeoutMicrotime = $this->getNowMicrotime() + ($timeoutMs / 1000);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function updateTimeoutMicrotime()
    {
        if (null !== $this->timeoutMs) {
            $this->timeoutMicrotime = $this->getNowMicrotime() + ($this->timeoutMs / 1000);
        }

        return $this;
    }
}
