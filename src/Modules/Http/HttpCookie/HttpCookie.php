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
        $theDate = Lib::date();
        $theType = Lib::type();

        $cookieExpiresString = $this->cookieParams['expires'] ?? '';

        $ret = $theType->idate_formatted($cookieExpiresString, 'D, d M Y H:i:s T');

        $cookieExpires = -99999;
        if ( $ret->isOk([ &$cookieExpiresDate ]) ) {
            $now = $theDate->idate_now();

            $cookieExpires = $cookieExpiresDate->getTimestamp() - $now->getTimestamp();

        } else {
            $ret = $theType->int($cookieExpiresString);

            if ( $ret->isOk([ &$cookieExpiresInt ]) ) {
                $cookieExpires = $cookieExpiresInt;
            }
        }

        $cookieOptions = [
            'path'     => $this->path,
            'domain'   => $this->domain,
            //
            'httponly' => $this->cookieParams['httponly'] ?? null,
            //
            'secure'   => $this->cookieParams['secure'] ?? null,
            'samesite' => $this->cookieParams['samesite'] ?? null,
            //
            'expires'  => $cookieExpires,
        ];

        return [ $this->name, $this->value, $cookieOptions ];
    }


    /**
     * @return Ret<static>|static
     */
    public static function from($from, $fb = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromObjectHttpHeader($from)->orNull($ret)
            ?? static::fromArraySetrawcookieArgs($from)->orNull($ret)
            ?? static::fromString($from)->orNull($ret);

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $instance);
    }

    /**
     * @return Ret<static>|static
     */
    public static function fromStatic($from, $fb = null)
    {
        if ( $from instanceof static ) {
            return Ret::ok($fb, $from);
        }

        return Ret::throw(
            $fb,
            [ 'The `from` should be instance of ' . static::class, $from ],
            [ __FILE__, __LINE__ ],
        );
    }

    /**
     * @return Ret<static>|static
     */
    public static function fromObjectHttpHeader($from, $fb = null)
    {
        $theType = Lib::type();

        if ( ! ($from instanceof HttpHeader) ) {
            return Ret::throw(
                $fb,
                [ 'The `from` should be instance of ' . HttpHeader::class, $from ],
                [ __FILE__, __LINE__ ],
            );
        }

        $header = $from;

        if ( 'SET-COOKIE' !== $header->getName() ) {
            return Ret::throw(
                $fb,
                [ 'The `header` name should be SET-COOKIE', $header ],
                [ __FILE__, __LINE__ ],
            );
        }

        $headerValue = $header->getValue();

        $headerArray = explode('=', $headerValue, 2);
        $headerArray = array_map('trim', $headerArray);

        [ $cookieName, $cookieValue ] = $headerArray;

        $ret = $theType->string_not_empty($cookieName);

        if ( ! $ret->isOk([ &$cookieNameString ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $cookieValueRawurlencode = rawurlencode($cookieValue);

        $headerParams = $header->getParams();

        $cookiePath = $headerParams['path'];
        $cookieDomain = $headerParams['domain'] ?? null;

        unset($headerParams['path']);
        unset($headerParams['domain']);

        $instance = new static();
        $instance->name = $cookieNameString;
        $instance->value = $cookieValueRawurlencode;
        $instance->path = $cookiePath;
        $instance->domain = $cookieDomain;
        $instance->cookieParams = $headerParams;

        return Ret::ok($fb, $instance);
    }

    /**
     * @return Ret<static>|static
     */
    public static function fromArraySetrawcookieArgs($from, $fb = null)
    {
        $theType = Lib::type();

        if ( ! is_array($from) ) {
            return Ret::throw(
                $fb,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ],
            );
        }

        $name = $from['name'] ?? $from[0] ?? null;
        $value = $from['value'] ?? $from[1] ?? '';
        $expiresOrOptions = $from['expires_or_options'] ?? $from[2] ?? null;

        $ret = $theType->string_not_empty($name);

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueRawurlencode = rawurlencode($value);

        if ( is_array($expiresOrOptions) ) {
            $expires = $expiresOrOptions['expires'] ?? null;

            $path = $expiresOrOptions['path'] ?? null;
            $domain = $expiresOrOptions['domain'] ?? null;

            $httponly = $expiresOrOptions['httponly'] ?? null;

            $secure = $expiresOrOptions['secure'] ?? null;
            $samesite = $expiresOrOptions['samesite'] ?? null;

        } elseif ( is_int($expiresOrOptions) ) {
            $expires = $expiresOrOptions;

            $path = $from['path'] ?? $from[3] ?? null;
            $domain = $from['domain'] ?? $from[4] ?? null;

            $httponly = $from['httponly'] ?? $from[6] ?? null;

            $secure = $from['secure'] ?? $from[5] ?? null;
            $samesite = null;

        } else {
            return Ret::throw(
                $fb,
                [ 'The `from[expires_or_options]` or `from[2]` should be an array or an integer', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $path = $path ?? '/';
        $expires = $expires ?? 0;

        $httponly = $httponly ?? true;

        $secure = $secure ?? false;

        $ret = $theType->int($expires);

        if ( ! $ret->isOk([ &$expiresInt ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $theType->string_not_empty($path);

        if ( ! $ret->isOk([ &$pathStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $domainString = null;
        if ( null !== $domain ) {
            $ret = $theType->string_not_empty($domain);

            if ( ! $ret->isOk([ &$domainStringNotEmpty ]) ) {
                return Ret::throw(
                    $fb,
                    $ret,
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
        $instance->path = $pathStringNotEmpty;
        $instance->domain = $domainString;
        $instance->cookieParams = $optionsLower;

        return Ret::ok($fb, $instance);
    }

    /**
     * @return Ret<static>|static
     */
    public static function fromString($from, $fb = null)
    {
        $ret = HttpHeader::fromString($from);

        if ( ! $ret->isOk([ &$httpHeaderObject ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = static::fromObjectHttpHeader($httpHeaderObject);

        if ( ! $ret->isOk([ &$httpCookieObject ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $httpCookieObject);
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

        if ( null !== $this->domain ) {
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
