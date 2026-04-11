<?php

namespace Gzhegow\Lib\Modules\Php\CallableParser;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;


class DefaultCallableParser implements CallableParserInterface
{
    /**
     * @param array{ 0?: array{ 0: class-string, 1: string }, 1?: string } $refs
     *
     * @return Ret<bool>|bool
     */
    public function typeMethod($fb, $value, array $refs = [])
    {
        $withMethodArray = array_key_exists(0, $refs);
        if ( $withMethodArray ) {
            $refMethodArray =& $refs[0];
        }
        $refMethodArray = null;

        $withMethodString = array_key_exists(1, $refs);
        if ( $withMethodString ) {
            $refMethodString =& $refs[1];
        }
        $refMethodString = null;

        $methodArray = null
            ?? $this->parseMethodArrayFromObject($value)
            ?? $this->parseMethodArrayFromArray($value)
            ?? $this->parseMethodArrayFromString($value);

        if ( null === $methodArray ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be method (object, array or string)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ /* $anObject */, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( $aMethod && $aMagic ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be real (not magic) method', $value ],
                [ __FILE__, __LINE__ ]
            );

        } elseif ( $aMagic ) {
            // > method not provided, but class or object has magic method
            if ( false
                || ($aMagic === '__invoke')
            ) {
                $refMethodArray = [ $aClass, '__invoke' ];
                $refMethodString = "{$aClass}->__invoke";

                return Ret::ok($fb, true);
            }

            return Ret::throw(
                $fb,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );

        } elseif ( $aMethod ) {
            // > method provided and exists

            if ( false
                || ($aMethod === '__invoke')
                || ($aMethod === '__call')
            ) {
                $refMethodArray = [ $aClass, $aMethod ];
                $refMethodString = "{$aClass}->{$aMethod}";

                return Ret::ok($fb, true);
            }

            if ( false
                || ($aMethod === '__callStatic')
            ) {
                $refMethodArray = [ $aClass, $aMethod ];
                $refMethodString = "{$aClass}::{$aMethod}";

                return Ret::ok($fb, true);
            }

            try {
                $rm = new \ReflectionMethod($aClass, $aMethod);

                $isStatic = $rm->isStatic();
            }
            catch ( \Throwable $e ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be existing method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $refMethodArray = [ $aClass, $aMethod ];
            $refMethodString = $isStatic
                ? "{$aClass}::{$aMethod}"
                : "{$aClass}->{$aMethod}";

            return Ret::ok($fb, true);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be valid method', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<array{ 0: class-string, 1: string }>|array{ 0: class-string, 1: string }
     */
    public function typeMethodArray($fb, $value)
    {
        $ret = $this->typeMethod(null, $value, [ &$refMethodArray ]);

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $refMethodArray);
    }

    /**
     * @return Ret<string>|string
     */
    public function typeMethodString($fb, $value)
    {
        $ret = $this->typeMethod(null, $value, [ 1 => &$refMethodString ]);

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $refMethodString);
    }


    /**
     * @param string|object $newScope
     *
     * @return Ret<callable>|callable
     */
    public function typeCallable($fb, $value, $newScope = 'static')
    {
        if ( ! $this->isCallable($value, $newScope) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( PHP_VERSION_ID >= 80000 ) {
            return Ret::ok($fb, $value);
        }

        if ( is_object($value) ) {
            // > \Closure or invokable
            return Ret::ok($fb, $value);
        }

        $function = $this->parseFunction($value);
        if ( null !== $function ) {
            // > plain function
            return Ret::ok($fb, $value);
        }

        $methodArray = null
            ?? $this->parseMethodArrayFromArray($value)
            ?? $this->parseMethodArrayFromString($value);

        if ( null === $methodArray ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be method (array or string)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ $anObject, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( $anObject ) {
            // > array with object
            return Ret::ok($fb, $value);
        }

        if ( false
            || ($aMethod === '__callStatic')
            || ($aMagic === '__callStatic')
        ) {
            return Ret::ok($fb, $value);
        }

        if ( false
            || ($aMethod === '__invoke')
            || ($aMethod === '__call')
            || ($aMagic === '__invoke')
            || ($aMagic === '__call')
        ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be real (not magic and not invoke) method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $rm = new \ReflectionMethod($aClass, $aMethod);

            if ( ! $rm->isStatic() ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be static method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $value);
    }


    /**
     * @return Ret<callable|\Closure|object>|callable|\Closure|object
     */
    public function typeCallableObject($fb, $value, $newScope = 'static')
    {
        if ( ! is_object($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be object', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->typeCallableObjectClosure(null, $value, $newScope);

        if ( $ret->isOk([ &$valueCallableObjectClosure ]) ) {
            return Ret::ok($fb, $valueCallableObjectClosure);
        }

        $ret = $this->typeCallableObjectInvokable(null, $value, $newScope);

        if ( $ret->isOk([ &$valueCallableObjectInvokable ]) ) {
            return Ret::ok($fb, $valueCallableObjectInvokable);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be callable, object', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<\Closure>|\Closure
     */
    public function typeCallableObjectClosure($fb, $value, $newScope = 'static')
    {
        if ( ! ($value instanceof \Closure) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be \Closure', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($value, $newScope) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` \Closure is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @return Ret<callable|object>|callable|object
     */
    public function typeCallableObjectInvokable($fb, $value, $newScope = 'static')
    {
        $invokable = $this->parseInvokable($value);
        if ( null === $invokable ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be invokable object', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($invokable, $newScope) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` invokable is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $invokable);
    }


    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>|callable|array{ 0: object|class-string, 1: string }
     */
    public function typeCallableArray($fb, $value, $newScope = 'static')
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->typeCallableArrayMethod(null, $value, $newScope);

        if ( $ret->isOk([ &$valueCallableArrayMethod ]) ) {
            return Ret::ok($fb, $valueCallableArrayMethod);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be callable array', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>|callable|array{ 0: object|class-string, 1: string }
     */
    public function typeCallableArrayMethod($fb, $value, $newScope = 'static')
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $methodArray = $this->parseMethodArrayFromArray($value);
        if ( null === $methodArray ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ $anObject, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( null === $aMethod ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $anObject ) {
            $callableMethodPublic = [ $anObject, $aMethod ];

            $callableMethodPublicMagic = null;
            if ( $aMagic ) {
                $callableMethodPublicMagic = [ $anObject, $aMagic ];
            }

            if ( ! $this->isCallable($callableMethodPublicMagic ?? $callableMethodPublic, $newScope) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok($fb, $callableMethodPublic);
        }

        if ( false
            || ($aMethod === '__invoke')
            || ($aMethod === '__call')
            || ($aMagic === '__invoke')
            || ($aMagic === '__call')
        ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be real (not magic and not invoke) method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $callableMethodStatic = [ $aClass, $aMethod ];

        $callableMethodStaticMagic = null;
        if ( $aMagic ) {
            $callableMethodStaticMagic = [ $aClass, $aMagic ];
        }

        if ( false
            || ($aMethod === '__callStatic')
            || ($aMagic === '__callStatic')
        ) {
            if ( ! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok($fb, $callableMethodStatic);
        }

        try {
            $rm = new \ReflectionMethod($aClass, $aMethod);

            if ( ! $rm->isStatic() ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be static method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($callableMethodStatic, $newScope) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $callableMethodStatic);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: class-string, 1: string }>|callable|array{ 0: class-string, 1: string }
     */
    public function typeCallableArrayMethodStatic($fb, $value, $newScope = 'static')
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $methodArray = $this->parseMethodArrayFromArray($value);
        if ( null === $methodArray ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ /* $anObject */, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( null === $aMethod ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( false
            || ($aMethod === '__invoke')
            || ($aMethod === '__call')
            || ($aMagic === '__invoke')
            || ($aMagic === '__call')
        ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be real (not magic and not invoke) method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $callableMethodStatic = [ $aClass, $aMethod ];

        $callableMethodStaticMagic = null;
        if ( $aMagic ) {
            $callableMethodStaticMagic = [ $aClass, $aMagic ];
        }

        if ( false
            || ($aMethod === '__callStatic')
            || ($aMagic === '__callStatic')
        ) {
            if ( ! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok($fb, $callableMethodStatic);
        }

        try {
            $rm = new \ReflectionMethod($aClass, $aMethod);

            if ( ! $rm->isStatic() ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be static method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($callableMethodStatic, $newScope) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $callableMethodStatic);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object, 1: string }>|callable|array{ 0: object, 1: string }
     */
    public function typeCallableArrayMethodNonStatic($fb, $value, $newScope = 'static')
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $methodArray = $this->parseMethodArrayFromArray($value);
        if ( null === $methodArray ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ $anObject, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( null === $aMethod ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( false
            || ($aMethod === '__callStatic')
            || ($aMagic === '__callStatic')
        ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array with non-static method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null === $anObject ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array with non-static method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $callableMethodPublic = [ $anObject, $aMethod ];

        if ( false
            || ($aMethod === '__invoke')
            || ($aMethod === '__call')
            || ($aMagic === '__invoke')
            || ($aMagic === '__call')
        ) {
            return Ret::ok($fb, $callableMethodPublic);
        }

        try {
            $rm = new \ReflectionMethod($aClass, $aMethod);

            if ( $rm->isStatic() ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be non static method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($callableMethodPublic, $newScope) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $callableMethodPublic);
    }


    /**
     * @return Ret<callable|string>|callable|string
     */
    public function typeCallableString($fb, $value, $newScope = 'static')
    {
        if ( ! is_string($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->typeCallableStringFunction(null, $value);

        if ( $ret->isOk([ &$valueCallableStringFunction ]) ) {
            return Ret::ok($fb, $valueCallableStringFunction);
        }

        $ret = $this->typeCallableStringMethodStatic(null, $value, $newScope);

        if ( $ret->isOk([ &$valueCallableStringMethodStatic ]) ) {
            return Ret::ok($fb, $valueCallableStringMethodStatic);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be callable string', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function typeCallableStringFunction($fb, $value)
    {
        if ( ! is_string($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( function_exists($value) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be callable string function', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function typeCallableStringFunctionInternal($fb, $value)
    {
        if ( ! is_string($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $rf = new \ReflectionFunction($value);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be existing function name', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $rf->isInternal() ) {
            return Ret::ok($fb, $rf->getName());
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be internal function name', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function typeCallableStringFunctionNonInternal($fb, $value)
    {
        if ( ! is_string($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $rf = new \ReflectionFunction($value);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be existing function name', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $rf->isInternal() ) {
            return Ret::ok($fb, $rf->getName());
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be non-internal function name', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function typeCallableStringMethodStatic($fb, $value, $newScope = 'static')
    {
        if ( ! is_string($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $methodArray = $this->parseMethodArrayFromString($value);
        if ( null === $methodArray ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be string with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ /* $anObject */, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( null === $aMethod ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $callableMethodStatic = "{$aClass}::{$aMethod}";

        $callableMethodStaticMagic = null;
        if ( $aMagic ) {
            $callableMethodStaticMagic = "{$aClass}::{$aMagic}";
        }

        if ( false
            || ($aMethod === '__callStatic')
            || ($aMagic === '__callStatic')
        ) {
            if ( ! $this->isCallable($callableMethodStaticMagic ?? $callableMethodStatic, $newScope) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok($fb, $callableMethodStatic);
        }

        if ( false
            || ($aMethod === '__invoke')
            || ($aMethod === '__call')
            || ($aMagic === '__invoke')
            || ($aMagic === '__call')
        ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be real (not magic and not invoke) method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $rm = new \ReflectionMethod($aClass, $aMethod);

            if ( ! $rm->isStatic() ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be static method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($callableMethodStatic, $newScope) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $callableMethodStatic);
    }


    /**
     * @param string|object $newScope
     */
    public function isCallable($value, $newScope = 'static') : bool
    {
        return Lib::php()->is_callable($value, $newScope);
    }


    /**
     * @return callable|string|null
     */
    protected function parseFunction($value) : ?string
    {
        if ( ! is_string($value) ) {
            return null;
        }

        if ( ! function_exists($value) ) {
            return null;
        }

        return $value;
    }


    /**
     * @return array{ 0: object, 1: class-string, 2: null, 3: string }
     */
    protected function parseMethodArrayFromObject($value) : ?array
    {
        if ( ! is_object($value) ) {
            return null;
        }

        if ( $value instanceof \Closure ) {
            return null;
        }

        $anObject = $value;
        $aClass = get_class($value);

        if ( method_exists($aClass, $aMagic = '__invoke') ) {
            return [ $anObject, $aClass, null, $aMagic ];
        }

        return null;
    }

    /**
     * @return array{ 0: object|null, 1: class-string, 2: string, 3: string|null }
     */
    protected function parseMethodArrayFromArray($value) : ?array
    {
        if ( ! is_array($value) ) {
            return null;
        }

        $list = array_values($value);

        [ $aClassOrAnObject, $aMethod ] = $list + [ '', '' ];

        if ( ! ((is_string($aMethod)) && ('' !== $aMethod)) ) {
            return null;
        }

        if ( ! (false
            || ($isObject = is_object($aClassOrAnObject))
            || ($isClass = is_string($aClassOrAnObject) && class_exists($aClassOrAnObject))
        ) ) {
            return null;
        }

        $anObject = null;
        $aClass = null;
        if ( $isObject ) {
            $anObject = $aClassOrAnObject;
            $aClass = get_class($anObject);

        } elseif ( $isClass ) {
            $aClass = $aClassOrAnObject;
        }

        if ( method_exists($aClass, $aMethod) ) {
            return [ $anObject, $aClass, $aMethod, null ];

        } else {
            if ( $isObject && method_exists($anObject, $aMagic = '__call') ) {
                return [ $anObject, $aClass, $aMethod, $aMagic ];
            }

            if ( method_exists($aClass, $aMagic = '__callStatic') ) {
                return [ $anObject, $aClass, $aMethod, $aMagic ];
            }
        }

        return null;
    }

    /**
     * @return array{ 0: null, 1: class-string, 2: string|null, 3: string|null }
     */
    protected function parseMethodArrayFromString($value) : ?array
    {
        if ( ! is_string($value) ) {
            return null;
        }

        $list = explode('::', $value);

        [ $aClass, $aMethod ] = $list + [ '', '' ];

        $hasClass = ('' !== $aClass);
        $hasMethod = ('' !== $aMethod);

        if ( ! $hasClass ) {
            return null;
        }

        if ( $hasMethod ) {
            if ( method_exists($aClass, $aMethod) ) {
                return [ null, $aClass, $aMethod, null ];

            } elseif ( method_exists($aClass, $aMagic = '__callStatic') ) {
                return [ null, $aClass, $aMethod, $aMagic ];
            }

        } else {
            if ( method_exists($aClass, $aMagic = '__invoke') ) {
                return [ null, $aClass, null, $aMagic ];
            }
        }

        return null;
    }


    /**
     * @return object|callable
     */
    protected function parseInvokable($value) : ?object
    {
        if ( ! is_object($value) ) {
            return null;
        }

        if ( $value instanceof \Closure ) {
            return null;
        }

        if ( ! method_exists($value, '__invoke') ) {
            return null;
        }

        return $value;
    }
}
