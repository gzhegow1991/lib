<?php

namespace Gzhegow\Lib\Modules\Http\HttpCookie;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Modules\Http\HttpHeader\HttpHeader;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;


class HttpCookie implements
    ToStringInterface,
    ToArrayInterface
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $value;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $domain;
    /**
     * @var array<string, string>
     */
    protected $options = [];


    public function __toString()
    {
        return $this->toString();
    }


    public function toString(array $options = []) : string
    {
        $optionsString = '';

        foreach ( $this->options as $option => $value ) {
            $optionsString .= "; {$option}={$value}";
        }

        return "SET-COOKIE: {$this->name}={$this->value}; path={$this->path}; domain={$this->domain}{$optionsString}";
    }


    public function toArray(array $options = []) : array
    {
        return $this->toArraySetrawcookieArgs($options);
    }

    public function toArraySetrawcookieArgs(array $options = []) : array
    {
        $theDate = Lib::date();

        $cookieExpiresString = $this->options[ 'expires' ] ?? '';

        $cookieExpires = 0;
        if ($theDate->type_idate_formatted($r, $cookieExpiresString, 'D, d M Y H:i:s T')) {
            $cookieExpires = $cookieExpiresString;
        }

        $cookieOptions = [
            'path'     => $this->path,
            'domain'   => $this->domain,
            //
            'expires'  => $cookieExpires,
            //
            'httponly' => $this->options[ 'httponly' ] ?? null,
            //
            'secure'   => $this->options[ 'secure' ] ?? null,
            'samesite' => $this->options[ 'samesite' ] ?? null,
        ];

        return [ $this->name, $this->value, $cookieOptions ];
    }


    /**
     * @return static|bool|null
     */
    public static function from($from, $ret = null)
    {
        $cur = Result::asValueNull();

        $instance = null
            ?? static::fromStatic($from, $cur)
            ?? static::fromObjectHttpHeader($from, $cur)
            ?? static::fromArraySetrawcookieArgs($from, $cur)
            ?? static::fromString($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ret, $cur);
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, $ret = null)
    {
        if ($from instanceof static) {
            return Result::ok($ret, $from);
        }

        return Result::err(
            $ret,
            [ 'The `from` should be instance of ' . static::class, $from ],
            [ __FILE__, __LINE__ ],
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromObjectHttpHeader($from, $ret = null)
    {
        $theType = Lib::type();

        if (! ($from instanceof HttpHeader)) {
            return Result::err(
                $ret,
                [ 'The `from` should be instance of ' . HttpHeader::class, $from ],
                [ __FILE__, __LINE__ ],
            );
        }

        $header = $from;

        if ('SET-COOKIE' !== $header->getName()) {
            return Result::err(
                $ret,
                [ 'The `header` name should be SET-COOKIE', $header ],
                [ __FILE__, __LINE__ ]
            );
        }

        $headerValue = $header->getValue();

        [
            $cookieName,
            $cookieValue,
        ] = array_map('trim', explode('=', $headerValue, 2));

        if (! $theType->string_not_empty($cookieNameString, $cookieName)) {
            return Result::err(
                $ret,
                [ 'The `from` should be string' ],
                [ __FILE__, __LINE__ ]
            );
        }

        $cookieValueRawurlencode = rawurlencode($cookieValue);

        $headerParams = $header->getParams();

        $cookiePath = $headerParams[ 'path' ];
        $cookieDomain = $headerParams[ 'domain' ] ?? null;
        unset($headerParams[ 'path' ]);
        unset($headerParams[ 'domain' ]);

        $instance = new static();
        $instance->name = $cookieName;
        $instance->value = $cookieValueRawurlencode;
        $instance->path = $cookiePath;
        $instance->domain = $cookieDomain;
        $instance->options = $headerParams;

        return Result::ok($ret, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromArraySetrawcookieArgs($from, $ret = null)
    {
        $theType = Lib::type();

        if (! is_array($from)) {
            return Result::err(
                $ret,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ],
            );
        }

        $name = $from[ 'name' ] ?? $from[ 0 ] ?? null;
        $value = $from[ 'value' ] ?? $from[ 1 ] ?? '';
        $expiresOrOptions = $from[ 'expires_or_options' ] ?? $from[ 2 ] ?? null;

        if (! $theType->string_not_empty($nameString, $name)) {
            return Result::err(
                $ret,
                [ 'The `from[name]` or `from[0]` should be a non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $valueRawurlencode = rawurlencode($value);

        if (is_array($expiresOrOptions)) {
            $expires = $expiresOrOptions[ 'expires' ] ?? null;

            $path = $expiresOrOptions[ 'path' ] ?? null;
            $domain = $expiresOrOptions[ 'domain' ] ?? null;

            $httponly = $expiresOrOptions[ 'httponly' ] ?? null;

            $secure = $expiresOrOptions[ 'secure' ] ?? null;
            $samesite = $expiresOrOptions[ 'samesite' ] ?? null;

        } elseif (is_int($expiresOrOptions)) {
            $expires = $expiresOrOptions;

            $path = $from[ 'path' ] ?? $from[ 3 ] ?? null;
            $domain = $from[ 'domain' ] ?? $from[ 4 ] ?? null;

            $httponly = $from[ 'httponly' ] ?? $from[ 6 ] ?? null;

            $secure = $from[ 'secure' ] ?? $from[ 5 ] ?? null;
            $samesite = null;

        } else {
            return Result::err(
                $ret,
                [ 'The `from[expires_or_options]` or `from[2]` should be an array or an integer', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $path = $path ?? '/';
        $expires = $expires ?? 0;

        $httponly = $httponly ?? true;

        $secure = $secure ?? false;

        if (! $theType->int($expiresInt, $expires)) {
            return Result::err(
                $ret,
                [ 'The `expires` should be an integer', $expires ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->string_not_empty($pathString, $path)) {
            return Result::err(
                $ret,
                [ 'The `path` should be a non-empty string', $path ],
                [ __FILE__, __LINE__ ]
            );
        }

        $domainString = null;
        if (null !== $domain) {
            if (! $theType->string_not_empty($domainString, $domain)) {
                return Result::err(
                    $ret,
                    [ 'The `domain` should be a non-empty string', $domain ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        $optionsLower = [
            'expires'  => $expiresInt,
            //
            'httponly' => $httponly,
            //
            'secure'   => $secure,
            'samesite' => $samesite,
        ];

        $instance = new static();
        $instance->name = $name;
        $instance->value = $valueRawurlencode;
        $instance->path = $pathString;
        $instance->domain = $domainString;
        $instance->options = $optionsLower;

        return Result::ok($ret, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromString($from, $ret = null)
    {
        $header = HttpHeader::fromString($from, $cur = Result::asValueNull());

        if ($cur->isErr()) {
            return Result::err($ret, $cur);
        }

        $instance = static::fromObjectHttpHeader($header, $cur = Result::asValueNull());

        if ($cur->isErr()) {
            return Result::err($ret, $cur);
        }

        return Result::ok($ret, $instance);
    }


    public function getName() : string
    {
        return $this->name;
    }


    public function getValue() : string
    {
        return $this->value;
    }

    public function getValueRawurldecode() : string
    {
        return rawurldecode($this->value);
    }


    public function getPath() : string
    {
        return $this->path;
    }


    public function hasDomain(?string &$result = null) : bool
    {
        $result = null;

        if (null !== $this->domain) {
            $result = $this->domain;

            return true;
        }

        return false;
    }

    public function getDomain() : string
    {
        return $this->domain;
    }


    public function getOptions() : array
    {
        return $this->options;
    }
}
