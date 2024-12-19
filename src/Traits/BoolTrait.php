<?php

namespace Gzhegow\Lib\Traits;

if (! defined('_UNDEFINED')) define('_UNDEFINED', NAN);

trait BoolTrait
{
    public static function is_null($value) : bool
    {
        return null === $value;
    }

    public static function is_not_null($value, &$result = null) : bool
    {
        $result = null;

        if (null === $value) {
            return false;
        }

        $result = $value;

        return true;
    }

    public static function assert_not_null($value, ...$throwableArgs) // : ?mixed
    {
        static::is_not_null($value, $result) || static::php_throw(...$throwableArgs);

        return $result;
    }

    public static function not_null(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if (null === $value) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    public static function is_false($value) : bool
    {
        return false === $value;
    }

    public static function is_not_false($value, &$result = null) : bool
    {
        $result = null;

        if (false === $value) {
            return false;
        }

        $result = $value;

        return true;
    }

    public static function assert_not_false($value, ...$throwableArgs) // : ?mixed
    {
        static::is_not_false($value, $result) || static::php_throw(...$throwableArgs);

        return $result;
    }

    public static function not_false(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if (false === $value) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    /**
     * @noinspection PhpMissingReturnTypeInspection
     *
     * @return string
     */
    public static function nil() // : mixed
    {
        return '{N}';
    }

    public static function is_nil($value) : bool
    {
        return static::nil() === $value;
    }

    public static function is_not_nil($value, &$result = null) : bool
    {
        $result = null;

        if (static::nil() === $value) {
            return false;
        }

        $result = $value;

        return true;
    }

    public static function assert_not_nil($value, ...$throwableArgs) // : ?mixed
    {
        static::is_not_nil($value, $result) || static::php_throw(...$throwableArgs);

        return $result;
    }

    public static function not_nil(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if (static::is_nil($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    public static function is_nan($value) : bool
    {
        return is_float($value) && is_nan($value);
    }

    public static function is_not_nan($value, &$result = null) : bool
    {
        $result = null;

        if (static::is_nan($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public static function assert_not_nan($value, ...$throwableArgs) // : ?mixed
    {
        static::is_not_nan($value, $result) || static::php_throw(...$throwableArgs);

        return $result;
    }

    public static function not_nan(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if (static::is_nan($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    public static function is_undefined($value) : bool
    {
        return is_float($value) && is_nan($value);
    }

    public static function is_not_undefined($value, &$result = null) : bool
    {
        $result = null;

        if (static::is_undefined($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public static function assert_not_undefined($value, ...$throwableArgs) // : ?mixed
    {
        static::is_not_undefined($value, $result) || static::php_throw(...$throwableArgs);

        return $result;
    }

    public static function not_undefined(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if (static::is_undefined($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    public static function is_empty($value, &$result = null) : bool
    {
        if (empty($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    public static function is_not_empty($value, &$result = null) : bool
    {
        $result = null;

        if (empty($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public static function assert_not_empty($value, ...$throwableArgs) // : ?mixed
    {
        static::is_not_empty($value, $result) || static::php_throw(...$throwableArgs);

        return $result;
    }

    public static function not_empty(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if (static::is_empty($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    /**
     * > gzhegow, null is nullable
     * > gzhegow, USERNULL is nullable
     * > gzhegow, NAN is nullable
     */
    public static function is_nullable($value, &$result = null) : bool
    {
        if (
            (null === $value)
            || static::is_nil($value)
            || static::is_nan($value)
        ) {
            $result = $value;

            return true;
        }

        return false;
    }

    public static function is_not_nullable($value, &$result = null) : bool
    {
        $result = null;

        if (static::is_nullable($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public static function assert_not_nullable($value, ...$throwableArgs) // : ?mixed
    {
        static::is_not_nullable($value, $result) || static::php_throw(...$throwableArgs);

        return $result;
    }

    public static function not_nullable(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if (static::is_nullable($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    /**
     * > gzhegow, any empty is blank
     * > gzhegow, any nullable is blank
     * > gzhegow, non-empty-string is not blank
     * > gzhegow, non-empty-array is not blank
     * > gzhegow, non-empty-countable is not blank
     */
    public static function is_blank($value, &$result = null) : bool
    {
        if ('' === $value) {
            $result = $value;

            return true;
        }

        if (false
            || static::is_nil($value)
            || static::is_nan($value)
        ) {
            $result = $value;

            return true;
        }

        if (is_scalar($value)) {
            return false;
        }

        if (empty($value)) {
            $result = $value;

            return true;
        }

        if (is_object($value)) {
            if (null === ($cnt = static::php_count($value))) {
                return false;
            }

            if (0 === $cnt) {
                $result = $value;

                return true;
            }
        }

        return false;
    }

    public static function is_not_blank($value, &$result = null) : bool
    {
        $result = null;

        if (static::is_blank($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public static function assert_not_blank($value, ...$throwableArgs) // : ?mixed
    {
        static::is_not_blank($value, $result) || static::php_throw(...$throwableArgs);

        return $result;
    }

    public static function not_blank(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if (static::is_blank($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    /**
     * > gzhegow, any non-void is passed
     * > gzhegow, '{N}' is passed
     */
    public static function is_passed($value, &$result = null) : bool
    {
        if (static::is_nil($value) || ! static::is_nullable($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    public static function is_not_passed($value, &$result = null) : bool
    {
        $result = null;

        if (static::is_passed($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public static function assert_passed($value, ...$throwableArgs) // : ?mixed
    {
        static::is_passed($value, $result) || static::php_throw(...$throwableArgs);

        return $result;
    }

    public static function passed(array $values) : array
    {
        foreach ( $values as $idx => $value ) {
            if (! static::is_passed($value)) {
                unset($values[ $idx ]);
            }
        }

        return $values;
    }
}
