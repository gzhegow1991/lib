<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class JsonModule
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
            throw new RuntimeException(
                'Missing PHP extension: json'
            );
        }
    }


    public function static_json_depth(?int $jsonDepth = null) : int
    {
        if (null !== $jsonDepth) {
            if ($jsonDepth < 0) {
                throw new LogicException(
                    'The `jsonDepth` must be non-negative integer'
                );
            }

            $last = $this->jsonDepth;

            $current = $jsonDepth;

            $result = $last;
        }

        $result = $result ?? $this->jsonDepth;

        return $result;
    }

    public function static_json_encode_flags(?int $jsonEncodeFlags = null) : int
    {
        if (null !== $jsonEncodeFlags) {
            if ($jsonEncodeFlags < 0) {
                throw new LogicException(
                    'The `jsonEncodeFlags` must be non-negative integer'
                );
            }

            $last = $this->jsonEncodeFlags;

            $current = $jsonEncodeFlags;

            $result = $last;
        }

        $result = $result ?? $this->jsonEncodeFlags;

        return $result;
    }

    public function static_json_decode_flags(?int $jsonDecodeFlags = null) : int
    {
        if (null !== $jsonDecodeFlags) {
            if ($jsonDecodeFlags < 0) {
                throw new LogicException(
                    'The `jsonDecodeFlags` must be non-negative integer'
                );
            }

            $last = $this->jsonDecodeFlags;

            $current = $jsonDecodeFlags;

            $result = $last;
        }

        $result = $result ?? $this->jsonDecodeFlags;

        return $result;
    }


    /**
     * @param array{ 0?: mixed } $fallback
     */
    public function json_decode(
        ?string $json, ?bool $associative = null,
        array $fallback = [],
        ?int $depth = null, ?int $flags = null
    ) // : mixed
    {
        if ('' === $json) {
            return '';
        }

        $result = [];

        if (null !== $json) {
            $result = $this->_json_decode(
                $json, $associative,
                $depth, $flags
            );
        }

        if ([] !== $result) {
            [ $value ] = $result;

        } elseif ([] !== $fallback) {
            [ $value ] = $fallback;

        } else {
            throw new RuntimeException(
                [ 'Unable to `json_decode`', $json ]
            );
        }

        return $value;
    }

    /**
     * @param array{ 0?: mixed } $fallback
     */
    public function jsonc_decode(
        ?string $json, ?bool $associative = null,
        array $fallback = [],
        ?int $depth = null, ?int $flags = null
    ) // : mixed
    {
        if ('' === $json) {
            return '';
        }

        $result = [];

        if (null !== $json) {
            $_json = $json;

            $regexes = [];
            $regexes[ '#' ] = '/' . preg_quote('#', '/') . '(.*?)$' . '/m';
            $regexes[ '//' ] = '/' . preg_quote('//', '/') . '(.*?)$' . '/m';
            $regexes[ '/*' ] = '/' . preg_quote('/*', '/') . '([\s\S]*?)' . preg_quote('*/', '/') . '/m';

            foreach ( $regexes as $substr => $regex ) {
                if (false === strpos($_json, $substr)) {
                    continue;
                }

                $_json = preg_replace($regex, '$1', $_json);
            }

            $result = $this->_json_decode(
                $_json, $associative,
                $depth, $flags
            );
        }

        if ([] !== $result) {
            [ $value ] = $result;

        } elseif ([] !== $fallback) {
            [ $value ] = $fallback;

        } else {
            throw new RuntimeException(
                [ 'Unable to `jsonc_decode`', $json ]
            );
        }

        return $value;
    }

    /**
     * @return array{ 0?: mixed }
     */
    protected function _json_decode(
        ?string $json, ?bool $associative = null,
        ?int $depth = null, ?int $flags = null
    ) : array
    {
        $error = null;

        if (null === $json) {
            return [];
        }

        if ('' === $json) {
            return [ '' ];
        }

        $value = null;

        $depth = $depth ?? $this->static_json_depth();
        $flags = $flags ?? $this->static_json_decode_flags();

        error_clear_last();

        try {
            $value = json_decode($json, $associative, $depth, $flags);
        }
        catch ( \Throwable $e ) {
        }

        if (error_get_last()) {
            $value = null;
        }

        if (null === $value) {
            return [];
        }

        return [ $value ];
    }


    /**
     * @param array{ 0?: string } $fallback
     */
    public function json_encode(
        $value, array $fallback = [],
        ?bool $allowNull = null,
        ?int $flags = null, ?int $depth = null
    ) : ?string
    {
        $allowNull = $allowNull ?? false;
        $flags = $flags ?? $this->static_json_encode_flags();
        $depth = $depth ?? $this->static_json_depth();

        $theType = Lib::type();

        if (false
            || ($theType->resource($var, $value))
            || (is_float($value) && is_nan($value))
            || (! $allowNull && is_null($value))
        ) {
            $json = null;

        } else {
            error_clear_last();

            try {
                $json = json_encode($value, $flags, $depth);
            }
            catch ( \Throwable $e ) {
                $json = null;
            }

            if (error_get_last()) {
                $json = null;
            }
        }

        if (null === $json) {
            if ([] !== $fallback) {
                [ $json ] = $fallback;

            } else {
                throw new RuntimeException(
                    [
                        'Unable to `json_encode`',
                        $value,
                    ]
                );
            }
        }

        return $json;
    }


    public function json_print(
        $value, array $fallback = [],
        ?bool $allowNull = null,
        ?int $flags = null, ?int $depth = null
    ) : ?string
    {
        $flags = $flags ?? (
            $this->static_json_encode_flags()
            | JSON_UNESCAPED_LINE_TERMINATORS
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        $json = $this->json_encode(
            $value,
            $fallback,
            $allowNull,
            $flags, $depth
        );

        return $json;
    }
}
