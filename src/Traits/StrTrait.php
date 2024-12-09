<?php

namespace Gzhegow\Lib\Traits;

trait StrTrait
{
    public static function str_mb(bool $bool = null) : bool
    {
        static $mb;

        $mb = $mb ?? false;

        if (null !== $bool) {
            $mb = $bool;
        }

        return $mb;
    }

    /**
     * @param callable|callable-string $fn
     *
     * @return callable|callable-string
     */
    public static function str_mbfunc(string $fn) : string
    {
        return static::str_mb()
            ? 'mb_' . $fn
            : $fn;
    }


    public static function str_lines(string $text) : array
    {
        $lines = explode("\n", $text);

        foreach ( $lines as $i => $line ) {
            $line = rtrim($line, PHP_EOL);

            $lines[ $i ] = $line;
        }

        return $lines;
    }

    public static function str_eol(string $text, array &$lines = null) : string
    {
        $lines = static::str_lines($text);

        $output = implode("\n", $lines);

        return $output;
    }
}
