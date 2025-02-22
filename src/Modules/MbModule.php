<?php

namespace Gzhegow\Lib\Modules;

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
