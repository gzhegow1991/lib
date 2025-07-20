<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Format\FormatCsv;
use Gzhegow\Lib\Modules\Format\FormatXml;
use Gzhegow\Lib\Modules\Format\FormatJson;


class FormatModule
{
    public function newCsv()
    {
        return new FormatCsv();
    }

    public function csv()
    {
        return $this->newCsv();
    }


    public function newJson()
    {
        return new FormatJson();
    }

    public function json()
    {
        return $this->newJson();
    }


    public function newXml()
    {
        return new FormatXml();
    }

    public function xml()
    {
        return $this->newXml();
    }


    /**
     * @return Ret<string>
     */
    public function type_html_tag($value)
    {
        $theType = Lib::$type;

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        if (! preg_match('/^[a-z][a-z0-9-]*$/', $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid html tag', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueStringNotEmpty);
    }

    /**
     * @return Ret<string>
     */
    public function type_xml_tag($value)
    {
        $theType = Lib::$type;

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_\-\.]*$/', $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid xml tag', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueStringNotEmpty);
    }

    /**
     * @return Ret<string>
     */
    public function type_xml_nstag($value)
    {
        $theType = Lib::$type;

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        if (! preg_match('/^(?:[A-Za-z_][A-Za-z0-9_\-\.]*)?:?[A-Za-z_][A-Za-z0-9_\-\.]*$/', $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid xml nstag', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueStringNotEmpty);
    }


    /**
     * @return Ret<int>
     */
    public function bytes_decode(string $size)
    {
        $theType = Lib::$type;

        if ('' === $size) {
            return Ret::err(
                [ 'The `size` should be a non-empty string', $size ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('0' === $size) {
            return Ret::ok(0);
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

        if (! preg_match($regex = '/^(\d+(?:\.\d+)?)([A-Z]{0,2})$/', $size, $matches)) {
            return Ret::err(
                [ 'The `size` should match regex: ' . $regex, $size ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ , $numUnit, $strUnit ] = $matches;

        if ('' === $strUnit) {
            $strUnit = 'B';
        }

        if (! isset($strUnitList[ $strUnit ])) {
            return Ret::err(
                [ 'Unknown `strUnit`', $strUnit ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->num_positive($numUnit)->isOk([ &$numUnitNumPositive, &$ret ])) {
            return $ret;
        }

        if (0 === $numUnitNumPositive) {
            return Ret::ok(0);
        }

        $bytesNum = $numUnit * pow(1024, $strUnitList[ $strUnit ]);

        $bytesCeil = ceil($bytesNum);

        if (false === $bytesCeil) {
            return Ret::err(
                [ 'Unable to `ceil`', $bytesNum ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok((int) $bytesCeil);
    }

    /**
     * @return Ret<array{ 0?: string }>
     */
    public function bytes_encode(
        $bytes,
        ?int $roundPrecision = null, ?int $unitLen = null
    )
    {
        $theType = Lib::$type;

        if (! $theType->num_non_negative($bytes)->isOk([ &$bytesNumNonNegative, &$ret ])) {
            return $ret;
        }

        $roundPrecision = $roundPrecision ?? 3;
        $unitLen = $unitLen ?? 2;

        if (0 === $bytesNumNonNegative) {
            return Ret::ok('0B');
        }

        $strUnitList = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

        $left = $bytesNumNonNegative;

        $pow = floor(log($bytesNumNonNegative) / log(1024));
        $pow = min($pow, count($strUnitList) - 1);

        $left /= pow(1024, $pow);

        $unit = $strUnitList[ $pow ];
        $unit = substr($unit, 0, $unitLen);

        $size = round($left, $roundPrecision) . $unit;

        return Ret::ok($size);
    }
}
