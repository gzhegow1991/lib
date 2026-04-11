<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class FormatCsv
{
    public function __construct()
    {
        if ( ! extension_loaded('fileinfo') ) {
            throw new ExtensionException(
                [ 'Missing PHP extension: fileinfo' ]
            );
        }
    }


    /**
     * @return Ret<string>|string
     */
    public function csv_encode_rows(
        $fb,
        $rows,
        ?string $separator = null, ?string $enclosure = null, ?string $escape = null, ?string $eol = null,
        array $refs = []
    )
    {
        $theType = Lib::type();

        $withLength = array_key_exists(0, $refs);
        if ( $withLength ) {
            $refLength =& $refs[0];
        }
        $refLength = 0;

        $ret = $theType->array_not_empty($rows);

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $theType->list($rows);

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        foreach ( $rows as $row ) {
            $ret = $theType->array_not_empty($row);

            if ( ! $ret->isOk() ) {
                return Ret::throw(
                    $fb,
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        $separatorChar = $theType->char($separator ?? ';')->orThrow();
        $enclosureChar = $theType->char($enclosure ?? '"')->orThrow();
        $escapeChar = $theType->char($escape ?? '\\')->orThrow();
        $eolStringNotEmpty = $theType->string_not_empty($eol ?? "\n")->orThrow();

        $fputcsvArgs = [ $separatorChar, $enclosureChar, $escapeChar ];

        if ( PHP_VERSION > 80100 ) {
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

        return Ret::ok($fb, $content);
    }

    /**
     * @return Ret<string>|string
     */
    public function csv_encode_row(
        $fb,
        $row,
        ?string $separator = null, ?string $enclosure = null, ?string $escape = null, ?string $eol = null,
        array $refs = []
    )
    {
        $theType = Lib::type();

        $withLength = array_key_exists(0, $refs);
        if ( $withLength ) {
            $refLength =& $refs[0];
        }
        $refLength = 0;

        $ret = $theType->array_not_empty($row);

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $separatorChar = $theType->char($separator ?? ';')->orThrow();
        $enclosureChar = $theType->char($enclosure ?? '"')->orThrow();
        $escapeChar = $theType->char($escape ?? '\\')->orThrow();
        $eolStringNotEmpty = $theType->string_not_empty($eol ?? "\n")->orThrow();

        $rowList = array_values($row);

        $fputcsvArgs = [ $separatorChar, $enclosureChar, $escapeChar ];

        if ( PHP_VERSION > 80100 ) {
            $fputcsvArgs[] = $eolStringNotEmpty;
        }

        $h = fopen('php://temp', 'wb');

        $len = fputcsv($h, $rowList, ...$fputcsvArgs);

        rewind($h);
        $content = stream_get_contents($h);
        fclose($h);

        $refLength = $len;

        return Ret::ok($fb, $content);
    }
}
