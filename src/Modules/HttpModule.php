<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Http\HttpHeader\HttpHeader;
use Gzhegow\Lib\Modules\Http\Cookies\DefaultCookies;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;
use Gzhegow\Lib\Modules\Http\Cookies\CookiesInterface;
use Gzhegow\Lib\Modules\Http\Session\SessionSafe\SessionSafe;
use Gzhegow\Lib\Modules\Http\Session\SessionSafe\SessionSafeProxy;
use Gzhegow\Lib\Modules\Http\Session\SessionDisabler\SessionDisabler;


class HttpModule
{
    /**
     * @var class-string<DefaultCookies>
     */
    protected static $cookiesClass = DefaultCookies::class;

    /**
     * @param class-string<CookiesInterface>|null $cookiesClass
     *
     * @return class-string<CookiesInterface>
     */
    public static function staticCookiesClass(?string $cookiesClass = null) : string
    {
        $last = static::$cookiesClass;

        if (null !== $cookiesClass) {
            if (! is_subclass_of($cookiesClass, CookiesInterface::class)) {
                throw new LogicException(
                    [ 'The `cookiesClass` should be subclass of: ' . CookiesInterface::class, $cookiesClass ]
                );
            }

            static::$cookiesClass = $cookiesClass;
        }

        static::$cookiesClass = static::$cookiesClass ?? DefaultCookies::class;

        return $last;
    }


    /**
     * @var CookiesInterface
     */
    protected $cookies;

    /**
     * @var SessionSafe
     */
    protected $sessionSafe;


    public function cookies() : CookiesInterface
    {
        if (null !== $this->cookies) {
            return $this->cookies;
        }

        $cookiesClass = $this->staticCookiesClass();

        return $this->cookies = $cookiesClass::getInstance();
    }


    public function newSessionSafe() : SessionSafeProxy
    {
        $sessionSafe = $this->createSessionSafe();

        return new SessionSafeProxy($sessionSafe);
    }

    public function cloneSessionSafe() : SessionSafeProxy
    {
        $sessionSafe = clone $this->getSessionSafe();

        return new SessionSafeProxy($sessionSafe);
    }

    public function sessionSafe() : SessionSafeProxy
    {
        $sessionSafe = $this->getSessionSafe();

        return new SessionSafeProxy($sessionSafe);
    }

    protected function createSessionSafe() : SessionSafe
    {
        return new SessionSafe();
    }

    protected function getSessionSafe() : SessionSafe
    {
        return $this->sessionSafe = $this->sessionSafe ?? $this->createSessionSafe();
    }


    public function is_ajax() : bool
    {
        $thePhp = Lib::php();

        if ($thePhp->is_terminal()) {
            return false;
        }

        $serverXRequestedWith = $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ?? null;
        $serverXRequestedWith = (string) $serverXRequestedWith;
        $serverXRequestedWith = strtolower($serverXRequestedWith);
        $serverXRequestedWith = $serverXRequestedWith ?: null;

        $isAjax = ('xmlhttprequest' === $serverXRequestedWith);

        return $isAjax;
    }

    public function is_web() : bool
    {
        $thePhp = Lib::php();

        if ($thePhp->is_terminal()) {
            return false;
        }

        if ($this->is_ajax()) {
            return false;
        }

        return true;
    }


    /**
     * @return static
     */
    public function disableSession()
    {
        if (isset($_SESSION)) {
            if (SessionDisabler::is($_SESSION)) {
                return $this;
            }

            if ([] !== $_SESSION) {
                throw new RuntimeException(
                    [ 'Unable to disable non-empty $_SESSION' ]
                );
            }
        }

        $sessionDisablerObject = SessionDisabler::new();

        $_SESSION =& $sessionDisablerObject;

        return $this;
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


    /**
     * @return static
     */
    public function header(string $header, ?bool $replace = null, ?int $response_code = null)
    {
        $replace = $replace ?? true;
        $response_code = $response_code ?? 0;

        $theFunc = Lib::func();

        $theFunc->safe_call(
            function () use ($header, $replace, $response_code) {
                header($header, $replace, $response_code);
            }
        );

        return $this;
    }

    /**
     * @return static
     */
    public function header_remove(?string $name)
    {
        $theFunc = Lib::func();

        $theFunc->safe_call(
            function () use ($name) {
                header_remove($name);
            }
        );

        return $this;
    }


    /**
     * @return static
     */
    public function setcookie(
        string $name, ?string $value = null,
        $expires_or_options = null,
        ?string $path = null, ?string $domain = null,
        ?bool $secure = null, ?bool $httponly = null
    )
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

        return $this;
    }

    /**
     * @return static
     */
    public function setrawcookie(
        string $name, ?string $value = null,
        $expires_or_options = null,
        ?string $path = null, ?string $domain = null,
        ?bool $secure = null, ?bool $httponly = null
    )
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

        return $this;
    }


    public function http_build_query_array($query, ...$queries) : array
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


    /**
     * @noinspection PhpStrFunctionsInspection
     */
    public function http_accept_match(string $httpAccept, array $acceptAnd = [], array ...$orAcceptAnd) : array
    {
        $theType = Lib::type();

        if ([] !== $acceptAnd) {
            array_unshift($orAcceptAnd, $acceptAnd);
        }

        $acceptList = [];

        $httpAcceptString = strtolower($httpAccept);
        if (0 === strpos($httpAcceptString, $substr = 'accept: ')) {
            $httpAcceptString = substr($httpAcceptString, strlen($substr));
        }

        $httpAcceptList = explode(',', $httpAcceptString);
        $httpAcceptList = array_map('trim', $httpAcceptList);

        foreach ( $httpAcceptList as $httpAcceptItem ) {
            $acceptVarsSplit = explode(';', $httpAcceptItem);

            $httpAcceptContentType = array_shift($acceptVarsSplit);

            $qValue = 1;
            $acceptVarsArray = [];
            foreach ( $acceptVarsSplit as $acceptVarsSplitItem ) {
                $acceptVarSplit = $acceptVarsSplitItem;
                $acceptVarSplit = explode('=', $acceptVarSplit, 2);
                $acceptVarSplit += [ '', '' ];

                [ $acceptVarName, $acceptVarValue ] = $acceptVarSplit;

                if ('q' === $acceptVarName) {
                    $qValue = $acceptVarValue;
                }

                $acceptVarsArray[ $acceptVarName ] = $acceptVarValue;
            }

            $qValueNumeric = $theType->numeric($qValue)->orNull();

            if (null === $qValueNumeric) {
                throw new LogicException(
                    [ 'The `httpAccept` has invalid header Accept value', [ $httpAcceptItem, 'q=' . $qValue ] ]
                );
            }

            $acceptList[ $httpAcceptContentType ] = [ $qValueNumeric, $acceptVarsArray ];
        }

        arsort($acceptList);

        if (! isset($acceptAnd)) {
            return $acceptList;
        }

        $result = [];

        foreach ( $orAcceptAnd as $acceptOrItem ) {
            $acceptAndList = array_map('strtolower', $acceptOrItem);
            $acceptAndList = array_filter($acceptAndList);

            $resultCurrent = [];

            foreach ( $acceptAndList as $acceptAndItem ) {
                if (! isset($acceptList[ $acceptAndItem ])) {
                    continue 2;
                }

                $resultCurrent[ $acceptAndItem ] = $acceptList[ $acceptAndItem ];
            }

            $result += $resultCurrent;
        }

        return $result;
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
}
