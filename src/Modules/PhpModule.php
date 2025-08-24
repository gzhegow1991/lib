<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Nil;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\ErrorBag\ErrorBag;
use Gzhegow\Lib\Modules\Php\Interfaces\ToListInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToBoolInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToFloatInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToObjectInterface;
use Gzhegow\Lib\Modules\Php\Pooling\DefaultPoolingFactory;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIntegerInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIterableInterface;
use Gzhegow\Lib\Modules\Php\Pooling\PoolingFactoryInterface;
use Gzhegow\Lib\Modules\Php\CallableParser\DefaultCallableParser;
use Gzhegow\Lib\Modules\Php\CallableParser\CallableParserInterface;


class PhpModule
{
    /**
     * @var class-string<\LogicException|\RuntimeException>
     */
    protected static $throwableClass = RuntimeException::class;
    /**
     * @var int
     */
    protected static $poolingTickUsleep = 1000;

    /**
     * @param class-string<\LogicException|\RuntimeException>|false|null $throwableClass
     *
     * @return class-string<\LogicException|\RuntimeException>
     */
    public static function staticThrowableClass($throwableClass = null) : string
    {
        $last = static::$throwableClass;

        if (null !== $throwableClass) {
            if (false === $throwableClass) {
                static::$throwableClass = RuntimeException::class;

            } else {
                if (! (false
                    || is_subclass_of($throwableClass, \LogicException::class)
                    || is_subclass_of($throwableClass, \RuntimeException::class)
                )) {
                    throw new LogicException(
                        [
                            ''
                            . 'The `throwableClass` should be a class-string that is subclass one of: '
                            . '[ ' . implode(' ][ ', [ \LogicException::class, \RuntimeException::class ]) . ' ]',
                            //
                            $throwableClass,
                        ]
                    );
                }

                static::$throwableClass = $throwableClass;
            }
        }

        static::$throwableClass = static::$throwableClass ?? RuntimeException::class;

        return $last;
    }

    /**
     * @param int|false|null $poolingTickUsleep
     */
    public static function staticPoolingTickUsleep($poolingTickUsleep = null) : int
    {
        $last = static::$poolingTickUsleep;

        if (null !== $poolingTickUsleep) {
            if (false === $poolingTickUsleep) {
                static::$poolingTickUsleep = 1000;

            } else {
                if ($poolingTickUsleep < 1) {
                    throw new LogicException(
                        [ 'The `pooling_tick_usleep` should be a positive integer', $poolingTickUsleep ]
                    );
                }

                static::$poolingTickUsleep = $poolingTickUsleep;
            }
        }

        static::$poolingTickUsleep = static::$poolingTickUsleep ?? 1000;

        return $last;
    }


    /**
     * @var CallableParserInterface
     */
    protected $callableParser;
    /**
     * @var PoolingFactoryInterface
     */
    protected $poolingFactory;


    /**
     * @return ErrorBag
     */
    public function newErrorBag()
    {
        return new ErrorBag();
    }


    public function newPoolingFactory() : PoolingFactoryInterface
    {
        return new DefaultPoolingFactory();
    }

    public function clonePoolingFactory() : PoolingFactoryInterface
    {
        return clone $this->poolingFactory();
    }

    public function poolingFactory(?PoolingFactoryInterface $poolingFactory = null) : PoolingFactoryInterface
    {
        return $this->poolingFactory = null
            ?? $poolingFactory
            ?? $this->poolingFactory
            ?? $this->newPoolingFactory();
    }


    public function newCallableParser() : CallableParserInterface
    {
        return new DefaultCallableParser();
    }

    public function cloneCallableParser() : CallableParserInterface
    {
        return clone $this->callableParser();
    }

    public function callableParser(?CallableParserInterface $callableParser = null) : CallableParserInterface
    {
        return $this->callableParser = null
            ?? $callableParser
            ?? $this->callableParser
            ?? $this->newCallableParser();
    }


    public function the_nil() : Nil
    {
        return new Nil();
    }


    /**
     * @return Ret<mixed>
     */
    public function type_empty($value)
    {
        if (empty($value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be empty', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_empty($value)
    {
        if (! empty($value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be not empty', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * > Специальный тип, который значит, что значение можно отбросить или не учитывать, т.к. оно не несёт информации
     *
     * @return Ret<string|array|\Countable|null>
     */
    public function type_blank($value)
    {
        // > NIL is not blank (NIL is always passed manually, that literally means NOT BLANK)
        // > CLOSED RESOURCE is not blank (actually it's still internal object)

        if (false
            // > NULL is blank (can appear from API to omit any actions on the value)
            || (null === $value)
            //
            // > NAN is blank (can be result of number function calculation or defined as default)
            || (is_float($value) && is_nan($value))
            //
            // > EMPTY STRING is blank (can appear from HTML forms with no input provided)
            || ('' === $value)
            //
            // > EMPTY ARRAY is blank (can appear from HTML forms with no checkbox/radio/select items choosen)
            || ([] === $value)
        ) {
            return Ret::val($value);
        }

        // > COUNTABLE w/ ZERO SIZE is blank
        if ($this->type_countable($value)->isOk([ &$valueCountable ])) {
            if (0 === count($valueCountable)) {
                return Ret::val($value);
            }
        }

        return Ret::err(
            [ 'The `value` should be blank', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_blank($value)
    {
        if (! $this->type_blank($value)->isOk()) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be not blank', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * > Специальный тип, который значит, что значение можно заменить NULL-ом
     *
     * @return Ret<mixed>
     */
    public function type_nullable($value)
    {
        // > NAN is not clearable (NAN means some error in the code and shouldn't be replaced)
        // > EMPTY ARRAY is not clearable (array functions is not applicable to nulls)
        // > COUNTABLE w/ ZERO SIZE is not clearable (countable/iterable functions is not applicable to nulls)

        if (false
            // > NULL is clearable (means nothing)
            || (null === $value)
            //
            // > EMPTY STRING is clearable (can appear from HTML forms with no input provided)
            || ('' === $value)
            //
            // > CLOSED RESOURCE is clearable (this is the internal garbage with no possible purpose)
            || ('resource (closed)' === gettype($value))
            //
            // > NIL is clearable (NIL should be replaced with NULL later or perform deleting actions)
            || Nil::is($value)
        ) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be nullable', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_nullable($value)
    {
        if (! $this->type_nullable($value)->isOk()) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be not nullable', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * > Специальный тип, который значит, что значение было отправлено пользователем, а не появилось из PHP
     *
     * @return Ret<mixed>
     */
    public function type_passed($value)
    {
        if ($this->type_nil($value)->isOk()) {
            return Ret::val($value);
        }

        if (false
            // > NULL is not passed (can appear from API to omit any actions on the value)
            || (null === $value)
            //
            // > EMPTY STRING is not passed (can appear from HTML form with no input provided)
            || ('' === $value)
            //
            // > EMPTY ARRAY is not passed (can appear from HTML forms with no checkbox/radio/select items choosen)
            || ([] === $value)
            //
            // > OBJECTS is not passed (they're only created from source code)
            || is_object($value)
            //
            // > NAN, -INF, INF is not passed (user cannot send NAN, -INF, +INF)
            || (is_float($value) && ! is_finite($value))
            //
            // > RESOURCE not passed (user cannot send resource)
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
        ) {
            return Ret::err(
                [ 'The `value` should be passed by user', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_passed($value)
    {
        if (! $this->type_passed($value)->isOk()) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be not passed by user', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * > Специальный тип-синоним NULL, переданный пользователем через API, например '{N}'
     * > в случаях, когда NULL интерпретируется как "не трогать", а NIL как "очистить"
     *
     * > NAN не равен ничему даже самому себе
     * > NIL равен только самому себе
     * > NULL означает пустоту и им можно заменить значения '', [], `resource (closed)`, NIL, но нельзя заменить NAN
     *
     * @return Ret<string|Nil>
     */
    public function type_nil($value)
    {
        if (Nil::is($value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be nil', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_nil($value)
    {
        if (! Nil::is($value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be not nil', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<null>
     */
    public function type_null($value)
    {
        if (null === $value) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be null', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_null($value)
    {
        if (null !== $value) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be not null', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<false>
     */
    public function type_false($value)
    {
        if (false === $value) {
            return Ret::val(false);
        }

        return Ret::err(
            [ 'The `value` should be false', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_false($value)
    {
        if (false !== $value) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be not false', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<true>
     */
    public function type_true($value)
    {
        if (true === $value) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be true', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_true($value)
    {
        if (true !== $value) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be not true', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<bool>
     */
    public function type_bool($value)
    {
        if (null === $value) {
            return Ret::err(
                [ 'The `value` should be bool, null is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (is_bool($value)) {
            return Ret::val($value);
        }

        if (is_int($value)) {
            return Ret::val(0 !== $value);
        }

        if (is_float($value)) {
            if (is_nan($value)) {
                return Ret::err(
                    [ 'The `value` should be bool, nan is not', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::val(0.0 !== $value);
        }

        if ($this->type_nil($value)->isOk()) {
            return Ret::err(
                [ 'The `value` should be bool, nil is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (is_string($value)) {
            if ('' === $value) {
                // > EMPTY STRING is false
                return Ret::val(false);
            }

            return Ret::val(true);
        }

        if (is_array($value)) {
            if ([] === $value) {
                // > EMPTY ARRAY is false
                return Ret::val(false);
            }

            return Ret::val(true);
        }

        if (is_resource($value)) {
            return Ret::val(true);

        } elseif ('resource (closed)' === gettype($value)) {
            return Ret::val(false);
        }

        if (is_object($value)) {
            if ($this->type_countable($value)->isOk([ &$valueCountable ])) {
                if (0 === count($valueCountable)) {
                    // > EMPTY COUNTABLE is false
                    return Ret::val(false);
                }
            }

            return Ret::val(true);
        }

        return Ret::err(
            [ 'The `value` should be bool', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<false>
     */
    public function type_boolfalse($value)
    {
        if (! $this->type_bool($value)->isOk([ &$valueBool, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if (false === $valueBool) {
            return Ret::val(false);
        }

        return Ret::err(
            [ 'The `value` should be bool, false', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<true>
     */
    public function type_booltrue($value)
    {
        if (! $this->type_bool($value)->isOk([ &$valueBool, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if (true === $valueBool) {
            return Ret::val(true);
        }

        return Ret::err(
            [ 'The `value` should be bool, true', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<bool>
     */
    public function type_userbool($value)
    {
        if (null === $value) {
            return Ret::err(
                [ 'The `value` should be userbool, null is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (is_bool($value)) {
            return Ret::val($value);
        }

        if (is_int($value)) {
            return Ret::val(0 !== $value);
        }

        if (is_float($value)) {
            if (is_nan($value)) {
                return Ret::err(
                    [ 'The `value` should be userbool, nan is not', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::val(0.0 !== $value);
        }

        if (is_string($value)) {
            $map = [
                //
                "true"  => true,
                'y'     => true,
                'yes'   => true,
                'on'    => true,
                '1'     => true,
                //
                "false" => false,
                'n'     => false,
                'no'    => false,
                'off'   => false,
                '0'     => false,
            ];

            $valueLower = strtolower($value);

            if (isset($map[ $valueLower ])) {
                return Ret::val($map[ $valueLower ]);
            }
        }

        return Ret::err(
            [ 'The `value` should be userbool', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<false>
     */
    public function type_userfalse($value)
    {
        if (! $this->type_userbool($value)->isOk([ &$valueUserbool, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if (false === $valueUserbool) {
            return Ret::val(false);
        }

        return Ret::err(
            [ 'The `value` should be userbool, false', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<false>
     */
    public function type_usertrue($value)
    {
        if (! $this->type_userbool($value)->isOk([ &$valueUserbool, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if (true === $valueUserbool) {
            return Ret::val(true);
        }

        return Ret::err(
            [ 'The `value` should be userbool, true', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<array>
     */
    public function type_array($value)
    {
        if (is_array($value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be array', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<array>
     */
    public function type_array_empty($value)
    {
        if ([] === $value) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be array, empty', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<array>
     */
    public function type_array_not_empty($value)
    {
        if (is_array($value) && ([] !== $value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be array, not empty', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_array_empty($value)
    {
        if ([] !== $value) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should not be empty array', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_array($value)
    {
        if (! is_array($value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should not be array', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<object>
     */
    public function type_object($value)
    {
        if (is_object($value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be object', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_object($value)
    {
        if (! is_object($value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should not be empty array', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<\stdClass>
     */
    public function type_stdclass($value)
    {
        if ($value instanceof \stdClass) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be instance of \stdClass', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_stdclass($value)
    {
        if (! ($value instanceof \stdClass)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should not be instance of \stdClass', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<array|\Countable>
     */
    public function type_countable($value)
    {
        if (PHP_VERSION_ID >= 70300) {
            if (is_countable($value)) {
                return Ret::val($value);
            }

            return Ret::err(
                [ 'The `value` should be countable', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (is_array($value)) {
            return Ret::val($value);
        }

        if ($value instanceof \Countable) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be countable', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<\Countable>
     */
    public function type_countable_object($value)
    {
        if (! is_object($value)) {
            return Ret::err(
                [ 'The `value` should be object', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (PHP_VERSION_ID >= 70300) {
            if (! is_countable($value)) {
                return Ret::err(
                    [ 'The `value` should be countable object', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::val($value);
        }

        if ($value instanceof \Countable) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be countable object', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<array|\Countable>
     */
    public function type_sizeable($value)
    {
        $theStr = Lib::str();

        if ($this->type_countable($value)->isOk()) {
            return Ret::val($value);
        }

        if ($theStr->type_string($value)->isOk()) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be sizeable', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>
     */
    public function type_struct_exists($value, ?int $flags = null)
    {
        $flags = $flags ?? _PHP_STRUCT_TYPE_ALL;

        $theType = Lib::type();

        $isObject = is_object($value);

        if ($isObject) {
            $class = get_class($value);

        } elseif ($theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty ])) {
            $class = ltrim($valueStringNotEmpty, '\\');

            if ('' === $class) {
                return Ret::err(
                    [ 'The `value` should be valid class', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

        } else {
            return Ret::err(
                [ 'The `value` should be existing class or object', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('__PHP_Incomplete_Class' === $class) {
            return Ret::err(
                [ 'The `value` should be existing class or object', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ($flags & _PHP_STRUCT_TYPE_CLASS) {
            if (PHP_VERSION_ID >= 80100) {
                if (class_exists($class) && ! enum_exists($class)) {
                    return Ret::val($class);
                }

            } else {
                if (class_exists($class)) {
                    return Ret::val($class);
                }
            }
        }

        if ($flags & _PHP_STRUCT_TYPE_ENUM) {
            if (PHP_VERSION_ID >= 80100) {
                if (enum_exists($class)) {
                    return Ret::val($class);
                }
            }
        }

        if (! $isObject) {
            if ($flags & _PHP_STRUCT_TYPE_INTERFACE) {
                if (interface_exists($class)) {
                    return Ret::val($class);
                }
            }

            if ($flags & _PHP_STRUCT_TYPE_TRAIT) {
                if (trait_exists($class)) {
                    return Ret::val($class);
                }
            }
        }

        return Ret::err(
            [ 'The `value` should be struct, existing', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>
     */
    public function type_struct($value, ?int $flags = null)
    {
        $theType = Lib::type();

        $flagsInt = $flags ?? (_PHP_STRUCT_TYPE_ALL | _PHP_STRUCT_EXISTS_TRUE);

        $flagGroups = [
            '_PHP_STRUCT_EXISTS' => [
                [
                    _PHP_STRUCT_EXISTS_TRUE,
                    _PHP_STRUCT_EXISTS_FALSE,
                    _PHP_STRUCT_EXISTS_IGNORE,
                ],
                _PHP_STRUCT_EXISTS_TRUE,
            ],
        ];

        foreach ( $flagGroups as $groupName => [ $conflict, $default ] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ($flagsInt & $flag) {
                    $cnt++;
                }
            }

            if ($cnt > 1) {
                return Ret::err(
                    [ 'The `flags` conflict in group: ' . $groupName, $flags ],
                    [ __FILE__, __LINE__ ]
                );

            } elseif (0 === $cnt) {
                $flagsInt |= $default;
            }
        }

        $isFlagExistsTrue = (bool) ($flagsInt & _PHP_STRUCT_EXISTS_TRUE);
        $isFlagExistsFalse = (bool) ($flagsInt & _PHP_STRUCT_EXISTS_FALSE);
        $isFlagExistsIgnore = (bool) ($flagsInt & _PHP_STRUCT_EXISTS_IGNORE);

        $isExists = null;

        if (is_object($value)) {
            $class = get_class($value);

            $isEnum = is_a($value, '\UnitEnum');
            $isClass = ! $isEnum;

            if ($isEnum && ($flagsInt & _PHP_STRUCT_TYPE_ENUM)) {
                $isExists = true;

            } elseif ($isClass && ($flagsInt & _PHP_STRUCT_TYPE_CLASS)) {
                $isExists = true;
            }

        } else {
            if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $class = ltrim($valueStringNotEmpty, '\\');

            if ('' === $class) {
                return Ret::err(
                    [ 'The `value` should be valid class', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        if ('__PHP_Incomplete_Class' === $class) {
            return Ret::err(
                [ 'The `value` should be existing class or object', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ($isFlagExistsTrue || $isFlagExistsFalse) {
            $isExists = null
                ?? $isExists
                ?? $this->type_struct_exists($class, $flagsInt)->isOk([ &$classString ]);

            if ($isExists && $isFlagExistsFalse) {
                return Ret::err(
                    [ 'The `value` should be non-existing struct', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ((! $isExists) && $isFlagExistsTrue) {
                return Ret::err(
                    [ 'The `value` should be existing struct', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ($isExists && $isFlagExistsTrue) {
                return Ret::val($class);
            }
        }

        if ($isFlagExistsFalse || $isFlagExistsIgnore) {
            $isValid = preg_match(
                '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/',
                $class
            );

            // $isValid = (! ((false === $isValid) || (0 === $isValid)));
            $isValid = (bool) $isValid;

            if ($isValid) {
                return Ret::val($class);
            }
        }

        return Ret::err(
            [ 'The `value` should be struct', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>
     */
    public function type_struct_class($value, ?int $flags = null)
    {
        $flagsInt = $flags;

        if (null === $flagsInt) {
            $flagsInt = (0
                | _PHP_STRUCT_TYPE_CLASS
                | _PHP_STRUCT_EXISTS_TRUE
            );

        } else {
            $flagsInt &= ~_PHP_STRUCT_TYPE_ALL;
            $flagsInt |= _PHP_STRUCT_TYPE_CLASS;
        }

        if (! $this->type_struct($value, $flagsInt)->isOk([ &$struct, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($struct);
    }

    /**
     * @return Ret<class-string>
     */
    public function type_struct_interface($value, ?int $flags = null)
    {
        $flagsInt = $flags;

        if (null === $flagsInt) {
            $flagsInt = (0
                | _PHP_STRUCT_TYPE_INTERFACE
                | _PHP_STRUCT_EXISTS_TRUE
            );

        } else {
            $flagsInt &= ~_PHP_STRUCT_TYPE_ALL;
            $flagsInt |= _PHP_STRUCT_TYPE_INTERFACE;
        }

        if (! $this->type_struct($value, $flagsInt)->isOk([ &$struct, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($struct);
    }

    /**
     * @return Ret<class-string>
     */
    public function type_struct_trait($value, ?int $flags = null)
    {
        $flagsInt = $flags ?? (0
            | _PHP_STRUCT_TYPE_TRAIT
            | _PHP_STRUCT_EXISTS_TRUE
        );

        if (null !== $flagsInt) {
            $flagsInt &= ~_PHP_STRUCT_TYPE_ALL;
            $flagsInt |= _PHP_STRUCT_TYPE_TRAIT;
        }

        if (! $this->type_struct($value, $flagsInt)->isOk([ &$struct, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($struct);
    }

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>
     */
    public function type_struct_enum($value, ?int $flags = null)
    {
        $flagsInt = $flags;

        if (null === $flagsInt) {
            $flagsInt = (0
                | _PHP_STRUCT_TYPE_ENUM
                | _PHP_STRUCT_EXISTS_TRUE
            );

        } else {
            $flagsInt &= ~_PHP_STRUCT_TYPE_ALL;
            $flagsInt |= _PHP_STRUCT_TYPE_ENUM;
        }

        if (! $this->type_struct($value, $flagsInt)->isOk([ &$struct, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($struct);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>
     */
    public function type_struct_fqcn($value, ?int $flags = null)
    {
        if (! $this->type_struct($value, $flags)->isOk([ &$valueStruct, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueStruct = '\\' . $valueStruct;

        return Ret::val($valueStruct);
    }

    /**
     * @return Ret<string>
     */
    public function type_struct_namespace($value, ?int $flags = null)
    {
        if (! $this->type_struct($value, $flags)->isOk([ &$valueStruct, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueNamespace = $this->dirname($valueStruct, '\\');

        if (null === $valueNamespace) {
            return Ret::err(
                [ 'The `value` should be struct namespace', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueNamespace);
    }

    /**
     * @return Ret<string>
     */
    public function type_struct_basename($value, ?int $flags = null)
    {
        if (! $this->type_struct($value, $flags)->isOk([ &$valueStruct, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueBasename = $this->basename($valueStruct, '\\');

        if (null === $valueBasename) {
            return Ret::err(
                [ 'The `value` should be struct basename', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueBasename);
    }


    /**
     * @return Ret<resource>
     */
    public function type_resource($value, ?string $resourceType = null)
    {
        if (is_resource($value)) {
            if (null === $resourceType) {
                return Ret::val($value);

            } else {
                if ($resourceType === get_resource_type($value)) {
                    return Ret::val($value);
                }
            }
        }

        if ('resource (closed)' === gettype($value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be resource, opened or closed' ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<resource>
     */
    public function type_resource_opened($value, ?string $resourceType = null)
    {
        if (is_resource($value)) {
            if (null === $resourceType) {
                return Ret::val($value);

            } else {
                if ($resourceType === get_resource_type($value)) {
                    return Ret::val($value);
                }
            }
        }

        return Ret::err(
            [ 'The `value` should be resource, opened' ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<resource>
     */
    public function type_resource_closed($value)
    {
        if ('resource (closed)' === gettype($value)) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be resource, closed' ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<resource>
     */
    public function type_any_not_resource($value)
    {
        if (! (false
            || is_resource($value)
            || ('resource (closed)' === gettype($value))
        )) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be any not opened or closed resource' ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<resource|\CurlHandle>
     */
    public function type_curl($value)
    {
        if (false
            || is_a($value, '\CurlHandle')
            || $this->type_resource_opened($value, 'curl')->isOk()
        ) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be curl resource, opened' ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @template-covariant T of \UnitEnum
     *
     * @param T|int|string         $value
     * @param class-string<T>|null $enumClass
     *
     * @return Ret<T>
     */
    public function type_enum_case($value, ?string $enumClass = null)
    {
        $hasEnumClass = false;
        if (null !== $enumClass) {
            if (! is_subclass_of($enumClass, '\UnitEnum')) {
                return Ret::err(
                    [ 'The `enumClass` should extend \UnitEnum', $enumClass ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $hasEnumClass = true;
        }

        if (is_object($value)) {
            $status = $hasEnumClass
                ? is_a($value, $enumClass)
                : is_subclass_of($value, '\UnitEnum');

            if ($status) {
                return Ret::val($value);
            }
        }

        if (! $hasEnumClass) {
            return Ret::err(
                [ 'Cannot obtain `enumClass` from given data', $value, $enumClass ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! (false
            || is_int($value)
            || is_string($value)
        )) {
            return Ret::err(
                [ 'The `value` should be int or string, cause Enums only support two types', $value, $enumClass ],
                [ __FILE__, __LINE__ ]
            );
        }

        $enumCase = null;
        try {
            $enumCase = $enumClass::tryFrom($value);
        }
        catch ( \Throwable $e ) {
        }

        if (null !== $enumCase) {
            return Ret::val($enumCase);
        }

        return Ret::err(
            [ 'The `value` should be enum case', $value, $enumClass ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * > метод не всегда callable, поскольку строка 'class->method' не является callable
     * > метод не всегда callable, поскольку массив [ 'class', 'method' ] не является callable, если метод публичный
     * > используйте type_callable_string, если собираетесь вызывать метод
     * > используйте type_callable_array, если собираетесь вызывать метод
     *
     * @param array{ 0?: array{ 0: class-string, 1: string }, 1?: string } $refs
     *
     * @return Ret<bool>
     */
    public function type_method($value, array $refs = [])
    {
        return $this->callableParser()->typeMethod($value, $refs);
    }

    /**
     * > метод не всегда callable, поскольку строка 'class->method' не является callable
     * > метод не всегда callable, поскольку массив [ 'class', 'method' ] не является callable, если метод публичный
     * > используйте type_callable_string, если собираетесь вызывать метод
     * > используйте type_callable_array, если собираетесь вызывать метод
     *
     * @return Ret<array{ 0: class-string, 1: string }>
     */
    public function type_method_array($value)
    {
        return $this->callableParser()->typeMethodArray($value);
    }

    /**
     * > метод не всегда callable, поскольку строка 'class->method' не является callable
     * > метод не всегда callable, поскольку массив [ 'class', 'method' ] не является callable, если метод публичный
     * > используйте type_callable_string, если собираетесь вызывать метод
     * > используйте type_callable_array, если собираетесь вызывать метод
     *
     * @return Ret<string>
     */
    public function type_method_string($value)
    {
        return $this->callableParser()->typeMethodString($value);
    }


    /**
     * > в версиях PHP до 8.0.0 публичный метод считался callable, если его проверить даже на имени класса
     * > при этом вызвать MyClass::publicMethod было нельзя, т.к. вызываемым является только MyClass::publicStaticMethod
     *
     * @param string|object $newScope
     *
     * @return Ret<callable>
     */
    public function type_callable($value, $newScope = 'static')
    {
        return $this->callableParser()->typeCallable($value, $newScope);
    }


    /**
     * @return Ret<callable|\Closure|object>
     */
    public function type_callable_object($value, $newScope = 'static')
    {
        return $this->callableParser()->typeCallableObject($value, $newScope);
    }

    /**
     * @return Ret<\Closure>
     */
    public function type_callable_object_closure($value, $newScope = 'static')
    {
        return $this->callableParser()->typeCallableObjectClosure($value, $newScope);
    }

    /**
     * @return Ret<callable|object>
     */
    public function type_callable_object_invokable($value, $newScope = 'static')
    {
        return $this->callableParser()->typeCallableObjectInvokable($value, $newScope);
    }


    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>
     */
    public function type_callable_array($value, $newScope = 'static')
    {
        return $this->callableParser()->typeCallableArray($value, $newScope);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>
     */
    public function type_callable_array_method($value, $newScope = 'static')
    {
        return $this->callableParser()->typeCallableArrayMethod($value, $newScope);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: class-string, 1: string }>
     */
    public function type_callable_array_method_static($value, $newScope = 'static')
    {
        return $this->callableParser()->typeCallableArrayMethodStatic($value, $newScope);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object, 1: string }>
     */
    public function type_callable_array_method_non_static($value, $newScope = 'static')
    {
        return $this->callableParser()->typeCallableArrayMethodNonStatic($value, $newScope);
    }


    /**
     * @return Ret<callable|callable-string>
     */
    public function type_callable_string($value, $newScope = 'static')
    {
        return $this->callableParser()->typeCallableString($value, $newScope);
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function type_callable_string_function($value)
    {
        return $this->callableParser()->typeCallableStringFunction($value);
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function type_callable_string_function_internal($value)
    {
        return $this->callableParser()->typeCallableStringFunctionInternal($value);
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function type_callable_string_function_non_internal($value)
    {
        return $this->callableParser()->typeCallableStringFunctionNonInternal($value);
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function type_callable_string_method_static($value, $newScope = 'static')
    {
        return $this->callableParser()->typeCallableStringMethodStatic($value, $newScope);
    }


    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function type_path(
        $value,
        array $refs = []
    )
    {
        $theType = Lib::type();

        $withPathInfo = array_key_exists(0, $refs);
        if ($withPathInfo) {
            $refPathInfo =& $refs[ 0 ];
        }
        $refPathInfo = null;

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ($withPathInfo) {
            try {
                $refPathInfo = $this->pathinfo($valueStringNotEmpty);
            }
            catch ( \Throwable $e ) {
                return Ret::err(
                    [ 'The `value` should be valid path', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::val($valueStringNotEmpty);
    }

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function type_path_normalized(
        $value, ?string $separator = null,
        array $refs = []
    )
    {
        $theType = Lib::type();

        $withPathInfo = array_key_exists(0, $refs);
        if ($withPathInfo) {
            $refPathInfo =& $refs[ 0 ];
        }
        $refPathInfo = null;

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $pathNormalized = $this->path_normalize($valueStringNotEmpty, $separator);
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        if ($withPathInfo) {
            try {
                $refPathInfo = $this->pathinfo($pathNormalized);
            }
            catch ( \Throwable $e ) {
                return Ret::err(
                    $e,
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::val($valueStringNotEmpty);
    }


    public function is_windows() : bool
    {
        static $current;

        return $current = $current ?? ('WIN' === strtoupper(substr(PHP_OS, 0, 3)));
    }

    public function is_terminal() : bool
    {
        static $current;

        return $current = $current ?? in_array(\PHP_SAPI, [ 'cli', 'phpdbg' ]);
    }


    /**
     * @return resource
     */
    public function hInput()
    {
        if (! defined('PHPIN')) define('PHPIN', fopen('php://input', 'rb'));

        return PHPIN;
    }

    /**
     * @return resource
     */
    public function hOutput()
    {
        if (! defined('PHPOUT')) define('PHPOUT', fopen('php://output', 'wb'));

        return PHPOUT;
    }


    public function to_bool($value, array $options = []) : bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value instanceof ToBoolInterface) {
            return $value->toBool($options);
        }

        $theType = Lib::type();

        $valueBool = $theType->bool($value)->orThrow();

        return $valueBool;
    }

    public function to_int($value, array $options = []) : int
    {
        if (is_int($value)) {
            return $value;
        }

        if ($value instanceof ToIntegerInterface) {
            return $value->toInteger($options);
        }

        $theType = Lib::type();

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to integer',
                    $value,
                ]
            );
        }

        $valueInt = $theType->int($value)->orThrow();

        return $valueInt;
    }

    public function to_float($value, array $options = []) : float
    {
        if (is_float($value)) {
            if (! is_finite($value)) {
                throw new LogicException(
                    [
                        'Unable to parse value while converting to float',
                        $value,
                    ]
                );
            }

            if (-0.0 === $value) {
                return 0.0;
            }

            return $value;
        }

        if ($value instanceof ToFloatInterface) {
            return $value->toFloat($options);
        }

        $theType = Lib::type();

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            // || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to float',
                    $value,
                ]
            );
        }

        $valueFloat = $theType->float($value)->orThrow();

        return $valueFloat;
    }

    public function to_string($value, array $options = []) : string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof ToStringInterface) {
            return $value->toString($options);
        }

        $theType = Lib::type();

        if (false
            || (null === $value)
            // || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to string',
                    $value,
                ]
            );
        }

        $valueString = $theType->string($value)->orThrow();

        return $valueString;
    }


    public function to_array($value, array $options = []) : array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            if ($value instanceof ToArrayInterface) {
                return $value->toArray($options);
            }

            if ($value instanceof ToObjectInterface) {
                return (array) $value->toObject($options);
            }

            $isStdClass = (get_class($value) === \stdClass::class);

            if (! $isStdClass) {
                throw new LogicException(
                    [
                        'The `value` (if object) should be an instance of: ' . \stdClass::class,
                        $value,
                    ]
                );
            }
        }

        if (false
            || (null === $value)
            // || ('' === $value)
            // || (is_bool($value))
            // || (is_array($value))
            || (is_float($value) && (! is_nan($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to array',
                    $value,
                ]
            );
        }

        $valueArray = (array) $value;

        return $valueArray;
    }

    public function to_object($value, array $options = []) : \stdClass
    {
        if (is_object($value)) {
            if ($value instanceof ToObjectInterface) {
                return $value->toObject($options);
            }

            if ($value instanceof ToArrayInterface) {
                return (object) $value->toArray($options);
            }

            $isStdClass = (get_class($value) === \stdClass::class);

            if (! $isStdClass) {
                throw new LogicException(
                    [
                        'The `value` (if object) should be an instance of: ' . \stdClass::class,
                        $value,
                    ]
                );
            }

            return $value;
        }

        if (false
            || (null === $value)
            // || ('' === $value)
            // || (is_bool($value))
            // || (is_array($value))
            || (is_float($value) && (! is_nan($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            throw new LogicException(
                [
                    'Unable to parse value while converting to string',
                    $value,
                ]
            );
        }

        $valueStdClass = (object) (array) $value;

        return $valueStdClass;
    }

    public function to_iterable($value, array $options = []) : iterable
    {
        if (null === $value) {
            return [];
        }

        if (is_object($value)) {
            if ($value instanceof ToIterableInterface) {
                return $value->toIterable($options);
            }

            if ($value instanceof \Traversable) {
                return $value;
            }
        }

        if (is_array($value)) {
            return $value;
        }

        return [ $value ];
    }


    /**
     * @param callable $fnAssert
     */
    public function to_list(
        $value, array $options = [],
        $fnAssert = null, array $fnAssertArgs = [], $fnAssertValueKey = 0
    ) : array
    {
        if (null === $value) {
            return [];
        }

        $theFunc = Lib::func();
        $theType = Lib::type();

        $hasAssert = (null !== $fnAssert);

        $fnArgs = [];
        if ($hasAssert) {
            [ $fnArgs ] = $theFunc->func_args_unique([ $fnAssertValueKey => null ], $fnAssertArgs);
        }

        $listValid = null;

        if ($value instanceof ToListInterface) {
            $list = $value->toList($options);

        } elseif (is_array($value)) {
            if ($hasAssert) {
                $fnArgs[ $fnAssertValueKey ] = $value;

                $status = (bool) call_user_func_array($fnAssert, $fnArgs);

                if ($status) {
                    $listValid = [ $value ];
                }
            }

            if (null === $listValid) {
                if ($theType->list($value)->isOk([ &$valueList ])) {
                    $list = $valueList;

                } else {
                    $list = [ $value ];
                }
            }

        } else {
            $list = [ $value ];
        }

        if (null !== $listValid) {
            return $listValid;
        }

        if ([] !== $list) {
            if ($hasAssert) {
                foreach ( $list as $i => $v ) {
                    $fnArgs[ $fnAssertValueKey ] = $v;

                    $status = (bool) call_user_func_array($fnAssert, $fnArgs);

                    if (! $status) {
                        throw new LogicException(
                            [ 'Each of `value` (if array) should pass `fnAssert` check', $v, $i ]
                        );
                    }
                }
            }
        }

        return $list;
    }

    public function to_list_it($value, array $options = []) : \Generator
    {
        if (null === $value) {
            return true;
        }

        $theType = Lib::type();

        if ($value instanceof ToListInterface) {
            $list = $value->toList($options);

            foreach ( $list as $v ) {
                yield $v;
            }

        } elseif (is_array($value)) {
            yield $value;

            if ($theType->list($value)->isOk([ &$valueList ])) {
                foreach ( $valueList as $v ) {
                    yield $v;
                }
            }

        } else {
            yield $value;
        }

        return true;
    }


    /**
     * @return int|float
     */
    public function count($value) // : int|NAN
    {
        if ($this->type_countable($value)->isOk([ &$valueCountable ])) {
            return count($valueCountable);
        }

        return NAN;
    }

    /**
     * @return int|float
     */
    public function size($value) // : int|NAN
    {
        $theType = Lib::type();

        if ($this->type_countable($value)->isOk([ &$valueCountable ])) {
            return count($valueCountable);
        }

        if ($theType->string($value)->isOk([ &$valueString ])) {
            return strlen($valueString);
        }

        return NAN;
    }

    /**
     * @return int|float
     */
    public function length($value) // : int|NAN
    {
        $theStr = Lib::str();
        $theType = Lib::type();

        if ($this->type_countable($value)->isOk([ &$valueCountable ])) {
            return count($valueCountable);
        }

        if ($theType->string($value)->isOk([ &$valueString ])) {
            return $theStr->strlen($valueString);
        }

        return NAN;
    }


    /**
     * @return array{
     *     internal: array<string, bool>,
     *     user: array<string, bool>,
     * }
     */
    public function get_defined_functions() : array
    {
        $getDefinedFunctions = get_defined_functions();

        $flipInternal = array_fill_keys($getDefinedFunctions[ 'internal' ] ?? [], true);
        $flipUser = array_fill_keys($getDefinedFunctions[ 'user' ] ?? [], true);

        ksort($flipInternal);
        ksort($flipUser);

        $result = [];
        $result[ 'internal' ] += $flipInternal;
        $result[ 'user' ] += $flipUser;

        return $result;
    }

    /**
     * @param object|class-string $objectOrClass
     *
     * @return class-string[]
     */
    public function class_uses($objectOrClass, ?bool $isRecursive = null)
    {
        $isRecursive = $isRecursive ?? false;

        $className = $objectOrClass;
        if (is_object($objectOrClass)) {
            $className = get_class($objectOrClass);
        }

        $uses = class_uses($className) ?: [];

        if ($isRecursive) {
            foreach ( $uses as $usesItem ) {
                // > ! recursion
                $uses += $this->class_uses($usesItem);
            }
        }

        return $uses;
    }

    /**
     * @param object|class-string $objectOrClass
     *
     * @return class-string[]
     */
    public function class_uses_with_parents($objectOrClass, ?bool $recursive = null)
    {
        $recursive = $recursive ?? false;

        $className = $objectOrClass;
        if (is_object($objectOrClass)) {
            $className = get_class($objectOrClass);
        }

        $uses = [];

        $sources = []
            + array_reverse(class_parents($className))
            + [ $className => $className ];

        foreach ( $sources as $sourceClassName ) {
            $uses += $this->class_uses($sourceClassName, $recursive);
        }

        $uses = array_unique($uses);

        return $uses;
    }


    /**
     * > функция property_exists() возвращает true для любых свойств, в том числе protected/private и вне зависимости от static
     * > эта используется, чтобы проверить публичные и/или статические свойства
     *
     * @param class-string|object $object_or_class
     */
    public function property_exists(
        $object_or_class, string $property,
        ?bool $public = null, ?bool $static = null
    ) : bool
    {
        $isObject = false;
        $isClass = false;
        if (! (false
            || ($isObject = (is_object($object_or_class)))
            || ($isClass = (is_string($object_or_class) && class_exists($object_or_class)))
        )) {
            return false;
        }

        $anObject = null;
        $aClass = null;
        if ($isObject) {
            $anObject = $object_or_class;
            $aClass = get_class($object_or_class);

        } elseif ($isClass) {
            $aClass = $object_or_class;
        }

        $isPublic = $public === true;
        $isNotPublic = $public === false;
        $isMaybePublic = ! $isNotPublic;

        $isStatic = $static === true;
        $isNotStatic = $static === false;
        $isMaybeStatic = ! $isNotStatic;
        $isNotStaticOrDoesntMatter = ! $isStatic;

        if ($isMaybePublic) {
            if ($isMaybeStatic) {
                if (isset($object_or_class::${$property})) {
                    return true;
                }
            }

            if ($anObject) {
                if ($isNotStaticOrDoesntMatter) {
                    if (isset($anObject->{$property})) {
                        return true;
                    }

                    $vars = get_object_vars($anObject);
                    if ($vars) {
                        if (array_key_exists($property, $vars)) {
                            return true;
                        }
                    }
                }
            }
        }

        if (! property_exists($object_or_class, $property)) {
            return false;
        }

        $isMattersPublic = $public !== null;
        $isMattersStatic = $static !== null;

        if (! $isMattersPublic && ! $isMattersStatic) {
            return true;
        }

        try {
            $rp = new \ReflectionProperty($aClass, $property);

            $isPublicProp = $rp->isPublic();
            $isStaticProp = $rp->isStatic();

            if (! $isPublicProp && $isPublic) {
                return false;
            }

            if (! $isStaticProp && $isStatic) {
                return false;
            }

            if ($isPublicProp && $isNotPublic) {
                return false;
            }

            if ($isStaticProp && $isNotStatic) {
                return false;
            }
        }
        catch ( \Throwable $e ) {
            return false;
        }

        return true;
    }

    /**
     * > функция method_exists() возвращает true для любых методов, в том числе protected/private и вне зависимости от static
     * > эта используется, чтобы проверить публичные и/или статические методы
     *
     * @param class-string|object $object_or_class
     */
    public function method_exists(
        $object_or_class, string $method,
        ?bool $public = null, ?bool $static = null
    ) : bool
    {
        $isObject = false;
        $isClass = false;
        if (! (false
            || ($isObject = (is_object($object_or_class)))
            || ($isClass = (is_string($object_or_class) && class_exists($object_or_class)))
        )) {
            return false;
        }

        $aClass = null;
        if ($isObject) {
            $aClass = get_class($object_or_class);

        } elseif ($isClass) {
            $aClass = $object_or_class;
        }

        if (! method_exists($object_or_class, $method)) {
            return false;
        }

        $isMattersPublic = $public !== null;
        $isMattersStatic = $static !== null;

        if (! $isMattersPublic && ! $isMattersStatic) {
            return true;
        }

        $isPublic = $public === true;
        $isStatic = $static === true;
        $isNotPublic = $public === false;
        $isNotStatic = $static === false;

        try {
            $rm = new \ReflectionMethod($aClass, $method);

            $isPublicMethod = $rm->isPublic();
            $isStaticMethod = $rm->isStatic();

            if (! $isPublicMethod && $isPublic) {
                return false;
            }

            if (! $isStaticMethod && $isStatic) {
                return false;
            }

            if ($isPublicMethod && $isNotPublic) {
                return false;
            }

            if ($isStaticMethod && $isNotStatic) {
                return false;
            }
        }
        catch ( \Throwable $e ) {
            return false;
        }

        return true;
    }


    /**
     * > функция get_object_vars() возвращает только публичные свойства для $this
     * > чтобы получить доступ ко всем свойствам, её нужно вызвать в обертке
     *
     * @param string|object $newScope
     */
    public function get_object_vars(object $object, $newScope = 'static') : array
    {
        if ('static' === $newScope) {
            // > if you need `static` scope you may call the existing php function
            throw new RuntimeException(
                'You should pass constant __CLASS__ to second argument to keep scope `static`'
            );
        }

        $fnGetObjectVars = null;
        if (null !== $newScope) {
            $fnGetObjectVars = (static function ($object) {
                return get_object_vars($object);
            })->bindTo(null, $newScope);
        }

        $vars = $fnGetObjectVars
            ? $fnGetObjectVars($object)
            : get_object_vars($object);

        return $vars;
    }

    /**
     * > функция get_class_vars() возвращает только публичные (и статические публичные) свойства для $object_or_class
     * > чтобы получить доступ ко всем свойствам, её нужно вызвать в обертке
     *
     * @param string|object $newScope
     */
    public function get_class_vars($object_or_class, $newScope = 'static') : array
    {
        if ('static' === $newScope) {
            // > if you need `static` scope you may call the existing php function
            throw new RuntimeException(
                'You should pass constant __CLASS__ to second argument to keep scope `static`'
            );
        }

        $fnGetClassVars = null;
        if (null !== $newScope) {
            $fnGetClassVars = (static function ($class) {
                return get_class_vars($class);
            })->bindTo(null, $newScope);
        }

        $class = is_object($object_or_class)
            ? get_class($object_or_class)
            : $object_or_class;

        $vars = $fnGetClassVars
            ? $fnGetClassVars($class)
            : get_class_vars($class);

        return $vars;
    }

    /**
     * > функция get_class_methods() возвращает только публичные (и статические публичные) методы для $object_or_class
     * > чтобы получить доступ ко всем методам, её нужно вызвать в обертке
     *
     * @param string|object $newScope
     */
    public function get_class_methods($object_or_class, $newScope = 'static') : array
    {
        if ('static' === $newScope) {
            // > if you need `static` scope you may call the existing php function
            throw new RuntimeException(
                'You should pass constant __CLASS__ to second argument to keep scope `static`'
            );
        }

        $fnGetClassMethods = null;
        if (null !== $newScope) {
            $fnGetClassMethods = (static function ($object_or_class) {
                return get_class_methods($object_or_class);
            })->bindTo(null, $newScope);
        }

        $vars = $fnGetClassMethods
            ? $fnGetClassMethods($object_or_class)
            : get_class_vars($object_or_class);

        return $vars;
    }


    /**
     * > is_callable является контекстно-зависимой функцией
     * > будучи вызванной снаружи класса она не покажет методы protected/private
     * > если её вызвать в обертке с указанием $newScope - это сработает
     *
     * @param string|object $newScope
     */
    public function is_callable($value, $newScope = 'static') : bool
    {
        if ('static' === $newScope) {
            // > if you need `static` scope you may call the existing php function
            throw new RuntimeException(
                'You should pass constant __CLASS__ to second argument to keep scope `static`'
            );
        }

        $fnIsCallable = null;
        if (null !== $newScope) {
            $fnIsCallable = (static function ($callable) {
                return is_callable($callable);
            })->bindTo(null, $newScope);
        }

        $status = $fnIsCallable
            ? $fnIsCallable($value)
            : is_callable($value);

        if ($status) {
            return true;
        }

        return false;
    }


    /**
     * > во встроенной функции pathinfo() для двойного расширения возвращается только последнее, `image.min.jpg` -> 'jpg`
     * > + поддерживает предварительную замену $separator на '/'
     * > + при указанных значениях возвращает все ключи, то есть отсутствие расширения при требовании его вернуть - ключ будет и будет NULL
     *
     * @return array{
     *     dirname?: string|null,
     *     basename?: string|null,
     *     filename?: string|null,
     *     extension?: string|null,
     *     file?: string|null,
     *     extensions?: string|null,
     * }
     */
    public function pathinfo(
        string $path, ?string $separator = null, ?string $dot = null,
        ?int $flags = null
    ) : array
    {
        $flags = $flags ?? _PHP_PATHINFO_ALL;

        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();
        $separatorString = $theType->char($separator ?? '/')->orThrow();
        $dotString = $theType->char($dot ?? '.')->orThrow();

        if ('/' === $dotString) {
            throw new LogicException(
                [ 'The `dot` should not be a `/` sign' ]
            );
        }

        $normalized = $this->path_normalize($pathStringNotEmpty, '/');

        $dirname = ltrim($normalized, '/');
        $basename = basename($normalized);

        $pi = [];

        if ($flags & PATHINFO_DIRNAME) {
            if (false === strpos($dirname, '/')) {
                $dirname = null;

            } else {
                $dirname = dirname($dirname);

                $dirname = str_replace('/', $separatorString, $dirname);

                $dirname = ('.' !== $dirname) ? $dirname : null;
            }

            $pi[ 'dirname' ] = $dirname;
        }

        if ($flags & _PHP_PATHINFO_BASENAME) {
            $pi[ 'basename' ] = ('' !== $basename) ? $basename : null;
        }

        if (false
            || ($flags & _PHP_PATHINFO_FILENAME)
            || ($flags & _PHP_PATHINFO_EXTENSION)
            || ($flags & _PHP_PATHINFO_FILE)
            || ($flags & _PHP_PATHINFO_EXTENSIONS)
        ) {
            $filename = $basename;

            $split = explode($dotString, $basename) + [ '', '' ];

            $file = array_shift($split);

            if ($flags & _PHP_PATHINFO_EXTENSION) {
                $extension = end($split);

                if ('' === $extension) {
                    $pi[ 'extension' ] = null;

                } else {
                    $pi[ 'extension' ] = $extension;

                    $filename = basename($basename, "{$dotString}{$extension}");
                }
            }

            if ($flags & _PHP_PATHINFO_FILENAME) {
                $pi[ 'filename' ] = ('' !== $filename) ? $filename : null;
            }

            if ($flags & _PHP_PATHINFO_FILE) {
                $pi[ 'file' ] = ('' !== $file) ? $file : null;
            }

            if ($flags & _PHP_PATHINFO_EXTENSIONS) {
                $extensions = null;
                if ([] !== $split) {
                    $extensions = implode($dotString, $split);
                }

                $pi[ 'extensions' ] = $extensions;
            }
        }

        return $pi;
    }

    public function dirname(
        string $path, ?string $separator = null,
        ?int $levels = null
    ) : ?string
    {
        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();
        $separatorChar = $theType->char($separator ?? '/')->orThrow();
        $levelsInt = $theType->int_positive($levels ?? 1)->orThrow();

        $normalized = $this->path_normalize($pathStringNotEmpty, '/');

        $dirname = ltrim($normalized, '/');

        if (false === strpos($dirname, '/')) {
            $dirname = null;

        } else {
            $dirname = dirname($dirname, $levelsInt);

            $dirname = str_replace('/', $separatorChar, $dirname);
        }

        return ('.' !== $dirname) ? $dirname : null;
    }

    public function basename(string $path, ?string $extension = null) : ?string
    {
        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();

        $normalized = $this->path_normalize($pathStringNotEmpty, '/');

        $basename = basename($normalized, $extension);

        return ('' !== $basename) ? $basename : null;
    }

    public function filename(string $path, ?string $dot = null) : ?string
    {
        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();
        $dotChar = $theType->char($dot ?? '.')->orThrow();

        $normalized = $this->path_normalize($pathStringNotEmpty, '/');

        $basename = basename($normalized);

        $split = explode($dotChar, $basename) + [ '', '' ];

        $extension = end($split);

        $filename = basename($basename, "{$dotChar}{$extension}");

        return ('' !== $filename) ? $filename : null;
    }

    public function fname(string $path, ?string $dot = null) : ?string
    {
        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();
        $dotString = $theType->char($dot ?? '.')->orThrow();

        $normalized = $this->path_normalize($pathStringNotEmpty, '/');

        $basename = basename($normalized);

        [ $file ] = explode($dotString, $basename, 2);

        return ('' !== $file) ? $file : null;
    }

    public function extension(string $path, ?string $dot = null) : ?string
    {
        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();
        $dotString = $theType->char($dot ?? '.')->orThrow();

        $normalized = $this->path_normalize($pathStringNotEmpty, '/');

        $basename = basename($normalized);

        $split = explode($dotString, $basename) + [ '', '' ];

        $extension = end($split);

        return ('' !== $extension) ? $extension : null;
    }

    public function extensions(string $path, ?string $dot = null) : ?string
    {
        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();
        $dotString = $theType->char($dot ?? '.')->orThrow();

        if ('/' === $dotString) {
            throw new LogicException(
                [ 'The `dot` should not be a `/` sign' ]
            );
        }

        $normalized = $this->path_normalize($pathStringNotEmpty, '/');

        $basename = basename($normalized);

        $split = explode($dotString, $basename) + [ 1 => '' ];

        array_shift($split);

        $extensions = null;
        if ([] !== $split) {
            $extensions = implode($dotString, $split);
        }

        return $extensions;
    }


    /**
     * > заменяет слеши в пути на указанные
     */
    public function path_normalize(string $path, ?string $separator = null) : string
    {
        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();
        $separatorString = $theType->char($separator ?? '/')->orThrow();

        /**
         * @noinspection PhpDuplicateArrayKeysInspection
         */
        $separators = [
            DIRECTORY_SEPARATOR => true,
            '\\'                => true,
            '/'                 => true,
            $separatorString    => true,
        ];
        $separators = array_keys($separators);

        $normalized = str_replace($separators, $separatorString, $pathStringNotEmpty);

        return $normalized;
    }

    /**
     * > разбирает последовательности `./path` и `../path` и возвращает нормализованный путь
     */
    public function path_resolve(string $path, ?string $separator = null, ?string $dot = null) : string
    {
        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();
        $separatorString = $theType->char($separator ?? '/')->orThrow();
        $dotString = $theType->char($dot ?? '.')->orThrow();

        $pathNormalized = $this->path_normalize($pathStringNotEmpty, '/');

        $root = ($pathNormalized[ 0 ] === '/')
            ? $separatorString
            : '';

        $segments = trim($pathNormalized, '/');
        $segments = explode('/', $segments);

        $segmentsNew = [];
        foreach ( $segments as $segment ) {
            if (false
                || ('' === $segment)
                || ($dotString === $segment)
            ) {
                continue;
            }

            if ($segment === "{$dotString}{$dotString}") {
                if ([] === $segmentsNew) {
                    throw new RuntimeException(
                        [
                            'The `path` is invalid to parse `..` segments',
                            $path,
                        ]
                    );
                }

                array_pop($segmentsNew);

                continue;
            }

            $segmentsNew[] = $segment;
        }

        $pathResolved = $root . implode($separatorString, $segmentsNew);

        if ('' === $pathResolved) {
            throw new RuntimeException(
                [
                    'Result path should be a non-empty string',
                    $path,
                    $separatorString,
                    $dotString,
                ]
            );
        }

        return $pathResolved;
    }


    /**
     * > возвращает относительный нормализованный путь, отрезая у него $root
     */
    public function path_relative(
        string $path, string $root,
        ?string $separator = null, ?string $dot = null
    ) : string
    {
        $theStr = Lib::str();
        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();
        $rootStringNotEmpty = $theType->string_not_empty($root)->orThrow();
        $separatorChar = $theType->char($separator ?? '/')->orThrow();

        $pathResolved = $this->path_resolve($pathStringNotEmpty, $separatorChar, $dot);

        $rootNormalized = $this->path_normalize($rootStringNotEmpty, $separatorChar);
        $rootNormalized = rtrim($rootNormalized, $separatorChar);

        $status = $theStr->str_starts(
            $pathResolved, ($rootNormalized . $separatorChar),
            false,
            [ &$pathRelative ]
        );

        if (! $status) {
            throw new RuntimeException(
                [ 'The `absolute` is not a part of the `root`', $root ]
            );
        }

        if ('' === $pathRelative) {
            throw new RuntimeException(
                [
                    'Result path should be a non-empty string',
                    $path,
                    $separatorChar,
                    $dot,
                ]
            );
        }

        return $pathRelative;
    }

    /**
     * > возвращает абсолютный нормализованный путь, с поддержкой `./path` и `../path`
     */
    public function path_absolute(
        string $relative, string $current,
        ?string $separator = null, ?string $dot = null
    ) : string
    {
        $theType = Lib::type();

        $relativeStringNotEmpty = $theType->string_not_empty($relative)->orThrow();
        $currentStringNotEmpty = $theType->string_not_empty($current)->orThrow();
        $separatorChar = $theType->char($separator ?? '/')->orThrow();

        $relativeNormalized = $this->path_normalize($relativeStringNotEmpty, $separatorChar);

        $isRoot = ($separatorChar === $relativeNormalized[ 0 ]);

        if ($isRoot) {
            $absoluteNormalized = $relativeNormalized;

        } else {
            $currentNormalized = $this->path_normalize($currentStringNotEmpty, $separatorChar);

            $absoluteNormalized = $currentNormalized . $separatorChar . $relativeNormalized;
        }

        $absoluteResolved = $this->path_resolve($absoluteNormalized, $separatorChar, $dot);

        return $absoluteResolved;
    }

    /**
     * > возвращает абсолютный нормализованный путь, с поддержкой `./path` и `../path`, но только если путь начинается с `.`
     */
    public function path_or_absolute(
        string $path, string $current,
        ?string $separator = null, ?string $dot = null
    ) : string
    {
        $theType = Lib::type();

        $pathStringNotEmpty = $theType->string_not_empty($path)->orThrow();
        $currentStringNotEmpty = $theType->string_not_empty($current)->orThrow();
        $dotChar = $theType->char($dot ?? '.')->orThrow();

        $isDot = ($dotChar === $path[ 0 ]);

        if ($isDot) {
            $pathResolved = $this->path_absolute(
                $pathStringNotEmpty,
                $currentStringNotEmpty,
                $separator,
                $dotChar
            );

        } else {
            $pathResolved = $this->path_normalize($pathStringNotEmpty, $separator);
        }

        return $pathResolved;
    }


    /**
     * @param mixed $data
     */
    public function serialize($data) : ?string
    {
        $theFunc = Lib::func();

        try {
            $result = $theFunc->safe_call(
                'serialize',
                [ $data ]
            );
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        if (! is_string($result)) {
            $result = null;
        }

        return $result;
    }

    /**
     * @return mixed|null
     */
    public function unserialize(string $data)
    {
        $theFunc = Lib::func();

        try {
            $result = $theFunc->safe_call(
                'unserialize',
                [ $data ]
            );
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        if (is_object($result) && (get_class($result) === '__PHP_Incomplete_Class')) {
            $result = null;
        }

        return $result;
    }


    /**
     * @param callable      $fnPooling
     * @param callable|null $fnCatch
     *
     * @return mixed|false
     */
    public function pooling_sync(
        ?int $tickUsleep, ?int $timeoutMs,
        $fnPooling, $fnCatch = null
    )
    {
        $hasFnCatch = (null !== $fnCatch);

        $tickUsleep = $tickUsleep ?? $this->staticPoolingTickUsleep();

        if ($tickUsleep <= 0) {
            throw new LogicException(
                [ 'The `tickUsleep` should be an integer positive', $tickUsleep ]
            );
        }

        if (! (false
            || (null === $timeoutMs)
            || ($timeoutMs >= 0)
        )) {
            throw new LogicException(
                [ 'The `timeoutMs` should be an integer non-negative or a null', $timeoutMs ]
            );
        }

        $thePoolingFactory = $this->poolingFactory();

        $ctx = $thePoolingFactory->newContext();

        $ctx->resetTimeoutMs($timeoutMs);

        do {
            $nowMicrotime = $ctx->updateNowMicrotime();

            if ($hasFnCatch) {
                try {
                    call_user_func_array($fnPooling, [ $ctx ]);
                }
                catch ( \Throwable $e ) {
                    call_user_func_array($fnCatch, [ $e, $ctx ]);
                }

            } else {
                call_user_func_array($fnPooling, [ $ctx ]);
            }

            if ($ctx->hasResult($refResult)) {
                return $refResult;

            } elseif ($ctx->hasError($refError)) {
                throw new RuntimeException(
                    [ 'Pooling function returned error', $refError ]
                );
            }

            if (null !== ($timeoutMicrotime = $ctx->hasTimeoutMicrotime())) {
                if ($nowMicrotime > $timeoutMicrotime) {
                    break;
                }
            }

            usleep($tickUsleep);
        } while ( true );

        return false;
    }


    public function throwable_args(...$throwableArgs) : array
    {
        $len = count($throwableArgs);

        $messageList = [];
        $messageDataList = [];
        $messageObjectList = [];
        $codeIntegerList = [];
        $codeStringList = [];
        $previousList = [];
        $fileList = [];
        $lineList = [];

        $__unresolved = [];

        for ( $i = 0; $i < $len; $i++ ) {
            $arg = $throwableArgs[ $i ];

            if (is_int($arg)) {
                $codeIntegerList[ $i ] = $arg;

                continue;
            }

            if (is_string($arg) && ('' !== $arg)) {
                /**
                 * @noinspection PhpStrFunctionsInspection
                 */
                if (true
                    && (false === strpos($arg, ' '))
                    && preg_match('/^[A-Z0-9_]+$/', $arg)
                ) {
                    $codeStringList[ $i ] = $arg;

                } else {
                    $messageList[ $i ] = $arg;
                }

                continue;
            }

            if (false
                || is_array($arg)
                || $arg instanceof \stdClass
            ) {
                $messageDataArray = (array) $arg;

                if ([] === $messageDataArray) {
                    continue;
                }

                if (true
                    && isset($messageDataArray[ 0 ])
                    && isset($messageDataArray[ 1 ])
                    && is_int($messageDataArray[ 1 ])
                    && is_file($messageDataArray[ 0 ])
                ) {
                    $fileList[ $i ] = $messageDataArray[ 0 ];
                    $lineList[ $i ] = $messageDataArray[ 1 ];

                    continue;
                }

                if (isset($messageDataArray[ 0 ])) {
                    $messageString = null;

                    if (false
                        || is_scalar($messageDataArray[ 0 ])
                        || is_object($messageDataArray[ 0 ])
                    ) {
                        $messageString = (string) $messageDataArray[ 0 ];
                    }

                    if ('' === $messageString) {
                        $messageString = null;
                    }

                    if (null !== $messageString) {
                        unset($messageDataArray[ 0 ]);

                        /**
                         * @noinspection PhpStrFunctionsInspection
                         */
                        if (true
                            && (false === strpos($messageString, ' '))
                            && preg_match('/^[A-Z0-9_]+$/', $messageString)
                        ) {
                            $codeStringList[ $i ] = $messageString;

                        } else {
                            $messageList[ $i ] = $messageString;
                        }
                    }
                }

                $messageDataList[ $i ] = $messageDataArray;

                continue;
            }

            if ($arg instanceof \Throwable) {
                $previousList[ $i ] = $arg;

                continue;
            }

            $__unresolved[ $i ] = $arg;
        }

        if ([] !== $previousList) {
            if ([] === $messageList) {
                $messageList = [];
                $codeIntegerList = [];
                $fileList = [];
                $lineList = [];

                foreach ( $previousList as $i => $previous ) {
                    $messageList[ $i ] = $previous->getMessage();
                    $codeIntegerList[ $i ] = $previous->getCode();
                    $fileList[ $i ] = $previous->getFile();
                    $lineList[ $i ] = $previous->getLine();
                }
            }
        }

        foreach ( $messageList as $i => $messageString ) {
            $messageData = $messageDataList[ $i ] ?? [];

            $messageObjectList[ $i ] = (object) ([ $messageString ] + $messageData);
        }

        $result = [];

        $result[ 'messageList' ] = $messageList;
        $result[ 'messageDataList' ] = $messageDataList;
        $result[ 'messageObjectList' ] = $messageObjectList;
        $result[ 'codeIntegerList' ] = $codeIntegerList;
        $result[ 'codeStringList' ] = $codeStringList;
        $result[ 'previousList' ] = $previousList;
        $result[ 'fileList' ] = $fileList;
        $result[ 'lineList' ] = $lineList;

        $result += [
            'message'       => (null
                ?? (([] !== $messageList) ? reset($messageList) : null)
                ?? (([] !== $codeStringList) ? reset($codeStringList) : null)
                ?? null
            ),
            'messageData'   => (([] !== $messageDataList) ? reset($messageDataList) : []),
            'messageObject' => (([] !== $messageObjectList) ? reset($messageObjectList) : null),
            //
            'code'          => (([] !== $codeIntegerList) ? reset($codeIntegerList) : -1),
            'codeString'    => (([] !== $codeStringList) ? reset($codeStringList) : ''),
            //
            'previous'      => (([] !== $previousList) ? reset($previousList) : null),
            //
            'file'          => (([] !== $fileList) ? reset($fileList) : null),
            'line'          => (([] !== $lineList) ? reset($lineList) : null),
        ];

        $result[ '__unresolved' ] = $__unresolved;

        return $result;
    }
}
