<?php

namespace Gzhegow\Lib\Modules\Php\CallableParser;

interface CallableParserInterface
{
    /**
     * @param array{ 0: class-string, 1: string }|null $r
     */
    public function typeMethodArray(&$r, $value) : bool;

    /**
     * @param string|null $r
     */
    public function typeMethodString(&$r, $value, array $refs = []) : bool;


    /**
     * @param callable|null $r
     * @param string|object $newScope
     */
    public function typeCallable(&$r, $value, $newScope = 'static') : bool;


    /**
     * @param callable|\Closure|object|null $r
     */
    public function typeCallableObject(&$r, $value, $newScope = 'static') : bool;

    /**
     * @param callable|object|null $r
     */
    public function typeCallableObjectClosure(&$r, $value, $newScope = 'static') : bool;

    /**
     * @param callable|object|null $r
     */
    public function typeCallableObjectInvokable(&$r, $value, $newScope = 'static') : bool;


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $r
     * @param string|object                                            $newScope
     */
    public function typeCallableArray(&$r, $value, $newScope = 'static') : bool;

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $r
     * @param string|object                                            $newScope
     */
    public function typeCallableArrayMethod(&$r, $value, $newScope = 'static') : bool;

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $r
     * @param string|object                                     $newScope
     */
    public function typeCallableArrayMethodStatic(&$r, $value, $newScope = 'static') : bool;

    /**
     * @param callable|array{ 0: object, 1: string }|null $r
     * @param string|object                               $newScope
     */
    public function typeCallableArrayMethodNonStatic(&$r, $value, $newScope = 'static') : bool;


    /**
     * @param callable-string|null $r
     */
    public function typeCallableString(&$r, $value, $newScope = 'static') : bool;

    /**
     * @param callable-string|null $r
     */
    public function typeCallableStringFunction(&$r, $value) : bool;

    /**
     * @param callable-string|null $r
     */
    public function typeCallableStringFunctionInternal(&$r, $value) : bool;

    /**
     * @param callable-string|null $r
     */
    public function typeCallableStringFunctionNonInternal(&$r, $value) : bool;

    /**
     * @param callable-string|null $r
     */
    public function typeCallableStringMethodStatic(&$r, $value, $newScope = 'static') : bool;


    /**
     * @param string|object $newScope
     */
    public function isCallable($value, $newScope = 'static') : bool;
}
