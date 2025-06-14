<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Bcmath\Number;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


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

            $frac = substr($frac, 0, PHP_FLOAT_DIG - 1);
            $frac = str_pad($frac, PHP_FLOAT_DIG, '9', STR_PAD_RIGHT);

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
     * @param Number|null $r
     */
    public function type_number(&$r, $value, ?bool $allowExp = null) : bool
    {
        $r = null;

        if ($value instanceof Number) {
            $r = $value;

            return true;
        }

        $status = $this->type_numeric($refValueNumeric, $value, $allowExp, [ &$split ]);
        if (! $status) {
            return false;
        }

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

        $r = $number;

        return true;
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

        if ($value instanceof Number) {
            $number = $value;

            $exp = $number->getExp();

            if (! $isAllowExp) {
                if ('' !== $exp) {
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

            return true;
        }

        $theType = Lib::type();

        $isInt = is_int($value);
        $isFloat = is_float($value);

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            //
            // || (is_float($value) && (! is_finite($value)))
            //
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || ($theType->nil($var, $value))
        ) {
            return false;
        }

        $valueTrim = null;

        if ($isInt || $isFloat) {
            if (0 == $value) {
                $valueTrim = '0';

                if (! $withSplit) {
                    $r = $valueTrim;

                    return true;
                }
            }
        }

        if (null === $valueTrim) {
            if (! $theType->trim($valueTrim, $value)) {
                return false;
            }
        }

        if ($hasExp = (false !== ($expPos = stripos($valueTrim, 'e')))) {
            if (! $isAllowExp) {
                return false;
            }

            $valueTrim[ $expPos ] = 'E';
        }

        if ($isInt && $isFloat) {
            if (! $withSplit) {
                if (! $hasExp) {
                    $valueTrim = rtrim($valueTrim, '0.');

                } else {
                    [ $left, $right ] = explode('E', $valueTrim);

                    $left = rtrim($left, '0.');

                    $valueTrim = "{$left}{$right}";
                }

                $r = $valueTrim;

                return true;
            }
        }

        $regex = ''
            . '/^'
            . '([+-]?)'
            . '((?:0|[1-9]\d*))'
            . '(\.\d+)?'
            . ($isAllowExp ? '([E][+-]?\d+)?' : '')
            . '$/';

        if (! preg_match($regex, $valueTrim, $matches)) {
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

        $refSplit =& $refs[ 0 ];

        // > btw, 1.1e1 is can be converted to integer 11 too
        // > we better dont support that numbers here
        if (! $this->type_numeric($refValueNumeric, $value, false, $refs)) {
            return false;
        }

        [ , , $frac ] = $refSplit;

        if ('' !== $frac) {
            return false;
        }

        $r = $refValueNumeric;

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

            return true;
        }

        $r = $_value;

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
     * 1.5 => '1.50'
     *
     * @param string|null $r
     */
    public function type_numeric_trimpad(&$r, $value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = []) : bool
    {
        $r = null;

        $withSplit = array_key_exists(0, $refs);
        $refSplit =& $refs[ 0 ];

        if (! $this->type_numeric($_value, $value, true, $refs)) {
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
     * 1.5 -> (2) -> '1.50'
     * 1.500 -> (2) -> '1.50'
     * 1.501 -> (2) -> false
     *
     * @param string|null $r
     */
    public function type_decimal(&$r, $value, int $scale = 0, array $refs = []) : bool
    {
        $r = null;

        if ($scale < 0) {
            return false;
        }

        $withSplit = array_key_exists(0, $refs);
        $refSplit =& $refs[ 0 ];

        if (! $this->type_numeric($_value, $value, false, $refs)) {
            return false;
        }

        [ $sign, $int, $frac ] = $refSplit;

        $valueScale = ('' === $frac) ? 0 : (strlen($frac) - 1);

        if ($valueScale > $scale) {
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

            if (0 == $value) {
                // > -0.0 to 0.0
                $r = 0.0;

                return true;
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
            //
            // || (is_float($value) && (! is_finite($value)))
            //
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

            if (-0.0 === $value) {
                $r = 0.0;

                return true;
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


    /**
     * > Математическое округление
     *
     * > Точка принятия решения - "дробная часть равна .5/.05/.005 и тд"
     * > К середине применяется режим округления, выше середины - правило всегда "от нуля", ниже середины - правило "к нулю"
     *
     * > 1.5 -> 2
     * > 1.05 -> 1
     * > -1.05 -> -1
     * > -1.5 -> -2
     */
    public function mathround(
        $number, ?int $precision = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : float
    {
        $precision = $precision ?? 0;

        if ($precision < 0) {
            throw new LogicException(
                [ 'The `precision` should be a non-negative integer', $precision ]
            );
        }

        if (! $this->type_number($refNumber, $number, false)) {
            throw new LogicException(
                [ 'The `num` should be a valid numeric without exponent', $number ]
            );
        }

        if ($refNumber->isZero()) {
            return 0.0;
        }

        $isNegative = $refNumber->isNegative();

        $hasFlagsNonNegative = (null !== $flags);
        $hasFlagsNegative = (null !== $flagsNegative);

        $flagsCurrent = 0;
        if ($isNegative) {
            if ($hasFlagsNegative) {
                $flagsCurrent = $flagsNegative;

            } elseif ($hasFlagsNonNegative) {
                $flagsCurrent = $flags;
            }

        } else {
            if ($hasFlagsNonNegative) {
                $flagsCurrent = $flags;

            } elseif ($hasFlagsNegative) {
                throw new LogicException(
                    [ 'Unable to set `flagsNegative` without `flags`', $flagsNegative, $flags ]
                );
            }
        }

        $flagGroups = [
            '_NUM_ROUND' => [
                [
                    _NUM_ROUND_AWAY_FROM_ZERO,
                    _NUM_ROUND_TOWARD_ZERO,
                    _NUM_ROUND_TO_POSITIVE_INF,
                    _NUM_ROUND_TO_NEGATIVE_INF,
                    _NUM_ROUND_EVEN,
                    _NUM_ROUND_ODD,
                ],
                _NUM_ROUND_AWAY_FROM_ZERO,
            ],
        ];

        foreach ( $flagGroups as $groupName => [ $conflict, $default ] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ($flagsCurrent & $flag) {
                    $cnt++;
                }
            }

            if ($cnt > 1) {
                throw new LogicException(
                    [ 'The `flagsNonNegative` conflict in group: ' . $groupName, $flags ]
                );

            } elseif (0 === $cnt) {
                $flagsCurrent |= $default;
            }
        }

        $isRoundAwayFromZero = false;
        $isRoundTowardZero = false;
        $isRoundToPositiveInf = false;
        $isRoundToNegativeInf = false;
        $isRoundEven = false;
        $isRoundOdd = false;
        if ($flagsCurrent & _NUM_ROUND_AWAY_FROM_ZERO) {
            $isRoundAwayFromZero = true;

        } elseif ($flagsCurrent & _NUM_ROUND_TOWARD_ZERO) {
            $isRoundTowardZero = true;

        } elseif ($flagsCurrent & _NUM_ROUND_TO_POSITIVE_INF) {
            $isRoundToPositiveInf = true;

        } elseif ($flagsCurrent & _NUM_ROUND_TO_NEGATIVE_INF) {
            $isRoundToNegativeInf = true;

        } elseif ($flagsCurrent & _NUM_ROUND_EVEN) {
            $isRoundEven = true;

        } elseif ($flagsCurrent & _NUM_ROUND_ODD) {
            $isRoundOdd = true;
        }

        $factor = ($precision > 0)
            ? ((int) pow(10, $precision))
            : 1;

        $scaledAbs = $refNumber->getValueAbsolute() * $factor;

        $this->type_number($scaledAbsNumber, $scaledAbs, false);

        $scaledAbsInt = intval($scaledAbsNumber->getValueAbsoluteInt());
        $scaledAbsFrac = $scaledAbsNumber->getFrac();

        $isMidpoint = isset($scaledAbsFrac[ 1 ]) && ('5' === $scaledAbsFrac[ 1 ]);

        if (! $isMidpoint) {
            $scaledAbsFracLen = strlen($scaledAbsFrac);

            $isUp = false;
            for ( $i = 1; $i < $scaledAbsFracLen; $i++ ) {
                $digit = $scaledAbsFrac[ $i ];

                if ('4' === $digit) {
                    continue;

                } elseif ($digit >= 5) {
                    $isUp = true;

                    break;

                } else {
                    $isUp = false;

                    break;
                }
            }

            if ($isUp) {
                $scaledAbs = $scaledAbsInt + 1;

            } else {
                $scaledAbs = $scaledAbsInt;
            }

        } else {
            if ($isRoundAwayFromZero) {
                $scaledAbs = $scaledAbsInt + 1;

            } elseif ($isRoundTowardZero) {
                $scaledAbs = $scaledAbsInt;

            } elseif ($isRoundToPositiveInf) {
                if ($isNegative) {
                    $scaledAbs = $scaledAbsInt;

                } else {
                    $scaledAbs = $scaledAbsInt + 1;
                }

            } elseif ($isRoundToNegativeInf) {
                if ($isNegative) {
                    $scaledAbs = $scaledAbsInt + 1;

                } else {
                    $scaledAbs = $scaledAbsInt;
                }

            } elseif ($isRoundEven) {
                $a = $scaledAbsInt;
                $b = (0 === ($a % 2)) ? $a : ($a - 1);
                $c = $b + 2;

                $scaledAbs = (abs($scaledAbs - $b) <= abs($c - $scaledAbs))
                    ? $b
                    : $c;

            } elseif ($isRoundOdd) {
                $a = $scaledAbsInt;
                $b = ($a % 2) ? $a : ($a - 1);
                $c = $b + 2;

                $scaledAbs = (abs($scaledAbs - $b) <= abs($c - $scaledAbs))
                    ? $b
                    : $c;

            } else {
                throw new RuntimeException(
                    [ 'The `round` mode is unknown', $flags ]
                );
            }
        }

        $result = $isNegative ? -$scaledAbs : $scaledAbs;

        $result = $result / $factor;

        return $result;
    }

    public function mathround_even($number, ?int $precision = null) : float
    {
        return $this->mathround(
            $number, $precision,
            _NUM_ROUND_EVEN, _NUM_ROUND_EVEN
        );
    }

    public function mathround_odd($number, ?int $precision = null) : float
    {
        return $this->mathround(
            $number, $precision,
            _NUM_ROUND_ODD, _NUM_ROUND_ODD
        );
    }


    /**
     * > Денежное округление
     *
     * > Точка принятия решения - "наличие дробной части", если есть - округляем, если нет - обрезаем
     * > Режим округления применяется к числу, у которого "есть дробная часть, даже минимальная"
     *
     * > 1.5 -> 2
     * > 1.05 -> 2
     * > -1.05 -> -2
     * > -1.5 -> -2
     */
    public function moneyround(
        $number, ?int $precision = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : float
    {
        $precision = $precision ?? 0;

        if ($precision < 0) {
            throw new LogicException(
                [ 'The `precision` should be a non-negative integer', $precision ]
            );
        }

        if (! $this->type_number($refNumber, $number, false)) {
            throw new LogicException(
                [ 'The `num` should be a valid numeric without exponent', $number ]
            );
        }

        if ($refNumber->isZero()) {
            return 0.0;
        }

        $isNegative = $refNumber->isNegative();

        $hasFlagsNonNegative = (null !== $flags);
        $hasFlagsNegative = (null !== $flagsNegative);

        $flagsCurrent = 0;
        if ($isNegative) {
            if ($hasFlagsNegative) {
                $flagsCurrent = $flagsNegative;

            } elseif ($hasFlagsNonNegative) {
                $flagsCurrent = $flags;
            }

        } else {
            if ($hasFlagsNonNegative) {
                $flagsCurrent = $flags;

            } elseif ($hasFlagsNegative) {
                throw new LogicException(
                    [ 'Unable to set `flagsNegative` without `flags`', $flagsNegative, $flags ]
                );
            }
        }

        $flagGroups = [
            '_NUM_ROUND' => [
                [
                    _NUM_ROUND_AWAY_FROM_ZERO,
                    _NUM_ROUND_TOWARD_ZERO,
                    _NUM_ROUND_TO_POSITIVE_INF,
                    _NUM_ROUND_TO_NEGATIVE_INF,
                    _NUM_ROUND_EVEN,
                    _NUM_ROUND_ODD,
                ],
                _NUM_ROUND_AWAY_FROM_ZERO,
            ],
        ];

        foreach ( $flagGroups as $groupName => [ $conflict, $default ] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ($flagsCurrent & $flag) {
                    $cnt++;
                }
            }

            if ($cnt > 1) {
                throw new LogicException(
                    [ 'The `flags` conflict in group: ' . $groupName, $flags ]
                );

            } elseif (0 === $cnt) {
                $flagsCurrent |= $default;
            }
        }

        $isRoundAwayFromZero = false;
        $isRoundTowardZero = false;
        $isRoundToPositiveInf = false;
        $isRoundToNegativeInf = false;
        $isRoundEven = false;
        $isRoundOdd = false;
        if ($flagsCurrent & _NUM_ROUND_AWAY_FROM_ZERO) {
            $isRoundAwayFromZero = true;

        } elseif ($flagsCurrent & _NUM_ROUND_TOWARD_ZERO) {
            $isRoundTowardZero = true;

        } elseif ($flagsCurrent & _NUM_ROUND_TO_POSITIVE_INF) {
            $isRoundToPositiveInf = true;

        } elseif ($flagsCurrent & _NUM_ROUND_TO_NEGATIVE_INF) {
            $isRoundToNegativeInf = true;

        } elseif ($flagsCurrent & _NUM_ROUND_EVEN) {
            $isRoundEven = true;

        } elseif ($flagsCurrent & _NUM_ROUND_ODD) {
            $isRoundOdd = true;
        }

        $factor = ($precision > 0)
            ? ((int) pow(10, $precision))
            : 1;

        $scaledAbs = $refNumber->getValueAbsolute() * $factor;

        $this->type_number($scaledAbsNumber, $scaledAbs, false);

        $scaledAbsInt = intval($scaledAbsNumber->getValueAbsoluteInt());
        $scaledAbsFrac = $scaledAbsNumber->getFrac();

        if ('' === $scaledAbsFrac) {
            $scaledAbs = $scaledAbsInt;

        } else {
            if ($isRoundAwayFromZero) {
                $scaledAbs = $scaledAbsInt + 1;

            } elseif ($isRoundTowardZero) {
                $scaledAbs = $scaledAbsInt;

            } elseif ($isRoundToPositiveInf) {
                if ($isNegative) {
                    $scaledAbs = $scaledAbsInt;

                } else {
                    $scaledAbs = $scaledAbsInt + 1;
                }

            } elseif ($isRoundToNegativeInf) {
                if ($isNegative) {
                    $scaledAbs = $scaledAbsInt + 1;

                } else {
                    $scaledAbs = $scaledAbsInt;
                }

            } elseif ($isRoundEven) {
                $a = $scaledAbsInt;
                $b = ($a % 2 === 0) ? $a : ($a - 1);
                $c = $b + 2;

                $scaledAbs = (abs($scaledAbs - $b) <= abs($c - $scaledAbs))
                    ? $b
                    : $c;

            } elseif ($isRoundOdd) {
                $a = $scaledAbsInt;
                $b = ($a % 2) ? $a : ($a - 1);
                $c = $b + 2;

                $scaledAbs = (abs($scaledAbs - $b) <= abs($c - $scaledAbs))
                    ? $b
                    : $c;

            } else {
                throw new RuntimeException(
                    [ 'The `round` mode is unknown', $flags ]
                );
            }
        }

        $result = $isNegative ? -$scaledAbs : $scaledAbs;

        $result = $result / $factor;

        return $result;
    }

    /**
     * > 1.5 -> 1
     * > 1.05 -> 1
     * > -1.05 -> -1
     * > -1.5 -> -1
     */
    public function moneytrunc($number, ?int $precision = null) : float
    {
        return $this->moneyround(
            $number, $precision,
            _NUM_ROUND_TOWARD_ZERO, _NUM_ROUND_TOWARD_ZERO
        );
    }

    /**
     * > 1.5 -> 2
     * > 1.05 -> 2
     * > -1.05 -> -1
     * > -1.5 -> -1
     */
    public function moneyceil($number, ?int $precision = null) : float
    {
        return $this->moneyround(
            $number, $precision,
            _NUM_ROUND_TO_POSITIVE_INF, _NUM_ROUND_TO_POSITIVE_INF
        );
    }

    /**
     * > 1.5 -> 1
     * > 1.05 -> 1
     * > -1.05 -> -2
     * > -1.5 -> -2
     */
    public function moneyfloor($number, ?int $precision = null) : float
    {
        return $this->moneyround(
            $number, $precision,
            _NUM_ROUND_TO_NEGATIVE_INF, _NUM_ROUND_TO_NEGATIVE_INF
        );
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

    // /**
    //  * > практическая польза нулевая, но для проверки дополнительный вызов и куча работы со строками
    //  *
    //  * @return float|false
    //  */
    // protected function castNumericToFloatMin($value, $split = null)
    // {
    //     if (null === $split) {
    //         if (! $this->type_numeric($numeric, $value, true, [ &$split ])) {
    //             return false;
    //         }
    //     }
    //
    //     [ $sign, $int, $frac, $exp ] = $split;
    //
    //     $fracDig = $frac;
    //     $fracDig = substr($fracDig, 0, PHP_FLOAT_DIG - 1);
    //     $fracDig = str_pad($fracDig, PHP_FLOAT_DIG, '9', STR_PAD_RIGHT);
    //
    //     $numericAbsDig = "{$int}{$fracDig}{$exp}";
    //
    //     if ($numericAbsDig === _NUM_PHP_FLOAT_MIN_STRING_DIG) {
    //         return ('' === $sign)
    //             ? _NUM_PHP_FLOAT_MIN_STRING_DIG
    //             : -_NUM_PHP_FLOAT_MIN_STRING_DIG;
    //     }
    //
    //     return false;
    // }
}
