<?php

namespace Gzhegow\Lib\Modules\Type\Base;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\TypeModule;
use Gzhegow\Lib\Exception\LogicException;


abstract class AssertModuleBase
{
    /**
     * @var TypeModule
     */
    protected $theType;

    /**
     * @var mixed
     */
    protected $value = [];

    /**
     * @var callable
     */
    protected $fnName;
    /**
     * @var array
     */
    protected $fnArguments;

    /**
     * @var bool
     */
    protected $status;
    /**
     * @var mixed
     */
    protected $result;


    public function __construct(TypeModule $theType = null)
    {
        $this->theType = $theType ?? Lib::type();
    }


    public function getStatus() : bool
    {
        return $this->status;
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
            Lib::php()->throw(
                debug_backtrace(),
                $throwableOrArg, ...$throwableArgs
            );

            return null;
        }

        return $this->result;
    }

    /**
     * @return mixed|void
     */
    public function orTriggerError($message, int $error_level = null)
    {
        $error_level = $error_level ?? E_USER_NOTICE;

        if (! $this->status) {
            trigger_error($message, $error_level);

            return null;
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
                'The assert failed, but no fallback provided: ' . $this->fnName
            );
        }

        return $this->result;
    }
}
