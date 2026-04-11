<?php

/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class PregModule
{
    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        if ( ! extension_loaded('mbstring') ) {
            throw new ExtensionException(
                [ 'Missing PHP extension: mbstring' ]
            );
        }

        if ( ! extension_loaded('pcre') ) {
            throw new ExtensionException(
                [ 'Missing PHP extension: pcre' ]
            );
        }

        return $this;
    }


    /**
     * @return Ret<string>|string
     */
    public function type_regex($fb, $value)
    {
        $theFunc = Lib::func();
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $regex = $valueStringNotEmpty;

        try {
            $isMatch = $theFunc->safe_call(
                'preg_match',
                [ $regex, '' ]
            );
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid regex', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( false === $isMatch ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid regex', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueStringNotEmpty);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_regexp($fb, $value, string $enclosure = '/', ?string $flags = null)
    {
        $theFunc = Lib::func();
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $isMatch = $theFunc->safe_call(
                'preg_match',
                [ "{$enclosure}{$valueStringNotEmpty}{$enclosure}{$flags}", '' ]
            );
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid regexp', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( false === $isMatch ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid regexp', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueStringNotEmpty);
    }


    public function preg_quote_ord(string $string, ?string $mb_encoding = null) : string
    {
        $theMb = Lib::mb();

        $len = mb_strlen($string);

        $result = '';
        for ( $i = 0; $i < $len; $i++ ) {
            $letter = $theMb->mb_substr($string, $i, 1);

            $code = (null !== $mb_encoding)
                ? $theMb->mb_ord($letter, $mb_encoding)
                : $theMb->mb_ord($letter);

            $result .= sprintf('\\x{%X}', $code);
        }

        return $result;
    }


    public function preg_escape(string $delimiter, ...$regexParts) : string
    {
        if ( [] === $regexParts ) {
            return '';
        }

        $regex = '';

        foreach ( $regexParts as $v ) {
            $regex .= is_array($v)
                ? $v[0]
                : preg_quote($v, $delimiter);
        }

        $regex = "{$delimiter}{$regex}{$delimiter}";

        if ( false === preg_match($regex, '') ) {
            throw new LogicException(
                [ 'Invalid regular expression: ' . $regex ]
            );
        }

        return $regex;
    }

    public function preg_escape_ord(?string $mb_encoding, string $delimiter, ...$regexParts) : string
    {
        if ( [] === $regexParts ) {
            return '';
        }

        $regex = '';

        foreach ( $regexParts as $v ) {
            $regex .= is_array($v)
                ? $v[0]
                : $this->preg_quote_ord($v, $mb_encoding);
        }

        $regex = "{$delimiter}{$regex}{$delimiter}";

        if ( false === preg_match($regex, '') ) {
            throw new LogicException(
                [ 'Invalid regular expression: ' . $regex ]
            );
        }

        return $regex;
    }
}
