<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Arr\ArrPath;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class ArrModule
{
    /**
     * @var bool
     */
    protected $fn_mode;


    public function static_fn_mode(?int $fn_mode = null) : ?int
    {
        if (null !== $fn_mode) {
            if ($fn_mode) {
                if (0 === ($fn_mode & ~_ARR_FN_USE_ALL)) {
                    throw new LogicException(
                        [
                            'The `fn_mode` should be valid flags',
                            $fn_mode,
                            _ARR_FN_USE_ALL,
                        ]
                    );
                }
            }

            $last = $this->fn_mode;

            $current = $fn_mode;

            $this->fn_mode = $current;

            $result = $last;
        }

        $result = $result ?? $this->fn_mode;

        return $result;
    }


    /**
     * @param mixed|null $result
     */
    public function type_key_exists(&$result, $value, $key) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        if ([] === $value) {
            return false;
        }

        if (! array_key_exists($key, $value)) {
            return false;
        }

        $result = $value[ $key ];

        return true;
    }


    /**
     * @param array|null $result
     */
    public function type_array_plain(&$result, $value) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        if ([] === $value) {
            return true;
        }

        foreach ( $value as $v ) {
            if (is_array($v) && ([] !== $v)) {
                return false;
            }
        }

        $result = $value;

        return true;
    }


    /**
     * @param array|null $result
     */
    public function type_list(&$result, $value, ?bool $isPlain = null) : bool
    {
        $result = null;

        $isPlain = $isPlain ?? false;

        if (! is_array($value)) {
            return false;
        }

        if ([] === $value) {
            $result = $value;

            return true;
        }

        if ($isPlain) {
            foreach ( $value as $key => $v ) {
                if (is_string($key)) {
                    return false;
                }

                if (is_array($v) && ([] !== $v)) {
                    return false;
                }
            }

        } else {
            foreach ( array_keys($value) as $key ) {
                if (is_string($key)) {
                    return false;
                }
            }
        }

        $result = $value;

        return true;
    }

    /**
     * @param array|null $result
     */
    public function type_list_sorted(&$result, $value, ?bool $isPlain = null) : bool
    {
        $result = null;

        $isPlain = $isPlain ?? false;

        if (! is_array($value)) {
            return false;
        }

        if ([] === $value) {
            $result = $value;

            return true;
        }

        $prev = -1;

        if ($isPlain) {
            foreach ( $value as $key => $v ) {
                if (is_string($key)) {
                    return false;
                }

                if (($key - $prev) !== 1) {
                    return false;
                }

                if (is_array($v) && ([] !== $v)) {
                    return false;
                }

                $prev = $key;
            }

        } else {
            foreach ( array_keys($value) as $key ) {
                if (is_string($key)) {
                    return false;
                }

                if (($key - $prev) !== 1) {
                    return false;
                }

                $prev = $key;
            }
        }

        $result = $value;

        return true;
    }


    /**
     * @param array|null $result
     */
    public function type_dict(&$result, $value, ?bool $isPlain = null) : bool
    {
        $result = null;

        $isPlain = $isPlain ?? false;

        if (! is_array($value)) {
            return false;
        }

        if ([] === $value) {
            $result = $value;

            return true;
        }

        if ($isPlain) {
            foreach ( $value as $key => $v ) {
                if (is_int($key)) {
                    return false;
                }

                if (is_array($v) && ([] !== $v)) {
                    return false;
                }
            }

        } else {
            foreach ( array_keys($value) as $key ) {
                if (is_int($key)) {
                    return false;
                }
            }
        }

        $result = $value;

        return true;
    }

    /**
     * @param array|null $result
     * @param callable   $fnCmp
     */
    public function type_dict_sorted(&$result, $value, ?bool $isPlain = null, $fnCmp = null) : bool
    {
        $result = null;

        $isPlain = $isPlain ?? false;
        $fnCmp = $fnCmp ?? 'strcmp';

        if (! is_array($value)) {
            return false;
        }

        if ([] === $value) {
            $result = $value;

            return true;
        }

        $prev = '';

        if ($isPlain) {
            foreach ( $value as $key => $v ) {
                if (is_int($key)) {
                    return false;
                }

                if (is_array($v) && ([] !== $v)) {
                    return false;
                }

                $cmp = call_user_func($fnCmp, $prev, $key);

                if (! is_int($cmp)) {
                    return false;
                }

                if ($cmp < 0) {
                    return false;
                }

                $prev = $key;
            }

        } else {
            foreach ( array_keys($value) as $key ) {
                if (is_int($key)) {
                    return false;
                }

                $cmp = call_user_func($fnCmp, $prev, $key);

                if (! is_int($cmp)) {
                    return false;
                }

                if ($cmp < 0) {
                    return false;
                }

                $prev = $key;
            }
        }

        $result = $value;

        return true;
    }


    /**
     * @param array|null $result
     */
    public function type_table(&$result, $value) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        $columns = [];
        for ( $i = 0; $i < count($value); $i++ ) {
            if (! is_array($value[ $i ])) {
                return false;
            }
        }

        $result = $value;

        return true;
    }

    /**
     * @param array|null $result
     */
    public function type_matrix(&$result, $value) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        for ( $i = 0; $i < count($value); $i++ ) {
            if (! $this->type_list($var, $value[ $i ])) {
                return false;
            }
        }

        $result = $value;

        return true;
    }

    /**
     * @param array|null $result
     */
    public function type_matrix_strict(&$result, $value) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        for ( $i = 0; $i < count($value); $i++ ) {
            if (! $this->type_list_sorted($var, $value[ $i ])) {
                return false;
            }
        }

        $result = $value;

        return true;
    }


    /**
     * @param ArrPath|null $result
     */
    public function type_arrpath(&$result, $value, ?string $dot = null) : bool
    {
        $result = null;

        if ($value instanceof ArrPath) {
            $result = $value;

            return true;
        }

        try {
            $array = (null !== $dot)
                ? $this->arrpath_dot($dot, $value)
                : $this->arrpath($value);

            $result = ArrPath::fromValidArray($array);

            return true;
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }


    public function has_key($array, $key, array $refs = []) : bool
    {
        $withValue = array_key_exists(0, $refs);

        if ($withValue) {
            $refValue =& $refs[ 0 ];
        }

        $refValue = null;

        if (! is_array($array)) {
            return false;
        }

        if (! Lib::type()->string($_key, $key)) {
            return false;
        }

        if (! array_key_exists($_key, $array)) {
            return false;
        }

        $refValue = $array[ $_key ];
        unset($refValue);

        return true;
    }

    /**
     * @throws \RuntimeException
     */
    public function get_key(array $array, $key, array $fallback = [])
    {
        $status = $this->has_key($array, $key, [ &$value ]);

        if (! $status) {
            if ($fallback) {
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
        if (PHP_VERSION_ID >= 70300) {
            return array_key_first($array);

        } else {
            reset($array);

            return key($array);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function first(array $array, array $fallback = [])
    {
        if ([] === $array) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                'Missing first element in array'
            );
        }

        if (PHP_VERSION_ID >= 70300) {
            return $array[ array_key_first($array) ];
        }

        $first = reset($array);

        return $first;
    }


    /**
     * @return int|string|null
     */
    public function key_last(array $array)
    {
        if (PHP_VERSION_ID >= 70300) {
            return array_key_last($array);

        } else {
            end($array);

            return key($array);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function last(array $array, array $fallback = [])
    {
        if ([] === $array) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                'Missing first element in array'
            );
        }

        if (PHP_VERSION_ID >= 70300) {
            return $array[ array_key_last($array) ];
        }

        $last = end($array);

        return $last;
    }


    public function key_next(array $src) : int
    {
        $arr = array_fill_keys(
            array_keys($src),
            true
        );

        $arr[] = true;

        if (PHP_VERSION_ID >= 70300) {
            $lastKey = array_key_last($arr);

        } else {
            end($src);

            $lastKey = key($arr);
        }

        return $lastKey;
    }


    public function has_pos(
        $array, $pos,
        array $refs = []
    ) : bool
    {
        $withValue = array_key_exists(0, $refs);
        $withKey = array_key_exists(1, $refs);

        if ($withValue) {
            $refValue =& $refs[ 0 ];
        }
        if ($withKey) {
            $refKey =& $refs[ 1 ];
        }

        $refValue = null;
        $refKey = null;

        if (! is_array($array)) {
            return false;
        }

        if (! is_int($pos)) {
            return false;
        }

        $isNegativePos = ($pos < 0);

        $copyArray = $array;

        if ($isNegativePos) {
            end($copyArray);

            $abs = abs($pos) - 1;

        } else {
            reset($copyArray);

            $abs = abs($pos);
        }

        while ( null !== ($k = key($copyArray)) ) {
            if (0 === $abs) {
                $refValue = $array[ $k ];
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

        if ($status) {
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

        if (! $status) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                'Missing pos in array: ' . $pos
            );
        }

        return $value;
    }


    /**
     * @return string[]
     */
    public function arrpath($path, ...$pathes) : array
    {
        $theType = Lib::type();

        $arrpath = [];

        $gen = $this->arrpath_it($path, ...$pathes);

        foreach ( $gen as $p ) {
            if ($theType->string($var, $p)) {
                $arrpath[] = $p;
            }
        }

        if ([] === $arrpath) {
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
     * @return string[]
     */
    public function arrpath_dot(string $dot, $path, ...$pathes) : array
    {
        if (! Lib::str()->type_char($symbol, $dot)) {
            throw new LogicException(
                'The `dot` should be one symbol',
                $dot
            );
        }

        $theType = Lib::type();

        $arrpath = [];

        $gen = $this->arrpath_it($path, ...$pathes);

        foreach ( $gen as $p ) {
            if ($theType->string($pString, $p)) {
                if ('' === $pString) {
                    $arrpath[] = $pString;

                } else {
                    $arrpath = array_merge(
                        $arrpath,
                        explode($dot, $pString)
                    );
                }
            }
        }

        if ([] === $arrpath) {
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
        if ([] === $pathes) {
            if ($path instanceof ArrPath) {
                foreach ( $path->getPath() as $p ) {
                    yield $p;
                }

                return;
            }
        }

        array_unshift($pathes, $path);

        $gen = $this->walk_it($pathes);

        $list = [];

        foreach ( $gen as $genPath => $p ) {
            if ($p instanceof ArrPath) {
                foreach ( $p->getPath() as $pp ) {
                    yield $pp;
                }

            } else {
                yield $p;
            }
        }
    }


    public function has_path(
        array $array, $path,
        array $refs = []
    ) : bool
    {
        if (! $this->type_arrpath($thePath, $path)) {
            return false;
        }

        $withValue = array_key_exists(0, $refs);
        $withKey = array_key_exists(1, $refs);

        if ($withValue) {
            $refValue =& $refs[ 0 ];
        }
        if ($withKey) {
            $refKey =& $refs[ 1 ];
        }

        $refValue = null;
        $refKey = null;

        $pathArray = $thePath->getPath();

        $refCurrent =& $array;

        $isFound = true;
        $pathStep = null;

        while ( $pathArray ) {
            $pathStep = array_shift($pathArray);

            if (! array_key_exists($pathStep, $refCurrent)) {
                $isFound = false;

                $pathStep = null;

                unset($refCurrent);
                $refCurrent = null;

                break;
            }

            $refCurrent =& $refCurrent[ $pathStep ];

            if ((! is_array($refCurrent)) && $pathArray) {
                $isFound = false;

                $pathStep = null;

                unset($refCurrent);
                $refCurrent = null;

                break;
            }
        }

        if ($isFound) {
            $refValue = $refCurrent;
            $refKey = $pathStep;
        }

        unset($refValue);
        unset($refKey);

        return $isFound;
    }


    /**
     * @throws \LogicException|\RuntimeException
     */
    public function &fetch_path(array &$array, $path)
    {
        if (! $this->type_arrpath($thePath, $path)) {
            throw new LogicException(
                'Unable to ' . __FUNCTION__ . ' due to invalid path'
            );
        }

        $pathArray = $thePath->getPath();

        $refCurrent =& $array;

        $isFound = true;

        $pathStep = null;

        while ( $pathArray ) {
            $pathStep = array_shift($pathArray);

            if (! array_key_exists($pathStep, $refCurrent)) {
                unset($refCurrent);
                $refCurrent = null;

                if ([] === $pathArray) {
                    throw new RuntimeException(
                        [
                            'Unable to ' . __FUNCTION__ . ': missing key in array',
                            $pathStep,
                            $path,
                        ]
                    );
                }
            }

            $refCurrent =& $refCurrent[ $pathStep ];

            if ((! is_array($refCurrent)) && $pathArray) {
                unset($refCurrent);
                $refCurrent = null;

                throw new RuntimeException(
                    [
                        'Unable to ' . __FUNCTION__ . ': trying to traverse scalar value',
                        $pathStep,
                        $path,
                    ]
                );
            }
        }

        return $refCurrent;
    }

    /**
     * @throws \RuntimeException
     */
    public function get_path(array $array, $path, array $fallback = [])
    {
        $status = $this->has_path($array, $path, [ &$value ]);

        if (! $status) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                [
                    'Missing array path',
                    $path,
                ]
            );
        }

        return $value;
    }


    /**
     * @throws \LogicException|\RuntimeException
     */
    public function &put_path(array &$array, $path, $value)
    {
        if (! $this->type_arrpath($thePath, $path)) {
            throw new LogicException(
                'Unable to ' . __FUNCTION__ . ' due to invalid path'
            );
        }

        $pathArray = $thePath->getPath();

        $refCurrent =& $array;

        while ( null !== key($pathArray) ) {
            $pathStep = array_shift($pathArray);

            if (! array_key_exists($pathStep, $refCurrent)) {
                $refCurrent[ $pathStep ] = $pathArray
                    ? []
                    : null;
            }

            $refCurrent =& $refCurrent[ $pathStep ];

            if ((! is_array($refCurrent)) && $pathArray) {
                unset($refCurrent);
                $refCurrent = null;

                throw new RuntimeException(
                    [
                        'Unable to ' . __FUNCTION__ . ': trying to traverse scalar value',
                        $pathStep,
                        $path,
                    ]
                );
            }
        }

        $refCurrent = $value;

        return $refCurrent;
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function set_path(array &$array, $path, $value) : void
    {
        $this->put_path($array, $path, $value);
    }


    /**
     * @throws \LogicException
     */
    public function unset_path(array &$array, $path) : bool
    {
        if (! $this->type_arrpath($thePath, $path)) {
            throw new LogicException(
                'Unable to ' . __FUNCTION__ . ' due to invalid path'
            );
        }

        $pathArray = $thePath->getPath();

        $refCurrent =& $array;

        $isDeleted = false;

        $pathStep = null;

        $refPrevious = null;
        foreach ( $pathArray as $pathStep ) {
            $refPrevious =& $refCurrent;

            if (! is_array($refCurrent)) {
                unset($refPrevious);

                break;
            }

            if (! array_key_exists($pathStep, $refCurrent)) {
                unset($refPrevious);

                break;
            }

            $refCurrent = &$refCurrent[ $pathStep ];
        }

        if (
            isset($refPrevious)
            && (
                isset($refPrevious[ $pathStep ])
                || array_key_exists($pathStep, $refPrevious)
            )
        ) {
            unset($refPrevious[ $pathStep ]);

            $isDeleted = true;
        }

        unset($pathStep);

        unset($refPrevious);
        $refPrevious = null;

        unset($refCurrent);
        $refCurrent = null;

        return $isDeleted;
    }


    /**
     * > создать массив из ключей и заполнить значениями, если значение не передать заполнит цифрами по порядку
     */
    public function fill_keys(array $keys, array $new = []) : array
    {
        if ([] === $keys) {
            return [];
        }

        $hasNew = ([] !== $new);

        if ($hasNew) {
            $result = array_fill_keys($keys, $new[ 0 ]);

        } else {
            $result = [];

            $i = 0;
            foreach ( $keys as $key ) {
                $result[ $key ] = ++$i;
            }
        }

        return $result;
    }


    /**
     * > выбросить/заменить указанные ключи
     */
    public function drop_keys(array $src, $keys, array $new = []) : array
    {
        $keys = (array) $keys;

        if ([] === $keys) {
            return $src;
        }

        $hasNew = ([] !== $new);

        foreach ( (array) $keys as $key ) {
            if (! array_key_exists($key, $src)) {
                continue;
            }

            if ($hasNew) {
                $src[ $key ] = $new[ 0 ];

            } else {
                unset($src[ $key ]);
            }
        }

        return $src;
    }

    /**
     * > оставить в массиве указанные ключи, остальные заменить
     */
    public function keep_keys(array $src, $keys, array $new = []) : array
    {
        $keys = (array) $keys;

        if ([] === $keys) {
            return [];
        }

        $hasNew = ([] !== $new);

        $keysToKeep = array_flip($keys);

        foreach ( $src as $key => $val ) {
            if (! isset($keysToKeep[ $key ])) {
                if ($hasNew) {
                    $src[ $key ] = $new[ 0 ];

                } else {
                    unset($src[ $key ]);
                }
            }
        }

        return $src;
    }


    /**
     * > выполнить array_filter с учетом _array_fn_mode()
     *
     * @param callable|null $fn
     */
    public function filter(array $src, $fn = null, ?int $flags = null) : array
    {
        if (null === $fn) {
            return $src;
        }

        if ([] === $src) {
            return [];
        }

        $filtered = $this->keep($src, $fn, $flags);

        return $filtered;
    }

    /**
     * > выполнить array_map с учетом _array_fn_mode()
     *
     * @param callable|null $fn
     */
    public function tap(array $src, $fn = null, ?int $flags = null) : void
    {
        if (null === $fn) {
            return;
        }

        if ([] === $src) {
            return;
        }

        $mode = null
            ?? $flags
            ?? $this->static_fn_mode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($isUseValue) $args[] = $val;
            if ($isUseKey) $args[] = $key;
            if ($isUseSrc) $args[] = $src;

            call_user_func_array($fn, $args);
        }
    }

    /**
     * > выполнить array_map с учетом _array_fn_mode()
     *
     * @param callable|null $fn
     */
    public function map(array $src, $fn = null, ?int $flags = null) : array
    {
        if (null === $fn) {
            return $src;
        }

        if ([] === $src) {
            return [];
        }

        $mode = null
            ?? $flags
            ?? $this->static_fn_mode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($isUseValue) $args[] = $val;
            if ($isUseKey) $args[] = $key;
            if ($isUseSrc) $args[] = $src;

            $src[ $key ] = call_user_func_array($fn, $args);
        }

        return $src;
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
        if (null === $fn) {
            return $initial;
        }

        if ([] === $src) {
            return $initial;
        }

        $mode = null
            ?? $flags
            ?? $this->static_fn_mode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        $current = $initial;
        foreach ( $src as $key => $val ) {
            $args = [];
            $args[] = $current;
            if ($isUseValue) $args[] = $val;
            if ($isUseKey) $args[] = $key;
            if ($isUseSrc) $args[] = $src;

            $current = call_user_func_array($fn, $args);
        }

        return $current;
    }


    /**
     * > оставить в массиве значения, что прошли фильтр, остальные выбросить. По сути array_filter()
     *
     * @param callable|null $fn
     */
    public function keep(array $src, $fn = null, ?int $flags = null) : array
    {
        if (null === $fn) {
            return [];
        }

        if ([] === $src) {
            return [];
        }

        $mode = null
            ?? $flags
            ?? $this->static_fn_mode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($isUseValue) $args[] = $val;
            if ($isUseKey) $args[] = $key;
            if ($isUseSrc) $args[] = $src;

            if (call_user_func_array($fn, $args)) {
                continue;
            }

            unset($src[ $key ]);
        }

        return $src;
    }

    /**
     * > оставить в массиве значения, что прошли фильтр, остальные заменить. По сути array_filter() с заменой на NULL
     *
     * @param callable|null $fn
     */
    public function keep_new(array $src, $new = null, $fn = null, ?int $flags = null) : array
    {
        if ([] === $src) {
            return [];
        }

        if (null === $fn) {
            foreach ( $src as $key => $val ) {
                $src[ $key ] = $new;
            }

            return $src;
        }

        $mode = null
            ?? $flags
            ?? $this->static_fn_mode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($isUseValue) $args[] = $val;
            if ($isUseKey) $args[] = $key;
            if ($isUseSrc) $args[] = $src;

            if (call_user_func_array($fn, $args)) {
                continue;
            }

            $src[ $key ] = $new;
        }

        return $src;
    }


    /**
     * > выбросить значения по фильтру, по сути array_filter(!function)
     *
     * @param callable|null $fn
     */
    public function drop(array $src, $fn = null, ?int $flags = null) : array
    {
        if (null === $fn) {
            return $src;
        }

        if ([] === $src) {
            return [];
        }

        $mode = null
            ?? $flags
            ?? $this->static_fn_mode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($isUseValue) $args[] = $val;
            if ($isUseKey) $args[] = $key;
            if ($isUseSrc) $args[] = $src;

            if (call_user_func_array($fn, $args)) {
                unset($src[ $key ]);
            }
        }

        return $src;
    }

    /**
     * > заменить значения по фильтру, по сути array_filter(!function) с заменой на null
     *
     * @param callable|null $fn
     */
    public function drop_new(array $src, $new = null, $fn = null, ?int $flags = null) : array
    {
        if (null === $fn) {
            return $src;
        }

        if ([] === $src) {
            return [];
        }

        $mode = null
            ?? $flags
            ?? $this->static_fn_mode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($isUseValue) $args[] = $val;
            if ($isUseKey) $args[] = $key;
            if ($isUseSrc) $args[] = $src;

            if (call_user_func_array($fn, $args)) {
                $src[ $key ] = $new;
            }
        }

        return $src;
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
        if ([] === $src) {
            return [ [], [] ];
        }

        $list = [];
        $dict = [];

        foreach ( $src as $key => $val ) {
            is_int($key)
                ? ($list[ $key ] = $val)
                : ($dict[ $key ] = $val);
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
        if (null === $fn) {
            return [ $src, [] ];
        }

        if ([] === $src) {
            return [ [], [] ];
        }

        $left = [];
        $right = [];

        $mode = null
            ?? $flags
            ?? $this->static_fn_mode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($isUseValue) $args[] = $val;
            if ($isUseValue) $args[] = $key;
            if ($isUseSrc) $args[] = $src;

            call_user_func_array($fn, $args)
                ? ($left[ $key ] = $val)
                : ($right[ $key ] = $val);
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
        if (null === $fn) {
            return [ '' => $src ];
        }

        if ([] === $src) {
            return [ '' => [] ];
        }

        $mode = null
            ?? $flags
            ?? $this->static_fn_mode()
            ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

        $result = [];

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($isUseValue) $args[] = $val;
            if ($isUseKey) $args[] = $key;
            if ($isUseSrc) $args[] = $src;

            $groupNames = call_user_func_array($fn, $args);

            foreach ( (array) $groupNames as $groupName ) {
                $result[ $groupName ][ $key ] = $val;
            }
        }

        return $result;
    }


    /**
     * > строит индекс ключей (int)
     * > [ 0 => 1, 2 => true, 3 => false ] -> [ 1 => true, 2 => true, 3 => false ]
     *
     * @return array<int, bool>
     */
    public function index_int(array $array, array ...$arrays) : array
    {
        array_unshift($arrays, $array);

        $index = array_merge(...$arrays);

        $result = [];

        foreach ( $index as $k => $v ) {
            if (is_int($v)) {
                $key = $v;

                $result[ $key ] = true;

            } elseif (! isset($result[ $k ])) {
                $key = $k;

                $v = (bool) $v;

                if ($v) {
                    $result[ $key ] = true;
                }
            }
        }

        return $result;
    }

    /**
     * > строит индекс ключей (string)
     * > [ 0 => 'key1', 'key2' => true, 'key3' => false ] -> [ 'key1' => true, 'key2' => true, 'key3' => false ]
     *
     * @return array<string, bool>
     */
    public function index_string(array $array, array ...$arrays) : array
    {
        array_unshift($arrays, $array);

        $index = array_merge(...$arrays);

        $result = [];

        foreach ( $index as $k => $v ) {
            if (is_string($k) && ($k !== '')) {
                $key = $k;

                $v = (bool) $v;

                if ($v) {
                    $result[ $key ] = true;
                }

            } elseif (is_string($v) && ! isset($result[ $v ])) {
                $key = $v;

                $result[ $key ] = true;
            }
        }

        return $result;
    }


    public function is_unique_keys(array ...$arrays) : bool
    {
        $seen = [];

        foreach ( $arrays as $arr ) {
            if ([] === $arr) {
                continue;
            }

            foreach ( array_keys($arr) as $k ) {
                if (isset($seen[ $k ])) {
                    return false;
                }

                $seen[ $k ] = true;
            }
        }

        return true;
    }


    public function is_diff_key(array ...$arrays) : bool
    {
        if ([] === $arrays) {
            return false;
        }

        if (1 === count($arrays)) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $i => $array ) {
            foreach ( array_keys($array) as $ii ) {
                if (! array_key_exists($ii, $src)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function is_diff(array ...$arrays) : bool
    {
        if ([] === $arrays) {
            return false;
        }

        if (1 === count($arrays)) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $i => $array ) {
            foreach ( $array as $ii => $v ) {
                if (! in_array($v, $src, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function is_diff_non_strict(array ...$arrays) : bool
    {
        if ([] === $arrays) {
            return false;
        }

        if (1 === count($arrays)) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $i => $array ) {
            foreach ( $array as $ii => $v ) {
                if (! in_array($v, $src)) {
                    return true;
                }
            }
        }

        return false;
    }


    public function is_intersect_key(array ...$arrays) : bool
    {
        if ([] === $arrays) {
            return false;
        }

        if (1 === count($arrays)) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $i => $array ) {
            foreach ( array_keys($array) as $ii ) {
                if (array_key_exists($ii, $src)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function is_intersect(array ...$arrays) : bool
    {
        if ([] === $arrays) {
            return false;
        }

        if (1 === count($arrays)) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $i => $array ) {
            foreach ( $array as $ii => $v ) {
                if (in_array($v, $src, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function is_intersect_non_strict(array ...$arrays) : bool
    {
        if ([] === $arrays) {
            return false;
        }

        if (1 === count($arrays)) {
            return false;
        }

        $src = array_shift($arrays);
        sort($src);

        foreach ( $arrays as $i => $array ) {
            foreach ( $array as $ii => $v ) {
                if (in_array($v, $src)) {
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
        if ([] === $arrays) {
            return [];
        }

        if (1 === count($arrays)) {
            return $arrays[ 0 ];
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
        if ([] === $arrays) {
            return [];
        }

        if (1 === count($arrays)) {
            return $arrays[ 0 ];
        }

        $src = array_shift($arrays);

        foreach ( array_keys($arrays) as $i ) {
            sort($arrays[ $i ]);
        }

        foreach ( $src as $i => $v ) {
            foreach ( array_keys($arrays) as $ii ) {
                if (in_array($v, $arrays[ $ii ], true)) {
                    unset($src[ $i ]);

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
        if ([] === $arrays) {
            return [];
        }

        if (1 === count($arrays)) {
            return $arrays[ 0 ];
        }

        $result = array_diff(...$arrays);

        return $result;
    }


    /**
     * > встроенная функция всегда требует два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function intersect_key(array ...$arrays) : array
    {
        if ([] === $arrays) {
            return [];
        }

        if (1 === count($arrays)) {
            return $arrays[ 0 ];
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
        if ([] === $arrays) {
            return [];
        }

        if (1 === count($arrays)) {
            return $arrays[ 0 ];
        }

        $src = array_shift($arrays);

        foreach ( array_keys($arrays) as $i ) {
            sort($arrays[ $i ]);
        }

        foreach ( $src as $i => $v ) {
            foreach ( array_keys($arrays) as $ii ) {
                if (in_array($v, $arrays[ $ii ], true)) {
                    continue 2;
                }
            }

            unset($src[ $i ]);
        }

        return $src;
    }

    /**
     * > встроенная функция всегда требует именно два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function intersect_non_strict(array ...$arrays) : array
    {
        if ([] === $arrays) {
            return [];
        }

        if (1 === count($arrays)) {
            return $arrays[ 0 ];
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

        if (null === $dot) {
            $dot = '.';

        } elseif (! Lib::str()->type_char($symbol, $dot)) {
            throw new LogicException(
                [
                    'The `dot` should be one symbol',
                    $dot,
                ]
            );
        }

        $hasFillKeys = ([] !== $fillKeys);

        $result = [];

        $gen = $this->walk_it($array, $walkFlags);

        foreach ( $gen as $path => $value ) {
            $result[ implode($dot, $path) ] = $hasFillKeys
                ? $fillKeys[ 0 ]
                : $value;
        }

        return $result;
    }

    /**
     * > превращает одноуровневый массив с ключами-точками во вложенный
     */
    public function undot(array $arrayDot, ?string $dot = null) : array
    {
        if (null === $dot) {
            $dot = '.';

        } elseif (! Lib::str()->type_char($symbol, $dot)) {
            throw new LogicException(
                'The `dot` should be one symbol',
                $dot
            );
        }

        $result = [];

        foreach ( $arrayDot as $dotKey => $value ) {
            $this->set_path(
                $result,
                explode($dot, $dotKey),
                $value
            );
        }

        return $result;
    }


    /**
     * > превращает вложенный массив во вложенный объект
     * > отсутствующие ключи в объекте всегда бросают исключения, не нужно писать `isset()` или оператор `?? null`
     */
    public function map_to_object(array $array, object $target = null) : object
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

            $currentArray = $current[ 'array' ];
            $currentObject = $current[ 'object' ];

            foreach ( $currentArray as $key => $value ) {
                if (! is_array($value)) {
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

                    if (property_exists($currentObject, $key)) {
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

                        if (is_object($var)) {
                            $childObject = $var;
                        }
                    }

                    if (null === $childObject) {
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
     * @param array    $array
     * @param int|null $flags
     *
     * @return \Iterator<array<TKey>, TValue>|\Generator<array<TKey>, TValue>
     */
    public function &walk_it(array &$array, ?int $flags = null) : \Generator
    {
        if ([] === $array) {
            return;
        }

        $flags = $flags ?? _ARR_WALK_WITH_LEAVES;

        $isModeDepthFirst = (bool) ($flags & _ARR_WALK_MODE_DEPTH_FIRST);
        $isModeBreadthFirst = (bool) ($flags & _ARR_WALK_MODE_BREADTH_FIRST);
        $sum = (int) ($isModeDepthFirst + $isModeBreadthFirst);
        if (1 !== $sum) {
            $isModeDepthFirst = true;
            $isModeBreadthFirst = false;
        }
        unset($sum);

        $isSortSelfFirst = (bool) ($flags & _ARR_WALK_SORT_SELF_FIRST);
        $isSortParentFirst = (bool) ($flags & _ARR_WALK_SORT_PARENT_FIRST);
        $isSortChildFirst = (bool) ($flags & _ARR_WALK_SORT_CHILD_FIRST);
        $sum = (int) ($isSortSelfFirst + $isSortParentFirst + $isSortChildFirst);
        if (1 !== $sum) {
            $isSortSelfFirst = true;
            $isSortParentFirst = false;
            $isSortChildFirst = false;
        }
        unset($sum);

        $isWithLeaves = (bool) ($flags & _ARR_WALK_WITH_LEAVES);
        $isWithoutLeaves = (bool) ($flags & _ARR_WALK_WITHOUT_LEAVES);
        $sum = (int) ($isWithLeaves + $isWithoutLeaves);
        if (1 !== $sum) {
            $isWithLeaves = true;
            $isWithoutLeaves = false;
        }
        unset($sum);

        $isWithDicts = (bool) ($flags & _ARR_WALK_WITH_DICTS);
        $isWithoutDicts = (bool) ($flags & _ARR_WALK_WITHOUT_DICTS);
        $sum = (int) ($isWithDicts + $isWithoutDicts);
        if (1 !== $sum) {
            $isWithDicts = false;
            $isWithoutDicts = false;
        }
        unset($sum);

        $isWithEmptyArrays = (bool) ($flags & _ARR_WALK_WITH_EMPTY_ARRAYS);
        $isWithoutEmptyArrays = (bool) ($flags & _ARR_WALK_WITHOUT_EMPTY_ARRAYS);
        $sum = (int) ($isWithEmptyArrays + $isWithoutEmptyArrays);
        if (1 !== $sum) {
            $isWithEmptyArrays = false;
            $isWithoutEmptyArrays = false;
        }
        unset($sum);

        $isWithLists = (bool) ($flags & _ARR_WALK_WITH_LISTS);
        $isWithoutLists = (bool) ($flags & _ARR_WALK_WITHOUT_LISTS);
        $sum = (int) ($isWithLists + $isWithoutLists);
        if (1 !== $sum) {
            $isWithLists = false;
            $isWithoutLists = false;
        }
        unset($sum);

        $isWithParents = (bool) ($flags & _ARR_WALK_WITH_PARENTS);
        $isWithoutParents = (bool) ($flags & _ARR_WALK_WITHOUT_PARENTS);
        $sum = (int) ($isWithParents + $isWithoutParents);
        if (1 !== $sum) {
            $isWithParents = false;
            $isWithoutParents = false;
        }
        unset($sum);

        if ($isSortSelfFirst) {
            $fnUsort = null;

        } elseif ($isSortParentFirst) {
            $fnUsort = static function ($a, $b) {
                $isParentA = is_array($a) && ! empty($a);
                $isParentB = is_array($b) && ! empty($b);

                return $isParentA <=> $isParentB;
            };

        } elseif ($isSortChildFirst) {
            $fnUsort = static function ($a, $b) {
                $isParentA = is_array($a) && ! empty($a);
                $isParentB = is_array($b) && ! empty($b);

                return $isParentB <=> $isParentA;
            };

        } else {
            throw new LogicException([ 'Invalid `sort`', $flags ]);
        }

        if ($isModeDepthFirst) {
            $stack = [];
            $buffer =& $stack;

        } elseif ($isModeBreadthFirst) {
            $queue = [];
            $buffer =& $queue;

        } else {
            throw new LogicException([ 'Invalid `mode`', $flags ]);
        }

        $theArr = Lib::arr();

        // > ref, path
        $buffer[] = [ &$array, [] ];

        $isRoot = true;
        while ( ! empty($buffer) ) {
            $cur = [];

            if ($isModeDepthFirst) {
                $cur = array_pop($buffer);

            } elseif ($isModeBreadthFirst) {
                $cur = array_shift($buffer);
            }

            $cur0 = $cur[ 0 ];

            $isArray = is_array($cur0);

            $isLeaf = $isEmptyArray = $isParent = $isList = $isDict = false;

            if (! $isArray) {
                $isLeaf = true;

            } elseif ((! $isRoot) && ([] === $cur0)) {
                $isEmptyArray = true;

            } elseif ((! $isRoot) && ([] !== $cur0)) {
                if ($isWithLists) {
                    $isList = $theArr->type_list($var, $cur0, true);
                }
                if ($isWithDicts) {
                    $isDict = $theArr->type_dict($var, $cur0, true);
                }

                $isParent = ! ($isList || $isDict);
            }

            if (
                ($isWithLeaves && $isLeaf)
                || ($isWithDicts && $isDict)
                || ($isWithEmptyArrays && $isEmptyArray)
                || ($isWithLists && $isList)
                || ($isWithParents && $isParent)
            ) {
                $refCur0 =& $cur[ 0 ];

                yield $cur[ 1 ] => $refCur0;

                if ($refCur0 !== $cur0) {
                    $isParent = is_array($refCur0) && ([] !== $refCur0);
                }

                unset($valueBefore);
                unset($refValueBefore);
            }

            if ($isRoot || $isParent) {
                $children = $cur0;

                if ($fnUsort) {
                    uasort($children, $fnUsort);
                }

                $keys = [];
                if ($isModeDepthFirst) {
                    $keys = array_reverse(array_keys($children));

                } elseif ($isModeBreadthFirst) {
                    $keys = array_keys($children);
                }

                unset($children);

                foreach ( $keys as $kk ) {
                    $fullpath = $cur[ 1 ];
                    $fullpath[] = $kk;

                    $buffer[] = [ &$cur[ 0 ][ $kk ], $fullpath ];
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
     */
    public function walk_tree_it(array $tree, ?array $list = null, $start = null) : \Generator
    {
        if (null === $start) {
            $start = null
                ?? (isset($tree[ 0 ]) ? 0 : null)
                ?? (isset($tree[ '' ]) ? '' : null);
        }

        if ((null === $start) || ! isset($tree[ $start ])) {
            return;
        }

        foreach ( $tree[ $start ] as $boolOrNull ) {
            if ($boolOrNull && ! is_bool($boolOrNull)) {
                throw new LogicException('Each of `tree` values should be null or boolean, use keys instead (uniqueness)');
            }
        }

        $stack[] = [ $start, [ $start ] ];

        while ( null !== key($stack) ) {
            [ $current, $path ] = array_pop($stack);

            $item = (null !== $list)
                ? ($list[ $current ] ?? null)
                : $current;

            if (null !== $item) {
                yield $path => $item;
            }

            if (isset($tree[ $current ])) {
                $parents = array_keys($tree[ $current ]);

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
     * @throws \LogicException
     */
    public function walk_collect_it(array $arrayList, ?int $arrayWalkFlags = null, array $fallback = []) : \Generator
    {
        $keyList = array_keys($arrayList);

        $generators = [];
        foreach ( $keyList as $key ) {
            if (! is_array($arrayList[ $key ])) {
                throw new LogicException(
                    [
                        'Each of `arrayList` must be array',
                        $arrayList[ $key ],
                        $key,
                    ]
                );
            }

            $generators[ $key ] = $this->walk_it($arrayList[ $key ], $arrayWalkFlags);
        }

        $result = [];

        $pathes = [];
        while ( $generators ) {
            foreach ( $generators as $generatorKey => $generator ) {
                /** @var \Generator $generator */

                if (! $generator->valid()) {
                    unset($generators[ $generatorKey ]);

                } else {
                    /** @var array $path */

                    $path = $generator->key();
                    $pathString = implode("\0", $path);

                    if (! isset($pathes[ $pathString ])) {
                        $yield = false;

                        $values = [];
                        foreach ( $keyList as $idx => $key ) {
                            $isFound = $this->has_path(
                                $arrayList[ $idx ], $path,
                                [ &$value ]
                            );

                            if ($isFound || $fallback) {
                                if (! $isFound) {
                                    [ $value ] = $fallback;
                                }

                                $values[ $key ] = $value;

                                $yield = true;
                            }
                        }

                        if ($yield) {
                            yield $path => $values;
                        }

                        $pathes[ $pathString ] = true;
                    }

                    $generator->next();
                }
            }
        }

        return $result;
    }
}
