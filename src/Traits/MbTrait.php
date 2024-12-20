<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\RuntimeException;


trait MbTrait
{
    public static function mb_mode_static(bool $mode = null) : bool
    {
        static $current;

        $current = $current ?? extension_loaded('mbstring');

        if (null !== $mode) {
            $last = $current;

            $current = $mode;
        }

        $result = $last ?? $current;

        if ($result && ! extension_loaded('mbstring')) {
            throw new RuntimeException('Unable to use multibyte mode without extension: ext-mbstring');
        }

        return $result;
    }


    public static function mb(bool $mode = null) : bool
    {
        return static::mb_mode_static($mode);
    }

    /**
     * @param callable|callable-string $fn
     *
     * @return callable|callable-string
     */
    public static function mbfunc(string $fn) : string
    {
        return static::mb_mode_static()
            ? 'mb_' . $fn
            : $fn;
    }
}
