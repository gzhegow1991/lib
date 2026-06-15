<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Func\Pipe\FuncPipe;
use Gzhegow\Lib\Modules\Func\Callback\FuncCallback;
use Gzhegow\Lib\Modules\Func\Invoker\DefaultFuncInvoker;
use Gzhegow\Lib\Modules\Func\Invoker\FuncInvokerInterface;


class FuncModule
{
    /**
     * @var FuncInvokerInterface
     */
    protected $invoker;


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    public function newPipe() : FuncPipe
    {
        return FuncPipe::new();
    }


    /**
     * @param callable                 $fn
     * @param object|null              $newThis
     * @param object|class-string|null $newScope
     *
     * @return FuncCallback
     */
    public function newCallback($fn, $newThis = null, $newScope = null) : FuncCallback
    {
        $callback = FuncCallback::new($fn);

        if ( null !== $newThis ) $callback->newThis($newThis);
        if ( null !== $newScope ) $callback->newScope($newScope);

        return $callback;
    }


    public function newInvoker() : FuncInvokerInterface
    {
        $instance = new DefaultFuncInvoker();

        return $instance;
    }

    public function cloneInvoker() : FuncInvokerInterface
    {
        return clone $this->invoker();
    }

    public function invoker(?FuncInvokerInterface $invoker = null) : FuncInvokerInterface
    {
        return $this->invoker = null
            ?? $invoker
            ?? $this->invoker
            ?? $this->newInvoker();
    }
}
