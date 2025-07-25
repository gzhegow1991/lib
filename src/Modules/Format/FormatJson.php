<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class FormatJson
{
    /**
     * @var int
     */
    protected $jsonDepth = 512;
    /**
     * @var int
     */
    protected $jsonEncodeFlags = 0;
    /**
     * @var int
     */
    protected $jsonDecodeFlags = 0;


    public function __construct()
    {
        if (! extension_loaded('json')) {
            throw new ExtensionException(
                'Missing PHP extension: json'
            );
        }
    }


    public function static_json_depth(?int $json_depth = null) : int
    {
        if (null !== $json_depth) {
            if ($json_depth < 0) {
                throw new LogicException(
                    'The `jsonDepth` should be a non-negative integer'
                );
            }

            $last = $this->jsonDepth;

            $this->jsonDepth = $json_depth;

            $result = $last;
        }

        $result = $result ?? $this->jsonDepth ?? 512;

        return $result;
    }

    public function static_json_encode_flags(?int $json_encode_flags = null) : int
    {
        if (null !== $json_encode_flags) {
            if ($json_encode_flags < 0) {
                throw new LogicException(
                    'The `jsonEncodeFlags` should be a non-negative integer'
                );
            }

            $last = $this->jsonEncodeFlags;

            $this->jsonEncodeFlags = $json_encode_flags;

            $result = $last;
        }

        $result = $result ?? $this->jsonEncodeFlags ?? 0;

        return $result;
    }

    public function static_json_decode_flags(?int $json_decode_flags = null) : int
    {
        if (null !== $json_decode_flags) {
            if ($json_decode_flags < 0) {
                throw new LogicException(
                    'The `jsonDecodeFlags` should be a non-negative integer'
                );
            }

            $last = $this->jsonDecodeFlags;

            $this->jsonDecodeFlags = $json_decode_flags;

            $result = $last;
        }

        $result = $result ?? $this->jsonDecodeFlags ?? 0;

        return $result;
    }


    /**
     * @param array{ 0?: mixed }|null $fallback # Pass `null` to return Ret<T> or pass `[]` to throw exception
     *
     * @return mixed|Ret<mixed>
     */
    public function json_decode(
        ?array $fallback,
        $json, ?bool $isAssociative = null,
        ?int $depth = null, ?int $flags = null
    )
    {
        if (null === $json) {
            return Ret::throw(
                $fallback,
                [ 'The `json` should be not null', $json ],
                [ __FILE__, __LINE__ ]
            );
        }

        $depth = $depth ?? $this->static_json_depth();
        $flags = $flags ?? $this->static_json_decode_flags();

        $theFunc = Lib::func();
        $theType = Lib::type();

        $jsonStringNotEmpty = $theType->string_not_empty($json)->orThrow();

        try {
            $result = $theFunc->safe_call(
                'json_decode',
                [ $jsonStringNotEmpty, $isAssociative, $depth, $flags ],
            );
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fallback,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        if (null === $result) {
            return Ret::throw(
                $fallback,
                [ 'Unable to `json_decode` due to invalid JSON', $json ],
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
    public function jsonc_decode(
        ?array $fallback,
        $jsonc, ?bool $isAssociative = null,
        ?int $depth = null, ?int $flags = null
    )
    {
        if (null === $jsonc) {
            return Ret::throw(
                $fallback,
                [ 'The `jsonc` should be not null', $jsonc ],
                [ __FILE__, __LINE__ ]
            );
        }

        $depth = $depth ?? $this->static_json_depth();
        $flags = $flags ?? $this->static_json_decode_flags();

        $theFunc = Lib::func();
        $theType = Lib::type();

        if (! $theType->string_not_empty($jsonc)->isOk([ &$jsoncStringNotEmpty, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        $regexes = [];
        $regexes[ '#' ] = '/' . preg_quote('#', '/') . '(.*?)$' . '/m';
        $regexes[ '//' ] = '/' . preg_quote('//', '/') . '(.*?)$' . '/m';
        $regexes[ '/*' ] = '/' . preg_quote('/*', '/') . '([\s\S]*?)' . preg_quote('*/', '/') . '/m';

        foreach ( $regexes as $substr => $regex ) {
            if (false === strpos($jsoncStringNotEmpty, $substr)) {
                continue;
            }

            $jsoncStringNotEmpty = preg_replace($regex, '$1', $jsoncStringNotEmpty);
        }

        try {
            $result = $theFunc->safe_call(
                'json_decode',
                [ $jsoncStringNotEmpty, $isAssociative, $depth, $flags ],
            );
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fallback,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        if (null === $result) {
            return Ret::throw(
                $fallback,
                [ 'Unable to `jsonc_decode` due to invalid JSON', $jsonc ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, $result);
    }


    /**
     * @return string|Ret<string>
     */
    public function json_encode(
        ?array $fallback,
        $value, ?bool $isAllowNull = null,
        ?int $flags = null, ?int $depth = null
    )
    {
        $isAllowNull = $isAllowNull ?? false;

        $theFunc = Lib::func();
        $theType = Lib::type();

        if (null === $value) {
            if (! $isAllowNull) {
                return Ret::throw(
                    $fallback,
                    [ 'The value `NULL` cannot be encoded to JSON when `allowsNull` is set to FALSE', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok($fallback, 'NULL');
        }

        if (false
            || ($theType->nan($value)->isOk())
            || ($theType->resource($value)->isOk())
        ) {
            return Ret::throw(
                $fallback,
                [ 'The value `NAN` or values of type `resource` cannot be encoded to JSON', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $flags = $flags ?? $this->static_json_encode_flags();
        $depth = $depth ?? $this->static_json_depth();

        try {
            $result = $theFunc->safe_call(
                'json_encode',
                [ $value, $flags, $depth ],
            );
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fallback,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, $result);
    }

    /**
     * @return string|Ret<string>
     */
    public function json_print(
        ?array $fallback,
        $value, ?bool $isAllowNull = null,
        ?int $flags = null, ?int $depth = null
    )
    {
        $isAllowNull = $isAllowNull ?? false;

        $theFunc = Lib::func();
        $theType = Lib::type();

        if (null === $value) {
            if (! $isAllowNull) {
                return Ret::throw(
                    $fallback,
                    [ 'Unable to `json_encode`', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok($fallback, 'NULL');
        }

        if (false
            || ($theType->nan($value)->isOk())
            || ($theType->resource($value)->isOk())
        ) {
            return Ret::throw(
                $fallback,
                [ 'The value `NAN` or values of type `resource` cannot be encoded to JSON', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $flags = $flags ?? $this->static_json_encode_flags();
        $flags = $flags
            | JSON_PRETTY_PRINT
            | JSON_UNESCAPED_LINE_TERMINATORS
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE;

        $depth = $depth ?? $this->static_json_depth();

        try {
            $result = $theFunc->safe_call(
                'json_encode',
                [ $value, $flags, $depth ],
            );
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fallback,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, $result);
    }
}
