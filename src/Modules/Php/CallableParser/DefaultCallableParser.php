<?php

namespace Gzhegow\Lib\Modules\Php\CallableParser;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;


class DefaultCallableParser implements CallableParserInterface
{
    /**
     * @param array{ 0?: array{ 0: class-string, 1: string }, 1?: string } $refs
     *
     * @return Ret<bool>
     */
    public function typeMethod($value, array $refs = [])
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
                null,
                [ 'The `value` should be method (object, array or string)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ /* $anObject */, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( $aMethod && $aMagic ) {
            return Ret::throw(
                null,
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

                return Ret::ok(null, true);
            }

            return Ret::throw(
                null,
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

                return Ret::ok(null, true);
            }

            if ( false
                || ($aMethod === '__callStatic')
            ) {
                $refMethodArray = [ $aClass, $aMethod ];
                $refMethodString = "{$aClass}::{$aMethod}";

                return Ret::ok(null, true);
            }

            try {
                $rm = new \ReflectionMethod($aClass, $aMethod);

                $isStatic = $rm->isStatic();
            }
            catch ( \Throwable $e ) {
                return Ret::throw(
                    null,
                    [ 'The `value` should be existing method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $refMethodArray = [ $aClass, $aMethod ];
            $refMethodString = $isStatic
                ? "{$aClass}::{$aMethod}"
                : "{$aClass}->{$aMethod}";

            return Ret::ok(null, true);
        }

        return Ret::throw(
            null,
            [ 'The `value` should be valid method', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<array{ 0: class-string, 1: string }>
     */
    public function typeMethodArray($value)
    {
        if ( ! $this->typeMethod($value, [ &$refMethodArray ])->isOk([ 1 => &$ret ]) ) {
            return Ret::throw(
                null,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok(null, $refMethodArray);
    }

    /**
     * @return Ret<string>
     */
    public function typeMethodString($value)
    {
        if ( ! $this->typeMethod($value, [ 1 => &$refMethodString ])->isOk([ 1 => &$ret ]) ) {
            return Ret::throw(
                null,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok(null, $refMethodString);
    }


    /**
     * @param string|object $newScope
     *
     * @return Ret<callable>
     */
    public function typeCallable($value, $newScope = 'static')
    {
        if ( ! $this->isCallable($value, $newScope) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( PHP_VERSION_ID >= 80000 ) {
            return Ret::ok(null, $value);
        }

        if ( is_object($value) ) {
            // > \Closure or invokable
            return Ret::ok(null, $value);
        }

        $function = $this->parseFunction($value);
        if ( null !== $function ) {
            // > plain function
            return Ret::ok(null, $value);
        }

        $methodArray = null
            ?? $this->parseMethodArrayFromArray($value)
            ?? $this->parseMethodArrayFromString($value);

        if ( null === $methodArray ) {
            return Ret::throw(
                null,
                [ 'The `value` should be method (array or string)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ $anObject, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( $anObject ) {
            // > array with object
            return Ret::ok(null, $value);
        }

        if ( false
            || ($aMethod === '__callStatic')
            || ($aMagic === '__callStatic')
        ) {
            return Ret::ok(null, $value);
        }

        if ( false
            || ($aMethod === '__invoke')
            || ($aMethod === '__call')
            || ($aMagic === '__invoke')
            || ($aMagic === '__call')
        ) {
            return Ret::throw(
                null,
                [ 'The `value` should be real (not magic and not invoke) method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $rm = new \ReflectionMethod($aClass, $aMethod);

            if ( ! $rm->isStatic() ) {
                return Ret::throw(
                    null,
                    [ 'The `value` should be static method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                null,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok(null, $value);
    }


    /**
     * @return Ret<callable|\Closure|object>
     */
    public function typeCallableObject($value, $newScope = 'static')
    {
        if ( ! is_object($value) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be object', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $this->typeCallableObjectClosure($value, $newScope)->isOk([ &$valueCallableObjectClosure ]) ) {
            return Ret::ok(null, $valueCallableObjectClosure);
        }

        if ( $this->typeCallableObjectInvokable($value, $newScope)->isOk([ &$valueCallableObjectInvokable ]) ) {
            return Ret::ok(null, $valueCallableObjectInvokable);
        }

        return Ret::throw(
            null,
            [ 'The `value` should be callable, object', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<\Closure>
     */
    public function typeCallableObjectClosure($value, $newScope = 'static')
    {
        if ( ! ($value instanceof \Closure) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be \Closure', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($value, $newScope) ) {
            return Ret::throw(
                null,
                [ 'The `value` \Closure is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok(null, $value);
    }

    /**
     * @return Ret<callable|object>
     */
    public function typeCallableObjectInvokable($value, $newScope = 'static')
    {
        $invokable = $this->parseInvokable($value);
        if ( null === $invokable ) {
            return Ret::throw(
                null,
                [ 'The `value` should be invokable object', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($invokable, $newScope) ) {
            return Ret::throw(
                null,
                [ 'The `value` invokable is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok(null, $invokable);
    }


    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>
     */
    public function typeCallableArray($value, $newScope = 'static')
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $this->typeCallableArrayMethod($value, $newScope)->isOk([ &$valueCallableArrayMethod ]) ) {
            return Ret::ok(null, $valueCallableArrayMethod);
        }

        return Ret::throw(
            null,
            [ 'The `value` should be callable array', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>
     */
    public function typeCallableArrayMethod($value, $newScope = 'static')
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $methodArray = $this->parseMethodArrayFromArray($value);
        if ( null === $methodArray ) {
            return Ret::throw(
                null,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ $anObject, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( null === $aMethod ) {
            return Ret::throw(
                null,
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
                    null,
                    [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok(null, $callableMethodPublic);
        }

        if ( false
            || ($aMethod === '__invoke')
            || ($aMethod === '__call')
            || ($aMagic === '__invoke')
            || ($aMagic === '__call')
        ) {
            return Ret::throw(
                null,
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
                    null,
                    [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok(null, $callableMethodStatic);
        }

        try {
            $rm = new \ReflectionMethod($aClass, $aMethod);

            if ( ! $rm->isStatic() ) {
                return Ret::throw(
                    null,
                    [ 'The `value` should be static method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                null,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($callableMethodStatic, $newScope) ) {
            return Ret::throw(
                null,
                [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok(null, $callableMethodStatic);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: class-string, 1: string }>
     */
    public function typeCallableArrayMethodStatic($value, $newScope = 'static')
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $methodArray = $this->parseMethodArrayFromArray($value);
        if ( null === $methodArray ) {
            return Ret::throw(
                null,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ /* $anObject */, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( null === $aMethod ) {
            return Ret::throw(
                null,
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
                null,
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
                    null,
                    [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok(null, $callableMethodStatic);
        }

        try {
            $rm = new \ReflectionMethod($aClass, $aMethod);

            if ( ! $rm->isStatic() ) {
                return Ret::throw(
                    null,
                    [ 'The `value` should be static method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                null,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($callableMethodStatic, $newScope) ) {
            return Ret::throw(
                null,
                [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok(null, $callableMethodStatic);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object, 1: string }>
     */
    public function typeCallableArrayMethodNonStatic($value, $newScope = 'static')
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $methodArray = $this->parseMethodArrayFromArray($value);
        if ( null === $methodArray ) {
            return Ret::throw(
                null,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ $anObject, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( null === $aMethod ) {
            return Ret::throw(
                null,
                [ 'The `value` should be array with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( false
            || ($aMethod === '__callStatic')
            || ($aMagic === '__callStatic')
        ) {
            return Ret::throw(
                null,
                [ 'The `value` should be array with non-static method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null === $anObject ) {
            return Ret::throw(
                null,
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
            return Ret::ok(null, $callableMethodPublic);
        }

        try {
            $rm = new \ReflectionMethod($aClass, $aMethod);

            if ( $rm->isStatic() ) {
                return Ret::throw(
                    null,
                    [ 'The `value` should be non static method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                null,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($callableMethodPublic, $newScope) ) {
            return Ret::throw(
                null,
                [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok(null, $callableMethodPublic);
    }


    /**
     * @return Ret<callable|callable-string>
     */
    public function typeCallableString($value, $newScope = 'static')
    {
        if ( ! is_string($value) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $this->typeCallableStringFunction($value)->isOk([ &$valueCallableStringFunction ]) ) {
            return Ret::ok(null, $valueCallableStringFunction);
        }

        if ( $this->typeCallableStringMethodStatic($value, $newScope)->isOk([ &$valueCallableStringMethodStatic ]) ) {
            return Ret::ok(null, $valueCallableStringMethodStatic);
        }

        return Ret::throw(
            null,
            [ 'The `value` should be callable string', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function typeCallableStringFunction($value)
    {
        if ( ! is_string($value) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( function_exists($value) ) {
            return Ret::ok(null, $value);
        }

        return Ret::throw(
            null,
            [ 'The `value` should be callable string function', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function typeCallableStringFunctionInternal($value)
    {
        if ( ! is_string($value) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $rf = new \ReflectionFunction($value);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                null,
                [ 'The `value` should be existing function name', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $rf->isInternal() ) {
            return Ret::ok(null, $rf->getName());
        }

        return Ret::throw(
            null,
            [ 'The `value` should be internal function name', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function typeCallableStringFunctionNonInternal($value)
    {
        if ( ! is_string($value) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $rf = new \ReflectionFunction($value);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                null,
                [ 'The `value` should be existing function name', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $rf->isInternal() ) {
            return Ret::ok(null, $rf->getName());
        }

        return Ret::throw(
            null,
            [ 'The `value` should be non-internal function name', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function typeCallableStringMethodStatic($value, $newScope = 'static')
    {
        if ( ! is_string($value) ) {
            return Ret::throw(
                null,
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $methodArray = $this->parseMethodArrayFromString($value);
        if ( null === $methodArray ) {
            return Ret::throw(
                null,
                [ 'The `value` should be string with method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ /* $anObject */, $aClass, $aMethod, $aMagic ] = $methodArray;

        if ( null === $aMethod ) {
            return Ret::throw(
                null,
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
                    null,
                    [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok(null, $callableMethodStatic);
        }

        if ( false
            || ($aMethod === '__invoke')
            || ($aMethod === '__call')
            || ($aMagic === '__invoke')
            || ($aMagic === '__call')
        ) {
            return Ret::throw(
                null,
                [ 'The `value` should be real (not magic and not invoke) method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $rm = new \ReflectionMethod($aClass, $aMethod);

            if ( ! $rm->isStatic() ) {
                return Ret::throw(
                    null,
                    [ 'The `value` should be static method', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                null,
                [ 'The `value` should be existing method', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $this->isCallable($callableMethodStatic, $newScope) ) {
            return Ret::throw(
                null,
                [ 'The `value` method is not callable in passed scope', $value, $newScope ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok(null, $callableMethodStatic);
    }


    /**
     * @param string|object $newScope
     */
    public function isCallable($value, $newScope = 'static') : bool
    {
        return Lib::php()->is_callable($value, $newScope);
    }


    /**
     * @return callable|callable-string|null
     *
     * @noinspection PhpDocSignatureInspection
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
     *
     * @noinspection PhpDocSignatureInspection
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
     *
     * @noinspection PhpDocSignatureInspection
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
     *
     * @noinspection PhpDocSignatureInspection
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
