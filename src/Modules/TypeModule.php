<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Arr\ArrPath;
use Gzhegow\Lib\Modules\Type\Number;
use Gzhegow\Lib\Modules\Str\Alphabet;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Modules\Type\Base\TypeModuleBase;


class TypeModule extends TypeModuleBase
{
    /**
     * @param bool|null $result
     */
    public function bool(&$result, $value) : bool
    {
        $result = null;

        if (is_bool($value)) {
            $result = $value;

            return true;
        }

        if (
            (null === $value)
            || (is_float($value) && is_nan($value))
            || ($this->is_nil($value))
        ) {
            // > NULL is not bool
            // > NAN is not bool
            // > NIL is not bool

            return false;
        }

        if ('0' === $value) {
            // > ANY NON-EMPTY STRING is true
            $result = true;

            return true;
        }

        if (0 === ($cnt = Lib::php()->count($value))) {
            // > EMPTY COUNTABLE is false

            $result = false;

            return true;
        }

        if ('resource (closed)' === gettype($value)) {
            // > CLOSED RESOURCE is false

            $result = false;

            return true;
        }

        $result = (bool) $value;

        return true;
    }

    /**
     * @param bool|null $result
     */
    public function userbool(&$result, $value) : bool
    {
        $result = null;

        if (is_bool($value)) {
            $result = $value;

            return true;
        }

        if (
            (null === $value)
            || (is_float($value) && is_nan($value))
            || ($this->is_nil($value))
        ) {
            // > NULL is not bool
            // > NAN is not bool
            // > NIL is not bool

            return false;
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

        if (0 === ($cnt = Lib::php()->count($value))) {
            // > EMPTY COUNTABLE is false

            $result = false;

            return true;
        }

        if ('resource (closed)' === gettype($value)) {
            // > CLOSED RESOURCE is false

            $result = false;

            return true;
        }

        $result = (bool) $value;

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
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && (! is_finite($value)))
            || (is_resource($value))
            || ('resource (closed)' === gettype($value))
            || ($this->is_nil($value))
        ) {
            // > NULL is not int
            // > BOOLEAN is not int
            // > ARRAY is not int
            // > NAN, INF, -INF is not int
            // > RESOURCE is not int
            // > CLOSED RESOURCE is not int
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
                return false;
            }

            $result = $value;

            return true;
        }

        if (
            (null === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_resource($value))
            || ('resource (closed)' === gettype($value))
            || ($this->is_nil($value))
        ) {
            // > NULL is not float
            // > BOOLEAN is not float
            // > ARRAY is not float
            // > RESOURCE is not float
            // > CLOSED RESOURCE is not float
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
     * @param int|float|null $result
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
     * @param int|float|null $result
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
     * @param int|float|null $result
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
     * @param int|float|null $result
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
     * @param int|float|null $result
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
                return false;
            }

            $result = $value;

            return true;
        }

        if (
            (null === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_resource($value))
            || ('resource (closed)' === gettype($value))
            || ($this->is_nil($value))
        ) {
            // > NULL is not num
            // > BOOLEAN is not num
            // > ARRAY is not num
            // > RESOURCE is not num
            // > CLOSED RESOURCE is not num
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
     * @param string|null $result
     */
    public function numeric(&$result, $value, bool $isAllowExp = null, array $refs = []) : bool
    {
        $result = null;

        $isAllowExp = $isAllowExp ?? true;

        $withSplit = array_key_exists(0, $refs);

        $refSplit = null;

        if ($withSplit) {
            $refSplit =& $refs[ 0 ];
            $refSplit = null;
        }

        $isFloat = is_float($value);
        if ($isFloat) {
            if (! is_finite($value)) {
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
            || (is_bool($value))
            || (is_array($value))
            || (is_resource($value))
            || ('resource (closed)' === gettype($value))
            || ($this->is_nil($value))
        ) {
            // > NULL is not numeric
            // > BOOLEAN is not numeric
            // > ARRAY is not numeric
            // > RESOURCE is not numeric
            // > CLOSED RESOURCE is not numeric
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
    public function numeric_non_zero(&$result, $value, bool $allowExp = null, array $refs = []) : bool
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
    public function numeric_non_negative(&$result, $value, bool $allowExp = null, array $refs = []) : bool
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
    public function numeric_non_positive(&$result, $value, bool $allowExp = null, array $refs = []) : bool
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
    public function numeric_negative(&$result, $value, bool $allowExp = null, array $refs = []) : bool
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
    public function numeric_positive(&$result, $value, bool $allowExp = null, array $refs = []) : bool
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

        // > btw, 1.1e1 is can be converted to integer too
        // > there's we better dont support that numbers
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
     * @param Number|null $result
     */
    public function number(&$result, $value, bool $allowExp = null) : bool
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

            $number = new Number(
                $value,
                $split[ 0 ], $split[ 1 ], $split[ 2 ], $split[ 3 ],
                $scale
            );

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
    public function string(&$result, $value) : bool
    {
        return Lib::str()->type_string($result, $value);
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
    public function trim(&$result, $value, string $characters = null) : bool
    {
        return Lib::str()->type_trim($result, $value, $characters);
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
    public function ctype_alpha(&$result, $value, bool $isIgnoreCase = null) : bool
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
    public function ctype_alnum(&$result, $value, bool $isIgnoreCase = null) : bool
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
    public function list(&$result, $value) : bool
    {
        return Lib::arr()->type_list($result, $value);
    }

    /**
     * @param array|null $result
     */
    public function list_sorted(&$result, $value) : bool
    {
        return Lib::arr()->type_list_sorted($result, $value);
    }


    /**
     * @param array|null $result
     */
    public function dict(&$result, $value) : bool
    {
        return Lib::arr()->type_dict($result, $value);
    }

    /**
     * @param array|null $result
     */
    public function dict_sorted(&$result, $value) : bool
    {
        return Lib::arr()->type_dict_sorted($result, $value);
    }


    /**
     * @param array|null $result
     */
    public function index_list(&$result, $value) : bool
    {
        return Lib::arr()->type_index_list($result, $value);
    }

    /**
     * @param array|null $result
     */
    public function index_dict(&$result, $value) : bool
    {
        return Lib::arr()->type_index_dict($result, $value);
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
    public function arrpath(&$result, $path, array $pathes = null, string $dot = null) : bool
    {
        return Lib::arr()->type_arrpath($result, $path, $pathes, $dot);
    }


    /**
     * @param string|null $result
     */
    public function regex(&$result, $value) : bool
    {
        return Lib::preg()->type_regex($result, $value);
    }


    /**
     * @param string|null $result
     */
    public function address_ip(&$result, $value) : bool
    {
        return Lib::net()->type_address_ip($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function address_ip_v4(&$result, $value) : bool
    {
        return Lib::net()->type_address_ip_v4($result, $value);
    }

    /**
     * @param string|null $result
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
     * @param string|null $result
     */
    public function subnet(&$result, $value, string $ipFallback = null) : bool
    {
        return Lib::net()->type_subnet($result, $value, $ipFallback);
    }

    /**
     * @param string|null $result
     */
    public function subnet_v4(&$result, $value, string $ipFallback = null) : bool
    {
        return Lib::net()->type_subnet_v4($result, $value, $ipFallback);
    }

    /**
     * @param string|null $result
     */
    public function subnet_v6(&$result, $value, string $ipFallback = null) : bool
    {
        return Lib::net()->type_subnet_v6($result, $value, $ipFallback);
    }



    /**
     * @param string|null       $result
     * @param string            $value
     * @param string|array|null $query
     * @param string|null       $fragment
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
     * @param string      $value
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
     * @param string|null       $result
     * @param string            $value
     * @param string|array|null $query
     * @param string|null       $fragment
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
     * @param \DateTimeZone|null $result
     */
    public function timezone(&$result, $value, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_timezone($result, $value, $allowedTimezoneTypes);
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
    public function date(&$result, $datestring, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_date($result, $datestring, $timezoneFallback, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTime|null $result
     */
    public function adate(&$result, $datestring, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_adate($result, $datestring, $timezoneFallback, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function idate(&$result, $datestring, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_idate($result, $datestring, $timezoneFallback, $allowedTimezoneTypes);
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function date_tz(&$result, $datestring, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_date_tz($result, $datestring, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTime|null $result
     */
    public function adate_tz(&$result, $datestring, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_adate_tz($result, $datestring, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function idate_tz(&$result, $datestring, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_idate_tz($result, $datestring, $allowedTimezoneTypes);
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function date_of(&$result, string $format, $dateFormatted, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_date_formatted($result, $format, $dateFormatted, $timezoneFallback, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTime|null $result
     */
    public function adate_of(&$result, string $format, $dateFormatted, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_adate_formatted($result, $format, $dateFormatted, $timezoneFallback, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function idate_of(&$result, string $format, $dateFormatted, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_idate_formatted($result, $format, $dateFormatted, $timezoneFallback, $allowedTimezoneTypes);
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function date_tz_formatted(&$result, string $format, $dateFormatted, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_date_tz_formatted($result, $format, $dateFormatted, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTime|null $result
     */
    public function adate_tz_formatted(&$result, string $format, $dateFormatted, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_adate_tz_formatted($result, $format, $dateFormatted, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function idate_tz_formatted(&$result, string $format, $dateFormatted, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_idate_tz_formatted($result, $format, $dateFormatted, $allowedTimezoneTypes);
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function date_microtime(&$result, $microtime, $timezoneSet = null, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_date_microtime($result, $microtime, $timezoneSet, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTime|null $result
     */
    public function adate_microtime(&$result, $microtime, $timezoneSet = null, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_adate_microtime($result, $microtime, $timezoneSet, $allowedTimezoneTypes);
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function idate_microtime(&$result, $microtime, $timezoneSet = null, array $allowedTimezoneTypes = null) : bool
    {
        return Lib::date()->type_idate_microtime($result, $microtime, $timezoneSet, $allowedTimezoneTypes);
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
    public function interval_ago(&$result, $date, \DateTimeInterface $from = null, bool $reverse = null) : bool
    {
        return Lib::date()->type_interval_ago($result, $date, $from, $reverse);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function struct_exists(&$result, $value, int $flags = null)
    {
        return Lib::php()->type_struct_exists($result, $value, $flags);
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function struct(&$result, $value, int $flags = null) : bool
    {
        return Lib::php()->type_struct($result, $value, $flags);
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function struct_class(&$result, $value, int $flags = null) : bool
    {
        return Lib::php()->type_struct_class($result, $value, $flags);
    }

    /**
     * @param class-string|null $result
     */
    public function struct_interface(&$result, $value, int $flags = null) : bool
    {
        return Lib::php()->type_struct_interface($result, $value, $flags);
    }

    /**
     * @param class-string|null $result
     */
    public function struct_trait(&$result, $value, int $flags = null) : bool
    {
        return Lib::php()->type_struct_trait($result, $value, $flags);
    }

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function struct_enum(&$result, $value, int $flags = null) : bool
    {
        return Lib::php()->type_struct_enum($result, $value, $flags);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null    $result
     * @param class-string<T>|T|mixed $value
     */
    public function struct_fqcn(&$result, $value, int $flags = null) : bool
    {
        return Lib::php()->type_struct_fqcn($result, $value, $flags);
    }

    /**
     * @param string|null $result
     */
    public function struct_namespace(&$result, $value, int $flags = null) : bool
    {
        return Lib::php()->type_struct_namespace($result, $value, $flags);
    }

    /**
     * @param string|null $result
     */
    public function struct_basename(&$result, $value, int $flags = null) : bool
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
     *
     * @return class-string|null
     */
    public function enum_case(&$result, $value, string $enumClass = null) : bool
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
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function path(
        &$result,
        $value,
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
        &$result,
        $value, bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_realpath(
            $result,
            $value, $allowSymlink,
            $refs
        );
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function dirpath(
        &$result,
        $value, bool $allowExists = null, bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_dirpath(
            $result,
            $value, $allowExists, $allowSymlink,
            $refs
        );
    }

    /**
     * @param string|null $result
     */
    public function filepath(
        &$result,
        $value, bool $allowExists = null, bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_filepath(
            $result,
            $value, $allowExists, $allowSymlink,
            $refs
        );
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function dirpath_realpath(
        &$result,
        $value, bool $allowSymlink = null,
        array $refs = []
    ) : bool
    {
        return Lib::fs()->type_dirpath_realpath(
            $result,
            $value, $allowSymlink,
            $refs
        );
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function filepath_realpath(
        &$result,
        $value, bool $allowSymlink = null,
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
}
