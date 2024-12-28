<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class RandomModule
{
    public function random_int(int $min, int $max) : int
    {
        try {
            $rand = random_int($min, $max);
        }
        catch ( \Throwable $e ) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $rand;
    }

    public function random_str(int $len, string $alphabet = null) : string
    {
        $alphabet = $alphabet ?? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $devnull = Lib::parse()->int_positive($len) ?? Lib::php()->throw([ 'The `len` should be positive integer', $len ]);

        $_alphabet = null
            ?? Lib::parse()->alphabet($alphabet)
            ?? Lib::php()->throw([ 'The `alphabet` should be valid alphabet', $alphabet ]);

        $fnStrlen = Lib::str()->mb_func('strlen');

        $keyspaceLen = $fnStrlen($_alphabet, '8bit');

        $min = 0;
        $max = $keyspaceLen - 1;

        $random = [];
        for ( $i = 0; $i < $len; ++$i ) {
            $rand = $this->random_int($min, $max);

            $random[ $i ] = $_alphabet[ $rand ];
        }

        $random = implode('', $random);

        return $random;
    }
}
