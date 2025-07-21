<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Nil;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Bcmath\Number;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class NumModule
{
    /**
     * @return Ret<float>
     */
    public function type_nan($value)
    {
        if (is_float($value) && is_nan($value)) {
            return Ret::ok($value);
        }

        return Ret::err(
            [ 'The `value` should be nan', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>
     */
    public function type_float_not_nan($value)
    {
        if (is_float($value) && ! is_nan($value)) {
            return Ret::ok($value);
        }

        return Ret::err(
            [ 'The `value` should be float not nan', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_nan($value)
    {
        if (! (is_float($value) && is_nan($value))) {
            return Ret::ok($value);
        }

        return Ret::err(
            [ 'The `value` should be not nan', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<float>
     */
    public function type_finite($value)
    {
        if (is_float($value) && is_finite($value)) {
            return Ret::ok($value);
        }

        return Ret::err(
            [ 'The `value` should be finite', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>
     */
    public function type_float_not_finite($value)
    {
        if (is_float($value) && ! is_finite($value)) {
            return Ret::ok($value);
        }

        return Ret::err(
            [ 'The `value` should be float not finite', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_finite($value)
    {
        if (! (is_float($value) && is_finite($value))) {
            return Ret::ok($value);
        }

        return Ret::err(
            [ 'The `value` should be not finite', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<float>
     */
    public function type_infinite($value)
    {
        if (is_float($value) && is_infinite($value)) {
            return Ret::ok($value);
        }

        return Ret::err(
            [ 'The `value` should be infinite', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>
     */
    public function type_float_not_infinite($value)
    {
        if (is_float($value) && ! is_infinite($value)) {
            return Ret::ok($value);
        }

        return Ret::err(
            [ 'The `value` should be bool, null is not', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_infinite($value)
    {
        if (! (is_float($value) && is_infinite($value))) {
            return Ret::ok($value);
        }

        return Ret::err(
            [ 'The `value` should be not infinite', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<float>
     */
    public function type_float_min($value)
    {
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
                return Ret::ok(
                    ($value > 0)
                        ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
                        : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG
                );
            }
        }

        return Ret::err(
            [ 'The `value` should be float min', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>
     */
    public function type_float_not_float_min($value)
    {
        if (! is_float($value)) {
            return Ret::err(
                [ 'The `value` should be float', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ($this->type_float_min($value)) {
            return Ret::err(
                [ 'The `value` should be float but not float min', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function type_any_not_float_min($value)
    {
        if (! $this->type_float_min($value)) {
            return Ret::ok($value);
        }

        return Ret::err(
            [ 'The `value` should be not float min', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<Number>
     */
    public function type_number($value, ?bool $allowExp = null)
    {
        if ($value instanceof Number) {
            return Ret::ok($value);
        }

        if (! $this
            ->type_numeric($value, $allowExp, [ &$split ])
            ->isOk([ 1 => &$ret ])
        ) {
            return $ret;
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
        ])->orThrow();

        return Ret::ok($number);
    }


    /**
     * @return Ret<string>
     */
    public function type_numeric($value, ?bool $isAllowExp = null, array $refs = [])
    {
        $isAllowExp = $isAllowExp ?? true;

        $theType = Lib::type();

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
                    return Ret::err(
                        [ 'The `value` should be numeric, without exponent', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }

            if ($withSplit) {
                $refSplit = [];
                $refSplit[ 0 ] = $number->getSign();
                $refSplit[ 1 ] = $number->getInt();
                $refSplit[ 2 ] = $number->getFrac();
                $refSplit[ 3 ] = $exp;
            }

            return Ret::ok($number->getValue());
        }

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
            || (Nil::is($value))
        ) {
            return Ret::err(
                [ 'The `value` should be numeric', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $valueTrim = null;

        if ($isInt || $isFloat) {
            if (0 == $value) {
                $valueTrim = '0';

                if (! $withSplit) {
                    return Ret::ok($valueTrim);
                }
            }
        }

        if (null === $valueTrim) {
            if (! $theType
                ->trim($value)
                ->isOk([ &$valueTrim, &$ret ])
            ) {
                return Ret::err(
                    [ 'The `value` should be numeric, empty trim is not', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        if ($hasExp = (false !== ($expPos = stripos($valueTrim, 'e')))) {
            if (! $isAllowExp) {
                return Ret::err(
                    [ 'The `value` should be numeric, without exponent', $value ],
                    [ __FILE__, __LINE__ ]
                );
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

                return Ret::ok($valueTrim);
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
            return Ret::err(
                [ 'The `value` should be numeric, regex is not match', $value ],
                [ __FILE__, __LINE__ ]
            );
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

        return Ret::ok($valueNumeric);
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_non_zero($value, ?bool $isAllowExp = null, array $refs = [])
    {
        if (! $this
            ->type_numeric($value, $isAllowExp, $refs)
            ->isOk([ &$valueString, &$ret ])
        ) {
            return $ret;
        }

        if ('0' !== $valueString) {
            return Ret::ok($valueString);
        }

        return Ret::err(
            [ 'The `value` should be numeric, non zero', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_non_negative($value, ?bool $isAllowExp = null, array $refs = [])
    {
        if (! $this
            ->type_numeric($value, $isAllowExp, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === $valueNumeric) {
            return Ret::ok($valueNumeric);
        }

        if ('-' !== $valueNumeric[ 0 ]) {
            return Ret::ok($valueNumeric);
        }

        return Ret::err(
            [ 'The `value` should be numeric, non negative', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_non_positive($value, ?bool $isAllowExp = null, array $refs = [])
    {
        if (! $this
            ->type_numeric($value, $isAllowExp, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === $valueNumeric) {
            return Ret::ok($valueNumeric);
        }

        if ('-' === $valueNumeric[ 0 ]) {
            return Ret::ok($valueNumeric);
        }

        return Ret::err(
            [ 'The `value` should be numeric, non positive', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_negative($value, ?bool $isAllowExp = null, array $refs = [])
    {
        if (! $this
            ->type_numeric($value, $isAllowExp, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === $valueNumeric) {
            return Ret::err(
                [ 'The `value` should be numeric, negative, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-' === $valueNumeric[ 0 ]) {
            return Ret::ok($valueNumeric);
        }

        return Ret::err(
            [ 'The `value` should be numeric, negative', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_positive($value, ?bool $isAllowExp = null, array $refs = [])
    {
        if (! $this
            ->type_numeric($value, $isAllowExp, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === $valueNumeric) {
            return Ret::err(
                [ 'The `value` should be numeric, positive, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-' !== $valueNumeric[ 0 ]) {
            return Ret::ok($valueNumeric);
        }

        return Ret::err(
            [ 'The `value` should be numeric, positive', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<string>
     */
    public function type_numeric_int($value, array $refs = [])
    {
        $refSplit =& $refs[ 0 ];

        // > btw, 1.1e1 is can be converted to integer 11 too
        // > we better don't support that numbers here
        if (! $this
            ->type_numeric($value, false, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        [ , , $frac ] = $refSplit;

        if ('' !== $frac) {
            return Ret::err(
                [ 'The `value` should be numeric int, without fractional part', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueNumeric);
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_int_non_zero($value, array $refs = [])
    {
        if (! $this
            ->type_numeric_int($value, $refs)
            ->isOk([ &$valueNumericInt, &$ret ])
        ) {
            return $ret;
        }

        if ('0' !== $valueNumericInt) {
            return Ret::ok($valueNumericInt);
        }

        return Ret::err(
            [ 'The `value` should be numeric int, non-zero', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_int_non_negative($value, array $refs = [])
    {
        if (! $this
            ->type_numeric_int($value, $refs)
            ->isOk([ &$valueNumericInt, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === $valueNumericInt) {
            return Ret::ok($valueNumericInt);
        }

        if ('-' !== $valueNumericInt[ 0 ]) {
            return Ret::ok($valueNumericInt);
        }

        return Ret::err(
            [ 'The `value` should be numeric int, non-negative', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_int_non_positive($value, array $refs = [])
    {
        if (! $this
            ->type_numeric_int($value, $refs)
            ->isOk([ &$valueNumericInt, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === $valueNumericInt) {
            return Ret::ok($valueNumericInt);
        }

        if ('-' === $valueNumericInt[ 0 ]) {
            return Ret::ok($valueNumericInt);
        }

        return Ret::err(
            [ 'The `value` should be numeric int, non-positive', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_int_negative($value, array $refs = [])
    {
        if (! $this
            ->type_numeric_int($value, $refs)
            ->isOk([ &$valueNumericInt, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === $valueNumericInt) {
            return Ret::err(
                [ 'The `value` should be numeric int, negative, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-' === $valueNumericInt[ 0 ]) {
            return Ret::ok($valueNumericInt);
        }

        return Ret::err(
            [ 'The `value` should be numeric int, negative', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_int_positive($value, array $refs = [])
    {
        if (! $this
            ->type_numeric_int($value, $refs)
            ->isOk([ &$valueNumericInt, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === $valueNumericInt) {
            return Ret::err(
                [ 'The `value` should be numeric int, positive, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-' !== $valueNumericInt[ 0 ]) {
            return Ret::ok($valueNumericInt);
        }

        return Ret::err(
            [ 'The `value` should be numeric int, positive', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_int_positive_or_minus_one($value, array $refs = [])
    {
        if (! $this
            ->type_numeric_int($value, $refs)
            ->isOk([ &$valueNumericInt, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === $valueNumericInt) {
            return Ret::err(
                [ 'The `value` should be numeric int, positive or minus one, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-1' === $valueNumericInt) {
            return Ret::ok($valueNumericInt);
        }

        if ('-' !== $valueNumericInt[ 0 ]) {
            return Ret::ok($valueNumericInt);
        }

        return Ret::err(
            [ 'The `value` should be numeric int, positive or minus one', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_int_non_negative_or_minus_one($value, array $refs = [])
    {
        if (! $this
            ->type_numeric_int($value, $refs)
            ->isOk([ &$valueNumericInt, &$ret ])
        ) {
            return $ret;
        }

        if ('-1' === $valueNumericInt) {
            return Ret::ok($valueNumericInt);
        }

        if ('0' === $valueNumericInt) {
            return Ret::ok($valueNumericInt);
        }

        if ('-' !== $valueNumericInt[ 0 ]) {
            return Ret::ok($valueNumericInt);
        }

        return Ret::err(
            [ 'The `value` should be numeric int, non negative or minus one', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<string>
     */
    public function type_numeric_float($value, array $refs = [])
    {
        $withSplit = array_key_exists(0, $refs);
        $refSplit =& $refs[ 0 ];

        // > btw, 1.1e-1 is can be converted to float 0.11 too
        // > we better don't support that numbers here
        if (! $this
            ->type_numeric($value, false, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        [ $sign, $int, $frac ] = $refSplit;

        if ('' === $frac) {
            $frac = '.0';

            $valueNumeric = "{$sign}{$int}{$frac}";

            if ($withSplit) {
                $refSplit[ 3 ] = $frac;
            }
        }

        if ('0' === $valueNumeric) {
            return Ret::ok('0.0');
        }

        return Ret::ok($valueNumeric);
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_float_non_zero($value, array $refs = [])
    {
        if (! $this
            ->type_numeric($value, false, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        if ('0.0' !== $valueNumeric) {
            return Ret::ok($valueNumeric);
        }

        return Ret::err(
            [ 'The `value` should be numeric float, non zero', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_float_non_negative($value, array $refs = [])
    {
        if (! $this
            ->type_numeric($value, false, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        if ('0.0' === $valueNumeric) {
            return Ret::ok($valueNumeric);
        }

        if ('-' !== $valueNumeric[ 0 ]) {
            return Ret::ok($valueNumeric);
        }

        return Ret::err(
            [ 'The `value` should be numeric float, non negative', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_float_non_positive($value, array $refs = [])
    {
        if (! $this
            ->type_numeric($value, false, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        if ('0.0' === $valueNumeric) {
            return Ret::ok($valueNumeric);
        }

        if ('-' === $valueNumeric[ 0 ]) {
            return Ret::ok($valueNumeric);
        }

        return Ret::err(
            [ 'The `value` should be numeric float, non positive', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_float_negative($value, array $refs = [])
    {
        if (! $this
            ->type_numeric($value, false, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        if ('0.0' === $valueNumeric) {
            return Ret::err(
                [ 'The `value` should be numeric float, negative, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-' === $valueNumeric[ 0 ]) {
            return Ret::ok($valueNumeric);
        }

        return Ret::err(
            [ 'The `value` should be numeric float, negative', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_float_positive($value, array $refs = [])
    {
        if (! $this
            ->type_numeric($value, false, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        if ('0.0' === $valueNumeric) {
            return Ret::err(
                [ 'The `value` should be numeric float, positive, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-' !== $valueNumeric[ 0 ]) {
            return Ret::ok($valueNumeric);
        }

        return Ret::err(
            [ 'The `value` should be numeric float, positive', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * 1.5 => '1.50'
     *
     * @return Ret<string>
     */
    public function type_numeric_trimpad($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        $withSplit = array_key_exists(0, $refs);
        $refSplit =& $refs[ 0 ];

        if (! $this
            ->type_numeric($value, false, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
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

            $valueNumeric = "{$sign}{$int}{$fracNew}{$exp}";

            if ($withSplit) {
                $refSplit[ 3 ] = $fracNew;
            }
        }

        return Ret::ok($valueNumeric);
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_trimpad_non_zero($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        if (! $this
            ->type_numeric_trimpad($value, $lenTrim, $lenPad, $stringPad, $refs)
            ->isOk([ &$valueNumericTrimpad, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === rtrim($valueNumericTrimpad, '0.')) {
            return Ret::ok($valueNumericTrimpad);
        }

        return Ret::err(
            [ 'The `value` should be numeric trimpad, non zero', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_trimpad_non_negative($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        if (! $this
            ->type_numeric_trimpad($value, $lenTrim, $lenPad, $stringPad, $refs)
            ->isOk([ &$valueNumericTrimpad, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === rtrim($valueNumericTrimpad, '0.')) {
            return Ret::ok($valueNumericTrimpad);
        }

        if ('-' !== $valueNumericTrimpad[ 0 ]) {
            return Ret::ok($valueNumericTrimpad);
        }

        return Ret::err(
            [ 'The `value` should be numeric trimpad, non negative', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_trimpad_non_positive($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        if (! $this
            ->type_numeric_trimpad($value, $lenTrim, $lenPad, $stringPad, $refs)
            ->isOk([ &$valueNumericTrimpad, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === rtrim($valueNumericTrimpad, '0.')) {
            return Ret::ok($valueNumericTrimpad);
        }

        if ('-' === $valueNumericTrimpad[ 0 ]) {
            return Ret::ok($valueNumericTrimpad);
        }

        return Ret::err(
            [ 'The `value` should be numeric trimpad, non positive', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_trimpad_negative($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        if (! $this
            ->type_numeric_trimpad($value, $lenTrim, $lenPad, $stringPad, $refs)
            ->isOk([ &$valueNumericTrimpad, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === rtrim($valueNumericTrimpad, '0.')) {
            return Ret::err(
                [ 'The `value` should be numeric trimpad, negative, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-' === $valueNumericTrimpad[ 0 ]) {
            return Ret::ok($valueNumericTrimpad);
        }

        return Ret::err(
            [ 'The `value` should be numeric trimpad, negative', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_numeric_trimpad_positive($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        if (! $this
            ->type_numeric_trimpad($value, $lenTrim, $lenPad, $stringPad, $refs)
            ->isOk([ &$valueNumericTrimpad, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === rtrim($valueNumericTrimpad, '0.')) {
            return Ret::err(
                [ 'The `value` should be numeric trimpad, positive, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-' !== $valueNumericTrimpad[ 0 ]) {
            return Ret::ok($valueNumericTrimpad);
        }

        return Ret::err(
            [ 'The `value` should be numeric trimpad, positive', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * 1.5 -> (2) -> '1.50'
     * 1.500 -> (2) -> '1.50'
     * 1.501 -> (2) -> false
     *
     * @return Ret<string>
     */
    public function type_decimal($value, int $scale = 0, array $refs = [])
    {
        if ($scale < 0) {
            return Ret::err(
                [ 'The `scale` should be positive', $scale ],
                [ __FILE__, __LINE__ ]
            );
        }

        $withSplit = array_key_exists(0, $refs);
        $refSplit =& $refs[ 0 ];

        if (! $this
            ->type_numeric($value, false, $refs)
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        [ $sign, $int, $frac ] = $refSplit;

        $valueScale = ('' === $frac)
            ? 0
            : (strlen($frac) - 1);

        if ($valueScale > $scale) {
            return Ret::err(
                [ 'The `value` scale should be less than limited scale', $scale ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ($valueScale < $scale) {
            if ('' === $frac) {
                $frac = '.';
            }

            $frac = str_pad($frac, $scale + 1, '0', STR_PAD_RIGHT);

            $valueNumeric = "{$sign}{$int}{$frac}";

            if ($withSplit) {
                $refSplit[ 3 ] = $frac;
            }
        }

        return Ret::ok($valueNumeric);
    }

    /**
     * @return Ret<string>
     */
    public function type_decimal_non_zero($value, int $scale = 0, array $refs = [])
    {
        if (! $this
            ->type_decimal($value, $scale, $refs)
            ->isOk([ &$valueDecimal, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === rtrim($valueDecimal, '0.')) {
            return Ret::ok($valueDecimal);
        }

        return Ret::err(
            [ 'The `value` should be decimal, non zero', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_decimal_non_negative($value, int $scale = 0, array $refs = [])
    {
        if (! $this
            ->type_decimal($value, $scale, $refs)
            ->isOk([ &$valueDecimal, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === rtrim($valueDecimal, '0.')) {
            return Ret::ok($valueDecimal);
        }

        if ('-' !== $valueDecimal[ 0 ]) {
            return Ret::ok($valueDecimal);
        }

        return Ret::err(
            [ 'The `value` should be decimal, non negative', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_decimal_non_positive($value, int $scale = 0, array $refs = [])
    {
        if (! $this
            ->type_decimal($value, $scale, $refs)
            ->isOk([ &$valueDecimal, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === rtrim($valueDecimal, '0.')) {
            return Ret::ok($valueDecimal);
        }

        if ('-' === $valueDecimal[ 0 ]) {
            return Ret::ok($valueDecimal);
        }

        return Ret::err(
            [ 'The `value` should be decimal, non positive', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_decimal_negative($value, int $scale = 0, array $refs = [])
    {
        if (! $this
            ->type_decimal($value, $scale, $refs)
            ->isOk([ &$valueDecimal, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === rtrim($valueDecimal, '0.')) {
            return Ret::err(
                [ 'The `value` should be decimal, negative, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-' === $valueDecimal[ 0 ]) {
            return Ret::ok($valueDecimal);
        }

        return Ret::err(
            [ 'The `value` should be decimal, negative', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_decimal_positive($value, int $scale = 0, array $refs = [])
    {
        if (! $this
            ->type_decimal($value, $scale, $refs)
            ->isOk([ &$valueDecimal, &$ret ])
        ) {
            return $ret;
        }

        if ('0' === rtrim($valueDecimal, '0.')) {
            return Ret::err(
                [ 'The `value` should be decimal, positive, zero is not', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('-' !== $valueDecimal[ 0 ]) {
            return Ret::ok($valueDecimal);
        }

        return Ret::err(
            [ 'The `value` should be decimal, positive', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<int|float>
     */
    public function type_num($value)
    {
        if (is_int($value)) {
            return Ret::ok($value);
        }

        if (is_float($value)) {
            if (! is_finite($value)) {
                // > NAN, INF, -INF is float, but should not be parsed
                return Ret::err(
                    [ 'The `value` should be num, non finite is not', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if (0 == $value) {
                // > -0.0 to 0.0
                return Ret::ok(0.0);
            }

            if (abs($value) >= _NUM_PHP_FLOAT_MAX_FLOAT_DIG) {
                return Ret::ok(
                    ($value > 0)
                        ? _NUM_PHP_FLOAT_MAX_FLOAT_DIG
                        : -_NUM_PHP_FLOAT_MAX_FLOAT_DIG
                );
            }

            // // > практическая польза нулевая, но для проверки дополнительный вызов и куча работы со строками
            // $valueFloatMin = $this->castNumericToFloatMin($valueAbs);
            // if (false !== $valueFloatMin) {
            //     return Ret::ok(
            //         $value > 0
            //             ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //             : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //     );
            // }

            return Ret::ok($value);
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
            || (Nil::is($value))
        ) {
            // > NULL is not num
            // > EMPTY STRING is not num
            // > BOOLEAN is not num
            // > ARRAY is not num
            // > RESOURCE is not num
            // > NIL is not num
            return Ret::err(
                [ 'The `value` should be num', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $this
            ->type_numeric($value, true, [ &$split ])
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        $valueNum = $this->castNumericToNum($valueNumeric, ...$split);

        if (false !== $valueNum) {
            return Ret::ok($valueNum);
        }

        return Ret::err(
            [ 'The `value` should be num', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<int|float>
     */
    public function type_num_non_zero($value)
    {
        if (! $this
            ->type_num($value)
            ->isOk([ &$valueNum, &$ret ])
        ) {
            return $ret;
        }

        if (0 == $valueNum) {
            return Ret::err(
                [ 'The `value` should be num, non zero', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueNum);
    }

    /**
     * @return Ret<int|float>
     */
    public function type_num_non_negative($value)
    {
        if (! $this
            ->type_num($value)
            ->isOk([ &$valueNum, &$ret ])
        ) {
            return $ret;
        }

        if ($valueNum < 0) {
            return Ret::err(
                [ 'The `value` should be num, non negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueNum);
    }

    /**
     * @return Ret<int|float>
     */
    public function type_num_non_positive($value)
    {
        if (! $this
            ->type_num($value)
            ->isOk([ &$valueNum, &$ret ])
        ) {
            return $ret;
        }

        if ($valueNum > 0) {
            return Ret::err(
                [ 'The `value` should be num, non positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueNum);
    }

    /**
     * @return Ret<int|float>
     */
    public function type_num_negative($value)
    {
        if (! $this
            ->type_num($value)
            ->isOk([ &$valueNum, &$ret ])
        ) {
            return $ret;
        }

        if ($valueNum >= 0) {
            return Ret::err(
                [ 'The `value` should be num, negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueNum);
    }

    /**
     * @return Ret<int|float>
     */
    public function type_num_positive($value)
    {
        if (! $this
            ->type_num($value)
            ->isOk([ &$valueNum, &$ret ])
        ) {
            return $ret;
        }

        if ($valueNum <= 0) {
            return Ret::err(
                [ 'The `value` should be num, positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueNum);
    }


    /**
     * @return Ret<int>
     */
    public function type_int($value)
    {
        if (is_int($value)) {
            return Ret::ok($value);
        }

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && ! is_finite($value))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            return Ret::err(
                [ 'The `value` should be int', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $this
            ->type_numeric($value, true, [ &$split ])
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        $valueInt = $this->castNumericToInt($valueNumeric, ...$split);

        if (false !== $valueInt) {
            return Ret::ok($valueInt);
        }

        return Ret::err(
            [ 'The `value` should be int', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<int>
     */
    public function type_int_non_zero($value)
    {
        if (! $this
            ->type_int($value)
            ->isOk([ &$valueInt, &$ret ])
        ) {
            return $ret;
        }

        if (0 === $valueInt) {
            return Ret::err(
                [ 'The `value` should be int, non-zero', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueInt);
    }

    /**
     * @return Ret<int>
     */
    public function type_int_non_negative($value)
    {
        if (! $this
            ->type_int($value)
            ->isOk([ &$valueInt, &$ret ])
        ) {
            return $ret;
        }

        if ($valueInt < 0) {
            return Ret::err(
                [ 'The `value` should be int, non negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueInt);
    }

    /**
     * @return Ret<int>
     */
    public function type_int_non_positive($value)
    {
        if (! $this
            ->type_int($value)
            ->isOk([ &$valueInt, &$ret ])
        ) {
            return $ret;
        }

        if ($valueInt > 0) {
            return Ret::err(
                [ 'The `value` should be int, non positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueInt);
    }

    /**
     * @return Ret<int>
     */
    public function type_int_negative($value)
    {
        if (! $this
            ->type_int($value)
            ->isOk([ &$valueInt, &$ret ])
        ) {
            return $ret;
        }

        if ($valueInt >= 0) {
            return Ret::err(
                [ 'The `value` should be int, negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueInt);
    }

    /**
     * @return Ret<int>
     */
    public function type_int_positive($value)
    {
        if (! $this
            ->type_int($value)
            ->isOk([ &$valueInt, &$ret ])
        ) {
            return $ret;
        }

        if ($valueInt <= 0) {
            return Ret::err(
                [ 'The `value` should be int, positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueInt);
    }

    /**
     * @return Ret<int>
     */
    public function type_int_positive_or_minus_one($value)
    {
        if (! $this
            ->type_int($value)
            ->isOk([ &$valueInt, &$ret ])
        ) {
            return $ret;
        }

        if (-1 === $valueInt) {
            return Ret::ok($valueInt);
        }

        if ($valueInt <= 0) {
            return Ret::err(
                [ 'The `value` should be int, positive or minus one', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueInt);
    }

    /**
     * @return Ret<int>
     */
    public function type_int_non_negative_or_minus_one($value)
    {
        if (! $this
            ->type_int($value)
            ->isOk([ &$valueInt, &$ret ])
        ) {
            return $ret;
        }

        if ($valueInt < -1) {
            return Ret::err(
                [ 'The `value` should be int, negative or minus one', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueInt);
    }


    /**
     * @return Ret<float>
     */
    public function type_float($value)
    {
        if (is_int($value)) {
            return Ret::ok((float) $value);
        }

        if (is_float($value)) {
            if (! is_finite($value)) {
                // > NAN, INF, -INF is float, but should not be parsed
                return Ret::err(
                    [ 'The `value` should be float, non finite is not', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if (-0.0 === $value) {
                return Ret::ok(0.0);
            }

            if (abs($value) >= _NUM_PHP_FLOAT_MAX_FLOAT_DIG) {
                return Ret::ok(
                    ($value > 0)
                        ? _NUM_PHP_FLOAT_MAX_FLOAT_DIG
                        : -_NUM_PHP_FLOAT_MAX_FLOAT_DIG
                );
            }

            // // > практическая польза нулевая, но для проверки дополнительный вызов и куча работы со строками
            // $valueFloatMin = $this->castNumericToFloatMin($valueAbs);
            // if (false !== $valueFloatMin) {
            //     return Ret::ok(
            //         ($value > 0)
            //             ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //             : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //     );
            // }

            return Ret::ok($value);
        }

        if (false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            // || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            // > NULL is not float
            // > EMPTY STRING is not float
            // > BOOLEAN is not float
            // > ARRAY is not float
            // > RESOURCE is not float
            // > NIL is not float
            return Ret::err(
                [ 'The `value` should be float', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $this
            ->type_numeric($value, true, [ &$split ])
            ->isOk([ &$valueNumeric, &$ret ])
        ) {
            return $ret;
        }

        $valueFloat = $this->castNumericToFloat($valueNumeric, ...$split);

        if (false !== $valueFloat) {
            return Ret::ok($valueFloat);
        }

        return Ret::err(
            [ 'The `value` should be float', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>
     */
    public function type_float_non_zero($value)
    {
        if (! $this
            ->type_float($value)
            ->isOk([ &$valueFloat, &$ret ])
        ) {
            return $ret;
        }

        if (0 == $valueFloat) {
            return Ret::err(
                [ 'The `value` should be float, non zero', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueFloat);
    }

    /**
     * @return Ret<float>
     */
    public function type_float_non_negative($value)
    {
        if (! $this
            ->type_float($value)
            ->isOk([ &$valueFloat, &$ret ])
        ) {
            return $ret;
        }

        if ($valueFloat < 0) {
            return Ret::err(
                [ 'The `value` should be float, non negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueFloat);
    }

    /**
     * @return Ret<float>
     */
    public function type_float_non_positive($value)
    {
        if (! $this
            ->type_float($value)
            ->isOk([ &$valueFloat, &$ret ])
        ) {
            return $ret;
        }

        if ($valueFloat > 0) {
            return Ret::err(
                [ 'The `value` should be float, non-positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueFloat);
    }

    /**
     * @return Ret<float>
     */
    public function type_float_negative($value)
    {
        if (! $this
            ->type_float($value)
            ->isOk([ &$valueFloat, &$ret ])
        ) {
            return $ret;
        }

        if ($valueFloat >= 0) {
            return Ret::err(
                [ 'The `value` should be float, negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueFloat);
    }

    /**
     * @return Ret<float>
     */
    public function type_float_positive($value)
    {
        if (! $this
            ->type_float($value)
            ->isOk([ &$valueFloat, &$ret ])
        ) {
            return $ret;
        }

        if ($valueFloat <= 0) {
            return Ret::err(
                [ 'The `value` should be float, positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueFloat);
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

        $numberValid = $this->type_number($number, false)->orThrow();

        if ($numberValid->isZero()) {
            return 0.0;
        }

        $isNegative = $numberValid->isNegative();

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

        $scaledAbs = $numberValid->getValueAbsolute() * $factor;

        $scaledAbsNumber = $this->type_number($scaledAbs, false)->orThrow();

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

        $numberValid = $this->type_number($number, false)->orThrow();

        if ($numberValid->isZero()) {
            return 0.0;
        }

        $isNegative = $numberValid->isNegative();

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

        $scaledAbs = $numberValid->getValueAbsolute() * $factor;

        $scaledAbsNumber = $this->type_number($scaledAbs, false)->orThrow();

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
