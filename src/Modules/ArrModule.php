<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


if (! defined('_ARRAY_FN_USE_VALUE')) define('_ARRAY_FN_USE_VALUE', 1 << 0);
if (! defined('_ARRAY_FN_USE_KEY')) define('_ARRAY_FN_USE_KEY', 1 << 1);
if (! defined('_ARRAY_FN_USE_SRC')) define('_ARRAY_FN_USE_SRC', 1 << 2);

if (! defined('_ARRAY_WALK_MODE_DEPTH_FIRST')) define('_ARRAY_WALK_MODE_DEPTH_FIRST', 1 << 0);
if (! defined('_ARRAY_WALK_MODE_BREADTH_FIRST')) define('_ARRAY_WALK_MODE_BREADTH_FIRST', 1 << 1);
if (! defined('_ARRAY_WALK_SORT_SELF_FIRST')) define('_ARRAY_WALK_SORT_SELF_FIRST', 1 << 2);
if (! defined('_ARRAY_WALK_SORT_PARENT_FIRST')) define('_ARRAY_WALK_SORT_PARENT_FIRST', 1 << 3);
if (! defined('_ARRAY_WALK_SORT_CHILD_FIRST')) define('_ARRAY_WALK_SORT_CHILD_FIRST', 1 << 4);
if (! defined('_ARRAY_WALK_WITH_LEAVES')) define('_ARRAY_WALK_WITH_LEAVES', 1 << 5);
if (! defined('_ARRAY_WALK_WITHOUT_LEAVES')) define('_ARRAY_WALK_WITHOUT_LEAVES', 1 << 6);
if (! defined('_ARRAY_WALK_WITH_EMPTY_ARRAYS')) define('_ARRAY_WALK_WITH_EMPTY_ARRAYS', 1 << 7);
if (! defined('_ARRAY_WALK_WITHOUT_EMPTY_ARRAYS')) define('_ARRAY_WALK_WITHOUT_EMPTY_ARRAYS', 1 << 8);
if (! defined('_ARRAY_WALK_WITH_PARENTS')) define('_ARRAY_WALK_WITH_PARENTS', 1 << 9);
if (! defined('_ARRAY_WALK_WITHOUT_PARENTS')) define('_ARRAY_WALK_WITHOUT_PARENTS', 1 << 10);


class ArrModule
{
    public function has(
        $array, $key,
        &$value = null, &$reference = null
    ) : bool
    {
        $value = null;
        $reference = null;

        if (! is_array($array)) {
            return false;
        }

        if (! (is_int($key) || is_string($key))) {
            return false;
        }

        if (! array_key_exists($key, $array)) {
            return false;
        }

        $value = $array[ $key ];
        $reference =& $array[ $key ];

        return true;
    }

    /**
     * @throws \RuntimeException
     */
    public function get($array, $key, array $fallback = []) // : ?mixed
    {
        $status = $this->has($array, $key, $value);

        if (! $status) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                'Missing key in array: ' . $key
            );
        }

        return $value;
    }


    public function key_first(array $src) // : ?int|string
    {
        reset($src);

        return key($src);
    }

    /**
     * @throws \RuntimeException
     */
    public function first(array $src, array $fallback = []) // : ?mixed
    {
        if (! $src) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                'Missing first element in array'
            );
        }

        $first = reset($src);

        return $first;
    }


    public function key_last(array $src) // : ?int|string
    {
        end($src);

        return key($src);
    }

    /**
     * @throws \RuntimeException
     */
    public function last(array $src, array $fallback = []) // : ?mixed
    {
        if (! $src) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                'Missing first element in array'
            );
        }

        $last = end($src);

        return $last;
    }


    public function key_next(array $src) : int
    {
        $arr = $src;
        $arr[] = true;

        return $this->key_last($arr);
    }


    public function has_pos(
        array $src, int $pos,
        &$value = null, &$key = null, &$reference = null
    ) : bool
    {
        $value = null;
        $key = null;
        $reference = null;

        if ($pos < 0) {
            end($src);

            $abs = abs($pos) - 1;

        } else {
            reset($src);

            $abs = abs($pos);
        }

        while ( null !== ($k = key($src)) ) {
            if (! $abs--) {
                $value = $src[ $k ];
                $key = $k;
                $reference =& $src[ $k ];

                return true;

            } else {
                ($pos < 0)
                    ? prev($src)
                    : next($src);
            }
        }

        return false;
    }

    /**
     * @return int|string|null
     */
    public function key_pos(array $src, int $pos) // : ?int|string
    {
        $status = $this->has_pos($src, $pos, $value, $key);

        if ($status) {
            return $key;
        }

        return null;
    }

    /**
     * @throws \RuntimeException
     */
    public function get_pos(array $src, int $pos, array $fallback = [])
    {
        $status = $this->has_pos($src, $pos, $value, $key);

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


    public function path($path, ...$pathes) : array
    {
        $result = [];

        $array = [ $path, $pathes ];

        array_walk_recursive($array, function ($value) use (&$result) {
            if (null !== $value) {
                $result[] = Lib::parse()->string($value);
            }
        });

        return $result;
    }

    public function has_path(
        array $src, $path,
        &$value = null, &$key = null, &$reference = null
    ) : bool
    {
        $value = null;
        $key = null;
        $reference = null;

        $_path = $this->path($path);

        $ref =& $src;

        $found = true;
        $p = null;

        while ( $_path ) {
            $p = array_shift($_path);

            if (! array_key_exists($p, $ref)) {
                $found = false;
                $p = null;

                unset($ref);
                $ref = null;

                break;
            }

            $ref =& $ref[ $p ];

            if ((! is_array($ref)) && $_path) {
                $found = false;
                $p = null;

                unset($ref);
                $ref = null;

                break;
            }
        }

        if ($found) {
            $value = $ref;
            $key = $p;
            $reference =& $ref;

            return true;
        }

        return false;
    }

    /**
     * @throws \RuntimeException
     */
    public function get_path(
        array $src, $path, array $fallback = []
    )
    {
        $status = $this->has_path($src, $path, $value);

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
    public function &put_path(array &$dst, $path, $value) // : &mixed
    {
        $fullpath = $this->path($path);

        if (! $fullpath) {
            throw new LogicException(
                'Unable to ' . __FUNCTION__ . ' due to empty path'
            );
        }

        $ref =& $dst;

        while ( null !== key($fullpath) ) {
            $p = array_shift($fullpath);

            if (! array_key_exists($p, $ref)) {
                $ref[ $p ] = $fullpath
                    ? []
                    : null;
            }

            $ref =& $ref[ $p ];

            if ((! is_array($ref)) && $fullpath) {
                unset($ref);
                $ref = null;

                throw new RuntimeException(
                    [
                        "Trying to traverse scalar value",
                        $p,
                        $path,
                    ]
                );
            }
        }

        $ref = $value;

        return $ref;
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function set_path(array &$dst, $path, $value) : void
    {
        $this->put_path($dst, $path, $value);
    }

    /**
     * @throws \LogicException
     */
    public function unset_path(array &$src, $path) : bool
    {
        $fullpath = $this->path($path);

        if (! $fullpath) {
            throw new LogicException(
                'Unable to ' . __FUNCTION__ . ' due to empty path'
            );
        }

        $ref =& $src;

        $status = false;

        $p = null;
        $refPrev = null;
        foreach ( $fullpath as $p ) {
            $refPrev =& $ref;

            if (! is_array($ref)) {
                unset($refPrev);

                break;
            }

            if (! array_key_exists($p, $ref)) {
                unset($refPrev);

                break;
            }

            $ref = &$ref[ $p ];
        }

        if (
            isset($refPrev)
            && (isset($refPrev[ $p ]) || array_key_exists($p, $refPrev))
        ) {
            unset($refPrev[ $p ]);

            $status = true;
        }

        unset($p);

        unset($refPrev);
        $refPrev = null;

        unset($ref);
        $ref = null;

        return $status;
    }


    /**
     * > gzhegow, разбивает массив на два, где в первом все цифровые ключи (список), во втором - все буквенные (словарь)
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
     * > gzhegow, выбросить указанные ключи
     */
    public function drop_keys(array $src, $keys) : array
    {
        if (null === $keys) {
            return $src;
        }

        $_keysToRemove = (array) $keys;

        foreach ( $_keysToRemove as $key ) {
            if (! array_key_exists($key, $src)) {
                continue;
            }

            unset($src[ $key ]);
        }

        return $src;
    }

    /**
     * > gzhegow, заменить указанные ключи
     */
    public function drop_keys_new(array $src, $keys, $new = null) : array
    {
        if (null === $keys) {
            return $src;
        }

        $_keysToRemove = (array) $keys;

        foreach ( $_keysToRemove as $key ) {
            if (! array_key_exists($key, $src)) {
                continue;
            }

            $src[ $key ] = $new;
        }

        return $src;
    }

    /**
     * > gzhegow, оставить в массиве указанные ключи, остальные выбросить
     */
    public function keep_keys(array $src, $keys) : array
    {
        if (null === $keys) {
            return [];
        }

        $_keysToKeep = array_flip((array) $keys);

        foreach ( $src as $key => $val ) {
            if (! isset($_keysToKeep[ $key ])) {
                unset($src[ $key ]);
            }
        }

        return $src;
    }

    /**
     * > gzhegow, оставить в массиве указанные ключи, остальные заменить
     */
    public function keep_keys_new(array $src, $keys, $new = null) : array
    {
        if (null === $keys) {
            return [];
        }

        $_keysToKeep = array_flip((array) $keys);

        foreach ( $src as $key => $val ) {
            if (! isset($_keysToKeep[ $key ])) {
                $src[ $key ] = $new;
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
     * > gzhegow, выполнить array_map с учетом _array_fn_mode()
     *
     * @param callable|null $fn
     */
    public function map(array $src, $fn = null) : array
    {
        if (! $fn) {
            return [];
        }

        $mode = $this->fn_mode() ?? _ARRAY_FN_USE_VALUE;

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($mode & _ARRAY_FN_USE_VALUE) $args[] = $val;
            if ($mode & _ARRAY_FN_USE_KEY) $args[] = $key;
            if ($mode & _ARRAY_FN_USE_SRC) $args[] = $src;

            $src[ $key ] = call_user_func_array($fn, $args);
        }

        return $src;
    }

    /**
     * > gzhegow, выполнить array_filter с учетом _array_fn_mode()
     *
     * @param callable|null $fn
     */
    public function filter(array $src, $fn = null) : array
    {
        return $this->keep($src, $fn);
    }


    /**
     * > gzhegow, оставить в массиве значения, что прошли фильтр, остальные выбросить. По сути array_filter()
     *
     * @param callable|null $fn
     */
    public function keep(array $src, $fn = null) : array
    {
        if (! $fn) {
            return [];
        }

        $mode = $this->fn_mode() ?? _ARRAY_FN_USE_VALUE;

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($mode & _ARRAY_FN_USE_VALUE) $args[] = $val;
            if ($mode & _ARRAY_FN_USE_KEY) $args[] = $key;
            if ($mode & _ARRAY_FN_USE_SRC) $args[] = $src;

            if (call_user_func_array($fn, $args)) {
                continue;
            }

            unset($src[ $key ]);
        }

        return $src;
    }

    /**
     * > gzhegow, оставить в массиве значения, что прошли фильтр, остальные заменить
     *
     * @param callable|null $fn
     */
    public function keep_new(array $src, $new = null, $fn = null) : array
    {
        if (! $fn) {
            foreach ( $src as $key => $val ) {
                $src[ $key ] = $new;
            }

            return $src;
        }

        $mode = $this->fn_mode() ?? _ARRAY_FN_USE_VALUE;

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($mode & _ARRAY_FN_USE_VALUE) $args[] = $val;
            if ($mode & _ARRAY_FN_USE_KEY) $args[] = $key;
            if ($mode & _ARRAY_FN_USE_SRC) $args[] = $src;

            if (call_user_func_array($fn, $args)) {
                continue;
            }

            $src[ $key ] = $new;
        }

        return $src;
    }


    /**
     * > gzhegow, выбросить значения по фильтру, по сути array_filter(!function)
     *
     * @param callable|null $fn
     */
    public function drop(array $src, $fn = null) : array
    {
        if (! $fn) {
            return $src;
        }

        $mode = $this->fn_mode() ?? _ARRAY_FN_USE_VALUE;

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($mode & _ARRAY_FN_USE_VALUE) $args[] = $val;
            if ($mode & _ARRAY_FN_USE_KEY) $args[] = $key;
            if ($mode & _ARRAY_FN_USE_SRC) $args[] = $src;

            if (call_user_func_array($fn, $args)) {
                unset($src[ $key ]);
            }
        }

        return $src;
    }

    /**
     * > gzhegow, заменить значения по фильтру, по сути array_filter(!function) с заменой на null
     *
     * @param callable|null $fn
     */
    public function drop_new(array $src, $new = null, $fn = null) : array
    {
        if (! $fn) {
            return $src;
        }

        $mode = $this->fn_mode() ?? _ARRAY_FN_USE_VALUE;

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($mode & _ARRAY_FN_USE_VALUE) $args[] = $val;
            if ($mode & _ARRAY_FN_USE_KEY) $args[] = $key;
            if ($mode & _ARRAY_FN_USE_SRC) $args[] = $src;

            if (call_user_func_array($fn, $args)) {
                $src[ $key ] = $new;
            }
        }

        return $src;
    }


    /**
     *  > gzhegow, разбивает массив на два по критерию
     *
     * @param callable $fn
     *
     * @return array{0: array, 1: array}
     */
    public function both(array $src, $fn) : array
    {
        $left = [];
        $right = [];

        $mode = $this->fn_mode() ?? _ARRAY_FN_USE_VALUE;

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($mode & _ARRAY_FN_USE_VALUE) $args[] = $val;
            if ($mode & _ARRAY_FN_USE_KEY) $args[] = $key;
            if ($mode & _ARRAY_FN_USE_SRC) $args[] = $src;

            call_user_func_array($fn, $args)
                ? ($left[ $key ] = $val)
                : ($right[ $key ] = $val);
        }

        return [ $left, $right ];
    }

    /**
     * > gzhegow, разбивает массив на группы, колбэк должен вернуть имена групп и значение попадет в эти группы
     *
     * @param callable $fn
     *
     * @return array<string, array>
     */
    public function group(array $src, $fn) : array
    {
        $result = [];

        $mode = $this->fn_mode() ?? _ARRAY_FN_USE_VALUE;

        foreach ( $src as $key => $val ) {
            $args = [];
            if ($mode & _ARRAY_FN_USE_VALUE) $args[] = $val;
            if ($mode & _ARRAY_FN_USE_KEY) $args[] = $key;
            if ($mode & _ARRAY_FN_USE_SRC) $args[] = $src;

            $groupNames = call_user_func_array($fn, $args);

            foreach ( (array) $groupNames as $groupName ) {
                $result[ $groupName ][ $key ] = $val;
            }
        }

        return $result;
    }


    /**
     * > gzhegow, строит индекс ключей (int)
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
     * > gzhegow, строит индекс ключей (string)
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
     * > gzhegow, встроенная функция всегда требует два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function diff(array ...$arrays) : array
    {
        if (! $arrays) {
            return [];
        }

        if (count($arrays) === 1) {
            return $arrays[ 0 ];
        }

        $result = array_diff(...$arrays);

        return $result;
    }

    /**
     * > gzhegow, встроенная функция всегда требует два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function diff_key(array ...$arrays) : array
    {
        if (! $arrays) {
            return [];
        }

        if (count($arrays) === 1) {
            return $arrays[ 0 ];
        }

        $result = array_diff_key(...$arrays);

        return $result;
    }

    /**
     * > gzhegow, встроенная функция всегда требует два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function intersect(array ...$arrays) : array
    {
        if (! $arrays) {
            return [];
        }

        if (count($arrays) === 1) {
            return $arrays[ 0 ];
        }

        $result = array_intersect(...$arrays);

        return $result;
    }

    /**
     * > gzhegow, встроенная функция всегда требует два массива на вход, вынуждая разруливать ифами то, что не нужно
     */
    public function intersect_key(array ...$arrays) : array
    {
        if (! $arrays) {
            return [];
        }

        if (count($arrays) === 1) {
            return $arrays[ 0 ];
        }

        $result = array_intersect_key(...$arrays);

        return $result;
    }


    /**
     * > gzhegow, превращает вложенный массив в одноуровневый, но теряет ключи
     */
    public function plain(...$values) : array
    {
        $result = [];

        foreach ( $this->walk($values) as $value ) {
            $result[] = $value;
        }

        return $result;
    }

    /** > превращает вложенный массив в одноуровневый, соединяя путь через точку */
    public function dot(array $array) : array
    {
        $result = [];

        foreach ( $this->walk($array, _ARRAY_WALK_WITH_EMPTY_ARRAYS) as $path => $value ) {
            $result[ implode('.', $path) ] = $value;
        }

        return $result;
    }

    /** > превращает одноуровневый массив с ключами-точками во вложенный */
    public function undot(array $arrayDot) : array
    {
        $result = [];

        foreach ( $arrayDot as $dotKey => $value ) {
            $this->set_path($result, explode('.', $dotKey), $value);
        }

        return $result;
    }


    /**
     * array_walk_recursive, позволяющий:
     * - получить путь до элемента
     * - подменить значение по ссылке
     * - сделать обход в ширину/глубину, т.е. (1 -> 1.1 -> 2 -> 2.1) => (1 -> 2 -> 1.1 -> 2.1)
     * - пройти с потомками/пустыми-массивами/родителями
     *
     * @param array    $array
     * @param int|null $flags
     *
     * @return \Iterator<array<int|string>, mixed>|\Generator<array<int|string>, mixed>
     */
    public function &walk(array &$array, int $flags = null) : \Generator
    {
        if (! $array) return;

        $flags = $flags ?? 0;

        $isModeDepthFirst = (bool) ($flags & _ARRAY_WALK_MODE_DEPTH_FIRST);
        $isModeBreadthFirst = (bool) ($flags & _ARRAY_WALK_MODE_BREADTH_FIRST);
        $sum = $isModeDepthFirst + $isModeBreadthFirst;
        if (! $sum || ($sum > 1)) {
            $isModeDepthFirst = true;
            $isModeBreadthFirst = false;
        }

        $isSortSelfFirst = (bool) ($flags & _ARRAY_WALK_SORT_SELF_FIRST);
        $isSortParentFirst = (bool) ($flags & _ARRAY_WALK_SORT_PARENT_FIRST);
        $isSortChildFirst = (bool) ($flags & _ARRAY_WALK_SORT_CHILD_FIRST);
        $sum = $isSortSelfFirst + $isSortParentFirst + $isSortChildFirst;
        if (! $sum || ($sum > 1)) {
            $isSortSelfFirst = true;
            $isSortParentFirst = false;
            $isSortChildFirst = false;
        }

        $isWithLeaves = (bool) ($flags & _ARRAY_WALK_WITH_LEAVES);
        $isWithoutLeaves = (bool) ($flags & _ARRAY_WALK_WITHOUT_LEAVES);
        $sum = $isWithLeaves + $isWithoutLeaves;
        if (! $sum || ($sum > 1)) {
            $isWithLeaves = true;
            $isWithoutLeaves = false;
        }
        $withLeaves = $isWithLeaves && ! $isWithoutLeaves;
        unset($sum);

        $isWithoutEmptyArrays = (bool) ($flags & _ARRAY_WALK_WITHOUT_EMPTY_ARRAYS);
        $isWithEmptyArrays = (bool) ($flags & _ARRAY_WALK_WITH_EMPTY_ARRAYS);
        $sum = $isWithoutEmptyArrays + $isWithEmptyArrays;
        if (! $sum || ($sum > 1)) {
            $isWithoutEmptyArrays = true;
            $isWithEmptyArrays = false;
        }
        $withEmptyArrays = $isWithEmptyArrays && ! $isWithoutEmptyArrays;
        unset($sum);

        $isWithoutParents = (bool) ($flags & _ARRAY_WALK_WITHOUT_PARENTS);
        $isWithParents = (bool) ($flags & _ARRAY_WALK_WITH_PARENTS);
        $sum = $isWithoutParents + $isWithParents;
        if (! $sum || ($sum > 1)) {
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
            throw new LogicException('Invalid `sort`');
        }

        if ($isModeDepthFirst) {
            $stack = [];
            $buffer_ =& $stack;

        } elseif ($isModeBreadthFirst) {
            $queue = [];
            $buffer_ =& $queue;

        } else {
            throw new LogicException('Invalid `mode`');
        }

        // > value, fullpath, force
        $buffer_[] = [ &$array, [], false ];

        $isRoot = true;
        while ( ! empty($buffer_) ) {
            $cur = [];
            if ($isModeDepthFirst) {
                $cur = array_pop($buffer_);

            } elseif ($isModeBreadthFirst) {
                $cur = array_shift($buffer_);
            }

            $isYieldLeaf = ! is_array($cur[ 0 ]);
            $isYieldParent = ! $isRoot && ! $isYieldLeaf && ! empty($cur[ 0 ]);
            $isYieldEmptyArray = ! $isRoot && ! $isYieldLeaf && empty($cur[ 0 ]);

            if (
                ($withLeaves && $isYieldLeaf)
                || ($withParents && $isYieldParent)
                || ($withEmptyArrays && $isYieldEmptyArray)
            ) {
                $before = $cur[ 0 ];

                $after_ =& $cur[ 0 ];

                yield $cur[ 1 ] => $after_;

                if ($after_ !== $before) {
                    $isYieldParent = is_array($after_) && ! empty($after_);
                }

                unset($before);
                unset($after_);
                $after_ = null;
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

                    $buffer_[] = [ &$cur[ 0 ][ $kk ], $fullpath ];
                }

                unset($keys);
            }

            $isRoot = false;
        }
    }

    /**
     * Обход дерева $tree: array<int, <int, bool>>, в итераторе будет элемент из $list: array<int, mixed>
     *
     * @template T
     *
     * @param array<int|string, array<int|string, bool>> $tree
     * @param array<int|string, T>|null                  $list
     * @param int|string|null                            $start
     *
     * @return \Iterator<array<int|string>, T>|\Generator<array<int|string>, T>
     */
    public function walk_tree(array $tree, array $list = null, $start = null) : \Generator
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

            $row = (null !== $list)
                ? ($list[ $current ] ?? null)
                : $current;

            if (null !== $row) {
                yield $path => $row;
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
     * позволяет сделать add/merge/replace рекурсивно в цикле foreach с получением пути до элемента
     *
     * @return \Iterator<array<int|string>, array>|\Generator<array<int|string>, array>
     * @throws \LogicException
     */
    public function walk_collect(array $arrayList, int $arrayWalkFlags = null, array $fallback = []) : \Generator
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

            $generators[ $key ] = $this->walk($arrayList[ $key ], $arrayWalkFlags);
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
                            $found = $this
                                ->has_path(
                                    $arrayList[ $idx ], $path,
                                    $value
                                )
                            ;

                            if ($found || $fallback) {
                                if (! $found) {
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
