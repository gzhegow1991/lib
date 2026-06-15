<?php

namespace Gzhegow\Lib\Modules\Func\Invoker;

use Gzhegow\Lib\Modules\Func\GenericCallable;


interface FuncInvokerInterface
{
    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    public function newInvokeObject(string $className, array $args = [], array $options = []) : object;


    /**
     * @param callable|GenericCallable $fn
     */
    public function callUserFunc($fn, ...$fnArgs);

    /**
     * @param callable|GenericCallable $fn
     */
    public function callUserFuncArray($fn, array $fnArgs = []);
}
