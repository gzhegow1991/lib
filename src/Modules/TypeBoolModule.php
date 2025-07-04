<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Nil;
use Gzhegow\Lib\Modules\Arr\ArrPath;
use Gzhegow\Lib\Modules\Net\SubnetV4;
use Gzhegow\Lib\Modules\Net\SubnetV6;
use Gzhegow\Lib\Modules\Str\Alphabet;
use Gzhegow\Lib\Modules\Bcmath\Number;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Modules\Net\AddressIpV4;
use Gzhegow\Lib\Modules\Net\AddressIpV6;


class TypeBoolModule
{
    /**
     * @param mixed|null $r
     */
    public function empty(&$r, $value) : bool
    {
        $r = null;

        if (empty($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_empty(&$r, $value) : bool
    {
        $r = null;

        if (! empty($value)) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * > Специальный тип, который значит, что значение можно отбросить или не учитывать, т.к. оно не несёт информации
     *
     * @param string|array|\Countable|null $r
     */
    public function blank(&$r, $value) : bool
    {
        $r = null;

        // > NAN is not blank (NAN equals nothing even itself)
        // > NIL is not blank (NIL is passed manually, that literally means NOT BLANK)
        // > CLOSED RESOURCE is not blank (actually its still internal object)

        if (false
            // > NULL is blank (can appear from API to omit any actions on the value)
            || (null === $value)
            //
            // > EMPTY STRING is blank (can appear from HTML forms with no input provided)
            || ('' === $value)
            //
            // > EMPTY ARRAY is blank (can appear from HTML forms with no checkbox/radio/select items choosen)
            || ([] === $value)
        ) {
            $r = $value;

            return true;
        }

        // > COUNTABLE w/ ZERO SIZE is blank
        if ($this->countable($countable, $value)) {
            if (0 === count($countable)) {
                $r = $value;

                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_blank(&$r, $value) : bool
    {
        $r = null;

        if (! $this->blank($var, $value)) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * > Специальный тип, который значит, что значение можно заменить NULL-ом
     *
     * @param mixed|null $r
     */
    public function nullable(&$r, $value) : bool
    {
        $r = null;

        // > NAN is not clearable (NAN means some error in the code and shouldnt be replaced)
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
            || $this->nil($var, $value)
        ) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_nullable(&$r, $value) : bool
    {
        $r = null;

        if (! $this->nullable($var, $value)) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * > Специальный тип, который значит, что значение было отправлено пользователем, а не появилось из PHP
     *
     * @param mixed|null $r
     */
    public function passed(&$r, $value) : bool
    {
        $r = null;

        if ($this->nil($var, $value)) {
            $r = $value;

            return true;
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
            return false;
        }

        $r = $value;

        return true;
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_passed(&$r, $value) : bool
    {
        $r = null;

        if (! $this->passed($var, $value)) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * > Специальный тип-синоним NULL, переданный пользователем через API, например '{N}'
     * > в случаях, когда NULL интерпретируется как "не трогать", а NIL как "очистить"
     *
     * > NAN не равен ничему даже самому себе
     * > NIL равен только самому себе
     * > NULL означает пустоту и им можно заменить значения '', [], `resource (closed)`, NIL, но нельзя заменить NAN
     *
     * @param string|Nil|null $r
     */
    public function nil(&$r, $value) : bool
    {
        $r = null;

        if (Nil::is($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_nil(&$r, $value) : bool
    {
        $r = null;

        if (! Nil::is($value)) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param null $r
     */
    public function a_null(&$r, $value) : bool
    {
        $r = null;

        if (null === $value) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_null(&$r, $value) : bool
    {
        $r = null;

        if (null !== $value) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param bool|null $r
     */
    public function a_bool(&$r, $value) : bool
    {
        $r = null;

        if (is_bool($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function an_any_not_bool(&$r, $value) : bool
    {
        $r = null;

        if (! is_bool($value)) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param false|null $r
     */
    public function a_false(&$r, $value) : bool
    {
        $r = null;

        if (false === $value) {
            $r = false;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_false(&$r, $value) : bool
    {
        $r = null;

        if (false !== $value) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param true|null $r
     */
    public function a_true(&$r, $value) : bool
    {
        $r = null;

        if (true === $value) {
            $r = true;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_true(&$r, $value) : bool
    {
        $r = null;

        if (true !== $value) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param bool|null $r
     */
    public function bool(&$r, $value) : bool
    {
        $r = null;

        if (null === $value) {
            return false;
        }

        if (is_bool($value)) {
            $r = $value;

            return true;
        }

        if (is_int($value)) {
            $r = (0 !== $value);

            return true;
        }

        if (is_float($value)) {
            if (is_nan($value)) {
                return false;
            }

            $r = (0.0 !== $value);

            return true;
        }

        if ($this->nil($var, $value)) {
            return false;
        }

        if (is_string($value)) {
            if ('' === $value) {
                $r = false;

                return true;
            }

            $r = true;

            return true;
        }

        if (is_array($value)) {
            if ([] === $value) {
                $r = false;

                return true;
            }

            $r = true;

            return true;
        }

        if (is_resource($value)) {
            $r = true;

            return true;

        } elseif ('resource (closed)' === gettype($value)) {
            $r = false;

            return true;
        }

        if (is_object($value)) {
            if ($this->countable($countable, $value)) {
                if (0 === count($countable)) {
                    // > EMPTY COUNTABLE is false

                    $r = false;

                    return true;
                }
            }

            $r = true;

            return true;
        }

        return false;
    }

    /**
     * @param false|null $r
     */
    public function false(&$r, $value) : bool
    {
        $r = null;

        if (! $this->bool($bool, $value)) {
            return false;
        }

        if (false === $bool) {
            $r = false;

            return true;
        }

        return false;
    }

    /**
     * @param false|null $r
     */
    public function true(&$r, $value) : bool
    {
        $r = null;

        if (! $this->bool($bool, $value)) {
            return false;
        }

        if (true === $bool) {
            $r = true;

            return true;
        }

        return false;
    }


    /**
     * @param bool|null $r
     */
    public function userbool(&$r, $value) : bool
    {
        $r = null;

        if (null === $value) {
            return false;
        }

        if (is_bool($value)) {
            $r = $value;

            return true;
        }

        if (is_int($value)) {
            $r = (0 !== $value);

            return true;
        }

        if (is_float($value)) {
            if (is_nan($value)) {
                return false;
            }

            $r = (0.0 !== $value);

            return true;
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

            $_value = strtolower($value);

            if (isset($map[ $_value ])) {
                $r = $map[ $_value ];

                return true;
            }
        }

        return false;
    }

    /**
     * @param false|null $r
     */
    public function userfalse(&$r, $value) : bool
    {
        $r = null;

        if (! $this->userbool($bool, $value)) {
            return false;
        }

        if (false === $bool) {
            $r = false;

            return true;
        }

        return false;
    }

    /**
     * @param false|null $r
     */
    public function usertrue(&$r, $value) : bool
    {
        $r = null;

        if (! $this->userbool($bool, $value)) {
            return false;
        }

        if (true === $bool) {
            $r = true;

            return true;
        }

        return false;
    }


    /**
     * @param float|null $r
     */
    public function nan(&$r, $value) : bool
    {
        return Lib::num()->type_nan($r, $value);
    }

    /**
     * @param float|null $r
     */
    public function float_not_nan(&$r, $value) : bool
    {
        return Lib::num()->type_float_not_nan($r, $value);
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_nan(&$r, $value) : bool
    {
        return Lib::num()->type_any_not_nan($r, $value);
    }


    /**
     * @param float|null $r
     */
    public function finite(&$r, $value) : bool
    {
        return Lib::num()->type_finite($r, $value);
    }

    /**
     * @param float|null $r
     */
    public function float_not_finite(&$r, $value) : bool
    {
        return Lib::num()->type_float_not_finite($r, $value);
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_finite(&$r, $value) : bool
    {
        return Lib::num()->type_any_not_finite($r, $value);
    }


    /**
     * @param float|null $r
     */
    public function infinite(&$r, $value) : bool
    {
        return Lib::num()->type_infinite($r, $value);
    }

    /**
     * @param float|null $r
     */
    public function float_not_infinite(&$r, $value) : bool
    {
        return Lib::num()->type_float_not_infinite($r, $value);
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_infinite(&$r, $value) : bool
    {
        return Lib::num()->type_any_not_infinite($r, $value);
    }


    /**
     * @param float|null $r
     */
    public function float_min(&$r, $value) : bool
    {
        return Lib::num()->type_float_min($r, $value);
    }

    /**
     * @param float|null $r
     */
    public function float_not_float_min(&$r, $value) : bool
    {
        return Lib::num()->type_float_not_float_min($r, $value);
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_float_min(&$r, $value) : bool
    {
        return Lib::num()->type_any_not_float_min($r, $value);
    }


    /**
     * @param Number|null $r
     */
    public function number(&$r, $value, ?bool $allowExp = null) : bool
    {
        return Lib::num()->type_number($r, $value, $allowExp);
    }


    /**
     * @param string|null $r
     */
    public function numeric(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        return Lib::num()->type_numeric($r, $value, $isAllowExp, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_non_zero(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_non_zero($r, $value, $isAllowExp, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_non_negative(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_non_negative($r, $value, $isAllowExp, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_non_positive(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_non_positive($r, $value, $isAllowExp, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_negative(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_negative($r, $value, $isAllowExp, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_positive(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_positive($r, $value, $isAllowExp, $refs);
    }


    /**
     * @param string|null $r
     */
    public function numeric_int(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_int($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_int_non_zero(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_int_non_zero($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_int_non_negative(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_int_non_negative($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_int_non_positive(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_int_non_positive($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_int_negative(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_int_negative($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_int_positive(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_int_positive($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_int_positive_or_minus_one(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_int_positive_or_minus_one($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_int_non_negative_or_minus_one(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_int_non_negative_or_minus_one($r, $value, $refs);
    }


    /**
     * @param string|null $r
     */
    public function numeric_float(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_float($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_float_non_zero(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_float_non_zero($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_float_non_negative(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_float_non_negative($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_float_non_positive(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_float_non_positive($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_float_negative(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_float_negative($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_float_positive(&$r, $value, array $refs = []) : bool
    {
        return Lib::num()->type_numeric_float_positive($r, $value, $refs);
    }


    /**
     * @param string|null $r
     */
    public function numeric_trimpad(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        return Lib::num()->type_numeric_trimpad($r, $value, $lenTrim, $lenPad, $stringPad, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_trimpad_non_zero(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        return Lib::num()->type_numeric_trimpad_non_zero($r, $value, $lenTrim, $lenPad, $stringPad, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_trimpad_non_negative(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        return Lib::num()->type_numeric_trimpad_non_negative($r, $value, $lenTrim, $lenPad, $stringPad, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_trimpad_non_positive(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        return Lib::num()->type_numeric_trimpad_non_positive($r, $value, $lenTrim, $lenPad, $stringPad, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_trimpad_negative(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        return Lib::num()->type_numeric_trimpad_negative($r, $value, $lenTrim, $lenPad, $stringPad, $refs);
    }

    /**
     * @param string|null $r
     */
    public function numeric_trimpad_positive(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        return Lib::num()->type_numeric_trimpad_positive($r, $value, $lenTrim, $lenPad, $stringPad, $refs);
    }


    /**
     * @param string|null $r
     */
    public function decimal(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        return Lib::num()->type_decimal($r, $value, $scale, $refs);
    }

    /**
     * @param string|null $r
     */
    public function decimal_non_zero(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        return Lib::num()->type_decimal_non_zero($r, $value, $scale, $refs);
    }

    /**
     * @param string|null $r
     */
    public function decimal_non_negative(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        return Lib::num()->type_decimal_non_negative($r, $value, $scale, $refs);
    }

    /**
     * @param string|null $r
     */
    public function decimal_non_positive(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        return Lib::num()->type_decimal_non_positive($r, $value, $scale, $refs);
    }

    /**
     * @param string|null $r
     */
    public function decimal_negative(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        return Lib::num()->type_decimal_negative($r, $value, $scale, $refs);
    }

    /**
     * @param string|null $r
     */
    public function decimal_positive(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        return Lib::num()->type_decimal_positive($r, $value, $scale, $refs);
    }


    /**
     * @param int|float|null $r
     */
    public function num(&$r, $value) : bool
    {
        return Lib::num()->type_num($r, $value);
    }

    /**
     * @param int|float|null $r
     */
    public function num_non_zero(&$r, $value) : bool
    {
        return Lib::num()->type_num_non_zero($r, $value);
    }

    /**
     * @param int|float|null $r
     */
    public function num_non_negative(&$r, $value) : bool
    {
        return Lib::num()->type_num_non_negative($r, $value);
    }

    /**
     * @param int|float|null $r
     */
    public function num_non_positive(&$r, $value) : bool
    {
        return Lib::num()->type_num_non_positive($r, $value);
    }

    /**
     * @param int|float|null $r
     */
    public function num_negative(&$r, $value) : bool
    {
        return Lib::num()->type_num_negative($r, $value);
    }

    /**
     * @param int|float|null $r
     */
    public function num_positive(&$r, $value) : bool
    {
        return Lib::num()->type_num_positive($r, $value);
    }


    /**
     * @param int|null $r
     */
    public function int(&$r, $value) : bool
    {
        return Lib::num()->type_int($r, $value);
    }

    /**
     * @param int|null $r
     */
    public function int_non_zero(&$r, $value) : bool
    {
        return Lib::num()->type_int_non_zero($r, $value);
    }

    /**
     * @param int|null $r
     */
    public function int_non_negative(&$r, $value) : bool
    {
        return Lib::num()->type_int_non_negative($r, $value);
    }

    /**
     * @param int|null $r
     */
    public function int_non_positive(&$r, $value) : bool
    {
        return Lib::num()->type_int_non_positive($r, $value);
    }

    /**
     * @param int|null $r
     */
    public function int_negative(&$r, $value) : bool
    {
        return Lib::num()->type_int_negative($r, $value);
    }

    /**
     * @param int|null $r
     */
    public function int_positive(&$r, $value) : bool
    {
        return Lib::num()->type_int_positive($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function int_positive_or_minus_one(&$r, $value) : bool
    {
        return Lib::num()->type_int_positive_or_minus_one($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function int_non_negative_or_minus_one(&$r, $value) : bool
    {
        return Lib::num()->type_int_non_negative_or_minus_one($r, $value);
    }


    /**
     * @param float|null $r
     */
    public function float(&$r, $value) : bool
    {
        return Lib::num()->type_float($r, $value);
    }

    /**
     * @param float|null $r
     */
    public function float_non_zero(&$r, $value) : bool
    {
        return Lib::num()->type_float_non_zero($r, $value);
    }

    /**
     * @param float|null $r
     */
    public function float_non_negative(&$r, $value) : bool
    {
        return Lib::num()->type_float_non_negative($r, $value);
    }

    /**
     * @param float|null $r
     */
    public function float_non_positive(&$r, $value) : bool
    {
        return Lib::num()->type_float_non_positive($r, $value);
    }

    /**
     * @param float|null $r
     */
    public function float_negative(&$r, $value) : bool
    {
        return Lib::num()->type_float_negative($r, $value);
    }

    /**
     * @param float|null $r
     */
    public function float_positive(&$r, $value) : bool
    {
        return Lib::num()->type_float_positive($r, $value);
    }


    /**
     * @param Bcnumber|null $r
     */
    public function bcnumber(&$r, $value) : bool
    {
        return Lib::bcmath()->type_bcnumber($r, $value);
    }


    /**
     * @param string|null $r
     */
    public function a_string(&$r, $value) : bool
    {
        return Lib::str()->type_a_string($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function a_string_empty(&$r, $value) : bool
    {
        return Lib::str()->type_a_string_empty($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function a_string_not_empty(&$r, $value) : bool
    {
        return Lib::str()->type_a_string_not_empty($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function a_trim(&$r, $value) : bool
    {
        return Lib::str()->type_a_trim($r, $value);
    }


    /**
     * @param string|null $r
     */
    public function string(&$r, $value) : bool
    {
        return Lib::str()->type_string($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function string_empty(&$r, $value) : bool
    {
        return Lib::str()->type_string_empty($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function string_not_empty(&$r, $value) : bool
    {
        return Lib::str()->type_string_not_empty($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function trim(&$r, $value, ?string $characters = null) : bool
    {
        return Lib::str()->type_trim($r, $value, $characters);
    }


    /**
     * @param string|null $r
     */
    public function char(&$r, $value) : bool
    {
        return Lib::str()->type_char($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function letter(&$r, $value) : bool
    {
        return Lib::str()->type_letter($r, $value);
    }

    /**
     * @param Alphabet|null $r
     */
    public function alphabet(&$r, $value) : bool
    {
        return Lib::str()->type_alphabet($r, $value);
    }


    /**
     * @param string|null $r
     */
    public function ctype_digit(&$r, $value) : bool
    {
        $r = null;

        if (! $this->string_not_empty($_value, $value)) {
            return false;
        }

        if (extension_loaded('ctype')) {
            if (ctype_digit($_value)) {
                $r = $_value;

                return true;
            }

            return false;
        }

        if (! preg_match('~[^0-9]~', $_value)) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param string|null $r
     */
    public function ctype_alpha(&$r, $value, ?bool $isIgnoreCase = null) : bool
    {
        $r = null;

        $isIgnoreCase = $isIgnoreCase ?? true;

        if (! $this->string_not_empty($_value, $value)) {
            return false;
        }

        if (extension_loaded('ctype')) {
            if (! $isIgnoreCase) {
                if (strtolower($_value) !== $_value) {
                    return false;
                }
            }

            if (ctype_alpha($_value)) {
                $r = $_value;

                return true;
            }

            return false;
        }

        $regexFlags = $isIgnoreCase
            ? 'i'
            : '';

        if (preg_match('~[^a-z]~' . $regexFlags, $_value)) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param string|null $r
     */
    public function ctype_alnum(&$r, $value, ?bool $isIgnoreCase = null) : bool
    {
        $r = null;

        $isIgnoreCase = $isIgnoreCase ?? true;

        if (! $this->string_not_empty($_value, $value)) {
            return false;
        }

        if (extension_loaded('ctype')) {
            if (! $isIgnoreCase) {
                if (strtolower($_value) !== $_value) {
                    return false;
                }
            }

            if (ctype_alnum($_value)) {
                $r = $_value;

                return true;
            }

            return false;
        }

        $regexFlags = $isIgnoreCase
            ? 'i'
            : '';

        if (preg_match('~[^0-9a-z]~' . $regexFlags, $_value)) {
            return false;
        }

        $r = $_value;

        return true;
    }


    /**
     * @param string|null $r
     */
    public function base(&$r, $value, $alphabet) : bool
    {
        return Lib::crypt()->type_base($r, $value, $alphabet);
    }

    /**
     * @param string|null $r
     */
    public function base_bin(&$r, $value) : bool
    {
        return Lib::crypt()->type_base_bin($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function base_oct(&$r, $value) : bool
    {
        return Lib::crypt()->type_base_oct($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function base_dec(&$r, $value) : bool
    {
        return Lib::crypt()->type_base_dec($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function base_hex(&$r, $value) : bool
    {
        return Lib::crypt()->type_base_hex($r, $value);
    }


    /**
     * @param array|null $r
     */
    public function array_empty(&$r, $value) : bool
    {
        $r = null;

        if ([] === $value) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param array|null $r
     */
    public function array_not_empty(&$r, $value) : bool
    {
        $r = null;

        if (is_array($value) && ([] !== $value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function any_not_array_empty(&$r, $value) : bool
    {
        $r = null;

        if ([] !== $value) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param mixed|null $r
     */
    public function key_exists(&$r, $value, $key) : bool
    {
        return Lib::arr()->type_key_exists($r, $value, $key);
    }


    /**
     * @param array|null $r
     */
    public function array_plain(&$r, $value) : bool
    {
        return Lib::arr()->type_array_plain($r, $value);
    }


    /**
     * @param array|null $r
     */
    public function list(&$r, $value, ?bool $isPlain = null) : bool
    {
        return Lib::arr()->type_list($r, $value, $isPlain);
    }

    /**
     * @param array|null $r
     */
    public function list_sorted(&$r, $value, ?bool $isPlain = null) : bool
    {
        return Lib::arr()->type_list_sorted($r, $value, $isPlain);
    }


    /**
     * @param array|null $r
     */
    public function dict(&$r, $value, ?bool $isPlain = null) : bool
    {
        return Lib::arr()->type_dict($r, $value, $isPlain);
    }

    /**
     * @param array|null $r
     */
    public function dict_sorted(&$r, $value, ?bool $isPlain = null) : bool
    {
        return Lib::arr()->type_dict_sorted($r, $value, $isPlain);
    }


    /**
     * @param array|null $r
     */
    public function table(&$r, $value) : bool
    {
        return Lib::arr()->type_table($r, $value);
    }

    /**
     * @param array|null $r
     */
    public function matrix(&$r, $value) : bool
    {
        return Lib::arr()->type_matrix($r, $value);
    }

    /**
     * @param array|null $r
     */
    public function matrix_strict(&$r, $value) : bool
    {
        return Lib::arr()->type_matrix_strict($r, $value);
    }


    /**
     * @param ArrPath|null $r
     */
    public function arrpath(&$r, $path, ?string $dot = null) : bool
    {
        return Lib::arr()->type_arrpath($r, $path, $dot);
    }


    /**
     * @param array|null $r
     */
    public function array_of_type(&$r, $value, string $type) : bool
    {
        return Lib::arr()->type_array_of_type($r, $value, $type);
    }

    /**
     * @param resource[]|null $r
     */
    public function array_of_resource_type(&$r, $value, string $resourceType) : bool
    {
        return Lib::arr()->type_array_of_resource_type($r, $value, $resourceType);
    }

    /**
     * @template T
     *
     * @param T[]             $r
     * @param class-string<T> $className
     */
    public function array_of_a(&$r, $value, string $className) : bool
    {
        return Lib::arr()->type_array_of_a($r, $value, $className);
    }

    /**
     * @template T
     *
     * @param T[]             $r
     * @param class-string<T> $className
     */
    public function array_of_class(&$r, $value, string $className) : bool
    {
        return Lib::arr()->type_array_of_class($r, $value, $className);
    }

    /**
     * @template T
     *
     * @param T[]             $r
     * @param class-string<T> $className
     */
    public function array_of_subclass(&$r, $value, string $className) : bool
    {
        return Lib::arr()->type_array_of_subclass($r, $value, $className);
    }

    /**
     * @param array|null $r
     */
    public function array_of_callback(&$r, $value, callable $fn, array $args = []) : bool
    {
        return Lib::arr()->type_array_of_callback($r, $value, $fn, $args);
    }


    /**
     * @param string|null $r
     */
    public function html_tag(&$r, $value) : bool
    {
        return Lib::format()->type_html_tag($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function xml_tag(&$r, $value) : bool
    {
        return Lib::format()->type_xml_tag($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function xml_nstag(&$r, $value) : bool
    {
        return Lib::format()->type_xml_nstag($r, $value);
    }


    /**
     * @param string|null $r
     */
    public function regex(&$r, $value) : bool
    {
        return Lib::preg()->type_regex($r, $value);
    }


    /**
     * @param AddressIpV4|AddressIpV6|null $r
     */
    public function address_ip(&$r, $value) : bool
    {
        return Lib::net()->type_address_ip($r, $value);
    }

    /**
     * @param AddressIpV4|null $r
     */
    public function address_ip_v4(&$r, $value) : bool
    {
        return Lib::net()->type_address_ip_v4($r, $value);
    }

    /**
     * @param AddressIpV6|null $r
     */
    public function address_ip_v6(&$r, $value) : bool
    {
        return Lib::net()->type_address_ip_v6($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function address_mac(&$r, $value) : bool
    {
        return Lib::net()->type_address_mac($r, $value);
    }


    /**
     * @param SubnetV4|SubnetV6|null $r
     */
    public function subnet(&$r, $value, ?string $ipFallback = null) : bool
    {
        return Lib::net()->type_subnet($r, $value, $ipFallback);
    }

    /**
     * @param SubnetV4|null $r
     */
    public function subnet_v4(&$r, $value, ?string $ipFallback = null) : bool
    {
        return Lib::net()->type_subnet_v4($r, $value, $ipFallback);
    }

    /**
     * @param SubnetV6|null $r
     */
    public function subnet_v6(&$r, $value, ?string $ipFallback = null) : bool
    {
        return Lib::net()->type_subnet_v6($r, $value, $ipFallback);
    }


    /**
     * @param string|null $r
     */
    public function url(
        &$r,
        $value, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null,
        array $refs = []
    ) : bool
    {
        return Lib::url()->type_url(
            $r,
            $value, $query, $fragment,
            $isHostIdnaAscii, $isLinkUrlencoded,
            $refs
        );
    }

    /**
     * @param string|null $r
     */
    public function host(
        &$r,
        $value,
        ?int $isIdnaAscii = null,
        array $refs = []
    ) : bool
    {
        return Lib::url()->type_host(
            $r,
            $value,
            $isIdnaAscii,
            $refs
        );
    }

    /**
     * @param string|null $r
     */
    public function link(
        &$r,
        $value, $query = null, $fragment = null,
        ?int $isUrlencoded = null,
        array $refs = []
    ) : bool
    {
        return Lib::url()->type_link(
            $r,
            $value, $query, $fragment,
            $isUrlencoded,
            $refs
        );
    }


    /**
     * @param string|null $r
     */
    public function uuid(&$r, $value) : bool
    {
        return Lib::random()->type_uuid($r, $value);
    }


    /**
     * @param array|\Countable|null $r
     */
    public function countable(&$r, $value) : bool
    {
        return Lib::php()->type_countable($r, $value);
    }

    /**
     * @param \Countable|null $r
     */
    public function countable_object(&$r, $value) : bool
    {
        return Lib::php()->type_countable_object($r, $value);
    }

    /**
     * @param string|array|\Countable|null $r
     */
    public function sizeable(&$r, $value) : bool
    {
        return Lib::php()->type_sizeable($r, $value);
    }


    /**
     * @param \DateTimeZone|null $r
     */
    public function timezone(&$r, $timezone, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_timezone($r, $timezone, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeZone|null $r
     */
    public function timezone_offset(&$r, $timezoneOrOffset) : bool
    {
        return Lib::date()->type_timezone_offset($r, $timezoneOrOffset);
    }

    /**
     * @param \DateTimeZone|null $r
     */
    public function timezone_abbr(&$r, $timezoneOrAbbr) : bool
    {
        return Lib::date()->type_timezone_abbr($r, $timezoneOrAbbr);
    }

    /**
     * @param \DateTimeZone|null $r
     */
    public function timezone_name(&$r, $timezoneOrName) : bool
    {
        return Lib::date()->type_timezone_name($r, $timezoneOrName);
    }

    /**
     * @param \DateTimeZone|null $r
     */
    public function timezone_nameabbr(&$r, $timezoneOrNameOrAbbr) : bool
    {
        return Lib::date()->type_timezone_nameabbr($r, $timezoneOrNameOrAbbr);
    }


    /**
     * @param \DateTimeInterface|null $r
     */
    public function date(&$r, $datestring, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_date($r, $datestring, $timezoneFallback);
    }

    /**
     * @param \DateTime|null $r
     */
    public function adate(&$r, $datestring, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_adate($r, $datestring, $timezoneFallback);
    }

    /**
     * @param \DateTimeImmutable|null $r
     */
    public function idate(&$r, $datestring, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_idate($r, $datestring, $timezoneFallback);
    }


    /**
     * @param \DateTimeInterface|null $r
     */
    public function date_formatted(&$r, $dateFormatted, $formats, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_date_formatted($r, $dateFormatted, $formats, $timezoneFallback);
    }

    /**
     * @param \DateTime|null $r
     */
    public function adate_formatted(&$r, $dateFormatted, $formats, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_adate_formatted($r, $dateFormatted, $formats, $timezoneFallback);
    }

    /**
     * @param \DateTimeImmutable|null $r
     */
    public function idate_formatted(&$r, $dateFormatted, $formats, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_idate_formatted($r, $dateFormatted, $formats, $timezoneFallback);
    }


    /**
     * @param \DateTimeInterface|null $r
     */
    public function date_tz(&$r, $datestring, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_date_tz($r, $datestring, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTime|null $r
     */
    public function adate_tz(&$r, $datestring, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_adate_tz($r, $datestring, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeImmutable|null $r
     */
    public function idate_tz(&$r, $datestring, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_idate_tz($r, $datestring, $allowedTimezoneTypes);
    }


    /**
     * @param \DateTimeInterface|null $r
     */
    public function date_tz_formatted(&$r, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_date_tz_formatted($r, $dateFormatted, $formats, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTime|null $r
     */
    public function adate_tz_formatted(&$r, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_adate_tz_formatted($r, $dateFormatted, $formats, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeImmutable|null $r
     */
    public function idate_tz_formatted(&$r, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_idate_tz_formatted($r, $dateFormatted, $formats, $allowedTimezoneTypes);
    }


    /**
     * @param \DateTimeInterface|null $r
     */
    public function date_microtime(&$r, $microtime, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_date_microtime($r, $microtime, $timezoneFallback);
    }

    /**
     * @param \DateTime|null $r
     */
    public function adate_microtime(&$r, $microtime, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_adate_microtime($r, $microtime, $timezoneFallback);
    }

    /**
     * @param \DateTimeImmutable|null $r
     */
    public function idate_microtime(&$r, $microtime, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_idate_microtime($r, $microtime, $timezoneFallback);
    }


    /**
     * @param \DateInterval|null $r
     */
    public function interval(&$r, $interval) : bool
    {
        return Lib::date()->type_interval($r, $interval);
    }

    /**
     * @param \DateInterval|null $r
     */
    public function interval_duration(&$r, $duration) : bool
    {
        return Lib::date()->type_interval_duration($r, $duration);
    }

    /**
     * @param \DateInterval|null $r
     */
    public function interval_datestring(&$r, $datestring) : bool
    {
        return Lib::date()->type_interval_datestring($r, $datestring);
    }

    /**
     * @param \DateInterval|null $r
     */
    public function interval_microtime(&$r, $microtime) : bool
    {
        return Lib::date()->type_interval_microtime($r, $microtime);
    }

    /**
     * @param \DateInterval|null $r
     */
    public function interval_ago(&$r, $date, ?\DateTimeInterface $from = null, ?bool $reverse = null) : bool
    {
        return Lib::date()->type_interval_ago($r, $date, $from, $reverse);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $r
     * @param class-string<T>|T|mixed $value
     */
    public function struct_exists(&$r, $value, ?int $flags = null)
    {
        return Lib::php()->type_struct_exists($r, $value, $flags);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $r
     * @param class-string<T>|T|mixed $value
     */
    public function struct(&$r, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct($r, $value, $flags);
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $r
     * @param class-string<T>|T|mixed $value
     */
    public function struct_class(&$r, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_class($r, $value, $flags);
    }

    /**
     * @param class-string|null $r
     */
    public function struct_interface(&$r, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_interface($r, $value, $flags);
    }

    /**
     * @param class-string|null $r
     */
    public function struct_trait(&$r, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_trait($r, $value, $flags);
    }

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param class-string<T>|null    $r
     * @param class-string<T>|T|mixed $value
     */
    public function struct_enum(&$r, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_enum($r, $value, $flags);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $r
     * @param class-string<T>|T|mixed $value
     */
    public function struct_fqcn(&$r, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_fqcn($r, $value, $flags);
    }

    /**
     * @param string|null $r
     */
    public function struct_namespace(&$r, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_namespace($r, $value, $flags);
    }

    /**
     * @param string|null $r
     */
    public function struct_basename(&$r, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_basename($r, $value, $flags);
    }


    /**
     * @param resource|null $r
     */
    public function resource(&$r, $value, ?string $resourceType = null) : bool
    {
        return Lib::php()->type_resource($r, $value, $resourceType);
    }

    /**
     * @param resource|null $r
     */
    public function resource_opened(&$r, $value, ?string $resourceType = null) : bool
    {
        return Lib::php()->type_resource_opened($r, $value, $resourceType);
    }

    /**
     * @param resource|null $r
     */
    public function resource_closed(&$r, $value) : bool
    {
        return Lib::php()->type_resource_closed($r, $value);
    }

    /**
     * @param resource|null $r
     */
    public function any_not_resource(&$r, $value) : bool
    {
        return Lib::php()->type_any_not_resource($r, $value);
    }


    /**
     * @param resource|\CurlHandle|null $r
     */
    public function curl(&$r, $value) : bool
    {
        return Lib::php()->type_curl($r, $value);
    }

    /**
     * @param resource|\Socket|null $r
     */
    public function socket(&$r, $value) : bool
    {
        return Lib::php()->type_socket($r, $value);
    }


    /**
     * @template-covariant T of \UnitEnum
     *
     * @param T|null               $r
     * @param T|int|string         $value
     * @param class-string<T>|null $enumClass
     */
    public function enum_case(&$r, $value, ?string $enumClass = null) : bool
    {
        return Lib::php()->type_enum_case($r, $value, $enumClass);
    }


    /**
     * @param array{ 0: class-string, 1: string }|null $r
     */
    public function method_array(&$r, $value) : bool
    {
        return Lib::php()->type_method_array($r, $value);
    }

    /**
     * @param string|null $r
     */
    public function method_string(&$r, $value, array $refs = []) : bool
    {
        return Lib::php()->type_method_string($r, $value, $refs);
    }


    /**
     * @param callable|null $r
     * @param string|object $newScope
     */
    public function callable(&$r, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object($r, $value, $newScope);
    }

    /**
     * @param callable|\Closure|object|null $r
     */
    public function callable_object(&$r, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object($r, $value, $newScope);
    }

    /**
     * @param callable|object|null $r
     */
    public function callable_object_closure(&$r, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object_closure($r, $value, $newScope);
    }

    /**
     * @param callable|object|null $r
     */
    public function callable_object_invokable(&$r, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object_invokable($r, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $r
     * @param string|object                                            $newScope
     */
    public function callable_array(&$r, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array($r, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $r
     * @param string|object                                            $newScope
     */
    public function callable_array_method(&$r, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array_method($r, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $r
     * @param string|object                                     $newScope
     */
    public function callable_array_method_static(&$r, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array_method_static($r, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object, 1: string }|null $r
     * @param string|object                               $newScope
     */
    public function callable_array_method_non_static(&$r, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array_method_non_static($r, $value, $newScope);
    }

    /**
     * @param callable-string|null $r
     */
    public function callable_string(&$r, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_string($r, $value, $newScope);
    }

    /**
     * @param callable-string|null $r
     */
    public function callable_string_function(&$r, $value) : bool
    {
        return Lib::php()->type_callable_string_function($r, $value);
    }

    /**
     * @param callable-string|null $r
     */
    public function callable_string_function_internal(&$r, $value) : bool
    {
        return Lib::php()->type_callable_string_function_internal($r, $value);
    }

    /**
     * @param callable-string|null $r
     */
    public function callable_string_function_non_internal(&$r, $value) : bool
    {
        return Lib::php()->type_callable_string_function_non_internal($r, $value);
    }

    /**
     * @param callable-string|null $r
     */
    public function callable_string_method_static(&$r, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_string_method_static($r, $value, $newScope);
    }


    /**
     * @template T
     *
     * @param mixed|T        $r
     * @param int|string     $key
     * @param array{ 0?: T } $set
     */
    public function ref(&$r, $key, array $refs = [], array $set = []) : bool
    {
        return Lib::php()->type_ref($r, $key, $refs, $set);
    }


    /**
     * @param int|null $r
     * @param string   $value
     */
    public function chmod(&$r, $value) : bool
    {
        return Lib::fs()->type_chmod($r, $value);
    }


    /**
     * @param string|null            $r
     * @param array{ 0: array|null } $refs
     */
    public function path(&$r, $value, array $refs = []) : bool
    {
        return Lib::fs()->type_path($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function realpath(&$r, $value, ?bool $allowSymlink = null, array $refs = []) : bool
    {
        return Lib::fs()->type_realpath($r, $value, $allowSymlink, $refs);
    }

    /**
     * @param string|null            $r
     * @param array{ 0: array|null } $refs
     */
    public function freepath(&$r, $value, array $refs = []) : bool
    {
        return Lib::fs()->type_freepath($r, $value, $refs);
    }


    /**
     * @param string|null $r
     */
    public function dirpath(
        &$r,
        $value,
        ?bool $allowExists = null,
        ?bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_dirpath($r, $value, $allowExists, $allowSymlink, $refs);
    }

    /**
     * @param string|null $r
     */
    public function filepath(
        &$r,
        $value,
        ?bool $allowExists,
        ?bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_filepath($r, $value, $allowExists, $allowSymlink, $refs);
    }


    /**
     * @param string|null            $r
     * @param array{ 0: array|null } $refs
     */
    public function dirpath_realpath(&$r, $value, ?bool $allowSymlink = null, array $refs = []) : bool
    {
        return Lib::fs()->type_dirpath_realpath($r, $value, $allowSymlink, $refs);
    }

    /**
     * @param string|null $r
     */
    public function filepath_realpath(&$r, $value, ?bool $allowSymlink = null, array $refs = []) : bool
    {
        return Lib::fs()->type_filepath_realpath($r, $value, $allowSymlink, $refs);
    }


    /**
     * @param string|null $r
     */
    public function filename(&$r, $value) : bool
    {
        return Lib::fs()->type_filename($r, $value);
    }


    /**
     * @param \SplFileInfo|null $r
     */
    public function file(
        &$r,
        $value,
        ?array $extensions = null,
        ?array $mimeTypes = null,
        ?array $filters = null
    ) : bool
    {
        return Lib::fs()->type_file($r, $value, $extensions, $mimeTypes, $filters);
    }

    /**
     * @param \SplFileInfo|null $r
     */
    public function image(
        &$r,
        $value,
        ?array $extensions = null,
        ?array $mimeTypes = null,
        ?array $filters = null
    ) : bool
    {
        return Lib::fs()->type_image($r, $value, $extensions, $mimeTypes, $filters);
    }


    /**
     * @param string|null $r
     */
    public function email(&$r, $value, ?array $filters = null, array $refs = []) : bool
    {
        return Lib::social()->type_email($r, $value, $filters, $refs);
    }

    /**
     * @param string|null $r
     */
    public function email_fake(&$r, $value, array $refs = []) : bool
    {
        return Lib::social()->type_email_fake($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function email_non_fake(&$r, $value, ?array $filters = null, array $refs = []) : bool
    {
        return Lib::social()->type_email_non_fake($r, $value, $filters, $refs);
    }


    /**
     * @param string|null $r
     */
    public function phone(&$r, $value, array $refs = []) : bool
    {
        return Lib::social()->type_phone($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function phone_fake(&$r, $value, array $refs = []) : bool
    {
        return Lib::social()->type_phone_fake($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function phone_non_fake(&$r, $value, array $refs = []) : bool
    {
        return Lib::social()->type_phone_non_fake($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function phone_real(&$r, $value, ?string $region = '', array $refs = []) : bool
    {
        return Lib::social()->type_phone_real($r, $value, $region, $refs);
    }


    /**
     * @param string|null $r
     */
    public function tel(&$r, $value, array $refs = []) : bool
    {
        return Lib::social()->type_tel($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function tel_fake(&$r, $value, array $refs = []) : bool
    {
        return Lib::social()->type_tel_fake($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function tel_non_fake(&$r, $value, array $refs = []) : bool
    {
        return Lib::social()->type_tel_non_fake($r, $value, $refs);
    }

    /**
     * @param string|null $r
     */
    public function tel_real(&$r, $value, ?string $region = '', array $refs = []) : bool
    {
        return Lib::social()->type_tel_real($r, $value, $region, $refs);
    }
}
