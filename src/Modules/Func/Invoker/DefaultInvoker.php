<?php

namespace Gzhegow\Lib\Modules\Func\Invoker;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Func\GenericCallable;


class DefaultInvoker implements InvokerInterface
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
    public function callUserFunc($fn, ...$args)
    {
        if ( ! ($fn instanceof GenericCallable) ) {
            $result = call_user_func_array($fn, $args);

        } else {
            if ( $fn->isClosure() ) {
                $cb = $fn->getClosureObject();

            } elseif ( $fn->isMethod() ) {
                if ( $fn->hasMethodClass($className) ) {
                    $cbObj = $this->newInvokeObject($fn->getMethodClass(), $args);

                } else {
                    $cbObj = $fn->getMethodObject();
                }

                $cb = [ $cbObj, $fn->getMethodName() ];

            } elseif ( $fn->isInvokable() ) {
                if ( $fn->hasInvokableClass($className) ) {
                    $cb = $this->newInvokeObject($className, $args);

                } else {
                    $cb = $fn->getInvokableObject();
                }

            } elseif ( $fn->isFunction() ) {
                if ( $fn->hasFunctionStringInternal($fnString) ) {
                    $theFunc = Lib::func();

                    $cb = static function (...$args) use (
                        $theFunc,
                        $fnString
                    ) {
                        return $theFunc->call_user_func_array($fnString, $args);
                    };

                } else {
                    $cb = $fn->getFunctionStringNonInternal();
                }

            } else {
                throw new RuntimeException(
                    [ 'Unable to extract callable from: ' . GenericCallable::class, $fn ]
                );
            }

            $result = call_user_func_array($cb, $args);
        }

        return $result;
    }

    /**
     * @param callable|GenericCallable $fn
     */
    public function callUserFuncArray($fn, array $args = [])
    {
        if ( ! ($fn instanceof GenericCallable) ) {
            $result = call_user_func_array($fn, $args);

        } else {
            if ( $fn->isClosure() ) {
                $cb = $fn->getClosureObject();

            } elseif ( $fn->isMethod() ) {
                if ( $fn->hasMethodClass($className) ) {
                    $cbObj = $this->newInvokeObject($fn->getMethodClass(), $args);

                } else {
                    $cbObj = $fn->getMethodObject();
                }

                $cb = [ $cbObj, $fn->getMethodName() ];

            } elseif ( $fn->isInvokable() ) {
                if ( $fn->hasInvokableClass($className) ) {
                    $cb = $this->newInvokeObject($className, $args);

                } else {
                    $cb = $fn->getInvokableObject();
                }

            } elseif ( $fn->isFunction() ) {
                if ( $fn->hasFunctionStringInternal($fnString) ) {
                    $theFunc = Lib::func();

                    $cb = static function (...$args) use (
                        $theFunc,
                        $fnString
                    ) {
                        return $theFunc->call_user_func_array($fnString, $args);
                    };

                } else {
                    $cb = $fn->getFunctionStringNonInternal();
                }

            } else {
                throw new RuntimeException(
                    [ 'Unable to extract callable from: ' . GenericCallable::class, $fn ]
                );
            }

            $result = call_user_func_array($cb, $args);
        }

        return $result;
    }
}
