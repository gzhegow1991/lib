<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Exception\LogicException;


class ItertoolsModule
{
    /**
     * reversed([ 'A', 'B', 'C' ]) --> C B A
     *
     * @param iterable $it
     *
     * @return \Generator
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
     * range(0,2) -> 0 1 2
     * range(2,0,-1) -> 2 1 0
     *
     * @param int      $start
     * @param int|null $end
     * @param int|null $step
     *
     * @return \Generator
     */
    public function range_it($start, $end, $step = null) : \Generator
    {
        $step = $step ?? 1;

        if (! (($isStringStart = is_string($start)) || is_int($start) || is_float($start))) {
            throw new LogicException(
                [ 'The `start` should be int|float|string', $start ]
            );
        }

        if (! (($isStringEnd = is_string($end)) || is_int($end) || is_float($end))) {
            throw new LogicException(
                [ 'The `end` should be int|float|string', $end ]
            );
        }

        if (! (($isFloatStep = is_float($step)) || is_int($step))) {
            throw new LogicException(
                [ 'The `step` should be int|float', $step ]
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
                [ 'The `step` should be integer if `start` or `end` are strings', $step ]
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

        while (
            ($isReverse && ($i >= $_end))
            || ($i <= $_end)
        ) {
            yield $i;

            if ($isModeString) {
                if (
                    ($isReverse && ($i === $_start))
                    || ($i === $_end)
                ) {
                    break;
                }

                for ( $ii = 0; $ii < $_step; $ii++ ) {
                    $isReverse
                        ? $i--
                        : $i++;
                }

            } else {
                $i += $_step;
            }
        }
    }


    /**
     * product([ 'A', 'B', 'C', 'D' ], [ 'x', 'y' ]) --> Ax Ay Bx By Cx Cy Dx Dy
     *
     * @param iterable ...$iterables
     *
     * @return \Generator
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
     * product_repeat(3, range(2)) --> 000 001 010 011 100 101 110 111
     *
     * @param null|int $repeat
     * @param iterable ...$iterables
     *
     * @return \Generator
     */
    public function product_repeat_it(int $repeat, iterable ...$iterables) : \Generator
    {
        $repeat = ($repeat > 1) ? $repeat : 1;

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

        yield from $this->product_it(...$pools);
    }


    /**
     * combinations_unique([ 'A', 'B', 'C', 'D' ], 2) --> AB AC AD BC BD CD
     * combinations_unique(range(4), 3) --> 012 013 023 123
     *
     * @param iterable $it
     * @param int      $len
     *
     * @return \Generator
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

        $row = [];
        $indexes = [];
        foreach ( $this->range_it(0, $len - 1) as $i ) {
            $row[] = $pool[ $i ];
            $indexes[] = $i;
        }

        yield $row;

        while ( true ) {
            $found = null;

            foreach ( $this->range_it($len - 1, 0, -1) as $i ) {
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

            foreach ( $this->range_it($i + 1, $len - 1) as $j ) {
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
     * combinations_all([ 'A', 'B', 'C' ], 2) --> AA AB AC BB BC CC
     *
     * @param iterable $it
     * @param int      $len
     *
     * @return \Generator
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

        $row = [];
        $indices = [];
        foreach ( $this->range_it(0, $len - 1) as $i ) {
            $row[] = $pool[ 0 ];
            $indices[] = 0;
        }

        yield $row;

        while ( true ) {
            $found = null;

            foreach ( $this->range_it($len - 1, 0, -1) as $i ) {
                if ($indices[ $i ] !== ($size - 1)) {
                    $found = $i;
                    break;
                }
            }

            if (null === $found) {
                return;
            }

            $i = $found;

            $replace = [];
            foreach ( $this->range_it(0, $len - $i - 1) as $ii ) {
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
     * permutations([ 'A', 'B', 'C', 'D' ], 2) --> AB AC AD BA BC BD CA CB CD DA DB DC
     * permutations(range(3)) --> 012 021 102 120 201 210
     *
     * @param iterable $it
     * @param null|int $len
     *
     * @return \Generator
     */
    public function permutations_it(iterable $it, ?int $len = null) : \Generator
    {
        $pool = [];
        foreach ( $it as $v ) {
            $pool[] = $v;
        }

        $size = count($pool);

        $len = $len ?? $size;

        if ($len > $size) {
            return;
        }

        $indices = [];
        foreach ( $this->range_it(0, $size - 1) as $i ) {
            $indices[] = $i;
        }

        $row = [];
        foreach ( array_slice($indices, 0, $len) as $i ) {
            $row[] = $pool[ $i ];
        }

        yield $row;

        $cycles = iterator_to_array(
            $this->range_it($size, $size - $len - 1, -1)
        );

        while ( $size ) {
            $found = null;
            foreach ( $this->range_it($len - 1, 0, -1) as $i ) {
                $cycles[ $i ] -= 1;

                if ($cycles[ $i ] === 0) {
                    array_splice(
                        $indices, $i, count($indices),
                        array_merge(
                            array_slice($indices, $i + 1),
                            array_slice($indices, $i, 1)
                        )
                    );

                    $cycles[ $i ] = $size - $i;

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
