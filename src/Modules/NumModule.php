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
     * @var int
     */
    protected static $scaleDefault = 0;

    /**
     * @var int
     */
    protected static $scaleFrac = 4;

    /**
     * @var int
     */
    protected static $scaleLimit = 16;

    /**
     * @param int|false|null $scaleDefault
     *
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public static function staticScaleDefault($scaleDefault = null) : int
    {
        $hasBcmath = extension_loaded('bcmath');

        $last = static::$scaleLimit;

        if ( null !== $scaleDefault ) {
            if ( false === $scaleDefault ) {
                static::$scaleDefault = $hasBcmath ? bcscale() : 0;

            } else {
                if ( $scaleDefault < 0 ) {
                    throw new LogicException(
                        [ 'The `scaleDefault` should be a non-negative integer', $scaleDefault ]
                    );
                }

                static::$scaleDefault = $scaleDefault;
            }
        }

        $scaleDefaultNew = null
            ?? static::$scaleDefault
            ?? ($hasBcmath ? bcscale() : 0);

        if ( $last !== $scaleDefaultNew ) {
            $scaleLimit = static::$scaleLimit;

            if ( $scaleDefaultNew > $scaleLimit ) {
                throw new LogicException(
                    [ 'The `scaleDefault` is bigger than allowed maximum', $scaleDefaultNew, $scaleLimit ]
                );
            }

            if ( $hasBcmath ) {
                bcscale($scaleDefaultNew);
            }
        }

        static::$scaleDefault = $scaleDefaultNew;

        return $last;
    }

    /**
     * @param int|false|null $scaleFrac
     */
    public static function staticScaleFrac($scaleFrac = null) : int
    {
        $last = static::$scaleFrac;

        if ( null !== $scaleFrac ) {
            if ( false === $scaleFrac ) {
                static::$scaleFrac = 4;

            } else {
                if ( $scaleFrac < 0 ) {
                    throw new LogicException(
                        [ 'The `scaleFrac` should be a non-negative integer', $scaleFrac ]
                    );
                }

                static::$scaleFrac = $scaleFrac;
            }
        }

        $scaleFracNew = static::$scaleFrac ?? 4;

        if ( $last !== $scaleFracNew ) {
            $scaleLimit = static::$scaleLimit;

            if ( $scaleFracNew > $scaleLimit ) {
                throw new LogicException(
                    [ 'The `scaleFrac` is bigger than allowed maximum', $scaleFracNew, $scaleLimit ]
                );
            }
        }

        static::$scaleFrac = $scaleFracNew;

        return $last;
    }

    /**
     * @param int|false|null $scaleLimit
     */
    public static function staticScaleLimit($scaleLimit = null) : int
    {
        $last = static::$scaleLimit;

        if ( null !== $scaleLimit ) {
            if ( false === $scaleLimit ) {
                static::$scaleLimit = 16;

            } else {
                if ( $scaleLimit < 0 ) {
                    throw new LogicException(
                        [ 'The `scaleLimit` should be a non-negative integer', $scaleLimit ]
                    );
                }

                static::$scaleLimit = $scaleLimit;
            }
        }

        $scaleLimitNew = static::$scaleLimit ?? 16;

        if ( $last !== $scaleLimitNew ) {
            $scaleDefault = static::$scaleDefault;
            $scaleFrac = static::$scaleFrac;

            if ( $scaleDefault > $scaleLimitNew ) {
                throw new LogicException(
                    [ 'The `scaleDefault` is bigger than allowed maximum', $scaleDefault, $scaleLimitNew ]
                );
            }

            if ( $scaleFrac > $scaleLimitNew ) {
                throw new LogicException(
                    [ 'The `scaleFrac` is bigger than allowed maximum', $scaleFrac, $scaleLimitNew ]
                );
            }
        }

        static::$scaleLimit = $scaleLimitNew;

        return $last;
    }


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    /**
     * @return Ret<float>|float
     */
    public function type_php_int($fb, $value)
    {
        if ( is_int($value) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be int', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>|float
     */
    public function type_php_float($fb, $value)
    {
        if ( is_float($value) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be float', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<float>|float
     */
    public function type_nan($fb, $value)
    {
        if ( is_float($value) && is_nan($value) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be nan', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_not_nan($fb, $value)
    {
        if ( is_float($value) && ! is_nan($value) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be float not nan', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_maybe_nan($fb, $value)
    {
        if ( is_float($value) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be float', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>|mixed
     */
    public function type_any_not_nan($fb, $value)
    {
        if ( ! (is_float($value) && is_nan($value)) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be not nan', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<float>|float
     */
    public function type_finite($fb, $value)
    {
        if ( is_float($value) && is_finite($value) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be finite', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_not_finite($fb, $value)
    {
        if ( is_float($value) && ! is_finite($value) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be float not finite', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>|mixed
     */
    public function type_any_not_finite($fb, $value)
    {
        if ( ! (is_float($value) && is_finite($value)) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be not finite', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<float>|float
     */
    public function type_infinite($fb, $value)
    {
        if ( is_float($value) && is_infinite($value) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be infinite', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_not_infinite($fb, $value)
    {
        if ( is_float($value) && ! is_infinite($value) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be bool, null is not', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>|mixed
     */
    public function type_any_not_infinite($fb, $value)
    {
        if ( ! (is_float($value) && is_infinite($value)) ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be not infinite', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<float>|float
     */
    public function type_float_min($fb, $value)
    {
        if ( false
            || is_float($value)
            || is_numeric($value)
        ) {
            [ $mant, $exp ] = explode('E', $value) + [ 1 => '' ];
            [ $int, $frac ] = explode('.', $mant) + [ 1 => '' ];

            $exp = ('' === $exp) ? '' : "E{$exp}";

            $frac = substr($frac, 0, PHP_FLOAT_DIG - 1);
            $frac = str_pad($frac, PHP_FLOAT_DIG, '9', STR_PAD_RIGHT);

            if ( "{$int}{$frac}{$exp}" === _NUM_PHP_FLOAT_MIN_STRING_DIG ) {
                return Ret::ok(
                    $fb,
                    ($value > 0)
                        ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
                        : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG
                );
            }
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be float min', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_not_float_min($fb, $value)
    {
        if ( ! is_float($value) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be float', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->type_float_min(null, $value);

        if ( ! $ret->isOk() ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be float but not equal `float_min`', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<mixed>|mixed
     */
    public function type_any_not_float_min($fb, $value)
    {
        $ret = $this->type_float_min(null, $value);

        if ( ! $ret->isOk() ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be not equal to `float_min`', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<string>|string
     */
    public function type_numeric($fb, $value, ?bool $isAllowExp = null, array $refs = [])
    {
        $isAllowExp = $isAllowExp ?? true;

        $theType = Lib::type();

        $withSplit = array_key_exists(0, $refs);
        if ( $withSplit ) {
            $refSplit =& $refs[0];
        }
        $refSplit = null;

        if ( $value instanceof Number ) {
            $number = $value;

            $exp = $number->getExp();

            if ( ! $isAllowExp ) {
                if ( '' !== $exp ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `value` should be numeric, without exponent', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }

            if ( $withSplit ) {
                $refSplit = [];
                $refSplit[0] = $number->getSign();
                $refSplit[1] = $number->getInt();
                $refSplit[2] = $number->getFrac();
                $refSplit[3] = $exp;
            }

            return Ret::ok($fb, $number->getValue());
        }

        $isInt = is_int($value);
        $isFloat = is_float($value);

        if ( false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            //
            // || (is_int($value))
            // || (is_float($value))
            // || (is_float($value) && (! is_finite($value)))
            //
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $valueTrim = null;

        if ( $isInt || $isFloat ) {
            if ( 0 == $value ) {
                $valueTrim = '0';

                if ( ! $withSplit ) {
                    return Ret::ok($fb, $valueTrim);
                }
            }
        }

        if ( null === $valueTrim ) {
            $ret = $theType->trim($value);

            if ( ! $ret->isOk([ &$valueTrim ]) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be numeric, empty trim is not', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        if ( $hasExp = (false !== ($expPos = stripos($valueTrim, 'e'))) ) {
            if ( ! $isAllowExp ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be numeric, without exponent', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $valueTrim[$expPos] = 'E';
        }

        if ( $isInt && $isFloat ) {
            if ( ! $withSplit ) {
                if ( ! $hasExp ) {
                    $valueTrim = rtrim($valueTrim, '0.');

                } else {
                    [ $left, $right ] = explode('E', $valueTrim);

                    $left = rtrim($left, '0.');

                    $valueTrim = "{$left}{$right}";
                }

                return Ret::ok($fb, $valueTrim);
            }
        }

        $regex = ''
            . '/^'
            . '([+-]?)'
            . '((?:0|[1-9]\d*))'
            . '(\.\d+)?'
            . ($isAllowExp ? '([E][+-]?\d+)?' : '')
            . '$/';

        if ( ! preg_match($regex, $valueTrim, $matches) ) {
            return Ret::throw(
                $fb,
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

        if ( $sign === '+' ) {
            $sign = '';
        }

        $frac = rtrim($frac, '0.');

        $isZero = ! preg_match('/[1-9]/', "{$int}{$frac}");

        if ( $isZero ) {
            $sign = '';
            $int = '0';
            $frac = '';
            $exp = '';
        }

        if ( $withSplit ) {
            $refSplit = [];
            $refSplit[0] = $sign;
            $refSplit[1] = $int;
            $refSplit[2] = $frac;
            $refSplit[3] = $exp;
        }

        $valueNumeric = "{$sign}{$int}{$frac}{$exp}";

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_int($fb, $value, array $refs = [])
    {
        $refSplit =& $refs[0];

        // > btw, '1.1e1' is can be converted to 11i too
        // > we better don't support that numbers here
        $ret = $this->type_numeric(null, $value, false, $refs);
        //
        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        [ , , $frac ] = $refSplit;

        if ( '' !== $frac ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric int, without fractional part', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_float($fb, $value, array $refs = [])
    {
        $withSplit = array_key_exists(0, $refs);
        $refSplit =& $refs[0];

        // > btw, '1.1e-1' can be converted to 0.11f too
        // > but, we better don't support that numbers here
        $ret = $this->type_numeric(null, $value, false, $refs);
        //
        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        [ $sign, $int, $frac ] = $refSplit;

        if ( '' === $frac ) {
            $frac = '.0';

            $valueNumeric = "{$sign}{$int}{$frac}";

            if ( $withSplit ) {
                $refSplit[3] = $frac;
            }
        }

        if ( '0' === $valueNumeric ) {
            return Ret::ok($fb, '0.0');
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * 1.5 -> (2) -> '1.50'
     * 1.500 -> (2) -> '1.50'
     * 1.501 -> (2) -> [ERROR]
     *
     * @return Ret<string>|string
     */
    public function type_decimal($fb, $value, int $scale = 0, array $refs = [])
    {
        if ( $scale < 0 ) {
            return Ret::throw(
                $fb,
                [ 'The `scale` should be positive', $scale ],
                [ __FILE__, __LINE__ ]
            );
        }

        $withSplit = array_key_exists(0, $refs);
        $refSplit =& $refs[0];

        $ret = $this->type_numeric(null, $value, false, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        [ $sign, $int, $frac ] = $refSplit;

        $valueScale = ('' === $frac)
            ? 0
            : (strlen($frac) - 1);

        if ( $valueScale > $scale ) {
            return Ret::throw(
                $fb,
                [ 'The `value` scale should be less than limited scale', $scale ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $valueScale < $scale ) {
            if ( '' === $frac ) {
                $frac = '.';
            }

            $frac = str_pad($frac, $scale + 1, '0', STR_PAD_RIGHT);

            $valueNumeric = "{$sign}{$int}{$frac}";

            if ( $withSplit ) {
                $refSplit[3] = $frac;
            }
        }

        return Ret::ok($fb, $valueNumeric);
    }


    /**
     * @return Ret<string>|string
     */
    public function type_numeric_non_zero($fb, $value, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNumeric == 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, non-zero', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_non_negative($fb, $value, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNumeric >= 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, non-negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_non_positive($fb, $value, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNumeric <= 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, non-positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_negative($fb, $value, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNumeric < 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_positive($fb, $value, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNumeric > 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_non_negative_or_minus_one($fb, $value, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! (false
            || ($valueNumeric >= 0)
            || ($valueNumeric == -1)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, non-negative or minus one', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_positive_or_minus_one($fb, $value, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! (false
            || ($valueNumeric > 0)
            || ($valueNumeric == -1)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, positive or minus one', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_gt($fb, $value, $gt, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $gtNum = $this->type_num([], $gt);

        if ( ! ($valueNumeric > $gtNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, GT ' . $gtNum, $value, $gt ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_gte($fb, $value, $gte, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $gteNum = $this->type_num([], $gte);

        if ( ! ($valueNumeric >= $gteNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, GTE ' . $gteNum, $value, $gte ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_lt($fb, $value, $lt, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ltNum = $this->type_num([], $lt);

        if ( ! ($valueNumeric < $ltNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, LT ' . $ltNum, $value, $lt ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_lte($fb, $value, $lte, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $lteNum = $this->type_num([], $lte);

        if ( ! ($valueNumeric <= $lteNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, LT ' . $lteNum, $value, $lte ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_between($fb, $value, $from, $to, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $fromNum = $this->type_num([], $from);
        $toNum = $this->type_num([], $to);

        if ( ! (true
            && ($fromNum >= $valueNumeric)
            && ($valueNumeric <= $toNum)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, BTW (' . $fromNum . ', ' . $toNum . ')', $value, $from, $to ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_numeric_inside($fb, $value, $from, $to, ?bool $isAllowExp = null, array $refs = [])
    {
        $ret = $this->type_numeric(null, $value, $isAllowExp, $refs);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $fromNum = $this->type_num([], $from);
        $toNum = $this->type_num([], $to);

        if ( ! (true
            && ($fromNum > $valueNumeric)
            && ($valueNumeric < $toNum)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be numeric, BTW (' . $fromNum . ', ' . $toNum . ')', $value, $from, $to ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNumeric);
    }


    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num($fb, $value)
    {
        if ( is_int($value) ) {
            return Ret::ok($fb, $value);
        }

        if ( is_float($value) ) {
            if ( ! is_finite($value) ) {
                // > NAN, INF, -INF is float, but should not be parsed
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be num, non finite is not', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( 0 == $value ) {
                // > -0.0 to 0.0
                return Ret::ok($fb, 0.0);
            }

            if ( abs($value) >= _NUM_PHP_FLOAT_MAX_FLOAT_DIG ) {
                return Ret::ok(
                    $fb,
                    ($value > 0)
                        ? _NUM_PHP_FLOAT_MAX_FLOAT_DIG
                        : -_NUM_PHP_FLOAT_MAX_FLOAT_DIG
                );
            }

            // // > практическая польза почти нулевая, но для проверки дополнительный вызов и куча работы со строками
            //
            // $valueFloatMin = $this->castNumericToFloatMin($valueAbs);
            // if ( false !== $valueFloatMin ) {
            //     return Ret::ok(
            //         $fb,
            //         ($value > 0)
            //             ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //             : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //     );
            // }
            //
            // // < практическая польза почти нулевая, но для проверки дополнительный вызов и куча работы со строками

            return Ret::ok($fb, $value);
        }

        if ( false
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
            return Ret::throw(
                $fb,
                [ 'The `value` should be num', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->type_numeric(null, $value, true, [ &$split ]);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueNum = $this->castNumericToNum($valueNumeric, ...$split);

        if ( false !== $valueNum ) {
            return Ret::ok($fb, $valueNum);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be num', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int($fb, $value)
    {
        if ( is_int($value) ) {
            return Ret::ok($fb, $value);
        }

        if ( false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && ! is_finite($value))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be int', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->type_numeric(null, $value, true, [ &$split ]);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueInt = $this->castNumericToInt($valueNumeric, ...$split);

        if ( false !== $valueInt ) {
            return Ret::ok($fb, $valueInt);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be int', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float($fb, $value)
    {
        if ( is_int($value) ) {
            return Ret::ok($fb, (float) $value);
        }

        if ( is_float($value) ) {
            if ( ! is_finite($value) ) {
                // > NAN, INF, -INF is float, but should not be parsed
                return Ret::throw(
                    $fb,
                    [ 'The `value` should be float, non finite is not', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( -0.0 === $value ) {
                return Ret::ok($fb, 0.0);
            }

            if ( abs($value) >= _NUM_PHP_FLOAT_MAX_FLOAT_DIG ) {
                return Ret::ok(
                    $fb,
                    ($value > 0)
                        ? _NUM_PHP_FLOAT_MAX_FLOAT_DIG
                        : -_NUM_PHP_FLOAT_MAX_FLOAT_DIG
                );
            }

            // // > практическая польза почти нулевая, но для проверки дополнительный вызов и куча работы со строками
            //
            // $valueFloatMin = $this->castNumericToFloatMin($valueAbs);
            // if ( false !== $valueFloatMin ) {
            //     return Ret::ok(
            //         $fb,
            //         ($value > 0)
            //             ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //             : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //     );
            // }
            //
            // // < практическая польза почти нулевая, но для проверки дополнительный вызов и куча работы со строками

            return Ret::ok($fb, $value);
        }

        if ( false
            || (null === $value)
            || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            //
            // || (is_int($value))
            // || (is_float($value))
            // || (is_float($value) && (! is_finite($value)))
            //
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            // > NULL is not float
            // > EMPTY STRING is not float
            // > BOOLEAN is not float
            // > ARRAY is not float
            // > RESOURCE is not float
            // > NIL is not float
            return Ret::throw(
                $fb,
                [ 'The `value` should be float', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->type_numeric(null, $value, true, [ &$split ]);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueFloat = $this->castNumericToFloat($valueNumeric, ...$split);

        if ( false !== $valueFloat ) {
            return Ret::ok($fb, $valueFloat);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be float', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_non_zero($fb, $value)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum == 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-zero', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_non_negative($fb, $value)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum >= 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_non_positive($fb, $value)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum <= 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_negative($fb, $value)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum < 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_positive($fb, $value)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum > 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_non_negative_or_minus_one($fb, $value)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! (false
            || ($valueNum >= 0)
            || ($valueNum == -1)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-negative or minus one', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_positive_or_minus_one($fb, $value)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! (false
            || ($valueNum > 0)
            || ($valueNum == -1)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, positive or minus one', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_gt($fb, $value, $gt)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $gtNum = $this->type_num([], $gt);

        if ( ! ($valueNum > $gtNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, GT ' . $gtNum, $value, $gt ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_gte($fb, $value, $gte)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $gteNum = $this->type_num([], $gte);

        if ( ! ($valueNum >= $gteNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, GTE ' . $gteNum, $value, $gte ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_lt($fb, $value, $lt)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ltNum = $this->type_num([], $lt);

        if ( ! ($valueNum < $ltNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, LT ' . $ltNum, $value, $lt ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_lte($fb, $value, $lte)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $lteNum = $this->type_num([], $lte);

        if ( ! ($valueNum <= $lteNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, LT ' . $lteNum, $value, $lte ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_between($fb, $value, $from, $to)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $fromNum = $this->type_num([], $from);
        $toNum = $this->type_num([], $to);

        if ( ! (true
            && ($fromNum >= $valueNum)
            && ($valueNum <= $toNum)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, BTW (' . $fromNum . ', ' . $toNum . ')', $value, $from, $to ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int|float>|int|float
     */
    public function type_num_inside($fb, $value, $from, $to)
    {
        $ret = $this->type_num(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $fromNum = $this->type_num([], $from);
        $toNum = $this->type_num([], $to);

        if ( ! (true
            && ($fromNum > $valueNum)
            && ($valueNum < $toNum)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, BTW (' . $fromNum . ', ' . $toNum . ')', $value, $from, $to ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }


    /**
     * @return Ret<int>|int
     */
    public function type_int_non_zero($fb, $value)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum == 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-zero', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_non_negative($fb, $value)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum >= 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_non_positive($fb, $value)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum <= 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_negative($fb, $value)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum < 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_positive($fb, $value)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum > 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_non_negative_or_minus_one($fb, $value)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! (false
            || ($valueNum >= 0)
            || ($valueNum == -1)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-negative or minus one', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_positive_or_minus_one($fb, $value)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! (false
            || ($valueNum > 0)
            || ($valueNum == -1)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, positive or minus one', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_gt($fb, $value, $gt)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $gtNum = $this->type_num([], $gt);

        if ( ! ($valueNum > $gtNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, GT ' . $gtNum, $value, $gt ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_gte($fb, $value, $gte)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $gteNum = $this->type_num([], $gte);

        if ( ! ($valueNum >= $gteNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, GTE ' . $gteNum, $value, $gte ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_lt($fb, $value, $lt)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ltNum = $this->type_num([], $lt);

        if ( ! ($valueNum < $ltNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, LT ' . $ltNum, $value, $lt ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_lte($fb, $value, $lte)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $lteNum = $this->type_num([], $lte);

        if ( ! ($valueNum <= $lteNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, LT ' . $lteNum, $value, $lte ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_between($fb, $value, $from, $to)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $fromNum = $this->type_num([], $from);
        $toNum = $this->type_num([], $to);

        if ( ! (true
            && ($fromNum >= $valueNum)
            && ($valueNum <= $toNum)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, BTW (' . $fromNum . ', ' . $toNum . ')', $value, $from, $to ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_int_inside($fb, $value, $from, $to)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $fromNum = $this->type_num([], $from);
        $toNum = $this->type_num([], $to);

        if ( ! (true
            && ($fromNum > $valueNum)
            && ($valueNum < $toNum)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, BTW (' . $fromNum . ', ' . $toNum . ')', $value, $from, $to ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }


    /**
     * @return Ret<float>|float
     */
    public function type_float_non_zero($fb, $value)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum == 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-zero', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_non_negative($fb, $value)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum >= 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_non_positive($fb, $value)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum <= 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_negative($fb, $value)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum < 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, negative', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_positive($fb, $value)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! ($valueNum > 0) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, positive', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_non_negative_or_minus_one($fb, $value)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! (false
            || ($valueNum >= 0)
            || ($valueNum == -1)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, non-negative or minus one', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_positive_or_minus_one($fb, $value)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! (false
            || ($valueNum > 0)
            || ($valueNum == -1)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, positive or minus one', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_gt($fb, $value, $gt)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $gtNum = $this->type_num([], $gt);

        if ( ! ($valueNum > $gtNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, GT ' . $gtNum, $value, $gt ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_gte($fb, $value, $gte)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $gteNum = $this->type_num([], $gte);

        if ( ! ($valueNum >= $gteNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, GTE ' . $gteNum, $value, $gte ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_lt($fb, $value, $lt)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ltNum = $this->type_num([], $lt);

        if ( ! ($valueNum < $ltNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, LT ' . $ltNum, $value, $lt ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_lte($fb, $value, $lte)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $lteNum = $this->type_num([], $lte);

        if ( ! ($valueNum <= $lteNum) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, LT ' . $lteNum, $value, $lte ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_between($fb, $value, $from, $to)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $fromNum = $this->type_num([], $from);
        $toNum = $this->type_num([], $to);

        if ( ! (true
            && ($fromNum >= $valueNum)
            && ($valueNum <= $toNum)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, BTW (' . $fromNum . ', ' . $toNum . ')', $value, $from, $to ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }

    /**
     * @return Ret<float>|float
     */
    public function type_float_inside($fb, $value, $from, $to)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueNum ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $fromNum = $this->type_num([], $from);
        $toNum = $this->type_num([], $to);

        if ( ! (true
            && ($fromNum > $valueNum)
            && ($valueNum < $toNum)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be num, BTW (' . $fromNum . ', ' . $toNum . ')', $value, $from, $to ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueNum);
    }


    /**
     * @return Ret<Number>|Number
     */
    public function type_number($fb, $value, ?bool $isAllowExp = null)
    {
        if ( $value instanceof Number ) {
            return Ret::ok($fb, $value);
        }

        $ret = $this->type_numeric(null, $value, $isAllowExp, [ &$split ]);

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $frac = $split[2];

        $scale = 0;
        if ( '' !== $frac ) {
            $scale = strlen($frac) - 1;
        }

        $ret = Number::fromValidArray([
            'original' => $value,
            //
            'sign'     => $split[0],
            'int'      => $split[1],
            'frac'     => $split[2],
            'exp'      => $split[3],
            'scale'    => $scale,
        ]);

        if ( ! $ret->isOk([ &$number ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $number);
    }


    /**
     * @return Ret<int>|int
     */
    public function type_exponent($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->int($value);

        if ( ! $ret->isOk([ &$exponentInt ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $exponentInt);
    }

    /**
     * @return Ret<int>|int
     */
    public function type_scale($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->int_non_negative($value);

        if ( ! $ret->isOk([ &$scaleInt ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $scaleLimit = static::staticScaleLimit();

        if ( $scaleInt > $scaleLimit ) {
            return Ret::throw(
                $fb,
                [
                    'The result `scaleMin` is bigger than allowed maximum',
                    $scaleInt,
                    $scaleLimit,
                ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $scaleInt);
    }


    /**
     * @return Ret<int>|int
     */
    public function type_percent($fb, $value)
    {
        $ret = $this->type_int(null, $value);

        if ( ! $ret->isOk([ &$valueInt ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be percent', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->type_num_between(null, $valueInt, 0, 100);

        if ( ! $ret->isOk([ &$valuePercent ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be percent', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valuePercent);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_percent_numeric($fb, $value)
    {
        $ret = $this->type_numeric_int(null, $value);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be percent, numeric', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->type_numeric_between(null, $valueNumeric, 0, 100);

        if ( ! $ret->isOk([ &$valuePercent ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be percent, numeric', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valuePercent);
    }


    /**
     * @return Ret<float>|float
     */
    public function type_ratio($fb, $value)
    {
        $ret = $this->type_float(null, $value);

        if ( ! $ret->isOk([ &$valueFloat ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be ratio', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->type_num_between(null, $valueFloat, -1, 1);

        if ( ! $ret->isOk([ &$valuePercent ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be ratio', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valuePercent);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_ratio_numeric($fb, $value)
    {
        $ret = $this->type_numeric_float(null, $value);

        if ( ! $ret->isOk([ &$valueNumeric ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be ratio, numeric', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->type_numeric_between(null, $valueNumeric, -1, 1);

        if ( ! $ret->isOk([ &$valuePercent ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be ratio, numeric', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valuePercent);
    }


    /**
     * > Математическое округление
     *
     * > Точка принятия решения - "дробная часть равна .5/.05/.005 и тд"
     * > Участвует только 1 разряд свыше указанного, как в математике (если число 1.005, а округляем до 1 знака, то 5 не участвует в решении, число будет 1.00)
     * > Середина определяется по первому не-нулевому разряду (для 1.005 при округлении до 2 знаков решение будет приниматься по третьему знаку 5)
     * > К середине применяется режим округления, выше середины - правило всегда "от нуля", ниже середины - правило "к нулю"
     *
     * > mathround(1.5) -> 2
     * > mathround(1.05) -> 1
     * > mathround(1.005) -> 1
     * > mathround(-1.005) -> -1
     * > mathround(-1.05) -> -1
     * > mathround(-1.5) -> -2
     */
    public function mathround(
        $number, ?int $scale = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : float
    {
        // $flags = $flags ?? _NUM_ROUND_AWAY_FROM_ZERO;
        // $flagsNegative = $flagsNegative ?? _NUM_ROUND_AWAY_FROM_ZERO;

        return $this->_mathround(
            $number, $scale,
            $flags, $flagsNegative
        );
    }

    /**
     * > mathround(1.5) -> 2
     * > mathround(1.05) -> 1
     * > mathround(1.005) -> 1
     * > mathround(-1.005) -> -1
     * > mathround(-1.05) -> -1
     * > mathround(-1.5) -> -2
     */
    public function mathround_even($number, ?int $scale = null) : float
    {
        return $this->_mathround(
            $number, $scale,
            _NUM_ROUND_EVEN, _NUM_ROUND_EVEN
        );
    }

    /**
     * > mathround(1.5) -> 3
     * > mathround(1.05) -> 1
     * > mathround(1.005) -> 1
     * > mathround(-1.005) -> -1
     * > mathround(-1.05) -> -1
     * > mathround(-1.5) -> -3
     */
    public function mathround_odd($number, ?int $scale = null) : float
    {
        return $this->_mathround(
            $number, $scale,
            _NUM_ROUND_ODD, _NUM_ROUND_ODD
        );
    }

    protected function _mathround(
        $number, ?int $scale = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : float
    {
        $scale = $scale ?? 0;

        if ( $scale < 0 ) {
            throw new LogicException(
                [ 'The `precision` should be a non-negative integer', $scale ]
            );
        }

        $numberValid = $this->type_number([], $number, false);

        if ( $numberValid->isZero() ) {
            return 0.0;
        }

        $isNegative = $numberValid->isNegative();

        $hasFlagsNonNegative = (null !== $flags);
        $hasFlagsNegative = (null !== $flagsNegative);

        $flagsCurrent = 0;
        if ( $isNegative ) {
            if ( $hasFlagsNegative ) {
                $flagsCurrent = $flagsNegative;

            } elseif ( $hasFlagsNonNegative ) {
                $flagsCurrent = $flags;
            }

        } else {
            if ( $hasFlagsNonNegative ) {
                $flagsCurrent = $flags;

            } elseif ( $hasFlagsNegative ) {
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

        foreach ( $flagGroups as $groupName => [$conflict, $default] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ( $flagsCurrent & $flag ) {
                    $cnt++;
                }
            }

            if ( $cnt > 1 ) {
                throw new LogicException(
                    [ 'The `flagsNonNegative` conflict in group: ' . $groupName, $flags ]
                );

            } elseif ( 0 === $cnt ) {
                $flagsCurrent |= $default;
            }
        }

        $isRoundAwayFromZero = false;
        $isRoundTowardZero = false;
        $isRoundToPositiveInf = false;
        $isRoundToNegativeInf = false;
        $isRoundEven = false;
        $isRoundOdd = false;
        if ( $flagsCurrent & _NUM_ROUND_AWAY_FROM_ZERO ) {
            $isRoundAwayFromZero = true;

        } elseif ( $flagsCurrent & _NUM_ROUND_TOWARD_ZERO ) {
            $isRoundTowardZero = true;

        } elseif ( $flagsCurrent & _NUM_ROUND_TO_POSITIVE_INF ) {
            $isRoundToPositiveInf = true;

        } elseif ( $flagsCurrent & _NUM_ROUND_TO_NEGATIVE_INF ) {
            $isRoundToNegativeInf = true;

        } elseif ( $flagsCurrent & _NUM_ROUND_EVEN ) {
            $isRoundEven = true;

        } elseif ( $flagsCurrent & _NUM_ROUND_ODD ) {
            $isRoundOdd = true;
        }

        $factor = ($scale > 0)
            ? ((int) pow(10, $scale))
            : 1;

        $scaledAbs = $numberValid->getValueAbsolute() * $factor;

        $scaledAbsNumber = $this->type_number([], $scaledAbs, false);

        $scaledAbsInt = intval($scaledAbsNumber->getValueAbsoluteInt());
        $scaledAbsFrac = $scaledAbsNumber->getFrac();

        $isMidpoint = isset($scaledAbsFrac[1]) && ('5' === $scaledAbsFrac[1]);

        if ( ! $isMidpoint ) {
            $scaledAbsFracLen = strlen($scaledAbsFrac);

            $isUp = null;
            for ( $i = 1; $i < $scaledAbsFracLen; $i++ ) {
                $digit = $scaledAbsFrac[$i];

                if ( '4' === $digit ) {
                    continue;

                } elseif ( $digit >= 5 ) {
                    $isUp = true;

                    break;

                } else {
                    $isUp = false;

                    break;
                }
            }

            if ( $isUp ) {
                $scaledAbs = $scaledAbsInt + 1;

            } else {
                $scaledAbs = $scaledAbsInt;
            }

        } else {
            if ( $isRoundAwayFromZero ) {
                $scaledAbs = $scaledAbsInt + 1;

            } elseif ( $isRoundTowardZero ) {
                $scaledAbs = $scaledAbsInt;

            } elseif ( $isRoundToPositiveInf ) {
                if ( $isNegative ) {
                    $scaledAbs = $scaledAbsInt;

                } else {
                    $scaledAbs = $scaledAbsInt + 1;
                }

            } elseif ( $isRoundToNegativeInf ) {
                if ( $isNegative ) {
                    $scaledAbs = $scaledAbsInt + 1;

                } else {
                    $scaledAbs = $scaledAbsInt;
                }

            } elseif ( $isRoundEven ) {
                $a = $scaledAbsInt;
                $b = (0 === ($a % 2)) ? $a : ($a - 1);
                $c = $b + 2;

                $scaledAbs = (abs($scaledAbs - $b) <= abs($c - $scaledAbs))
                    ? $b
                    : $c;

            } elseif ( $isRoundOdd ) {
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
     * > Денежное округление
     *
     * > Точка принятия решения - "наличие дробной части", если есть - округляем, если нет - обрезаем
     * > Участвует всё число
     * > Режим округления применяется к числу, у которого "есть дробная часть, даже минимальная"
     *
     * > moneyround(1.5, 1) -> 1.5
     * > moneyround(1.05, 1) -> 1.1
     * > moneyround(1.005, 1) -> 1.1
     * > moneyround(-1.005, 1) -> -1.1
     * > moneyround(-1.05, 1) -> -1.1
     * > moneyround(-1.5, 1) -> -1.5
     */
    public function moneyround(
        $number, ?int $scale = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : float
    {
        // $flags = $flags ?? _NUM_ROUND_AWAY_FROM_ZERO;
        // $flagsNegative = $flagsNegative ?? _NUM_ROUND_AWAY_FROM_ZERO;

        return $this->_moneyround(
            $number, $scale,
            $flags, $flagsNegative
        );
    }

    /**
     * > moneytrunc(1.5, 1) -> 1.5
     * > moneytrunc(1.05, 1) -> 1
     * > moneytrunc(1.005, 1) -> 1
     * > moneytrunc(-1.005, 1) -> -1
     * > moneytrunc(-1.05, 1) -> -1
     * > moneytrunc(-1.5, 1) -> -1.5
     */
    public function moneytrunc($number, ?int $scale = null) : float
    {
        return $this->_moneyround(
            $number, $scale,
            _NUM_ROUND_TOWARD_ZERO, _NUM_ROUND_TOWARD_ZERO
        );
    }

    /**
     * > moneyceil(1.5, 1) -> 1.5
     * > moneyceil(1.05, 1) -> 1.1
     * > moneyceil(1.005, 1) -> 1.1
     * > moneyceil(-1.005, 1) -> -1
     * > moneyceil(-1.05, 1) -> -1
     * > moneyceil(-1.5, 1) -> -1.5
     */
    public function moneyceil($number, ?int $scale = null) : float
    {
        return $this->_moneyround(
            $number, $scale,
            _NUM_ROUND_TO_POSITIVE_INF, _NUM_ROUND_TO_POSITIVE_INF
        );
    }

    /**
     * > moneyfloor(1.5, 1) -> 1.5
     * > moneyfloor(1.05, 1) -> 1
     * > moneyfloor(1.005, 1) -> 1
     * > moneyfloor(-1.005, 1) -> -1.1
     * > moneyfloor(-1.05, 1) -> -1.1
     * > moneyfloor(-1.5, 1) -> -1.5
     */
    public function moneyfloor($number, ?int $scale = null) : float
    {
        return $this->_moneyround(
            $number, $scale,
            _NUM_ROUND_TO_NEGATIVE_INF, _NUM_ROUND_TO_NEGATIVE_INF
        );
    }

    protected function _moneyround(
        $number, ?int $scale = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : float
    {
        $scale = $scale ?? 0;

        if ( $scale < 0 ) {
            throw new LogicException(
                [ 'The `precision` should be a non-negative integer', $scale ]
            );
        }

        $numberValid = $this->type_number([], $number, false);

        if ( $numberValid->isZero() ) {
            return 0.0;
        }

        $isNegative = $numberValid->isNegative();

        $hasFlagsNonNegative = (null !== $flags);
        $hasFlagsNegative = (null !== $flagsNegative);

        $flagsCurrent = 0;
        if ( $isNegative ) {
            if ( $hasFlagsNegative ) {
                $flagsCurrent = $flagsNegative;

            } elseif ( $hasFlagsNonNegative ) {
                $flagsCurrent = $flags;
            }

        } else {
            if ( $hasFlagsNonNegative ) {
                $flagsCurrent = $flags;

            } elseif ( $hasFlagsNegative ) {
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

        foreach ( $flagGroups as $groupName => [$conflict, $default] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ( $flagsCurrent & $flag ) {
                    $cnt++;
                }
            }

            if ( $cnt > 1 ) {
                throw new LogicException(
                    [ 'The `flags` conflict in group: ' . $groupName, $flags ]
                );

            } elseif ( 0 === $cnt ) {
                $flagsCurrent |= $default;
            }
        }

        $isRoundAwayFromZero = false;
        $isRoundTowardZero = false;
        $isRoundToPositiveInf = false;
        $isRoundToNegativeInf = false;
        $isRoundEven = false;
        $isRoundOdd = false;
        if ( $flagsCurrent & _NUM_ROUND_AWAY_FROM_ZERO ) {
            $isRoundAwayFromZero = true;

        } elseif ( $flagsCurrent & _NUM_ROUND_TOWARD_ZERO ) {
            $isRoundTowardZero = true;

        } elseif ( $flagsCurrent & _NUM_ROUND_TO_POSITIVE_INF ) {
            $isRoundToPositiveInf = true;

        } elseif ( $flagsCurrent & _NUM_ROUND_TO_NEGATIVE_INF ) {
            $isRoundToNegativeInf = true;

        } elseif ( $flagsCurrent & _NUM_ROUND_EVEN ) {
            $isRoundEven = true;

        } elseif ( $flagsCurrent & _NUM_ROUND_ODD ) {
            $isRoundOdd = true;
        }

        $factor = ($scale > 0)
            ? ((int) pow(10, $scale))
            : 1;

        $scaledAbs = $numberValid->getValueAbsolute() * $factor;

        $scaledAbsNumber = $this->type_number([], $scaledAbs, false);

        $scaledAbsInt = intval($scaledAbsNumber->getValueAbsoluteInt());
        $scaledAbsFrac = $scaledAbsNumber->getFrac();

        if ( '' === $scaledAbsFrac ) {
            $scaledAbs = $scaledAbsInt;

        } else {
            if ( $isRoundAwayFromZero ) {
                $scaledAbs = $scaledAbsInt + 1;

            } elseif ( $isRoundTowardZero ) {
                $scaledAbs = $scaledAbsInt;

            } elseif ( $isRoundToPositiveInf ) {
                if ( $isNegative ) {
                    $scaledAbs = $scaledAbsInt;

                } else {
                    $scaledAbs = $scaledAbsInt + 1;
                }

            } elseif ( $isRoundToNegativeInf ) {
                if ( $isNegative ) {
                    $scaledAbs = $scaledAbsInt + 1;

                } else {
                    $scaledAbs = $scaledAbsInt;
                }

            } elseif ( $isRoundEven ) {
                $a = $scaledAbsInt;
                $b = ($a % 2 === 0) ? $a : ($a - 1);
                $c = $b + 2;

                $scaledAbs = (abs($scaledAbs - $b) <= abs($c - $scaledAbs))
                    ? $b
                    : $c;

            } elseif ( $isRoundOdd ) {
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
     * @return int|float|false
     */
    protected function castNumericToNum(string $numeric, string $sign, string $int, string $frac, string $exp)
    {
        if ( '0' === $numeric ) {
            return 0;
        }

        $hasExponent = ('' !== $exp);

        $valueFloat = (float) $numeric;

        if ( ! is_finite($valueFloat) ) {
            if ( ! $hasExponent ) {
                return false;
            }

            $fracDig = '';
            if ( '' !== $frac ) {
                $fracDig = substr($frac, 1, PHP_FLOAT_DIG - 1);
                $fracDig = str_pad($fracDig, PHP_FLOAT_DIG, '0', STR_PAD_RIGHT);
                $fracDig = '.' . $fracDig;
            }

            $numericDig = "{$sign}{$int}{$fracDig}{$exp}";

            $valueFloat = (float) sprintf('%.' . PHP_FLOAT_DIG . 'e', $numericDig);

            if ( ! is_finite($valueFloat) ) {
                return false;
            }
        }

        if ( 0.0 === $valueFloat ) {
            return false;
        }

        $valueFloatAbs = abs($valueFloat);

        if ( $valueFloatAbs > 0.0 ) {
            if ( $valueFloatAbs >= _NUM_PHP_FLOAT_MAX_FLOAT_DIG ) {
                return $valueFloat > 0
                    ? _NUM_PHP_FLOAT_MAX_FLOAT_DIG
                    : -_NUM_PHP_FLOAT_MAX_FLOAT_DIG;
            }

            // // > практическая польза почти нулевая, но для проверки дополнительный вызов и куча работы со строками
            //
            // $valueFloatMin = $this->castNumericToFloatMin($valueAbs);
            // if ( false !== $valueFloatMin ) {
            //     return Ret::ok(
            //         $fb,
            //         ($value > 0)
            //             ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //             : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //     );
            // }
            //
            // // < практическая польза почти нулевая, но для проверки дополнительный вызов и куча работы со строками
        }

        if ( ! ((_NUM_PHP_INT_MIN_FLOAT <= $valueFloat) && ($valueFloat <= _NUM_PHP_INT_MAX_FLOAT)) ) {
            return $valueFloat;
        }

        $valueInt = (int) $numeric;

        if ( $valueFloat === ((float) $valueInt) ) {
            return $valueInt;
        }

        return $valueFloat;
    }

    /**
     * @return int|float|false
     */
    protected function castNumericToInt(string $numeric, string $sign, string $int, string $frac, string $exp)
    {
        if ( '0' === $numeric ) {
            return 0;
        }

        $hasFrac = ('' !== $frac);
        if ( $hasFrac ) {
            return false;
        }

        $hasExponent = ('' !== $exp);
        if ( $hasExponent ) {
            return false;
        }

        $valueFloat = (float) $numeric;

        if ( ! ((_NUM_PHP_INT_MIN_FLOAT <= $valueFloat) && ($valueFloat <= _NUM_PHP_INT_MAX_FLOAT)) ) {
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
        if ( '0' === $numeric ) {
            return 0.0;
        }

        $hasExponent = ('' !== $exp);

        $valueFloat = (float) $numeric;

        if ( ! is_finite($valueFloat) ) {
            if ( ! $hasExponent ) {
                return false;
            }

            $fracDig = '';
            if ( '' !== $frac ) {
                $fracDig = substr($frac, 1, PHP_FLOAT_DIG - 1);
                $fracDig = str_pad($fracDig, PHP_FLOAT_DIG, '0', STR_PAD_RIGHT);
                $fracDig = '.' . $fracDig;
            }

            $numericDig = "{$sign}{$int}{$fracDig}{$exp}";

            $valueFloat = (float) sprintf('%.' . PHP_FLOAT_DIG . 'e', $numericDig);

            if ( ! is_finite($valueFloat) ) {
                return false;
            }
        }

        if ( 0.0 === $valueFloat ) {
            return false;
        }

        $valueFloatAbs = abs($valueFloat);

        if ( $valueFloatAbs > 0.0 ) {
            if ( $valueFloatAbs >= _NUM_PHP_FLOAT_MAX_FLOAT_DIG ) {
                return $valueFloat > 0
                    ? _NUM_PHP_FLOAT_MAX_FLOAT_DIG
                    : -_NUM_PHP_FLOAT_MAX_FLOAT_DIG;
            }

            // // > практическая польза почти нулевая, но для проверки дополнительный вызов и куча работы со строками
            //
            // $valueFloatMin = $this->castNumericToFloatMin($valueAbs);
            // if ( false !== $valueFloatMin ) {
            //     return Ret::ok(
            //         $fb,
            //         ($value > 0)
            //             ? _NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //             : -_NUM_PHP_FLOAT_MIN_FLOAT_DIG
            //     );
            // }
            //
            // // < практическая польза почти нулевая, но для проверки дополнительный вызов и куча работы со строками
        }

        return $valueFloat;
    }

    // /**
    //  * > практическая польза почти нулевая, но для проверки дополнительный вызов и куча работы со строками
    //  *
    //  * @return float|false
    //  */
    // protected function castNumericToFloatMin($value, $split = null)
    // {
    //     if ( null === $split ) {
    //         $ret = $this->type_numeric(null, $value, true, [ &$split ]);
    //
    //         if ( ! $ret->isOk() ) {
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
    //     if ( $numericAbsDig === _NUM_PHP_FLOAT_MIN_STRING_DIG ) {
    //         return ('' === $sign)
    //             ? _NUM_PHP_FLOAT_MIN_STRING_DIG
    //             : -_NUM_PHP_FLOAT_MIN_STRING_DIG;
    //     }
    //
    //     return false;
    // }
}
