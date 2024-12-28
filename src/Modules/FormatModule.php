<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


class FormatModule
{
    /**
     * @return array{0: string, 1: int}
     */
    public function csv_rows(
        array $rows,
        string $separator = null,
        string $enclosure = null,
        string $escape = null,
        string $eol = null
    ) : array
    {
        if (! count($rows)) {
            throw new LogicException(
                'The `rows` should be not-empty array'
            );
        }

        $_rows = array_values($rows);
        foreach ( $_rows as $i => $row ) {
            if (! $row) {
                throw new LogicException(
                    [ 'Each of `rows` should be not-empty array', $row, $i ]
                );
            }
        }

        $fputcsvArgs = [ $separator, $enclosure, $escape ];

        if (PHP_VERSION > 80100) {
            $fputcsvArgs[] = $eol;
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
    public function csv_row(
        array $row,
        string $separator = null,
        string $enclosure = null,
        string $escape = null,
        string $eol = null
    ) : array
    {
        if (! count($row)) {
            throw new LogicException(
                'The `row` should be not-empty array'
            );
        }

        $_row = array_values($row);

        $fputcsvArgs = [ $separator, $enclosure, $escape ];

        if (PHP_VERSION > 80100) {
            $fputcsvArgs[] = $eol;
        }

        $h = fopen('php://temp', 'w');

        $len = fputcsv($h, $_row, ...$fputcsvArgs);

        rewind($h);
        $content = stream_get_contents($h);
        fclose($h);

        return [ $content, $len ];
    }


    /**
     * @return float|int|null
     */
    public function bytes_decode(string $size)
    {
        if (! strlen($size)) {
            return null;
        }

        $aUnits = [
            'B'  => 0,
            'K'  => 1,
            'KB' => 1,
            'M'  => 2,
            'MB' => 2,
            'G'  => 3,
            'GB' => 3,
            'T'  => 4,
            'TB' => 4,
            'P'  => 5,
            'PB' => 5,
            'E'  => 6,
            'EB' => 6,
            'Z'  => 7,
            'ZB' => 7,
            'Y'  => 8,
            'YB' => 8,
        ];

        if (! preg_match('~^([0-9]+)([A-Z]{0,2})$~', $size, $matches)) {
            return null;
        }

        [ , $iUnit, $sUnit ] = $matches;

        if (! $sUnit) $sUnit = 'B';

        if (! isset($aUnits[ $sUnit ])) {
            throw new LogicException(
                [ "Unknown `sUnit`", $sUnit ]
            );
        }

        $iUnit = (int) $iUnit;
        if (! $iUnit) {
            return 0;
        }

        $result = $iUnit * pow(1024, $aUnits[ $sUnit ]);

        return $result;
    }

    /**
     * @param float|int $bytes
     */
    public function bytes_encode($bytes, int $precision = null, int $lenUnit = null) : string
    {
        $precision = $precision ?? 3;
        $lenUnit = $lenUnit ?? 2;

        $devnull = null
            ?? Lib::parse()->num_non_negative($bytes)
            ?? Lib::php()->throw([ 'The `bytes` should be non-negative num', $bytes ]);

        $units = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

        $_bytes = max($bytes, 0);

        $pow = floor(($_bytes ? log($_bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $_bytes /= pow(1024, $pow);

        $unit = $units[ $pow ];
        $unit = substr($unit, 0, $lenUnit);

        return round($_bytes, $precision) . $unit;
    }
}
