<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Http\HttpHeader\HttpHeader;
use Gzhegow\Lib\Modules\Http\Cookies\DefaultCookies;
use Gzhegow\Lib\Modules\Http\Session\DefaultSession;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;
use Gzhegow\Lib\Modules\Http\Cookies\CookiesInterface;
use Gzhegow\Lib\Modules\Http\Session\SessionInterface;


class HttpModule
{
    /**
     * @var CookiesInterface
     */
    protected $cookies;
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var class-string<DefaultCookies>
     */
    protected $cookiesClass = DefaultCookies::class;
    /**
     * @var class-string<DefaultSession>
     */
    protected $sessionClass = DefaultSession::class;
    /**
     * @var array
     */
    protected $sessionOptions = [];


    public function cookies() : CookiesInterface
    {
        if (null !== $this->cookies) {
            return $this->cookies;
        }

        $cookiesClass = $this->static_cookies_class();

        return $this->cookies = $cookiesClass::getInstance();
    }

    public function session() : SessionInterface
    {
        if (null !== $this->session) {
            return $this->session;
        }

        $sessionClass = $this->static_session_class();

        return $this->session = $sessionClass::getInstance();
    }


    /**
     * @param class-string<CookiesInterface>|null $cookiesClass
     *
     * @return class-string<CookiesInterface>
     */
    public function static_cookies_class(?string $cookiesClass = null) : string
    {
        if (null !== $cookiesClass) {
            if (! is_subclass_of($cookiesClass, CookiesInterface::class)) {
                throw new LogicException(
                    [ 'The `cookiesClass` should be subclass of: ' . CookiesInterface::class, $cookiesClass ]
                );
            }

            $last = $this->cookiesClass;

            $this->cookiesClass = $cookiesClass;

            $result = $last;
        }

        $result = $result ?? $this->cookiesClass ?? DefaultCookies::class;

        return $result;
    }

    /**
     * @param class-string<SessionInterface>|null $sessionClass
     *
     * @return class-string<SessionInterface>
     */
    public function static_session_class(?string $sessionClass = null) : string
    {
        if (null !== $sessionClass) {
            if (! is_subclass_of($sessionClass, SessionInterface::class)) {
                throw new LogicException(
                    [ 'The `sessionClass` should be subclass of: ' . SessionInterface::class, $sessionClass ]
                );
            }

            $last = $this->sessionClass;

            $this->sessionClass = $sessionClass;

            $result = $last;
        }

        $result = $result ?? $this->sessionClass ?? DefaultSession::class;

        return $result;
    }

    public function static_session_options(?array $sessionOptions = null) : array
    {
        if (null !== $sessionOptions) {
            $last = $this->sessionOptions;

            $this->sessionOptions = $sessionOptions;

            $result = $last;
        }

        $result = $result ?? $this->sessionOptions ?? [];

        return $result;
    }


    /**
     * @return HttpHeader[]
     */
    public function headers_list(?array $headers_list = null) : array
    {
        $result = [];

        $headers_list = $headers_list ?? headers_list();

        foreach ( $headers_list as $header ) {
            $result[] = HttpHeader::fromString($header);
        }

        return $result;
    }

    public function headers_clear() : array
    {
        $headers = headers_list();

        foreach ( $headers as $header ) {
            [ $headerName ] = explode(':', $header, 2);

            header_remove($headerName);
        }

        return $headers;
    }


    public function header(string $header, ?bool $replace = null, ?int $response_code = null) : void
    {
        $replace = $replace ?? true;
        $response_code = $response_code ?? 0;

        $theFunc = Lib::func();

        $theFunc->safe_call(
            function () use ($header, $replace, $response_code) {
                header($header, $replace, $response_code);
            }
        );
    }

    public function header_remove(?string $name) : void
    {
        $theFunc = Lib::func();

        $theFunc->safe_call(
            function () use ($name) {
                header_remove($name);
            }
        );
    }


    public function setcookie(
        string $name, ?string $value = null,
        $expires_or_options = null,
        ?string $path = null, ?string $domain = null,
        ?bool $secure = null, ?bool $httponly = null
    ) : void
    {
        $value = $value ?? '';
        $expires_or_options = $expires_or_options ?? 0;
        $path = $path ?? '';
        $domain = $domain ?? '';
        $secure = $secure ?? false;
        $httponly = $httponly ?? false;

        $theFunc = Lib::func();

        $theFunc->safe_call(
            function () use ($name, $value, $expires_or_options, $path, $domain, $secure, $httponly) {
                is_array($expires_or_options)
                    ? setcookie($name, $value, $expires_or_options)
                    : setcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly);
            }
        );
    }

    public function setrawcookie(
        string $name, ?string $value = null,
        $expires_or_options = null,
        ?string $path = null, ?string $domain = null,
        ?bool $secure = null, ?bool $httponly = null
    ) : void
    {
        $value = $value ?? '';
        $expires_or_options = $expires_or_options ?? 0;
        $path = $path ?? '';
        $domain = $domain ?? '';
        $secure = $secure ?? false;
        $httponly = $httponly ?? false;

        $theFunc = Lib::func();

        $theFunc->safe_call(
            function () use ($name, $value, $expires_or_options, $path, $domain, $secure, $httponly) {
                is_array($expires_or_options)
                    ? setrawcookie($name, $value, $expires_or_options)
                    : setrawcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly);
            }
        );
    }


    public function data_replace(?array $dataArray, ?array ...$dataArrays) : array
    {
        $theArr = Lib::arr();

        if ($dataArray) {
            array_unshift($dataArrays, $dataArray);
        }

        foreach ( $dataArrays as $idx => $dataArrayItem ) {
            if (null === $dataArrayItem) {
                unset($dataArrays[ $idx ]);
            }
        }

        $dataArraysKeys = array_keys($dataArrays);

        foreach (
            $theArr->walk_collect_it(
                $dataArrays,
                _ARR_WALK_WITH_EMPTY_ARRAYS,
                [ null ]
            ) as $path => $values
        ) {
            $last = end($values);

            if (false === $last) {
                foreach ( $dataArraysKeys as $key ) {
                    $theArr->unset_path($dataArrays[ $key ], $path);
                }
            }
        }

        foreach (
            $theArr->walk_it(
                $dataArrays,
                _ARR_WALK_WITH_EMPTY_ARRAYS
            )
            as $path => $value
        ) {
            if ([] === $value) {
                $theArr->unset_path($dataArrays, $path);
            }
        }

        $result = array_replace_recursive(...$dataArrays);

        return $result;
    }

    public function data_merge(?array $dataArray, ?array ...$dataArrays) : array
    {
        $theArr = Lib::arr();

        if ($dataArray) {
            array_unshift($dataArrays, $dataArray);
        }

        foreach ( $dataArrays as $idx => $_dataArray ) {
            if (null === $_dataArray) {
                unset($dataArrays[ $idx ]);
            }
        }

        $dataArraysKeys = array_keys($dataArrays);

        foreach (
            $theArr->walk_collect_it(
                $dataArrays,
                _ARR_WALK_WITH_EMPTY_ARRAYS,
                [ null ]
            ) as $path => $values
        ) {
            $last = end($values);

            if (false === $last) {
                foreach ( $dataArraysKeys as $key ) {
                    $theArr->unset_path($dataArrays[ $key ], $path);
                }
            }
        }

        foreach (
            $theArr->walk_it(
                $dataArrays,
                _ARR_WALK_WITH_EMPTY_ARRAYS
            )
            as $path => $value
        ) {
            if ([] === $value) {
                $theArr->unset_path($dataArrays, $path);
            }
        }

        $result = array_merge_recursive(...$dataArrays);

        return $result;
    }


    public function build_query_array($query, ...$queries) : array
    {
        $theType = Lib::type();

        if ($queries) {
            array_unshift($queries, $query);
        }

        foreach ( $queries as $idx => $_query ) {
            if (null === $_query) {
                unset($queries[ $idx ]);
            }
        }

        foreach ( $queries as $idx => $_query ) {
            if (is_array($_query)) {
                continue;

            } elseif ($theType->string_not_empty($_query)->isOk([ &$_queryString ])) {
                parse_str($_queryString, $queryArray);

                $queries[ $idx ] = $queryArray;

                unset($queryArray);

            } else {
                throw new LogicException(
                    [ 'Each of `queries` should be a string or an array', $query, $idx ]
                );
            }
        }

        $result = $this->data_merge(...$queries);

        return $result;
    }


    public function accept_match(string $httpAccept, $acceptAnd = null, ...$acceptOr) : array
    {
        $theType = Lib::type();

        array_unshift($acceptOr, $acceptAnd);

        $acceptList = [];

        $accept = str_replace(' ', '', $httpAccept);
        $accept = strtolower($accept);
        $accept = explode(',', $accept);

        foreach ( $accept as $acceptItem ) {
            $qValue = 1;

            $acceptVarsArray = null;
            if (strpos($acceptItem, $substr = ';')) {
                $acceptVars = explode($substr, $acceptItem);

                $acceptItem = array_shift($acceptVars);

                foreach ( $acceptVars as $acceptVar ) {
                    [
                        $acceptVarName,
                        $acceptVarValue,
                    ] = explode('=', $acceptVar, 2) + [ null, '' ];

                    if ($acceptVarName === 'q') {
                        $qValue = $acceptVarValue;
                    }

                    $acceptVarsArray[ $acceptVarName ] = $acceptVarValue;
                }
            }

            $qValueNumeric = $theType->numeric($qValue)->orNull();

            $acceptList[ $acceptItem ] = [ $qValueNumeric, $acceptVarsArray ];
        }
        arsort($acceptList);

        if (! isset($acceptAnd)) {
            return $acceptList;
        }

        foreach ( $acceptOr as $i => $list ) {
            $list = (array) $list;
            $list = array_map('strtolower', $list);
            $list = array_filter($list);

            if ($list) {
                $acceptOr[ $i ] = $list;

            } else {
                unset($acceptOr[ $i ]);
            }

            $result = [];
            foreach ( $list as $item ) {
                if (null === $acceptList[ $item ][ 0 ]) {
                    continue 2;
                }

                $result[ $item ] = $acceptList[ $item ];
            }

            return $result;
        }

        return [];
    }


    /**
     * @return string|false
     */
    public function idn_to_ascii(string $domain, ?int $flags = null, ?int $variant = null, array $refs = [])
    {
        if (! extension_loaded('intl')) {
            throw new ExtensionException(
                'Missing PHP extension: intl'
            );
        }

        $flags = $flags ?? IDNA_DEFAULT;
        $variant = $variant ?? INTL_IDNA_VARIANT_UTS46;

        $withIdnaInfo = array_key_exists(0, $refs);
        if ($withIdnaInfo) {
            $refIdnaInfo =& $refs[ 0 ];
        }
        $refIdnaInfo = null;

        return $withIdnaInfo
            ? idn_to_ascii($domain, $flags, $variant, $refIdnaInfo)
            : idn_to_ascii($domain, $flags, $variant);
    }

    /**
     * @return string|false
     */
    public function idn_to_utf8(string $domain, ?int $flags = null, ?int $variant = null, array $refs = [])
    {
        if (! extension_loaded('intl')) {
            throw new ExtensionException(
                'Missing PHP extension: intl'
            );
        }

        $flags = $flags ?? IDNA_DEFAULT;
        $variant = $variant ?? INTL_IDNA_VARIANT_UTS46;

        $withIdnaInfo = array_key_exists(0, $refs);
        if ($withIdnaInfo) {
            $refIdnaInfo =& $refs[ 0 ];
        }
        $refIdnaInfo = null;

        return $withIdnaInfo
            ? idn_to_utf8($domain, $flags, $variant, $refIdnaInfo)
            : idn_to_utf8($domain, $flags, $variant);
    }
}
