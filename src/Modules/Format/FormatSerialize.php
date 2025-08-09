<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;


class FormatSerialize
{
    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @param mixed                   $data
     *
     * @return string|Ret<string>
     */
    public function serialize(?array $fallback, $data)
    {
        $theFunc = Lib::func();

        try {
            $result = $theFunc->safe_call(
                'serialize',
                [ $data ]
            );
        }
        catch ( \Throwable $e ) {
            return Ret::throw($fallback, $e);
        }

        if (! is_string($result)) {
            return Ret::throw(
                $fallback,
                [ 'The `serialize` returned non-string, serialization is failed', $result ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, $result);
    }

    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @return mixed|Ret<mixed>
     */
    public function unserialize(?array $fallback, $serialized)
    {
        $theFunc = Lib::func();
        $theType = Lib::type();

        if (! $theType->string_not_empty($serialized)->isOk([ &$serializedString, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        try {
            $result = $theFunc->safe_call(
                'unserialize',
                [ $serializedString ]
            );
        }
        catch ( \Throwable $e ) {
            return Ret::throw($fallback, $e);
        }

        if (is_object($result) && (get_class($result) === '__PHP_Incomplete_Class')) {
            return Ret::throw(
                $fallback,
                [ 'The `unserialize` returned object of class that was not loaded in current PHP script', $result ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, $result);
    }
}
