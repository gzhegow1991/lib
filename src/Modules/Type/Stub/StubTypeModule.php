<?php

namespace Gzhegow\Lib\Modules\Type\Stub;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Nil;
use Gzhegow\Lib\Modules\Arr\ArrPath;
use Gzhegow\Lib\Modules\Type\Number;
use Gzhegow\Lib\Modules\Str\Alphabet;
use Gzhegow\Lib\Modules\Net\SubnetV4;
use Gzhegow\Lib\Modules\Net\SubnetV6;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Modules\Net\AddressIpV6;
use Gzhegow\Lib\Modules\Net\AddressIpV4;


class StubTypeModule
{
    /**
     * > Специальный тип-синоним NULL, переданный пользователем через API, например '{N}'
     * > в случаях, когда NULL интерпретируется как "не трогать", а NIL как "очистить"
     *
     * > NAN не равен ничему даже самому себе
     * > NIL равен только самому себе
     * > NULL означает пустоту и им можно заменить значения '', [], `resource (closed)`, NIL, но нельзя заменить NAN
     *
     * @param string|Nil|null $result
     */
    public function a_nil(&$result, $value) : bool
    {
        $result = null;

        if (Nil::is($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_nil(&$result, $value) : bool
    {
        $result = null;

        if (! Nil::is($value)) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param null $result
     */
    public function a_null(&$result, $value) : bool
    {
        $result = null;

        if (null === $value) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_null(&$result, $value) : bool
    {
        $result = null;

        if (null !== $value) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * > Специальный тип, который значит, что значение можно заменить NULL-ом
     *
     * @param mixed|null $result
     */
    public function a_nullable(&$result, $value) : bool
    {
        $result = null;

        // > NAN is not clearable (NAN means some error in the code and shouldnt be replaced)
        // > EMPTY ARRAY is not clearable (array functions is not applicable to nulls)
        // > COUNTABLE w/ ZERO SIZE is not clearable (countable/iterable functions is not applicable to nulls)

        if (
            // > NULL is clearable (means nothing)
            (null === $value)
            //
            // > EMPTY STRING is clearable (can appear from HTML forms with no input provided)
            || ('' === $value)
            //
            // > CLOSED RESOURCE is clearable (this is the internal garbage with no possible purpose)
            || ('resource (closed)' === gettype($value))
            //
            // > NIL is clearable (NIL should be replaced with NULL later or perform deleting actions)
            || $this->a_nil($var, $value)
        ) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_nullable(&$result, $value) : bool
    {
        $result = null;

        if (! $this->a_nullable($var, $value)) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param mixed|null $result
     */
    public function a_empty(&$result, $value) : bool
    {
        $result = null;

        if (empty($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_empty(&$result, $value) : bool
    {
        $result = null;

        if (! empty($value)) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * > Специальный тип, который значит, что значение можно отбросить или не учитывать, т.к. оно не несёт информации
     *
     * @param string|array|\Countable|null $result
     */
    public function a_blank(&$result, $value) : bool
    {
        $result = null;

        // > NAN is not blank (NAN equals nothing even itself)
        // > NIL is not blank (NIL is passed manually, that literally means NOT BLANK)
        // > CLOSED RESOURCE is not blank (actually its still internal object)

        if (
            // > NULL is blank (can appear from API to omit any actions on the value)
            (null === $value)
            //
            // > EMPTY STRING is blank (can appear from HTML forms with no input provided)
            || ('' === $value)
            //
            // > EMPTY ARRAY is blank (can appear from HTML forms with no checkbox/radio/select items choosen)
            || ([] === $value)
        ) {
            $result = $value;

            return true;
        }

        // > COUNTABLE w/ ZERO SIZE is blank
        if ($this->countable($countable, $value)) {
            if (0 === count($countable)) {
                $result = $value;

                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_blank(&$result, $value) : bool
    {
        $result = null;

        if (! $this->a_blank($var, $value)) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * > Специальный тип, который значит, что значение было отправлено пользователем, а не появилось из PHP
     *
     * @param mixed|null $result
     */
    public function a_passed(&$result, $value) : bool
    {
        $result = null;

        if ($this->a_nil($var, $value)) {
            $result = $value;

            return true;
        }

        if (
            // > NULL is not passed (can appear from API to omit any actions on the value)
            (null === $value)
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

        $result = $value;

        return true;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_passed(&$result, $value) : bool
    {
        $result = null;

        if (! $this->a_passed($var, $value)) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param bool|null $result
     */
    public function a_bool(&$result, $value) : bool
    {
        $result = null;

        if (is_bool($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function an_any_not_bool(&$result, $value) : bool
    {
        $result = null;

        if (! is_bool($value)) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param false|null $result
     */
    public function a_false(&$result, $value) : bool
    {
        $result = null;

        if (false === $value) {
            $result = false;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_false(&$result, $value) : bool
    {
        $result = null;

        if (false !== $value) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param true|null $result
     */
    public function a_true(&$result, $value) : bool
    {
        $result = null;

        if (true === $value) {
            $result = true;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_true(&$result, $value) : bool
    {
        $result = null;

        if (true !== $value) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param bool|null $result
     */
    public function bool(&$result, $value) : bool
    {
        $result = null;

        if (null === $value) {
            return false;
        }

        if (is_bool($value)) {
            $result = $value;

            return true;
        }

        if (is_int($value)) {
            $result = (0 !== $value);

            return true;
        }

        if (is_float($value)) {
            if (is_nan($value)) {
                return false;
            }

            $result = (0.0 !== $value);

            return true;
        }

        if ($this->a_nil($var, $value)) {
            return false;
        }

        if (is_string($value)) {
            if ('' === $value) {
                $result = false;

                return true;
            }

            $result = true;

            return true;
        }

        if (is_array($value)) {
            if ([] === $value) {
                $result = false;

                return true;
            }

            $result = true;

            return true;
        }

        if (is_resource($value)) {
            $result = true;

            return true;

        } elseif ('resource (closed)' === gettype($value)) {
            $result = false;

            return true;
        }

        if (is_object($value)) {
            if ($this->countable($countable, $value)) {
                if (0 === count($countable)) {
                    // > EMPTY COUNTABLE is false

                    $result = false;

                    return true;
                }
            }

            $result = true;

            return true;
        }

        return false;
    }

    /**
     * @param false|null $result
     */
    public function false(&$result, $value) : bool
    {
        $result = null;

        if (! $this->bool($bool, $value)) {
            return false;
        }

        if (false === $bool) {
            $result = false;

            return true;
        }

        return false;
    }

    /**
     * @param false|null $result
     */
    public function true(&$result, $value) : bool
    {
        $result = null;

        if (! $this->bool($bool, $value)) {
            return false;
        }

        if (true === $bool) {
            $result = true;

            return true;
        }

        return false;
    }


    /**
     * @param bool|null $result
     */
    public function userbool(&$result, $value) : bool
    {
        $result = null;

        if (null === $value) {
            return false;
        }

        if (is_bool($value)) {
            $result = $value;

            return true;
        }

        if (is_int($value)) {
            $result = (0 !== $value);

            return true;
        }

        if (is_float($value)) {
            if (is_nan($value)) {
                return false;
            }

            $result = (0.0 !== $value);

            return true;
        }

        if (is_string($value)) {
            $map = [
                //
                'true'  => true,
                'y'     => true,
                'yes'   => true,
                'on'    => true,
                '1'     => true,
                //
                'false' => false,
                'n'     => false,
                'no'    => false,
                'off'   => false,
                '0'     => false,
            ];

            $_value = strtolower($value);

            if (isset($map[ $_value ])) {
                $result = $map[ $_value ];

                return true;
            }
        }

        return false;
    }

    /**
     * @param false|null $result
     */
    public function userfalse(&$result, $value) : bool
    {
        $result = null;

        if (! $this->userbool($bool, $value)) {
            return false;
        }

        if (false === $bool) {
            $result = false;

            return true;
        }

        return false;
    }

    /**
     * @param false|null $result
     */
    public function usertrue(&$result, $value) : bool
    {
        $result = null;

        if (! $this->userbool($bool, $value)) {
            return false;
        }

        if (true === $bool) {
            $result = true;

            return true;
        }

        return false;
    }


    /**
     * @param float|null $result
     */
    public function a_nan(&$result, $value) : bool
    {
        $result = null;

        if (is_float($value) && is_nan($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param float|null $result
     */
    public function a_float_not_nan(&$result, $value) : bool
    {
        $result = null;

        if (is_float($value) && ! is_nan($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_nan(&$result, $value) : bool
    {
        $result = null;

        if (! (is_float($value) && is_nan($value))) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param float|null $result
     */
    public function a_finite(&$result, $value) : bool
    {
        $result = null;

        if (is_float($value) && is_finite($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param float|null $result
     */
    public function a_float_not_finite(&$result, $value) : bool
    {
        $result = null;

        if (is_float($value) && ! is_finite($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_finite(&$result, $value) : bool
    {
        $result = null;

        if (! (is_float($value) && is_finite($value))) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param float|null $result
     */
    public function a_infinite(&$result, $value) : bool
    {
        $result = null;

        if (is_float($value) && is_infinite($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param float|null $result
     */
    public function a_float_not_infinite(&$result, $value) : bool
    {
        $result = null;

        if (is_float($value) && ! is_infinite($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_infinite(&$result, $value) : bool
    {
        $result = null;

        if (! (is_float($value) && is_infinite($value))) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $result
     */
    public function numeric(&$result, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        $result = null;

        $isAllowExp = $isAllowExp ?? true;

        $withSplit = array_key_exists(0, $refs);

        if ($withSplit) {
            $refSplit =& $refs[ 0 ];
        }

        $refSplit = null;

        $isFloat = is_float($value);
        if ($isFloat) {
            if (! is_finite($value)) {
                // > NAN, INF, -INF is float, but should not be parsed

                unset($refSplit);

                return false;
            }
        }

        if (! $withSplit) {
            if ($isFloat || is_int($value)) {
                $valueString = (string) $value;

                if (! $isAllowExp) {
                    if (false !== strpos($valueString, 'e')) {
                        unset($refSplit);

                        return false;
                    }
                }

                $result = $valueString;

                unset($refSplit);

                return true;
            }
        }

        if (
            (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            // || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || ($this->a_nil($var, $value))
        ) {
            // > NULL is not numeric
            // > EMPTY STRING is not numeric
            // > BOOLEAN is not numeric
            // > ARRAY is not numeric
            // > RESOURCE is not numeric
            // > NIL is not numeric

            return false;
        }

        if ($value instanceof Number) {
            $number = $value;

            $exp = $number->getExp();

            if (! $isAllowExp) {
                if ('' !== $exp) {
                    unset($refSplit);

                    return false;
                }
            }

            $result = $number->getValue();

            if ($withSplit) {
                $refSplit = [];
                $refSplit[ 0 ] = $number->getSign();
                $refSplit[ 1 ] = $number->getInt();
                $refSplit[ 2 ] = $number->getFrac();
                $refSplit[ 3 ] = $exp;
            }

            unset($refSplit);

            return true;
        }

        if (! Lib::str()->type_trim($valueTrim, $value)) {
            unset($refSplit);

            return false;
        }

        $regex = ''
            . '/^'
            . '([+-]?)'
            . '((?:0|[1-9]\d*))'
            . '(\.\d+)?'
            . ($isAllowExp ? '([eE][+-]?\d+)?' : '')
            . '$/';

        if (! preg_match($regex, $valueTrim, $matches)) {
            unset($refSplit);

            return false;
        }

        [
            1 => $sign,
            2 => $int,
            3 => $frac,
            4 => $exp,
        ] = $matches + [ '', '', '', '', '' ];

        if ($sign === '+') {
            $sign = '';
        }

        $frac = rtrim($frac, '0.');

        $isZero = ! preg_match('/[1-9]/', "{$int}{$frac}");

        if ($isZero) {
            $sign = '';
            $int = '0';
            $frac = '';
            $exp = '';
        }

        if ($withSplit) {
            $refSplit = [];
            $refSplit[ 0 ] = $sign;
            $refSplit[ 1 ] = $int;
            $refSplit[ 2 ] = $frac;
            $refSplit[ 3 ] = $exp;
        }

        $valueNumeric = "{$sign}{$int}{$frac}{$exp}";

        $result = $valueNumeric;

        unset($refSplit);

        return true;
    }

    /**
     * @param string|null $result
     */
    public function numeric_non_zero(&$result, $value, ?bool $allowExp = null, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value, $allowExp, $refs)) {
            return false;
        }

        if ('0' !== $_value) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_non_negative(&$result, $value, ?bool $allowExp = null, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value, $allowExp, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            $result = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_non_positive(&$result, $value, ?bool $allowExp = null, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value, $allowExp, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            $result = $_value;

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_negative(&$result, $value, ?bool $allowExp = null, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value, $allowExp, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_positive(&$result, $value, ?bool $allowExp = null, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value, $allowExp, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            return false;
        }

        if ('-' !== $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $result
     */
    public function numeric_int(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        $withSplit = array_key_exists(0, $refs);

        $refSplit =& $refs[ 0 ];

        // > btw, 1.1e1 is can be converted to integer 11 too
        // > we better dont support that numbers here
        if (! $this->numeric($_value, $value, false, $refs)) {
            unset($refSplit);

            return false;
        }

        [ , , $frac ] = $refSplit;

        if ('' !== $frac) {
            unset($refSplit);

            if (! $withSplit) {
                unset($refs[ 0 ]);
            }

            return false;
        }

        $result = $_value;

        unset($refSplit);

        if (! $withSplit) {
            unset($refs[ 0 ]);
        }

        return true;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_non_zero(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' !== $_value) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_non_negative(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            $result = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_non_positive(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            $result = $_value;

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_negative(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_positive(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            return false;
        }

        if ('-' !== $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_positive_fallback(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            $result = $_value;

            return false;
        }

        if ('-1' === $_value) {
            $result = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_non_negative_fallback(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('-1' === $_value) {
            $result = $_value;

            return true;
        }

        if ('0' === $_value) {
            $result = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $result
     */
    public function numeric_float(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        $withSplit = array_key_exists(0, $refs);

        $refSplit =& $refs[ 0 ];

        // > btw, 1.1e-1 is can be converted to float 0.11 too
        // > we better dont support that numbers here
        if (! $this->numeric($_value, $value, false, $refs)) {
            unset($refSplit);

            return false;
        }

        [ $sign, $int, $frac ] = $refSplit;

        if ('' === $frac) {
            $frac = '.0';

            $_value = "{$sign}{$int}{$frac}";

            if ($withSplit) {
                $refSplit[ 3 ] = $frac;
            }
        }

        if ('0' === $_value) {
            $result = '0.0';

            unset($refSplit);

            if (! $withSplit) {
                unset($refs[ 0 ]);
            }

            return true;
        }

        $result = $_value;

        unset($refSplit);

        if (! $withSplit) {
            unset($refs[ 0 ]);
        }

        return true;
    }

    /**
     * @param string|null $result
     */
    public function numeric_float_non_zero(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_float($_value, $value, $refs)) {
            return false;
        }

        if ('0.0' !== $_value) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_float_non_negative(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_float($_value, $value, $refs)) {
            return false;
        }

        if ('0.0' === $_value) {
            $result = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_float_non_positive(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_float($_value, $value, $refs)) {
            return false;
        }

        if ('0.0' === $_value) {
            $result = $_value;

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_float_negative(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_float($_value, $value, $refs)) {
            return false;
        }

        if ('0.0' === $_value) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_float_positive(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->numeric_float($_value, $value, $refs)) {
            return false;
        }

        if ('0.0' === $_value) {
            return false;
        }

        if ('-' !== $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $result
     */
    public function decimal(&$result, $value, int $scale = 0, array $refs = []) : bool
    {
        $result = null;

        if ($scale < 0) return false;

        $withSplit = array_key_exists(0, $refs);

        $refSplit =& $refs[ 0 ];

        if (! $this->numeric($_value, $value, false, $refs)) {
            unset($refSplit);

            return false;
        }

        [ $sign, $int, $frac ] = $refSplit;

        $numericScale = 0;
        if ('' !== $frac) {
            $numericScale = strlen($frac) - 1;
        }

        if ($numericScale > $scale) {
            unset($refSplit);

            if (! $withSplit) {
                unset($refs[ 0 ]);
            }

            return false;
        }

        if ($numericScale < $scale) {
            if ('' === $frac) {
                $frac = '.';
            }

            $frac = str_pad($frac, $scale + 1, '0', STR_PAD_RIGHT);

            $_value = "{$sign}{$int}{$frac}";

            if ($withSplit) {
                $refSplit[ 3 ] = $frac;
            }
        }

        $result = $_value;

        unset($refSplit);

        if (! $withSplit) {
            unset($refs[ 0 ]);
        }

        return true;
    }

    /**
     * @param string|null $result
     */
    public function decimal_non_zero(&$result, $value, int $scale = 0, array $refs = []) : bool
    {
        $result = null;

        if (! $this->decimal($_value, $value, $scale, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function decimal_non_negative(&$result, $value, int $scale = 0, array $refs = []) : bool
    {
        $result = null;

        if (! $this->decimal($_value, $value, $scale, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            $result = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function decimal_non_positive(&$result, $value, int $scale = 0, array $refs = []) : bool
    {
        $result = null;

        if (! $this->decimal($_value, $value, $scale, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            $result = $_value;

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function decimal_negative(&$result, $value, int $scale = 0, array $refs = []) : bool
    {
        $result = null;

        if (! $this->decimal($_value, $value, $scale, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function decimal_positive(&$result, $value, int $scale = 0, array $refs = []) : bool
    {
        $result = null;

        if (! $this->decimal($_value, $value, $scale, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            return false;
        }

        if ('-' !== $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param int|float|null $result
     */
    public function num(&$result, $value) : bool
    {
        $result = null;

        if (is_int($value)) {
            $result = $value;

            return true;
        }

        if (is_float($value)) {
            if (! is_finite($value)) {
                // > NAN, INF, -INF is float, but should not be parsed
                return false;
            }

            $result = $value;

            return true;
        }

        if (
            (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            // || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || ($this->a_nil($var, $value))
        ) {
            // > NULL is not num
            // > EMPTY STRING is not num
            // > BOOLEAN is not num
            // > ARRAY is not num
            // > RESOURCE is not num
            // > NIL is not num

            return false;
        }

        if (! $this->numeric($valueNumeric, $value, true, [ &$split ])) {
            return false;
        }

        $map = [];

        if (PHP_INT_SIZE === 8) {
            $map += [
                ' ' . ((string) PHP_INT_MAX)  => PHP_INT_MAX,
                ' ' . ((string) -PHP_INT_MAX) => -PHP_INT_MAX,
                ' ' . ((string) PHP_INT_MIN)  => PHP_INT_MIN,
                //
                ' 9223372036854775807'        => PHP_INT_MAX,
                ' -9223372036854775807'       => -PHP_INT_MAX,
                ' -9223372036854775808'       => PHP_INT_MIN,
            ];

        } elseif (PHP_INT_SIZE === 4) {
            $map += [
                ' ' . ((string) PHP_INT_MAX)  => PHP_INT_MAX,
                ' ' . ((string) -PHP_INT_MAX) => -PHP_INT_MAX,
                ' ' . ((string) PHP_INT_MIN)  => PHP_INT_MIN,
                //
                ' 2147483647'                 => PHP_INT_MAX,
                ' -2147483647'                => -PHP_INT_MAX,
                ' -2147483648'                => PHP_INT_MIN,
            ];
        }

        $map += [
            ' ' . ((string) PHP_FLOAT_MAX)  => PHP_FLOAT_MAX,
            ' ' . ((string) PHP_FLOAT_MIN)  => PHP_FLOAT_MIN,
            //
            ' ' . ((string) -PHP_FLOAT_MAX) => -PHP_FLOAT_MAX,
            ' ' . ((string) -PHP_FLOAT_MIN) => -PHP_FLOAT_MIN,
            //
            ' 1.797693134862316E+308'       => PHP_FLOAT_MAX,
            ' 1.7976931348623157E+308'      => PHP_FLOAT_MAX,
            //
            ' 2.225073858507201E-308'       => PHP_FLOAT_MIN,
            ' 2.2250738585072014E-308'      => PHP_FLOAT_MIN,
            //
            ' -1.797693134862316E+308'      => -PHP_FLOAT_MAX,
            ' -1.7976931348623157E+308'     => -PHP_FLOAT_MAX,
            //
            ' -2.225073858507201E-308'      => -PHP_FLOAT_MIN,
            ' -2.2250738585072014E-308'     => -PHP_FLOAT_MIN,
        ];

        if (isset($map[ $key = ' ' . $valueNumeric ])) {
            $result = $map[ $key ];

            return true;
        }

        $valueFloat = null;
        $valueFloat17g = null;

        $hasExponent = ('' !== $split[ 3 ]);
        if ($hasExponent) {
            $valueFloat = floatval($valueNumeric);

        } else {
            // > IEEE 754 double-precision floating point (64-bit float)
            $valueFloat17g = floatval(sprintf('%.17g', $valueNumeric));
        }

        $valueMaybeFloat = $valueFloat17g ?? $valueFloat;

        if (0.0 === $valueMaybeFloat) {
            if ($valueNumeric !== '0') {
                return false;
            }
        }

        if (! is_finite($valueMaybeFloat)) {
            return false;
        }

        if (null !== $valueFloat) {
            $result = $valueFloat;

            return true;
        }

        if (null !== $valueFloat17g) {
            if (
                ($valueFloat17g < (float) PHP_INT_MIN)
                || ($valueFloat17g > (float) PHP_INT_MAX)
            ) {
                $result = $valueFloat17g;

                return true;
            }

            $valueInt = intval($valueFloat17g);

            if ($valueFloat17g === floatval($valueInt)) {
                $result = $valueInt;

                return true;
            }

            $result = $valueFloat17g;

            return true;
        }

        return false;
    }

    /**
     * @param int|float|null $result
     */
    public function num_non_zero(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|float|null $result
     */
    public function num_non_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if ($_value < 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|float|null $result
     */
    public function num_non_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if ($_value > 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|float|null $result
     */
    public function num_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if ($_value >= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|float|null $result
     */
    public function num_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if ($_value <= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param int|null $result
     */
    public function int(&$result, $value) : bool
    {
        $result = null;

        if (is_int($value)) {
            $result = $value;

            return true;
        }

        if (
            (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && ! is_finite($value))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || ($this->a_nil($var, $value))
        ) {
            // > NULL is not int
            // > EMPTY STRING is not int
            // > BOOLEAN is not int
            // > ARRAY is not int
            // > NAN, INF, -INF is not int
            // > RESOURCE is not int
            // > NIL is not int

            return false;
        }

        if (! $this->num($_value, $value)) {
            return false;
        }

        if (! is_int($_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|null $result
     */
    public function int_non_zero(&$result, $value) : bool
    {
        $result = null;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value === 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|null $result
     */
    public function int_non_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value < 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|null $result
     */
    public function int_non_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value > 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|null $result
     */
    public function int_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value >= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|null $result
     */
    public function int_positive(&$result, $value) : bool
    {
        $result = false;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value <= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function int_positive_fallback(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if (-1 === $_value) {
            $result = $_value;

            return true;
        }

        if ($_value <= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function int_non_negative_fallback(&$result, $value, array $refs = []) : bool
    {
        $result = null;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value < -1) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param float|null $result
     */
    public function float(&$result, $value) : bool
    {
        $result = null;

        if (is_int($value)) {
            $result = (float) $value;

            return true;

        } elseif (is_float($value)) {
            if (! is_finite($value)) {
                // > NAN, INF, -INF is float, but should not be parsed
                return false;
            }

            $result = $value;

            return true;
        }

        if (
            (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            // || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || ($this->a_nil($var, $value))
        ) {
            // > NULL is not float
            // > EMPTY STRING is not float
            // > BOOLEAN is not float
            // > ARRAY is not float
            // > RESOURCE is not float
            // > NIL is not float

            return false;
        }

        if (! $this->numeric($valueNumeric, $value, true, [ &$split ])) {
            return false;
        }

        $map = [
            ' ' . ((string) PHP_FLOAT_MAX)  => PHP_FLOAT_MAX,
            ' ' . ((string) PHP_FLOAT_MIN)  => PHP_FLOAT_MIN,
            //
            ' ' . ((string) -PHP_FLOAT_MAX) => -PHP_FLOAT_MAX,
            ' ' . ((string) -PHP_FLOAT_MIN) => -PHP_FLOAT_MIN,
            //
            ' 1.797693134862316E+308'       => PHP_FLOAT_MAX,
            ' 1.7976931348623157E+308'      => PHP_FLOAT_MAX,
            //
            ' 2.225073858507201E-308'       => PHP_FLOAT_MIN,
            ' 2.2250738585072014E-308'      => PHP_FLOAT_MIN,
            //
            ' -1.797693134862316E+308'      => -PHP_FLOAT_MAX,
            ' -1.7976931348623157E+308'     => -PHP_FLOAT_MAX,
            //
            ' -2.225073858507201E-308'      => -PHP_FLOAT_MIN,
            ' -2.2250738585072014E-308'     => -PHP_FLOAT_MIN,
        ];

        if (isset($map[ $key = ' ' . $valueNumeric ])) {
            $result = $map[ $key ];

            return true;
        }

        $valueFloat = floatval($valueNumeric);

        if (0.0 === $valueFloat) {
            if ($valueNumeric !== '0') {
                $valueFloat = null;
            }
        }

        if (! is_finite($valueFloat)) {
            $valueFloat = null;
        }

        if (null !== $valueFloat) {
            $result = $valueFloat;

            return true;
        }

        return false;
    }

    /**
     * @param float|null $result
     */
    public function float_non_zero(&$result, $value) : bool
    {
        $result = null;

        if (! $this->float($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param float|null $result
     */
    public function float_non_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->float($_value, $value)) {
            return false;
        }

        if ($_value < 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param float|null $result
     */
    public function float_non_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->float($_value, $value)) {
            return false;
        }

        if ($_value > 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param float|null $result
     */
    public function float_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->float($_value, $value)) {
            return false;
        }

        if ($_value >= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param float|null $result
     */
    public function float_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->float($_value, $value)) {
            return false;
        }

        if ($_value <= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param Number|null $result
     */
    public function number(&$result, $value, ?bool $allowExp = null) : bool
    {
        $result = null;

        if ($value instanceof Number) {
            $result = $value;

            return true;
        }

        $status = $this->numeric($valueNumeric, $value, $allowExp, [ &$split ]);

        if ($status) {
            $frac = $split[ 2 ];

            $scale = 0;
            if ('' !== $frac) {
                $scale = strlen($frac) - 1;
            }

            $number = Number::fromValidArray([
                'original' => $value,
                'sign'     => $split[ 0 ],
                'int'      => $split[ 1 ],
                'frac'     => $split[ 2 ],
                'exp'      => $split[ 3 ],
                'scale'    => $scale,
            ]);

            $result = $number;

            return true;
        }

        return false;
    }

    /**
     * @param Bcnumber|null $result
     */
    public function bcnumber(&$result, $value) : bool
    {
        return Lib::bcmath()->type_bcnumber($result, $value);
    }


    /**
     * @param string|null $result
     */
    public function a_string(&$result, $value) : bool
    {
        return Lib::str()->type_a_string($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function a_string_empty(&$result, $value) : bool
    {
        return Lib::str()->type_a_string_empty($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function a_string_not_empty(&$result, $value) : bool
    {
        return Lib::str()->type_a_string_not_empty($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function a_trim(&$result, $value) : bool
    {
        return Lib::str()->type_a_trim($result, $value);
    }


    /**
     * @param string|null $result
     */
    public function string(&$result, $value) : bool
    {
        return Lib::str()->type_string($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function string_empty(&$result, $value) : bool
    {
        return Lib::str()->type_string_empty($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function string_not_empty(&$result, $value) : bool
    {
        return Lib::str()->type_string_not_empty($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function trim(&$result, $value, ?string $characters = null) : bool
    {
        return Lib::str()->type_trim($result, $value, $characters);
    }


    /**
     * @param string|null $result
     */
    public function char(&$result, $value) : bool
    {
        return Lib::str()->type_char($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function letter(&$result, $value) : bool
    {
        return Lib::str()->type_letter($result, $value);
    }

    /**
     * @param Alphabet|null $result
     */
    public function alphabet(&$result, $value) : bool
    {
        return Lib::str()->type_alphabet($result, $value);
    }


    /**
     * @param string|null $result
     */
    public function ctype_digit(&$result, $value) : bool
    {
        $result = null;

        if (! $this->string_not_empty($_value, $value)) {
            return false;
        }

        if (extension_loaded('ctype')) {
            if (ctype_digit($_value)) {
                $result = $_value;

                return true;
            }

            return false;
        }

        if (! preg_match('~[^0-9]~', $_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function ctype_alpha(&$result, $value, ?bool $isIgnoreCase = null) : bool
    {
        $result = null;

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
                $result = $_value;

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

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function ctype_alnum(&$result, $value, ?bool $isIgnoreCase = null) : bool
    {
        $result = null;

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
                $result = $_value;

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

        $result = $_value;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function base(&$result, $value, $alphabet) : bool
    {
        return Lib::crypt()->type_base($result, $value, $alphabet);
    }

    /**
     * @param string|null $result
     */
    public function base_bin(&$result, $value) : bool
    {
        return Lib::crypt()->type_base_bin($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function base_oct(&$result, $value) : bool
    {
        return Lib::crypt()->type_base_oct($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function base_dec(&$result, $value) : bool
    {
        return Lib::crypt()->type_base_dec($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function base_hex(&$result, $value) : bool
    {
        return Lib::crypt()->type_base_hex($result, $value);
    }


    /**
     * @param array|null $result
     */
    public function array_empty(&$result, $value) : bool
    {
        $result = null;

        if ([] === $value) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param array|null $result
     */
    public function array_not_empty(&$result, $value) : bool
    {
        $result = null;

        if (is_array($value) && ([] !== $value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $result
     */
    public function any_not_array_empty(&$result, $value) : bool
    {
        $result = null;

        if ([] !== $value) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * @param mixed|null $result
     */
    public function key_exists(&$result, $value, $key) : bool
    {
        return Lib::arr()->type_key_exists($result, $value, $key);
    }


    /**
     * @param array|null $result
     */
    public function array_plain(&$result, $value) : bool
    {
        return Lib::arr()->type_array_plain($result, $value);
    }


    /**
     * @param array|null $result
     */
    public function list(&$result, $value, ?bool $isPlain = null) : bool
    {
        return Lib::arr()->type_list($result, $value, $isPlain);
    }

    /**
     * @param array|null $result
     */
    public function list_sorted(&$result, $value, ?bool $isPlain = null) : bool
    {
        return Lib::arr()->type_list_sorted($result, $value, $isPlain);
    }


    /**
     * @param array|null $result
     */
    public function dict(&$result, $value, ?bool $isPlain = null) : bool
    {
        return Lib::arr()->type_dict($result, $value, $isPlain);
    }

    /**
     * @param array|null $result
     */
    public function dict_sorted(&$result, $value, ?bool $isPlain = null) : bool
    {
        return Lib::arr()->type_dict_sorted($result, $value, $isPlain);
    }


    /**
     * @param array|null $result
     */
    public function table(&$result, $value) : bool
    {
        return Lib::arr()->type_table($result, $value);
    }

    /**
     * @param array|null $result
     */
    public function matrix(&$result, $value) : bool
    {
        return Lib::arr()->type_matrix($result, $value);
    }

    /**
     * @param array|null $result
     */
    public function matrix_strict(&$result, $value) : bool
    {
        return Lib::arr()->type_matrix_strict($result, $value);
    }


    /**
     * @param ArrPath|null $result
     */
    public function arrpath(&$result, $path, ?string $dot = null) : bool
    {
        return Lib::arr()->type_arrpath($result, $path, $dot);
    }


    /**
     * @param string|null $result
     */
    public function regex(&$result, $value) : bool
    {
        return Lib::preg()->type_regex($result, $value);
    }


    /**
     * @param AddressIpV4|AddressIpV6|null $result
     */
    public function address_ip(&$result, $value) : bool
    {
        return Lib::net()->type_address_ip($result, $value);
    }

    /**
     * @param AddressIpV4|null $result
     */
    public function address_ip_v4(&$result, $value) : bool
    {
        return Lib::net()->type_address_ip_v4($result, $value);
    }

    /**
     * @param AddressIpV6|null $result
     */
    public function address_ip_v6(&$result, $value) : bool
    {
        return Lib::net()->type_address_ip_v6($result, $value);
    }


    /**
     * @param string|null $result
     */
    public function address_mac(&$result, $value) : bool
    {
        return Lib::net()->type_address_mac($result, $value);
    }


    /**
     * @param SubnetV4|SubnetV6|null $result
     */
    public function subnet(&$result, $value, ?string $ipFallback = null) : bool
    {
        return Lib::net()->type_subnet($result, $value, $ipFallback);
    }

    /**
     * @param SubnetV4|null $result
     */
    public function subnet_v4(&$result, $value, ?string $ipFallback = null) : bool
    {
        return Lib::net()->type_subnet_v4($result, $value, $ipFallback);
    }

    /**
     * @param SubnetV6|null $result
     */
    public function subnet_v6(&$result, $value, ?string $ipFallback = null) : bool
    {
        return Lib::net()->type_subnet_v6($result, $value, $ipFallback);
    }


    /**
     * @param string|null $result
     */
    public function url(
        &$result,
        $value, $query = null, $fragment = null,
        array $refs = []
    ) : bool
    {
        return Lib::url()->type_url($result, $value, $query, $fragment, $refs);
    }

    /**
     * @param string|null $result
     */
    public function host(
        &$result,
        $value,
        array $refs = []
    ) : bool
    {
        return Lib::url()->type_host($result, $value, $refs);
    }

    /**
     * @param string|null $result
     */
    public function link(
        &$result,
        $value, $query = null, $fragment = null,
        array $refs = []
    ) : bool
    {
        return Lib::url()->type_link($result, $value, $query, $fragment, $refs);
    }


    /**
     * @param string|null $result
     */
    public function uuid(&$result, $value) : bool
    {
        return Lib::random()->type_uuid($result, $value);
    }


    /**
     * @param array|\Countable|null $result
     */
    public function countable(&$result, $value) : bool
    {
        return Lib::php()->type_countable($result, $value);
    }

    /**
     * @param \Countable|null $result
     */
    public function countable_object(&$result, $value) : bool
    {
        return Lib::php()->type_countable_object($result, $value);
    }

    /**
     * @param string|array|\Countable|null $result
     */
    public function sizeable(&$result, $value) : bool
    {
        return Lib::php()->type_sizeable($result, $value);
    }


    /**
     * @param \DateTimeZone|null $result
     */
    public function timezone(&$result, $timezone, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_timezone($result, $timezone, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeZone|null $result
     */
    public function timezone_offset(&$result, $timezoneOrOffset) : bool
    {
        return Lib::date()->type_timezone_offset($result, $timezoneOrOffset);
    }

    /**
     * @param \DateTimeZone|null $result
     */
    public function timezone_abbr(&$result, $timezoneOrAbbr) : bool
    {
        return Lib::date()->type_timezone_abbr($result, $timezoneOrAbbr);
    }

    /**
     * @param \DateTimeZone|null $result
     */
    public function timezone_name(&$result, $timezoneOrName) : bool
    {
        return Lib::date()->type_timezone_name($result, $timezoneOrName);
    }

    /**
     * @param \DateTimeZone|null $result
     */
    public function timezone_nameabbr(&$result, $timezoneOrNameOrAbbr) : bool
    {
        return Lib::date()->type_timezone_nameabbr($result, $timezoneOrNameOrAbbr);
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function date(&$result, $datestring, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_date($result, $datestring, $timezoneFallback);
    }

    /**
     * @param \DateTime|null $result
     */
    public function adate(&$result, $datestring, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_adate($result, $datestring, $timezoneFallback);
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function idate(&$result, $datestring, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_idate($result, $datestring, $timezoneFallback);
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function date_formatted(&$result, $dateFormatted, $formats, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_date_formatted($result, $dateFormatted, $formats, $timezoneFallback);
    }

    /**
     * @param \DateTime|null $result
     */
    public function adate_formatted(&$result, $dateFormatted, $formats, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_adate_formatted($result, $dateFormatted, $formats, $timezoneFallback);
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function idate_formatted(&$result, $dateFormatted, $formats, $timezoneFallback = null) : bool
    {
        return Lib::date()->type_idate_formatted($result, $dateFormatted, $formats, $timezoneFallback);
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function date_tz(&$result, $datestring, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_date_tz($result, $datestring, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTime|null $result
     */
    public function adate_tz(&$result, $datestring, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_adate_tz($result, $datestring, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function idate_tz(&$result, $datestring, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_idate_tz($result, $datestring, $allowedTimezoneTypes);
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function date_tz_formatted(&$result, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_date_tz_formatted($result, $dateFormatted, $formats, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTime|null $result
     */
    public function adate_tz_formatted(&$result, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_adate_tz_formatted($result, $dateFormatted, $formats, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function idate_tz_formatted(&$result, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_idate_tz_formatted($result, $dateFormatted, $formats, $allowedTimezoneTypes);
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function date_microtime(&$result, $microtime, $timezoneSet = null) : bool
    {
        return Lib::date()->type_date_microtime($result, $microtime, $timezoneSet);
    }

    /**
     * @param \DateTime|null $result
     */
    public function adate_microtime(&$result, $microtime, $timezoneSet = null) : bool
    {
        return Lib::date()->type_adate_microtime($result, $microtime, $timezoneSet);
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function idate_microtime(&$result, $microtime, $timezoneSet = null) : bool
    {
        return Lib::date()->type_idate_microtime($result, $microtime, $timezoneSet);
    }


    /**
     * @param \DateInterval|null $result
     */
    public function interval(&$result, $interval) : bool
    {
        return Lib::date()->type_interval($result, $interval);
    }

    /**
     * @param \DateInterval|null $result
     */
    public function interval_duration(&$result, $duration) : bool
    {
        return Lib::date()->type_interval_duration($result, $duration);
    }

    /**
     * @param \DateInterval|null $result
     */
    public function interval_datestring(&$result, $datestring) : bool
    {
        return Lib::date()->type_interval_datestring($result, $datestring);
    }

    /**
     * @param \DateInterval|null $result
     */
    public function interval_microtime(&$result, $microtime) : bool
    {
        return Lib::date()->type_interval_microtime($result, $microtime);
    }

    /**
     * @param \DateInterval|null $result
     */
    public function interval_ago(&$result, $date, ?\DateTimeInterface $from = null, ?bool $reverse = null) : bool
    {
        return Lib::date()->type_interval_ago($result, $date, $from, $reverse);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function struct_exists(&$result, $value, ?int $flags = null)
    {
        return Lib::php()->type_struct_exists($result, $value, $flags);
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function struct(&$result, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct($result, $value, $flags);
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function struct_class(&$result, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_class($result, $value, $flags);
    }

    /**
     * @param class-string|null $result
     */
    public function struct_interface(&$result, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_interface($result, $value, $flags);
    }

    /**
     * @param class-string|null $result
     */
    public function struct_trait(&$result, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_trait($result, $value, $flags);
    }

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function struct_enum(&$result, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_enum($result, $value, $flags);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function struct_fqcn(&$result, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_fqcn($result, $value, $flags);
    }

    /**
     * @param string|null $result
     */
    public function struct_namespace(&$result, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_namespace($result, $value, $flags);
    }

    /**
     * @param string|null $result
     */
    public function struct_basename(&$result, $value, ?int $flags = null) : bool
    {
        return Lib::php()->type_struct_basename($result, $value, $flags);
    }


    /**
     * @param resource|null $result
     */
    public function resource(&$result, $value) : bool
    {
        return Lib::php()->type_resource($result, $value);
    }

    /**
     * @param resource|null $result
     */
    public function any_not_resource(&$result, $value) : bool
    {
        return Lib::php()->type_any_not_resource($result, $value);
    }


    /**
     * @param resource|null $result
     */
    public function resource_opened(&$result, $value) : bool
    {
        return Lib::php()->type_resource_opened($result, $value);
    }

    /**
     * @param resource|null $result
     */
    public function resource_closed(&$result, $value) : bool
    {
        return Lib::php()->type_resource_closed($result, $value);
    }


    /**
     * @template-covariant T of \UnitEnum
     *
     * @param T|null               $result
     * @param T|int|string         $value
     * @param class-string<T>|null $enumClass
     */
    public function enum_case(&$result, $value, ?string $enumClass = null) : bool
    {
        return Lib::php()->type_enum_case($result, $value, $enumClass);
    }


    /**
     * @param array{ 0: class-string, 1: string }|null $result
     */
    public function method_array(&$result, $value) : bool
    {
        return Lib::php()->type_method_array($result, $value);
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function method_string(&$result, $value, array $refs = []) : bool
    {
        return Lib::php()->type_method_string($result, $value, $refs);
    }


    /**
     * @param callable|null $result
     * @param string|object $newScope
     */
    public function callable(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object($result, $value, $newScope);
    }


    /**
     * @param callable|\Closure|object|null $result
     */
    public function callable_object(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object($result, $value, $newScope);
    }

    /**
     * @param callable|object|null $result
     */
    public function callable_object_closure(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object_closure($result, $value, $newScope);
    }

    /**
     * @param callable|object|null $result
     */
    public function callable_object_invokable(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object_invokable($result, $value, $newScope);
    }


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function callable_array(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function callable_array_method(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array_method($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $result
     * @param string|object                                     $newScope
     */
    public function callable_array_method_static(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array_method_static($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object, 1: string }|null $result
     * @param string|object                               $newScope
     */
    public function callable_array_method_non_static(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array_method_non_static($result, $value, $newScope);
    }


    /**
     * @param callable-string|null $result
     */
    public function callable_string(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_string($result, $value, $newScope);
    }

    /**
     * @param callable-string|null $result
     */
    public function callable_string_function(&$result, $value) : bool
    {
        return Lib::php()->type_callable_string_function($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function callable_string_function_internal(&$result, $value) : bool
    {
        return Lib::php()->type_callable_string_function_internal($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function callable_string_function_non_internal(&$result, $value) : bool
    {
        return Lib::php()->type_callable_string_function_non_internal($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function callable_string_method_static(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_string_method_static($result, $value, $newScope);
    }


    /**
     * @template T
     *
     * @param mixed|T        $result
     * @param int|string     $key
     * @param array{ 0?: T } $set
     */
    public function ref(&$result, $key, array $refs = [], array $set = []) : bool
    {
        return Lib::php()->type_ref($result, $key, $refs, $set);
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function path(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_path($result, $value, $refs);
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function realpath(
        &$result, $value,
        ?bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_realpath(
            $result, $value,
            $allowSymlink,
            $refs
        );
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function freepath(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_freepath($result, $value, $refs);
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function dirpath(
        &$result, $value,
        ?bool $allowExists = null, ?bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_dirpath(
            $result, $value,
            $allowExists, $allowSymlink,
            $refs
        );
    }

    /**
     * @param string|null $result
     */
    public function filepath(
        &$result, $value,
        ?bool $allowExists = null, ?bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_filepath(
            $result, $value,
            $allowExists, $allowSymlink,
            $refs
        );
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function dirpath_realpath(
        &$result, $value,
        ?bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_dirpath_realpath(
            $result, $value,
            $allowSymlink,
            $refs
        );
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function filepath_realpath(
        &$result, $value,
        ?bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_filepath_realpath(
            $result,
            $value, $allowSymlink,
            $refs
        );
    }


    /**
     * @param string|null $result
     */
    public function filename(&$result, $value) : bool
    {
        return Lib::fs()->type_filename($result, $value);
    }


    /**
     * @param \SplFileInfo|null $result
     */
    public function file(
        &$result, $value,
        ?array $extensions = null, ?array $mimeTypes = null,
        ?array $filters = null
    ) : bool
    {
        return Lib::fs()->type_file(
            $result, $value,
            $extensions, $mimeTypes,
            $filters
        );
    }

    /**
     * @param \SplFileInfo|null $result
     */
    public function image(
        &$result, $value,
        ?array $extensions = null, ?array $mimeTypes = null,
        ?array $filters = null
    ) : bool
    {
        return Lib::fs()->type_image(
            $result, $value,
            $extensions, $mimeTypes,
            $filters
        );
    }


    /**
     * @param string|null $result
     */
    public function email(
        &$result, $value, ?array $filters = null,
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_email(
            $result, $value,
            $filters,
            $refs
        );
    }

    /**
     * @param string|null $result
     */
    public function email_fake(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_email_fake(
            $result, $value,
            $refs
        );
    }

    /**
     * @param string|null $result
     */
    public function email_non_fake(
        &$result, $value, ?array $filters = null,
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_email_non_fake(
            $result, $value,
            $filters,
            $refs
        );
    }


    /**
     * @param string|null $result
     */
    public function phone(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_phone(
            $result, $value,
            $refs,
        );
    }

    /**
     * @param string|null $result
     */
    public function phone_fake(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_phone_fake(
            $result, $value,
            $refs,
        );
    }

    /**
     * @param string|null $result
     */
    public function phone_non_fake(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_phone_non_fake(
            $result, $value,
            $refs,
        );
    }

    /**
     * @param string|null $result
     */
    public function phone_real(
        &$result, $value, ?string $region = '',
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_phone_real(
            $result, $value, $region,
            $refs,
        );
    }


    /**
     * @param string|null $result
     */
    public function tel(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_tel(
            $result, $value,
            $refs,
        );
    }

    /**
     * @param string|null $result
     */
    public function tel_fake(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_tel_fake(
            $result, $value,
            $refs,
        );
    }

    /**
     * @param string|null $result
     */
    public function tel_non_fake(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_tel_non_fake(
            $result, $value,
            $refs,
        );
    }

    /**
     * @param string|null $result
     */
    public function tel_real(
        &$result, $value, ?string $region = '',
        array $refs = []
    ) : bool
    {
        return Lib::social()->type_tel_real(
            $result, $value, $region,
            $refs,
        );
    }
}
