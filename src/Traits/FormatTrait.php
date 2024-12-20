<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


trait FormatTrait
{
    public static function format_json_depth_static(int $depth = null) : int
    {
        static $current;

        $current = $current ?? 512;

        if (null !== $depth) {
            $last = $current;

            $current = $depth;

            return $last;
        }

        return $current;
    }

    public static function format_json_encode_flags_static(int $flags = null) : int
    {
        static $current;

        $current = $current ?? 0;

        if (null !== $flags) {
            $last = $current;

            $current = $flags;

            return $last;
        }

        return $current;
    }

    public static function format_json_decode_flags_static(int $flags = null)
    {
        static $current;

        $current = $current ?? 0;

        if (null !== $flags) {
            $last = $current;

            $current = $flags;

            return $last;
        }

        return $current;
    }


    /**
     * @param array{ 0?: mixed } $fallback
     */
    public static function format_jsonc_decode(
        string $json, bool $associative = null,
        array $fallback = [],
        int $depth = null, int $flags = null
    ) // : mixed
    {
        $regex = [];
        $regex[] = preg_quote('#', '/') . '(.*?)$';
        $regex[] = preg_quote('/*', '/') . '([\s\S]*?)' . preg_quote('*/', '/');
        $regex[] = preg_quote('//', '/') . '(.*?)$';
        $regex = '/' . implode('|', $regex) . '/mu';

        $_json = preg_replace($regex, '$1', $json);

        $value = static::format_json_decode(
            $_json, $associative,
            $fallback,
            $depth, $flags
        );

        return $value;
    }

    /**
     * @param array{ 0?: mixed } $fallback
     */
    public static function format_json_decode(
        string $json, bool $associative = null,
        array $fallback = [],
        int $depth = null, int $flags = null
    ) // : mixed
    {
        if (! extension_loaded('json')) {
            throw new RuntimeException(
                'The `ext-json` must be loaded to use this function'
            );
        }

        $depth = $depth ?? static::format_json_depth_static();
        $flags = $flags ?? static::format_json_decode_flags_static();

        error_clear_last();

        try {
            $value = json_decode($json, $associative, $depth, $flags);
        }
        catch ( \Throwable $e ) {
            $value = null;
        }

        if (error_get_last()) {
            $value = null;
        }

        if (null === $value) {
            if (count($fallback)) {
                [ $value ] = $fallback;

            } else {
                throw new RuntimeException(
                    [
                        'Unable to `json_decode`',
                        $json,
                    ]
                );
            }
        }

        return $value;
    }

    /**
     * @param array{ 0?: string } $fallback
     */
    public static function format_json_encode(
        $value,
        array $fallback = [],
        int $flags = null, int $depth = null
    ) : ?string
    {
        if (! extension_loaded('json')) {
            throw new RuntimeException(
                'The `ext-json` must be loaded to use this function'
            );
        }

        $flags = $flags ?? static::format_json_encode_flags_static();
        $depth = $depth ?? static::format_json_depth_static();

        if (false
            || is_resource($value)
            || is_float($value) && is_nan($value)
        ) {
            $json = null;

        } else {
            error_clear_last();

            try {
                $json = json_encode($value, $flags, $depth);
            }
            catch ( \Throwable $e ) {
                $json = null;
            }

            if (error_get_last()) {
                $json = null;
            }
        }

        if (null === $json) {
            if (count($fallback)) {
                [ $json ] = $fallback;

            } else {
                throw new RuntimeException(
                    [
                        'Unable to `json_encode`',
                        $value,
                    ]
                );
            }
        }

        return $json;
    }

    public static function format_json_print(
        $value,
        array $fallback = [],
        int $flags = null, int $depth = null
    ) : ?string
    {
        $flags = $flags ?? (static::format_json_encode_flags_static() | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $json = static::format_json_encode(
            $value, $fallback,
            $flags, $depth
        );

        return $json;
    }


    /**
     * @return array{0: string, 1: int}
     */
    public static function format_csv_rows(
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
    public static function format_csv_row(
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
    public static function format_bytes_decode(string $size)
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
    public static function format_bytes_encode($bytes, int $precision = null, int $lenUnit = null) : string
    {
        $precision = $precision ?? 3;
        $lenUnit = $lenUnit ?? 2;

        $devnull = null
            ?? static::parse_num_non_negative($bytes)
            ?? static::php_throw([ 'The `bytes` should be non-negative num', $bytes ]);

        $units = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        $unit = $units[ $pow ];
        $unit = substr($unit, 0, $lenUnit);

        return round($bytes, $precision) . $unit;
    }
}
