<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\LogicException;


trait BcTrait
{
    public static function bc_scale_limit(int $scale = null) : int
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

    /**
     * @param (int|float|string)[] ...$numbers
     *
     * @return int[]
     */
    public static function bc_scales(...$numbers) : array
    {
        $scales = [];

        foreach ( $numbers as $i => $number ) {
            if (null === ($_number = static::parse_numeric($number))) {
                throw new LogicException(
                    [ 'Each of `numbers` should be numeric', $number, $i ]
                );
            }

            $frac = static::bc_frac($_number);

            $scaleFrac = strlen($frac);

            $scales[] = $scaleFrac;
        }

        return $scales;
    }

    public static function bc_scale_min(int $scale = null, ...$numbers) : int
    {
        $scales = [];

        $scaleLimit = static::bc_scale_limit();

        if (null !== $scale) {
            $_scale = null
                ?? static::parse_int_non_negative($scale)
                ?? static::php_throw([ 'The `scale` must me non negative integer', $scale ]);

            $devnull = null
                ?? static::php_cmp($_scale, $scaleLimit, [ -1, 0 ])
                ?? static::php_throw([ 'The `scale` should be less than or equal `scaleLimit`', $scaleLimit, $scale ]);

            $scales[] = $_scale;
        }

        $scales = array_merge(
            $scales,
            static::bc_scales(...$numbers)
        );

        $scaleMin = min($scales);

        $devnull = null
            ?? static::php_cmp($scaleMin, $scaleLimit, [ -1, 0 ])
            ?? static::php_throw([ 'Scale is bigger than allowed maximum', $scaleMin, $scaleLimit ]);

        return $scaleMin;
    }

    public static function bc_scale_max(int $scale = null, ...$numbers) : int
    {
        $scales = [];

        $scaleLimit = static::bc_scale_limit();

        if (null !== $scale) {
            $_scale = null
                ?? static::parse_int_non_negative($scale)
                ?? static::php_throw([ 'The `scale` must me non negative integer', $scale ]);

            $devnull = null
                ?? static::php_cmp($_scale, $scaleLimit, [ -1, 0 ])
                ?? static::php_throw([ 'The `scale` should be less than or equal `scaleLimit`', $scaleLimit, $scale ]);

            $scales[] = $_scale;
        }

        $scales = array_merge(
            $scales,
            static::bc_scales(...$numbers)
        );

        $scaleMax = max($scales);

        $devnull = null
            ?? static::php_cmp($scaleMax, $scaleLimit, [ -1, 0 ])
            ?? static::php_throw([ 'Scale is bigger than allowed maximum', $scaleMax, $scaleLimit ]);

        return $scaleMax;
    }


    public static function bc_frac($number) : string
    {
        $_number = null
            ?? static::parse_numeric($number)
            ?? static::php_throw([ 'The `number` should be number', $number ]);

        $frac = '';
        if (false !== ($pos = strrchr($_number, '.'))) {
            $frac = substr($pos, 1);
        }

        return $frac;
    }


    public static function bc_moneyround($number, int $scale = null) : string
    {
        $_number = static::parse_bcnum($number, $scaleParsed);
        $_scale = $scale ?? $scaleParsed;

        $numberMinus = '-' === $_number[ 0 ];
        $numberAbs = $numberMinus ? substr($_number, 1) : $_number;
        $numberAbsCut = bcadd($numberAbs, 0, $_scale);

        if (! $scaleParsed) {
            $bcmoneyround = $_number;

        } else {
            $bonus = bccomp($numberAbs, $numberAbsCut, $scaleParsed)
                ? (1 / pow(10, $_scale))
                : 0;

            $bcmoneyround = bcadd($numberAbsCut, $bonus, $_scale);
        }

        if ($numberMinus) {
            $bcmoneyround = '-' . $bcmoneyround;
        }

        return $bcmoneyround;
    }


    public static function bc_base_convert_floor(
        $floor,
        string $baseCharsTo, string $baseCharsFrom = null,
        int $baseShiftTo = null, int $baseShiftFrom = null
    ) : string
    {
        $baseCharsFrom = $baseCharsFrom ?? '0123456789';

        $_floor = null
            ?? static::parse_floor($floor)
            ?? static::php_throw([ 'The `floor` should be valid floor part', $floor ]);

        $_baseCharsTo = null
            ?? static::parse_alphabet($baseCharsTo)
            ?? static::php_throw([ 'The `baseCharsTo` should be valid alphabet', $baseCharsTo ]);

        $_baseCharsFrom = null
            ?? static::parse_alphabet($baseCharsFrom)
            ?? static::php_throw([ 'The `baseCharsFrom` should be valid alphabet', $baseCharsFrom ]);

        $fnStrlen = static::mbfunc('strlen');
        $fnStrSplit = static::mbfunc('str_split');
        $fnSubstr = static::mbfunc('substr');

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
            if (0 > bccomp(bcadd($div, $baseShiftTo, 0), 0, 0)) {
                throw new LogicException(
                    [ 'Unable to convert cause of `baseShiftTo`', $div ]
                );
            }

            do {
                $div = bcadd($div, $baseShiftTo, 0);

                $mod = bcmod($div, $baseTo, 0);
                $div = bcdiv($div, $baseTo, 0);

                $result[] = $fnSubstr($_baseCharsTo, (int) $mod, 1);
            } while ( bccomp($div, 0, 1) );

            $result = implode('', array_reverse($result));

        } elseif ($_baseCharsTo === $baseChars10) {
            $result = '0';

            for ( $i = 1; $i <= $len; $i++ ) {
                $idx = $baseCharsFromIndex[ $fnSubstr($_floor, $i - 1, 1) ];
                $idx = bcsub($idx, $baseShiftFrom, 0);

                $pow = bcpow($baseFrom, $len - $i, 0);
                $sum = bcmul($idx, $pow, 0);

                $result = bcadd($result, $sum, 0);
            }

        } else {
            $result = $_floor;
            $result = static::bc_base_convert_floor(
                $result,
                $baseChars10, $_baseCharsFrom,
                0, $baseShiftFrom
            );
            $result = static::bc_base_convert_frac(
                $result,
                $_baseCharsTo, $baseChars10,
                $baseShiftTo
            );
        }

        return $result;
    }

    public static function bc_base_convert_frac(
        $frac,
        string $baseCharsTo, string $baseCharsFrom = null,
        int $scale = null
    ) : string
    {
        $baseCharsFrom = $baseCharsFrom ?? '0123456789';

        $_frac = null
            ?? static::parse_frac($frac)
            ?? static::php_throw([ 'The `frac` should be valid fractional part', $frac ]);

        $_baseCharsTo = null
            ?? static::parse_alphabet($baseCharsTo)
            ?? static::php_throw([ 'The `baseCharsTo` should be valid alphabet', $baseCharsTo ]);

        $_baseCharsFrom = null
            ?? static::parse_alphabet($baseCharsFrom)
            ?? static::php_throw([ 'The `baseCharsFrom` should be valid alphabet', $baseCharsFrom ]);

        $_frac = ltrim($_frac, '.');

        $fnStrlen = static::mbfunc('strlen');
        $fnStrSplit = static::mbfunc('str_split');
        $fnSubstr = static::mbfunc('substr');

        $len = $fnStrlen($_frac);
        if (! $len) {
            return '';
        }

        $_scale = static::bc_scale_min($scale, '0.' . $_frac);

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

            $mul = bcadd('0.' . $_frac, 0, $_scale);

            $limit = $_scale;
            while ( $limit-- ) {
                $mul = bcmul($mul, $baseTo, $_scale);
                $floor = bcadd($mul, 0, 0);

                $mul = bcsub($mul, $floor, $_scale);

                $result[] = $fnSubstr($_baseCharsTo, (int) $floor, 1);

                if (0 === bccomp($mul, 0, $_scale)) break;
            }

            $result = implode('', $result);

        } elseif ($_baseCharsTo === $baseChars10) {
            $result = '0';

            for ( $i = 1; $i <= $len; $i++ ) {
                $idx = $baseCharsFromIndex[ $fnSubstr($_frac, $i - 1, 1) ];

                $pow = bcpow($baseFrom, -$i, $_scale);
                $sum = bcmul($idx, $pow, $_scale);

                $result = bcadd($result, $sum, $_scale);
            }

            $result = explode('.', $result, 2)[ 1 ] ?? '0';

        } else {
            $result = $_frac;
            $result = static::bc_base_convert_frac($result, $baseChars10, $_baseCharsFrom);
            $result = static::bc_base_convert_frac($result, $_baseCharsTo, $baseChars10);
        }

        return '.' . $result;
    }

    public static function bc_base_convert(
        $num,
        string $baseCharsTo, string $baseCharsFrom = null,
        int $scale = null,
        int $baseShiftTo = null, int $baseShiftFrom = null
    ) : string
    {
        $_num = null
            ?? static::parse_numeric($num)
            ?? static::php_throw([ 'The `num` should be valid numeric', $num ]);

        $minus = ('-' === $_num[ 0 ]) ? '-' : '';

        $numAbs = ltrim($_num, '-');
        [ $numFloor, $numFrac ] = explode('.', $numAbs, 2) + [ '0', '' ];

        $resultFloor = '';
        if ('' !== $numFloor) {
            $resultFloor = static::bc_base_convert_floor(
                $numFloor,
                $baseCharsTo, $baseCharsFrom,
                $baseShiftTo, $baseShiftFrom
            );
        }

        $resultFrac = '';
        if ('' !== $numFrac) {
            $resultFrac = static::bc_base_convert_frac(
                $numFrac,
                $baseCharsTo, $baseCharsFrom,
                $scale
            );
        }

        return "{$minus}{$resultFloor}{$resultFrac}";
    }
}
