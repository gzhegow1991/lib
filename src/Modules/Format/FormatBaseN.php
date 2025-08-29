<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;


class FormatBaseN
{
    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @return string|Ret<string>
     */
    public function base64_encode(?array $fallback, $value)
    {
        $theType = Lib::type();

        if ( ! $theType->string_not_empty($value)->isOk([ &$valueString, &$ret ]) ) {
            return Ret::throw($fallback, $ret);
        }

        $b64 = base64_encode($valueString);

        return Ret::ok($fallback, $b64);
    }

    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @return string|Ret<string>
     */
    public function base64_decode(?array $fallback, $base64)
    {
        $theType = Lib::type();

        if ( ! $theType->string_not_empty($base64)->isOk([ &$base64String, &$ret ]) ) {
            return Ret::throw($fallback, $ret);
        }

        $b64 = base64_decode($base64String, true);

        if ( false === $b64 ) {
            return Ret::throw(
                $fallback,
                [ 'Unable to decode value from base64', $base64 ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, $b64);
    }
}
