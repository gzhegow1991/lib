<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Exception\LogicException;


class ItertoolsModule
{
    /**
     * > reversed([ 'A', 'B', 'C' ]) --> C B A
     */
    public function reversed_it(iterable $it) : \Generator
    {
        $reversed = [];
        foreach ( $it as $key => $item ) {
            $reversed[] = [ $key, $item ];
        }

        foreach ( array_reverse($reversed) as [ $key, $item ] ) {
            yield $key => $item;
        }
    }


    /**
     * > range(0, 2) --> 0 1 2
     * > range(2, 0, -1) --> 2 1 0
     *
     * @param int|float|string $start
     * @param int|float|string $end
     * @param int|float|null   $step
     */
    public function range_it($start, $end, $step = null) : \Generator
    {
        $step = $step ?? 1;

        if (! (false
            || ($isStringStart = is_string($start))
            || is_int($start)
            || is_float($start)
        )) {
            throw new LogicException(
                [ 'The `start` should be an int|float|string', $start ]
            );
        }

        if (! (false
            || ($isStringEnd = is_string($end))
            || is_int($end)
            || is_float($end)
        )) {
            throw new LogicException(
                [ 'The `end` should be an int|float|string', $end ]
            );
        }

        if (! (false
            || ($isFloatStep = is_float($step))
            || is_int($step)
        )) {
            throw new LogicException(
                [ 'The `step` should be an int|float', $step ]
            );
        }

        $_step = $step;

        $intStep = (int) $step;
        $floatStep = (float) $step;

        if ($floatStep === (float) $intStep) {
            $_step = $intStep;

            $isFloatStep = false;
        }

        if (0 == $_step) {
            return;
        }

        $isModeString = ($isStringStart || $isStringEnd);

        if ($isModeString && $isFloatStep) {
            throw new LogicException(
                [ 'The `step` should be an integer if `start` or `end` are strings', $step ]
            );
        }

        $_start = $start;
        $_end = $end;

        if ($isModeString) {
            if (! $isStringStart) {
                $_start = (string) $_start;
            }

            if (! $isStringEnd) {
                $_end = (string) $_end;
            }

        } else {
            $intStart = (int) $start;
            $intEnd = (int) $end;

            $floatStart = (float) $start;
            $floatEnd = (float) $end;

            if ($floatStart === (float) $intStart) {
                $_start = $intStart;

            } else {
                $_start = $floatStart;
            }

            if ($floatEnd === (float) $intEnd) {
                $_end = $intEnd;

            } else {
                $_end = $floatEnd;
            }
        }

        $isReverse = $_step < 0;

        $i = $_start;

        if ($isReverse) {
            while ( $i >= $_end ) {
                yield $i;

                if ($isModeString) {
                    if ($i === $_start) {
                        break;
                    }

                    for ( $ii = 0; $ii < $_step; $ii++ ) {
                        $i--;
                    }

                } else {
                    $i += $_step;
                }
            }

        } else {
            while ( $i <= $_end ) {
                yield $i;

                if ($isModeString) {
                    if ($i === $_end) {
                        break;
                    }

                    for ( $ii = 0; $ii < $_step; $ii++ ) {
                        $i++;
                    }

                } else {
                    $i += $_step;
                }
            }
        }
    }


    /**
     * > product([ 'A', 'B', 'C', 'D' ], [ 'x', 'y' ]) --> Ax Ay Bx By Cx Cy Dx Dy
     */
    public function product_it(iterable ...$iterables) : \Generator
    {
        $pools = [];
        foreach ( $iterables as $i => $iterable ) {
            foreach ( $iterable as $ii => $v ) {
                $pools[ $i ][ $ii ] = $v;
            }
        }

        $result = [ [] ];
        foreach ( $pools as $pool ) {
            $resultCurrent = [];

            foreach ( $result as $x ) {
                foreach ( $pool as $y ) {
                    $resultCurrent[] = array_merge($x, [ $y ]);
                }
            }

            $result = $resultCurrent;
        }

        foreach ( $result as $item ) {
            yield $item;
        }
    }

    /**
     * > product_repeat(1, [ 'A', 'B', 'C', 'D' ], [ 'x', 'y' ]) --> Ax Ay Bx By Cx Cy Dx Dy
     * > product_repeat(3, [ 0, 1 ]) --> 000 001 010 011 100 101 110 111
     */
    public function product_repeat_it(int $repeat, iterable ...$iterables) : \Generator
    {
        if ($repeat < 1) {
            throw new LogicException(
                [ 'The `repeat` should be positive integer', $repeat ]
            );
        }

        $pools = [];
        foreach ( $iterables as $i => $iterable ) {
            foreach ( $iterable as $ii => $v ) {
                $pools[ $i ][ $ii ] = $v;
            }
        }

        $list = [];
        for ( $i = 0; $i < $repeat; $i++ ) {
            $list[] = $pools;
        }

        $pools = array_merge(...$list);

        $result = [ [] ];
        foreach ( $pools as $pool ) {
            $resultCurrent = [];

            foreach ( $result as $x ) {
                foreach ( $pool as $y ) {
                    $resultCurrent[] = array_merge($x, [ $y ]);
                }
            }

            $result = $resultCurrent;
        }

        foreach ( $result as $item ) {
            yield $item;
        }
    }


    /**
     * > combinations_unique([ 'A', 'B', 'C', 'D' ], 2) --> AB AC AD BC BD CD
     * > combinations_unique([ 0, 1, 2, 3 ], 3) --> 012 013 023 123
     */
    public function combinations_unique_it(iterable $it, int $len) : ?\Generator
    {
        $pool = [];
        foreach ( $it as $v ) {
            $pool[] = $v;
        }

        $size = count($pool);

        if ($len > $size) {
            return;
        }

        $iMax = ($len - 1);
        $row = [];
        $indexes = [];
        for ( $i = 0; $i <= $iMax; $i++ ) {
            $row[] = $pool[ $i ];
            $indexes[] = $i;
        }

        yield $row;

        while ( true ) {
            $found = null;

            $iMax = ($len - 1);
            // foreach ( $this->range_it(($len - 1), 0, -1) as $i ) {
            for ( $i = $iMax; $i >= 0; $i-- ) {
                if ($indexes[ $i ] !== $i + $size - $len) {
                    $found = $i;
                    break;
                }
            }

            if (null === $found) {
                return;
            }

            $i = $found;

            $indexes[ $i ] += 1;

            $iMin = ($i + 1);
            $iMax = ($len - 1);
            // foreach ( $this->range_it(($i + 1), ($len - 1)) as $j ) {
            for ( $j = $iMin; $j <= $iMax; $j++ ) {
                $indexes[ $j ] = $indexes[ $j - 1 ] + 1;
            }

            $row = [];
            foreach ( $indexes as $i ) {
                $row[] = $pool[ $i ];
            }

            yield $row;
        }
    }

    /**
     * > combinations_all([ 'A', 'B', 'C' ], 2) --> AA AB AC BB BC CC
     * > combinations_all([ 0, 1, 2, 3 ], 3) --> 000 001 002 003 011 012 013 022 023 033 111 112 113 122 123 133 222 223 233 333
     */
    public function combinations_all_it(iterable $it, int $len) : ?\Generator
    {
        $pool = [];
        foreach ( $it as $v ) {
            $pool[] = $v;
        }

        $size = count($pool);

        if ((0 === $size) && $len) {
            return;
        }

        $iMax = ($len - 1);
        $row = [];
        $indices = [];
        for ( $i = 0; $i <= $iMax; $i++ ) {
            $row[] = $pool[ 0 ];
            $indices[] = 0;
        }

        yield $row;

        while ( true ) {
            $found = null;

            $iMax = ($len - 1);
            // foreach ( $this->range_it(($len - 1), 0, -1) as $i ) {
            for ( $i = $iMax; $i >= 0; $i-- ) {
                if ($indices[ $i ] !== ($size - 1)) {
                    $found = $i;

                    break;
                }
            }

            if (null === $found) {
                return;
            }

            $i = $found;

            $iMax = ($len - $i - 1);
            $replace = [];
            // foreach ( $this->range_it(0, ($len - $i - 1), 1) as $i ) {
            for ( $ii = 0; $ii <= $iMax; $ii++ ) {
                $replace[] = $indices[ $i ] + 1;
            }

            array_splice($indices, $i, count($indices), $replace);

            $row = [];
            foreach ( $indices as $i ) {
                $row[] = $pool[ $i ];
            }

            yield $row;
        }
    }


    /**
     * > permutations([ 'A', 'B', 'C' ]) --> ABC ACB BAC BCA CAB CBA
     * > permutations([ 'A', 'B', 'C', 'D' ], 2) --> AB AC AD BA BC BD CA CB CD DA DB DC
     */
    public function permutations_it(iterable $it, ?int $len = null) : \Generator
    {
        $pool = [];
        foreach ( $it as $v ) {
            $pool[] = $v;
        }
        $poolSize = count($pool);

        $len = $len ?? $poolSize;

        if ($len > $poolSize) {
            return;
        }

        $iMax = $poolSize - 1;
        $indices = [];
        for ( $i = 0; $i <= $iMax; $i++ ) {
            $indices[] = $i;
        }

        $row = [];
        foreach ( array_slice($indices, 0, $len) as $i ) {
            $row[] = $pool[ $i ];
        }

        yield $row;

        $iMax = $poolSize;
        $iMin = ($poolSize - $len - 1);
        $cycles = [];
        for ( $i = $iMax; $i >= $iMin; $i-- ) {
            $cycles[] = $i;
        }

        while ( $poolSize ) {
            $found = null;
            $iMax = ($len - 1);
            $iMin = 0;
            for ( $i = $iMax; $i >= $iMin; $i-- ) {
                $cycles[ $i ] -= 1;

                if ($cycles[ $i ] === 0) {
                    array_splice(
                        $indices, $i, count($indices),
                        array_merge(
                            array_slice($indices, $i + 1),
                            array_slice($indices, $i, 1)
                        )
                    );

                    $cycles[ $i ] = $poolSize - $i;

                } else {
                    $j = $cycles[ $i ];

                    [
                        $indices[ $i ],
                        $indices[ count($indices) - $j ],
                    ] = [
                        $indices[ count($indices) - $j ],
                        $indices[ $i ],
                    ];

                    $row = [];
                    foreach ( array_slice($indices, 0, $len) as $ii ) {
                        $row[] = $pool[ $ii ];
                    }

                    yield $row;

                    $found = $i;

                    break;
                }
            }

            if (null === $found) {
                return;
            }
        }
    }
}
