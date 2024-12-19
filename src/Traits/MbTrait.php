<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\RuntimeException;


trait MbTrait
{
    public static function mb(bool $mb = null) : bool
    {
        static $current;

        $current = $current ?? extension_loaded('mbstring');

        if (null !== $mb) {
            $last = $current;

            $current = $mb;
        }

        $result = $last ?? $current;

        if ($result && ! extension_loaded('mbstring')) {
            throw new RuntimeException('Unable to use multibyte mode without extension: ext-mbstring');
        }

        return $result;
    }

    /**
     * @param callable|callable-string $fn
     *
     * @return callable|callable-string
     */
    public static function mbfunc(string $fn) : string
    {
        return static::mb()
            ? 'mb_' . $fn
            : $fn;
    }
}
