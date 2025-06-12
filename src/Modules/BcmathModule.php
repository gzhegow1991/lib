<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Bcmath\Number;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class BcmathModule
{
    /**
     * @var int
     */
    protected $scaleLimit = 16;


    public function __construct()
    {
        if (! extension_loaded('bcmath')) {
            throw new RuntimeException(
                'Missing PHP extension: bcmath'
            );
        }
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

        $status = Lib::type()->numeric($refValueNumeric, $value, $allowExp, [ &$split ]);
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
     * > Точка принятия решения - "дробная часть равна .5/.05/.005 и тд"
     * > Участвует только 1 разряд свыше указанного, как в математике, т.е. если число 1.005, а округляем до 1 знака, то 5 не участвует в решении, число будет 1.00
     * > Середина определяется по первому не-нулевому разряду, то есть для 1.005 при округлении до 2 знаков решение будет приниматься по третьему знаку 5
     * > К самой середине применяется правило округления, все что выше середины - правило всегда "от нуля", все что ниже середины - правило "к нулю"
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

        if (! $this->type_bcnumber($refBcNumber, $number)) {
            throw new LogicException(
                [ 'The `num` should be a valid numeric without exponent', $number ]
            );
        }

        if ($refBcNumber->isZero()) {
            return $refBcNumber;
        }

        if ($scale < 0) {
            throw new LogicException(
                [ 'The `scale` should be a non-negative integer', $scale ]
            );
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $flagsNonNegativeCurrent = 0;
        $flagsNegativeCurrent = 0;

        $hasFlags = (null !== $flags);
        $hasFlagsNegative = (null !== $flagsNegative);

        if ($hasFlags && $hasFlagsNegative) {
            $flagsNonNegativeCurrent = $flags;
            $flagsNegativeCurrent = $flagsNegative;

        } elseif ($hasFlags) {
            $flagsNonNegativeCurrent = $flags;
            $flagsNegativeCurrent = $flags;

        } elseif ($hasFlagsNegative) {
            throw new LogicException(
                [ 'Unable to set `flagsNegative` without `flags`', $flagsNegative, $flags ]
            );
        }

        $flagGroups = [
            '_NUM_ROUND_ROUNDING' => [
                [
                    _NUM_ROUND_ROUNDING_AWAY_FROM_ZERO,
                    _NUM_ROUND_ROUNDING_TOWARD_ZERO,
                    _NUM_ROUND_ROUNDING_TO_POSITIVE_INF,
                    _NUM_ROUND_ROUNDING_TO_NEGATIVE_INF,
                    _NUM_ROUND_ROUNDING_EVEN,
                    _NUM_ROUND_ROUNDING_ODD,
                ],
                _NUM_ROUND_ROUNDING_AWAY_FROM_ZERO,
            ],
        ];

        foreach ( $flagGroups as $groupName => [ $conflict, $default ] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ($flagsNonNegativeCurrent & $flag) {
                    $cnt++;
                }
            }

            if ($cnt > 1) {
                throw new LogicException(
                    [ 'The `flagsNonNegative` conflict in group: ' . $groupName, $flags ]
                );

            } elseif (0 === $cnt) {
                $flagsNonNegativeCurrent |= $default;
            }
        }
        foreach ( $flagGroups as $groupName => [ $conflict, $default ] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ($flagsNegativeCurrent & $flag) {
                    $cnt++;
                }
            }

            if ($cnt > 1) {
                throw new LogicException(
                    [ 'The `flagsNegative` conflict in group: ' . $groupName, $flags ]
                );

            } elseif (0 === $cnt) {
                $flagsNegativeCurrent |= $default;
            }
        }

        $isNonNegativeRoundingToPositiveInf = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_TO_POSITIVE_INF));
        $isNonNegativeRoundingToNegativeInf = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_TO_NEGATIVE_INF));
        $isNonNegativeRoundingAwayFromZero = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_AWAY_FROM_ZERO));
        $isNonNegativeRoundingTowardZero = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_TOWARD_ZERO));
        $isNonNegativeRoundingEven = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_EVEN));
        $isNonNegativeRoundingOdd = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_ODD));

        $isNegativeRoundingToPositiveInf = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_TO_POSITIVE_INF));
        $isNegativeRoundingToNegativeInf = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_TO_NEGATIVE_INF));
        $isNegativeRoundingAwayFromZero = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_AWAY_FROM_ZERO));
        $isNegativeRoundingTowardZero = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_TOWARD_ZERO));
        $isNegativeRoundingEven = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_EVEN));
        $isNegativeRoundingOdd = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_ODD));

        $sign = $refBcNumber->getSign();

        $isNegative = ('-' === $sign);

        $factor = ($scaleMax > 0)
            ? ((string) pow(10, $scaleMax))
            : '1';

        $factorMath = $factor . '0';

        $scaled = bcmul($refBcNumber, $factorMath, 0);
        $scaled = bcdiv($scaled, '10', 1);

        $this->type_bcnumber($refBcScaled, $scaled);

        $scaledFrac = $refBcScaled->getFrac();

        if ($isNegative) {
            if ($isNegativeRoundingAwayFromZero) {
                $isNegativeRoundingToNegativeInf = true;
                unset($isNegativeRoundingAwayFromZero);
            }
            if ($isNegativeRoundingTowardZero) {
                $isNegativeRoundingToPositiveInf = true;
                unset($isNegativeRoundingTowardZero);
            }

            $firstNonZeroDigit = ltrim($scaledFrac, '.0');
            if ('' === $firstNonZeroDigit) {
                $firstNonZeroDigit = 0;

            } else {
                $firstNonZeroDigit = (int) $firstNonZeroDigit[ 0 ];
            }

            $isMidpoint = ($firstNonZeroDigit === 5);

            if (! $isMidpoint) {
                if ($firstNonZeroDigit > 5) {
                    $refBcScaled = $this->bcfloor($refBcScaled);

                } elseif ($firstNonZeroDigit < 5) {
                    $refBcScaled = $this->bcceil($refBcScaled);

                } else {
                    throw new RuntimeException(
                        [ 'The negative `rounding` mode is unknown', $flags ]
                    );
                }

            } else {
                if ($isNegativeRoundingToPositiveInf) {
                    $refBcScaled = $this->bcceil($refBcScaled);

                } elseif ($isNegativeRoundingToNegativeInf) {
                    $refBcScaled = $this->bcfloor($refBcScaled);

                } else {
                    if ($isNegativeRoundingEven) {
                        $a = $this->bcfloor($refBcScaled);
                        $b = ($aIsMod2 = $this->bcmod($a, '2')->isZero()) ? $a : $this->bcsub($a, '1');
                        $c = $this->bcadd($b, '2');

                        $diff1 = $this->bcabs($this->bcsub($refBcScaled, $b));
                        $diff2 = $this->bcabs($this->bcsub($c, $refBcScaled));

                        $refBcScaled = ($isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0)) ? $b : $c;

                    } elseif ($isNegativeRoundingOdd) {
                        $a = $this->bcfloor($refBcScaled);
                        $b = ($aIsNotMod2 = (! ($this->bcmod($a, '2')->isZero()))) ? $a : $this->bcsub($a, '1');
                        $c = $this->bcadd($b, '2');

                        $diff1 = $this->bcabs($this->bcsub($refBcScaled, $b));
                        $diff2 = $this->bcabs($this->bcsub($c, $refBcScaled));

                        $refBcScaled = ($isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0)) ? $b : $c;

                    } else {
                        throw new RuntimeException(
                            [ 'The negative `rounding` mode is unknown', $flags ]
                        );
                    }
                }
            }

        } else {
            // if ($isNonNegative) {

            if ($isNonNegativeRoundingAwayFromZero) {
                $isNonNegativeRoundingToPositiveInf = true;
                unset($isNonNegativeRoundingAwayFromZero);
            }
            if ($isNonNegativeRoundingTowardZero) {
                $isNonNegativeRoundingToNegativeInf = true;
                unset($isNonNegativeRoundingTowardZero);
            }

            $firstNonZeroDigit = ltrim($scaledFrac, '.0');
            if ('' === $firstNonZeroDigit) {
                $firstNonZeroDigit = 0;

            } else {
                $firstNonZeroDigit = (int) $firstNonZeroDigit[ 0 ];
            }

            $isMidpoint = ($firstNonZeroDigit === 5);

            if (! $isMidpoint) {
                if ($firstNonZeroDigit > 5) {
                    $refBcScaled = $this->bcceil($refBcScaled);

                } elseif ($firstNonZeroDigit < 5) {
                    $refBcScaled = $this->bcfloor($refBcScaled);

                } else {
                    throw new RuntimeException(
                        [ 'The non-negative `rounding` mode is unknown', $flags ]
                    );
                }

            } else {
                if ($isNonNegativeRoundingToPositiveInf) {
                    $refBcScaled = $this->bcceil($refBcScaled);

                } elseif ($isNonNegativeRoundingToNegativeInf) {
                    $refBcScaled = $this->bcfloor($refBcScaled);

                } else {
                    if ($isNonNegativeRoundingEven) {
                        $a = $this->bcfloor($refBcScaled);
                        $b = ($aIsMod2 = $this->bcmod($a, '2')->isZero()) ? $a : $this->bcsub($a, '1');
                        $c = $this->bcadd($b, '2');

                        $diff1 = $this->bcabs($this->bcsub($refBcScaled, $b));
                        $diff2 = $this->bcabs($this->bcsub($c, $refBcScaled));

                        $refBcScaled = ($isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0)) ? $b : $c;

                    } elseif ($isNonNegativeRoundingOdd) {
                        $a = $this->bcfloor($refBcScaled);
                        $b = ($aIsNotMod2 = (! ($this->bcmod($a, '2')->isZero()))) ? $a : $this->bcsub($a, '1');
                        $c = $this->bcadd($b, '2');

                        $diff1 = $this->bcabs($this->bcsub($refBcScaled, $b));
                        $diff2 = $this->bcabs($this->bcsub($c, $refBcScaled));

                        $refBcScaled = ($isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0)) ? $b : $c;

                    } else {
                        throw new RuntimeException(
                            [ 'The non-negative `rounding` mode is unknown', $flags ]
                        );
                    }
                }
            }
        }

        $bcResult = $this->bcdiv($refBcScaled, $factor, $scaleMax);

        return $bcResult;
    }

    /**
     * > 1.5 -> 1
     * > 1.05 -> 1
     * > -1.05 -> -1
     * > -1.5 -> -1
     */
    public function bcmathtrunc($number, ?int $scale = null) : Bcnumber
    {
        return $this->bcmathround(
            $number, $scale,
            _NUM_ROUND_ROUNDING_TOWARD_ZERO, _NUM_ROUND_ROUNDING_TOWARD_ZERO
        );
    }

    /**
     * > 1.5 -> 2
     * > 1.05 -> 1
     * > -1.05 -> -1
     * > -1.5 -> -1
     */
    public function bcmathceil($number, ?int $scale = null) : Bcnumber
    {
        return $this->bcmathround(
            $number, $scale,
            _NUM_ROUND_ROUNDING_TO_POSITIVE_INF, _NUM_ROUND_ROUNDING_TO_POSITIVE_INF
        );
    }

    /**
     * > 1.5 -> 1
     * > 1.05 -> 1
     * > -1.05 -> -1
     * > -1.5 -> -2
     */
    public function bcmathfloor($number, ?int $scale = null) : Bcnumber
    {
        return $this->bcmathround(
            $number, $scale,
            _NUM_ROUND_ROUNDING_TO_NEGATIVE_INF, _NUM_ROUND_ROUNDING_TO_NEGATIVE_INF
        );
    }


    /**
     * > Денежное округление
     * > Точка принятия решения - "наличие дробной части", если есть - округляем, если нет - обрезаем
     * > Участвует всё число
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

        if (! $this->type_bcnumber($refBcNumber, $number)) {
            throw new LogicException(
                [ 'The `num` should be a valid numeric without exponent', $number ]
            );
        }

        if ($refBcNumber->isZero()) {
            return $refBcNumber;
        }

        if ($scale < 0) {
            throw new LogicException(
                [ 'The `scale` should be a non-negative integer', $scale ]
            );
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $flagsNonNegativeCurrent = 0;
        $flagsNegativeCurrent = 0;

        $hasFlags = (null !== $flags);
        $hasFlagsNegative = (null !== $flagsNegative);

        if ($hasFlags && $hasFlagsNegative) {
            $flagsNonNegativeCurrent = $flags;
            $flagsNegativeCurrent = $flagsNegative;

        } elseif ($hasFlags) {
            $flagsNonNegativeCurrent = $flags;
            $flagsNegativeCurrent = $flags;

        } elseif ($hasFlagsNegative) {
            throw new LogicException(
                [ 'Unable to set `flagsNegative` without `flags`', $flagsNegative, $flags ]
            );
        }

        $flagGroups = [
            '_NUM_ROUND_ROUNDING' => [
                [
                    _NUM_ROUND_ROUNDING_AWAY_FROM_ZERO,
                    _NUM_ROUND_ROUNDING_TOWARD_ZERO,
                    _NUM_ROUND_ROUNDING_TO_POSITIVE_INF,
                    _NUM_ROUND_ROUNDING_TO_NEGATIVE_INF,
                    _NUM_ROUND_ROUNDING_EVEN,
                    _NUM_ROUND_ROUNDING_ODD,
                ],
                _NUM_ROUND_ROUNDING_AWAY_FROM_ZERO,
            ],
        ];

        foreach ( $flagGroups as $groupName => [ $conflict, $default ] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ($flagsNonNegativeCurrent & $flag) {
                    $cnt++;
                }
            }

            if ($cnt > 1) {
                throw new LogicException(
                    [ 'The `flagsNonNegative` conflict in group: ' . $groupName, $flags ]
                );

            } elseif (0 === $cnt) {
                $flagsNonNegativeCurrent |= $default;
            }
        }
        foreach ( $flagGroups as $groupName => [ $conflict, $default ] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ($flagsNegativeCurrent & $flag) {
                    $cnt++;
                }
            }

            if ($cnt > 1) {
                throw new LogicException(
                    [ 'The `flagsNegative` conflict in group: ' . $groupName, $flags ]
                );

            } elseif (0 === $cnt) {
                $flagsNegativeCurrent |= $default;
            }
        }

        $isNonNegativeRoundingToPositiveInf = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_TO_POSITIVE_INF));
        $isNonNegativeRoundingToNegativeInf = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_TO_NEGATIVE_INF));
        $isNonNegativeRoundingAwayFromZero = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_AWAY_FROM_ZERO));
        $isNonNegativeRoundingTowardZero = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_TOWARD_ZERO));
        $isNonNegativeRoundingEven = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_EVEN));
        $isNonNegativeRoundingOdd = ((bool) ($flagsNonNegativeCurrent & _NUM_ROUND_ROUNDING_ODD));

        $isNegativeRoundingToPositiveInf = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_TO_POSITIVE_INF));
        $isNegativeRoundingToNegativeInf = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_TO_NEGATIVE_INF));
        $isNegativeRoundingAwayFromZero = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_AWAY_FROM_ZERO));
        $isNegativeRoundingTowardZero = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_TOWARD_ZERO));
        $isNegativeRoundingEven = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_EVEN));
        $isNegativeRoundingOdd = ((bool) ($flagsNegativeCurrent & _NUM_ROUND_ROUNDING_ODD));

        $sign = $refBcNumber->getSign();

        $isNegative = ('-' === $sign);

        $factor = ($scaleMax > 0)
            ? ((string) pow(10, $scaleMax))
            : '1';

        $refBcScaled = $this->bcmul($refBcNumber, $factor);

        $scaledFrac = $refBcScaled->getFrac();

        if ($isNegative) {
            if ($isNegativeRoundingAwayFromZero) {
                $isNegativeRoundingToNegativeInf = true;
                unset($isNegativeRoundingAwayFromZero);
            }
            if ($isNegativeRoundingTowardZero) {
                $isNegativeRoundingToPositiveInf = true;
                unset($isNegativeRoundingTowardZero);
            }

            $hasFrac = ($scaledFrac !== '');

            if (! $hasFrac) {
                $refBcScaled = $this->bcceil($refBcScaled);

            } else {
                if ($isNegativeRoundingToPositiveInf) {
                    $refBcScaled = $this->bcceil($refBcScaled);

                } elseif ($isNegativeRoundingToNegativeInf) {
                    $refBcScaled = $this->bcfloor($refBcScaled);

                } else {
                    if ($isNegativeRoundingEven) {
                        $a = $this->bcfloor($refBcScaled);
                        $b = ($aIsMod2 = $this->bcmod($a, '2')->isZero()) ? $a : $this->bcsub($a, '1');
                        $c = $this->bcadd($b, '2');

                        $diff1 = $this->bcabs($this->bcsub($refBcScaled, $b));
                        $diff2 = $this->bcabs($this->bcsub($c, $refBcScaled));

                        $refBcScaled = ($isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0)) ? $b : $c;

                    } elseif ($isNegativeRoundingOdd) {
                        $a = $this->bcfloor($refBcScaled);
                        $b = ($aIsNotMod2 = (! ($this->bcmod($a, '2')->isZero()))) ? $a : $this->bcsub($a, '1');
                        $c = $this->bcadd($b, '2');

                        $diff1 = $this->bcabs($this->bcsub($refBcScaled, $b));
                        $diff2 = $this->bcabs($this->bcsub($c, $refBcScaled));

                        $refBcScaled = ($isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0)) ? $b : $c;

                    } else {
                        throw new RuntimeException(
                            [ 'The negative `rounding` mode is unknown', $flags ]
                        );
                    }
                }
            }

        } else {
            // if (! $isNonNegative) {

            if ($isNonNegativeRoundingAwayFromZero) {
                $isNonNegativeRoundingToPositiveInf = true;
                unset($isNonNegativeRoundingAwayFromZero);
            }
            if ($isNonNegativeRoundingTowardZero) {
                $isNonNegativeRoundingToNegativeInf = true;
                unset($isNonNegativeRoundingTowardZero);
            }

            $hasFrac = ($scaledFrac !== '');

            if (! $hasFrac) {
                $refBcScaled = $this->bcfloor($refBcScaled);

            } else {
                if ($isNonNegativeRoundingToPositiveInf) {
                    $refBcScaled = $this->bcceil($refBcScaled);

                } elseif ($isNonNegativeRoundingToNegativeInf) {
                    $refBcScaled = $this->bcfloor($refBcScaled);

                } else {
                    if ($isNonNegativeRoundingEven) {
                        $a = $this->bcfloor($refBcScaled);
                        $b = ($aIsMod2 = $this->bcmod($a, '2')->isZero()) ? $a : $this->bcsub($a, '1');
                        $c = $this->bcadd($b, '2');

                        $diff1 = $this->bcabs($this->bcsub($refBcScaled, $b));
                        $diff2 = $this->bcabs($this->bcsub($c, $refBcScaled));

                        $refBcScaled = ($isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0)) ? $b : $c;

                    } elseif ($isNonNegativeRoundingOdd) {
                        $a = $this->bcfloor($refBcScaled);
                        $b = ($aIsNotMod2 = (! ($this->bcmod($a, '2')->isZero()))) ? $a : $this->bcsub($a, '1');
                        $c = $this->bcadd($b, '2');

                        $diff1 = $this->bcabs($this->bcsub($refBcScaled, $b));
                        $diff2 = $this->bcabs($this->bcsub($c, $refBcScaled));

                        $refBcScaled = ($isLessThanOrEqual = ($this->bccomp($diff1, $diff2) <= 0)) ? $b : $c;

                    } else {
                        throw new RuntimeException(
                            [ 'The non-negative `rounding` mode is unknown', $flags ]
                        );
                    }
                }
            }
        }

        $bcResult = $this->bcdiv($refBcScaled, $factor, $scaleMax);

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
            _NUM_ROUND_ROUNDING_TOWARD_ZERO, _NUM_ROUND_ROUNDING_TOWARD_ZERO
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
            _NUM_ROUND_ROUNDING_TO_POSITIVE_INF, _NUM_ROUND_ROUNDING_TO_POSITIVE_INF
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
            _NUM_ROUND_ROUNDING_TO_NEGATIVE_INF, _NUM_ROUND_ROUNDING_TO_NEGATIVE_INF
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
