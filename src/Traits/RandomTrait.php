<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\RuntimeException;


trait RandomTrait
{
    public static function random_int(int $min, int $max) : int
    {
        try {
            $rand = random_int($min, $max);
        }
        catch ( \Throwable $e ) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $rand;
    }

    public static function random_str(int $len, string $alphabet = null) : string
    {
        $alphabet = $alphabet ?? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $devnull = static::parse_int_positive($len) ?? static::php_throw([ 'The `len` should be positive integer', $len ]);

        $_alphabet = null
            ?? static::parse_alphabet($alphabet)
            ?? static::php_throw([ 'The `alphabet` should be valid alphabet', $alphabet ]);

        $fnStrlen = static::mbfunc('strlen');

        $keyspaceLen = $fnStrlen($_alphabet, '8bit');

        $min = 0;
        $max = $keyspaceLen - 1;

        $random = [];
        for ( $i = 0; $i < $len; ++$i ) {
            $rand = static::random_int($min, $max);

            $random[ $i ] = $_alphabet[ $rand ];
        }

        $random = implode('', $random);

        return $random;
    }
}
