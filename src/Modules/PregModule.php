<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Type\Base\ParseModuleBase;


class PregModule extends ParseModuleBase
{
    /**
     * @param string|null $result
     */
    public function type_regex(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        error_clear_last();

        try {
            $status = preg_match($_value, '');
        }
        catch ( \Throwable $e ) {
            return false;
        }

        if (error_get_last()) {
            return false;
        }

        if (false === $status) {
            return false;
        }

        $result = $_value;

        return true;
    }


    public function preg_quote_ord(string $string, string $mb_encoding = null) : string
    {
        $theMb = Lib::mb();

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
        if (0 === count($regexParts)) {
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
        if (0 === count($regexParts)) {
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
