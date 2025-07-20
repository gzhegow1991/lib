<?php

namespace Gzhegow\Lib\Modules\Http\HttpCookie;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
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
    protected $cookieParams = [];


    public function __toString()
    {
        return $this->toString();
    }


    public function toString(array $options = []) : string
    {
        $optionsString = '';

        foreach ( $this->cookieParams as $option => $value ) {
            $optionsString .= "; {$option}={$value}";
        }

        return "SET-COOKIE: {$this->name}={$this->value}; path={$this->path}; domain={$this->domain}{$optionsString}";
    }


    public function toArray(array $options = []) : array
    {
        return $this->toArraySetrawcookieArgs();
    }

    public function toArraySetrawcookieArgs() : array
    {
        $theDate = Lib::$date;
        $theType = Lib::$type;

        $cookieExpiresString = $this->cookieParams[ 'expires' ] ?? '';

        $cookieExpires = -99999;
        if ($theType->idate_formatted($cookieExpiresString, 'D, d M Y H:i:s T')->isOk([ &$cookieExpiresDate ])) {
            $now = $theDate->idate_now();
            $cookieExpires = $cookieExpiresDate->getTimestamp() - $now->getTimestamp();

        } elseif ($theType->int($cookieExpiresString)->isOk([ &$cookieExpiresInt ])) {
            $cookieExpires = $cookieExpiresInt;
        }

        $cookieOptions = [
            'path'     => $this->path,
            'domain'   => $this->domain,
            //
            'httponly' => $this->cookieParams[ 'httponly' ] ?? null,
            //
            'secure'   => $this->cookieParams[ 'secure' ] ?? null,
            'samesite' => $this->cookieParams[ 'samesite' ] ?? null,
            //
            'expires'  => $cookieExpires,
        ];

        return [ $this->name, $this->value, $cookieOptions ];
    }


    /**
     * @return static|Ret<static>
     */
    public static function from($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromObjectHttpHeader($from)->orNull($ret)
            ?? static::fromArraySetrawcookieArgs($from)->orNull($ret)
            ?? static::fromString($from)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::val($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
            return Ret::val($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be instance of ' . static::class, $from ],
            [ __FILE__, __LINE__ ],
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromObjectHttpHeader($from, ?array $fallback = null)
    {
        $theType = Lib::$type;

        if (! ($from instanceof HttpHeader)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be instance of ' . HttpHeader::class, $from ],
                [ __FILE__, __LINE__ ],
            );
        }

        $header = $from;

        if ('SET-COOKIE' !== $header->getName()) {
            return Ret::throw(
                $fallback,
                [ 'The `header` name should be SET-COOKIE', $header ],
                [ __FILE__, __LINE__ ],
            );
        }

        $headerValue = $header->getValue();

        $headerArray = explode('=', $headerValue, 2);
        $headerArray = array_map('trim', $headerArray);

        [ $cookieName, $cookieValue ] = $headerArray;

        if (! $theType
            ->string_not_empty($cookieName)
            ->isOk([ 1 => &$ret ])
        ) {
            return Ret::throw($fallback, $ret);
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
        $instance->cookieParams = $headerParams;

        return Ret::val($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromArraySetrawcookieArgs($from, ?array $fallback = null)
    {
        $theType = Lib::$type;

        if (! is_array($from)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ],
            );
        }

        $name = $from[ 'name' ] ?? $from[ 0 ] ?? null;
        $value = $from[ 'value' ] ?? $from[ 1 ] ?? '';
        $expiresOrOptions = $from[ 'expires_or_options' ] ?? $from[ 2 ] ?? null;

        if (! $theType->string_not_empty($name)->isOk([ 1 => &$ret ])) {
            return Ret::throw($fallback, $ret);
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
            return Ret::throw(
                $fallback,
                [ 'The `from[expires_or_options]` or `from[2]` should be an array or an integer', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $path = $path ?? '/';
        $expires = $expires ?? 0;

        $httponly = $httponly ?? true;

        $secure = $secure ?? false;

        if (! $theType
            ->int($expires)
            ->isOk([ &$expiresInt, &$ret ])
        ) {
            return Ret::throw($fallback, $ret);
        }

        if (! $theType
            ->string_not_empty($path)
            ->isOk([ &$pathStringNotEmpty, &$ret ])
        ) {
            return Ret::throw($fallback, $ret);
        }

        $domainString = null;
        if (null !== $domain) {
            if (! $theType
                ->string_not_empty($domain)
                ->isOk([ &$domainStringNotEmpty, &$ret ])
            ) {
                return Ret::throw($fallback, $ret);
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
        $instance->path = $pathStringNotEmpty;
        $instance->domain = $domainString;
        $instance->cookieParams = $optionsLower;

        return Ret::val($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromString($from, ?array $fallback = null)
    {
        if (! HttpHeader::fromString($from)->isOk([ &$httpHeaderObject, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        if (! static::fromObjectHttpHeader($httpHeaderObject)->isOk([ &$httpCookieObject, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::val($fallback, $httpCookieObject);
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
        return $this->cookieParams;
    }
}
