<?php

declare(strict_types=1);

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class BcmathModule2
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


    public function scale_limit_static(int $scaleLimit = null) : int
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


    public function parse_bcnum($value, int &$scaleParsed = null) : ?Bcnumber
    {
        if ($value instanceof Bcnumber) {
            return $value;
        }

        if (null === ($_value = Lib::parse()->numeric($value))) {
            return null;
        }

        // > gzhegow, 0.000022 becomes 2.2E-5, so you need to pass formatted string instead of float
        if (false !== stripos($_value, 'e')) {
            return null;
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
        ] = explode(_PARSE_DECIMAL_POINT, $valueAbs) + [ '0', '' ];

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

        return $bcnum;
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
            if (null === ($_number = Lib::parse()->numeric($number))) {
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

        $scaleLimit = $this->scale_limit_static();

        if (null !== $scale) {
            $_scale = null
                ?? Lib::parse()->int_non_negative($scale)
                ?? Lib::php()->throw([ 'The `scale` must be non negative integer', $scale ]);

            $scales[] = $_scale;
        }

        if (count($numbers)) {
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

        $scaleLimit = $this->scale_limit_static();

        if (null !== $scale) {
            $_scale = null
                ?? Lib::parse()->int_non_negative($scale)
                ?? Lib::php()->throw([ 'The `scale` must be non negative integer', $scale ]);

            $scales[] = $_scale;
        }

        if (count($numbers)) {
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
        if (null === ($bcnum = $this->parse_bcnum($num))) {
            throw new LogicException(
                [ 'The `num` should be valid bcmath', $num ]
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

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }

    public function bcfloor($num, int $scale = 0) : Bcnumber
    {
        if (null === ($bcnum = $this->parse_bcnum($num))) {
            throw new LogicException(
                [ 'The `num` should be valid bcmath', $num ]
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

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }

    public function bcround($num, int $scale = 0) : Bcnumber
    {
        if (null === ($bcnum = $this->parse_bcnum($num))) {
            throw new LogicException(
                [ 'The `num` should be valid bcmath', $num ]
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

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }


    public function bcmoneyceil($num, int $scale = 0) : Bcnumber
    {
        if (null === ($bcnum = $this->parse_bcnum($num))) {
            throw new LogicException(
                [ 'The `num` should be valid bcmath', $num ]
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

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }

    public function bcmoneyfloor($num, int $scale = 0) : Bcnumber
    {
        if (null === ($bcnum = $this->parse_bcnum($num))) {
            throw new LogicException(
                [ 'The `num` should be valid bcmath', $num ]
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

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }


    public function bcfrac($number, int &$scaleParsed = null) : ?string
    {
        $scaleParsed = null;

        $frac = null;

        $_number = null
            ?? Lib::parse()->numeric($number)
            ?? Lib::php()->throw([ 'The `number` should be number', $number ]);

        $scaleParsed = 0;

        $frac = strrchr($_number, _PARSE_DECIMAL_POINT);

        if (false !== $frac) {
            $scaleParsed = strlen($frac) - 1;
        }

        return $frac;
    }


    public function bccomp($num1, $num2, int $scale = null) : int
    {
        $bcnum1 = null
            ?? $this->parse_bcnum($num1)
            ?? Lib::php()->throw([ 'The `num1` should be valid bcmath', $num1 ]);

        $bcnum2 = null
            ?? $this->parse_bcnum($num2)
            ?? Lib::php()->throw([ 'The `num2` should be valid bcmath', $num2 ]);

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
        $bcnum1 = null
            ?? $this->parse_bcnum($num1)
            ?? Lib::php()->throw([ 'The `num1` should be valid bcmath', $num1 ]);

        $bcnum2 = null
            ?? $this->parse_bcnum($num2)
            ?? Lib::php()->throw([ 'The `num2` should be valid bcmath', $num2 ]);

        $_scale = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum1, $bcnum2);

        $result = bcadd(
            $bcnum1->getValue(),
            $bcnum2->getValue(),
            $_scale
        );

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }

    public function bcsub($num1, $num2, int $scale = null) : Bcnumber
    {
        $bcnum1 = null
            ?? $this->parse_bcnum($num1)
            ?? Lib::php()->throw([ 'The `num1` should be valid bcmath', $num1 ]);

        $bcnum2 = null
            ?? $this->parse_bcnum($num2)
            ?? Lib::php()->throw([ 'The `num2` should be valid bcmath', $num2 ]);

        $_scale = null
            ?? $this->scale_max($scale)
            ?? $this->scale_max(null, $bcnum1, $bcnum2);

        $result = bcsub(
            $bcnum1->getValue(),
            $bcnum2->getValue(),
            $_scale
        );

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }

    public function bcmul($num1, $num2, int $scale = null) : Bcnumber
    {
        $bcnum1 = null
            ?? $this->parse_bcnum($num1)
            ?? Lib::php()->throw([ 'The `num1` should be valid bcmath', $num1 ]);

        $bcnum2 = null
            ?? $this->parse_bcnum($num2)
            ?? Lib::php()->throw([ 'The `num2` should be valid bcmath', $num2 ]);

        if (null === $scale) {
            if ($bcnum1->getFractionalPart() && $bcnum2->getFractionalPart()) {
                throw new LogicException(
                    [ 'The `scale` should be defined if both arguments have fractional parts', $num1, $num2 ]
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

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }

    /**
     * > gzhegow, поскольку при делении число дробных знаков может увелится, параметр $scale сделан обязательным
     */
    public function bcdiv($num1, $num2, int $scale) : Bcnumber
    {
        $bcnum1 = null
            ?? $this->parse_bcnum($num1)
            ?? Lib::php()->throw([ 'The `num1` should be valid bcmath', $num1 ]);

        $bcnum2 = null
            ?? $this->parse_bcnum($num2)
            ?? Lib::php()->throw([ 'The `num2` should be valid bcmath', $num2 ]);

        $_scale = $this->scale_max($scale);

        $result = bcdiv(
            $bcnum1->getValue(),
            $bcnum2->getValue(),
            $_scale
        );

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }


    /**
     * > gzhegow, оригинальная функция ожидает три аргумента, но это противоречит самой идее получения остатка от деления
     * > перед взятием остатка дробная часть обоих чисел отбрасывается
     */
    public function bcmod($num1, $num2) : Bcnumber
    {
        $bcnum1 = null
            ?? $this->parse_bcnum($num1)
            ?? Lib::php()->throw([ 'The `num1` should be valid bcmath', $num1 ]);

        $bcnum2 = null
            ?? $this->parse_bcnum($num2)
            ?? Lib::php()->throw([ 'The `num2` should be valid bcmath', $num2 ]);

        $result = bcmod(
            $bcnum1->getInteger(),
            $bcnum2->getInteger(),
            0
        );

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }


    public function bcpow($num, int $exponent, int $scale = null) : Bcnumber
    {
        $bcnum = null
            ?? $this->parse_bcnum($num)
            ?? Lib::php()->throw([ 'The `num` should be valid bcmath', $num ]);

        if (null === $scale) {
            if ($bcnum->getFractionalPart()) {
                throw new LogicException(
                    [ 'The `scale` should be defined if `num` has fractional part', $num ]
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

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }


    public function bcsqrt($num, int $scale) : Bcnumber
    {
        $bcnum = null
            ?? $this->parse_bcnum($num)
            ?? Lib::php()->throw([ 'The `num` should be valid bcmath', $num ]);

        $_scale = $this->scale_max($scale);

        $result = bcsqrt(
            $bcnum->getValue(),
            $_scale
        );

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }


    public function bcgcd($num1, $num2) : Bcnumber
    {
        $bcNum1 = null
            ?? $this->parse_bcnum($num1)
            ?? Lib::php()->throw([ 'The `a` should be valid bcmath', $num1 ]);

        $bcNum2 = null
            ?? $this->parse_bcnum($num2)
            ?? Lib::php()->throw([ 'The `b` should be valid bcmath', $num2 ]);

        $bcNum1Abs = $bcNum1->getAbsolute();
        $bcNum2Abs = $bcNum2->getAbsolute();

        while ( $bcNum2Abs !== '0' ) {
            $mod = bcmod($bcNum1Abs, $bcNum2Abs, 0);

            $bcNum1Abs = $bcNum2Abs;
            $bcNum2Abs = $mod;
        }

        $bcgcd = $this->parse_bcnum($bcNum1Abs);

        return $bcgcd;
    }

    public function bclcm($num1, $num2) : Bcnumber
    {
        $bcNum1 = null
            ?? $this->parse_bcnum($num1)
            ?? Lib::php()->throw([ 'The `a` should be valid bcmath', $num1 ]);

        $bcNum2 = null
            ?? $this->parse_bcnum($num2)
            ?? Lib::php()->throw([ 'The `b` should be valid bcmath', $num2 ]);

        $bcNum1Abs = $bcNum1->getAbsolute();
        $bcNum2Abs = $bcNum2->getAbsolute();

        $mul = bcmul($bcNum1Abs, $bcNum2Abs, 0);

        $bcGcd = $this->bcgcd($bcNum1Abs, $bcNum2Abs);
        $bcGcdAbs = $bcGcd->getAbsolute();

        $lcm = bcdiv($mul, $bcGcdAbs, 0);

        $bcLcm = $this->parse_bcnum($lcm);

        return $bcLcm;
    }
}
