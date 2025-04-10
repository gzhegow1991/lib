<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class FormatModule
{
    const INTERVAL_MINUTE = 60;
    const INTERVAL_HOUR   = 3600;
    const INTERVAL_DAY    = 86400;
    const INTERVAL_WEEK   = 604800;
    const INTERVAL_MONTH  = 2592000;
    const INTERVAL_YEAR   = 31536000;


    public function interval_encode(\DateInterval $interval) : string
    {
        // > ISO 8601

        $search = [ 'M0S', 'H0M', 'DT0H', 'M0D', 'P0Y', 'Y0M', 'P0M' ];
        $replace = [ 'M', 'H', 'DT', 'M', 'P', 'Y', 'P' ];

        if ($interval->f) {
            $microseconds = sprintf('%.6f', $interval->f);
            $microseconds = substr($microseconds, 2);
            $microseconds = rtrim($microseconds, '0.');
            $microseconds = (int) $microseconds;

            $result = $interval->format("P%yY%mM%dDT%hH%iM%s.{$microseconds}S");

        } else {
            $result = $interval->format('P%yY%mM%dDT%hH%iM%sS');
        }

        $result = str_replace($search, $replace, $result);
        $result = rtrim($result, 'PT') ?: 'P0D';

        return $result;
    }

    /**
     * @template-covariant T of \DateInterval
     *
     * @param string               $duration
     * @param class-string<T>|null $intervalClass
     *
     * @return T
     */
    public function interval_decode(string $duration, string $intervalClass = null) : \DateInterval
    {
        // > ISO 8601

        $theStr = Lib::str();

        if ('' === $duration) {
            throw new LogicException(
                [ 'The `duration` should be non-empty string' ]
            );
        }

        if (null !== $intervalClass) {
            if (! is_a($intervalClass, \DateInterval::class, true)) {
                throw new LogicException(
                    [
                        'The `intervalClass` should be class-string of: ' . \DateInterval::class,
                        $intervalClass,
                    ]
                );
            }
        }

        $intervalClass = $intervalClass ?? \DateInterval::class;

        $_duration = $duration;

        $regex = '/(\d+\.\d+)([YMWDHS])/';

        $hasDecimalValue = preg_match_all($regex, $_duration, $matches);

        $decimalValueFrac = null;
        $decimalLetter = null;
        if ($hasDecimalValue) {
            $decimal = $matches[ 0 ];
            $decimalSubstr = $matches[ 0 ][ 0 ];
            $decimalValue = $matches[ 1 ][ 0 ];
            $decimalLetter = $matches[ 2 ][ 0 ];

            if (count($decimal) > 1) {
                throw new LogicException(
                    [
                        'The `duration` can contain only one `.` in smallest period (according ISO 8601)',
                        $duration,
                    ]
                );
            }

            if (! $theStr->str_ends($duration, $decimalSubstr, false)) {
                throw new LogicException(
                    [
                        'The `duration` can contain only one `.` in smallest period (according ISO 8601)',
                        $duration,
                    ]
                );
            }

            $decimalValueFloat = (float) $decimalValue;
            $decimalValueInt = (int) $decimalValue;

            $decimalValueFrac = $decimalValueFloat - (float) $decimalValueInt;

            $_duration = str_replace($decimalValue, $decimalValueInt, $_duration);
        }

        try {
            $instance = new \DateInterval($_duration);
        }
        catch ( \Throwable $e ) {
            throw new LogicException($e);
        }

        if ($hasDecimalValue) {
            $now = new \DateTime('now');
            $nowModified = clone $now;

            $nowModified->add($instance);

            $seconds = null;
            switch ( $decimalLetter ):
                case 'Y':
                    $seconds = intval($decimalValueFrac * static::INTERVAL_YEAR);

                    break;

                case 'W':
                    $seconds = intval($decimalValueFrac * static::INTERVAL_WEEK);

                    break;

                case 'D':
                    $seconds = intval($decimalValueFrac * static::INTERVAL_DAY);

                    break;

                case 'H':
                    $seconds = intval($decimalValueFrac * static::INTERVAL_HOUR);

                    break;

                case 'M':
                    if (false === strpos($duration, 'T')) {
                        $seconds = intval($decimalValueFrac * static::INTERVAL_MONTH);

                    } else {
                        $seconds = intval($decimalValueFrac * static::INTERVAL_MINUTE);
                    }

                    break;

            endswitch;

            if (null !== $seconds) {
                $nowModified->modify("+{$seconds} seconds");
            }

            $interval = $nowModified->diff($now);

            $instance->y = $interval->y;
            $instance->m = $interval->m;
            $instance->d = $interval->d;
            $instance->h = $interval->h;
            $instance->i = $interval->i;
            $instance->s = $interval->s;

            if (null !== $decimalValueFrac) {
                if ('S' === $decimalLetter) {
                    $instance->f = $decimalValueFrac;
                }
            }
        }

        return $instance;
    }


    /**
     * @return null|int|float
     */
    public function bytes_decode(string $size) // : null|int|float
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

        if ('' === $sUnit) {
            $sUnit = 'B';
        }

        if (! isset($aUnits[ $sUnit ])) {
            throw new LogicException(
                [ "Unknown `sUnit`", $sUnit ]
            );
        }

        $iUnit = (int) $iUnit;
        if (0 === $iUnit) {
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

        if (! Lib::type()->num_non_negative($var, $bytes)) {
            throw new LogicException(
                [ 'The `bytes` should be non-negative num', $bytes ]
            );
        }

        $units = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

        $_bytes = max($bytes, 0);

        $pow = floor(($_bytes ? log($_bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $_bytes /= pow(1024, $pow);

        $unit = $units[ $pow ];
        $unit = substr($unit, 0, $lenUnit);

        return round($_bytes, $precision) . $unit;
    }


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
        if (0 === count($rows)) {
            throw new LogicException(
                'The `rows` should be not-empty array'
            );
        }

        $_rows = array_values($rows);

        foreach ( $_rows as $i => $row ) {
            if (! (is_array($row) && (0 !== count($row)))) {
                throw new LogicException(
                    [
                        'Each of `rows` should be not-empty array',
                        $row,
                        $i,
                    ]
                );
            }
        }

        $separator = Lib::parse()->char($separator) ?? ';';
        $enclosure = Lib::parse()->char($enclosure) ?? '"';
        $escape = Lib::parse()->char($escape) ?? '\\';
        $eol = Lib::parse()->string_not_empty($eol) ?? PHP_EOL;

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
        if (0 === count($row)) {
            throw new LogicException(
                'The `row` should be not-empty array'
            );
        }

        $separator = $separator ?? ';';
        $enclosure = $enclosure ?? '"';
        $escape = $escape ?? '\\';
        $eol = $eol ?? PHP_EOL;

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


    public function sql_in(
        ?array &$params,
        string $sql, array $in, string $paramNamePrefix = null
    ) : string
    {
        $params = $params ?? [];

        if (0 === count($in)) {
            return '';
        }

        $paramNamePrefix = (string) $paramNamePrefix;

        $hasParamNamePrefix = ('' !== $paramNamePrefix);

        $i = 0;
        $sqlIn = '';
        foreach ( $in as $value ) {
            if ($hasParamNamePrefix) {
                $paramName = ":{$paramNamePrefix}{$i}";

                if (isset($params[ $paramName ])) {
                    throw new RuntimeException(
                        [ 'The `params` already has parameter named: ' . $paramName, $params ]
                    );
                }

                $params[ $paramName ] = $value;

                $sqlIn .= "{$paramName}, ";

            } else {
                $params[] = $value;

                $sqlIn .= "?, ";
            }

            $i++;
        }
        $sqlIn = rtrim($sqlIn, ', ');
        $sqlIn = "{$sql} IN ({$sqlIn})";

        return $sqlIn;
    }

    public function sql_like_quote(string $string, string $escaper = null) : string
    {
        $escaper = $escaper ?? '\\';

        $search = [ '%', '_' ];
        $replace = [ "{$escaper}%", "{$escaper}_" ];

        $result = str_replace($search, $replace, $string);

        return $result;
    }

    public function sql_like_escape(string $sql, string $like = 'LIKE', ...$valueParts)
    {
        if (0 === count($valueParts)) {
            return '';
        }

        $value = '';
        foreach ( $valueParts as $v ) {
            $value .= is_array($v)
                ? $v[ 0 ]
                : $this->sql_like_quote($v);
        }

        $result = "{$sql} {$like} \"{$value}\"";

        return $result;
    }
}
