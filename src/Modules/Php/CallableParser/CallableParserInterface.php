<?php

namespace Gzhegow\Lib\Modules\Php\CallableParser;

use Gzhegow\Lib\Modules\Type\Ret;


interface CallableParserInterface
{
    /**
     * @param array{ 0?: array{ 0: class-string, 1: string }, 1?: string } $refs
     *
     * @return Ret<bool>
     */
    public function typeMethod($value, array $refs = []);

    /**
     * @return Ret<array{ 0: class-string, 1: string }>
     */
    public function typeMethodArray($value);

    /**
     * @return Ret<string>
     */
    public function typeMethodString($value);


    /**
     * @param string|object $newScope
     *
     * @return Ret<callable>
     */
    public function typeCallable($value, $newScope = 'static');


    /**
     * @return Ret<callable|\Closure|object>
     */
    public function typeCallableObject($value, $newScope = 'static');

    /**
     * @return Ret<\Closure>
     */
    public function typeCallableObjectClosure($value, $newScope = 'static');

    /**
     * @return Ret<callable|object>
     */
    public function typeCallableObjectInvokable($value, $newScope = 'static');


    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>
     */
    public function typeCallableArray($value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>
     */
    public function typeCallableArrayMethod($value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: class-string, 1: string }>
     */
    public function typeCallableArrayMethodStatic($value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object, 1: string }>
     */
    public function typeCallableArrayMethodNonStatic($value, $newScope = 'static');


    /**
     * @return Ret<callable-string>
     */
    public function typeCallableString($value, $newScope = 'static');

    /**
     * @return Ret<callable-string>
     */
    public function typeCallableStringFunction($value);

    /**
     * @return Ret<callable-string>
     */
    public function typeCallableStringFunctionInternal($value);

    /**
     * @return Ret<callable-string>
     */
    public function typeCallableStringFunctionNonInternal($value);

    /**
     * @return Ret<callable-string>
     */
    public function typeCallableStringMethodStatic($value, $newScope = 'static');


    /**
     * @param string|object $newScope
     */
    public function isCallable($value, $newScope = 'static') : bool;
}
