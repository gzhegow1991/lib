<?php

namespace Gzhegow\Lib\Modules;

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


    public function json_depth_static(int $jsonDepth = null) : int
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

    public function json_encode_flags_static(int $jsonEncodeFlags = null) : int
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

    public function json_decode_flags_static(int $jsonDecodeFlags = null) : int
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
        string $json, bool $associative = null,
        array $fallback = [],
        int $depth = null, int $flags = null
    ) // : mixed
    {
        $depth = $depth ?? $this->json_depth_static();
        $flags = $flags ?? $this->json_decode_flags_static();

        error_clear_last();

        try {
            $value = json_decode($json, $associative, $depth, $flags);
        }
        catch ( \Throwable $e ) {
            $value = null;
        }

        if (error_get_last()) {
            $value = null;
        }

        if (null === $value) {
            if (count($fallback)) {
                [ $value ] = $fallback;

            } else {
                throw new RuntimeException(
                    [
                        'Unable to `json_decode`',
                        $json,
                    ]
                );
            }
        }

        return $value;
    }

    /**
     * @param array{ 0?: mixed } $fallback
     */
    public function jsonc_decode(
        string $json, bool $associative = null,
        array $fallback = [],
        int $depth = null, int $flags = null
    ) // : mixed
    {
        $regex = [];
        $regex[] = preg_quote('#', '/') . '(.*?)$';
        $regex[] = preg_quote('/*', '/') . '([\s\S]*?)' . preg_quote('*/', '/');
        $regex[] = preg_quote('//', '/') . '(.*?)$';
        $regex = '/' . implode('|', $regex) . '/mu';

        $_json = preg_replace($regex, '$1', $json);

        $value = $this->json_decode(
            $_json, $associative,
            $fallback,
            $depth, $flags
        );

        return $value;
    }


    /**
     * @param array{ 0?: string } $fallback
     */
    public function json_encode(
        $value,
        array $fallback = [],
        int $flags = null, int $depth = null
    ) : ?string
    {
        $flags = $flags ?? $this->json_encode_flags_static();
        $depth = $depth ?? $this->json_depth_static();

        if (false
            || is_resource($value)
            || is_float($value) && is_nan($value)
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
            if (count($fallback)) {
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
        $value,
        array $fallback = [],
        int $flags = null, int $depth = null
    ) : ?string
    {
        $flags = $flags ?? (
            $this->json_encode_flags_static()
            | JSON_UNESCAPED_LINE_TERMINATORS
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        $json = $this->json_encode(
            $value, $fallback,
            $flags, $depth
        );

        return $json;
    }
}
