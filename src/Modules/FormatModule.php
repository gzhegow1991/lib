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
    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    public function newBaseN() : FormatBaseN
    {
        $instance = new FormatBaseN();

        return $instance;
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
        $instance = new FormatCsv();

        return $instance;
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
        $instance = new FormatJson();

        return $instance;
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
        $instance = new FormatSerialize();

        return $instance;
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
        $instance = new FormatXml();

        return $instance;
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
     * @return Ret<string>|string
     */
    public function type_html_tag($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! preg_match('/^[a-z][a-z0-9-]*$/', $valueStringNotEmpty) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid html tag', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueStringNotEmpty);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_xml_tag($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! preg_match('/^[A-Za-z_][A-Za-z0-9_\-\.]*$/', $valueStringNotEmpty) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid xml tag', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueStringNotEmpty);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_xml_nstag($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! preg_match('/^(?:[A-Za-z_][A-Za-z0-9_\-\.]*)?:?[A-Za-z_][A-Za-z0-9_\-\.]*$/', $valueStringNotEmpty) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid xml nstag', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueStringNotEmpty);
    }


    /**
     * @return Ret<int>|int
     */
    public function bytes_decode(
        $fb,
        $size
    )
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($size);

        if ( ! $ret->isOk([ &$sizeString ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( '0' === $size ) {
            return Ret::ok($fb, 0);
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

        if ( ! preg_match($regex = '/^(\d+(?:\.\d+)?)([A-Z]{0,2})$/', $size, $matches) ) {
            return Ret::throw(
                $fb,
                [ 'The `size` should match regex: ' . $regex, $size ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ , $numUnit, $strUnit ] = $matches;

        if ( '' === $strUnit ) {
            $strUnit = 'B';
        }

        if ( ! isset($strUnitList[$strUnit]) ) {
            return Ret::throw(
                $fb,
                [ 'Unknown `strUnit`', $strUnit ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $theType->num_non_negative($numUnit);

        if ( ! $ret->isOk([ &$numUnitNumNonNegative ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( 0 == $numUnitNumNonNegative ) {
            return Ret::ok($fb, 0);
        }

        $bytesNum = $numUnit * pow(1024, $strUnitList[$strUnit]);
        $bytesNumCeil = ceil($bytesNum);

        /**
         * > ceil() may return false, suppress damn PHPStorm
         */
        if ( false === $bytesNumCeil ) {
            return Ret::throw(
                $fb,
                [ 'Unable to `ceil`', $bytesNum ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, (int) $bytesNumCeil);
    }

    /**
     * @return Ret<string>|string
     */
    public function bytes_encode(
        $fb,
        $bytes,
        ?int $roundPrecision = null, ?int $unitLen = null
    )
    {
        $theType = Lib::type();

        $ret = $theType->num_non_negative($bytes);

        if ( ! $ret->isOk([ &$bytesNumNonNegative ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $roundPrecision = $roundPrecision ?? 3;
        $unitLen = $unitLen ?? 2;

        if ( 0 === $bytesNumNonNegative ) {
            return Ret::ok($fb, '0B');
        }

        $strUnitList = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

        $left = $bytesNumNonNegative;

        $pow = floor(log($bytesNumNonNegative) / log(1024));
        $pow = min($pow, count($strUnitList) - 1);

        $left /= pow(1024, $pow);

        $unit = $strUnitList[$pow];
        $unit = substr($unit, 0, $unitLen);

        $size = round($left, $roundPrecision) . $unit;

        return Ret::ok($fb, $size);
    }


    /**
     * @return Ret<mixed>|mixed
     */
    public function json_base64_decode(
        $fb,
        $base64, ?bool $isAssociative = null,
        ?int $depth = null, ?int $flags = null
    )
    {
        $theFormatBaseN = Lib::formatBaseN();
        $theFormatJson = Lib::formatJson();

        $ret = $theFormatBaseN->base64_decode(
            null,
            $base64
        );

        if ( ! $ret->isOk([ &$jsonString ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $theFormatJson->json_decode(
            null,
            $jsonString, $isAssociative,
            $depth, $flags
        );

        if ( ! $ret->isOk([ &$data ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $data);
    }

    /**
     * @return Ret<string>|string
     */
    public function json_base64_encode(
        $fb,
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

        if ( ! $ret->isOk([ &$jsonString, &$ret ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $theFormatBaseN->base64_encode(
            null,
            $jsonString
        );

        if ( ! $ret->isOk([ &$base64String ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $base64String);
    }
}
