<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;


class FormatJson
{
    /**
     * @var int
     */
    protected $stateJsonDepth;
    /**
     * @var int
     */
    protected $stateJsonEncodeFlags;
    /**
     * @var int
     */
    protected $stateJsonDecodeFlags;

    /**
     * @param int|false|null $jsonDepth
     */
    public function stateJsonDepth($jsonDepth = null) : ?int
    {
        $last = null;

        if ( $isChange = (null !== $jsonDepth) ) {
            $last = $this->stateJsonDepth;

            if ( false === $jsonDepth ) {
                $this->stateJsonDepth = null;

            } else {
                $theType = Lib::type();

                $jsonDepthValid = $theType->int_non_negative($jsonDepth)->orThrow();

                $this->stateJsonDepth = $jsonDepthValid;
            }
        }

        if ( null === $this->stateJsonDepth ) {
            $this->stateJsonDepth = 512;
        }

        return $isChange ? $last : $this->stateJsonDepth;
    }

    /**
     * @param int|false|null $jsonEncodeFlags
     */
    public function stateJsonEncodeFlags($jsonEncodeFlags = null) : ?int
    {
        $last = null;

        if ( $isChange = (null !== $jsonEncodeFlags) ) {
            $last = $this->stateJsonEncodeFlags;

            if ( false === $jsonEncodeFlags ) {
                $this->stateJsonEncodeFlags = null;

            } else {
                $theType = Lib::type();

                $jsonEncodeFlagsValid = $theType->int_non_negative($jsonEncodeFlags)->orThrow();

                $this->stateJsonEncodeFlags = $jsonEncodeFlagsValid;
            }
        }

        if ( null === $this->stateJsonEncodeFlags ) {
            $this->stateJsonEncodeFlags = 0;
        }

        return $isChange ? $last : $this->stateJsonEncodeFlags;
    }

    /**
     * @param int|false|null $jsonDecodeFlags
     */
    public function stateJsonDecodeFlags($jsonDecodeFlags = null) : ?int
    {
        $last = null;

        if ( $isChange = (null !== $jsonDecodeFlags) ) {
            $last = $this->stateJsonDecodeFlags;

            if ( false === $jsonDecodeFlags ) {
                $this->stateJsonDecodeFlags = null;

            } else {
                $theType = Lib::type();

                $jsonDecodeFlagsValid = $theType->int_non_negative($jsonDecodeFlags)->orThrow();

                $this->stateJsonDecodeFlags = $jsonDecodeFlagsValid;
            }
        }

        if ( null === $this->stateJsonDecodeFlags ) {
            $this->stateJsonDecodeFlags = 0;
        }

        return $isChange ? $last : $this->stateJsonDecodeFlags;
    }


    /**
     * @return Ret<mixed>|mixed
     */
    public function json_decode(
        $fb,
        $json, ?bool $isAssociative = null,
        ?int $depth = null, ?int $flags = null
    )
    {
        if ( null === $json ) {
            return Ret::throw(
                $fb,
                [ 'The `json` should be not null', $json ],
                [ __FILE__, __LINE__ ]
            );
        }

        $depth = $depth ?? $this->stateJsonDepth();
        $flags = $flags ?? $this->stateJsonDecodeFlags();

        $theType = Lib::type();

        $jsonStringNotEmpty = $theType->string_not_empty($json)->orThrow();

        $fnJsonDecode = Lib::fn('json_decode')->setSafe()->make();

        try {
            $result = $fnJsonDecode($jsonStringNotEmpty, $isAssociative, $depth, $flags);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null === $result ) {
            return Ret::throw(
                $fb,
                [ 'Unable to `json_decode` due to invalid JSON', $json ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }

    /**
     * @return Ret<mixed>|mixed
     */
    public function jsonc_decode(
        $fb,
        $jsonc, ?bool $isAssociative = null,
        ?int $depth = null, ?int $flags = null
    )
    {
        if ( null === $jsonc ) {
            return Ret::throw(
                $fb,
                [ 'The `jsonc` should be not null', $jsonc ],
                [ __FILE__, __LINE__ ]
            );
        }

        $depth = $depth ?? $this->stateJsonDepth();
        $flags = $flags ?? $this->stateJsonDecodeFlags();

        $theFunc = Lib::func();
        $theType = Lib::type();

        $ret = $theType->string_not_empty($jsonc);

        if ( ! $ret->isOk([ &$jsoncStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $regexes = [];
        $regexes['#'] = '/' . preg_quote('#', '/') . '(.*?)$' . '/m';
        $regexes['//'] = '/' . preg_quote('//', '/') . '(.*?)$' . '/m';
        $regexes['/*'] = '/' . preg_quote('/*', '/') . '([\s\S]*?)' . preg_quote('*/', '/') . '/m';

        foreach ( $regexes as $substr => $regex ) {
            if ( false === strpos($jsoncStringNotEmpty, $substr) ) {
                continue;
            }

            $jsoncStringNotEmpty = preg_replace($regex, '', $jsoncStringNotEmpty);
        }

        $fnJsonDecode = Lib::fn('json_decode')->setSafe()->make();

        try {
            $result = $fnJsonDecode($jsoncStringNotEmpty, $isAssociative, $depth, $flags);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null === $result ) {
            return Ret::throw(
                $fb,
                [ 'Unable to `jsonc_decode` due to invalid JSON', $jsonc ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }


    /**
     * @return Ret<string>|string
     */
    public function json_encode(
        $fb,
        $value, ?bool $isAllowNull = null,
        ?int $flags = null, ?int $depth = null
    )
    {
        $isAllowNull = $isAllowNull ?? false;

        $theFunc = Lib::func();
        $theType = Lib::type();

        if ( null === $value ) {
            if ( ! $isAllowNull ) {
                return Ret::throw(
                    $fb,
                    [ 'The value `NULL` cannot be encoded to JSON when `allowsNull` is set to FALSE', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok($fb, 'NULL');
        }

        if ( false
            || ($theType->nan($value)->isOk())
            || ($theType->resource($value)->isOk())
        ) {
            return Ret::throw(
                $fb,
                [ 'The value `NAN` or values of type `resource` cannot be encoded to JSON', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $flags = $flags ?? $this->stateJsonEncodeFlags();
        $depth = $depth ?? $this->stateJsonDepth();

        $fnJsonEncode = Lib::fn('json_encode')->setSafe()->make();

        try {
            $result = $fnJsonEncode($value, $flags, $depth);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }

    /**
     * @return Ret<string>|string
     */
    public function json_print(
        $fb,
        $value, ?bool $isAllowNull = null,
        ?int $flags = null, ?int $depth = null
    )
    {
        $isAllowNull = $isAllowNull ?? false;

        $theFunc = Lib::func();
        $theType = Lib::type();

        if ( null === $value ) {
            if ( ! $isAllowNull ) {
                return Ret::throw(
                    $fb,
                    [ 'Unable to `json_encode`', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::ok($fb, 'NULL');
        }

        if ( false
            || ($theType->nan($value)->isOk())
            || ($theType->resource($value)->isOk())
        ) {
            return Ret::throw(
                $fb,
                [ 'The value `NAN` or values of type `resource` cannot be encoded to JSON', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $flags = $flags ?? $this->stateJsonEncodeFlags();
        $flags = $flags
            | JSON_PRETTY_PRINT
            | JSON_UNESCAPED_LINE_TERMINATORS
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE;

        $depth = $depth ?? $this->stateJsonDepth();

        $fnJsonEncode = Lib::fn('json_encode')->setSafe()->make();

        try {
            $result = $fnJsonEncode($value, $flags, $depth);
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }
}
