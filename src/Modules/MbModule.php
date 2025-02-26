<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class MbModule
{
    public function __construct()
    {
        if (! extension_loaded('mbstring')) {
            throw new RuntimeException(
                'Missing PHP extension: mbstring'
            );
        }
    }


    public function is_utf8(string $str) : bool
    {
        return Lib::str()->is_utf8($str);
    }


    public function list_encodings(array $order = null, array $groups = null) : array
    {
        static $cache;

        if (null === $order) {
            $order = [
                'utf32',
                'utf16',
                'utf8',
                'utf7',
                'ascii',
                'windows',
                'cp',
                'iso',
                'koi8',
                'sjis',
                'jis',
                'eucjp',
                'euc',
                'ucs',
            ];

        } elseif ([] === $order) {
            $string = mb_detect_order();

            foreach ( explode(',', $string) as $enc ) {
                $order[] = $enc;
            }
        }

        $_order = [];
        foreach ( $order as $i => $encGroup ) {
            $_encGroup = trim($encGroup);
            $_encGroup = strtolower($_encGroup);
            $_encGroup = str_replace([ ' ', '-' ], '', $_encGroup);

            $_order[ $_encGroup ] = true;
        }

        if (null === $groups) {
            $groups = $_order;
        }

        $cacheKey = crc32(serialize($_order));

        if (! isset($cache[ $cacheKey ])) {
            $list = mb_list_encodings();

            if (Lib::php()->is_windows()) {
                $list[] = 'CP1251';
                $list[] = 'Windows-1251';
            }

            $remove = [];
            if (PHP_VERSION_ID < 82000) {
                $remove = [
                    '7bit'            => true,
                    '8bit'            => true,
                    'base64'          => true,
                    'htmlentities'    => true,
                    'qprint'          => true,
                    'quotedprintable' => true,
                    'uuencode'        => true,
                ];
            }

            $_list = [];
            foreach ( $list as $i => $enc ) {
                $encKey = trim($enc);
                $encKey = strtolower($encKey);
                $encKey = str_replace([ ' ', '-' ], '', $encKey);

                if (! isset($remove[ $encKey ])) {
                    $_list[ $encKey ] = $enc;
                }
            }

            $priority = 0;
            $orderIndex = [];
            foreach ( $_order as $encGroup => $bool ) {
                $orderIndex[ $encGroup ] = ++$priority;
            }

            $_listOrder = [];
            foreach ( $_list as $encKey => $enc ) {
                $group = null;
                $priority = null;
                foreach ( $orderIndex as $encGroup => $i ) {
                    if (0 === stripos($encKey, $encGroup)) {
                        $group = $encGroup;
                        $priority = $i;

                        break;
                    }
                }
                $priority = $priority ?? INF;

                $_listOrder[ $enc ] = [ $group, $priority ];
            }

            usort($_list,
                static function ($encA, $encB) use (&$_listOrder) {
                    $aPriority = $_listOrder[ $encA ][ 1 ];
                    $bPriority = $_listOrder[ $encB ][ 1 ];

                    return 0
                        ?: ($aPriority <=> $bPriority)
                            ?: (strlen($encB) <=> strlen($encA))
                                ?: strnatcasecmp($encB, $encA);
                }
            );

            $listGroups = [];
            foreach ( $_list as $enc ) {
                $encGroup = $_listOrder[ $enc ][ 0 ];

                $listGroups[ $encGroup ][] = $enc;
            }

            $cache[ $cacheKey ] = [ $list, $listGroups ];
        }

        return $cache[ $cacheKey ];
    }

    /**
     * @param array|string|null $encondings
     * @param bool|null         $strict
     *
     * @return array<string, bool>
     */
    public function detect_encoding(string $string, $encondings = '', bool $strict = null, array $detect_order = null) : array
    {
        $strict = $strict ?? true;

        $encGroups = null;
        if ($encondings === '') {
            [ , $encGroups ] = $this->list_encodings($detect_order);

        } else {
            $encGroups = [];
            $encGroups[ '' ] = $encondings;
        }

        $result = [];
        foreach ( $encGroups as $encGroup => $encList ) {
            $result[ $encGroup ] = mb_detect_encoding($string, $encList, $strict);
        }

        return $result;
    }

    /**
     * @param array|string|null $from_encoding
     *
     * @return array|string|null
     */
    public function convert_encoding($string, string $to_encoding, $from_encoding = '', array $detect_order = null)
    {
        if ($from_encoding === '') {
            $array = $this->detect_encoding($string);

            $list = [];
            foreach ( $array as $encGroup => $encodingOrFalse ) {
                if (false !== $encodingOrFalse) {
                    $list[] = $encodingOrFalse;
                }
            }

            $from_encoding = $list;
        }

        $result = mb_convert_encoding($string, $to_encoding, $from_encoding);

        return (false === $result)
            ? null
            : $result;
    }


    public function lcfirst(string $string, string $mb_encoding = null) : string
    {
        $mbEncodingArgs = [];
        if (null !== $mb_encoding) {
            $mbEncodingArgs[] = $mb_encoding;
        }

        $result = (''
            . mb_strtolower(mb_substr($string, 0, 1, ...$mbEncodingArgs), ...$mbEncodingArgs)
            . mb_substr($string, 1, null, ...$mbEncodingArgs)
        );

        return $result;
    }

    public function ucfirst(string $string, string $mb_encoding = null) : string
    {
        $mbEncodingArgs = [];
        if (null !== $mb_encoding) {
            $mbEncodingArgs[] = $mb_encoding;
        }

        $result = (''
            . mb_strtoupper(mb_substr($string, 0, 1, ...$mbEncodingArgs), ...$mbEncodingArgs)
            . mb_substr($string, 1, null, ...$mbEncodingArgs)
        );

        return $result;
    }


    public function str_split(string $string, int $length = null, string $mb_encoding = null) : array
    {
        $length = $length ?? 1;

        $mbEncodingArgs = [];
        if (null !== $mb_encoding) {
            $mbEncodingArgs[] = $mb_encoding;
        }

        if (PHP_VERSION_ID >= 74000) {
            return mb_str_split($string, $length, ...$mbEncodingArgs);
        }

        if ($length < 1) {
            throw new LogicException(
                'The `length` must be greater than 0'
            );
        }

        $len = mb_strlen($string, ...$mbEncodingArgs);

        $result = [];
        for ( $i = 0; $i < $len; $i += $length ) {
            $result[] = mb_substr($string, $i, $length, ...$mbEncodingArgs);
        }

        return $result;
    }
}
