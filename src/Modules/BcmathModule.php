<?php

declare(strict_types=1);

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class BcmathModule
{
    /**
     * @var int
     */
    protected $scaleLimit = 10;


    public function __construct()
    {
        if (! extension_loaded('bcmath')) {
            throw new RuntimeException(
                'Missing PHP extension: bcmath'
            );
        }
    }


    /**
     * @param Bcnumber|null $result
     */
    public function type_bcnum(&$result, $value, int &$scaleParsed = null) : bool
    {
        $result = null;

        if ($value instanceof Bcnumber) {
            $result = $value;

            return true;
        }

        $theType = Lib::type();

        if (! $theType->numeric($_value, $value)) {
            return false;
        }

        $valueMinus = '';
        $valueAbs = $_value;

        $isMinus = ('-' === $_value[ 0 ]);
        if ($isMinus) {
            $valueMinus = '-';
            $valueAbs = substr($_value, 1);
        }

        [
            $valueAbsFloor,
            $valueAbsFrac,
        ] = explode($theType->the_decimal_point(), $valueAbs) + [ '0', '' ];

        $valueAbsFloor = ltrim($valueAbsFloor, '0');  // 0000.1
        $valueAbsFrac = rtrim($valueAbsFrac, '0');    // 1.0000

        $scaleParsed = strlen($valueAbsFrac);

        $minus = (($valueMinus && ($valueAbs != 0)) ? '-' : '');
        $integral = (('' !== $valueAbsFloor) ? $valueAbsFloor : "0");
        $fractional = (('' !== $valueAbsFrac) ? ".{$valueAbsFrac}" : "");

        $bcnum = new Bcnumber(
            $value,
            $minus,
            $integral,
            $fractional,
            $scaleParsed
        );

        $result = $bcnum;

        return true;
    }


    public function static_scale_limit(int $scaleLimit = null) : int
    {
        if (null !== $scaleLimit) {
            if ($scaleLimit < 0) {
                throw new LogicException(
                    [ 'The `scaleLimit` must be non-negative integer', $scaleLimit ]
                );
            }

            $last = $this->scaleLimit;

            $current = $scaleLimit;

            $result = $last;
        }

        $result = $result ?? $this->scaleLimit;

        return $result;
    }


    /**
     * @param (int|float|string)[] ...$numbers
     *
     * @return int[]
     */
    public function scales(...$numbers) : array
    {
        $scales = [];

        foreach ( $numbers as $i => $number ) {
            if (Lib::type()->numeric($_number, $number)) {
                throw new LogicException(
                    [ 'Each of `numbers` should be numeric', $number, $i ]
                );
            }

            $frac = $this->bcfrac($_number, $scale);

            $scales[] = $scale;
        }

        return $scales;
    }


    public function scale_min(int $scale = null, ...$numbers) : int
    {
        $scales = [];

        $scaleLimit = $this->static_scale_limit();

        if (null !== $scale) {
            if (! Lib::type()->int_non_negative($_scale, $scale)) {
                throw new LogicException(
                    [ 'The `scale` must be non negative integer', $scale ]
                );
            }

            $scales[] = $_scale;
        }

        if (0 !== count($numbers)) {
            $scales = array_merge(
                $scales,
                $this->scales(...$numbers)
            );
        }

        $scaleMin = min($scales);

        if ($scaleMin > $scaleLimit) {
            throw new RuntimeException(
                [ 'Scale is bigger than allowed maximum', $scaleMin, $scaleLimit ]
            );
        }

        return $scaleMin;
    }

    public function scale_max(int $scale = null, ...$numbers) : int
    {
        $scales = [];

        $scaleLimit = $this->static_scale_limit();

        if (null !== $scale) {
            if (! Lib::type()->int_non_negative($_scale, $scale)) {
                throw new LogicException(
                    [ 'The `scale` must be non negative integer', $scale ]
                );
            }

            $scales[] = $_scale;
        }

        if (0 !== count($numbers)) {
            $scales = array_merge(
                $scales,
                $this->scales(...$numbers)
            );
        }

        $scaleMax = max($scales);

        if ($scaleMax > $scaleLimit) {
            throw new RuntimeException(
                [ 'Scale is bigger than allowed maximum', $scaleMax, $scaleLimit ]
            );
        }

        return $scaleMax;
    }


    public function bcceil($num, int $scale = 0) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        $fractional = $bcnum->getFractionalPart();

        if (! $fractional) {
            return $bcnum;
        }

        $value = $bcnum->getValue();
        $minus = $bcnum->getMinus();

        $_scale = $this->scale_max($scale);

        $result = $value;

        $hasScale = ($_scale > 0);
        if ($hasScale) {
            $factor = '1' . str_repeat('0', $_scale);

            $result = bcmul(
                $value,
                $factor,
                0
            );
        }

        if (! $minus) {
            $result = bcadd($result, '1');
        }

        if ($hasScale) {
            $result = bcdiv(
                $result,
                $factor,
                $_scale
            );

        } else {
            $result = bcadd(
                $result,
                '0',
                0
            );
        }

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }

    public function bcfloor($num, int $scale = 0) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        $fractional = $bcnum->getFractionalPart();

        if (! $fractional) {
            return $bcnum;
        }

        $value = $bcnum->getValue();
        $minus = $bcnum->getMinus();

        $_scale = $this->scale_max($scale);

        $result = $value;

        $hasScale = ($_scale > 0);
        if ($hasScale) {
            $factor = '1' . str_repeat('0', $_scale);

            $result = bcmul(
                $value,
                $factor,
                0
            );
        }

        if ($minus) {
            $result = bcsub($result, '1');
        }

        if ($hasScale) {
            $result = bcdiv(
                $result,
                $factor,
                $_scale
            );

        } else {
            $result = bcadd(
                $result,
                '0',
                0
            );
        }

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }

    public function bcround($num, int $scale = 0) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        $value = $bcnum->getValue();
        $minus = $bcnum->getMinus();

        $_scale = $this->scale_max($scale);

        $result = $value;

        $hasScale = ($_scale > 0);

        if ($hasScale) {
            $factor = '1' . str_repeat('0', $_scale);

            $result = bcmul(
                $value,
                $factor,
                1
            );
        }

        if ($minus) {
            $result = bcsub($result, '0.5', 0);

        } else {
            $result = bcadd($result, '0.5', 0);
        }

        if ($hasScale) {
            $result = bcdiv(
                $result,
                $factor,
                $_scale
            );

        } else {
            $result = bcadd(
                $result,
                '0',
                0
            );
        }

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }


    public function bcmoneyceil($num, int $scale = 0) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        $fractional = $bcnum->getFractionalPart();

        if (! $fractional) {
            return $bcnum;
        }

        $value = $bcnum->getValue();
        $minus = $bcnum->getMinus();

        $_scale = $this->scale_max($scale);

        $result = $value;

        $hasScale = ($_scale > 0);
        if ($hasScale) {
            $factor = '1' . str_repeat('0', $_scale);

            $result = bcmul(
                $value,
                $factor,
                0
            );
        }

        if ($minus) {
            $result = bcsub($result, '1');

        } else {
            $result = bcadd($result, '1');
        }

        if ($hasScale) {
            $result = bcdiv(
                $result,
                $factor,
                $_scale
            );

        } else {
            $result = bcadd(
                $result,
                '0',
                0
            );
        }

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }

    public function bcmoneyfloor($num, int $scale = 0) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        $fractional = $bcnum->getFractionalPart();

        if (! $fractional) {
            return $bcnum;
        }

        $_scale = $this->scale_max($scale);

        $result = $bcnum->getValue();

        $result = bcadd(
            $result,
            '0',
            $_scale
        );

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }


    public function bcfrac($number, int &$scaleParsed = null) : ?string
    {
        $scaleParsed = null;

        $theType = Lib::type();

        $frac = null;

        if (! Lib::type()->int_non_negative($_number, $number)) {
            throw new LogicException(
                [ 'The `number` should be number', $number ]
            );
        }

        $scaleParsed = 0;

        $frac = strrchr((string) $_number, $theType->the_decimal_point());

        if (false !== $frac) {
            $scaleParsed = strlen($frac) - 1;
        }

        return $frac;
    }


    public function bccomp($num1, $num2, int $scale = null) : int
    {
        if (! $this->type_bcnum($bcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnum($bcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $_scale = null
            ?? $scale
            ?? $this->scale_max(null, $bcnum1, $bcnum2);

        $result = bccomp(
            $bcnum1->getValue(),
            $bcnum2->getValue(),
            $_scale
        );

        return $result;
    }


    public function bcadd($num1, $num2, int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnum($bcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $_scale = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum1, $bcnum2);

        $result = bcadd(
            $bcnum1->getValue(),
            $bcnum2->getValue(),
            $_scale
        );

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }

    public function bcsub($num1, $num2, int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnum($bcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $_scale = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum1, $bcnum2);

        $result = bcsub(
            $bcnum1->getValue(),
            $bcnum2->getValue(),
            $_scale
        );

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }

    public function bcmul($num1, $num2, int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnum($bcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        if (null === $scale) {
            if ($bcnum1->getFractionalPart() && $bcnum2->getFractionalPart()) {
                throw new LogicException(
                    [ 'The `scale` should be passed if both arguments have fractional parts', $num1, $num2 ]
                );
            }
        }

        $_scale = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum1, $bcnum2);

        $result = bcmul(
            $bcnum1->getValue(),
            $bcnum2->getValue(),
            $_scale
        );

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }

    /**
     * > поскольку при делении число дробных знаков может увелится, параметр $scale сделан обязательным
     */
    public function bcdiv($num1, $num2, int $scale) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnum($bcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $_scale = $this->scale_max($scale);

        $result = bcdiv(
            $bcnum1->getValue(),
            $bcnum2->getValue(),
            $_scale
        );

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }


    /**
     * > оригинальная функция ожидает три аргумента, но это противоречит самой идее получения остатка от деления
     * > перед взятием остатка дробная часть обоих чисел отбрасывается
     */
    public function bcmod($num1, $num2) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnum($bcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $result = bcmod(
            $bcnum1->getInteger(),
            $bcnum2->getInteger(),
            0
        );

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }


    public function bcpow($num, int $exponent, int $scale = null) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        if (null === $scale) {
            if ($bcnum->getFractionalPart()) {
                throw new LogicException(
                    [ 'The `scale` should be passed if `num` has fractional part', $num ]
                );
            }
        }

        $_scale = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum);

        $result = bcpow(
            $bcnum->getValue(),
            (string) $exponent,
            $_scale
        );

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }


    public function bcsqrt($num, int $scale) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum, $num)) {
            throw new LogicException(
                [ 'The `num` should be valid Bcnumber', $num ]
            );
        }

        $_scale = $this->scale_max($scale);

        $result = bcsqrt(
            $bcnum->getValue(),
            $_scale
        );

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }


    /**
     * > Greatest Common Divisor
     */
    public function bcgcd($num1, $num2) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnum($bcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $num1Abs = $bcnum1->getAbsolute();
        $num2Abs = $bcnum2->getAbsolute();

        while ( $num2Abs !== '0' ) {
            $mod = bcmod($num1Abs, $num2Abs, 0);

            $num1Abs = $num2Abs;
            $num2Abs = $mod;
        }

        $result = $gcd = $num1Abs;

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }

    /**
     * > Lowest Common Multiplier
     */
    public function bclcm($num1, $num2) : Bcnumber
    {
        if (! $this->type_bcnum($bcnum1, $num1)) {
            throw new LogicException(
                [ 'The `num1` should be valid Bcnumber', $num1 ]
            );
        }

        if (! $this->type_bcnum($bcnum2, $num2)) {
            throw new LogicException(
                [ 'The `num2` should be valid Bcnumber', $num2 ]
            );
        }

        $num1Abs = $bcnum1->getAbsolute();
        $num2Abs = $bcnum2->getAbsolute();

        $mul = bcmul($num1Abs, $num2Abs, 0);

        $bcGcd = $this->bcgcd($num1Abs, $num2Abs);

        $gcdAbs = $bcGcd->getAbsolute();

        $result = $lcm = bcdiv($mul, $gcdAbs, 0);

        $this->type_bcnum($bcresult, $result);

        return $bcresult;
    }
}
