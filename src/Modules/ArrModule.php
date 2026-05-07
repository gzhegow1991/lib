<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Arr\ArrPath;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class ArrModule
{
    /**
     * @var int
     */
    protected static $fnMode = _ARR_FN_USE_VALUE;

    /**
     * @param int|false|null $fnMode
     */
    public static function staticFnMode($fnMode = null) : ?int
    {
        $last = static::$fnMode;

        if ( null !== $fnMode ) {
            if ( false === $fnMode ) {
                static::$fnMode = _ARR_FN_USE_VALUE;

            } else {
                if ( 0 === ($fnMode & ~_ARR_FN_USE_ALL) ) {
                    throw new LogicException(
                        [
                            'The `fn_mode` should be a valid sequence of flags',
                            //
                            $fnMode,
                            _ARR_FN_USE_ALL,
                        ]
                    );
                }

                static::$fnMode = $fnMode;
            }
        }

        static::$fnMode = static::$fnMode ?? _ARR_FN_USE_VALUE;

        return $last;
    }


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    /**
     * @return Ret<int|string>|int|string
     */
    public function type_key($fb, $key)
    {
        $theType = Lib::type();

        if ( is_int($key) ) {
            return Ret::ok($fb, $key);

        } elseif ( is_string($key) ) {
            return Ret::ok($fb, $key);

        } else {
            $ret = $theType->string($key);

            if ( $ret->isOk([ &$keyString ]) ) {
                return Ret::ok($fb, $keyString);
            }
        }

        return Ret::throw(
            $fb,
            [ 'The `key` should be valid array key', $key ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<mixed>|mixed
     */
    public function type_key_exists($fb, array $array, $key)
    {
        if ( isset($array[$key]) ) {
            return Ret::ok($fb, $array[$key]);
        }

        if ( array_key_exists($key, $array) ) {
            return Ret::ok($fb, $array[$key]);
        }

        return Ret::throw(
            $fb,
            [ 'The `key` should be existing key in `array`', $key, $array ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<mixed>|mixed
     */
    public function type_value_in_array($fb, array $array, $value, ?bool $strict = null)
    {
        if ( ! in_array($value, $array, $strict) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should exists in `array`', $value, $array ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @return Ret<int|string>|int|string
     */
    public function type_value_in_array_key($fb, array $array, $value, ?bool $strict = null)
    {
        $key = array_search($value, $array, $strict);

        if ( false === $key ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should exists in `array`', $value, $array ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $key);
    }

    /**
     * @return Ret<array>|array
     */
    public function type_value_in_array_pos($fb, array $array, $value, ?bool $strict = null)
    {
        $copy = array_values($array);

        $pos = array_search($value, $copy, $strict);

        if ( false === $pos ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should exists in `array`', $value, $array ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $pos);
    }


    /**
     * @return Ret<array>|array
     */
    public function type_keys_exists($fb, array $array, $keys)
    {
        $keys = (array) $keys;

        if ( [] === $keys ) {
            throw new LogicException(
                [ 'The `keys` should be array, non-empty', $keys ],
            );
        }

        if ( [] === $array ) {
            return Ret::throw(
                $fb,
                [ 'The `keys` should exists in `array`', $keys, $array ],
                [ __FILE__, __LINE__ ]
            );
        }

        foreach ( $keys as $key ) {
            if ( isset($array[$key]) ) {
                continue;
            }

            if ( array_key_exists($key, $array) ) {
                continue;
            }

            return Ret::throw(
                $fb,
                [ 'The `keys` should exists in `array`', $key, $keys, $array ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $array);
    }

    /**
     * @return Ret<array>|array
     */
    public function type_keys_not_exists($fb, array $array, $keys)
    {
        if ( [] === $array ) {
            return Ret::ok($fb, $array);
        }

        $keys = (array) $keys;

        if ( [] === $keys ) {
            throw new LogicException(
                [ 'The `keys` should be array, non-empty', $keys ],
            );
        }

        foreach ( $keys as $key ) {
            if ( isset($array[$key]) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `keys` should not exists in `array`', $key, $keys, $array ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( array_key_exists($key, $array) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `keys` should not exists in `array`', $key, $keys, $array ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok($fb, $array);
    }


    /**
     * @return Ret<array>|array
     */
    public function type_values_in_array($fb, array $array, $values, ?bool $strict = null)
    {
        $values = (array) $values;

        if ( [] === $values ) {
            throw new LogicException(
                [ 'The `values` should be array, non-empty', $values ],
            );
        }

        if ( [] === $array ) {
            return Ret::throw(
                $fb,
                [ 'The `values` should exists in `array`', $values, $array ],
                [ __FILE__, __LINE__ ]
            );
        }

        foreach ( $values as $value ) {
            if ( in_array($value, $values, $strict) ) {
                continue;
            }

            return Ret::throw(
                $fb,
                [ 'The `values` should exists in `array`', $value, $values, $array, $strict ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $array);
    }

    /**
     * @return Ret<array>|array
     */
    public function type_values_not_in_array($fb, array $array, $values, ?bool $strict = null)
    {
        if ( [] === $array ) {
            return Ret::ok($fb, $array);
        }

        $values = (array) $values;

        if ( [] === $values ) {
            throw new LogicException(
                [ 'The `values` should be array, non-empty', $values ],
            );
        }

        foreach ( $values as $value ) {
            if ( in_array($value, $values, $strict) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `values` should not exists in `array`', $value, $values, $array, $strict ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok($fb, $array);
    }


    /**
     * @return Ret<array>|array
     */
    public function type_array_plain($fb, $value, ?int $plainMaxDepth = null)
    {
        $plainMaxDepth = $plainMaxDepth ?? 1;

        if ( $plainMaxDepth < 1 ) {
            throw new LogicException(
                [ 'The `maxDepth` should be greater than 1', $plainMaxDepth ],
            );
        }

        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( [] === $value ) {
            return Ret::ok($fb, $value);
        }

        if ( 1 === $plainMaxDepth ) {
            foreach ( $value as $v ) {
                if ( is_array($v) ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `value` should be array of depth that is passed to `plainMaxDepth`', $value, $plainMaxDepth ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }

        } else {
            $depth = 0;

            $queue = [
                [ array_reverse($value), 1 ],
            ];

            while ( [] !== $queue ) {
                [ $child, $level ] = array_pop($queue);

                $depth = max($depth, $level);

                foreach ( array_reverse($child) as $v ) {
                    if ( is_array($v) ) {
                        $levelNew = $level + 1;

                        if ( $levelNew > $plainMaxDepth ) {
                            return Ret::throw(
                                $fb,
                                [ 'The `value` should be array of depth that is passed to `plainMaxDepth`', $value, $plainMaxDepth ],
                                [ __FILE__, __LINE__ ]
                            );
                        }

                        $queue[] = [ $v, $levelNew ];
                    }
                }
            }
        }

        return Ret::ok($fb, $value);
    }


    /**
     * @return Ret<array>|array
     */
    public function type_list($fb, $value, ?int $plainMaxDepth = null)
    {
        $hasMaxDepth = (null !== $plainMaxDepth);

        if ( $hasMaxDepth ) {
            if ( $plainMaxDepth < 1 ) {
                throw new LogicException(
                    [ 'The `maxDepth` should be greater than 1', $plainMaxDepth ],
                );
            }
        }

        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( [] === $value ) {
            return Ret::ok($fb, $value);
        }

        if ( $hasMaxDepth ) {
            if ( 1 === $plainMaxDepth ) {
                foreach ( $value as $key => $v ) {
                    if ( is_string($key) ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `value` should be array without string keys', $value ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    if ( is_array($v) ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `value` should be array of depth that is passed to `plainMaxDepth`', $value, $plainMaxDepth ],
                            [ __FILE__, __LINE__ ]
                        );
                    }
                }

            } else {
                $depth = 0;

                $queue = [
                    [ array_reverse($value, true), 1 ],
                ];

                while ( [] !== $queue ) {
                    [ $child, $level ] = array_pop($queue);

                    $depth = max($depth, $level);

                    foreach ( array_reverse($child, true) as $key => $v ) {
                        if ( is_string($key) ) {
                            return Ret::throw(
                                $fb,
                                [ 'The `value` should be array without string keys', $value ],
                                [ __FILE__, __LINE__ ]
                            );
                        }

                        if ( is_array($v) ) {
                            $levelNew = $level + 1;

                            if ( $levelNew > $plainMaxDepth ) {
                                return Ret::throw(
                                    $fb,
                                    [ 'The `value` should be array of depth that is passed to `plainMaxDepth`', $value, $plainMaxDepth ],
                                    [ __FILE__, __LINE__ ]
                                );
                            }

                            $queue[] = [ $v, $levelNew ];
                        }
                    }
                }
            }

        } else {
            foreach ( array_keys($value) as $key ) {
                if ( is_string($key) ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `value` should be array without string keys', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @return Ret<array>|array
     */
    public function type_list_sorted($fb, $value, ?int $plainMaxDepth = null)
    {
        $hasMaxDepth = (null !== $plainMaxDepth);

        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( [] === $value ) {
            return Ret::ok($fb, $value);
        }

        $prev = -1;

        if ( $hasMaxDepth ) {
            if ( $plainMaxDepth < 1 ) {
                return Ret::throw(
                    $fb,
                    [ 'The `plainMaxDepth` should be greater than 1', $plainMaxDepth ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( 1 === $plainMaxDepth ) {
                foreach ( $value as $key => $v ) {
                    if ( is_string($key) ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `value` should be array without string keys', $value ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    if ( ($key - $prev) !== 1 ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `value` should be sorted array', $value ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    if ( is_array($v) ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `value` should be array of depth that is passed to `plainMaxDepth`', $value, $plainMaxDepth ],
                            [ __FILE__, __LINE__ ]
                        );
                    }
                }

            } else {
                $depth = 0;

                $queue = [
                    [ array_reverse($value, true), 1 ],
                ];

                while ( [] !== $queue ) {
                    [ $child, $level ] = array_pop($queue);

                    $depth = max($depth, $level);

                    $prev = -1;

                    foreach ( array_reverse($child, true) as $key => $v ) {
                        if ( is_string($key) ) {
                            return Ret::throw(
                                $fb,
                                [ 'The `value` should be array without string keys', $value ],
                                [ __FILE__, __LINE__ ]
                            );
                        }

                        if ( ($key - $prev) !== 1 ) {
                            return Ret::throw(
                                $fb,
                                [ 'The `value` should be sorted array', $value ],
                                [ __FILE__, __LINE__ ]
                            );
                        }

                        if ( is_array($v) ) {
                            $levelNew = $level + 1;

                            if ( $levelNew > $plainMaxDepth ) {
                                return Ret::throw(
                                    $fb,
                                    [ 'The `value` should be array of depth that is passed to `plainMaxDepth`', $value, $plainMaxDepth ],
                                    [ __FILE__, __LINE__ ]
                                );
                            }

                            $queue[] = [ $v, $levelNew ];
                        }

                        $prev = $key;
                    }
                }
            }

        } else {
            foreach ( array_keys($value) as $key ) {
                if ( is_string($key) ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `value` should be array without string keys', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }

                if ( ($key - $prev) !== 1 ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `value` should be sorted array', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }

                $prev = $key;
            }
        }

        return Ret::ok($fb, $value);
    }


    /**
     * @return Ret<array>|array
     */
    public function type_dict($fb, $value, ?int $plainMaxDepth = null)
    {
        $hasMaxDepth = (null !== $plainMaxDepth);

        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( [] === $value ) {
            return Ret::ok($fb, $value);
        }

        if ( $hasMaxDepth ) {
            if ( $plainMaxDepth < 1 ) {
                return Ret::throw(
                    $fb,
                    [ 'The `plainMaxDepth` should be greater than 1', $plainMaxDepth ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( 1 === $plainMaxDepth ) {
                foreach ( $value as $key => $v ) {
                    if ( is_int($key) ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `value` should be array without int keys', $value ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    if ( is_array($v) ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `value` should be array of depth that is passed to `plainMaxDepth`', $value, $plainMaxDepth ],
                            [ __FILE__, __LINE__ ]
                        );
                    }
                }

            } else {
                $depth = 0;

                $queue = [
                    [ array_reverse($value, true), 1 ],
                ];

                while ( [] !== $queue ) {
                    [ $child, $level ] = array_pop($queue);

                    $depth = max($depth, $level);

                    foreach ( array_reverse($child, true) as $key => $v ) {
                        if ( is_int($key) ) {
                            return Ret::throw(
                                $fb,
                                [ 'The `value` should be array without int keys', $value ],
                                [ __FILE__, __LINE__ ]
                            );
                        }

                        if ( is_array($v) ) {
                            $levelNew = $level + 1;

                            if ( $levelNew > $plainMaxDepth ) {
                                return Ret::throw(
                                    $fb,
                                    [ 'The `value` should be array of depth that is passed to `plainMaxDepth`', $value, $plainMaxDepth ],
                                    [ __FILE__, __LINE__ ]
                                );
                            }

                            $queue[] = [ $v, $levelNew ];
                        }
                    }
                }
            }

        } else {
            foreach ( array_keys($value) as $key ) {
                if ( is_int($key) ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `value` should be array without int keys', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @param callable $fnSortCmp
     *
     * @return Ret<array>|array
     */
    public function type_dict_sorted($fb, $value, ?int $plainMaxDepth = null, $fnSortCmp = null)
    {
        $hasMaxDepth = (null !== $plainMaxDepth);

        $fnSortCmp = $fnSortCmp ?? 'strcmp';

        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( [] === $value ) {
            return Ret::ok($fb, $value);
        }

        $prev = '';

        if ( $hasMaxDepth ) {
            if ( $plainMaxDepth < 1 ) {
                return Ret::throw(
                    $fb,
                    [ 'The `plainMaxDepth` should be greater than 1', $plainMaxDepth ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( 1 === $plainMaxDepth ) {
                foreach ( $value as $key => $v ) {
                    if ( is_int($key) ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `value` should be array without int keys', $value ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    if ( is_array($v) ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `value` should be array of depth that is passed to `plainMaxDepth`', $value, $plainMaxDepth ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    $cmp = call_user_func($fnSortCmp, $prev, $key);

                    if ( ! is_int($cmp) ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `fnSortCmp` should return integer', $fnSortCmp ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    if ( $cmp < 0 ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `value` should be sorted array', $value ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    $prev = $key;
                }

            } else {
                $depth = 0;

                $queue = [
                    [ array_reverse($value, true), 1 ],
                ];

                while ( [] !== $queue ) {
                    [ $child, $level ] = array_pop($queue);

                    $depth = max($depth, $level);

                    $prev = -1;

                    foreach ( array_reverse($child, true) as $key => $v ) {
                        if ( is_int($key) ) {
                            return Ret::throw(
                                $fb,
                                [ 'The `value` should be array without int keys', $value ],
                                [ __FILE__, __LINE__ ]
                            );
                        }

                        $cmp = call_user_func($fnSortCmp, $prev, $key);

                        if ( ! is_int($cmp) ) {
                            return Ret::throw(
                                $fb,
                                [ 'The `fnSortCmp` should return integer', $fnSortCmp ],
                                [ __FILE__, __LINE__ ]
                            );
                        }

                        if ( $cmp < 0 ) {
                            return Ret::throw(
                                $fb,
                                [ 'The `value` should be sorted array', $value ],
                                [ __FILE__, __LINE__ ]
                            );
                        }

                        if ( is_array($v) ) {
                            $levelNew = $level + 1;

                            if ( $levelNew > $plainMaxDepth ) {
                                return Ret::throw(
                                    $fb,
                                    [ 'The `value` should be array of depth that is passed to `plainMaxDepth`', $value, $plainMaxDepth ],
                                    [ __FILE__, __LINE__ ]
                                );
                            }

                            $queue[] = [ $v, $levelNew ];
                        }

                        $prev = $key;
                    }
                }
            }

        } else {
            foreach ( array_keys($value) as $key ) {
                if ( is_int($key) ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `value` should be array without int keys', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }

                $cmp = call_user_func($fnSortCmp, $prev, $key);

                if ( ! is_int($cmp) ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `fnSortCmp` should return integer', $fnSortCmp ],
                        [ __FILE__, __LINE__ ]
                    );
                }

                if ( $cmp < 0 ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `value` should be sorted array', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }

                $prev = $key;
            }
        }

        return Ret::ok($fb, $value);
    }


    /**
     * > проверить, является ли значение таблицей - массивом массивов
     *
     * @return Ret<array>|array
     */
    public function type_table($fb, $value)
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        for ( $i = 0; $i < count($value); $i++ ) {
            if ( ! is_array($value[$i]) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be array of arrays', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok(null, $value);
    }


    /**
     * > проверить, является ли значение матрицей - массивом второго уровня, все ключи которого цифровые
     *
     * @return Ret<array>|array
     */
    public function type_matrix($fb, $value)
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        for ( $i = 0; $i < count($value); $i++ ) {
            $ret = $this->type_list(null, $value[$i]);

            if ( ! $ret->isOk() ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be array of lists', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @return Ret<array>|array
     */
    public function type_matrix_sorted($fb, $value)
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        for ( $i = 0; $i < count($value); $i++ ) {
            $ret = $this->type_list_sorted(null, $value[$i]);

            if ( ! $ret->isOk() ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be array of sorted lists', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok($fb, $value);
    }


    /**
     * @return Ret<ArrPath>|ArrPath
     */
    public function type_arrpath($fb, $value)
    {
        if ( $value instanceof ArrPath ) {
            return Ret::ok($fb, $value);
        }

        try {
            $array = $this->arrpath($value);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid arrpath', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = ArrPath::fromValidArray($array);

        if ( ! $ret->isOk([ &$arrpathObject ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $arrpathObject);
    }

    /**
     * @return Ret<ArrPath>|ArrPath
     */
    public function type_arrpath_dot($fb, $value, ?string $dot = '.')
    {
        if ( $value instanceof ArrPath ) {
            return Ret::ok($fb, $value);
        }

        try {
            $array = (null === $dot)
                ? $this->arrpath($dot, $value)
                : $this->arrpath_dot($dot, $value);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid arrpath_dot', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = ArrPath::fromValidArray($array);

        if ( ! $ret->isOk([ &$arrpathObject ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $arrpathObject);
    }


    /**
     * @return Ret<array>|array
     */
    public function type_array_of_type($fb, $value, string $type)
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $mapTypes = [
            "mixed"             => "mixed",
            //
            "null"              => "NULL",
            "boolean"           => "boolean",
            "integer"           => "integer",
            "double"            => "double",
            "string"            => "string",
            "array"             => "array",
            "object"            => "object",
            "resource"          => "resource",
            "resource (closed)" => "resource (closed)",
            "unknown type"      => "unknown type",
            //
            ""                  => 'mixed',
            "int"               => "integer",
            "float"             => "double",
        ];

        if ( ! isset($mapTypes[$type]) ) {
            return Ret::throw(
                $fb,
                [
                    ''
                    . 'The `type` should be one of: '
                    . '[ ' . implode(' ][ ', array_keys($mapTypes)) . ' ]',
                    //
                    $type,
                ],
                [ __FILE__, __LINE__ ]
            );
        }

        foreach ( $value as $i => $v ) {
            if ( $type !== gettype($v) ) {
                return Ret::throw(
                    $fb,
                    [ 'Each of `value` should be passed type', $v, $i, $type ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @return Ret<array>|array
     */
    public function type_array_of_resource_type($fb, $value, string $resourceType)
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        foreach ( $value as $i => $v ) {
            if ( ! is_resource($v) ) {
                return Ret::throw(
                    $fb,
                    [ 'Each of `value` should be opened resource', $v, $i, $resourceType ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( $resourceType !== get_resource_type($v) ) {
                return Ret::throw(
                    $fb,
                    [ 'Each of `value` should be opened resource of type', $v, $i, $resourceType ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>|T[]
     */
    public function type_array_of_a($fb, $value, string $className)
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $theType = Lib::type();

        $ret = $theType->struct_exists($className);

        if ( ! $ret->isOk([ &$classNameValid ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        foreach ( $value as $i => $v ) {
            if ( ! is_a($v, $classNameValid) ) {
                return Ret::throw(
                    $fb,
                    [ 'Each of `value` should be instance of passed `className`', $v, $i, $className ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>|T[]
     */
    public function type_array_of_class($fb, $value, string $className)
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $theType = Lib::type();

        $ret = $theType->struct_exists($className);

        if ( ! $ret->isOk([ &$classNameValid ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        foreach ( $value as $i => $v ) {
            if ( ! is_object($v) ) {
                return Ret::throw(
                    $fb,
                    [ 'Each of `value` should be object', $v, $i ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( $className !== get_class($v) ) {
                return Ret::throw(
                    $fb,
                    [ 'Each of `value` should be object of passed class (exact match)', $v, $i, $className ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>|T[]
     */
    public function type_array_of_subclass($fb, $value, string $className)
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $theType = Lib::type();

        $ret = $theType->struct_exists($className);

        if ( ! $ret->isOk([ &$classNameValid ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        foreach ( $value as $i => $v ) {
            if ( ! is_subclass_of($v, $className) ) {
                return Ret::throw(
                    $fb,
                    [ 'Each of `value` should be instance of passed subclass `className`', $v, $i, $className ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @param callable $fn
     *
     * @return Ret<array>|array
     *
     * @noinspection PhpDocSignatureIsNotCompleteInspection
     */
    public function type_array_of_callback($fb, $value, callable $fn, array $fnArgs = [])
    {
        if ( ! is_array($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        foreach ( $value as $i => $v ) {
            $vArgs = array_merge([ $v ], $fnArgs);

            if ( ! call_user_func_array($fn, $vArgs) ) {
                return Ret::throw(
                    $fb,
                    [ 'Each of `value` should pass passed `fn` check', $v, $i, $fn, $fnArgs ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::ok($fb, $value);
    }


    /**
     * @return string[]
     */
    public function arrpath($path, ...$pathes) : array
    {
        $theType = Lib::type();

        $gen = $this->arrpath_it($path, ...$pathes);

        $arrpath = [];
        foreach ( $gen as $p ) {
            $ret = $theType->string($p);

            if ( $ret->isOk([ &$pString ]) ) {
                $arrpath[] = $pString;
            }
        }

        if ( [] === $arrpath ) {
            throw new LogicException(
                [ 'Result path is empty', $path, $pathes ]
            );
        }

        return $arrpath;
    }

    /**
     * @return string[]
     */
    public function arrpath_dot(string $dot, $path, ...$pathes) : array
    {
        $theType = Lib::type();

        $dotChar = $theType->char($dot)->orThrow();

        $gen = $this->arrpath_it($path, ...$pathes);

        $arrpath = [];
        foreach ( $gen as $p ) {
            $ret = $theType->string($p);

            if ( $ret->isOk([ &$pString ]) ) {
                if ( '' === $pString ) {
                    $arrpath[] = $pString;

                } else {
                    $arrpath = array_merge(
                        $arrpath,
                        explode($dotChar, $pString)
                    );
                }
            }
        }

        if ( [] === $arrpath ) {
            throw new LogicException(
                [
                    'Result path is empty',
                    $path,
                    $pathes,
                ]
            );
        }

        return $arrpath;
    }

    /**
     * @return \Generator<mixed>
     */
    public function arrpath_it($path, ...$pathes) : \Generator
    {
        if ( [] === $pathes ) {
            if ( $path instanceof ArrPath ) {
                foreach ( $path->getPath() as $p ) {
                    yield $p;
                }

                return;
            }
        }

        array_unshift($pathes, $path);

        $gen = $this->walk_it($pathes);

        foreach ( $gen as $p ) {
            if ( $p instanceof ArrPath ) {
                foreach ( $p->getPath() as $pp ) {
                    yield $pp;
                }

            } else {
                yield $p;
            }
        }
    }


    public function has_key($array, $key, array $refs = []) : bool
    {
        $withValue = array_key_exists(0, $refs);
        if ( $withValue ) {
            $refValue =& $refs[0];
        }
        $refValue = null;

        if ( ! is_array($array) ) {
            return false;
        }

        if ( [] === $array ) {
            return false;
        }

        try {
            $keyString = (string) $key;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        if ( ! array_key_exists($keyString, $array) ) {
            return false;
        }

        $refValue = $array[$keyString];

        return true;
    }

    /**
     * @throws \RuntimeException
     */
    public function get_key(array $array, $key, array $fallback = [])
    {
        $status = $this->has_key($array, $key, [ &$value ]);

        if ( ! $status ) {
            if ( $fallback ) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                [ 'Missing key in array', $key ]
            );
        }

        return $value;
    }


    /**
     * @return int|string|null
     */
    public function key_first(array $array)
    {
        if ( PHP_VERSION_ID >= 70300 ) {
            return array_key_first($array);
        }

        reset($array);

        return key($array);
    }

    /**
     * @return int|string|null
     */
    public function key_last(array $array)
    {
        if ( PHP_VERSION_ID >= 70300 ) {
            return array_key_last($array);
        }

        end($array);

        return key($array);
    }

    public function key_next(array $src) : int
    {
        $copy = array_fill_keys(
            array_keys($src),
            true
        );

        $copy[] = true;

        if ( PHP_VERSION_ID >= 70300 ) {
            $lastKey = array_key_last($copy);

        } else {
            end($src);

            $lastKey = key($copy);
        }

        return $lastKey;
    }


    /**
     * @throws \RuntimeException
     */
    public function first(array $array, array $fallback = [])
    {
        if ( [] === $array ) {
            if ( $fallback ) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                [ 'Missing `fallback[0]` array element', $fallback ]
            );
        }

        if ( PHP_VERSION_ID >= 70300 ) {
            return $array[array_key_first($array)];
        }

        $first = reset($array);

        return $first;
    }

    /**
     * @throws \RuntimeException
     */
    public function last(array $array, array $fallback = [])
    {
        if ( [] === $array ) {
            if ( $fallback ) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                [ 'Missing `fallback[0]` array element', $fallback ]
            );
        }

        if ( PHP_VERSION_ID >= 70300 ) {
            return $array[array_key_last($array)];
        }

        $last = end($array);

        return $last;
    }


    public function has_pos(
        $array, $pos,
        array $refs = []
    ) : bool
    {
        $withValue = array_key_exists(0, $refs);
        if ( $withValue ) {
            $refValue =& $refs[0];
        }
        $refValue = null;

        $withKey = array_key_exists(1, $refs);
        if ( $withKey ) {
            $refKey =& $refs[1];
        }
        $refKey = null;

        if ( ! is_array($array) ) {
            return false;
        }

        if ( ! is_int($pos) ) {
            return false;
        }

        $isNegativePos = ($pos < 0);

        $copyArray = $array;

        if ( $isNegativePos ) {
            end($copyArray);

            $abs = abs($pos) - 1;

        } else {
            reset($copyArray);

            $abs = abs($pos);
        }

        while ( null !== ($k = key($copyArray)) ) {
            if ( 0 === $abs ) {
                $refValue = $array[$k];
                $refKey = $k;

                unset($refValue);
                unset($refKey);

                return true;
            }

            $isNegativePos
                ? prev($copyArray)
                : next($copyArray);

            $abs--;
        }

        return false;
    }

    /**
     * @return int|string|null
     */
    public function key_pos(array $array, int $pos)
    {
        $status = $this->has_pos($array, $pos, [ 1 => &$key ]);

        if ( $status ) {
            return $key;
        }

        return null;
    }

    /**
     * @throws \RuntimeException
     */
    public function get_pos(array $array, int $pos, array $fallback = [])
    {
        $status = $this->has_pos($array, $pos, [ &$value, &$key ]);

        if ( ! $status ) {
            if ( $fallback ) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                'Missing pos in array: ' . $pos
            );
        }

        return $value;
    }


    public function has(array $arr, $path, ?string $dot = '.', array $refs = []) : bool
    {
        $ret = $this->type_arrpath_dot(null, $path, $dot);

        if ( ! $ret->isOk([ &$pathObject ]) ) {
            return false;
        }

        $bool = $this->has_path($arr, $pathObject, $refs);

        return $bool;
    }

    public function has_path(array $array, $path, array $refs = []) : bool
    {
        /** @var ArrPath $pathObject */

        $ret = $this->type_arrpath(null, $path);

        if ( ! $ret->isOk([ &$pathObject ]) ) {
            return false;
        }

        $pathArray = $pathObject->getPath();

        $withValue = array_key_exists(0, $refs);
        if ( $withValue ) {
            $refValue =& $refs[0];
        }
        $refValue = null;

        $withKey = array_key_exists(1, $refs);
        if ( $withKey ) {
            $refKey =& $refs[1];
        }
        $refKey = null;

        $refCurrent =& $array;

        $isFound = true;
        $pathStep = null;

        while ( $pathArray ) {
            $pathStep = array_shift($pathArray);

            if ( ! array_key_exists($pathStep, $refCurrent) ) {
                $isFound = false;

                $pathStep = null;

                unset($refCurrent);
                $refCurrent = null;

                break;
            }

            $refCurrent =& $refCurrent[$pathStep];

            if ( (! is_array($refCurrent)) && $pathArray ) {
                $isFound = false;

                $pathStep = null;

                unset($refCurrent);
                $refCurrent = null;

                break;
            }
        }

        if ( $isFound ) {
            $refValue = $refCurrent;
            $refKey = $pathStep;
        }

        return $isFound;
    }


    /**
     * @throws \LogicException|\RuntimeException
     */
    public function &fetch(array &$arr, $path, ?string $dot = '.')
    {
        $pathObject = $this->type_arrpath_dot([], $path, $dot);

        return $this->fetch_path($arr, $pathObject);
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function &fetch_path(array &$refArray, $path)
    {
        $pathObject = $this->type_arrpath([], $path);

        $pathArray = $pathObject->toArray();

        $refCurrent =& $refArray;

        while ( [] !== $pathArray ) {
            $pathStep = array_shift($pathArray);

            if ( ! array_key_exists($pathStep, $refCurrent) ) {
                unset($refCurrent);
                $refCurrent = null;

                if ( [] === $pathArray ) {
                    throw new RuntimeException(
                        [ 'Missing key in array', $pathStep, $path ]
                    );
                }
            }

            $refCurrent =& $refCurrent[$pathStep];

            if ( (! is_array($refCurrent)) && $pathArray ) {
                unset($refCurrent);
                $refCurrent = null;

                throw new RuntimeException(
                    [ 'Trying to traverse scalar value', $pathStep, $path ]
                );
            }
        }

        return $refCurrent;
    }


    /**
     * @throws \RuntimeException
     */
    public function get(array $arr, $path, array $fallback = [], ?string $dot = '.')
    {
        $pathObject = $this->type_arrpath_dot($fallback, $path, $dot);

        $res = $this->get_path($arr, $pathObject, $fallback);

        return $res;
    }

    /**
     * @throws \RuntimeException
     */
    public function get_path(array $array, $path, array $fallback = [])
    {
        $status = $this->has_path($array, $path, [ &$value ]);

        if ( ! $status ) {
            if ( $fallback ) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                [ 'Missing array path', $path ]
            );
        }

        return $value;
    }


    /**
     * @throws \LogicException|\RuntimeException
     */
    public function &put(array &$arr, $path, $val, ?string $dot = '.')
    {
        $pathObject = $this->type_arrpath_dot([], $path, $dot);

        $ref = $this->put_path($arr, $pathObject, $val);

        return $ref;
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function &set(array &$arr, $path, $val, ?string $dot = '.')
    {
        $pathObject = $this->type_arrpath_dot([], $path, $dot);

        $ref = $this->put_path($arr, $pathObject, $val);

        return $ref;
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function &put_path(array &$refArray, $path, $value)
    {
        $pathObject = $this->type_arrpath([], $path);

        $pathArray = $pathObject->getPath();

        $refCurrent =& $refArray;

        while ( [] !== $pathArray ) {
            $pathStep = array_shift($pathArray);

            if ( ! array_key_exists($pathStep, $refCurrent) ) {
                $refCurrent[$pathStep] = $pathArray
                    ? []
                    : null;
            }

            $refCurrent =& $refCurrent[$pathStep];

            if ( (! is_array($refCurrent)) && $pathArray ) {
                unset($refCurrent);
                $refCurrent = null;

                throw new RuntimeException(
                    [ 'Trying to traverse scalar value', $pathStep, $path ]
                );
            }
        }

        $refCurrent = $value;

        return $refCurrent;
    }


    /**
     * @throws \LogicException
     */
    public function unset(array &$arr, $path, ?string $dot = '.') : bool
    {
        /** @var ArrPath $pathObject */

        $ret = $this->type_arrpath_dot(null, $path, $dot);

        if ( ! $ret->isOk([ &$pathObject ]) ) {
            return false;
        }

        return $this->unset_path($arr, $pathObject);
    }

    /**
     * @throws \LogicException
     */
    public function unset_path(array &$refArray, $path) : bool
    {
        /** @var ArrPath $pathObject */

        $ret = $this->type_arrpath(null, $path);

        if ( ! $ret->isOk([ &$pathObject ]) ) {
            return false;
        }

        $pathArray = $pathObject->getPath();

        $refCurrent =& $refArray;

        $isDeleted = false;

        $pathStep = null;

        $refPrevious = null;
        foreach ( $pathArray as $pathStep ) {
            $refPrevious =& $refCurrent;

            if ( ! is_array($refCurrent) ) {
                unset($refPrevious);

                break;
            }

            if ( ! array_key_exists($pathStep, $refCurrent) ) {
                unset($refPrevious);

                break;
            }

            $refCurrent = &$refCurrent[$pathStep];
        }

        if ( isset($refPrevious)
            && (false
                || isset($refPrevious[$pathStep])
                || array_key_exists($pathStep, $refPrevious)
            )
        ) {
            unset($refPrevious[$pathStep]);

            $isDeleted = true;
        }

        unset($pathStep);

        unset($refPrevious);
        $refPrevious = null;

        unset($refCurrent);
        $refCurrent = null;

        return $isDeleted;
    }


    public function is_array_depth($array, int $maxdepth) : bool
    {
        if ( ! is_array($array) ) {
            return false;
        }

        if ( $maxdepth <= 0 ) {
            throw new LogicException(
                [ 'The `maxdepth` should be greater than 1', $maxdepth ]
            );
        }

        if ( $maxdepth === 1 ) {
            return true;
        }

        $depth = 0;

        $queue = [
            [ $array, 1 ],
        ];

        while ( [] !== $queue ) {
            [ $child, $level ] = array_pop($queue);

            $depth = max($depth, $level);

            foreach ( $child as $v ) {
                if ( is_array($v) ) {
                    $levelNew = $level + 1;

                    if ( $levelNew === $maxdepth ) {
                        return true;
                    }

                    $queue[] = [ $v, $levelNew ];
                }
            }
        }

        return false;
    }

    public function array_depth(array $array) : int
    {
        $depth = 0;

        $queue = [
            [ $array, 1 ],
        ];

        while ( [] !== $queue ) {
            [ $child, $level ] = array_pop($queue);

            $depth = max($depth, $level);

            foreach ( $child as $v ) {
                if ( is_array($v) ) {
                    $queue[] = [ $v, $level + 1 ];
                }
            }
        }

        return $depth;
    }


    /**
     * > создать массив из ключей и заполнить значениями, если значение не передать заполнит цифрами по порядку
     */
    public function fill_keys(array $keys, array $new = []) : array
    {
        if ( [] === $keys ) {
            return [];
        }

        if ( [] !== $new ) {
            if ( ! array_key_exists(0, $new) ) {
                throw new LogicException(
                    [ 'Missing `new[0]` array key', $new ]
                );
            }

            $result = array_fill_keys($keys, $new[0]);

        } else {
            $result = [];

            $i = 0;
            foreach ( $keys as $key ) {
                $result[$key] = ++$i;
            }
        }

        return $result;
    }


    /**
     * > выбросить/заменить указанные ключи
     */
    public function drop_keys(array $src, $keys, array $replace = []) : array
    {
        if ( [] === $src ) {
            return $src;
        }

        $keys = (array) $keys;

        if ( [] === $keys ) {
            return $src;
        }

        $hasReplace = ([] !== $replace);

        $copy = $src;

        foreach ( (array) $keys as $key ) {
            if ( ! array_key_exists($key, $src) ) {
                continue;
            }

            if ( $hasReplace ) {
                $copy[$key] = $replace[0];

            } else {
                unset($copy[$key]);
            }
        }

        return $copy;
    }

    /**
     * > выбросить значения по фильтру, по сути array_filter(!function)
     *
     * @param callable|null $fn
     */
    public function drop(array $src, $fn = null, array $replace = [], ?int $flags = null) : array
    {
        if ( null === $fn ) {
            return $src;
        }

        if ( [] === $src ) {
            return $src;
        }

        $mode = null
            ?? $flags
            ?? static::staticFnMode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        $hasReplace = array_key_exists(0, $replace);

        $copy = $src;

        foreach ( $copy as $key => $val ) {
            $args = [];
            if ( $isUseValue ) $args[] = $val;
            if ( $isUseKey ) $args[] = $key;
            if ( $isUseSrc ) $args[] = $copy;

            if ( call_user_func_array($fn, $args) ) {
                if ( $hasReplace ) {
                    $copy[$key] = $replace[0];

                } else {
                    unset($copy[$key]);
                }
            }
        }

        return $copy;
    }


    /**
     * > оставить в массиве указанные ключи, остальные заменить
     */
    public function keep_keys(array $src, $keys, array $replace = []) : array
    {
        if ( [] === $src ) {
            return $src;
        }

        $keys = (array) $keys;

        if ( [] === $keys ) {
            return [];
        }

        $hasReplace = array_key_exists(0, $replace);

        $copy = $src;

        $keysToKeep = array_fill_keys(
            array_flip($keys),
            true
        );

        foreach ( $copy as $key => $val ) {
            if ( ! isset($keysToKeep[$key]) ) {
                if ( $hasReplace ) {
                    $copy[$key] = $replace[0];

                } else {
                    unset($copy[$key]);
                }
            }
        }

        return $copy;
    }

    /**
     * > оставить в массиве значения, что прошли фильтр, остальные выбросить. По сути array_filter()
     *
     * @param callable|null $fn
     */
    public function keep(array $src, $fn = null, array $replace = [], ?int $flags = null) : array
    {
        if ( null === $fn ) {
            return [];
        }

        if ( [] === $src ) {
            return [];
        }

        $mode = null
            ?? $flags
            ?? static::staticFnMode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        $hasReplace = array_key_exists(0, $replace);

        $copy = $src;

        foreach ( $copy as $key => $val ) {
            $args = [];
            if ( $isUseValue ) $args[] = $val;
            if ( $isUseKey ) $args[] = $key;
            if ( $isUseSrc ) $args[] = $copy;

            if ( call_user_func_array($fn, $args) ) {
                continue;
            }

            if ( $hasReplace ) {
                $copy[$key] = $replace[0];

            } else {
                unset($copy[$key]);
            }
        }

        return $copy;
    }


    /**
     * > выполнить array_filter с учетом _array_fn_mode()
     *
     * @param callable|null $fn
     */
    public function filter(array $src, $fn = null, ?int $flags = null) : array
    {
        if ( null === $fn ) {
            return $src;
        }

        if ( [] === $src ) {
            return $src;
        }

        $mode = null
            ?? $flags
            ?? static::staticFnMode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        $copy = $src;

        foreach ( $copy as $key => $val ) {
            $args = [];
            if ( $isUseValue ) $args[] = $val;
            if ( $isUseKey ) $args[] = $key;
            if ( $isUseSrc ) $args[] = $copy;

            if ( call_user_func_array($fn, $args) ) {
                continue;
            }

            unset($copy[$key]);
        }

        return $copy;
    }

    /**
     * > выполнить array_map с учетом _array_fn_mode()
     *
     * @param callable|null $fn
     */
    public function map(array $src, $fn = null, ?int $flags = null) : array
    {
        if ( null === $fn ) {
            return $src;
        }

        if ( [] === $src ) {
            return $src;
        }

        $mode = null
            ?? $flags
            ?? static::staticFnMode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        $copy = $src;

        foreach ( $copy as $key => $val ) {
            $args = [];
            if ( $isUseValue ) $args[] = $val;
            if ( $isUseKey ) $args[] = $key;
            if ( $isUseSrc ) $args[] = $src;

            $copy[$key] = call_user_func_array($fn, $args);
        }

        return $copy;
    }

    /**
     * > выполнить обход/map, не изменяя значения в массиве, с учетом _array_fn_mode()
     *
     * @param callable|null $fn
     */
    public function tap(array $src, $fn = null, ?int $flags = null)
    {
        if ( null === $fn ) {
            return $src;
        }

        if ( [] === $src ) {
            return $src;
        }

        $mode = null
            ?? $flags
            ?? static::staticFnMode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        $copy = $src;

        foreach ( $copy as $key => $val ) {
            $args = [];
            if ( $isUseValue ) $args[] = $val;
            if ( $isUseKey ) $args[] = $key;
            if ( $isUseSrc ) $args[] = $copy;

            call_user_func_array($fn, $args);
        }

        return $copy;
    }

    /**
     * > выполнить array_reduce с учетом _array_fn_mode()
     *
     * @template-covariant T of mixed
     *
     * @param callable|null $fn
     *
     * @param T             $initial
     *
     * @return T|mixed
     */
    public function reduce(array $src, $fn = null, $initial = null, ?int $flags = null)
    {
        if ( null === $fn ) {
            return $initial;
        }

        if ( [] === $src ) {
            return $initial;
        }

        $mode = null
            ?? $flags
            ?? static::staticFnMode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        $copy = $src;

        $current = $initial;
        foreach ( $copy as $key => $val ) {
            $args = [];
            $args[] = $current;
            if ( $isUseValue ) $args[] = $val;
            if ( $isUseKey ) $args[] = $key;
            if ( $isUseSrc ) $args[] = $copy;

            $current = call_user_func_array($fn, $args);
        }

        return $current;
    }


    /**
     * > разбивает массив на два, где в первом все цифровые ключи (список), во втором - все буквенные (словарь)
     *
     * @return array{
     *     0: array<int, mixed>,
     *     1: array<string, mixed>
     * }
     */
    public function kwargs(array $src) : array
    {
        if ( [] === $src ) {
            return [ [], [] ];
        }

        $list = [];
        $dict = [];

        foreach ( $src as $key => $val ) {
            is_int($key)
                ? ($list[$key] = $val)
                : ($dict[$key] = $val);
        }

        return [ $list, $dict ];
    }

    /**
     * > разбивает массив на два по критерию
     *
     * @param callable $fn
     *
     * @return array{0: array, 1: array}
     */
    public function both(array $src, $fn = null, ?int $flags = null) : array
    {
        if ( null === $fn ) {
            return [ $src, [] ];
        }

        if ( [] === $src ) {
            return [ [], [] ];
        }

        $left = [];
        $right = [];

        $mode = null
            ?? $flags
            ?? static::staticFnMode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        foreach ( $src as $key => $val ) {
            $args = [];
            if ( $isUseValue ) $args[] = $val;
            if ( $isUseKey ) $args[] = $key;
            if ( $isUseSrc ) $args[] = $src;

            call_user_func_array($fn, $args)
                ? ($left[$key] = $val)
                : ($right[$key] = $val);
        }

        return [ $left, $right ];
    }

    /**
     * > разбивает массив на группы, колбэк должен вернуть имена групп и значение попадет в эти группы
     *
     * @param callable $fn
     *
     * @return array<string, array>
     */
    public function group(array $src, $fn = null, ?int $flags = null) : array
    {
        if ( null === $fn ) {
            return [ '' => $src ];
        }

        if ( [] === $src ) {
            return [ '' => [] ];
        }

        $mode = null
            ?? $flags
            ?? static::staticFnMode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        $result = [];

        foreach ( $src as $key => $val ) {
            $args = [];
            if ( $isUseValue ) $args[] = $val;
            if ( $isUseKey ) $args[] = $key;
            if ( $isUseSrc ) $args[] = $src;

            $groupNames = call_user_func_array($fn, $args);

            foreach ( (array) $groupNames as $groupName ) {
                $result[$groupName][$key] = $val;
            }
        }

        return $result;
    }


    public function is_unique_keys(array ...$arrays) : bool
    {
        $seen = [];

        foreach ( $arrays as $arr ) {
            if ( [] === $arr ) {
                continue;
            }

            foreach ( array_keys($arr) as $k ) {
                if ( isset($seen[$k]) ) {
                    return false;
                }

                $seen[$k] = true;
            }
        }

        return true;
    }


    public function is_diff_key(array ...$arrays) : bool
    {
        if ( [] === $arrays ) {
            return false;
        }

        if ( 1 === count($arrays) ) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $array ) {
            foreach ( array_keys($array) as $ii ) {
                if ( ! array_key_exists($ii, $src) ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function is_diff(array ...$arrays) : bool
    {
        if ( [] === $arrays ) {
            return false;
        }

        if ( 1 === count($arrays) ) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $array ) {
            foreach ( $array as $v ) {
                if ( ! in_array($v, $src, true) ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function is_diff_non_strict(array ...$arrays) : bool
    {
        if ( [] === $arrays ) {
            return false;
        }

        if ( 1 === count($arrays) ) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $array ) {
            foreach ( $array as $v ) {
                if ( ! in_array($v, $src) ) {
                    return true;
                }
            }
        }

        return false;
    }


    public function is_intersect_key(array ...$arrays) : bool
    {
        if ( [] === $arrays ) {
            return false;
        }

        if ( 1 === count($arrays) ) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $array ) {
            foreach ( array_keys($array) as $ii ) {
                if ( array_key_exists($ii, $src) ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function is_intersect(array ...$arrays) : bool
    {
        if ( [] === $arrays ) {
            return false;
        }

        if ( 1 === count($arrays) ) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $array ) {
            foreach ( $array as $v ) {
                if ( in_array($v, $src, true) ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function is_intersect_non_strict(array ...$arrays) : bool
    {
        if ( [] === $arrays ) {
            return false;
        }

        if ( 1 === count($arrays) ) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $array ) {
            foreach ( $array as $v ) {
                if ( in_array($v, $src) ) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * > встроенная функция всегда требует именно два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function diff_key(array ...$arrays) : array
    {
        if ( [] === $arrays ) {
            return [];
        }

        if ( 1 === count($arrays) ) {
            return $arrays[0];
        }

        $result = array_diff_key(...$arrays);

        return $result;
    }

    /**
     * > встроенная функция всегда требует именно два массива на вход, вынуждая разруливать ифами то, что не нужно
     * > встроенная функция делает non-strict сравнение
     */
    public function diff(array ...$arrays) : array
    {
        if ( [] === $arrays ) {
            return [];
        }

        if ( 1 === count($arrays) ) {
            return $arrays[0];
        }

        $src = array_shift($arrays);

        foreach ( array_keys($arrays) as $i ) {
            sort($arrays[$i]);
        }

        foreach ( $src as $i => $v ) {
            foreach ( array_keys($arrays) as $ii ) {
                if ( in_array($v, $arrays[$ii], true) ) {
                    unset($src[$i]);

                    break;
                }
            }
        }

        return $src;
    }

    /**
     * > встроенная функция всегда требует именно два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function diff_non_strict(array ...$arrays) : array
    {
        if ( [] === $arrays ) {
            return [];
        }

        if ( 1 === count($arrays) ) {
            return $arrays[0];
        }

        $result = array_diff(...$arrays);

        return $result;
    }


    /**
     * > встроенная функция всегда требует два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function intersect_key(array ...$arrays) : array
    {
        if ( [] === $arrays ) {
            return [];
        }

        if ( 1 === count($arrays) ) {
            return $arrays[0];
        }

        $result = array_intersect_key(...$arrays);

        return $result;
    }

    /**
     * > встроенная функция всегда требует именно два массива на вход, вынуждая разруливать ифами то, что не нужно
     * > встроенная функция делает non-strict сравнение
     */
    public function intersect(array ...$arrays) : array
    {
        if ( [] === $arrays ) {
            return [];
        }

        if ( 1 === count($arrays) ) {
            return $arrays[0];
        }

        $src = array_shift($arrays);

        foreach ( array_keys($arrays) as $i ) {
            sort($arrays[$i]);
        }

        foreach ( $src as $i => $v ) {
            foreach ( array_keys($arrays) as $ii ) {
                if ( in_array($v, $arrays[$ii], true) ) {
                    continue 2;
                }
            }

            unset($src[$i]);
        }

        return $src;
    }

    /**
     * > встроенная функция всегда требует именно два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function intersect_non_strict(array ...$arrays) : array
    {
        if ( [] === $arrays ) {
            return [];
        }

        if ( 1 === count($arrays) ) {
            return $arrays[0];
        }

        $result = array_intersect(...$arrays);

        return $result;
    }


    /**
     * > превращает вложенный массив в одноуровневый, но теряет ключи
     */
    public function plain(?int $walkFlags, ...$values) : array
    {
        $walkFlags = $walkFlags ?? _ARR_WALK_WITH_EMPTY_ARRAYS;

        $result = [];

        foreach ( $this->walk_it($values, $walkFlags) as $value ) {
            $result[] = $value;
        }

        return $result;
    }


    /**
     * > превращает вложенный массив в одноуровневый, соединяя путь через точку
     */
    public function dot(
        array $array, ?string $dot = null, array $fillKeys = [],
        ?int $walkFlags = null
    ) : array
    {
        $walkFlags = $walkFlags ?? _ARR_WALK_WITH_EMPTY_ARRAYS;

        $theType = Lib::type();

        if ( null === $dot ) {
            $dotChar = '.';

        } else {
            $dotChar = $theType->char($dot)->orThrow();
        }

        $hasFillKeys = ([] !== $fillKeys);

        $result = [];

        $gen = $this->walk_it($array, $walkFlags);

        foreach ( $gen as $path => $value ) {
            $result[implode($dotChar, $path)] = $hasFillKeys
                ? $fillKeys[0]
                : $value;
        }

        return $result;
    }

    /**
     * > превращает одноуровневый массив с ключами-точками во вложенный
     */
    public function undot(array $arrayDot, ?string $dot = null) : array
    {
        $theType = Lib::type();

        if ( null === $dot ) {
            $dotChar = '.';

        } else {
            $dotChar = $theType->char($dot)->orThrow();
        }

        $result = [];

        foreach ( $arrayDot as $dotKey => $value ) {
            $this->put_path(
                $result,
                explode($dotChar, $dotKey),
                $value
            );
        }

        return $result;
    }


    /**
     * > превращает вложенный массив во вложенный объект
     * > отсутствующие ключи в объекте всегда бросают исключения, не нужно писать `isset()` или оператор `?? null`
     *
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function map_to_object(array $array, ?object $target = null) : object
    {
        $target = $target ?? new \stdClass();

        $stack = [
            [
                'array'  => $array,
                'object' => $target,
            ],
        ];

        $fnGet = function ($object, $key) {
            $object = $object ?? $this;

            return $object->{$key};
        };
        $fnSet = function ($object, $key, $value) {
            $object = $object ?? $this;

            $object->{$key} = $value;
        };

        while ( [] !== $stack ) {
            $current = array_pop($stack);

            $currentArray = $current['array'];
            $currentObject = $current['object'];

            foreach ( $currentArray as $key => $value ) {
                if ( ! is_array($value) ) {
                    $e = null;
                    $ee = null;
                    try {
                        $fnSet($currentObject, $key, $value);
                    }
                    catch ( \Throwable $e ) {
                        try {
                            $fnSet->call($currentObject, null, $key, $value);
                        }
                        catch ( \Throwable $ee ) {
                            throw new RuntimeException(
                                [ 'Unable to ' . __FUNCTION__ ], $ee, $e
                            );
                        }
                    }

                } else {
                    $childObject = null;

                    if ( property_exists($currentObject, $key) ) {
                        $e = null;
                        $ee = null;
                        try {
                            $var = $fnGet($currentObject, $key);
                        }
                        catch ( \Throwable $e ) {
                            try {
                                $var = $fnGet->call($currentObject, null, $key);
                            }
                            catch ( \Throwable $ee ) {
                                throw new RuntimeException(
                                    [ 'Unable to ' . __FUNCTION__ ], $ee, $e
                                );
                            }
                        }

                        if ( is_object($var) ) {
                            $childObject = $var;
                        }
                    }

                    if ( null === $childObject ) {
                        $childObject = new \stdClass();

                        $e = null;
                        $ee = null;
                        try {
                            $fnSet($currentObject, $key, $childObject);
                        }
                        catch ( \Throwable $e ) {
                            try {
                                $fnSet->call($currentObject, null, $key, $childObject);
                            }
                            catch ( \Throwable $ee ) {
                                throw new RuntimeException(
                                    [ 'Unable to ' . __FUNCTION__ ], $ee, $e
                                );
                            }
                        }
                    }

                    $stack[] = [
                        'array'  => $value,
                        'object' => $childObject,
                    ];
                }
            }
        }

        return $target;
    }


    /**
     * > Реализация array_walk_recursive, позволяющая:
     * > - получить путь до элемента
     * > - подменить значение по ссылке
     * > - сделать обход в ширину/глубину, т.е. (1 -> 1.1 -> 2 -> 2.1) || (1 -> 2 -> 1.1 -> 2.1)
     * > - выводить потомки | пустые-массивы | родители
     *
     * @template TKey of int|string
     * @template TValue
     *
     * @param array    $refArray
     * @param int|null $flags
     *
     * @return \Iterator<array<TKey>, TValue>|\Generator<array<TKey>, TValue>
     *
     * @noinspection PhpDocSignatureInspection
     */
    public function &walk_it(array &$refArray, ?int $flags = null) : \Generator
    {
        if ( [] === $refArray ) {
            return;
        }

        $flagsCurrent = $flags ?? 0;

        $flagGroups = [
            '_ARR_WALK_MODE'              => [
                [
                    _ARR_WALK_MODE_DEPTH_FIRST,
                    _ARR_WALK_MODE_BREADTH_FIRST,
                ],
                _ARR_WALK_MODE_DEPTH_FIRST,
            ],
            //
            '_ARR_WALK_SORT'              => [
                [
                    _ARR_WALK_SORT_SELF_FIRST,
                    _ARR_WALK_SORT_PARENT_FIRST,
                    _ARR_WALK_SORT_CHILD_FIRST,
                ],
                _ARR_WALK_SORT_SELF_FIRST,
            ],
            //
            '_ARR_WALK_WITH_LEAVES'       => [
                [
                    _ARR_WALK_WITH_LEAVES,
                    _ARR_WALK_WITHOUT_LEAVES,
                ],
                _ARR_WALK_WITH_LEAVES,
            ],
            //
            '_ARR_WALK_WITH_EMPTY_ARRAYS' => [
                [
                    _ARR_WALK_WITH_EMPTY_ARRAYS,
                    _ARR_WALK_WITHOUT_EMPTY_ARRAYS,
                ],
                _ARR_WALK_WITHOUT_EMPTY_ARRAYS,
            ],
            //
            '_ARR_WALK_WITH_PARENTS'      => [
                [
                    _ARR_WALK_WITH_PARENTS,
                    _ARR_WALK_WITHOUT_PARENTS,
                ],
                _ARR_WALK_WITHOUT_PARENTS,
            ],
            //
            '_ARR_WALK_WITH_DICTS'        => [
                [
                    _ARR_WALK_WITH_DICTS,
                    _ARR_WALK_WITHOUT_DICTS,
                ],
                _ARR_WALK_WITHOUT_DICTS,
            ],
            //
            '_ARR_WALK_WITH_LISTS'        => [
                [
                    _ARR_WALK_WITH_LISTS,
                    _ARR_WALK_WITHOUT_LISTS,
                ],
                _ARR_WALK_WITHOUT_LISTS,
            ],
        ];

        foreach ( $flagGroups as $groupName => [$conflict, $default] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ( $flagsCurrent & $flag ) {
                    $cnt++;
                }
            }

            if ( $cnt > 1 ) {
                throw new LogicException(
                    [ 'The `flags` conflict in group: ' . $groupName, $flags ]
                );

            } elseif ( 0 === $cnt ) {
                $flagsCurrent |= $default;
            }
        }

        $isModeDepthFirst = (bool) ($flagsCurrent & _ARR_WALK_MODE_DEPTH_FIRST);
        $isModeBreadthFirst = (bool) ($flagsCurrent & _ARR_WALK_MODE_BREADTH_FIRST);
        $isSortSelfFirst = (bool) ($flagsCurrent & _ARR_WALK_SORT_SELF_FIRST);
        $isSortParentFirst = (bool) ($flagsCurrent & _ARR_WALK_SORT_PARENT_FIRST);
        $isSortChildFirst = (bool) ($flagsCurrent & _ARR_WALK_SORT_CHILD_FIRST);
        $isWithLeaves = (bool) ($flagsCurrent & _ARR_WALK_WITH_LEAVES);
        $isWithDicts = (bool) ($flagsCurrent & _ARR_WALK_WITH_DICTS);
        $isWithEmptyArrays = (bool) ($flagsCurrent & _ARR_WALK_WITH_EMPTY_ARRAYS);
        $isWithLists = (bool) ($flagsCurrent & _ARR_WALK_WITH_LISTS);
        $isWithParents = (bool) ($flagsCurrent & _ARR_WALK_WITH_PARENTS);

        if ( $isSortSelfFirst ) {
            $fnUsort = null;

        } elseif ( $isSortParentFirst ) {
            $fnUsort = static function ($a, $b) {
                $isParentA = is_array($a) && ! empty($a);
                $isParentB = is_array($b) && ! empty($b);

                return $isParentA <=> $isParentB;
            };

        } elseif ( $isSortChildFirst ) {
            $fnUsort = static function ($a, $b) {
                $isParentA = is_array($a) && ! empty($a);
                $isParentB = is_array($b) && ! empty($b);

                return $isParentB <=> $isParentA;
            };

        } else {
            throw new LogicException([ 'Invalid `sort`', $flags ]);
        }

        if ( $isModeDepthFirst ) {
            $stack = [];
            $buffer =& $stack;

        } elseif ( $isModeBreadthFirst ) {
            $queue = [];
            $buffer =& $queue;

        } else {
            throw new LogicException([ 'Invalid `mode`', $flags ]);
        }

        // > ref, path
        $buffer[] = [ &$refArray, [] ];

        $isRoot = true;
        while ( ! empty($buffer) ) {
            $cur = [];

            if ( $isModeDepthFirst ) {
                $cur = array_pop($buffer);

            } elseif ( $isModeBreadthFirst ) {
                $cur = array_shift($buffer);
            }

            $cur0 = $cur[0];

            $isArray = is_array($cur0);

            $isLeaf =
            $isEmptyArray =
            $isParent =
            $isList =
            $isDict = false;

            if ( ! $isArray ) {
                $isLeaf = true;

            } elseif ( (! $isRoot) && ([] === $cur0) ) {
                $isEmptyArray = true;

            } elseif ( (! $isRoot) && ([] !== $cur0) ) {
                if ( $isWithLists ) {
                    $ret = $this->type_list(null, $cur0, 1);

                    $isList = $ret->isOk();
                }
                if ( $isWithDicts ) {
                    $ret = $this->type_dict(null, $cur0, 1);

                    $isDict = $ret->isOk();
                }

                $isParent = ! ($isList || $isDict);
            }

            if ( false
                || ($isWithLeaves && $isLeaf)
                || ($isWithDicts && $isDict)
                || ($isWithEmptyArrays && $isEmptyArray)
                || ($isWithLists && $isList)
                || ($isWithParents && $isParent)
            ) {
                $refCur0 =& $cur[0];

                yield $cur[1] => $refCur0;

                if ( $refCur0 !== $cur0 ) {
                    $isParent = is_array($refCur0) && ([] !== $refCur0);
                }

                unset($valueBefore);
                unset($refValueBefore);
            }

            if ( $isRoot || $isParent ) {
                $children = $cur0;

                if ( $fnUsort ) {
                    uasort($children, $fnUsort);
                }

                $keys = [];
                if ( $isModeDepthFirst ) {
                    $keys = array_reverse(array_keys($children));

                } elseif ( $isModeBreadthFirst ) {
                    $keys = array_keys($children);
                }

                unset($children);

                foreach ( $keys as $kk ) {
                    $fullpath = $cur[1];
                    $fullpath[] = $kk;

                    $buffer[] = [ &$cur[0][$kk], $fullpath ];
                }

                unset($keys);
            }

            $isRoot = false;
        }
    }

    /**
     * > Обход дерева $tree, в итераторе будет элемент из $list
     *
     * @template TKey of int|string
     * @template TValue
     *
     * @param array<TKey, array<TKey, bool>> $tree
     * @param array<TKey, TValue>|null       $list
     * @param TKey|null                      $start
     *
     * @return \Iterator<array<TKey>, TValue>|\Generator<array<TKey>, TValue>
     *
     * @noinspection PhpDocSignatureInspection
     */
    public function walk_tree_it(array $tree, ?array $list = null, $start = null) : \Generator
    {
        if ( null === $start ) {
            $start = null
                ?? (isset($tree[0]) ? 0 : null)
                ?? (isset($tree['']) ? '' : null);
        }

        if ( false
            || (null === $start)
            || ! isset($tree[$start])
        ) {
            return;
        }

        foreach ( $tree[$start] as $boolOrNull ) {
            if ( $boolOrNull && ! is_bool($boolOrNull) ) {
                throw new LogicException(
                    [ 'Each of `tree` values should be a null or a boolean, use keys instead (uniqueness)' ]
                );
            }
        }

        $stack[] = [ $start, [ $start ] ];

        while ( null !== key($stack) ) {
            [ $current, $path ] = array_pop($stack);

            $item = (null !== $list)
                ? ($list[$current] ?? null)
                : $current;

            if ( null !== $item ) {
                yield $path => $item;
            }

            if ( isset($tree[$current]) ) {
                $parents = array_keys($tree[$current]);

                foreach ( array_reverse($parents) as $parent ) {
                    $fullpath = $path;
                    $fullpath[] = $parent;

                    $stack[] = [ $parent, $fullpath ];
                }
            }
        }
    }

    /**
     * > Позволяет сделать add/merge/replace массивов рекурсивно в цикле foreach с получением пути до элемента
     *
     * @template TKey of int|string
     * @template TValue
     *
     * @return \Iterator<array<TKey>, array<TValue>>|\Generator<array<TKey>, array<TValue>>
     *
     * @throws \LogicException
     *
     * @noinspection PhpDocSignatureInspection
     */
    public function walk_collect_it(array $arrayList, ?int $arrayWalkFlags = null, array $fallback = []) : \Generator
    {
        $keyList = array_keys($arrayList);

        $generators = [];
        foreach ( $keyList as $key ) {
            if ( ! is_array($arrayList[$key]) ) {
                throw new LogicException(
                    [
                        'Each of `arrayList` should be an array',
                        $arrayList[$key],
                        $key,
                    ]
                );
            }

            $generators[$key] = $this->walk_it($arrayList[$key], $arrayWalkFlags);
        }

        $result = [];

        $pathes = [];
        while ( $generators ) {
            foreach ( $generators as $generatorKey => $generator ) {
                /** @var \Generator $generator */

                if ( ! $generator->valid() ) {
                    unset($generators[$generatorKey]);

                } else {
                    /** @var array $path */

                    $path = $generator->key();
                    $pathString = implode("\0", $path);

                    if ( ! isset($pathes[$pathString]) ) {
                        $yield = false;

                        $values = [];
                        foreach ( $keyList as $idx => $key ) {
                            $isFound = $this->has_path(
                                $arrayList[$idx], $path,
                                [ &$value ]
                            );

                            if ( $isFound || $fallback ) {
                                if ( ! $isFound ) {
                                    [ $value ] = $fallback;
                                }

                                $values[$key] = $value;

                                $yield = true;
                            }
                        }

                        if ( $yield ) {
                            yield $path => $values;
                        }

                        $pathes[$pathString] = true;
                    }

                    $generator->next();
                }
            }
        }

        return $result;
    }
}
