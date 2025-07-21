<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Bcmath\Number;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class BcmathModule
{
    /**
     * @var int
     */
    protected $scaleLimit = 16;


    public function __construct()
    {
        if (! extension_loaded('bcmath')) {
            throw new ExtensionException(
                'Missing PHP extension: bcmath'
            );
        }
    }


    public function static_scale_limit(?int $scaleLimit = null) : int
    {
        if (null !== $scaleLimit) {
            if ($scaleLimit < 0) {
                throw new LogicException(
                    [ 'The `scaleLimit` should be a non-negative integer', $scaleLimit ]
                );
            }

            $last = $this->scaleLimit;

            $this->scaleLimit = $scaleLimit;

            $result = $last;
        }

        $result = $result ?? $this->scaleLimit ?? 16;

        return $result;
    }


    /**
     * @return Ret<Bcnumber>
     */
    public function type_bcnumber($value)
    {
        if ($value instanceof Bcnumber) {
            return Ret::ok($value);
        }

        $theType = Lib::type();

        if (! $theType
            ->numeric($value, false, [ &$split ])
            ->isOk([ 1 => &$ret ])
        ) {
            return $ret;
        }

        $frac = $split[ 2 ];

        $scale = 0;
        if ('' !== $frac) {
            $scale = strlen($frac) - 1;
        }

        $bcnumber = Bcnumber::fromValidArray([
            'original' => $value,
            'sign'     => $split[ 0 ],
            'int'      => $split[ 1 ],
            'frac'     => $split[ 2 ],
            'scale'    => $scale,
        ])->orThrow();

        return Ret::ok($bcnumber);
    }


    /**
     * @param (int|float|string|Number|Bcnumber)[] ...$numbers
     *
     * @return int[]
     */
    public function scales(...$numbers) : array
    {
        $theType = Lib::type();

        $scaleList = [];

        foreach ( $numbers as $number ) {
            $bcnumber = $theType->bcnumber($number)->orThrow();

            $scaleList[] = $bcnumber->getScale();
        }

        return $scaleList;
    }


    public function scale_min(?int $scale = null, ...$numbers) : ?int
    {
        $theType = Lib::type();

        $scaleIntList = [];

        $scaleLimit = $this->static_scale_limit();

        if (null !== $scale) {
            $scaleInt = $theType->int_non_negative($scale)->orThrow();

            $scaleIntList[] = $scaleInt;
        }

        if ([] !== $numbers) {
            $scaleIntList = array_merge(
                $scaleIntList,
                $this->scales(...$numbers)
            );
        }

        if ([] === $scaleIntList) {
            return null;
        }

        $scaleMin = min($scaleIntList);

        if ($scaleMin > $scaleLimit) {
            throw new RuntimeException(
                [
                    'The result `scaleMin` is bigger than allowed maximum',
                    $scaleMin,
                    $scaleLimit,
                ]
            );
        }

        return $scaleMin;
    }

    public function scale_max(?int $scale = null, ...$numbers) : ?int
    {
        $theType = Lib::type();

        $scaleIntList = [];

        $scaleLimit = $this->static_scale_limit();

        if (null !== $scale) {
            $scaleInt = $theType->int_non_negative($scale)->orThrow();

            $scaleIntList[] = $scaleInt;
        }

        if ([] !== $numbers) {
            $scaleIntList = array_merge(
                $scaleIntList,
                $this->scales(...$numbers)
            );
        }

        if ([] === $scaleIntList) {
            return null;
        }

        $scaleMax = max($scaleIntList);

        if ($scaleMax > $scaleLimit) {
            throw new RuntimeException(
                [ 'The result `scaleMax` is bigger than allowed maximum', $scaleMax, $scaleLimit ]
            );
        }

        return $scaleMax;
    }


    public function bccomp($num1, $num2, ?int $scale = null) : int
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum1, $bcnum2)
            ?? $this->scale_max(bcscale());

        $result = bccomp(
            $bcnum1,
            $bcnum2,
            $scaleMax
        );

        return $result;
    }


    public function bcabs($number) : Bcnumber
    {
        $theType = Lib::type();

        $bcnumber = $theType->bcnumber($number)->orThrow();

        $result = $bcnumber->getValueAbsolute();

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    public function bcceil($number) : Bcnumber
    {
        $theType = Lib::type();

        $bcnumber = $theType->bcnumber($number)->orThrow();

        $hasNonZeroFrac = false;
        if ($bcnumber->hasFrac($frac)) {
            $hasNonZeroFrac = ('' !== ltrim($frac, '.0'));
        }

        $result = $bcnumber->getValueInt();

        if ($bcnumber->isNegative()) {
            if ($hasNonZeroFrac) {
                $result = bcadd($result, '0', 0);
            }

        } else {
            if ($hasNonZeroFrac) {
                $result = bcadd($result, '1', 0);
            }
        }

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    public function bcfloor($number) : Bcnumber
    {
        $theType = Lib::type();

        $bcnumber = $theType->bcnumber($number)->orThrow();

        $hasNonZeroFrac = false;
        if ($bcnumber->hasFrac($frac)) {
            $hasNonZeroFrac = ('' !== ltrim($frac, '.0'));
        }

        $result = $bcnumber->getValueInt();

        if ($bcnumber->isNegative()) {
            if ($hasNonZeroFrac) {
                $result = bcsub($result, '1', 0);
            }

        } else {
            if ($hasNonZeroFrac) {
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
     * > 1.5 -> 2
     * > 1.05 -> 1
     * > -1.05 -> -1
     * > -1.5 -> -2
     */
    public function bcmathround(
        $number, ?int $scale = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : Bcnumber
    {
        $scale = $scale ?? 0;

        $theType = Lib::type();

        $bcnumber = $theType->bcnumber($number)->orThrow();
        $scaleInt = $theType->int_non_negative($scale)->orThrow();

        if ($bcnumber->isZero()) {
            return clone $bcnumber;
        }

        $scaleMax = $this->scale_max(
            $scaleInt
        );

        $isNegative = $bcnumber->isNegative();

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

        $factor = ($scaleMax > 0)
            ? ((string) pow(10, $scaleMax))
            : '1';

        $refBcScaledAbs = $this->bcmul($bcnumber->getValueAbsolute(), $factor);

        $scaledAbsInt = intval($refBcScaledAbs->getValueAbsoluteInt());
        $scaledAbsFrac = $refBcScaledAbs->getFrac();

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

                $diff1 = $this->bcabs($this->bcsub($refBcScaledAbs, $b));
                $diff2 = $this->bcabs($this->bcsub($c, $refBcScaledAbs));

                $isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0);
                $scaledAbs = $isLessThanOrEqual ? $b : $c;

            } elseif ($isRoundOdd) {
                $a = $scaledAbsInt;
                $b = ($a % 2) ? $a : ($a - 1);
                $c = $b + 2;

                $diff1 = $this->bcabs($this->bcsub($refBcScaledAbs, $b));
                $diff2 = $this->bcabs($this->bcsub($c, $refBcScaledAbs));

                $isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0);
                $scaledAbs = $isLessThanOrEqual ? $b : $c;

            } else {
                throw new RuntimeException(
                    [ 'The `round` mode is unknown', $flags ]
                );
            }
        }

        $result = $isNegative ? "-{$scaledAbs}" : $scaledAbs;

        $bcresult = $this->bcdiv($result, $factor, $scaleMax);

        return $bcresult;
    }

    /**
     * > 2.5 -> 2
     * > 1.05 -> 2
     * > -1.05 -> -2
     * > -2.5 -> -2
     */
    public function bcmathround_even($number, ?int $scale = null) : Bcnumber
    {
        return $this->bcmathround(
            $number, $scale,
            _NUM_ROUND_EVEN, _NUM_ROUND_EVEN
        );
    }

    /**
     * > 2.5 -> 3
     * > 1.05 -> 1
     * > -1.05 -> -1
     * > -2.5 -> -3
     */
    public function bcmathround_odd($number, ?int $scale = null) : Bcnumber
    {
        return $this->bcmathround(
            $number, $scale,
            _NUM_ROUND_ODD, _NUM_ROUND_ODD
        );
    }


    /**
     * > Денежное округление
     *
     * > Точка принятия решения - "наличие дробной части", если есть - округляем, если нет - обрезаем
     * > Участвует всё число
     * > Режим округления применяется к числу, у которого "есть дробная часть, даже минимальная"
     *
     * > 1.5 -> 2
     * > 1.05 -> 2
     * > -1.05 -> -2
     * > -1.5 -> -2
     */
    public function bcmoneyround(
        $number, ?int $scale = null,
        ?int $flags = null, ?int $flagsNegative = null
    ) : Bcnumber
    {
        $scale = $scale ?? 0;

        $theType = Lib::type();

        $bcnumber = $theType->bcnumber($number)->orThrow();
        $scaleInt = $theType->int_non_negative($scale)->orThrow();

        if ($bcnumber->isZero()) {
            return clone $bcnumber;
        }

        $scaleMax = $this->scale_max(
            $scaleInt
        );

        $isNegative = $bcnumber->isNegative();

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

        $factor = ($scaleMax > 0)
            ? ((string) pow(10, $scaleMax))
            : '1';

        $refBcScaledAbs = $this->bcmul($bcnumber->getValueAbsolute(), $factor);

        $scaledAbsInt = intval($refBcScaledAbs->getValueAbsoluteInt());
        $scaledAbsFrac = $refBcScaledAbs->getFrac();

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
                $b = (0 === ($a % 2)) ? $a : ($a - 1);
                $c = $b + 2;

                $diff1 = $this->bcabs($this->bcsub($refBcScaledAbs, $b));
                $diff2 = $this->bcabs($this->bcsub($c, $refBcScaledAbs));

                $isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0);
                $scaledAbs = $isLessThanOrEqual ? $b : $c;

            } elseif ($isRoundOdd) {
                $a = $scaledAbsInt;
                $b = ($a % 2) ? $a : ($a - 1);
                $c = $b + 2;

                $diff1 = $this->bcabs($this->bcsub($refBcScaledAbs, $b));
                $diff2 = $this->bcabs($this->bcsub($c, $refBcScaledAbs));

                $isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0);
                $scaledAbs = $isLessThanOrEqual ? $b : $c;

            } else {
                throw new RuntimeException(
                    [ 'The `round` mode is unknown', $flags ]
                );
            }
        }

        $result = $isNegative ? "-{$scaledAbs}" : $scaledAbs;

        $bcresult = $this->bcdiv($result, $factor, $scaleMax);

        return $bcresult;
    }

    /**
     * > 1.5 -> 1
     * > 1.05 -> 1
     * > -1.05 -> -1
     * > -1.5 -> -1
     */
    public function bcmoneytrunc($number, ?int $scale = null) : Bcnumber
    {
        return $this->bcmoneyround(
            $number, $scale,
            _NUM_ROUND_TOWARD_ZERO, _NUM_ROUND_TOWARD_ZERO
        );
    }

    /**
     * > 1.5 -> 2
     * > 1.05 -> 2
     * > -1.05 -> -1
     * > -1.5 -> -1
     */
    public function bcmoneyceil($number, ?int $scale = null) : Bcnumber
    {
        return $this->bcmoneyround(
            $number, $scale,
            _NUM_ROUND_TO_POSITIVE_INF, _NUM_ROUND_TO_POSITIVE_INF
        );
    }

    /**
     * > 1.5 -> 1
     * > 1.05 -> 1
     * > -1.05 -> -2
     * > -1.5 -> -2
     */
    public function bcmoneyfloor($number, ?int $scale = null) : Bcnumber
    {
        return $this->bcmoneyround(
            $number, $scale,
            _NUM_ROUND_TO_NEGATIVE_INF, _NUM_ROUND_TO_NEGATIVE_INF
        );
    }


    public function bcadd($num1, $num2, ?int $scale = null) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum1, $bcnum2)
            ?? $this->scale_max(bcscale());

        $result = bcadd(
            $bcnum1,
            $bcnum2,
            $scaleMax
        );

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    public function bcsub($num1, $num2, ?int $scale = null) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum1, $bcnum2)
            ?? $this->scale_max(bcscale());

        $result = bcsub(
            $bcnum1,
            $bcnum2,
            $scaleMax
        );

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    public function bcmul($num1, $num2, ?int $scale = null) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        if (null === $scale) {
            if ($bcnum1->getFrac() && $bcnum2->getFrac()) {
                throw new LogicException(
                    [ 'The `scale` should be passed if both arguments have fractional parts', $num1, $num2 ]
                );
            }
        }

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum1, $bcnum2)
            ?? $this->scale_max(bcscale());

        $result = bcmul(
            $bcnum1,
            $bcnum2,
            $scaleMax
        );

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }

    /**
     * > поскольку при делении число дробных знаков может увелится, параметр $scale сделан обязательным
     */
    public function bcdiv($num1, $num2, int $scale) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();
        $scaleInt = $theType->int_non_negative($scale)->orThrow();

        $scaleMax = $this->scale_max(
            $scaleInt
        );

        $result = bcdiv(
            $bcnum1,
            $bcnum2,
            $scaleMax
        );

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }


    public function bcmod($num1, $num2) : Bcnumber
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

    public function bcfmod($num1, $num2, int $scale) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();
        $scaleInt = $theType->int_non_negative($scale)->orThrow();

        $scaleMax = $this->scale_max(
            $scaleInt
        );

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


    public function bcpow($num, int $exponent, ?int $scale = null) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum = $theType->bcnumber($num)->orThrow();

        if (null === $scale) {
            if ($bcnum->hasFrac()) {
                throw new LogicException(
                    [ 'The `scale` should be passed if `num` has fractional part', $num ]
                );
            }
        }

        $exponentString = (string) $exponent;

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum)
            ?? $this->scale_max(bcscale());

        $result = bcpow($bcnum, $exponentString, $scaleMax);

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }


    public function bcsqrt($num, int $scale) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum = $theType->bcnumber($num)->orThrow();
        $scaleInt = $theType->int_non_negative($scale)->orThrow();

        $scaleMax = $this->scale_max(
            $scaleInt
        );

        $result = bcsqrt($bcnum, $scaleMax);

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }


    /**
     * > Greatest Common Divisor
     */
    public function bcgcd($num1, $num2) : Bcnumber
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
    public function bclcm($num1, $num2) : Bcnumber
    {
        $theType = Lib::type();

        $bcnum1 = $theType->bcnumber($num1)->orThrow();
        $bcnum2 = $theType->bcnumber($num2)->orThrow();

        $abs1 = $bcnum1->getValueAbsolute();
        $abs2 = $bcnum2->getValueAbsolute();

        $result = bcmul($abs1, $abs2, 0);

        $bcGcd = $this->bcgcd($abs1, $abs2);

        $absGcd = $bcGcd->getValueAbsolute();

        $result = bcdiv($result, $absGcd, 0);

        $bcresult = $theType->bcnumber($result)->orThrow();

        return $bcresult;
    }
}
