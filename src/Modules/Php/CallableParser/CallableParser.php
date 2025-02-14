<?php

namespace Gzhegow\Lib\Modules\Php\CallableParser;


class CallableParser implements CallableParserInterface
{
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

        if (null !== $methodArray) {
            $result = [ $methodArray[ 1 ], $methodArray[ 2 ] ];

            return true;
        }

        return false;
    }

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

        [ , $class, $method ] = $methodArray;

        if ($method === '__invoke') {
            $result = "{$class}->{$method}";

            return true;
        }

        try {
            $rm = new \ReflectionMethod($class, $method);

            $isStatic = $rm->isStatic();
        }
        catch ( \Throwable $e ) {
            return false;
        }

        $result = $isStatic
            ? "{$class}::{$method}"
            : "{$class}->{$method}";

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

        if (null === $methodArray) {
            $result = $value;

            return true;
        }

        [ $object, $class, $method ] = $methodArray;

        if ($object) {
            $result = $value;

            return true;
        }

        try {
            $rm = new \ReflectionMethod($class, $method);

            if ($rm->isStatic()) {
                $result = $value;

                return true;
            }
        }
        catch ( \Throwable $e ) {
            return false;
        }

        return false;
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

        $status = $this->typeCallableObjectClosure($closure, $value, $newScope);
        if ($status) {
            $result = $closure;

            return true;
        }

        $status = $this->typeCallableObjectInvokable($invokable, $value, $newScope);
        if ($status) {
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

        [ $object, $class, $method ] = $methodArray;

        if ($object) {
            $callableMethodPublic = [ $object, $method ];

            if (! $this->isCallable($callableMethodPublic, $newScope)) {
                return false;
            }

            $result = $callableMethodPublic;

            return true;
        }

        try {
            $rm = new \ReflectionMethod($class, $method);

            if ($rm->isStatic()) {
                $callableMethodStatic = [ $class, $method ];

                if (! $this->isCallable($callableMethodStatic, $newScope)) {
                    return false;
                }

                $result = $callableMethodStatic;

                return true;
            }
        }
        catch ( \Throwable $e ) {
            return false;
        }

        return false;
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

        [ $object, $class, $method ] = $methodArray;

        if ($object) {
            return false;
        }

        try {
            $rm = new \ReflectionMethod($class, $method);

            if ($rm->isStatic()) {
                $callableMethodStatic = [ $class, $method ];

                if (! $this->isCallable($callableMethodStatic, $newScope)) {
                    return false;
                }

                $result = $callableMethodStatic;

                return true;
            }
        }
        catch ( \Throwable $e ) {
            return false;
        }

        return false;
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

        [ $object, $class, $method ] = $methodArray;

        if ($object) {
            $callableMethodPublic = [ $object, $method ];

            if (! $this->isCallable($callableMethodPublic, $newScope)) {
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

        [ $object, $class, $method ] = $methodArray;

        if ($object) {
            return false;
        }

        try {
            $rm = new \ReflectionMethod($class, $method);

            if ($rm->isStatic()) {
                $callableMethodStatic = "{$class}::{$method}";

                if (! $this->isCallable($callableMethodStatic, $newScope)) {
                    return false;
                }

                $result = $callableMethodStatic;

                return true;
            }
        }
        catch ( \Throwable $e ) {
            return false;
        }

        return false;
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

        $object = $value;
        $class = get_class($value);
        $method = '__invoke';

        if (! method_exists($class, $method)) {
            return null;
        }

        return [ $object, $class, $method ];
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

        [ $classOrObject, $method ] = $list + [ '', '' ];

        $hasObject = is_object($classOrObject);
        $hasClass = is_string($classOrObject) && class_exists($classOrObject);

        if (! ($hasObject || $hasClass)) {
            return null;
        }

        $object = null;
        $class = null;
        if ($hasObject) {
            $object = $classOrObject;
            $class = get_class($object);

        } elseif ($hasClass) {
            $class = $classOrObject;
        }

        if (! is_string($method)) {
            return null;
        }

        if ('' === $method) {
            return null;
        }

        if (! method_exists($class, $method)) {
            return null;
        }

        return [ $object, $class, $method ];
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

        [ $class, $method ] = $list + [ '', '' ];

        if ('' === $class) {
            return null;
        }

        if ('' === $method) {
            return null;
        }

        if (! method_exists($class, $method)) {
            return null;
        }

        return [ null, $class, $method ];
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
