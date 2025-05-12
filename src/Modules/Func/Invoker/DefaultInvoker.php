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
    public function new(string $className, array $options = []) : object
    {
        return new $className();
    }


    /**
     * @param callable|GenericCallable $fn
     */
    public function callUserFunc($fn, ...$args)
    {
        if (! ($fn instanceof GenericCallable)) {
            $result = call_user_func_array($fn, $args);

        } else {
            if ($fn->isClosure()) {
                $cb = $fn->getClosureObject();

            } elseif ($fn->isMethod()) {
                if ($fn->hasMethodClass($className)) {
                    $cbObj = $this->new($fn->getMethodClass(), [ 'args' => $args ]);

                } else {
                    $cbObj = $fn->getMethodObject();
                }

                $cb = [ $cbObj, $fn->getMethodName() ];

            } elseif ($fn->isInvokable()) {
                if ($fn->hasInvokableClass($className)) {
                    $cb = $this->new($className, [ 'args' => $args ]);

                } else {
                    $cb = $fn->getInvokableObject();
                }

            } elseif ($fn->isFunction()) {
                if ($fn->hasFunctionStringInternal($fnString)) {
                    $cb = static function (...$args) use ($fnString) {
                        return Lib::func()->call_user_func_array($fnString, $args);
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
        if (! ($fn instanceof GenericCallable)) {
            $result = call_user_func_array($fn, $args);

        } else {
            if ($fn->isClosure()) {
                $cb = $fn->getClosureObject();

            } elseif ($fn->isMethod()) {
                if ($fn->hasMethodClass($className)) {
                    $cbObj = $this->new($fn->getMethodClass(), [ 'args' => $args ]);

                } else {
                    $cbObj = $fn->getMethodObject();
                }

                $cb = [ $cbObj, $fn->getMethodName() ];

            } elseif ($fn->isInvokable()) {
                if ($fn->hasInvokableClass($className)) {
                    $cb = $this->new($className, [ 'args' => $args ]);

                } else {
                    $cb = $fn->getInvokableObject();
                }

            } elseif ($fn->isFunction()) {
                if ($fn->hasFunctionStringInternal($fnString)) {
                    $cb = static function (...$args) use ($fnString) {
                        return Lib::func()->call_user_func_array($fnString, $args);
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
