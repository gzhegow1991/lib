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

            $this->jsonDepth = $jsonDepth;

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

            $this->jsonEncodeFlags = $jsonEncodeFlags;

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

            $this->jsonDecodeFlags = $jsonDecodeFlags;

            $result = $last;
        }

        $result = $result ?? $this->jsonDecodeFlags;

        return $result;
    }


    /**
     * @param array{ 0?: mixed } $fallback
     *
     * @return mixed
     */
    public function json_decode(
        ?string $json, ?bool $associative = null, array $fallback = [],
        ?int $depth = null, ?int $flags = null
    )
    {
        if (null === $json) {
            $result = [];

        } elseif ('' === $json) {
            $result = [ '' ];

        } else {
            $result = $this->_json_decode(
                $error,
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
     *
     * @return mixed
     */
    public function jsonc_decode(
        ?string $json, ?bool $associative = null, array $fallback = [],
        ?int $depth = null, ?int $flags = null
    )
    {
        if (null === $json) {
            $result = [];

        } elseif ('' === $json) {
            $result = [ '' ];

        } else {
            $jsonString = $json;

            $regexes = [];
            $regexes[ '#' ] = '/' . preg_quote('#', '/') . '(.*?)$' . '/m';
            $regexes[ '//' ] = '/' . preg_quote('//', '/') . '(.*?)$' . '/m';
            $regexes[ '/*' ] = '/' . preg_quote('/*', '/') . '([\s\S]*?)' . preg_quote('*/', '/') . '/m';

            foreach ( $regexes as $substr => $regex ) {
                if (false === strpos($jsonString, $substr)) {
                    continue;
                }

                $jsonString = preg_replace($regex, '$1', $jsonString);
            }

            $result = $this->_json_decode(
                $error,
                $jsonString, $associative,
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
        &$error,
        ?string $json, ?bool $associative = null,
        ?int $depth = null, ?int $flags = null
    ) : array
    {
        if (null === $json) {
            $error = new RuntimeException(
                [ 'The `json` should be not null', $json ]
            );

            $result = [];

        } elseif ('' === $json) {
            $error = null;

            $result = [ '' ];

        } else {
            $error = null;

            $depth = $depth ?? $this->static_json_depth();
            $flags = $flags ?? $this->static_json_decode_flags();

            error_clear_last();

            try {
                $result = json_decode(
                    $json, $associative,
                    $depth, $flags
                );

                if (null === $result) {
                    $error = new RuntimeException(
                        [ 'The `json` should be valid JSON string', $json ]
                    );

                    $result = [];

                } else {
                    $result = [ $result ];
                }
            }
            catch ( \Throwable $e ) {
                $error = $e;

                $result = [];
            }

            if (null === $error) {
                if ($error = error_get_last()) {
                    $result = [];
                }
            }
        }

        return $result;
    }


    /**
     * @param array{ 0?: string } $fallback
     */
    public function json_print(
        $value, array $fallback = [],
        ?bool $allowNull = null,
        ?int $flags = null, ?int $depth = null
    ) : ?string
    {
        $flags = $flags ?? $this->static_json_encode_flags();

        $flags = $flags
            | JSON_UNESCAPED_LINE_TERMINATORS
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES;

        $result = $this->_json_encode(
            $error,
            $value, $allowNull,
            $flags, $depth
        );

        if ([] !== $result) {
            [ $json ] = $result;

        } elseif ([] !== $fallback) {
            [ $json ] = $fallback;

        } else {
            throw new RuntimeException(
                [ 'Unable to `json_print`', $value ]
            );
        }

        return $json;
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
        $result = $this->_json_encode(
            $error,
            $value, $allowNull,
            $flags, $depth
        );

        if ([] !== $result) {
            [ $json ] = $result;

        } elseif ([] !== $fallback) {
            [ $json ] = $fallback;

        } else {
            throw new RuntimeException(
                [ 'Unable to `json_encode`', $value ]
            );
        }

        return $json;
    }

    /**
     * @return array{ 0?: mixed }
     */
    protected function _json_encode(
        &$error,
        $value,
        ?bool $allowNull = null,
        ?int $flags = null, ?int $depth = null
    ) : array
    {
        $allowNull = $allowNull ?? false;

        if (
            (is_float($value) && is_nan($value))
            || (Lib::type()->resource($var, $value))
        ) {
            $error = new RuntimeException(
                [ 'Unable to `json_encode`', $value ]
            );

            $json = [];

        } elseif (! $allowNull && is_null($value)) {
            $error = new RuntimeException(
                [ 'Unable to `json_encode`', $value ]
            );

            $json = [];

        } else {
            $flags = $flags ?? $this->static_json_encode_flags();
            $depth = $depth ?? $this->static_json_depth();

            error_clear_last();

            try {
                $json = json_encode($value, $flags, $depth);

                if (false === $json) {
                    $error = new RuntimeException(
                        [ 'Unable to `json_encode` given value', $value ]
                    );

                    $json = [];

                } else {
                    $json = [ $json ];
                }
            }
            catch ( \Throwable $e ) {
                $error = $e;

                $json = [];
            }

            if (null === $error) {
                if ($error = error_get_last()) {
                    $json = [];
                }
            }
        }

        return $json;
    }
}
