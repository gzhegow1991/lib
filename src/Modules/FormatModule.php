<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Modules\Format\FormatCsv;
use Gzhegow\Lib\Modules\Format\FormatXml;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Modules\Format\FormatJson;


class FormatModule
{
    /**
     * @param FormatCsv $ref
     *
     * @return FormatCsv
     */
    public function csv(&$ref = null)
    {
        return $ref = new FormatCsv();
    }

    /**
     * @param FormatJson $ref
     *
     * @return FormatJson
     */
    public function json(&$ref = null)
    {
        return $ref = new FormatJson();
    }

    /**
     * @param FormatXml $ref
     *
     * @return FormatXml
     */
    public function xml(&$ref = null)
    {
        return $ref = new FormatXml();
    }


    /**
     * @param string|null $r
     */
    public function type_html_tag(&$r, $value) : bool
    {
        $r = null;

        if (! Lib::type()->string_not_empty($valueString, $value)) {
            return false;
        }

        if (preg_match('/^[a-z][a-z0-9-]*$/', $valueString)) {
            $r = $valueString;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_xml_tag(&$r, $value) : bool
    {
        $r = null;

        if (! Lib::type()->string_not_empty($valueString, $value)) {
            return false;
        }

        if (preg_match('/^[A-Za-z_][A-Za-z0-9_\-\.]*$/', $valueString)) {
            $r = $valueString;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     */
    public function type_xml_nstag(&$r, $value) : bool
    {
        $r = null;

        if (! Lib::type()->string_not_empty($valueString, $value)) {
            return false;
        }

        if (preg_match('/^(?:[A-Za-z_][A-Za-z0-9_\-\.]*)?:?[A-Za-z_][A-Za-z0-9_\-\.]*$/', $valueString)) {
            $r = $valueString;

            return true;
        }

        return false;
    }


    /**
     * @param Ret $ret
     *
     * @return int|mixed
     */
    public function bytes_decode(
        string $size,
        $ret = null
    )
    {
        if ('' === $size) {
            return Result::err(
                $ret,
                [ 'The `size` should be non-empty string', $size ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('0' === $size) {
            return Result::ok($ret, 0);
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
            return Result::err(
                $ret,
                [ 'The `size` should match regex: ' . $regex, $size ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ , $numUnit, $strUnit ] = $matches;

        if ('' === $strUnit) {
            $strUnit = 'B';
        }

        if (! isset($strUnitList[ $strUnit ])) {
            return Result::err(
                $ret,
                [ 'Unknown `strUnit`', $strUnit ],
                [ __FILE__, __LINE__ ]
            );
        }

        $theType = Lib::type();

        if (! $theType->num_positive($number, $numUnit)) {
            return Result::err(
                $ret,
                [ 'Unknown `numUnit`', $numUnit ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (0 === $number) {
            return Result::ok($ret, 0);
        }

        $bytesNum = $numUnit * pow(1024, $strUnitList[ $strUnit ]);

        $bytesCeil = ceil($bytesNum);

        if (false === $bytesCeil) {
            return Result::err(
                $ret,
                [ 'Unable to `ceil`', $bytesNum ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Result::ok($ret, (int) $bytesCeil);
    }

    /**
     * @param int|float $bytes
     * @param Ret       $ret
     *
     * @return array{ 0?: string }|mixed
     */
    public function bytes_encode(
        $bytes,
        ?int $roundPrecision = null,
        ?int $unitLen = null,
        $ret = null
    )
    {
        if (! Lib::type()->num_non_negative($bytesNumber, $bytes)) {
            return Result::err(
                $ret,
                [ 'The `bytes` should be non-negative num', $bytes ],
                [ __FILE__, __LINE__ ]
            );
        }

        $roundPrecision = $roundPrecision ?? 3;
        $unitLen = $unitLen ?? 2;

        if (0 === $bytesNumber) {
            return Result::ok($ret, '0B');
        }

        $strUnitList = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

        $left = $bytesNumber;

        $pow = floor(log($bytesNumber) / log(1024));
        $pow = min($pow, count($strUnitList) - 1);

        $left /= pow(1024, $pow);

        $unit = $strUnitList[ $pow ];
        $unit = substr($unit, 0, $unitLen);

        $size = round($left, $roundPrecision) . $unit;

        return Result::ok($ret, $size);
    }
}
