<?php

namespace Gzhegow\Lib\Modules\Php\CallableParser;

use Gzhegow\Lib\Modules\Type\Ret;


interface CallableParserInterface
{
    /**
     * @param array{ 0?: array{ 0: class-string, 1: string }, 1?: string } $refs
     *
     * @return Ret<bool>|bool
     */
    public function typeMethod($fb, $value, array $refs = []);

    /**
     * @return Ret<array{ 0: class-string, 1: string }>|array{ 0: class-string, 1: string }
     */
    public function typeMethodArray($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function typeMethodString($fb, $value);


    /**
     * @param string|object $newScope
     *
     * @return Ret<callable>|callable
     */
    public function typeCallable($fb, $value, $newScope = 'static');


    /**
     * @return Ret<callable|\Closure|object>|callable|\Closure|object
     */
    public function typeCallableObject($fb, $value, $newScope = 'static');

    /**
     * @return Ret<\Closure>|\Closure
     */
    public function typeCallableObjectClosure($fb, $value, $newScope = 'static');

    /**
     * @return Ret<callable|object>|callable|object
     */
    public function typeCallableObjectInvokable($fb, $value, $newScope = 'static');


    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>|callable|array{ 0: object|class-string, 1: string }
     */
    public function typeCallableArray($fb, $value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>|callable|array{ 0: object|class-string, 1: string }
     */
    public function typeCallableArrayMethod($fb, $value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: class-string, 1: string }>|callable|array{ 0: class-string, 1: string }
     */
    public function typeCallableArrayMethodStatic($fb, $value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object, 1: string }>|callable|array{ 0: object, 1: string }
     */
    public function typeCallableArrayMethodNonStatic($fb, $value, $newScope = 'static');


    /**
     * @return Ret<callable|string>|callable|string
     */
    public function typeCallableString($fb, $value, $newScope = 'static');

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function typeCallableStringFunction($fb, $value);

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function typeCallableStringFunctionInternal($fb, $value);

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function typeCallableStringFunctionNonInternal($fb, $value);

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function typeCallableStringMethodStatic($fb, $value, $newScope = 'static');


    /**
     * @param string|object $newScope
     */
    public function isCallable($value, $newScope = 'static') : bool;
}
