<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


class PregModule
{
    /**
     * @param string|null $r
     */
    public function type_regex(&$r, $value) : bool
    {
        $r = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        try {
            $isMatch = Lib::func()->safe_call(
                'preg_match',
                [ $_value, '' ]
            );
        }
        catch ( \Throwable $e ) {
            return false;
        }

        if (false === $isMatch) {
            return false;
        }

        $r = $_value;

        return true;
    }


    public function preg_quote_ord(string $string, ?string $mb_encoding = null) : string
    {
        Lib::mb();

        $len = mb_strlen($string);

        $result = '';
        for ( $i = 0; $i < $len; $i++ ) {
            $letter = mb_substr($string, $i, 1);

            $code = (null !== $mb_encoding)
                ? mb_ord($letter, $mb_encoding)
                : mb_ord($letter);

            $result .= sprintf('\\x{%X}', $code);
        }

        return $result;
    }


    public function preg_escape(string $delimiter, ...$regexParts) : string
    {
        if ([] === $regexParts) {
            return '';
        }

        $regex = '';

        foreach ( $regexParts as $v ) {
            $regex .= is_array($v)
                ? $v[ 0 ]
                : preg_quote($v, $delimiter);
        }

        $regex = "{$delimiter}{$regex}{$delimiter}";

        if (false === preg_match($regex, '')) {
            throw new LogicException(
                [ 'Invalid regular expression: ' . $regex ]
            );
        }

        return $regex;
    }

    public function preg_escape_ord(?string $mb_encoding, string $delimiter, ...$regexParts) : string
    {
        if ([] === $regexParts) {
            return '';
        }

        $regex = '';

        foreach ( $regexParts as $v ) {
            $regex .= is_array($v)
                ? $v[ 0 ]
                : $this->preg_quote_ord($v, $mb_encoding);
        }

        $regex = "{$delimiter}{$regex}{$delimiter}";

        if (false === preg_match($regex, '')) {
            throw new LogicException(
                [ 'Invalid regular expression: ' . $regex ]
            );
        }

        return $regex;
    }
}
