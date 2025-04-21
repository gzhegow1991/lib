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


    public function __construct(?TypeModule $theType = null)
    {
        $this->theType = $theType ?? Lib::type();
    }


    /**
     * @return static
     */
    public function of($value)
    {
        $instance = clone $this;
        $instance->value = $value;

        return $instance;
    }


    /**
     * @return mixed
     */
    public function get(&$result = null)
    {
        $result = $this->result;

        return $result;
    }

    public function getStatus() : bool
    {
        return $this->status;
    }


    /**
     * @return static
     */
    public function withTriggerError($message, int $error_level = null)
    {
        $error_level = $error_level ?? E_USER_NOTICE;

        if (! $this->status) {
            trigger_error($message, $error_level);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function withThrow($throwableOrArg, ...$throwableArgs)
    {
        if (! $this->status) {
            return Lib::php()->throw(
                debug_backtrace(),
                $throwableOrArg, ...$throwableArgs
            );
        }

        return $this;
    }

    /**
     * @return static
     */
    public function withFallback(array $fallback = [])
    {
        if (! $this->status) {
            if ([] === $fallback) {
                throw new LogicException(
                    [ 'The assert failed, and no fallback provided', $this->fnList ]
                );
            }

            $this->result = $fallback[ 0 ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function withCallback(\Closure $fn)
    {
        if (! $this->status) {
            $result = call_user_func_array(
                $fn,
                [ $this->value, $this->fnList ]
            );

            $this->result = $result;
        }

        return $this;
    }


    /**
     * @return mixed
     */
    public function orNull()
    {
        if (! $this->status) {
            $this->result = null;
        }

        return $this->result;
    }

    /**
     * @return mixed
     */
    public function orTriggerError($message, int $error_level = null)
    {
        $this->withTriggerError($message, $error_level);

        return $this->result;
    }

    /**
     * @return mixed
     */
    public function orThrow($throwableOrArg, ...$throwableArgs)
    {
        $this->withThrow($throwableOrArg, ...$throwableArgs);

        return $this->result;
    }

    /**
     * @return mixed
     */
    public function orFallback(array $fallback = [])
    {
        $this->withFallback($fallback);

        return $this->result;
    }

    /**
     * @return mixed
     */
    public function orCallback(\Closure $fn)
    {
        $this->withCallback($fn);

        return $this->result;
    }
}
