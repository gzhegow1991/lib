<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;


class FormatBaseN
{
    /**
     * @return Ret<string>|string
     */
    public function base64_encode($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueString ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $b64 = base64_encode($valueString);

        return Ret::ok($fb, $b64);
    }

    /**
     * @return Ret<string>|string
     */
    public function base64_decode($fb, $base64)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($base64);

        if ( ! $ret->isOk([ &$base64String ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $b64 = base64_decode($base64String, true);

        if ( false === $b64 ) {
            return Ret::throw(
                $fb,
                [ 'Unable to decode value from base64', $base64 ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $b64);
    }
}
