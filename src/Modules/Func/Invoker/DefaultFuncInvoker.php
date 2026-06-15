<?php

namespace Gzhegow\Lib\Modules\Func\Invoker;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Func\GenericCallable;


class DefaultFuncInvoker implements FuncInvokerInterface
{
    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    public function newInvokeObject(string $className, array $args = [], array $options = []) : object
    {
        $theArr = Lib::arr();

        $list = [];

        if ( [] !== $args ) {
            [ $list ] = $theArr->kwargs($args);
        }

        return new $className(...$list);
    }


    /**
     * @param callable|GenericCallable $fn
     */
    public function callUserFunc($fn, ...$fnArgs)
    {
        if ( ! ($fn instanceof GenericCallable) ) {
            $result = call_user_func_array($fn, $fnArgs);

        } else {
            if ( $fn->isClosure() ) {
                $cb = $fn->getClosureObject();

            } elseif ( $fn->isMethod() ) {
                if ( $fn->hasMethodClass($className) ) {
                    $cbObj = $this->newInvokeObject($fn->getMethodClass(), $fnArgs);

                } else {
                    $cbObj = $fn->getMethodObject();
                }

                $cb = [ $cbObj, $fn->getMethodName() ];

            } elseif ( $fn->isInvokable() ) {
                if ( $fn->hasInvokableClass($className) ) {
                    $cb = $this->newInvokeObject($className, $fnArgs);

                } else {
                    $cb = $fn->getInvokableObject();
                }

            } elseif ( $fn->isFunction() ) {
                if ( $fn->hasFunctionStringInternal($fnStringInternal) ) {
                    $cb = Lib::fn($fnStringInternal)->setInternal()->make();

                } else {
                    $cb = $fn->getFunctionStringNonInternal();
                }

            } else {
                throw new RuntimeException(
                    [ 'Unable to extract callable from: ' . GenericCallable::class, $fn ]
                );
            }

            $result = call_user_func_array($cb, $fnArgs);
        }

        return $result;
    }

    /**
     * @param callable|GenericCallable $fn
     */
    public function callUserFuncArray($fn, array $fnArgs = [])
    {
        if ( ! ($fn instanceof GenericCallable) ) {
            $result = call_user_func_array($fn, $fnArgs);

        } else {
            if ( $fn->isClosure() ) {
                $cb = $fn->getClosureObject();

            } elseif ( $fn->isMethod() ) {
                if ( $fn->hasMethodClass($className) ) {
                    $cbObj = $this->newInvokeObject($fn->getMethodClass(), $fnArgs);

                } else {
                    $cbObj = $fn->getMethodObject();
                }

                $cb = [ $cbObj, $fn->getMethodName() ];

            } elseif ( $fn->isInvokable() ) {
                if ( $fn->hasInvokableClass($className) ) {
                    $cb = $this->newInvokeObject($className, $fnArgs);

                } else {
                    $cb = $fn->getInvokableObject();
                }

            } elseif ( $fn->isFunction() ) {
                if ( $fn->hasFunctionStringInternal($fnStringInternal) ) {
                    $cb = Lib::fn($fnStringInternal)->setInternal()->make();

                } else {
                    $cb = $fn->getFunctionStringNonInternal();
                }

            } else {
                throw new RuntimeException(
                    [ 'Unable to extract callable from: ' . GenericCallable::class, $fn ]
                );
            }

            $result = call_user_func_array($cb, $fnArgs);
        }

        return $result;
    }
}
