<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;


class FormatCsv
{
    public function __construct()
    {
        if (! extension_loaded('fileinfo')) {
            throw new RuntimeException(
                'Missing PHP extension: fileinfo'
            );
        }
    }


    /**
     * @param Ret $ret
     *
     * @return array{ 0: string, 1: int }|mixed
     */
    public function csv_encode_rows(
        array $rows,
        ?string $separator = null,
        ?string $enclosure = null,
        ?string $escape = null,
        ?string $eol = null,
        $ret = null
    ) : array
    {
        if ([] === $rows) {
            return Result::err(
                $ret,
                [ 'The `rows` should be not-empty array' ],
                [ __FILE__, __LINE__ ],
            );
        }

        $_rows = array_values($rows);

        foreach ( $_rows as $i => $row ) {
            $isNonEmptyArray = is_array($row) && ([] !== $row);

            if (! $isNonEmptyArray) {
                return Result::err(
                    $ret,
                    [ 'Each of `rows` should be not-empty array', $row, $i ],
                    [ __FILE__, __LINE__ ],
                );
            }
        }

        $theType = Lib::type();

        if (! $theType->char($separatorString, $separator ?? ';')) {
            return Result::err(
                $ret,
                [ 'The `separator` should be char', $separator ],
                [ __FILE__, __LINE__ ],
            );
        }

        if (! $theType->char($enclosureString, $enclosure ?? '"')) {
            return Result::err(
                $ret,
                [ 'The `enclosure` should be char', $enclosure ],
                [ __FILE__, __LINE__ ],
            );
        }

        if (! $theType->char($escapeString, $escape ?? '\\')) {
            return Result::err(
                $ret,
                [ 'The `escape` should be char', $escape ],
                [ __FILE__, __LINE__ ],
            );
        }

        if (! $theType->string_not_empty($eolString, $eol ?? "\n")) {
            return Result::err(
                $ret,
                [ 'The `eol` should be non-empty string', $eol ],
                [ __FILE__, __LINE__ ],
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

        return Result::ok($ret, [ $content, $len ]);
    }

    /**
     * @param Ret $ret
     *
     * @return array{ 0: string, 1: int }|mixed
     */
    public function csv_encode_row(
        array $row,
        ?string $separator = null,
        ?string $enclosure = null,
        ?string $escape = null,
        ?string $eol = null,
        $ret = null
    ) : array
    {
        if ([] === $row) {
            return Result::err(
                $ret,
                [ 'The `row` should be not-empty array' ],
                [ __FILE__, __LINE__ ],
            );
        }

        $theType = Lib::type();

        if (! $theType->char($separatorString, $separator ?? ';')) {
            return Result::err(
                $ret,
                [ 'The `separator` should be char', $separator ],
                [ __FILE__, __LINE__ ],
            );
        }

        if (! $theType->char($enclosureString, $enclosure ?? '"')) {
            return Result::err(
                $ret,
                [ 'The `enclosure` should be char', $enclosure ],
                [ __FILE__, __LINE__ ],
            );
        }

        if (! $theType->char($escapeString, $escape ?? '\\')) {
            return Result::err(
                $ret,
                [ 'The `escape` should be char', $escape ],
                [ __FILE__, __LINE__ ],
            );
        }

        if (! $theType->string_not_empty($eolString, $eol ?? "\n")) {
            return Result::err(
                $ret,
                [ 'The `eol` should be non-empty string', $eol ],
                [ __FILE__, __LINE__ ],
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

        return Result::ok($ret, [ $content, $len ]);
    }
}
