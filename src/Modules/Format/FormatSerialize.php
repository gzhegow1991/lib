<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;


class FormatSerialize
{
    /**
     * @return Ret<string>|string
     */
    public function serialize($fb, $data)
    {
        $theFunc = Lib::func();

        try {
            $result = $theFunc->safe_call(
                'serialize',
                [ $data ]
            );
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! is_string($result) ) {
            return Ret::throw(
                $fb,
                [ 'The `serialize` returned non-string, serialization is failed', $result ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }

    /**
     * @return Ret<mixed>|mixed
     */
    public function unserialize($fb, $serialized)
    {
        $theFunc = Lib::func();
        $theType = Lib::type();

        $ret = $theType->string_not_empty($serialized);

        if ( ! $ret->isOk([ &$serializedString ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $result = $theFunc->safe_call(
                'unserialize',
                [ $serializedString ]
            );
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( is_object($result) && (get_class($result) === '__PHP_Incomplete_Class') ) {
            return Ret::throw(
                $fb,
                [ 'The `unserialize` returned object of class that was not loaded in current PHP script', $result ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }
}
