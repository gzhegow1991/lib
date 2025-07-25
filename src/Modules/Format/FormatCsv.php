<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class FormatCsv
{
    public function __construct()
    {
        if (! extension_loaded('fileinfo')) {
            throw new ExtensionException(
                'Missing PHP extension: fileinfo'
            );
        }
    }


    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @return string|Ret<string>
     */
    public function csv_encode_rows(
        ?array $fallback,
        $rows,
        ?string $separator = null, ?string $enclosure = null, ?string $escape = null, ?string $eol = null,
        array $refs = []
    )
    {
        $theType = Lib::type();

        $withLength = array_key_exists(0, $refs);
        if ($withLength) {
            $refLength =& $refs[ 0 ];
        }
        $refLength = 0;

        if (! $theType->array_not_empty($rows)->isOk([ 1 => &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        if (! $theType->list($rows)->isOk([ 1 => &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        foreach ( $rows as $row ) {
            if (! $theType->array_not_empty($row)->isOk([ 1 => &$ret ])) {
                return Ret::throw($fallback, $ret);
            }
        }

        $separatorChar = $theType->char($separator ?? ';')->orThrow();
        $enclosureChar = $theType->char($enclosure ?? '"')->orThrow();
        $escapeChar = $theType->char($escape ?? '\\')->orThrow();
        $eolStringNotEmpty = $theType->string_not_empty($eol ?? "\n")->orThrow();

        $fputcsvArgs = [ $separatorChar, $enclosureChar, $escapeChar ];

        if (PHP_VERSION > 80100) {
            $fputcsvArgs[] = $eolStringNotEmpty;
        }

        $h = fopen('php://temp', 'wb');
        $len = 0;

        foreach ( $rows as $row ) {
            $rowList = array_values($row);

            $len += fputcsv($h, $rowList, ...$fputcsvArgs);
        }

        rewind($h);
        $content = stream_get_contents($h);
        fclose($h);

        $refLength = $len;

        return Ret::ok($fallback, $content);
    }

    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @return string|Ret<string>
     */
    public function csv_encode_row(
        ?array $fallback,
        $row,
        ?string $separator = null, ?string $enclosure = null, ?string $escape = null, ?string $eol = null,
        array $refs = []
    )
    {
        $theType = Lib::type();

        $withLength = array_key_exists(0, $refs);
        if ($withLength) {
            $refLength =& $refs[ 0 ];
        }
        $refLength = 0;

        if (! $theType->array_not_empty($row)->isOk([ 1 => &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        $separatorChar = $theType->char($separator ?? ';')->orThrow();
        $enclosureChar = $theType->char($enclosure ?? '"')->orThrow();
        $escapeChar = $theType->char($escape ?? '\\')->orThrow();
        $eolStringNotEmpty = $theType->string_not_empty($eol ?? "\n")->orThrow();

        $rowList = array_values($row);

        $fputcsvArgs = [ $separatorChar, $enclosureChar, $escapeChar ];

        if (PHP_VERSION > 80100) {
            $fputcsvArgs[] = $eolStringNotEmpty;
        }

        $h = fopen('php://temp', 'wb');

        $len = fputcsv($h, $rowList, ...$fputcsvArgs);

        rewind($h);
        $content = stream_get_contents($h);
        fclose($h);

        $refLength = $len;

        return Ret::ok($fallback, $content);
    }
}
