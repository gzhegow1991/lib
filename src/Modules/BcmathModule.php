<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
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
     * @param Bcnumber|null $r
     */
    public function type_bcnumber(&$r, $value) : bool
    {
        $r = null;

        if ($value instanceof Bcnumber) {
            $r = $value;

            return true;
        }

        $status = Lib::type()->numeric($refValueNumeric, $value, false, [ &$split ]);
        if (! $status) {
            return false;
        }

        $frac = $split[ 2 ];

        $scale = 0;
        if ('' !== $frac) {
            $scale = strlen($frac) - 1;
        }

        $bcnum = Bcnumber::fromValidArray([
            'original' => $value,
            'sign'     => $split[ 0 ],
            'int'      => $split[ 1 ],
            'frac'     => $split[ 2 ],
            'scale'    => $scale,
        ]);

        $r = $bcnum;

        return true;
    }


    /**
     * @param (int|float|string|Number|Bcnumber)[] ...$numbers
     *
     * @return int[]
     */
    public function scales(...$numbers) : array
    {
        $scales = [];

        foreach ( $numbers as $i => $number ) {
            if (! $this->type_bcnumber($refBcnumber, $number)) {
                throw new LogicException(
                    [ 'Each of `numbers` should be a valid Bcnumber', $number, $i ]
                );
            }

            $scales[] = $refBcnumber->getScale();
        }

        return $scales;
    }


    public function scale_min(?int $scale = null, ...$numbers) : ?int
    {
        $scales = [];

        $scaleLimit = $this->static_scale_limit();

        if (null !== $scale) {
            if (! Lib::type()->int_non_negative($refScaleInt, $scale)) {
                throw new LogicException(
                    [ 'The `scale` should be a non-negative integer', $scale ]
                );
            }

            $scales[] = $refScaleInt;
        }

        if ([] !== $numbers) {
            $scales = array_merge(
                $scales,
                $this->scales(...$numbers)
            );
        }

        if ([] === $scales) {
            return null;
        }

        $scaleMin = min($scales);

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
        $scales = [];

        $scaleLimit = $this->static_scale_limit();

        if (null !== $scale) {
            if (! Lib::type()->int_non_negative($refScaleInt, $scale)) {
                throw new LogicException(
                    [ 'The `scale` should be a non-negative integer', $scale ]
                );
            }

            $scales[] = $refScaleInt;
        }

        if ([] !== $numbers) {
            $scales = array_merge(
                $scales,
                $this->scales(...$numbers)
            );
        }

        if ([] === $scales) {
            return null;
        }

        $scaleMax = max($scales);

        if ($scaleMax > $scaleLimit) {
            throw new RuntimeException(
                [ 'The result `scaleMax` is bigger than allowed maximum', $scaleMax, $scaleLimit ]
            );
        }

        return $scaleMax;
    }


    public function bccomp($num1, $num2, ?int $scale = null) : int
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be a valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be a valid Bcnumber', $num2 ]
            );
        }

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $refBcnum1, $refBcnum2)
            ?? $this->scale_max(bcscale());

        $result = bccomp(
            $refBcnum1,
            $refBcnum2,
            $scaleMax
        );

        return $result;
    }


    public function bcabs($number) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnumber, $number)) {
            throw new LogicException(
                [ 'The `num` should be a valid Bcnumber', $number ]
            );
        }

        $result = $refBcnumber->getValueAbsolute();

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    public function bcceil($number) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnumber, $number)) {
            throw new LogicException(
                [ 'The `num` should be a valid Bcnumber', $number ]
            );
        }

        $hasNonZeroFrac = false;
        if ($refBcnumber->hasFrac($frac)) {
            $hasNonZeroFrac = ('' !== ltrim($frac, '.0'));
        }

        $result = $refBcnumber->getValueInt();

        if ($refBcnumber->isNegative()) {
            if ($hasNonZeroFrac) {
                $result = bcadd($result, '0', 0);
            }

        } else {
            if ($hasNonZeroFrac) {
                $result = bcadd($result, '1', 0);
            }
        }

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    public function bcfloor($number) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnumber, $number)) {
            throw new LogicException(
                [ 'The `num` should be a valid Bcnumber', $number ]
            );
        }

        $hasNonZeroFrac = false;
        if ($refBcnumber->hasFrac($frac)) {
            $hasNonZeroFrac = ('' !== ltrim($frac, '.0'));
        }

        $result = $refBcnumber->getValueInt();

        if ($refBcnumber->isNegative()) {
            if ($hasNonZeroFrac) {
                $result = bcsub($result, '1', 0);
            }

        } else {
            if ($hasNonZeroFrac) {
                $result = bcadd($result, '0', 0);
            }
        }

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
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

        if ($scale < 0) {
            throw new LogicException(
                [ 'The `precision` should be a non-negative integer', $scale ]
            );
        }

        if (! $this->type_bcnumber($refBcNumber, $number)) {
            throw new LogicException(
                [ 'The `num` should be a valid numeric without exponent', $number ]
            );
        }

        if ($refBcNumber->isZero()) {
            return clone $refBcNumber;
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $isNegative = $refBcNumber->isNegative();

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

        $refBcScaledAbs = $this->bcmul($refBcNumber->getValueAbsolute(), $factor);

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

        $bcResult = $this->bcdiv($result, $factor, $scaleMax);

        return $bcResult;
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

        if ($scale < 0) {
            throw new LogicException(
                [ 'The `precision` should be a non-negative integer', $scale ]
            );
        }

        if (! $this->type_bcnumber($refBcNumber, $number)) {
            throw new LogicException(
                [ 'The `num` should be a valid numeric without exponent', $number ]
            );
        }

        if ($refBcNumber->isZero()) {
            return clone $refBcNumber;
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $isNegative = $refBcNumber->isNegative();

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

        $refBcScaledAbs = $this->bcmul($refBcNumber->getValueAbsolute(), $factor);

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

        $bcResult = $this->bcdiv($result, $factor, $scaleMax);

        return $bcResult;
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
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be a valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be a valid Bcnumber', $num2 ]
            );
        }

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $refBcnum1, $refBcnum2)
            ?? $this->scale_max(bcscale());

        $result = bcadd(
            $refBcnum1,
            $refBcnum2,
            $scaleMax
        );

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    public function bcsub($num1, $num2, ?int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be a valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be a valid Bcnumber', $num2 ]
            );
        }

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $refBcnum1, $refBcnum2)
            ?? $this->scale_max(bcscale());

        $result = bcsub(
            $refBcnum1,
            $refBcnum2,
            $scaleMax
        );

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    public function bcmul($num1, $num2, ?int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be a valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be a valid Bcnumber', $num2 ]
            );
        }

        if (null === $scale) {
            if ($refBcnum1->getFrac() && $refBcnum2->getFrac()) {
                throw new LogicException(
                    [ 'The `scale` should be passed if both arguments have fractional parts', $num1, $num2 ]
                );
            }
        }

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $refBcnum1, $refBcnum2)
            ?? $this->scale_max(bcscale());

        $result = bcmul(
            $refBcnum1,
            $refBcnum2,
            $scaleMax
        );

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    /**
     * > поскольку при делении число дробных знаков может увелится, параметр $scale сделан обязательным
     */
    public function bcdiv($num1, $num2, int $scale) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be a valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be a valid Bcnumber', $num2 ]
            );
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $result = bcdiv(
            $refBcnum1,
            $refBcnum2,
            $scaleMax
        );

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }


    public function bcmod($num1, $num2) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be a valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be a valid Bcnumber', $num2 ]
            );
        }

        $result = bcmod(
            $refBcnum1->getValueInt(),
            $refBcnum2->getValueInt(),
            0
        );

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    public function bcfmod($num1, $num2, int $scale) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be a valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be a valid Bcnumber', $num2 ]
            );
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $result = bcdiv(
            $refBcnum1,
            $refBcnum2,
            $scaleMax
        );

        $result = (bccomp($result, '0', $scaleMax) >= 0)
            ? bcadd($result, '0', 0)
            : bcsub($result, '0', 0);

        $result = bcmul($result, $refBcnum2, $scaleMax);

        $result = bcsub($refBcnum1, $result, $scale);

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }


    public function bcpow($num, int $exponent, ?int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be a valid Bcnumber', $num ]
            );
        }

        if (null === $scale) {
            if ($refBcnum->hasFrac()) {
                throw new LogicException(
                    [ 'The `scale` should be passed if `num` has fractional part', $num ]
                );
            }
        }

        $exponentString = (string) $exponent;

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $refBcnum)
            ?? $this->scale_max(bcscale());

        $result = bcpow($refBcnum, $exponentString, $scaleMax);

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }


    public function bcsqrt($num, int $scale) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be a valid Bcnumber', $num ]
            );
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $result = bcsqrt($refBcnum, $scaleMax);

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }


    /**
     * > Greatest Common Divisor
     */
    public function bcgcd($num1, $num2) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be a valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be a valid Bcnumber', $num2 ]
            );
        }

        $abs1 = $refBcnum1->getValueAbsolute();
        $abs2 = $refBcnum2->getValueAbsolute();

        while ( '0' !== $abs2 ) {
            $mod = bcmod($abs1, $abs2, 0);

            [ $abs2, $abs1 ] = [ $mod, $abs2 ];
        }

        $result = $abs1;

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    /**
     * > Lowest Common Multiplier
     */
    public function bclcm($num1, $num2) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be a valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be a valid Bcnumber', $num2 ]
            );
        }

        $abs1 = $refBcnum1->getValueAbsolute();
        $abs2 = $refBcnum2->getValueAbsolute();

        $result = bcmul($abs1, $abs2, 0);

        $bcGcd = $this->bcgcd($abs1, $abs2);

        $absGcd = $bcGcd->getValueAbsolute();

        $result = bcdiv($result, $absGcd, 0);

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }
}
