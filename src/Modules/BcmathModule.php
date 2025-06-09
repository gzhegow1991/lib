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

        $status = $this->type_number($refValueNumber, $value, false);
        if (! $status) {
            return false;
        }

        $exp = $refValueNumber->getExp();
        if ('' !== $exp) {
            return false;
        }

        $bcnum = Bcnumber::fromValidArray([
            'original' => $refValueNumber->getOriginal(),
            'sign'     => $refValueNumber->getSign(),
            'int'      => $refValueNumber->getInt(),
            'frac'     => $refValueNumber->getFrac(),
            'scale'    => $refValueNumber->getScale(),
        ]);

        $r = $bcnum;

        return true;
    }


    public function static_scale_limit(?int $scaleLimit = null) : int
    {
        if (null !== $scaleLimit) {
            if ($scaleLimit < 0) {
                throw new LogicException(
                    [ 'The `scaleLimit` must be non-negative integer', $scaleLimit ]
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
                    [ 'Each of `numbers` should be valid Bcnumber', $number, $i ]
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
                    [ 'The `scale` must be non negative integer', $scale ]
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
                    [ 'The `scale` must be non negative integer', $scale ]
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
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $refBcnum1, $refBcnum2)
            ?? $this->scale_max(bcscale());

        $result = bccomp(
            $refBcnum1->getValue(),
            $refBcnum2->getValue(),
            $scaleMax
        );

        return $result;
    }


    public function bcround($num, ?int $scale = null) : Bcnumber
    {
        $scale = $scale ?? 0;

        if (! $this->type_bcnumber($refBcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        if (! $refBcnum->hasFrac()) {
            return $refBcnum;
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $result = $refBcnum->getValue();

        $hasScale = ($scaleMax > 0);
        if ($hasScale) {
            $factor = '1' . str_repeat('0', $scaleMax);

            $result = bcmul($result, $factor, 1);
        }

        if ($refBcnum->isNegative()) {
            $result = bcsub($result, '0.5', 0);

        } else {
            $result = bcadd($result, '0.5', 0);
        }

        if ($hasScale) {
            $result = bcdiv($result, $factor, $scaleMax);

        } else {
            $result = bcadd($result, '0', 0);
        }

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }


    public function bcceil($num, ?int $scale = null) : Bcnumber
    {
        $scale = $scale ?? 0;

        if (! $this->type_bcnumber($refBcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        if (! $refBcnum->hasFrac()) {
            return $refBcnum;
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $result = $refBcnum->getValue();

        $hasScale = ($scaleMax > 0);
        if ($hasScale) {
            $factor = '1' . str_repeat('0', $scaleMax);

            $result = bcmul($result, $factor, 0);
        }

        if ($refBcnum->isPositive()) {
            $result = bcadd($result, '1');
        }

        if ($hasScale) {
            $result = bcdiv($result, $factor, $scaleMax);

        } else {
            $result = bcadd($result, '0', 0);
        }

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    public function bcfloor($num, ?int $scale = null) : Bcnumber
    {
        $scale = $scale ?? 0;

        if (! $this->type_bcnumber($refBcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        if (! $refBcnum->hasFrac()) {
            return $refBcnum;
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $result = $refBcnum->getValue();

        $hasScale = ($scaleMax > 0);
        if ($hasScale) {
            $factor = '1' . str_repeat('0', $scaleMax);

            $result = bcmul($result, $factor, 0);
        }

        if ($refBcnum->isNegative()) {
            $result = bcsub($result, '1');
        }

        if ($hasScale) {
            $result = bcdiv($result, $factor, $scaleMax);

        } else {
            $result = bcadd($result, '0', 0);
        }

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }


    public function bcmoneyceil($num, ?int $scale = null) : Bcnumber
    {
        $scale = $scale ?? 0;

        if (! $this->type_bcnumber($refBcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        if (! $refBcnum->hasFrac()) {
            return $refBcnum;
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $result = $refBcnum->getValue();

        $hasScale = ($scaleMax > 0);
        if ($hasScale) {
            $factor = '1' . str_repeat('0', $scaleMax);

            $result = bcmul($result, $factor, 0);
        }

        if ($refBcnum->isNegative()) {
            $result = bcsub($result, '1');

        } else {
            $result = bcadd($result, '1');
        }

        if ($hasScale) {
            $result = bcdiv($result, $factor, $scaleMax);

        } else {
            $result = bcadd($result, '0', 0);
        }

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    public function bcmoneyfloor($num, ?int $scale = null) : Bcnumber
    {
        $scale = $scale ?? 0;

        if (! $this->type_bcnumber($refBcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        if (! $refBcnum->hasFrac()) {
            return $refBcnum;
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $result = $refBcnum->getValue();

        $hasScale = ($scaleMax > 0);
        if ($hasScale) {
            $result = bcadd($result, '0', $scaleMax);

        } else {
            $result = bcadd($result, '0', 0);
        }

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }


    public function bcadd($num1, $num2, ?int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $refBcnum1, $refBcnum2)
            ?? $this->scale_max(bcscale());

        $result = bcadd(
            $refBcnum1->getValue(),
            $refBcnum2->getValue(),
            $scaleMax
        );

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    public function bcsub($num1, $num2, ?int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $scaleMax = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $refBcnum1, $refBcnum2)
            ?? $this->scale_max(bcscale());

        $result = bcsub(
            $refBcnum1->getValue(),
            $refBcnum2->getValue(),
            $scaleMax
        );

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }

    public function bcmul($num1, $num2, ?int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
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
            $refBcnum1->getValue(),
            $refBcnum2->getValue(),
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
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $result = bcdiv(
            $refBcnum1->getValue(),
            $refBcnum2->getValue(),
            $scaleMax
        );

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }


    public function bcmod($num1, $num2) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
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
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $value1 = $refBcnum1->getValue();
        $value2 = $refBcnum2->getValue();

        $result = bcdiv($value1, $value2, $scaleMax);

        $result = (bccomp($result, '0', $scaleMax) >= 0)
            ? bcadd($result, '0', 0)
            : bcsub($result, '0', 0);

        $result = bcmul($result, $value2, $scaleMax);

        $result = bcsub($value1, $result, $scale);

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }


    public function bcpow($num, int $exponent, ?int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
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

        $result = $refBcnum->getValue();

        $result = bcpow($result, $exponentString, $scaleMax);

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }


    public function bcsqrt($num, int $scale) : Bcnumber
    {
        if (! $this->type_bcnumber($refBcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        $scaleMax = $this->scale_max(
            $scale
        );

        $result = $refBcnum->getValue();

        $result = bcsqrt($result, $scaleMax);

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
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $num1Abs = $refBcnum1->getValueAbsolute();
        $num2Abs = $refBcnum2->getValueAbsolute();

        while ( '0' !== $num2Abs ) {
            $mod = bcmod($num1Abs, $num2Abs, 0);

            [ $num2Abs, $num1Abs ] = [ $mod, $num2Abs ];
        }

        $result = $num1Abs;

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
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnumber($refBcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $num1Abs = $refBcnum1->getValueAbsolute();
        $num2Abs = $refBcnum2->getValueAbsolute();

        $result = bcmul($num1Abs, $num2Abs, 0);

        $bcGcd = $this->bcgcd($num1Abs, $num2Abs);

        $gcdAbs = $bcGcd->getValueAbsolute();

        $result = bcdiv($result, $gcdAbs, 0);

        $this->type_bcnumber($refBcresult, $result);

        return $refBcresult;
    }
}
