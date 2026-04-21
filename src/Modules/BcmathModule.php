<?php

/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Bcmath\Number;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @method bcadd(...$args)
 * @method bccomp(...$args)
 * @method bcdiv(...$args)
 * @method bcmod(...$args)
 * @method bcmul(...$args)
 * @method bcpow(...$args)
 * @method bcscale(...$args)
 * @method bcsub(...$args)
 */
class BcmathModule
{
    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        $theType = Lib::type();

        $theType->is_extension_loaded('bcmath')->orThrow();

        return $this;
    }


    public function __call($name, $arguments)
    {
        if ( 'bc' === substr($name, 0, 2) ) {
            if ( function_exists($name) ) {
                return call_user_func_array($name, $arguments);
            }
        }

        throw new RuntimeException([ 'Method not found: ' . $name ]);
    }


    /**
     * @return Ret<Bcnumber>|Bcnumber
     */
    public function type_bcnumber($fb, $value)
    {
        if ( $value instanceof Bcnumber ) {
            return Ret::ok($fb, $value);
        }

        $theType = Lib::type();

        $ret = $theType->numeric($value, false, [ &$split ]);

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

        $ret = Bcnumber::fromValidArray([
            'original' => $value,
            'sign'     => $split[0],
            'int'      => $split[1],
            'frac'     => $split[2],
            'scale'    => $scale,
        ]);

        if ( ! $ret->isOk([ &$bcnumber ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $bcnumber);
    }


    /**
     * @param (int|float|string|Number|Bcnumber)[] $scales
     * @param (int|float|string|Number|Bcnumber)[] $numbers
     *
     * @return int[]
     */
    public function scales(array $scales = [], array $numbers = []) : array
    {
        $theType = Lib::type();

        $scaleIntList = [];

        foreach ( $scales as $scale ) {
            if ( null === $scale ) {
                $scaleValid = 0;

            } else {
                $scaleValid = $theType->scale($scale)->orThrow();
            }

            $scaleIntList[] = $scaleValid;
        }

        foreach ( $numbers as $number ) {
            if ( null === $number ) {
                $scaleIntList[] = 0;

            } else {
                $bcnumber = $theType->bcnumber($number)->orThrow();

                $scaleIntList[] = $bcnumber->getScale();
            }
        }

        return $scaleIntList;
    }


    public function scale_min(array $scales = [], array $numbers = []) : ?int
    {
        $theNum = Lib::num();

        $scaleLimit = $theNum::staticScaleLimit();

        $scaleIntList = $this->scales($scales, $numbers);

        if ( [] === $scaleIntList ) {
            return null;

        } else {
            $scaleMin = min($scaleIntList);

            if ( $scaleMin > $scaleLimit ) {
                $scaleMin = null;
            }
        }

        return $scaleMin;
    }

    public function scale_max(array $scales = [], array $numbers = []) : ?int
    {
        $theNum = Lib::num();

        $scaleLimit = $theNum::staticScaleLimit();

        $scaleIntList = $this->scales($scales, $numbers);

        if ( [] === $scaleIntList ) {
            $scaleMax = null;

        } else {
            $scaleMax = max($scaleIntList);

            if ( $scaleMax > $scaleLimit ) {
                $scaleMax = null;
            }
        }

        return $scaleMax;
    }


    public function bc_comp($num1, $num2, ?int $scale = null) : int
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $scaleMax = $this->scale_max([], [ $bcnum1, $bcnum2 ]);

        $scaleValid = null
            ?? $theType->scale($scale)->orNull()
            ?? $theType->scale($scaleMax)->orNull()
            ?? $theType->scale(bcscale())->orNull();

        $result = bccomp(
            $bcnum1,
            $bcnum2,
            $scaleValid
        );

        return $result;
    }


    public function bc_abs($number) : Bcnumber
    {
        $theType = Lib::type();

        $bcnumber = $theType->bcnumber($number)->orThrow();

        $result = $bcnumber->getValueAbsolute();

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    public function bc_ceil($number) : Bcnumber
    {
        $theType = Lib::type();

        $bcnumber = $theType->bcnumber($number)->orThrow();

        $hasNonZeroFrac = false;
        if ( $bcnumber->hasFrac($frac) ) {
            $hasNonZeroFrac = ('' !== ltrim($frac, '.0'));
        }

        $result = $bcnumber->getValueInt();

        if ( $bcnumber->isNegative() ) {
            if ( $hasNonZeroFrac ) {
                $result = bcadd($result, '0', 0);
            }

        } else {
            if ( $hasNonZeroFrac ) {
                $result = bcadd($result, '1', 0);
            }
        }

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    public function bc_floor($number) : Bcnumber
    {
        $theType = Lib::type();

        $bcnumber = $theType->bcnumber($number)->orThrow();

        $hasNonZeroFrac = false;
        if ( $bcnumber->hasFrac($frac) ) {
            $hasNonZeroFrac = ('' !== ltrim($frac, '.0'));
        }

        $result = $bcnumber->getValueInt();

        if ( $bcnumber->isNegative() ) {
            if ( $hasNonZeroFrac ) {
                $result = bcsub($result, '1', 0);
            }

        } else {
            if ( $hasNonZeroFrac ) {
                $result = bcadd($result, '0', 0);
            }
        }

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }


    /**
     * > Математическое округление
     *
     * > Точка принятия решения - "дробная часть равна .5/.05/.005 и тд"
     * > Участвует только 1 разряд свыше указанного, как в математике (если число 1.005, а округляем до 1 знака, то 5 не участвует в решении, число будет 1.00)
     * > Середина определяется по первому не-нулевому разряду (для 1.005 при округлении до 2 знаков решение будет приниматься по третьему знаку 5)
     * > К середине применяется режим округления, выше середины - правило всегда "от нуля", ниже середины - правило "к нулю"
     *
     * > bc_mathround(1.5) -> 2
     * > bc_mathround(1.05) -> 1
     * > bc_mathround(1.005) -> 1
     * > bc_mathround(-1.005) -> -1
     * > bc_mathround(-1.05) -> -1
     * > bc_mathround(-1.5) -> -2
     */
    public function bc_mathround(
        $number, ?int $scale = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : Bcnumber
    {
        // $flags = $flags ?? _NUM_ROUND_AWAY_FROM_ZERO;
        // $flagsNegative = $flagsNegative ?? _NUM_ROUND_AWAY_FROM_ZERO;

        return $this->_bc_mathround(
            $number, $scale,
            $flags, $flagsNegative
        );
    }

    /**
     * > bc_mathround_even(2.5) -> 2
     * > bc_mathround_even(1.05) -> 1
     * > bc_mathround_even(1.005) -> 1
     * > bc_mathround_even(-1.005) -> -1
     * > bc_mathround_even(-1.05) -> -1
     * > bc_mathround_even(-2.5) -> -2
     */
    public function bc_mathround_even($number, ?int $scale = null) : Bcnumber
    {
        return $this->_bc_mathround(
            $number, $scale,
            _NUM_ROUND_EVEN, _NUM_ROUND_EVEN
        );
    }

    /**
     * > bc_mathround_odd(2.5) -> 3
     * > bc_mathround_odd(1.05) -> 1
     * > bc_mathround_odd(1.005) -> 1
     * > bc_mathround_odd(-1.005) -> -1
     * > bc_mathround_odd(-1.05) -> -1
     * > bc_mathround_odd(-2.5) -> -3
     */
    public function bc_mathround_odd($number, ?int $scale = null) : Bcnumber
    {
        return $this->_bc_mathround(
            $number, $scale,
            _NUM_ROUND_ODD, _NUM_ROUND_ODD
        );
    }

    protected function _bc_mathround(
        $number, ?int $scale = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : Bcnumber
    {
        $scale = $scale ?? 0;

        $theType = Lib::type();

        $bcnumber = $theType->bcnumber($number)->orThrow();
        $scaleInt = $theType->scale($scale)->orThrow();

        if ( $bcnumber->isZero() ) {
            return clone $bcnumber;
        }

        $scaleMax = $scaleInt;

        $isNegative = $bcnumber->isNegative();

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

        $factor = ($scaleMax > 0)
            ? ((string) pow(10, $scaleMax))
            : '1';

        $refBcScaledAbs = $this->bc_mul($bcnumber->getValueAbsolute(), $factor);

        $scaledAbsInt = intval($refBcScaledAbs->getValueAbsoluteInt());
        $scaledAbsFrac = $refBcScaledAbs->getFrac();

        $isMidpoint = isset($scaledAbsFrac[1]) && ('5' === $scaledAbsFrac[1]);

        if ( ! $isMidpoint ) {
            $scaledAbsFracLen = strlen($scaledAbsFrac);

            $isUp = false;
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

                $diff1 = $this->bc_abs($this->bc_sub($refBcScaledAbs, $b));
                $diff2 = $this->bc_abs($this->bc_sub($c, $refBcScaledAbs));

                $isLessThanOrEqual = ($this->bc_comp($diff1, $diff2) <= 0);
                $scaledAbs = $isLessThanOrEqual ? $b : $c;

            } elseif ( $isRoundOdd ) {
                $a = $scaledAbsInt;
                $b = ($a % 2) ? $a : ($a - 1);
                $c = $b + 2;

                $diff1 = $this->bc_abs($this->bc_sub($refBcScaledAbs, $b));
                $diff2 = $this->bc_abs($this->bc_sub($c, $refBcScaledAbs));

                $isLessThanOrEqual = ($this->bc_comp($diff1, $diff2) <= 0);
                $scaledAbs = $isLessThanOrEqual ? $b : $c;

            } else {
                throw new RuntimeException(
                    [ 'The `round` mode is unknown', $flags ]
                );
            }
        }

        $result = $isNegative ? "-{$scaledAbs}" : $scaledAbs;

        $bcresult = $this->bc_div($result, $factor, $scaleMax);

        return $bcresult;
    }


    /**
     * > Денежное округление
     *
     * > Точка принятия решения - "наличие дробной части", если есть - округляем, если нет - обрезаем
     * > Участвует всё число
     * > Режим округления применяется к числу, у которого "есть дробная часть, даже минимальная"
     *
     * > bc_moneyround(1.5, 1) -> 1.5
     * > bc_moneyround(1.05, 1) -> 1.1
     * > bc_moneyround(1.005, 1) -> 1.1
     * > bc_moneyround(-1.005, 1) -> -1.1
     * > bc_moneyround(-1.05, 1) -> -1.1
     * > bc_moneyround(-1.5, 1) -> -1.5
     */
    public function bc_moneyround(
        $number, ?int $scale = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : Bcnumber
    {
        // $flags = $flags ?? _NUM_ROUND_AWAY_FROM_ZERO;
        // $flagsNegative = $flagsNegative ?? _NUM_ROUND_AWAY_FROM_ZERO;

        return $this->_bc_moneyround(
            $number, $scale,
            $flags, $flagsNegative
        );
    }

    /**
     * > bc_moneytrunc(1.5, 1) -> 1.5
     * > bc_moneytrunc(1.05, 1) -> 1
     * > bc_moneytrunc(1.005, 1) -> 1
     * > bc_moneytrunc(-1.005, 1) -> -1
     * > bc_moneytrunc(-1.05, 1) -> -1
     * > bc_moneytrunc(-1.5, 1) -> -1.5
     */
    public function bc_moneytrunc($number, ?int $scale = null) : Bcnumber
    {
        return $this->_bc_moneyround(
            $number, $scale,
            _NUM_ROUND_TOWARD_ZERO, _NUM_ROUND_TOWARD_ZERO
        );
    }

    /**
     * > bc_moneyceil(1.5, 1) -> 1.5
     * > bc_moneyceil(1.05, 1) -> 1.1
     * > bc_moneyceil(1.005, 1) -> 1.1
     * > bc_moneyceil(-1.005, 1) -> -1
     * > bc_moneyceil(-1.05, 1) -> -1
     * > bc_moneyceil(-1.5, 1) -> -1.5
     */
    public function bc_moneyceil($number, ?int $scale = null) : Bcnumber
    {
        return $this->_bc_moneyround(
            $number, $scale,
            _NUM_ROUND_TO_POSITIVE_INF, _NUM_ROUND_TO_POSITIVE_INF
        );
    }

    /**
     * > bc_moneyfloor(1.5, 1) -> 1.5
     * > bc_moneyfloor(1.05, 1) -> 1
     * > bc_moneyfloor(1.005, 1) -> 1
     * > bc_moneyfloor(-1.005, 1) -> -1.1
     * > bc_moneyfloor(-1.05, 1) -> -1.1
     * > bc_moneyfloor(-1.5, 1) -> -1.5
     */
    public function bc_moneyfloor($number, ?int $scale = null) : Bcnumber
    {
        return $this->_bc_moneyround(
            $number, $scale,
            _NUM_ROUND_TO_NEGATIVE_INF, _NUM_ROUND_TO_NEGATIVE_INF
        );
    }

    protected function _bc_moneyround(
        $number, ?int $scale = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : Bcnumber
    {
        $scale = $scale ?? 0;

        $theType = Lib::type();

        $bcnumber = $theType->bcnumber($number)->orThrow();
        $scaleInt = $theType->scale($scale)->orThrow();

        if ( $bcnumber->isZero() ) {
            return clone $bcnumber;
        }

        $scaleMax = $scaleInt;

        $isNegative = $bcnumber->isNegative();

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

        $factor = ($scaleMax > 0)
            ? ((string) pow(10, $scaleMax))
            : '1';

        $refBcScaledAbs = $this->bc_mul($bcnumber->getValueAbsolute(), $factor);

        $scaledAbsInt = intval($refBcScaledAbs->getValueAbsoluteInt());
        $scaledAbsFrac = $refBcScaledAbs->getFrac();

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
                $b = (0 === ($a % 2)) ? $a : ($a - 1);
                $c = $b + 2;

                $diff1 = $this->bc_abs($this->bc_sub($refBcScaledAbs, $b));
                $diff2 = $this->bc_abs($this->bc_sub($c, $refBcScaledAbs));

                $isLessThanOrEqual = ($this->bc_comp($diff1, $diff2) <= 0);
                $scaledAbs = $isLessThanOrEqual ? $b : $c;

            } elseif ( $isRoundOdd ) {
                $a = $scaledAbsInt;
                $b = ($a % 2) ? $a : ($a - 1);
                $c = $b + 2;

                $diff1 = $this->bc_abs($this->bc_sub($refBcScaledAbs, $b));
                $diff2 = $this->bc_abs($this->bc_sub($c, $refBcScaledAbs));

                $isLessThanOrEqual = ($this->bc_comp($diff1, $diff2) <= 0);
                $scaledAbs = $isLessThanOrEqual ? $b : $c;

            } else {
                throw new RuntimeException(
                    [ 'The `round` mode is unknown', $flags ]
                );
            }
        }

        $result = $isNegative ? "-{$scaledAbs}" : $scaledAbs;

        $bcresult = $this->bc_div($result, $factor, $scaleMax);

        return $bcresult;
    }


    /**
     * > поскольку при сложении число дробных знаков может увелится до наибольшего числа знаков в одном из чисел, параметр $scale желателен
     */
    public function bc_add($num1, $num2, ?int $scale = null, array $refs = []) : Bcnumber
    {
        $withRefFrac = array_key_exists(0, $refs);
        if ( $withRefFrac ) {
            $refFrac =& $refs[0];
        }
        $refFrac = null;

        $theType = Lib::type();

        $hasScale = (null !== $scale);

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $scaleInt = $theType->scale($scale)->orNull();

        $scaleMax = null;
        if ( $withRefFrac || (! $hasScale) ) {
            $scaleMax = $this->scale_max([], [ $bcnum1, $bcnum2 ]);

            $scaleMax = $theType->scale($scaleMax)->orNull();
        }

        $scaleValid = null
            ?? $scaleInt
            ?? $scaleMax
            ?? $theType->scale(bcscale())->orNull();

        $result = bcadd(
            $bcnum1,
            $bcnum2,
            $scaleValid
        );

        if ( $withRefFrac ) {
            $theNum = Lib::num();

            $scaleFrac = $theNum::staticScaleFrac();
            $scaleLimit = $theNum::staticScaleLimit();

            $scaleFullValid = null
                ?? $scaleMax
                ?? ((null !== $scaleInt) ? $theType->scale($scaleInt + $scaleFrac)->orNull() : null)
                ?? $scaleLimit;

            $resultFull = bcadd(
                $bcnum1,
                $bcnum2,
                $scaleFullValid
            );

            $refFrac = bcsub($resultFull, $result, $scaleFullValid);
        }

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    /**
     * > поскольку при вычитании число дробных знаков может увелится до наибольшего числа знаков в одном из чисел, параметр $scale желателен
     */
    public function bc_sub($num1, $num2, ?int $scale = null, array $refs = []) : Bcnumber
    {
        $withRefFrac = array_key_exists(0, $refs);
        if ( $withRefFrac ) {
            $refFrac =& $refs[0];
        }
        $refFrac = null;

        $theType = Lib::type();

        $hasScale = (null !== $scale);

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $scaleInt = $theType->scale($scale)->orNull();

        $scaleMax = null;
        if ( $withRefFrac || (! $hasScale) ) {
            $scaleMax = $this->scale_max([], [ $bcnum1, $bcnum2 ]);

            $scaleMax = $theType->scale($scaleMax)->orNull();
        }

        $scaleValid = null
            ?? $scaleInt
            ?? $scaleMax
            ?? $theType->scale(bcscale())->orNull();

        $result = bcsub(
            $bcnum1,
            $bcnum2,
            $scaleValid
        );

        if ( $withRefFrac ) {
            $theNum = Lib::num();

            $scaleFrac = $theNum::staticScaleFrac();
            $scaleLimit = $theNum::staticScaleLimit();

            $scaleFullValid = null
                ?? $scaleMax
                ?? ((null !== $scaleInt) ? $theType->scale($scaleInt + $scaleFrac)->orNull() : null)
                ?? $scaleLimit;

            $resultFull = bcsub(
                $bcnum1,
                $bcnum2,
                $scaleFullValid
            );

            $refFrac = bcsub($resultFull, $result, $scaleFullValid);
        }

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    /**
     * > поскольку при умножении число дробных знаков может увелится до суммы знаков обоих чисел, параметр $scale желателен
     */
    public function bc_mul($num1, $num2, ?int $scale = null, array $refs = []) : Bcnumber
    {
        $withRefFrac = array_key_exists(0, $refs);
        if ( $withRefFrac ) {
            $refFrac =& $refs[0];
        }
        $refFrac = null;

        $theType = Lib::type();

        $hasScale = (null !== $scale);

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        if ( ! $hasScale ) {
            if ( $bcnum1->getFrac() && $bcnum2->getFrac() ) {
                throw new LogicException(
                    [ 'The `scale` should be passed if both arguments have fractional parts', $num1, $num2 ]
                );
            }
        }

        $scaleInt = $theType->scale($scale)->orNull();

        $scaleMax = null;
        if ( $withRefFrac || (! $hasScale) ) {
            $scaleMax = array_sum(
                $this->scales([], [ $bcnum1, $bcnum2 ])
            );

            $scaleMax = $theType->scale($scaleMax)->orNull();
        }

        $scaleValid = null
            ?? $scaleInt
            ?? $scaleMax
            ?? $theType->scale(bcscale())->orNull();

        $result = bcmul(
            $bcnum1,
            $bcnum2,
            $scaleValid
        );

        if ( $withRefFrac ) {
            $theNum = Lib::num();

            $scaleFrac = $theNum::staticScaleFrac();
            $scaleLimit = $theNum::staticScaleLimit();

            $scaleFullValid = null
                ?? $scaleMax
                ?? ((null !== $scaleInt) ? $theType->scale($scaleInt + $scaleFrac)->orNull() : null)
                ?? $scaleLimit;

            $resultFull = bcmul(
                $bcnum1,
                $bcnum2,
                $scaleFullValid
            );

            $refFrac = bcsub($resultFull, $result, $scaleFullValid);
        }

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    /**
     * > поскольку при делении число дробных знаков может увелится, параметр $scale сделан обязательным
     */
    public function bc_div($num1, $num2, int $scale, array $refs = []) : Bcnumber
    {
        $withRefFrac = array_key_exists(0, $refs);
        if ( $withRefFrac ) {
            $refFrac =& $refs[0];
        }
        $refFrac = null;

        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $scaleInt = $theType->scale($scale)->orThrow();

        $scaleValid = $scaleInt;

        $result = bcdiv(
            $bcnum1,
            $bcnum2,
            $scaleValid
        );

        if ( $withRefFrac ) {
            $theNum = Lib::num();

            $scaleFrac = $theNum::staticScaleFrac();
            $scaleLimit = $theNum::staticScaleLimit();

            $scaleFullValid = null
                ?? $theType->scale($scaleInt + $scaleFrac)->orNull()
                ?? $scaleLimit;

            $resultFull = bcdiv(
                $bcnum1,
                $bcnum2,
                $scaleFullValid
            );

            $refFrac = bcsub($resultFull, $result, $scaleFullValid);
        }

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }


    public function bc_pow($num, int $exponent, ?int $scale = null, array $refs = []) : Bcnumber
    {
        $withRefFrac = array_key_exists(0, $refs);
        if ( $withRefFrac ) {
            $refFrac =& $refs[0];
        }
        $refFrac = null;

        $theType = Lib::type();

        $hasScale = (null !== $scale);

        $bcnum = $theType->bcnumber($num)->orThrow();
        $exponentInt = $theType->exponent($exponent)->orThrow();

        if ( ! $hasScale ) {
            if ( $bcnum->hasFrac() ) {
                throw new LogicException(
                    [ 'The `scale` should be passed if `num` has fractional part', $num ]
                );
            }
        }

        $scaleInt = $theType->scale($scale)->orNull();

        $scaleMax = null;
        if ( $withRefFrac || (! $hasScale) ) {
            if ( $exponent >= 0 ) {
                $scaleMax = $this->scales([], [ $bcnum ])[0];
                $scaleMax = $scaleMax * $exponentInt;
                $scaleMax = $theType->scale($scaleMax)->orNull();
            }
        }

        $scaleValid = null
            ?? $scaleInt
            ?? $scaleMax
            ?? $theType->scale(bcscale())->orNull();

        $result = bcpow(
            $bcnum,
            $exponentInt,
            $scaleValid
        );

        if ( $withRefFrac ) {
            $theNum = Lib::num();

            $scaleFrac = $theNum::staticScaleFrac();
            $scaleLimit = $theNum::staticScaleLimit();

            $scaleFullValid = null
                ?? $scaleMax
                ?? ((null !== $scaleInt) ? $theType->scale($scaleInt + $scaleFrac)->orNull() : null)
                ?? $scaleLimit;

            $resultFull = bcpow(
                $bcnum,
                $exponentInt,
                $scaleFullValid
            );

            $refFrac = bcsub($resultFull, $result, $scaleFullValid);
        }

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    public function bc_sqrt($num, int $scale, array $refs = []) : Bcnumber
    {
        $withRefFrac = array_key_exists(0, $refs);
        if ( $withRefFrac ) {
            $refFrac =& $refs[0];
        }
        $refFrac = null;

        $theType = Lib::type();

        $bcnum = $theType->bcnumber($num)->orThrow();
        $scaleInt = $theType->scale($scale)->orThrow();

        $scaleValid = $scaleInt;

        $result = bcsqrt(
            $bcnum,
            $scaleValid
        );

        if ( $withRefFrac ) {
            $theNum = Lib::num();

            $scaleFrac = $theNum::staticScaleFrac();
            $scaleLimit = $theNum::staticScaleLimit();

            $scaleFullValid = null
                ?? $theType->scale($scaleInt + $scaleFrac)->orNull()
                ?? $scaleLimit;

            $resultFull = bcsqrt(
                $bcnum,
                $scaleFullValid
            );

            $refFrac = bcsub($resultFull, $result, $scaleFullValid);
        }

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }


    public function bc_mod($num1, $num2) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $result = bcmod(
            $bcnum1->getValueInt(),
            $bcnum2->getValueInt(),
            0
        );

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    public function bc_fmod($num1, $num2, int $scale) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();
        $scaleInt = $theType->scale($scale)->orThrow();

        $scaleMax = $scaleInt;

        $result = bcdiv(
            $bcnum1,
            $bcnum2,
            $scaleMax
        );

        $result = (bccomp($result, '0', $scaleMax) >= 0)
            ? bcadd($result, '0', 0)
            : bcsub($result, '0', 0);

        $result = bcmul($result, $bcnum2, $scaleMax);

        $result = bcsub($bcnum1, $result, $scaleInt);

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }


    /**
     * > Greatest Common Divisor
     */
    public function bc_gcd($num1, $num2) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $abs1 = $bcnum1->getValueAbsolute();
        $abs2 = $bcnum2->getValueAbsolute();

        while ( '0' !== $abs2 ) {
            $mod = bcmod($abs1, $abs2, 0);

            [ $abs2, $abs1 ] = [ $mod, $abs2 ];
        }

        $result = $abs1;

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    /**
     * > Lowest Common Multiplier
     */
    public function bc_lcm($num1, $num2) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $abs1 = $bcnum1->getValueAbsolute();
        $abs2 = $bcnum2->getValueAbsolute();

        $result = bcmul($abs1, $abs2, 0);

        $bcGcd = $this->bc_gcd($abs1, $abs2);

        $absGcd = $bcGcd->getValueAbsolute();

        $result = bcdiv($result, $absGcd, 0);

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }
}
