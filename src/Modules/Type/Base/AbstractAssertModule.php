<?php

namespace Gzhegow\Lib\Modules\Type\Base;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\TypeModule;
use Gzhegow\Lib\Exception\LogicException;


abstract class AbstractAssertModule
{
    /**
     * @var TypeModule
     */
    protected $theType;

    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var bool
     */
    protected $status;
    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var array<array{0: callable, 1: array}>
     */
    protected $fnList = [];


    public function __construct($value, ?TypeModule $theType = null)
    {
        $this->theType = $theType ?? Lib::type();

        $this->value = $value;
    }


    public function getStatus() : bool
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }


    /**
     * @return static
     */
    public function triggerError($message, int $error_level = null)
    {
        $error_level = $error_level ?? E_USER_NOTICE;

        if (! $this->status) {
            trigger_error($message, $error_level);
        }

        return $this;
    }


    /**
     * @return mixed|null
     */
    public function orNull()
    {
        if (! $this->status) {
            return null;
        }

        return $this->result;
    }

    /**
     * @return mixed|void
     */
    public function orThrow($throwableOrArg, ...$throwableArgs)
    {
        if (! $this->status) {
            return Lib::php()->throw(
                debug_backtrace(),
                $throwableOrArg, ...$throwableArgs
            );
        }

        return $this->result;
    }

    /**
     * @return mixed|void
     */
    public function orFallback(array $fallback = [])
    {
        if (! $this->status) {
            if (array_key_exists(0, $fallback)) {
                return $fallback[ 0 ];
            }

            throw new LogicException(
                [ 'The assert failed, and no fallback provided', $this->fnList ]
            );
        }

        return $this->result;
    }

    /**
     * @return mixed|void
     */
    public function orCallback(\Closure $fn)
    {
        if (! $this->status) {
            $result = call_user_func_array(
                $fn,
                [ $this->value, $this->fnList ]
            );

            return $result;
        }

        return $this->result;
    }
}
