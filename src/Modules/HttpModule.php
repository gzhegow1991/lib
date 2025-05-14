<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Http\Cookies\Cookies;


class HttpModule
{
    /**
     * @var Cookies
     */
    protected $cookies;
    /**
     * @var object
     */
    protected $session;


    public function __construct()
    {
        $this->cookies = new Cookies();
    }


    public function headers_flush() : array
    {
        $headers = headers_list();

        foreach ( $headers as $header ) {
            [ $headerName ] = explode(':', $header, 2);

            header_remove($headerName);
        }

        return $headers;
    }


    public function headers_collect(?array $headersArray, array ...$headersArrays) : array
    {
        if ($headersArray) {
            array_unshift($headersArrays, $headersArray);
        }

        foreach ( $headersArrays as $idx => $_headerArray ) {
            if (null === $_headerArray) {
                unset($headersArrays[ $idx ]);
            }
        }

        $result = [];

        foreach ( Lib::arr()->walk_it($headersArrays) as $path => $header ) {
            /** @var string[] $path */

            if (! Lib::type()->string_not_empty($_header, $header)) {
                continue;
            }

            [ $headerName, $headerValue ] = explode(':', $_header, 2) + [ 1 => null ];

            if (null === $headerValue) {
                $headerValue = $headerName;
                $headerName = null;
            }

            if (null !== $headerName) {
                if (false !== strpos($headerName, ' ')) {
                    $headerValue = $_header;
                    $headerName = null;
                }
            }

            if (null === $headerName) {
                foreach ( array_reverse($path) as $current ) {
                    if (is_string($current)) {
                        $headerName = $current;

                        break;
                    }
                }
            }

            $result[ $headerName ][] = $headerValue;
        }

        return $result;
    }


    public function header(string $header, ?bool $replace = null, ?int $response_code = null) : void
    {
        $replace = $replace ?? true;
        $response_code = $response_code ?? 0;

        if (headers_sent($file, $line)) {
            throw new LogicException(
                "Headers already sent at {$file} : {$line}"
            );
        }

        header($header, $replace, $response_code);
    }

    public function header_remove(?string $name) : void
    {
        if (headers_sent($file, $line)) {
            throw new LogicException(
                "Headers already sent at {$file} : {$line}"
            );
        }

        header_remove($name);
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

        if (headers_sent($file, $line)) {
            throw new LogicException(
                "Headers already sent at {$file} : {$line}"
            );
        }

        setcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly);
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

        if (headers_sent($file, $line)) {
            throw new LogicException(
                "Headers already sent at {$file} : {$line}"
            );
        }

        setrawcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly);
    }


    public function static_cookies(?Cookies $cookies = null) : Cookies
    {
        if (null !== $cookies) {
            $last = $this->cookies;

            $this->cookies = $cookies;

            $result = $last;
        }

        $result = $result ?? $this->cookies;

        return $result;
    }

    public function cookie_has(string $name, &$result = null) : bool
    {
        $result = null;

        if ('' === $name) {
            return false;
        }

        if (! array_key_exists($name, $_COOKIE)) {
            return false;
        }

        // > convert empty string to null
        Lib::type()->string_not_empty($cookieString, $_COOKIE[ $name ]);

        $result = $cookieString;

        return true;
    }

    public function cookie_get(string $name, array $fallback = []) : ?string
    {
        if ('' === $name) {
            throw new LogicException(
                'The `name` should be non-empty string'
            );
        }

        $status = $this->cookie_has($name, $result);

        if (! $status) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new RuntimeException(
                "Missing COOKIE[ {$name} ]"
            );
        }

        return $result;
    }

    public function cookie_set(
        string $name, string $value, ?int $expires = null,
        ?string $path = null, ?string $domain = null,
        ?bool $secure = null, ?bool $httpOnly = null
    ) : void
    {
        if ('' === $name) {
            throw new LogicException(
                'The `name` should be non-empty string'
            );
        }

        if ($expires < 0) $expires = 0;

        $_name = $name ?: null;
        $_path = $path ?: '/';
        $_domain = $domain ?: null;

        $theCookies = $this->static_cookies();

        // > convert empty string to null
        Lib::type()->string_not_empty($valueString, $value);

        $_value = $valueString ?? ' ';
        $_value = rawurlencode($_value);

        $_expires = $expires ?: 0;
        $_secure = $secure ?? false;
        $_httpOnly = $httpOnly ?? false;

        $setrawcookieArgs = [
            $_name,
            $_value,
            $_expires,
            $_path,
            $_domain,
            $_secure,
            $_httpOnly,
        ];

        $theCookies->remove(
            $_name, $_path, $_domain
        );

        $theCookies->add(
            $setrawcookieArgs,
            $name, $_path, $_domain
        );
    }

    public function cookie_unset(
        string $name,
        ?string $path = null, ?string $domain = null,
        ?bool $secure = null, ?bool $httpOnly = null
    ) : void
    {
        // > смещение временной зоны в самых отвратительных кейсах может быть до 26 часов
        // > в секундах это 93600, пусть будет для красоты 99999

        $this->cookie_set(
            $name, ' ', time() - 99999,
            $path, $domain,
            $secure, $httpOnly
        );
    }


    public function static_session(?object $session = null) : ?object
    {
        /**
         * @noinspection PhpUndefinedNamespaceInspection
         *
         * @see          composer require symfony/http-foundation
         * @see          \Symfony\Component\HttpFoundation\Session\SessionInterface
         */

        if (null !== $session) {
            $last = $this->session;

            $this->session = $session;

            $result = $last;
        }

        $result = $result ?? $this->session;

        return $result;
    }

    public function session_has(string $name, &$result = null) : bool
    {
        $result = null;

        if (! strlen($name)) {
            throw new LogicException(
                'The `name` should be non-empty string'
            );
        }

        $theSession = $this->static_session();

        if (! $theSession->has($name)) {
            return false;
        }

        $result = $theSession->get($name);

        return true;
    }

    public function session_get(string $name, array $fallback = []) : bool
    {
        if (! strlen($name)) {
            throw new LogicException(
                'The `name` should be non-empty string'
            );
        }

        $theSession = $this->static_session();

        if (! $theSession->has($name)) {
            if ($fallback) {
                [ $fallback ] = $fallback;

                return $fallback;
            }

            throw new LogicException(
                'Missing session key: ' . $name
            );
        }

        $result = $theSession->get($name);

        return $result;
    }

    public function session_set(string $name, $value) : void
    {
        if (! strlen($name)) {
            throw new LogicException(
                'The `name` should be non-empty string'
            );
        }

        $theSession = $this->static_session();

        $theSession->set($name, $value);
    }

    public function session_unset(string $name)
    {
        if (! strlen($name)) {
            throw new LogicException(
                'The `name` should be non-empty string'
            );
        }

        $theSession = $this->static_session();

        $last = $theSession->remove($name);

        return $last;
    }


    public function data_replace(?array $dataArray, ?array ...$dataArrays) : array
    {
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
            Lib::arr()->walk_collect_it(
                $dataArrays,
                _ARR_WALK_WITH_EMPTY_ARRAYS,
                [ null ]
            ) as $path => $values
        ) {
            $last = end($values);

            if (false === $last) {
                foreach ( $dataArraysKeys as $key ) {
                    Lib::arr()->unset_path($dataArrays[ $key ], $path);
                }
            }
        }

        foreach (
            Lib::arr()->walk_it(
                $dataArrays,
                _ARR_WALK_WITH_EMPTY_ARRAYS
            )
            as $path => $value
        ) {
            if ([] === $value) {
                Lib::arr()->unset_path($dataArrays, $path);
            }
        }

        $result = array_replace_recursive(...$dataArrays);

        return $result;
    }

    public function data_merge(?array $dataArray, ?array ...$dataArrays) : array
    {
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
            Lib::arr()->walk_collect_it(
                $dataArrays,
                _ARR_WALK_WITH_EMPTY_ARRAYS,
                [ null ]
            ) as $path => $values
        ) {
            $last = end($values);

            if (false === $last) {
                foreach ( $dataArraysKeys as $key ) {
                    Lib::arr()->unset_path($dataArrays[ $key ], $path);
                }
            }
        }

        foreach (
            Lib::arr()->walk_it(
                $dataArrays,
                _ARR_WALK_WITH_EMPTY_ARRAYS
            )
            as $path => $value
        ) {
            if ([] === $value) {
                Lib::arr()->unset_path($dataArrays, $path);
            }
        }

        $result = array_merge_recursive(...$dataArrays);

        return $result;
    }


    public function build_query_array($query, ...$queries) : array
    {
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

            } elseif (Lib::type()->string_not_empty($_queryString, $_query)) {
                parse_str($_queryString, $queryArray);

                $queries[ $idx ] = $queryArray;
                unset($queryArray);

            } else {
                throw new LogicException(
                    [ 'Each of `queries` should be string or array', $query, $idx ]
                );
            }
        }

        $result = $this->data_merge(...$queries);

        return $result;
    }


    public function accept_match(string $httpAccept, $acceptAnd = null, ...$acceptOr) : array
    {
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

            // > convert non-numeric value to NULL
            Lib::type()->numeric($qValueNumeric, $qValue);

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
}
