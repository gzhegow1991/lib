<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;


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
            throw new RuntimeException(
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
     * @param Ret $ret
     *
     * @return mixed
     */
    public function json_decode(
        $json, ?bool $associative = null,
        ?int $depth = null, ?int $flags = null,
        $ret = null
    )
    {
        if (! Lib::type()->string_not_empty($jsonString, $json)) {
            return Result::err(
                $ret,
                [ 'The `json` should be a non-empty string', $json ],
                [ __FILE__, __LINE__ ]
            );
        }

        $depth = $depth ?? $this->static_json_depth();
        $flags = $flags ?? $this->static_json_decode_flags();

        try {
            $result = Lib::func()->safe_call(
                'json_decode',
                [ $jsonString, $associative, $depth, $flags ],
            );
        }
        catch ( \Throwable $e ) {
            return Result::err(
                $ret,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        if (null === $result) {
            return Result::err(
                $ret,
                [ 'Unable to `json_decode` due to invalid JSON', $json ],
                [ __FILE__, __LINE__ ]
            );
        }

        return $result;
    }

    /**
     * @param Ret $ret
     *
     * @return mixed
     */
    public function jsonc_decode(
        ?string $jsonc, ?bool $associative = null,
        ?int $depth = null, ?int $flags = null,
        $ret = null
    )
    {
        if (! Lib::type()->string_not_empty($jsonString, $jsonc)) {
            return Result::err(
                $ret,
                [ 'The `jsonc` should be a non-empty string', $jsonc ],
                [ __FILE__, __LINE__ ]
            );
        }

        $jsonString = $jsonc;

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

        $depth = $depth ?? $this->static_json_depth();
        $flags = $flags ?? $this->static_json_decode_flags();

        try {
            $result = Lib::func()->safe_call(
                'json_decode',
                [ $jsonString, $associative, $depth, $flags ],
            );
        }
        catch ( \Throwable $e ) {
            return Result::err(
                $ret,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        if (null === $result) {
            return Result::err(
                $ret,
                [ 'Unable to `jsonc_decode` due to invalid JSON', $jsonc ],
                [ __FILE__, __LINE__ ]
            );
        }

        return $result;
    }


    /**
     * @param Ret $ret
     *
     * @return string|mixed
     */
    public function json_encode(
        $value, ?bool $allowNull = null,
        ?int $flags = null, ?int $depth = null,
        $ret = null
    )
    {
        $allowNull = $allowNull ?? false;

        if (null === $value) {
            if (! $allowNull) {
                return Result::err(
                    $ret,
                    [ 'The NULL values cannot be encoded to JSON when `allowsNull` is set to FALSE', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Result::ok($ret, 'NULL');
        }

        if (false
            || (is_float($value) && is_nan($value))
            || (Lib::type()->resource($var, $value))
        ) {
            return Result::err(
                $ret,
                [ 'The values of types [ NAN ][ resource ] cannot be encoded to JSON', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $flags = $flags ?? $this->static_json_encode_flags();
        $depth = $depth ?? $this->static_json_depth();

        try {
            $result = Lib::func()->safe_call(
                'json_encode',
                [ $value, $flags, $depth ],
            );
        }
        catch ( \Throwable $e ) {
            return Result::err(
                $ret,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Result::ok($ret, $result);
    }

    /**
     * @param Ret $ret
     *
     * @return string|mixed
     */
    public function json_print(
        $value, ?bool $allowNull = null,
        ?int $flags = null, ?int $depth = null,
        $ret = null
    )
    {
        $allowNull = $allowNull ?? false;

        if (null === $value) {
            if (! $allowNull) {
                return Result::err(
                    $ret,
                    [ 'Unable to `json_encode`', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Result::ok($ret, 'NULL');
        }

        if (false
            || (is_float($value) && is_nan($value))
            || (Lib::type()->resource($var, $value))
        ) {
            return Result::err(
                $ret,
                [ 'The values of types [ NAN ][ resource ] cannot be encoded to JSON', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $flags = $flags ?? $this->static_json_encode_flags();
        $flags = $flags
            | JSON_PRETTY_PRINT
            | JSON_UNESCAPED_LINE_TERMINATORS
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        ;

        $depth = $depth ?? $this->static_json_depth();

        try {
            $result = Lib::func()->safe_call(
                'json_encode',
                [ $value, $flags, $depth ],
            );
        }
        catch ( \Throwable $e ) {
            return Result::err(
                $ret,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Result::ok($ret, $result);
    }
}
