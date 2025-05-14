<?php

namespace Gzhegow\Lib\Modules\Php\CallableParser;

use Gzhegow\Lib\Lib;


class DefaultCallableParser implements CallableParserInterface
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

        if (null === $methodArray) {
            // > passed value is not a method or a \Closure

            return false;
        }

        [ , $theClass, $theMethod, $theMagic ] = $methodArray;

        if ($theMethod && $theMagic) {
            // > method provided and not exists, but class or object has magic method __call() or __callStatic()
            return false;

        } elseif ($theMagic) {
            // > method not provided, but but class or object has magic method __invoke()
            if (false
                || ($theMagic === '__invoke')
            ) {
                $result = [ $theClass, $theMagic ];

                return true;
            }

            return false;

        } elseif ($theMethod) {
            // > method provided and exists

            $result = [ $theClass, $theMethod ];

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function typeMethodString(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        $withMethodArray = array_key_exists(0, $refs);

        if ($withMethodArray) {
            $refResultArray =& $refs[ 0 ];
        }

        $refResultArray = null;

        $methodArray = null
            ?? $this->parse_method_array_from_object($value)
            ?? $this->parse_method_array_from_array($value)
            ?? $this->parse_method_array_from_string($value);

        if (null === $methodArray) {
            // > passed value is not a method or a \Closure
            return false;
        }

        [ , $theClass, $theMethod, $theMagic ] = $methodArray;

        if ($theMethod && $theMagic) {
            // > method provided and not exists, but class or object has magic method __call() or __callStatic()
            return false;

        } elseif ($theMagic) {
            // > method not provided, but but class or object has magic method __invoke()
            if (
                ($theMagic === '__invoke')
            ) {
                $result = "{$theClass}->__invoke";

                $refResultArray = [ $theClass, '__invoke' ];
                unset($refResultArray);

                return true;
            }

            return false;

        } elseif ($theMethod) {
            // > method provided and exists

            if (
                ($theMethod === '__invoke')
                || ($theMethod === '__call')
            ) {
                $result = "{$theClass}->{$theMethod}";

                $refResultArray = [ $theClass, $theMethod ];
                unset($refResultArray);

                return true;
            }

            if (
                ($theMethod === '__callStatic')
            ) {
                $result = "{$theClass}::{$theMethod}";

                $refResultArray = [ $theClass, $theMethod ];
                unset($refResultArray);

                return true;
            }

            $refResultArray = [ $theClass, $theMethod ];
            unset($refResultArray);

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

        unset($refResultArray);

        return false;
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

        if (PHP_VERSION_ID >= 80000) {
            $result = $value;

            return true;
        }

        if (is_object($value)) {
            // > \Closure or invokable
            $result = $value;

            return true;
        }

        $function = $this->parse_function($value);
        if (null !== $function) {
            // > plain function
            $result = $value;

            return true;
        }

        $methodArray = null
            ?? $this->parse_method_array_from_array($value)
            ?? $this->parse_method_array_from_string($value);

        if (null === $methodArray) {
            return false;
        }

        [ $theObject, $theClass, $theMethod, $theMagic ] = $methodArray;

        if ($theObject) {
            // > array with object
            $result = $value;

            return true;
        }

        if (
            ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            $result = $value;

            return true;
        }

        if (
            ($theMethod === '__invoke')
            || ($theMethod === '__call')
            || ($theMagic === '__invoke')
            || ($theMagic === '__call')
        ) {
            return false;
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

        if (! ($value instanceof \Closure)) {
            return false;
        }

        if (! $this->isCallable($value, $newScope)) {
            return false;
        }

        $result = $value;

        return true;
    }

    /**
     * @param callable|object|null $result
     */
    public function typeCallableObjectInvokable(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        $invokable = $this->parse_invokable($value);

        if (null === $invokable) {
            return false;
        }

        if (! $this->isCallable($invokable, $newScope)) {
            return false;
        }

        $result = $invokable;

        return true;
    }


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function typeCallableArray(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

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

        if (! is_array($value)) {
            return false;
        }

        $methodArray = $this->parse_method_array_from_array($value);

        if (null === $methodArray) {
            return false;
        }

        [ $theObject, $theClass, $theMethod, $theMagic ] = $methodArray;

        if (null === $theMethod) {
            return false;
        }

        if ($theObject) {
            $callableMethodPublic = [ $theObject, $theMethod ];

            $callableMethodPublicMagic = null;
            if ($theMagic) {
                $callableMethodPublicMagic = [ $theObject, $theMagic ];
            }

            if (! $this->isCallable($callableMethodPublicMagic ?? $callableMethodPublic, $newScope)) {
                return false;
            }

            $result = $callableMethodPublic;

            return true;
        }

        if (
            ($theMethod === '__invoke')
            || ($theMethod === '__call')
            || ($theMagic === '__invoke')
            || ($theMagic === '__call')
        ) {
            return false;
        }

        $callableMethodStatic = [ $theClass, $theMethod ];

        $callableMethodStaticMagic = null;
        if ($theMagic) {
            $callableMethodStaticMagic = [ $theClass, $theMagic ];
        }

        if (
            ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            if (! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope)) {
                return false;
            }

            $result = $callableMethodStatic;

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

        if (! $this->isCallable($callableMethodStatic, $newScope)) {
            return false;
        }

        $result = $callableMethodStatic;

        return true;
    }

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $result
     * @param string|object                                     $newScope
     */
    public function typeCallableArrayMethodStatic(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        $methodArray = $this->parse_method_array_from_array($value);

        if (null === $methodArray) {
            return false;
        }

        [ /* $theObject */, $theClass, $theMethod, $theMagic ] = $methodArray;

        if (null === $theMethod) {
            return false;
        }

        if (
            ($theMethod === '__invoke')
            || ($theMethod === '__call')
            || ($theMagic === '__invoke')
            || ($theMagic === '__call')
        ) {
            return false;
        }

        $callableMethodStatic = [ $theClass, $theMethod ];

        $callableMethodStaticMagic = null;
        if ($theMagic) {
            $callableMethodStaticMagic = [ $theClass, $theMagic ];
        }

        if (
            ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            if (! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope)) {
                return false;
            }

            $result = $callableMethodStatic;

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

        if (! $this->isCallable($callableMethodStatic, $newScope)) {
            return false;
        }

        $result = $callableMethodStatic;

        return true;
    }

    /**
     * @param callable|array{ 0: object, 1: string }|null $result
     * @param string|object                               $newScope
     */
    public function typeCallableArrayMethodNonStatic(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        $methodArray = $this->parse_method_array_from_array($value);

        if (null === $methodArray) {
            return false;
        }

        [ $theObject, $theClass, $theMethod, $theMagic ] = $methodArray;

        if (null === $theMethod) {
            return false;
        }

        if (
            ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            return false;
        }

        if (null === $theObject) {
            return false;
        }

        $callableMethodPublic = [ $theObject, $theMethod ];

        if (
            ($theMethod === '__invoke')
            || ($theMethod === '__call')
            || ($theMagic === '__invoke')
            || ($theMagic === '__call')
        ) {
            $result = $callableMethodPublic;

            return true;
        }

        try {
            $rm = new \ReflectionMethod($theClass, $theMethod);

            if ($rm->isStatic()) {
                return false;
            }
        }
        catch ( \Throwable $e ) {
            return false;
        }

        if (! $this->isCallable($callableMethodPublic, $newScope)) {
            return false;
        }

        $result = $callableMethodPublic;

        return true;
    }


    /**
     * @param callable-string|null $result
     */
    public function typeCallableString(&$result, $value, $newScope = 'static') : bool
    {
        $result = null;

        if (! is_string($value)) {
            return false;
        }

        $status = $this->typeCallableStringFunction($function, $value);
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
    public function typeCallableStringFunction(&$result, $value) : bool
    {
        $result = null;

        if (! is_string($value)) {
            return false;
        }

        if (function_exists($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param callable-string|null $result
     */
    public function typeCallableStringFunctionInternal(&$result, $value) : bool
    {
        $result = null;

        if (! is_string($value)) {
            return false;
        }

        try {
            $rf = new \ReflectionFunction($value);
        }
        catch ( \Throwable $e ) {
            return false;
        }

        if ($rf->isInternal()) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param callable-string|null $result
     */
    public function typeCallableStringFunctionNonInternal(&$result, $value) : bool
    {
        $result = null;

        if (! is_string($value)) {
            return false;
        }

        try {
            $rf = new \ReflectionFunction($value);
        }
        catch ( \Throwable $e ) {
            return false;
        }

        if (! $rf->isInternal()) {
            $result = $value;

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

        if (! is_string($value)) {
            return false;
        }

        $methodArray = $this->parse_method_array_from_string($value);

        if (null === $methodArray) {
            return false;
        }

        [ , $theClass, $theMethod, $theMagic ] = $methodArray;

        if (null === $theMethod) {
            return false;
        }

        $callableMethodStatic = "{$theClass}::{$theMethod}";

        $callableMethodStaticMagic = null;
        if ($theMagic) {
            $callableMethodStaticMagic = "{$theClass}::{$theMagic}";
        }

        if (
            ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            if (! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope)) {
                return false;
            }

            $result = $callableMethodStatic;

            return true;
        }

        if (
            ($theMethod === '__invoke')
            || ($theMethod === '__call')
            || ($theMagic === '__invoke')
            || ($theMagic === '__call')
        ) {
            return false;
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

        if (! $this->isCallable($callableMethodStatic, $newScope)) {
            return false;
        }

        $result = $callableMethodStatic;

        return true;
    }


    /**
     * @param string|object $newScope
     */
    public function isCallable($value, $newScope = 'static') : bool
    {
        return Lib::php()->is_callable($value, $newScope);
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
     * @return array{ 0: object, 1: class-string, 2: null, 3: string }
     */
    private function parse_method_array_from_object($value) : ?array
    {
        if (! is_object($value)) {
            return null;
        }

        if ($value instanceof \Closure) {
            return null;
        }

        $theObject = $value;
        $theClass = get_class($value);

        if (method_exists($theClass, $theMagic = '__invoke')) {
            return [ $theObject, $theClass, null, $theMagic ];
        }

        return null;
    }

    /**
     * @return array{ 0: object|null, 1: class-string, 2: string, 3: string|null }
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

        if (! (
            ($isObject = is_object($classOrObject))
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
            if ($isObject && method_exists($theObject, $theMagic = '__call')) {
                return [ $theObject, $theClass, $theMethod, $theMagic ];
            }

            if (method_exists($theClass, $theMagic = '__callStatic')) {
                return [ $theObject, $theClass, $theMethod, $theMagic ];
            }
        }

        return null;
    }

    /**
     * @return array{ 0: null, 1: class-string, 2: string|null, 3: string|null }
     */
    private function parse_method_array_from_string($value) : ?array
    {
        if (! is_string($value)) {
            return null;
        }

        $list = explode('::', $value);

        [ $theClass, $theMethod ] = $list + [ '', '' ];

        $hasClass = ('' !== $theClass);
        $hasMethod = ('' !== $theMethod);

        if (! $hasClass) {
            return null;
        }

        if ($hasMethod) {
            if (method_exists($theClass, $theMethod)) {
                return [ null, $theClass, $theMethod, null ];

            } elseif (method_exists($theClass, $theMagic = '__callStatic')) {
                return [ null, $theClass, $theMethod, $theMagic ];
            }

        } else {
            if (method_exists($theClass, $theMagic = '__invoke')) {
                return [ null, $theClass, null, $theMagic ];
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

        if ($value instanceof \Closure) {
            return null;
        }

        if (! method_exists($value, '__invoke')) {
            return null;
        }

        return $value;
    }
}
