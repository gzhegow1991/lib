<?php

namespace Gzhegow\Lib\Modules\Php\CallableParser;


class CallableParser implements CallableParserInterface
{
    /**
     * @param string|null $result
     */
    public function typeMethodString(&$result, $value) : bool
    {
        $result = null;

        $methodArray = null
            ?? $this->parse_method_array_from_object($value)
            ?? $this->parse_method_array_from_array($value)
            ?? $this->parse_method_array_from_string($value);

        if (null === $methodArray) {
            return false;
        }

        [ , $theClass, $theMethod, $theMagic ] = $methodArray;

        if (null !== $theMagic) {
            return false;
        }

        if ($theMethod === '__invoke') {
            $result = "{$theClass}->{$theMethod}";

            return true;
        }

        try {
            $rm = new \ReflectionMethod($theClass, $theMethod);

            $isStatic = $rm->isStatic();
        }
        catch ( \Throwable $e ) {
            return false;
        }

        $result = $isStatic
            ? "{$theClass}::{$theMethod}"
            : "{$theClass}->{$theMethod}";

        return true;
    }

    /**
     * @param array{ 0: class-string, 1: string }|null $result
     */
    public function typeMethodArray(&$result, $value) : bool
    {
        $result = null;

        $methodArray = null
            ?? $this->parse_method_array_from_object($value)
            ?? $this->parse_method_array_from_array($value)
            ?? $this->parse_method_array_from_string($value);

        if (null === $methodArray) {
            return false;
        }

        [ , $theClass, $theMethod, $theMagic ] = $methodArray;

        if (null !== $theMagic) {
            return false;
        }

        $result = [ $theClass, $theMethod ];

        return true;
    }


    /**
     * @param callable|null $result
     * @param string|object $newScope
     */
    public function typeCallable(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (! $this->isCallable($value, $newScope)) {
            return false;
        }

        if (false
            || is_object($value)
            || (PHP_VERSION_ID >= 80000)
        ) {
            $result = $value;

            return true;
        }

        $methodArray = null
            ?? $this->parse_method_array_from_array($value)
            ?? $this->parse_method_array_from_string($value);

        if ($methodArray) {
            [ $theObject, $theClass, $theMethod, $theMagic ] = $methodArray;

            if ($theObject) {
                $result = $value;

                return true;

            } else {
                if ($theMagic === '__callStatic') {
                    $result = $value;

                    return true;
                }

                try {
                    $rm = new \ReflectionMethod($theClass, $theMethod);

                    if (! $rm->isStatic()) {
                        return false;
                    }
                }
                catch ( \Throwable $e ) {
                    return false;
                }
            }
        }

        $result = $value;

        return true;
    }


    /**
     * @param callable|\Closure|object|null $result
     */
    public function typeCallableObject(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (! is_object($value)) {
            return false;
        }

        if ($value instanceof \Closure) {
            if (! $this->isCallable($value, $newScope)) {
                return false;
            }

            $result = $value;

            return true;
        }

        $invokable = $this->parse_invokable($value);
        if (null !== $invokable) {
            if (! $this->isCallable($invokable, $newScope)) {
                return false;
            }

            $result = $invokable;

            return true;
        }

        return false;
    }

    /**
     * @param callable|object|null $result
     */
    public function typeCallableObjectClosure(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (! is_object($value)) {
            return false;
        }

        if ($value instanceof \Closure) {
            if (! $this->isCallable($value, $newScope)) {
                return false;
            }

            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param callable|object|null $result
     */
    public function typeCallableObjectInvokable(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (! is_object($value)) {
            return false;
        }

        $invokable = $this->parse_invokable($value);
        if (null !== $invokable) {
            if (! $this->isCallable($invokable, $newScope)) {
                return false;
            }

            $result = $invokable;

            return true;
        }

        return false;
    }


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function typeCallableArray(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        $status = $this->typeCallableArrayMethod($method, $value, $newScope);
        if ($status) {
            $result = $method;

            return true;
        }

        return false;
    }

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function typeCallableArrayMethod(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        $methodArray = null
            ?? $this->parse_method_array_from_object($value)
            ?? $this->parse_method_array_from_array($value)
            ?? $this->parse_method_array_from_string($value);

        if (null === $methodArray) {
            return false;
        }

        [ $theObject, $theClass, $theMethod, $theMagic ] = $methodArray;

        if ($theObject) {
            $callableMethodPublic = [ $theObject, $theMethod ];
            $callableMethodPublicMagic = null;
            if (null !== $theMagic) {
                // __invoke / __call
                $callableMethodPublicMagic = [ $theObject, $theMagic ];
            }

            if (! $this->isCallable($callableMethodPublicMagic ?? $callableMethodPublic, $newScope)) {
                return false;
            }

            $result = $callableMethodPublic;

            return true;

        } else {
            $callableMethodStatic = [ $theClass, $theMethod ];
            $callableMethodStaticMagic = null;
            if (null !== $theMagic) {
                // __callStatic
                $callableMethodStaticMagic = [ $theClass, $theMagic ];
            }

            if (false
                || ($theMethod === '__callStatic')
                || ($theMagic === '__callStatic')
            ) {
                if (! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope)) {
                    return false;
                }

                $result = $callableMethodStatic;

                return true;

            } else {
                try {
                    $rm = new \ReflectionMethod($theClass, $theMethod);
                    if (! $rm->isStatic()) {
                        return false;
                    }

                    if (! $this->isCallable($callableMethodStatic, $newScope)) {
                        return false;
                    }

                    $result = $callableMethodStatic;

                    return true;
                }
                catch ( \Throwable $e ) {
                    return false;
                }
            }
        }
    }

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $result
     * @param string|object                                     $newScope
     */
    public function typeCallableArrayMethodStatic(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        $methodArray = null
            ?? $this->parse_method_array_from_array($value)
            ?? $this->parse_method_array_from_string($value);

        if (null === $methodArray) {
            return false;
        }

        [ $theObject, $theClass, $theMethod, $theMagic ] = $methodArray;

        if ($theObject) {
            return false;
        }

        $callableMethodStatic = [ $theClass, $theMethod ];
        $callableMethodStaticMagic = null;
        if (null !== $theMagic) {
            // __callStatic
            $callableMethodStaticMagic = [ $theClass, $theMagic ];
        }

        if (false
            || ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            if (! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope)) {
                return false;
            }

            $result = $callableMethodStatic;

            return true;

        } else {
            try {
                $rm = new \ReflectionMethod($theClass, $theMethod);
                if (! $rm->isStatic()) {
                    return false;
                }

                if (! $this->isCallable($callableMethodStatic, $newScope)) {
                    return false;
                }

                $result = $callableMethodStatic;

                return true;
            }
            catch ( \Throwable $e ) {
                return false;
            }
        }
    }

    /**
     * @param callable|array{ 0: object, 1: string }|null $result
     * @param string|object                               $newScope
     */
    public function typeCallableArrayMethodNonStatic(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        $methodArray = null
            ?? $this->parse_method_array_from_object($value)
            ?? $this->parse_method_array_from_array($value);

        if (null === $methodArray) {
            return false;
        }

        [ $theObject, $theClass, $theMethod, $theMagic ] = $methodArray;

        if ($theObject) {
            $callableMethodPublic = [ $theObject, $theMethod ];
            $callableMethodPublicMagic = null;
            if (null !== $theMagic) {
                // __invoke / __call
                $callableMethodPublicMagic = [ $theObject, $theMagic ];
            }

            if (! $this->isCallable($callableMethodPublicMagic ?? $callableMethodPublic, $newScope)) {
                return false;
            }

            $result = $callableMethodPublic;

            return true;
        }

        return false;
    }


    /**
     * @param callable-string|null $result
     */
    public function typeCallableString(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (is_object($value)) {
            return false;
        }

        $status = $this->typeCallableStringFunction($function, $value, $newScope);
        if ($status) {
            $result = $function;

            return true;
        }

        $status = $this->typeCallableStringMethodStatic($methodStatic, $value, $newScope);
        if ($status) {
            $result = $methodStatic;

            return true;
        }

        return false;
    }

    /**
     * @param callable-string|null $result
     */
    public function typeCallableStringFunction(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (is_object($value)) {
            return false;
        }

        $function = $this->parse_function($value);
        if (null !== $function) {
            if (! $this->isCallable($function, $newScope)) {
                return false;
            }

            $result = $function;

            return true;
        }

        return false;
    }

    /**
     * @param callable-string|null $result
     */
    public function typeCallableStringMethodStatic(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (is_object($value)) {
            return false;
        }

        $methodArray = null
            ?? $this->parse_method_array_from_array($value)
            ?? $this->parse_method_array_from_string($value);

        if (null === $methodArray) {
            return false;
        }

        [ $theObject, $theClass, $theMethod, $theMagic ] = $methodArray;

        if ($theObject) {
            return false;
        }

        $callableMethodStatic = "{$theClass}::{$theMethod}";
        $callableMethodStaticMagic = null;
        if (null !== $theMagic) {
            // __callStatic
            $callableMethodStaticMagic = "{$theClass}::{$theMagic}";
        }

        if (false
            || ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            if (! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope)) {
                return false;
            }

            $result = $callableMethodStatic;

            return true;

        } else {
            try {
                $rm = new \ReflectionMethod($theClass, $theMethod);
                if (! $rm->isStatic()) {
                    return false;
                }

                if (! $this->isCallable($callableMethodStatic, $newScope)) {
                    return false;
                }

                $result = $callableMethodStatic;

                return true;
            }
            catch ( \Throwable $e ) {
                return false;
            }
        }
    }


    /**
     * @param string|object $newScope
     */
    public function isCallable($value, $newScope = 'static') : bool
    {
        $result = null;

        $fnIsCallable = null;
        if ('static' !== $newScope) {
            $_newScope = null
                ?? $newScope
                ?? new class {
                };

            $fnIsCallable = (static function ($callable) {
                return is_callable($callable);
            })->bindTo(null, $_newScope);
        }

        $status = $fnIsCallable
            ? $fnIsCallable($value)
            : is_callable($value);

        if ($status) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @return callable-string
     */
    private function parse_function($value) : ?string
    {
        if (! is_string($value)) {
            return null;
        }

        if (! function_exists($value)) {
            return null;
        }

        return $value;
    }


    /**
     * @return array{ 0: object, 1: class-string, 2: string }
     */
    private function parse_method_array_from_object($value) : ?array
    {
        if (! is_object($value)) {
            return null;
        }

        $theObject = $value;
        $theClass = get_class($value);
        $theMethod = '__invoke';

        if (method_exists($theClass, $theMethod)) {
            return [ $theObject, $theClass, $theMethod, null ];
        }

        return null;
    }

    /**
     * @return array{ 0: object|null, 1: class-string, 2: string }
     */
    private function parse_method_array_from_array($value) : ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $list = array_values($value);

        [ $classOrObject, $theMethod ] = $list + [ '', '' ];

        if ((! is_string($theMethod)) || ('' === $theMethod)) {
            return null;
        }

        $isObject = null;
        $isClass = null;
        if (! (false
            || ($isObject = is_object($classOrObject))
            || ($isClass = is_string($classOrObject) && class_exists($classOrObject))
        )) {
            return null;
        }

        $theObject = null;
        $theClass = null;
        if ($isObject) {
            $theObject = $classOrObject;
            $theClass = get_class($theObject);

        } elseif ($isClass) {
            $theClass = $classOrObject;
        }

        if (method_exists($theClass, $theMethod)) {
            return [ $theObject, $theClass, $theMethod, null ];

        } else {
            if ($theObject && method_exists($theObject, '__call')) {
                return [ $theObject, $theClass, $theMethod, '__call' ];
            }

            if ($theClass && method_exists($theClass, '__callStatic')) {
                return [ null, $theClass, $theMethod, '__callStatic' ];
            }
        }

        return null;
    }

    /**
     * @return array{ 0: null, 1: class-string, 2: string }
     */
    private function parse_method_array_from_string($value) : ?array
    {
        if (! is_string($value)) {
            return null;
        }

        $list = explode('::', $value);

        [ $theClass, $theMethod ] = $list + [ '', '' ];

        if ('' === $theClass) {
            return null;
        }

        if ('' === $theMethod) {
            return null;
        }

        if (method_exists($theClass, $theMethod)) {
            return [ null, $theClass, $theMethod, null ];

        } else {
            if ($theClass && method_exists($theClass, '__callStatic')) {
                return [ null, $theClass, $theMethod, '__callStatic' ];
            }
        }

        return null;
    }


    /**
     * @return object|callable
     */
    private function parse_invokable($value) : ?object
    {
        if (! is_object($value)) {
            return null;
        }

        if (! method_exists($value, '__invoke')) {
            return null;
        }

        return $value;
    }
}
