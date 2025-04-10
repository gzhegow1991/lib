<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Arr\ArrPath;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class ArrModule
{
    /**
     * @param array|null $result
     */
    public function type_list(&$result, $value) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        foreach ( array_keys($value) as $key ) {
            if (is_string($key)) {
                return false;
            }
        }

        $result = $value;

        return true;
    }

    /**
     * @param array|null $result
     */
    public function type_list_sorted(&$result, $value) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        $keys = array_keys($value);

        foreach ( $keys as $key ) {
            if (is_string($key)) {
                return false;
            }
        }

        if ($keys !== range(0, count($value))) {
            return false;
        }

        $result = $value;

        return true;
    }


    /**
     * @param array|null $result
     */
    public function type_dict(&$result, $value) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        foreach ( array_keys($value) as $key ) {
            if (is_int($key)) {
                return false;
            }
        }

        $result = $value;

        return true;
    }

    /**
     * @param array|null $result
     */
    public function type_dict_sorted(&$result, $value) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        $keys = array_keys($value);

        foreach ( $keys as $key ) {
            if (is_int($key)) {
                return false;
            }
        }

        $keysSorted = $keys;
        sort($keysSorted);

        if ($keys !== $keysSorted) {
            return false;
        }

        $result = $value;

        return true;
    }


    /**
     * @param array|null $result
     */
    public function type_index_list(&$result, $value) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        foreach ( array_keys($value) as $key ) {
            if (is_string($key)) {
                return false;
            }

            if (0 === $key) {
                return false;
            }
        }

        $result = $value;

        return true;
    }

    /**
     * @param array|null $result
     */
    public function type_index_dict(&$result, $value) : bool
    {
        $result = null;

        if (! is_array($value)) {
            return false;
        }

        foreach ( array_keys($value) as $key ) {
            if (is_int($key)) {
                return false;
            }

            if ('' === $key) {
                return false;
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
    public function type_arrpath(&$result, $value, string $dot = null) : bool
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

            $result = ArrPath::fromValid($array);

            return true;
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }


    public function has_key($array, $key, array $refs = []) : bool
    {
        $withValue = array_key_exists(0, $refs);

        $refValue = null;

        if ($withValue) {
            $refValue =& $refs[ 0 ];
            $refValue = null;
        }

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
    public function get_key(array $array, $key, array $fallback = []) // : ?mixed
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


    public function key_first(array $array) // : ?int|string
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
    public function first(array $array, array $fallback = []) // : ?mixed
    {
        if (0 === count($array)) {
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


    public function key_last(array $array) // : ?int|string
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
    public function last(array $array, array $fallback = []) // : ?mixed
    {
        if (0 === count($array)) {
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

        $refValue = null;
        $refKey = null;

        if ($withValue) {
            $refValue =& $refs[ 0 ];
            $refValue = null;
        }
        if ($withKey) {
            $refKey =& $refs[ 1 ];
            $refKey = null;
        }

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
    public function key_pos(array $array, int $pos) // : ?int|string
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


    public function arrpath($path, ...$pathes) : array
    {
        array_unshift($pathes, $path);

        if (1 === count($pathes)) {
            if ($path instanceof ArrPath) {
                return $path->getPath();
            }
        }

        $gen = $this->walk_it($pathes);

        $theType = Lib::type();

        $arrpath = [];

        foreach ( $gen as $genPath => $p ) {
            if ($p instanceof ArrPath) {
                $result = array_merge($arrpath, $p->getPath());

            } else {
                if (! $theType->string($pString, $p)) {
                    throw new LogicException(
                        [
                            'Each of `pathes` should be non-null stringable or instance of: ' . ArrPath::class,
                            $p,
                            $genPath,
                        ]
                    );
                }

                $arrpath[] = $pString;
            }
        }

        if (! count($arrpath)) {
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

    public function arrpath_dot(string $dot, $path, ...$pathes) : array
    {
        if (! Lib::str()->type_char($symbol, $dot)) {
            throw new LogicException(
                'The `dot` should be one symbol',
                $dot
            );
        }

        array_unshift($pathes, $path);

        if (1 === count($pathes)) {
            if ($path instanceof ArrPath) {
                return $path->getPath();
            }
        }

        $gen = $this->walk_it($pathes);

        $theType = Lib::type();

        $arrpath = [];

        foreach ( $gen as $genPath => $p ) {
            if ($p instanceof ArrPath) {
                $result = array_merge($arrpath, $p->getPath());

            } else {
                if (! $theType->string($pString, $p)) {
                    throw new LogicException(
                        [
                            'Each of `pathes` should be non-null stringable or instance of: ' . ArrPath::class,
                            $p,
                            $genPath,
                        ]
                    );
                }

                $arrpath = array_merge(
                    $arrpath,
                    explode($dot, $pString)
                );
            }
        }

        if (! count($arrpath)) {
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

        $refValue = null;
        $refKey = null;

        if ($withValue) {
            $refValue =& $refs[ 0 ];
            $refValue = null;
        }
        if ($withKey) {
            $refKey =& $refs[ 1 ];
            $refKey = null;
        }

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
    public function &fetch_path(array &$array, $path) // : mixed
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

                if (0 === count($pathArray)) {
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
    public function &put_path(array &$array, $path, $value) // : &mixed
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
    public function fill_keys($keys, array $new = []) : array
    {
        $keys = (array) $keys;
        if (0 === count($keys)) {
            return [];
        }

        $hasNew = (0 !== count($new));

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
        if (0 === count($keys)) {
            return $src;
        }

        $hasNew = (0 !== count($new));

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
        if (0 === count($keys)) {
            return [];
        }

        $hasNew = (0 !== count($new));

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


    public function fn_mode($mode = '') : ?int
    {
        static $modeCurrent;

        if ('' !== $mode) {
            if (null !== $mode) {
                if (! is_int($mode)) {
                    throw new LogicException(
                        [
                            'The `mode` should be int',
                            $mode,
                        ]
                    );
                }

                if ($mode > 0b111) {
                    throw new LogicException(
                        [
                            'The `mode` should be less than: ' . decbin(0b111),
                            $mode,
                        ]
                    );
                }
            }

            $modeCurrent = $mode;
        }

        return $modeCurrent;
    }

    /**
     * > выполнить array_map с учетом _array_fn_mode()
     *
     * @param callable|null $fn
     */
    public function map(array $src, $fn = null) : array
    {
        if (null === $fn) {
            return [];
        }

        $mode = $this->fn_mode() ?? _ARR_FN_USE_VALUE;

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
     * > выполнить array_filter с учетом _array_fn_mode()
     *
     * @param callable|null $fn
     */
    public function filter(array $src, $fn = null) : array
    {
        return $this->keep($src, $fn);
    }


    /**
     * > оставить в массиве значения, что прошли фильтр, остальные выбросить. По сути array_filter()
     *
     * @param callable|null $fn
     */
    public function keep(array $src, $fn = null) : array
    {
        if (null === $fn) {
            return [];
        }

        $mode = $this->fn_mode() ?? _ARR_FN_USE_VALUE;

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
    public function keep_new(array $src, $new = null, $fn = null) : array
    {
        if (null === $fn) {
            foreach ( $src as $key => $val ) {
                $src[ $key ] = $new;
            }

            return $src;
        }

        $mode = $this->fn_mode() ?? _ARR_FN_USE_VALUE;

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
    public function drop(array $src, $fn = null) : array
    {
        if (null === $fn) {
            return $src;
        }

        $mode = $this->fn_mode() ?? _ARR_FN_USE_VALUE;

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
    public function drop_new(array $src, $new = null, $fn = null) : array
    {
        if (null === $fn) {
            return $src;
        }

        $mode = $this->fn_mode() ?? _ARR_FN_USE_VALUE;

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
    public function kwargs(array $src = null) : array
    {
        if (! isset($src)) return [];

        $list = [];
        $dict = [];

        foreach ( $src as $idx => $val ) {
            is_int($idx)
                ? ($list[ $idx ] = $val)
                : ($dict[ $idx ] = $val);
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
    public function both(array $src, $fn) : array
    {
        $left = [];
        $right = [];

        $mode = $this->fn_mode() ?? _ARR_FN_USE_VALUE;

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
    public function group(array $src, $fn) : array
    {
        $result = [];

        $mode = $this->fn_mode() ?? _ARR_FN_USE_VALUE;

        $isUseValue = ($mode & _ARR_FN_USE_VALUE);
        $isUseKey = ($mode & _ARR_FN_USE_KEY);
        $isUseSrc = ($mode & _ARR_FN_USE_SRC);

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


    /**
     * > встроенная функция всегда требует два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function diff(array ...$arrays) : array
    {
        if (0 === count($arrays)) {
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
    public function diff_key(array ...$arrays) : array
    {
        if (0 === count($arrays)) {
            return [];
        }

        if (1 === count($arrays)) {
            return $arrays[ 0 ];
        }

        $result = array_diff_key(...$arrays);

        return $result;
    }

    /**
     * > встроенная функция всегда требует два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function intersect(array ...$arrays) : array
    {
        if (0 === count($arrays)) {
            return [];
        }

        if (1 === count($arrays)) {
            return $arrays[ 0 ];
        }

        $result = array_intersect(...$arrays);

        return $result;
    }

    /**
     * > встроенная функция всегда требует два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function intersect_key(array ...$arrays) : array
    {
        if (0 === count($arrays)) {
            return [];
        }

        if (1 === count($arrays)) {
            return $arrays[ 0 ];
        }

        $result = array_intersect_key(...$arrays);

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
        array $array, string $dot = null,
        int $walkFlags = null
    ) : array
    {
        $walkFlags = $walkFlags ?? _ARR_WALK_WITH_EMPTY_ARRAYS;

        if (null === $dot) {
            $dot = '.';

        } elseif (! Lib::str()->type_char($symbol, $dot)) {
            throw new LogicException(
                'The `dot` should be one symbol',
                $dot
            );
        }

        $result = [];

        $gen = $this->walk_it($array, $walkFlags);

        foreach ( $gen as $path => $value ) {
            $result[ implode($dot, $path) ] = $value;
        }

        return $result;
    }

    /**
     * > превращает вложенный массив в одноуровневый, соединяя путь через точку
     * > вместо значений использует $value
     */
    public function dot_keys(
        array $array, string $dot = null, $value = null,
        int $walkFlags = null
    ) : array
    {
        $value = $value ?? true;
        $walkFlags = $walkFlags ?? _ARR_WALK_WITH_EMPTY_ARRAYS;

        if (null === $dot) {
            $dot = '.';

        } elseif (! Lib::str()->type_char($symbol, $dot)) {
            throw new LogicException(
                'The `dot` should be one symbol',
                $dot
            );
        }

        $result = [];

        $gen = $this->walk_it($array, $walkFlags);

        foreach ( $gen as $path => $devnull ) {
            $result[ implode($dot, $path) ] = $value;
        }

        return $result;
    }

    /**
     * > превращает одноуровневый массив с ключами-точками во вложенный
     */
    public function undot(array $arrayDot, string $dot = null) : array
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
    public function &walk_it(array &$array, int $flags = null) : \Generator
    {
        if (0 === count($array)) return;

        $flags = $flags ?? 0;

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
        $withLeaves = $isWithLeaves && ! $isWithoutLeaves;
        unset($sum);

        $isWithoutEmptyArrays = (bool) ($flags & _ARR_WALK_WITHOUT_EMPTY_ARRAYS);
        $isWithEmptyArrays = (bool) ($flags & _ARR_WALK_WITH_EMPTY_ARRAYS);
        $sum = (int) ($isWithoutEmptyArrays + $isWithEmptyArrays);
        if (1 !== $sum) {
            $isWithoutEmptyArrays = true;
            $isWithEmptyArrays = false;
        }
        $withEmptyArrays = $isWithEmptyArrays && ! $isWithoutEmptyArrays;
        unset($sum);

        $isWithoutParents = (bool) ($flags & _ARR_WALK_WITHOUT_PARENTS);
        $isWithParents = (bool) ($flags & _ARR_WALK_WITH_PARENTS);
        $sum = (int) ($isWithoutParents + $isWithParents);
        if (1 !== $sum) {
            $isWithoutParents = true;
            $isWithParents = false;
        }
        $withParents = $isWithParents && ! $isWithoutParents;
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

        // > valueRef, fullpath, force
        $buffer[] = [ &$array, [], false ];

        $isRoot = true;
        while ( ! empty($buffer) ) {
            $cur = [];
            if ($isModeDepthFirst) {
                $cur = array_pop($buffer);

            } elseif ($isModeBreadthFirst) {
                $cur = array_shift($buffer);
            }

            $isYieldLeaf = ! is_array($cur[ 0 ]);
            $isYieldParent = ! $isRoot && ! $isYieldLeaf && ! empty($cur[ 0 ]);
            $isYieldEmptyArray = ! $isRoot && ! $isYieldLeaf && empty($cur[ 0 ]);

            if (
                ($withLeaves && $isYieldLeaf)
                || ($withParents && $isYieldParent)
                || ($withEmptyArrays && $isYieldEmptyArray)
            ) {
                $valueBefore = $cur[ 0 ];
                $valueReference =& $cur[ 0 ];

                yield $cur[ 1 ] => $valueReference;

                if ($valueReference !== $valueBefore) {
                    $isYieldParent = is_array($valueReference) && ! empty($valueReference);
                }

                unset($valueBefore);
                unset($valueReference);
                $valueReference = null;
            }

            if ($isRoot || $isYieldParent) {
                $children = $cur[ 0 ];

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
    public function walk_tree_it(array $tree, array $list = null, $start = null) : \Generator
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
    public function walk_collect_it(array $arrayList, int $arrayWalkFlags = null, array $fallback = []) : \Generator
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
