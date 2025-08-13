<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Format\FormatCsv;
use Gzhegow\Lib\Modules\Format\FormatXml;
use Gzhegow\Lib\Modules\Format\FormatJson;
use Gzhegow\Lib\Modules\Format\FormatBaseN;
use Gzhegow\Lib\Modules\Format\FormatSerialize;


class FormatModule
{
    public function newBaseN() : FormatBaseN
    {
        return new FormatBaseN();
    }

    public function cloneBaseN() : FormatBaseN
    {
        return clone $this->baseN();
    }

    public function baseN() : FormatBaseN
    {
        return $this->newBaseN();
    }


    public function newCsv() : FormatCsv
    {
        return new FormatCsv();
    }

    public function cloneCsv() : FormatCsv
    {
        return clone $this->csv();
    }

    public function csv() : FormatCsv
    {
        return $this->newCsv();
    }


    public function newJson() : FormatJson
    {
        return new FormatJson();
    }

    public function cloneJson() : FormatJson
    {
        return clone $this->json();
    }

    public function json() : FormatJson
    {
        return $this->newJson();
    }


    public function newSerialize() : FormatSerialize
    {
        return new FormatSerialize();
    }

    public function cloneSerialize() : FormatSerialize
    {
        return clone $this->serialize();
    }

    public function serialize() : FormatSerialize
    {
        return $this->newSerialize();
    }


    public function newXml() : FormatXml
    {
        return new FormatXml();
    }

    public function cloneXml() : FormatXml
    {
        return clone $this->xml();
    }

    public function xml() : FormatXml
    {
        return $this->newXml();
    }


    /**
     * @return Ret<string>
     */
    public function type_html_tag($value)
    {
        $theType = Lib::type();

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        if (! preg_match('/^[a-z][a-z0-9-]*$/', $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid html tag', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueStringNotEmpty);
    }

    /**
     * @return Ret<string>
     */
    public function type_xml_tag($value)
    {
        $theType = Lib::type();

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_\-\.]*$/', $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid xml tag', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueStringNotEmpty);
    }

    /**
     * @return Ret<string>
     */
    public function type_xml_nstag($value)
    {
        $theType = Lib::type();

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        if (! preg_match('/^(?:[A-Za-z_][A-Za-z0-9_\-\.]*)?:?[A-Za-z_][A-Za-z0-9_\-\.]*$/', $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid xml nstag', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueStringNotEmpty);
    }


    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @return int|Ret<int>
     */
    public function bytes_decode(?array $fallback, $size)
    {
        $theType = Lib::type();

        if (! $theType->string_not_empty($size)->isOk([ &$sizeString, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        if ('0' === $size) {
            return Ret::ok($fallback, 0);
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
            return Ret::throw(
                $fallback,
                [ 'The `size` should match regex: ' . $regex, $size ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ , $numUnit, $strUnit ] = $matches;

        if ('' === $strUnit) {
            $strUnit = 'B';
        }

        if (! isset($strUnitList[ $strUnit ])) {
            return Ret::throw(
                $fallback,
                [ 'Unknown `strUnit`', $strUnit ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->num_non_negative($numUnit)->isOk([ &$numUnitNumNonNegative, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        if (0 == $numUnitNumNonNegative) {
            return Ret::ok($fallback, 0);
        }

        $bytesNum = $numUnit * pow(1024, $strUnitList[ $strUnit ]);
        $bytesNumCeil = ceil($bytesNum);

        /**
         * > ceil() may return false, suppress damn PHPStorm
         * @noinspection PhpStrictComparisonWithOperandsOfDifferentTypesInspection
         */
        if (false === $bytesNumCeil) {
            return Ret::throw(
                $fallback,
                [ 'Unable to `ceil`', $bytesNum ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, (int) $bytesNumCeil);
    }

    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @return string|Ret<string>
     */
    public function bytes_encode(
        ?array $fallback,
        $bytes,
        ?int $roundPrecision = null, ?int $unitLen = null
    )
    {
        $theType = Lib::type();

        if (! $theType->num_non_negative($bytes)->isOk([ &$bytesNumNonNegative, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        $roundPrecision = $roundPrecision ?? 3;
        $unitLen = $unitLen ?? 2;

        if (0 === $bytesNumNonNegative) {
            return Ret::ok($fallback, '0B');
        }

        $strUnitList = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

        $left = $bytesNumNonNegative;

        $pow = floor(log($bytesNumNonNegative) / log(1024));
        $pow = min($pow, count($strUnitList) - 1);

        $left /= pow(1024, $pow);

        $unit = $strUnitList[ $pow ];
        $unit = substr($unit, 0, $unitLen);

        $size = round($left, $roundPrecision) . $unit;

        return Ret::ok($fallback, $size);
    }


    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @return mixed|Ret<mixed>
     */
    public function json_base64_decode(
        ?array $fallback,
        $base64, ?bool $isAssociative = null,
        ?int $depth = null, ?int $flags = null
    )
    {
        $theFormatBaseN = Lib::formatBaseN();
        $theFormatJson = Lib::formatJson();

        if (! $theFormatBaseN->base64_decode(null, $base64)->isOk([ &$jsonString, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        $ret = $theFormatJson->json_decode(
            null,
            $jsonString, $isAssociative,
            $depth, $flags
        );

        if (! $ret->isOk([ &$data, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $data);
    }

    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @return string|Ret<string>
     */
    public function json_base64_encode(
        ?array $fallback,
        $value, ?bool $isAllowNull = null,
        ?int $flags = null, ?int $depth = null
    )
    {
        $theFormatBaseN = Lib::formatBaseN();
        $theFormatJson = Lib::formatJson();

        $ret = $theFormatJson->json_encode(
            null,
            $value, $isAllowNull,
            $flags, $depth
        );

        if (! $ret->isOk([ &$jsonString, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        if (! $theFormatBaseN->base64_encode(null, $jsonString)->isOk([ &$base64String, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $base64String);
    }
}
