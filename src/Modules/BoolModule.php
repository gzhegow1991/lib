<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;


if (! defined('_BOOL_NIL')) define('_BOOL_NIL', '{N}');
if (! defined('_BOOL_UNDEFINED')) define('_BOOL_UNDEFINED', NAN);

class BoolModule
{
    const NIL       = _BOOL_NIL;
    const UNDEFINED = _BOOL_UNDEFINED;


    public function is_null($value) : bool
    {
        return null === $value;
    }

    public function is_not_null($value, &$result = null) : bool
    {
        $result = null;

        if (null === $value) {
            return false;
        }

        $result = $value;

        return true;
    }

    public function assert_not_null($value, ...$throwableArgs) // : ?mixed
    {
        $this->is_not_null($value, $result) || Lib::php()->throw(...$throwableArgs);

        return $result;
    }

    public function not_null(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if (null === $value) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    public function is_false($value) : bool
    {
        return false === $value;
    }

    public function is_not_false($value, &$result = null) : bool
    {
        $result = null;

        if (false === $value) {
            return false;
        }

        $result = $value;

        return true;
    }

    public function assert_not_false($value, ...$throwableArgs) // : ?mixed
    {
        $this->is_not_false($value, $result) || Lib::php()->throw(...$throwableArgs);

        return $result;
    }

    public function not_false(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if (false === $value) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    public function is_nan($value) : bool
    {
        return is_float($value) && is_nan($value);
    }

    public function is_not_nan($value, &$result = null) : bool
    {
        $result = null;

        if ($this->is_nan($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public function assert_not_nan($value, ...$throwableArgs) // : ?mixed
    {
        $this->is_not_nan($value, $result) || Lib::php()->throw(...$throwableArgs);

        return $result;
    }

    public function not_nan(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if ($this->is_nan($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    public function is_empty($value, &$result = null) : bool
    {
        if (empty($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    public function is_not_empty($value, &$result = null) : bool
    {
        $result = null;

        if (empty($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public function assert_not_empty($value, ...$throwableArgs) // : ?mixed
    {
        $this->is_not_empty($value, $result) || Lib::php()->throw(...$throwableArgs);

        return $result;
    }

    public function not_empty(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if ($this->is_empty($value)) {
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
    public function nil() // : mixed
    {
        return _BOOL_NIL;
    }

    public function is_nil($value) : bool
    {
        return $this->nil() === $value;
    }

    public function is_not_nil($value, &$result = null) : bool
    {
        $result = null;

        if ($this->nil() === $value) {
            return false;
        }

        $result = $value;

        return true;
    }

    public function assert_not_nil($value, ...$throwableArgs) // : ?mixed
    {
        $this->is_not_nil($value, $result) || Lib::php()->throw(...$throwableArgs);

        return $result;
    }

    public function not_nil(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if ($this->is_nil($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    public function undefined() : float
    {
        return _BOOL_UNDEFINED;
    }

    public function is_undefined($value) : bool
    {
        return is_float($value) && is_nan($value);
    }

    public function is_not_undefined($value, &$result = null) : bool
    {
        $result = null;

        if ($this->is_undefined($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public function assert_not_undefined($value, ...$throwableArgs) // : ?mixed
    {
        $this->is_not_undefined($value, $result) || Lib::php()->throw(...$throwableArgs);

        return $result;
    }

    public function not_undefined(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if ($this->is_undefined($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    /**
     * > null is nullable
     * > USERNULL is nullable
     * > NAN is nullable
     */
    public function is_nullable($value, &$result = null) : bool
    {
        if (
            (null === $value)
            || $this->is_nil($value)
            || $this->is_nan($value)
        ) {
            $result = $value;

            return true;
        }

        return false;
    }

    public function is_not_nullable($value, &$result = null) : bool
    {
        $result = null;

        if ($this->is_nullable($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public function assert_not_nullable($value, ...$throwableArgs) // : ?mixed
    {
        $this->is_not_nullable($value, $result) || Lib::php()->throw(...$throwableArgs);

        return $result;
    }

    public function not_nullable(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if ($this->is_nullable($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    /**
     * > any empty is blank
     * > any nullable is blank
     * > non-empty-string is not blank
     * > non-empty-array is not blank
     * > non-empty-countable is not blank
     */
    public function is_blank($value, &$result = null) : bool
    {
        if ('' === $value) {
            $result = $value;

            return true;
        }

        if (false
            || $this->is_nil($value)
            || $this->is_nan($value)
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
            if (null === ($cnt = Lib::php()->count($value))) {
                return false;
            }

            if (0 === $cnt) {
                $result = $value;

                return true;
            }
        }

        return false;
    }

    public function is_not_blank($value, &$result = null) : bool
    {
        $result = null;

        if ($this->is_blank($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public function assert_not_blank($value, ...$throwableArgs) // : ?mixed
    {
        $this->is_not_blank($value, $result) || Lib::php()->throw(...$throwableArgs);

        return $result;
    }

    public function not_blank(array $array) : array
    {
        foreach ( $array as $idx => $value ) {
            if ($this->is_blank($value)) {
                unset($array[ $idx ]);
            }
        }

        return $array;
    }


    /**
     * > any non-void is passed
     * > '{N}' is passed
     */
    public function is_passed($value, &$result = null) : bool
    {
        if ($this->is_nil($value) || ! $this->is_nullable($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    public function is_not_passed($value, &$result = null) : bool
    {
        $result = null;

        if ($this->is_passed($value)) {
            return false;
        }

        $result = $value;

        return true;
    }

    public function assert_passed($value, ...$throwableArgs) // : ?mixed
    {
        $this->is_passed($value, $result) || Lib::php()->throw(...$throwableArgs);

        return $result;
    }

    public function passed(array $values) : array
    {
        foreach ( $values as $idx => $value ) {
            if (! $this->is_passed($value)) {
                unset($values[ $idx ]);
            }
        }

        return $values;
    }
}
