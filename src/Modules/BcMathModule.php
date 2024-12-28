<?php

declare(strict_types=1);

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\BcMath\BcNumber;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class BcMathModule
{
    public function __construct()
    {
        if (! extension_loaded('bcmath')) {
            throw new RuntimeException(
                'Missing PHP extension: bcmath'
            );
        }
    }


    public function scale_limit_static(int $scale = null) : int
    {
        static $current;

        $current = $current ?? 10;

        if (null !== $scale) {
            $scale = ($scale > 0) ? $scale : 0;

            $last = $current;

            $current = $scale;

            return $last;
        }

        return $current;
    }


    public function parse_bcnum($value, int &$scaleParsed = null) : ?BcNumber
    {
        $scaleParsed = null;

        if ($value instanceof BcNumber) {
            return $value;
        }

        if (null === ($_value = Lib::parse()->numeric($value))) {
            return null;
        }

        // > gzhegow, parse()->numeric() converts to string
        if (in_array($_value, [ 'NAN', 'INF', '-INF' ])) {
            return null;
        }

        // > gzhegow, 0.000022 becomes 2.2E-5, so you need to pass formatted string instead of float
        if (false !== strpos(strtolower($_value), 'e')) {
            return null;
        }

        $valueMinus = ('-' === $_value[ 0 ]);
        $valueAbs = $valueMinus ? substr($_value, 1) : $_value;
        [ $valueAbsFloor, $valueAbsFrac ] = explode('.', $valueAbs) + [ 1 => '' ];

        $valueAbsFloor = ltrim($valueAbsFloor, '0');  // 0000.1
        $valueAbsFrac = rtrim($valueAbsFrac, '0');    // 1.0000

        $scaleParsed = strlen($valueAbsFrac);

        $minus = (($valueMinus && ($valueAbs != 0)) ? '-' : '');
        $integral = (('' !== $valueAbsFloor) ? $valueAbsFloor : "0");
        $fractional = (('' !== $valueAbsFrac) ? ".{$valueAbsFrac}" : "");

        $bcnum = new BcNumber(
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


    public function bcceil($num, int $scale = 0) : BcNumber
    {
        if (null === ($bcnum = $this->parse_bcnum($num))) {
            throw new LogicException(
                [ 'The `num` should be valid bcmath', $num ]
            );
        }

        $fractional = $bcnum->getFractional();

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

    public function bcfloor($num, int $scale = 0) : BcNumber
    {
        if (null === ($bcnum = $this->parse_bcnum($num))) {
            throw new LogicException(
                [ 'The `num` should be valid bcmath', $num ]
            );
        }

        $fractional = $bcnum->getFractional();

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

    public function bcround($num, int $scale = 0) : BcNumber
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


    public function bcmoneyceil($num, int $scale = 0) : BcNumber
    {
        if (null === ($bcnum = $this->parse_bcnum($num))) {
            throw new LogicException(
                [ 'The `num` should be valid bcmath', $num ]
            );
        }

        $fractional = $bcnum->getFractional();

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

    public function bcmoneyfloor($num, int $scale = 0) : BcNumber
    {
        if (null === ($bcnum = $this->parse_bcnum($num))) {
            throw new LogicException(
                [ 'The `num` should be valid bcmath', $num ]
            );
        }

        $fractional = $bcnum->getFractional();

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

        $frac = strrchr($_number, '.');

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


    public function bcadd($num1, $num2, int $scale = null) : BcNumber
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

    public function bcsub($num1, $num2, int $scale = null) : BcNumber
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

    public function bcmul($num1, $num2, int $scale = null) : BcNumber
    {
        $bcnum1 = null
            ?? $this->parse_bcnum($num1)
            ?? Lib::php()->throw([ 'The `num1` should be valid bcmath', $num1 ]);

        $bcnum2 = null
            ?? $this->parse_bcnum($num2)
            ?? Lib::php()->throw([ 'The `num2` should be valid bcmath', $num2 ]);

        if (null === $scale) {
            if ($bcnum1->getFractional() && $bcnum2->getFractional()) {
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
    public function bcdiv($num1, $num2, int $scale) : BcNumber
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
    public function bcmod($num1, $num2) : BcNumber
    {
        $bcnum1 = null
            ?? $this->parse_bcnum($num1)
            ?? Lib::php()->throw([ 'The `num1` should be valid bcmath', $num1 ]);

        $bcnum2 = null
            ?? $this->parse_bcnum($num2)
            ?? Lib::php()->throw([ 'The `num2` should be valid bcmath', $num2 ]);

        $result = bcmod(
            $bcnum1->getFloor(),
            $bcnum2->getFloor(),
            0
        );

        $bcresult = $this->parse_bcnum($result);

        return $bcresult;
    }


    public function bcpow($num, int $exponent, int $scale = null) : BcNumber
    {
        $bcnum = null
            ?? $this->parse_bcnum($num)
            ?? Lib::php()->throw([ 'The `num` should be valid bcmath', $num ]);

        if (null === $scale) {
            if ($bcnum->getFractional()) {
                throw new LogicException(
                    [ 'The `scale` should be defined if number has fractional part', $num ]
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


    public function bcsqrt($num, int $scale) : BcNumber
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


    public function base_convert(
        $num,
        string $baseCharsTo, string $baseCharsFrom = null,
        int $scale = null,
        int $baseShiftTo = null, int $baseShiftFrom = null
    ) : string
    {
        $_num = null
            ?? Lib::parse()->numeric($num)
            ?? Lib::php()->throw([ 'The `num` should be valid numeric', $num ]);

        $minus = ('-' === $_num[ 0 ]) ? '-' : '';

        $numAbs = ltrim($_num, '-');
        [ $numFloor, $numFrac ] = explode('.', $numAbs, 2) + [ '0', '' ];

        $resultFloor = '';
        if ('' !== $numFloor) {
            $resultFloor = $this->base_convert_floor(
                $numFloor,
                $baseCharsTo, $baseCharsFrom,
                $baseShiftTo, $baseShiftFrom
            );
        }

        $resultFrac = '';
        if ('' !== $numFrac) {
            $resultFrac = $this->base_convert_frac(
                $numFrac,
                $baseCharsTo, $baseCharsFrom,
                $scale
            );
        }

        return "{$minus}{$resultFloor}{$resultFrac}";
    }

    public function base_convert_floor(
        $floor,
        string $baseCharsTo, string $baseCharsFrom = null,
        int $baseShiftTo = null, int $baseShiftFrom = null
    ) : string
    {
        $baseCharsFrom = $baseCharsFrom ?? '0123456789';

        $_floor = null
            ?? Lib::parse()->floor($floor)
            ?? Lib::php()->throw([ 'The `floor` should be valid floor part', $floor ]);

        $_baseCharsTo = null
            ?? Lib::parse()->alphabet($baseCharsTo)
            ?? Lib::php()->throw([ 'The `baseCharsTo` should be valid alphabet', $baseCharsTo ]);

        $_baseCharsFrom = null
            ?? Lib::parse()->alphabet($baseCharsFrom)
            ?? Lib::php()->throw([ 'The `baseCharsFrom` should be valid alphabet', $baseCharsFrom ]);

        $fnStrlen = Lib::str()->mb_func('strlen');
        $fnStrSplit = Lib::str()->mb_func('str_split');
        $fnSubstr = Lib::str()->mb_func('substr');

        $len = $fnStrlen($_floor);
        if (! $len) {
            return '';
        }

        $baseTo = $fnStrlen($_baseCharsTo);
        $baseFrom = $fnStrlen($_baseCharsFrom);

        $baseCharsFromIndex = array_flip($fnStrSplit($_baseCharsFrom));

        $baseChars10 = '0123456789';

        if ($_baseCharsFrom === $_baseCharsTo) {
            return $_floor;

        } elseif ($_baseCharsFrom === $baseChars10) {
            $result = [];

            $div = $_floor;

            $bccomp = bccomp(
                bcadd($div, (string) $baseShiftTo, 0),
                '0',
                0
            );
            if (0 > $bccomp) {
                throw new LogicException(
                    [ 'Unable to convert cause of `baseShiftTo`', $div ]
                );
            }

            do {
                $div = bcadd($div, (string) $baseShiftTo, 0);

                $mod = bcmod($div, (string) $baseTo);
                $div = bcdiv($div, (string) $baseTo, 0);

                $result[] = $fnSubstr($_baseCharsTo, (int) $mod, 1);
            } while ( bccomp($div, '0', 1) );

            $result = implode('', array_reverse($result));

        } elseif ($_baseCharsTo === $baseChars10) {
            $result = '0';

            for ( $i = 1; $i <= $len; $i++ ) {
                $idx = $baseCharsFromIndex[ $fnSubstr($_floor, $i - 1, 1) ];
                $idx = bcsub($idx, (string) $baseShiftFrom, 0);

                $pow = bcpow($baseFrom, (string) ($len - $i), 0);
                $sum = bcmul($idx, $pow, 0);

                $result = bcadd($result, $sum, 0);
            }

        } else {
            $result = $_floor;
            $result = $this->base_convert_floor(
                $result,
                $baseChars10, $_baseCharsFrom,
                0, $baseShiftFrom
            );
            $result = $this->base_convert_frac(
                $result,
                $_baseCharsTo, $baseChars10,
                $baseShiftTo
            );
        }

        return $result;
    }

    public function base_convert_frac(
        $frac,
        string $baseCharsTo, string $baseCharsFrom = null,
        int $scale = null
    ) : string
    {
        $baseCharsFrom = $baseCharsFrom ?? '0123456789';

        $_frac = null
            ?? Lib::parse()->frac($frac)
            ?? Lib::php()->throw([ 'The `frac` should be valid fractional part', $frac ]);

        $_baseCharsTo = null
            ?? Lib::parse()->alphabet($baseCharsTo)
            ?? Lib::php()->throw([ 'The `baseCharsTo` should be valid alphabet', $baseCharsTo ]);

        $_baseCharsFrom = null
            ?? Lib::parse()->alphabet($baseCharsFrom)
            ?? Lib::php()->throw([ 'The `baseCharsFrom` should be valid alphabet', $baseCharsFrom ]);

        $_frac = ltrim($_frac, '.');

        $fnStrlen = Lib::str()->mb_func('strlen');
        $fnStrSplit = Lib::str()->mb_func('str_split');
        $fnSubstr = Lib::str()->mb_func('substr');

        $len = $fnStrlen($_frac);
        if (! $len) {
            return '';
        }

        $_scale = $this->scale_min($scale, '0.' . $_frac);

        $baseTo = $fnStrlen($_baseCharsTo);
        $baseFrom = $fnStrlen($_baseCharsFrom);

        $baseCharsFromIndex = array_flip($fnStrSplit($_baseCharsFrom));

        for ( $i = 0; $i < $len; $i++ ) {
            if (! isset($baseCharsFromIndex[ $fnSubstr($_frac, $i, 1) ])) {
                throw new LogicException(
                    [ 'The `frac` contains char outside `baseCharsFrom`', $_frac[ $i ] ]
                );
            }
        }

        $baseChars10 = '0123456789';

        if ($_baseCharsTo === $_baseCharsFrom) {
            return $_frac;

        } elseif ($_baseCharsFrom === $baseChars10) {
            $result = [];

            $mul = bcadd('0.' . $_frac, '0', $_scale);

            $limit = $_scale;
            while ( $limit-- ) {
                $mul = bcmul($mul, $baseTo, $_scale);
                $floor = bcadd($mul, '0', 0);

                $mul = bcsub($mul, $floor, $_scale);

                $result[] = $fnSubstr($_baseCharsTo, (int) $floor, 1);

                if (0 === bccomp($mul, '0', $_scale)) break;
            }

            $result = implode('', $result);

        } elseif ($_baseCharsTo === $baseChars10) {
            $result = '0';

            for ( $i = 1; $i <= $len; $i++ ) {
                $idx = $baseCharsFromIndex[ $fnSubstr($_frac, $i - 1, 1) ];

                $pow = bcpow($baseFrom, (string) (-$i), $_scale);
                $sum = bcmul($idx, $pow, $_scale);

                $result = bcadd($result, $sum, $_scale);
            }

            $result = explode('.', $result, 2)[ 1 ] ?? '0';

        } else {
            $result = $_frac;
            $result = $this->base_convert_frac($result, $baseChars10, $_baseCharsFrom);
            $result = $this->base_convert_frac($result, $_baseCharsTo, $baseChars10);
        }

        return '.' . $result;
    }
}
