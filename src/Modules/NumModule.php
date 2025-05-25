<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Bcmath\Number;
use Gzhegow\Lib\Exception\LogicException;


class NumModule
{
    /**
     * @param float|null $r
     */
    public function type_nan(&$r, $value) : bool
    {
        $r = null;

        if (is_float($value) && is_nan($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param float|null $r
     */
    public function type_float_not_nan(&$r, $value) : bool
    {
        $r = null;

        if (is_float($value) && ! is_nan($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function type_any_not_nan(&$r, $value) : bool
    {
        $r = null;

        if (! (is_float($value) && is_nan($value))) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param float|null $r
     */
    public function type_finite(&$r, $value) : bool
    {
        $r = null;

        if (is_float($value) && is_finite($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param float|null $r
     */
    public function type_float_not_finite(&$r, $value) : bool
    {
        $r = null;

        if (is_float($value) && ! is_finite($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function type_any_not_finite(&$r, $value) : bool
    {
        $r = null;

        if (! (is_float($value) && is_finite($value))) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param float|null $r
     */
    public function type_infinite(&$r, $value) : bool
    {
        $r = null;

        if (is_float($value) && is_infinite($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param float|null $r
     */
    public function type_float_not_infinite(&$r, $value) : bool
    {
        $r = null;

        if (is_float($value) && ! is_infinite($value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function type_any_not_infinite(&$r, $value) : bool
    {
        $r = null;

        if (! (is_float($value) && is_infinite($value))) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param float|null $r
     */
    public function type_float_min(&$r, $value) : bool
    {
        $r = null;

        if (false
            || is_float($value)
            || is_numeric($value)
        ) {
            [ $mant, $exp ] = explode('E', $value) + [ 1 => '' ];
            [ $int, $frac ] = explode('.', $mant) + [ 1 => '' ];

            $exp = ('' === $exp) ? '' : "E{$exp}";

            $frac = $this->frac_trimpad($frac, PHP_FLOAT_DIG - 1, PHP_FLOAT_DIG, '9');

            if ("{$int}{$frac}{$exp}" === _NUM_PHP_FLOAT_MIN_STRING_DIG) {
                $r = ($value > 0)
                    ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
                    : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG;

                return true;
            }
        }

        return false;
    }

    /**
     * @param float|null $r
     */
    public function type_float_not_float_min(&$r, $value) : bool
    {
        $r = null;

        if (! is_float($value)) {
            return false;
        }

        if (! $this->type_float_min($var, $value)) {
            $r = $value;

            return true;
        }

        return false;
    }

    /**
     * @param mixed|null $r
     */
    public function type_any_not_float_min(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_float_min($var, $value)) {
            $r = $value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $r
     */
    public function type_numeric(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        $r = null;

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

                $r = $valueString;

                unset($refSplit);

                return true;
            }
        }

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            // || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->nil($var, $value))
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

            $r = $number->getValue();

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

        if (! Lib::type()->trim($valueTrim, $value)) {
            unset($refSplit);

            return false;
        }

        if (false !== ($pos = stripos($valueTrim, 'e'))) {
            $valueTrim[ $pos ] = 'E';
        }

        $regex = ''
            . '/^'
            . '([+-]?)'
            . '((?:0|[1-9]\d*))'
            . '(\.\d+)?'
            . ($isAllowExp ? '([E][+-]?\d+)?' : '')
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

        $r = $valueNumeric;

        unset($refSplit);

        return true;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_non_zero(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric($_value, $value, $isAllowExp, $refs)) {
            return false;
        }

        if ('0' !== $_value) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_non_negative(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric($_value, $value, $isAllowExp, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            $r = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_non_positive(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric($_value, $value, $isAllowExp, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            $r = $_value;

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_negative(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric($_value, $value, $isAllowExp, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_positive(&$r, $value, ?bool $isAllowExp = null, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric($_value, $value, $isAllowExp, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            return false;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $r
     */
    public function type_numeric_int(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        $withSplit = array_key_exists(0, $refs);
        $refSplit =& $refs[ 0 ];

        // > btw, 1.1e1 is can be converted to integer 11 too
        // > we better dont support that numbers here
        if (! $this->type_numeric($_value, $value, false, $refs)) {
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

        $r = $_value;

        unset($refSplit);

        if (! $withSplit) {
            unset($refs[ 0 ]);
        }

        return true;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_int_non_zero(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' !== $_value) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_int_non_negative(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            $r = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_int_non_positive(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            $r = $_value;

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_int_negative(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_int_positive(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            return false;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_int_positive_or_minus_one(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('0' === $_value) {
            $r = $_value;

            return false;
        }

        if ('-1' === $_value) {
            $r = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_int_non_negative_or_minus_one(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_int($_value, $value, $refs)) {
            return false;
        }

        if ('-1' === $_value) {
            $r = $_value;

            return true;
        }

        if ('0' === $_value) {
            $r = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $r
     */
    public function type_numeric_float(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        $withSplit = array_key_exists(0, $refs);

        $refSplit =& $refs[ 0 ];

        // > btw, 1.1e-1 is can be converted to float 0.11 too
        // > we better dont support that numbers here
        if (! $this->type_numeric($_value, $value, false, $refs)) {
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
            $r = '0.0';

            unset($refSplit);

            if (! $withSplit) {
                unset($refs[ 0 ]);
            }

            return true;
        }

        $r = $_value;

        unset($refSplit);

        if (! $withSplit) {
            unset($refs[ 0 ]);
        }

        return true;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_float_non_zero(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_float($_value, $value, $refs)) {
            return false;
        }

        if ('0.0' !== $_value) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_float_non_negative(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_float($_value, $value, $refs)) {
            return false;
        }

        if ('0.0' === $_value) {
            $r = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_float_non_positive(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_float($_value, $value, $refs)) {
            return false;
        }

        if ('0.0' === $_value) {
            $r = $_value;

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_float_negative(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_float($_value, $value, $refs)) {
            return false;
        }

        if ('0.0' === $_value) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_float_positive(&$r, $value, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_float($_value, $value, $refs)) {
            return false;
        }

        if ('0.0' === $_value) {
            return false;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $r
     */
    public function type_numeric_trimpad(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        $r = null;

        $withSplit = array_key_exists(0, $refs);
        $refSplit =& $refs[ 0 ];

        if (! $this->type_numeric($_value, $value, true, $refs)) {
            unset($refSplit);

            return false;
        }

        [ $sign, $int, $frac, $exp ] = $refSplit;

        $fracDigitsNew = $fracDigits = ltrim($frac, '.');

        if (null !== $lenTrim) {
            $fracDigitsNew = substr($fracDigitsNew, 0, $lenTrim);
        }

        if (null !== $lenPad) {
            $fracDigitsNew = str_pad($fracDigitsNew, $lenPad, $stringPad, STR_PAD_RIGHT);
        }

        if ($fracDigitsNew !== $fracDigits) {
            $fracNew = ('' === $fracDigitsNew) ? '' : ".{$fracDigitsNew}";

            $_value = "{$sign}{$int}{$fracNew}{$exp}";

            if ($withSplit) {
                $refSplit[ 3 ] = $fracNew;
            }
        }

        $r = $_value;

        unset($refSplit);

        if (! $withSplit) {
            unset($refs[ 0 ]);
        }

        return true;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_trimpad_non_zero(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_trimpad($_value, $value, $lenTrim, $lenPad, $stringPad, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_trimpad_non_negative(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_trimpad($_value, $value, $lenTrim, $lenPad, $stringPad, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            $r = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_trimpad_non_positive(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_trimpad($_value, $value, $lenTrim, $lenPad, $stringPad, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            $r = $_value;

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_trimpad_negative(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_trimpad($_value, $value, $lenTrim, $lenPad, $stringPad, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_numeric_trimpad_positive(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_numeric_trimpad($_value, $value, $lenTrim, $lenPad, $stringPad, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            return false;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $r
     */
    public function type_decimal(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        $r = null;

        if ($scale < 0) return false;

        $withSplit = array_key_exists(0, $refs);
        $refSplit =& $refs[ 0 ];

        if (! $this->type_numeric($_value, $value, false, $refs)) {
            unset($refSplit);

            return false;
        }

        [ $sign, $int, $frac ] = $refSplit;

        $valueScale = ('' === $frac) ? 0 : (strlen($frac) - 1);

        if ($valueScale > $scale) {
            unset($refSplit);

            if (! $withSplit) {
                unset($refs[ 0 ]);
            }

            return false;
        }

        if ($valueScale < $scale) {
            if ('' === $frac) {
                $frac = '.';
            }

            $frac = str_pad($frac, $scale + 1, '0', STR_PAD_RIGHT);

            $_value = "{$sign}{$int}{$frac}";

            if ($withSplit) {
                $refSplit[ 3 ] = $frac;
            }
        }

        $r = $_value;

        unset($refSplit);

        if (! $withSplit) {
            unset($refs[ 0 ]);
        }

        return true;
    }

    /**
     * @param string|null $r
     */
    public function type_decimal_non_zero(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_decimal($_value, $value, $scale, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_decimal_non_negative(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_decimal($_value, $value, $scale, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            $r = $_value;

            return true;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_decimal_non_positive(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_decimal($_value, $value, $scale, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            $r = $_value;

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_decimal_negative(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_decimal($_value, $value, $scale, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_decimal_positive(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        $r = null;

        if (! $this->type_decimal($_value, $value, $scale, $refs)) {
            return false;
        }

        if ('0' === rtrim($_value, '0.')) {
            return false;
        }

        if ('-' !== $_value[ 0 ]) {
            $r = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param int|float|null $r
     */
    public function type_num(&$r, $value) : bool
    {
        $r = null;

        if (is_int($value)) {
            $r = $value;

            return true;
        }

        if (is_float($value)) {
            if (! is_finite($value)) {
                // > NAN, INF, -INF is float, but should not be parsed
                return false;
            }

            if (abs($value) >= _NUM_PHP_FLOAT_MAX_FLOAT_DIG) {
                $r = $value > 0
                    ? _NUM_PHP_FLOAT_MAX_FLOAT_DIG
                    : -_NUM_PHP_FLOAT_MAX_FLOAT_DIG;

                return true;
            }

            // > практическая польза нулевая, но для проверки дополнительный вызов и куча работы со строками
            // $valueFloatMin = $this->castNumericToFloatMin($valueAbs);
            // if (false !== $valueFloatMin) {
            //     $r = $value > 0
            //         ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //         : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG;
            //
            //     return true;
            // }

            $r = $value;

            return true;
        }

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            // || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->nil($var, $value))
        ) {
            // > NULL is not num
            // > EMPTY STRING is not num
            // > BOOLEAN is not num
            // > ARRAY is not num
            // > RESOURCE is not num
            // > NIL is not num

            return false;
        }

        if (! $this->type_numeric($valueNumeric, $value, true, [ &$split ])) {
            return false;
        }

        $valueNum = $this->castNumericToNum($valueNumeric, ...$split);

        if (false !== $valueNum) {
            $r = $valueNum;

            return true;
        }

        return false;
    }

    /**
     * @param int|float|null $r
     */
    public function type_num_non_zero(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_num($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param int|float|null $r
     */
    public function type_num_non_negative(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_num($_value, $value)) {
            return false;
        }

        if ($_value < 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param int|float|null $r
     */
    public function type_num_non_positive(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_num($_value, $value)) {
            return false;
        }

        if ($_value > 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param int|float|null $r
     */
    public function type_num_negative(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_num($_value, $value)) {
            return false;
        }

        if ($_value >= 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param int|float|null $r
     */
    public function type_num_positive(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_num($_value, $value)) {
            return false;
        }

        if ($_value <= 0) {
            return false;
        }

        $r = $_value;

        return true;
    }


    /**
     * @param int|null $r
     */
    public function type_int(&$r, $value) : bool
    {
        $r = null;

        if (is_int($value)) {
            $r = $value;

            return true;
        }

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && ! is_finite($value))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->nil($var, $value))
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

        if (! $this->type_numeric($valueNumeric, $value, true, [ &$split ])) {
            return false;
        }

        $valueInt = $this->castNumericToInt($valueNumeric, ...$split);

        if (false !== $valueInt) {
            $r = $valueInt;

            return true;
        }

        return false;
    }

    /**
     * @param int|null $r
     */
    public function type_int_non_zero(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_int($_value, $value)) {
            return false;
        }

        if ($_value === 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param int|null $r
     */
    public function type_int_non_negative(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_int($_value, $value)) {
            return false;
        }

        if ($_value < 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param int|null $r
     */
    public function type_int_non_positive(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_int($_value, $value)) {
            return false;
        }

        if ($_value > 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param int|null $r
     */
    public function type_int_negative(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_int($_value, $value)) {
            return false;
        }

        if ($_value >= 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param int|null $r
     */
    public function type_int_positive(&$r, $value) : bool
    {
        $r = false;

        if (! $this->type_int($_value, $value)) {
            return false;
        }

        if ($_value <= 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param string|null $r
     */
    public function type_int_positive_or_minus_one(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_int($_value, $value)) {
            return false;
        }

        if (-1 === $_value) {
            $r = $_value;

            return true;
        }

        if ($_value <= 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param string|null $r
     */
    public function type_int_non_negative_or_minus_one(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_int($_value, $value)) {
            return false;
        }

        if ($_value < -1) {
            return false;
        }

        $r = $_value;

        return true;
    }


    /**
     * @param float|null $r
     */
    public function type_float(&$r, $value) : bool
    {
        $r = null;

        if (is_int($value)) {
            $r = (float) $value;

            return true;
        }

        if (is_float($value)) {
            if (! is_finite($value)) {
                // > NAN, INF, -INF is float, but should not be parsed
                return false;
            }

            if (abs($value) >= _NUM_PHP_FLOAT_MAX_FLOAT_DIG) {
                $r = $value > 0
                    ? _NUM_PHP_FLOAT_MAX_FLOAT_DIG
                    : -_NUM_PHP_FLOAT_MAX_FLOAT_DIG;

                return true;
            }

            // > практическая польза нулевая, но для проверки дополнительный вызов и куча работы со строками
            // $valueFloatMin = $this->castNumericToFloatMin($valueAbs);
            // if (false !== $valueFloatMin) {
            //     $r = $value > 0
            //         ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //         : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG;
            //
            //     return true;
            // }

            $r = $value;

            return true;
        }

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            // || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Lib::type()->nil($var, $value))
        ) {
            // > NULL is not float
            // > EMPTY STRING is not float
            // > BOOLEAN is not float
            // > ARRAY is not float
            // > RESOURCE is not float
            // > NIL is not float

            return false;
        }

        if (! $this->type_numeric($valueNumeric, $value, true, [ &$split ])) {
            return false;
        }

        $valueFloat = $this->castNumericToFloat($valueNumeric, ...$split);

        if (false !== $valueFloat) {
            $r = $valueFloat;

            return true;
        }

        return false;
    }

    /**
     * @param float|null $r
     */
    public function type_float_non_zero(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_float($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param float|null $r
     */
    public function type_float_non_negative(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_float($_value, $value)) {
            return false;
        }

        if ($_value < 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param float|null $r
     */
    public function type_float_non_positive(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_float($_value, $value)) {
            return false;
        }

        if ($_value > 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param float|null $r
     */
    public function type_float_negative(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_float($_value, $value)) {
            return false;
        }

        if ($_value >= 0) {
            return false;
        }

        $r = $_value;

        return true;
    }

    /**
     * @param float|null $r
     */
    public function type_float_positive(&$r, $value) : bool
    {
        $r = null;

        if (! $this->type_float($_value, $value)) {
            return false;
        }

        if ($_value <= 0) {
            return false;
        }

        $r = $_value;

        return true;
    }


    public function round($num, ?int $precision = null) : float
    {
        if (! $this->type_numeric($_num, $num)) {
            throw new LogicException(
                [ 'The `num` should be a valid number', $num ]
            );
        }

        $precision = $precision ?? 0;

        $result = round($_num, $precision);

        return $result;
    }

    public function ceil($num, ?int $precision = null) : float
    {
        if (! $this->type_numeric($_num, $num)) {
            throw new LogicException(
                [ 'The `num` should be a valid number', $num ]
            );
        }

        $precision = $precision ?? 0;

        $factor = pow(10, $precision);

        $result = ceil($_num * $factor) / $factor;

        return $result;
    }

    public function floor($num, ?int $precision = null) : float
    {
        if (! $this->type_numeric($_num, $num)) {
            throw new LogicException(
                [ 'The `num` should be a valid number', $num ]
            );
        }

        $precision = $precision ?? 0;

        $factor = pow(10, $precision);

        $result = ceil($_num * $factor) / $factor;

        return $result;
    }


    public function moneyceil($num, ?int $precision = null) : float
    {
        if (! $this->type_numeric($_num, $num)) {
            throw new LogicException(
                [ 'The `num` should be a valid number', $num ]
            );
        }

        $precision = $precision ?? 0;

        $factor = pow(10, $precision);

        $result = ($_num >= 0)
            ? (ceil($_num * $factor) / $factor)
            : (floor($_num * $factor) / $factor);

        return $result;
    }

    public function moneyfloor($num, ?int $precision = null) : float
    {
        if (! $this->type_numeric($_num, $num)) {
            throw new LogicException(
                [ 'The `num` should be a valid number', $num ]
            );
        }

        $precision = $precision ?? 0;

        $factor = pow(10, $precision);

        $result = ($_num >= 0)
            ? (floor($_num * $factor) / $factor)
            : (ceil($_num * $factor) / $factor);

        return $result;
    }


    public function frac_trimpad(string $frac, int $lenTrim, ?int $lenPad = null, string $stringPad = '') : string
    {
        $fracDigits = ltrim($frac, '.');

        $fracDigits = substr($fracDigits, 0, $lenTrim);

        if (null !== $lenPad) {
            $fracDigits = str_pad($fracDigits, $lenPad, $stringPad);
        }

        return ".{$fracDigits}";
    }


    /**
     * @return int|float|false
     */
    protected function castNumericToNum(string $numeric, string $sign, string $int, string $frac, string $exp)
    {
        if ('0' === $numeric) {
            return 0;
        }

        $hasExponent = ('' !== $exp);

        $valueFloat = (float) $numeric;

        if (! is_finite($valueFloat)) {
            if (! $hasExponent) {
                return false;
            }

            $fracDig = '';
            if ('' !== $frac) {
                $fracDig = substr($frac, 1, PHP_FLOAT_DIG - 1);
                $fracDig = str_pad($fracDig, PHP_FLOAT_DIG, '0', STR_PAD_RIGHT);
                $fracDig = '.' . $fracDig;
            }

            $numericDig = "{$sign}{$int}{$fracDig}{$exp}";

            $valueFloat = (float) sprintf('%.' . PHP_FLOAT_DIG . 'e', $numericDig);

            if (! is_finite($valueFloat)) {
                return false;
            }
        }

        if (0.0 === $valueFloat) {
            return false;
        }

        $valueFloatAbs = abs($valueFloat);

        if ($valueFloatAbs > 0.0) {
            if ($valueFloatAbs >= _NUM_PHP_FLOAT_MAX_FLOAT_DIG) {
                return $valueFloat > 0
                    ? _NUM_PHP_FLOAT_MAX_FLOAT_DIG
                    : -_NUM_PHP_FLOAT_MAX_FLOAT_DIG;
            }

            // > практическая польза нулевая, но для проверки дополнительный вызов и куча работы со строками
            // $valueFloatMin = $this->castNumericToFloatMin($valueFloatAbs, [ $sign, $int, $frac, $exp ]);
            // if (false !== $valueFloatMin) {
            //     return $valueFloat > 0
            //         ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //         : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG;
            // }
        }

        if (! ((_NUM_PHP_INT_MIN_FLOAT <= $valueFloat) && ($valueFloat <= _NUM_PHP_INT_MAX_FLOAT))) {
            return $valueFloat;
        }

        $valueInt = (int) $numeric;

        if ($valueFloat === ((float) $valueInt)) {
            return $valueInt;
        }

        return $valueFloat;
    }

    /**
     * @return int|float|false
     */
    protected function castNumericToInt(string $numeric, string $sign, string $int, string $frac, string $exp)
    {
        if ('0' === $numeric) {
            return 0;
        }

        $hasFrac = ('' !== $frac);
        if ($hasFrac) {
            return false;
        }

        $hasExponent = ('' !== $exp);
        if ($hasExponent) {
            return false;
        }

        $valueFloat = (float) $numeric;

        if (! ((_NUM_PHP_INT_MIN_FLOAT <= $valueFloat) && ($valueFloat <= _NUM_PHP_INT_MAX_FLOAT))) {
            return false;
        }

        $valueInt = (int) $numeric;

        return $valueInt;
    }

    /**
     * @return float|false
     */
    protected function castNumericToFloat(string $numeric, string $sign, string $int, string $frac, string $exp)
    {
        if ('0' === $numeric) {
            return 0.0;
        }

        $hasExponent = ('' !== $exp);

        $valueFloat = (float) $numeric;

        if (! is_finite($valueFloat)) {
            if (! $hasExponent) {
                return false;
            }

            $fracDig = '';
            if ('' !== $frac) {
                $fracDig = substr($frac, 1, PHP_FLOAT_DIG - 1);
                $fracDig = str_pad($fracDig, PHP_FLOAT_DIG, '0', STR_PAD_RIGHT);
                $fracDig = '.' . $fracDig;
            }

            $numericDig = "{$sign}{$int}{$fracDig}{$exp}";

            $valueFloat = (float) sprintf('%.' . PHP_FLOAT_DIG . 'e', $numericDig);

            if (! is_finite($valueFloat)) {
                return false;
            }
        }

        if (0.0 === $valueFloat) {
            return false;
        }

        $valueFloatAbs = abs($valueFloat);

        if ($valueFloatAbs > 0.0) {
            if ($valueFloatAbs >= _NUM_PHP_FLOAT_MAX_FLOAT_DIG) {
                return $valueFloat > 0
                    ? _NUM_PHP_FLOAT_MAX_FLOAT_DIG
                    : -_NUM_PHP_FLOAT_MAX_FLOAT_DIG;
            }

            // > практическая польза нулевая, но для проверки дополнительный вызов и куча работы со строками
            // $valueFloatMin = $this->castNumericToFloatMin($valueFloatAbs, [ $sign, $int, $frac, $exp ]);
            // if (false !== $valueFloatMin) {
            //     return $valueFloat > 0
            //         ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //         : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG;
            // }
        }

        return $valueFloat;
    }

    /**
     * @return float|false
     */
    protected function castNumericToFloatMin($value, $split = null)
    {
        if (null === $split) {
            if (! $this->type_numeric($numeric, $value, true, [ &$split ])) {
                return false;
            }
        }

        [ $sign, $int, $frac, $exp ] = $split;

        $fracDig = $this->frac_trimpad($frac, PHP_FLOAT_DIG - 1, PHP_FLOAT_DIG, '9');

        $numericAbsDig = "{$int}{$fracDig}{$exp}";

        if ($numericAbsDig === _NUM_PHP_FLOAT_MIN_STRING_DIG) {
            return ('' === $sign)
                ? _NUM_PHP_FLOAT_MIN_STRING_DIG
                : -_NUM_PHP_FLOAT_MIN_STRING_DIG;
        }

        return false;
    }
}
