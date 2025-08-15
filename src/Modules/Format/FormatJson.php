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
    protected static $jsonDepth = 512;
    /**
     * @var int
     */
    protected static $jsonEncodeFlags = 0;
    /**
     * @var int
     */
    protected static $jsonDecodeFlags = 0;

    /**
     * @param int|false|null $jsonDepth
     */
    public static function staticJsonDepth($jsonDepth = null) : int
    {
        $last = static::$jsonDepth;

        if (null !== $jsonDepth) {
            if (false === $jsonDepth) {
                static::$jsonDepth = 512;

            } else {
                if ($jsonDepth < 0) {
                    throw new LogicException(
                        [ 'The `jsonDepth` should be a non-negative integer', $jsonDepth ]
                    );
                }

                static::$jsonDepth = $jsonDepth;
            }
        }

        static::$jsonDepth = static::$jsonDepth ?? 512;

        return $last;
    }

    /**
     * @param int|false|null $jsonEncodeFlags
     */
    public static function staticJsonEncodeFlags($jsonEncodeFlags = null) : int
    {
        $last = static::$jsonEncodeFlags;

        if (null !== $jsonEncodeFlags) {
            if (false === $jsonEncodeFlags) {
                static::$jsonEncodeFlags = 0;

            } else {
                if ($jsonEncodeFlags < 0) {
                    throw new LogicException(
                        [ 'The `jsonEncodeFlags` should be a non-negative integer', $jsonEncodeFlags ]
                    );
                }

                static::$jsonEncodeFlags = $jsonEncodeFlags;
            }
        }

        static::$jsonEncodeFlags = static::$jsonEncodeFlags ?? 0;

        return $last;
    }

    /**
     * @param int|false|null $jsonDecodeFlags
     */
    public static function staticJsonDecodeFlags($jsonDecodeFlags = null) : int
    {
        $last = static::$jsonDecodeFlags;

        if (null !== $jsonDecodeFlags) {
            if (false === $jsonDecodeFlags) {
                static::$jsonDecodeFlags = 0;

            } else {
                if ($jsonDecodeFlags < 0) {
                    throw new LogicException(
                        [ 'The `jsonDecodeFlags` should be a non-negative integer', $jsonDecodeFlags ]
                    );
                }

                static::$jsonDecodeFlags = $jsonDecodeFlags;
            }
        }

        static::$jsonDecodeFlags = static::$jsonDecodeFlags ?? 0;

        return $last;
    }


    public function __construct()
    {
        if (! extension_loaded('json')) {
            throw new ExtensionException(
                [ 'Missing PHP extension: json' ]
            );
        }
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

        $depth = $depth ?? $this->staticJsonDepth();
        $flags = $flags ?? $this->staticJsonDecodeFlags();

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

        $depth = $depth ?? $this->staticJsonDepth();
        $flags = $flags ?? $this->staticJsonDecodeFlags();

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

            $jsoncStringNotEmpty = preg_replace($regex, '', $jsoncStringNotEmpty);
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

        $flags = $flags ?? $this->staticJsonEncodeFlags();
        $depth = $depth ?? $this->staticJsonDepth();

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

        $flags = $flags ?? $this->staticJsonEncodeFlags();
        $flags = $flags
            | JSON_PRETTY_PRINT
            | JSON_UNESCAPED_LINE_TERMINATORS
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE;

        $depth = $depth ?? $this->staticJsonDepth();

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
