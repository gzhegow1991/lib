<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class FormatModule
{
    /**
     * @return int|float
     */
    public function bytes_decode(
        string $bytesSize, array $fallback = []
    )
    {
        $result = [];

        $e = null;
        try {
            $result = $this->_bytes_decode($bytesSize);
        }
        catch ( \Throwable $e ) {
        }

        if ([] !== $result) {
            [ $bytesNum ] = $result;

        } elseif ([] !== $fallback) {
            [ $bytesNum ] = $fallback;

        } else {
            throw new RuntimeException(
                [ 'Unable to ' . __FUNCTION__, $bytesSize, $e ]
            );
        }

        return $bytesNum;
    }

    /**
     * @return array{ 0?: int|float }
     */
    protected function _bytes_decode(string $size) : array
    {
        if ('' === $size) {
            throw new LogicException(
                [ 'The `size` should be non-empty string', $size ]
            );
        }

        $strUnitList = [
            [ 'B' => 0 ],
            [ 'K' => 1, 'KB' => 1 ],
            [ 'M' => 2, 'MB' => 2 ],
            [ 'G' => 3, 'GB' => 3 ],
            [ 'T' => 4, 'TB' => 4 ],
            [ 'P' => 5, 'PB' => 5 ],
            [ 'E' => 6, 'EB' => 6 ],
            [ 'Z' => 7, 'ZB' => 7 ],
            [ 'Y' => 8, 'YB' => 8 ],
        ];
        $strUnitList = array_merge(...$strUnitList);

        if (! preg_match($regex = '~^(.*[0-9])([A-Z]{0,2})$~', $size, $matches)) {
            throw new LogicException(
                [ 'The `size` should match regex: ' . $regex, $size ]
            );
        }

        [ , $numUnit, $strUnit ] = $matches;

        if ('' === $strUnit) {
            $strUnit = 'B';
        }

        if (! isset($strUnitList[ $strUnit ])) {
            throw new LogicException(
                [ 'Unknown `strUnit`', $strUnit ]
            );
        }

        $theType = Lib::type();

        if (! $theType->num_positive($number, $numUnit)) {
            throw new LogicException(
                [ 'Invalid `numUnit`', $numUnit ]
            );
        }

        if (0 == $numUnit) {
            $result = [ 0 ];

        } else {
            $bytesNum = $numUnit * pow(1024, $strUnitList[ $strUnit ]);

            $bytesCeil = ceil($bytesNum);

            if ($bytesCeil === false) {
                throw new LogicException(
                    [ 'Unable to ceil', $bytesNum ]
                );
            }

            $theType->int($bytesCeilInt, $bytesCeil);

            $result = [ $bytesCeilInt ?? $bytesCeil ];
        }

        return $result;
    }


    /**
     * @param int|float $bytes
     */
    public function bytes_encode(
        $bytes, array $fallback = [],
        ?int $precision = null, ?int $unitLen = null
    ) : string
    {
        $result = [];

        $e = null;
        try {
            $result = $this->_bytes_encode(
                $bytes,
                $precision, $unitLen
            );
        }
        catch ( \Throwable $e ) {
        }

        if ([] !== $result) {
            [ $bytesSize ] = $result;

        } elseif ([] !== $fallback) {
            [ $bytesSize ] = $fallback;

        } else {
            throw new RuntimeException(
                [ 'Unable to ' . __FUNCTION__, $bytes, $e ]
            );
        }

        return $bytesSize;
    }

    /**
     * @param int|float $bytes
     *
     * @return array{ 0?: string }
     */
    protected function _bytes_encode($bytes, ?int $precision = null, ?int $unitLen = null) : array
    {
        if (! Lib::type()->num_non_negative($number, $bytes)) {
            throw new LogicException(
                [ 'The `bytes` should be non-negative num', $bytes ]
            );
        }

        $precision = $precision ?? 3;
        $unitLen = $unitLen ?? 2;

        if (0 == $number) {
            $result = [ '0B' ];

        } else {
            $strUnitList = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

            $left = $number;

            $pow = floor(log($number) / log(1024));
            $pow = min($pow, count($strUnitList) - 1);

            $left /= pow(1024, $pow);

            $unit = $strUnitList[ $pow ];
            $unit = substr($unit, 0, $unitLen);

            $size = round($left, $precision) . $unit;

            $result = [ $size ];
        }

        return $result;
    }


    /**
     * @return array{0: string, 1: int}
     */
    public function csv_encode_rows(
        array $rows,
        ?string $separator = null,
        ?string $enclosure = null,
        ?string $escape = null,
        ?string $eol = null
    ) : array
    {
        if ([] === $rows) {
            throw new LogicException(
                'The `rows` should be not-empty array'
            );
        }

        $_rows = array_values($rows);

        foreach ( $_rows as $i => $row ) {
            $isNonEmptyArray = is_array($row) && ([] !== $row);

            if (! $isNonEmptyArray) {
                throw new LogicException(
                    [
                        'Each of `rows` should be not-empty array',
                        $row,
                        $i,
                    ]
                );
            }
        }

        $theType = Lib::type();

        if (! $theType->char($separatorString, $separator ?? ';')) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        if (! $theType->char($enclosureString, $enclosure ?? '"')) {
            throw new LogicException(
                [ 'The `enclosure` should be char', $enclosure ]
            );
        }

        if (! $theType->char($escapeString, $escape ?? '\\')) {
            throw new LogicException(
                [ 'The `escape` should be char', $escape ]
            );
        }

        if (! $theType->string_not_empty($eolString, $eol ?? "\n")) {
            throw new LogicException(
                [ 'The `eol` should be non-empty string', $eol ]
            );
        }

        $fputcsvArgs = [ $separatorString, $enclosureString, $escapeString ];

        if (PHP_VERSION > 80100) {
            $fputcsvArgs[] = $eolString;
        }

        $h = fopen('php://temp', 'w');
        $len = 0;

        foreach ( $_rows as $row ) {
            $_row = array_values($row);

            $len += fputcsv($h, $_row, ...$fputcsvArgs);
        }

        rewind($h);
        $content = stream_get_contents($h);
        fclose($h);

        return [ $content, $len ];
    }

    /**
     * @return array{0: string, 1: int}
     */
    public function csv_encode_row(
        array $row,
        ?string $separator = null,
        ?string $enclosure = null,
        ?string $escape = null,
        ?string $eol = null
    ) : array
    {
        if ([] === $row) {
            throw new LogicException(
                'The `row` should be not-empty array'
            );
        }

        $theType = Lib::type();

        if (! $theType->char($separatorString, $separator ?? ';')) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        if (! $theType->char($enclosureString, $enclosure ?? '"')) {
            throw new LogicException(
                [ 'The `enclosure` should be char', $enclosure ]
            );
        }

        if (! $theType->char($escapeString, $escape ?? '\\')) {
            throw new LogicException(
                [ 'The `escape` should be char', $escape ]
            );
        }

        if (! $theType->string_not_empty($eolString, $eol ?? "\n")) {
            throw new LogicException(
                [ 'The `eol` should be non-empty string', $eol ]
            );
        }

        $_row = array_values($row);

        $fputcsvArgs = [ $separatorString, $enclosureString, $escapeString ];

        if (PHP_VERSION > 80100) {
            $fputcsvArgs[] = $eolString;
        }

        $h = fopen('php://temp', 'w');

        $len = fputcsv($h, $_row, ...$fputcsvArgs);

        rewind($h);
        $content = stream_get_contents($h);
        fclose($h);

        return [ $content, $len ];
    }
}
