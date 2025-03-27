<?php

namespace Gzhegow\Lib\Modules\Php\CallableParser;

interface CallableParserInterface
{
    /**
     * @param array{ 0: class-string, 1: string }|null $result
     */
    public function typeMethodArray(&$result, $value) : bool;

    /**
     * @param string|null                                          $result
     * @param array{ 0: array{ 0: class-string, 1: string }|null } $refs
     */
    public function typeMethodString(&$result, $value, array $refs = []) : bool;


    /**
     * @param callable|null $result
     * @param string|object $newScope
     */
    public function typeCallable(&$result, $value, $newScope = 'static') : bool;


    /**
     * @param callable|\Closure|object|null $result
     */
    public function typeCallableObject(&$result, $value, $newScope = 'static') : bool;

    /**
     * @param callable|object|null $result
     */
    public function typeCallableObjectClosure(&$result, $value, $newScope = 'static') : bool;

    /**
     * @param callable|object|null $result
     */
    public function typeCallableObjectInvokable(&$result, $value, $newScope = 'static') : bool;


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function typeCallableArray(&$result, $value, $newScope = 'static') : bool;

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function typeCallableArrayMethod(&$result, $value, $newScope = 'static') : bool;

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $result
     * @param string|object                                     $newScope
     */
    public function typeCallableArrayMethodStatic(&$result, $value, $newScope = 'static') : bool;

    /**
     * @param callable|array{ 0: object, 1: string }|null $result
     * @param string|object                               $newScope
     */
    public function typeCallableArrayMethodNonStatic(&$result, $value, $newScope = 'static') : bool;


    /**
     * @param callable-string|null $result
     */
    public function typeCallableString(&$result, $value, $newScope = 'static') : bool;

    /**
     * @param callable-string|null $result
     */
    public function typeCallableStringFunction(&$result, $value) : bool;

    /**
     * @param callable-string|null $result
     */
    public function typeCallableStringFunctionInternal(&$result, $value) : bool;

    /**
     * @param callable-string|null $result
     */
    public function typeCallableStringFunctionNonInternal(&$result, $value) : bool;

    /**
     * @param callable-string|null $result
     */
    public function typeCallableStringMethodStatic(&$result, $value, $newScope = 'static') : bool;


    /**
     * @param string|object $newScope
     */
    public function isCallable($value, $newScope = 'static') : bool;
}
