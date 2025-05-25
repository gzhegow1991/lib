<?php

namespace Gzhegow\Lib\Modules\Php\CallableParser;

use Gzhegow\Lib\Lib;


class DefaultCallableParser implements CallableParserInterface
{
    /**
     * @param array{ 0: class-string, 1: string }|null $r
     */
    public function typeMethodArray(&$r, $value) : bool
    {
        $r = null;

        $methodArray = null
            ?? $this->parseMethodArrayFromObject($value)
            ?? $this->parseMethodArrayFromArray($value)
            ?? $this->parseMethodArrayFromString($value);

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
                $r = [ $theClass, $theMagic ];

                return true;
            }

            return false;

        } elseif ($theMethod) {
            // > method provided and exists

            $r = [ $theClass, $theMethod ];

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function typeMethodString(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        $withMethodArray = array_key_exists(0, $refs);
        if ($withMethodArray) {
            $refResultArray =& $refs[ 0 ];
        }
        $refResultArray = null;

        $methodArray = null
            ?? $this->parseMethodArrayFromObject($value)
            ?? $this->parseMethodArrayFromArray($value)
            ?? $this->parseMethodArrayFromString($value);

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
                $r = "{$theClass}->__invoke";

                $refResultArray = [ $theClass, '__invoke' ];
                unset($refResultArray);

                return true;
            }

            return false;

        } elseif ($theMethod) {
            // > method provided and exists

            if (false
                || ($theMethod === '__invoke')
                || ($theMethod === '__call')
            ) {
                $r = "{$theClass}->{$theMethod}";

                $refResultArray = [ $theClass, $theMethod ];
                unset($refResultArray);

                return true;
            }

            if (false
                || ($theMethod === '__callStatic')
            ) {
                $r = "{$theClass}::{$theMethod}";

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

            $r = $isStatic
                ? "{$theClass}::{$theMethod}"
                : "{$theClass}->{$theMethod}";

            return true;
        }

        unset($refResultArray);

        return false;
    }


    /**
     * @param callable|null $r
     * @param string|object $newScope
     */
    public function typeCallable(&$r, $value, $newScope = 'static') : bool
    {
        $r = null;

        if (! $this->isCallable($value, $newScope)) {
            return false;
        }

        if (PHP_VERSION_ID >= 80000) {
            $r = $value;

            return true;
        }

        if (is_object($value)) {
            // > \Closure or invokable
            $r = $value;

            return true;
        }

        $function = $this->parseFunction($value);
        if (null !== $function) {
            // > plain function
            $r = $value;

            return true;
        }

        $methodArray = null
            ?? $this->parseMethodArrayFromArray($value)
            ?? $this->parseMethodArrayFromString($value);

        if (null === $methodArray) {
            return false;
        }

        [ $theObject, $theClass, $theMethod, $theMagic ] = $methodArray;

        if ($theObject) {
            // > array with object
            $r = $value;

            return true;
        }

        if (false
            || ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            $r = $value;

            return true;
        }

        if (false
            || ($theMethod === '__invoke')
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

        $r = $value;

        return true;
    }


    /**
     * @param callable|\Closure|object|null $r
     */
    public function typeCallableObject(&$r, $value, $newScope = 'static') : bool
    {
        $r = null;

        if (! is_object($value)) {
            return false;
        }

        $status = $this->typeCallableObjectClosure($closure, $value, $newScope);
        if ($status) {
            $r = $closure;

            return true;
        }

        $status = $this->typeCallableObjectInvokable($invokable, $value, $newScope);
        if ($status) {
            $r = $invokable;

            return true;
        }

        return false;
    }

    /**
     * @param callable|object|null $r
     */
    public function typeCallableObjectClosure(&$r, $value, $newScope = 'static') : bool
    {
        $r = null;

        if (! ($value instanceof \Closure)) {
            return false;
        }

        if (! $this->isCallable($value, $newScope)) {
            return false;
        }

        $r = $value;

        return true;
    }

    /**
     * @param callable|object|null $r
     */
    public function typeCallableObjectInvokable(&$r, $value, $newScope = 'static') : bool
    {
        $r = null;

        $invokable = $this->parseInvokable($value);

        if (null === $invokable) {
            return false;
        }

        if (! $this->isCallable($invokable, $newScope)) {
            return false;
        }

        $r = $invokable;

        return true;
    }


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $r
     * @param string|object                                            $newScope
     */
    public function typeCallableArray(&$r, $value, $newScope = 'static') : bool
    {
        $r = null;

        if (! is_array($value)) {
            return false;
        }

        $status = $this->typeCallableArrayMethod($method, $value, $newScope);
        if ($status) {
            $r = $method;

            return true;
        }

        return false;
    }

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $r
     * @param string|object                                            $newScope
     */
    public function typeCallableArrayMethod(&$r, $value, $newScope = 'static') : bool
    {
        $r = null;

        if (! is_array($value)) {
            return false;
        }

        $methodArray = $this->parseMethodArrayFromArray($value);

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

            $r = $callableMethodPublic;

            return true;
        }

        if (false
            || ($theMethod === '__invoke')
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

        if (false
            || ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            if (! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope)) {
                return false;
            }

            $r = $callableMethodStatic;

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

        $r = $callableMethodStatic;

        return true;
    }

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $r
     * @param string|object                                     $newScope
     */
    public function typeCallableArrayMethodStatic(&$r, $value, $newScope = 'static') : bool
    {
        $r = null;

        if (! is_array($value)) {
            return false;
        }

        $methodArray = $this->parseMethodArrayFromArray($value);

        if (null === $methodArray) {
            return false;
        }

        [ /* $theObject */, $theClass, $theMethod, $theMagic ] = $methodArray;

        if (null === $theMethod) {
            return false;
        }

        if (false
            || ($theMethod === '__invoke')
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

        if (false
            || ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            if (! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope)) {
                return false;
            }

            $r = $callableMethodStatic;

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

        $r = $callableMethodStatic;

        return true;
    }

    /**
     * @param callable|array{ 0: object, 1: string }|null $r
     * @param string|object                               $newScope
     */
    public function typeCallableArrayMethodNonStatic(&$r, $value, $newScope = 'static') : bool
    {
        $r = null;

        if (! is_array($value)) {
            return false;
        }

        $methodArray = $this->parseMethodArrayFromArray($value);

        if (null === $methodArray) {
            return false;
        }

        [ $theObject, $theClass, $theMethod, $theMagic ] = $methodArray;

        if (null === $theMethod) {
            return false;
        }

        if (false
            || ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            return false;
        }

        if (null === $theObject) {
            return false;
        }

        $callableMethodPublic = [ $theObject, $theMethod ];

        if (false
            || ($theMethod === '__invoke')
            || ($theMethod === '__call')
            || ($theMagic === '__invoke')
            || ($theMagic === '__call')
        ) {
            $r = $callableMethodPublic;

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

        $r = $callableMethodPublic;

        return true;
    }


    /**
     * @param callable-string|null $r
     */
    public function typeCallableString(&$r, $value, $newScope = 'static') : bool
    {
        $r = null;

        if (! is_string($value)) {
            return false;
        }

        $status = $this->typeCallableStringFunction($function, $value);
        if ($status) {
            $r = $function;

            return true;
        }

        $status = $this->typeCallableStringMethodStatic($methodStatic, $value, $newScope);
        if ($status) {
            $r = $methodStatic;

            return true;
        }

        return false;
    }

    /**
     * @param callable-string|null $r
     */
    public function typeCallableStringFunction(&$r, $value) : bool
    {
        $r = null;

        if (! is_string($value)) {
            return false;
        }

        if (function_exists($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param callable-string|null $r
     */
    public function typeCallableStringFunctionInternal(&$r, $value) : bool
    {
        $r = null;

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
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param callable-string|null $r
     */
    public function typeCallableStringFunctionNonInternal(&$r, $value) : bool
    {
        $r = null;

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
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param callable-string|null $r
     */
    public function typeCallableStringMethodStatic(&$r, $value, $newScope = 'static') : bool
    {
        $r = null;

        if (! is_string($value)) {
            return false;
        }

        $methodArray = $this->parseMethodArrayFromString($value);

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

        if (false
            || ($theMethod === '__callStatic')
            || ($theMagic === '__callStatic')
        ) {
            if (! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope)) {
                return false;
            }

            $r = $callableMethodStatic;

            return true;
        }

        if (false
            || ($theMethod === '__invoke')
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

        $r = $callableMethodStatic;

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
    private function parseFunction($value) : ?string
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
    private function parseMethodArrayFromObject($value) : ?array
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
    private function parseMethodArrayFromArray($value) : ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $list = array_values($value);

        [ $classOrObject, $theMethod ] = $list + [ '', '' ];

        if (! ((is_string($theMethod)) && ('' !== $theMethod))) {
            return null;
        }

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
    private function parseMethodArrayFromString($value) : ?array
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
    private function parseInvokable($value) : ?object
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
