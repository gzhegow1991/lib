<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class UrlModule
{
    /**
     * @param string|null       $r
     * @param string            $url
     * @param string|array|null $query
     * @param string|null       $fragment
     */
    public function type_url(
        &$r,
        $url, $query = null, $fragment = null,
        array $refs = []
    ) : bool
    {
        $r = null;

        try {
            $result = $this->url(
                $url, $query, $fragment,
                $refs
            );
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        if (null !== $result) {
            $r = $result;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $r
     * @param string      $url
     */
    public function type_host(
        &$r,
        $url,
        array $refs = []
    ) : bool
    {
        $r = null;

        try {
            $result = $this->host($url, $refs);
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        if (null !== $result) {
            $r = $result;

            return true;
        }

        return false;
    }

    /**
     * @param string|null       $r
     * @param string            $url
     * @param string|array|null $query
     * @param string|null       $fragment
     */
    public function type_link(
        &$r,
        $url, $query = null, $fragment = null,
        array $refs = []
    ) : bool
    {
        $r = null;

        try {
            $result = $this->link($url, $query, $fragment, $refs);
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        if (null !== $result) {
            $r = $result;

            return true;
        }

        return false;
    }


    /**
     * @param string                  $url
     * @param false|string|array|null $query
     * @param false|string|null       $fragment
     */
    public function url(
        ?string $url = '', $query = null, $fragment = null,
        array $refs = []
    ) : ?string
    {
        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        $hasQuery = (null !== $query);
        $hasFragment = (null !== $fragment);

        if (null === $url) {
            return null;
        }

        $_url = null
            ?? (('' === $url) ? $this->link_current() : null)
            ?? $url;

        if (! $theType->string_not_empty($_urlString, $_url)) {
            return null;
        }

        $_query = null
            ?? ((false === $query) ? false : null)
            ?? (is_array($query) ? $query : null)
            ?? (is_string($query) ? [ $query ] : null);

        $_fragment = null
            ?? ((false === $fragment) ? false : null)
            ?? (is_string($fragment) ? $fragment : null);

        if ($hasQuery && (null === $_query)) {
            throw new LogicException(
                [ 'The `query` should be a string, an array or a false', $query ]
            );
        }

        if ($hasFragment && (null === $_fragment)) {
            throw new LogicException(
                [ 'The `fragment` should be a string or the FALSE', $fragment ]
            );
        }

        if (null === $refParseUrl) {
            $refParseUrl = parse_url($_urlString);

            if (false === $refParseUrl) {
                return null;
            }
        }

        if (empty($refParseUrl[ 'host' ])) {
            $_urlString = $this->host_current() . '/' . ltrim($_urlString, '/');

            $refParseUrl = parse_url($_urlString);

            if (false === $refParseUrl) {
                return null;
            }
        }

        if (isset($refParseUrl[ 'path' ])) {
            $test = str_replace('/', '', $refParseUrl[ 'path' ]);

            if (urlencode($test) !== $test) {
                return null;
            }
        }

        $wasQuery = isset($refParseUrl[ 'query' ]);

        if (false === $_query) {
            unset($refParseUrl[ 'query' ]);

        } else {
            $theHttp = Lib::http();

            $httpQuery = null;
            if ($hasQuery && $wasQuery) {
                $httpQuery = $theHttp->build_query_array($refParseUrl[ 'query' ], $_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ($hasQuery) {
                $httpQuery = $theHttp->build_query_array($_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ($wasQuery) {
                $httpQuery = $refParseUrl[ 'query' ];
            }

            $refParseUrl[ 'query' ] = $httpQuery;
        }

        if (false === $_fragment) {
            unset($refParseUrl[ 'fragment' ]);

        } else {
            if ($hasFragment) {
                $refParseUrl[ 'fragment' ] = $_fragment;
            }
        }

        $result = $this->url_build($refParseUrl);

        unset($refParseUrl);

        return $result;
    }

    /**
     * @param string $url
     */
    public function host(
        ?string $url = '',
        array $refs = []
    ) : ?string
    {
        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $url) {
            return null;
        }

        $_url = null
            ?? (('' === $url) ? $this->link_current() : null)
            ?? $url;

        if (! $theType->string_not_empty($_urlString, $_url)) {
            return null;
        }

        if (null === $refParseUrl) {
            $refParseUrl = parse_url($_urlString);

            if (false === $refParseUrl) {
                return null;
            }
        }

        if (empty($refParseUrl[ 'host' ])) {
            $_urlString = $this->host_current() . '/' . ltrim($_urlString, '/');

            $refParseUrl = parse_url($_urlString);

            if (false === $refParseUrl) {
                return null;
            }
        }

        $refParseUrl[ 'path' ] = null;
        $refParseUrl[ 'query' ] = null;
        $refParseUrl[ 'fragment' ] = null;

        $result = $this->url_build($refParseUrl);

        unset($refParseUrl);

        return $result;
    }

    /**
     * @param string            $url
     * @param string|array|null $query
     * @param string|null       $fragment
     */
    public function link(
        ?string $url = '', $query = null, $fragment = null,
        array $refs = []
    ) : ?string
    {
        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        $hasQuery = (null !== $query);
        $hasFragment = (null !== $fragment);

        if (null === $url) {
            return null;
        }

        $_url = null
            ?? (('' === $url) ? $this->link_current() : null)
            ?? $url;

        if (! $theType->string_not_empty($_urlString, $_url)) {
            return null;
        }

        $_query = null
            ?? ((false === $query) ? false : null)
            ?? (is_array($query) ? $query : null)
            ?? (is_string($query) ? [ $query ] : null);

        $_fragment = null
            ?? ((false === $fragment) ? false : null)
            ?? (is_string($fragment) ? $fragment : null);

        if ($hasQuery && (null === $_query)) {
            throw new LogicException(
                [ 'The `query` should be a string, an array or a false', $query ]
            );
        }

        if ($hasFragment && (null === $_fragment)) {
            throw new LogicException(
                [ 'The `fragment` should be a string or the FALSE', $fragment ]
            );
        }

        if (null === $refParseUrl) {
            $refParseUrl = parse_url($_urlString);

            if (false === $refParseUrl) {
                return null;
            }
        }

        if (! isset($refParseUrl[ 'path' ])) {
            return null;

        } else {
            $test = str_replace('/', '', $refParseUrl[ 'path' ]);

            if (urlencode($test) !== $test) {
                return null;
            }
        }

        $wasQuery = isset($refParseUrl[ 'query' ]);

        if (false === $_query) {
            unset($refParseUrl[ 'query' ]);

        } else {
            $theHttp = Lib::http();

            $httpQuery = null;
            if ($hasQuery && $wasQuery) {
                $httpQuery = $theHttp->build_query_array($refParseUrl[ 'query' ], $_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ($hasQuery) {
                $httpQuery = $theHttp->build_query_array($_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ($wasQuery) {
                $httpQuery = $refParseUrl[ 'query' ];
            }

            $refParseUrl[ 'query' ] = $httpQuery;
        }

        if (false === $_fragment) {
            unset($refParseUrl[ 'fragment' ]);

        } else {
            if ($hasFragment) {
                $refParseUrl[ 'fragment' ] = $_fragment;
            }
        }

        $refParseUrl[ 'scheme' ] = null;
        $refParseUrl[ 'user' ] = null;
        $refParseUrl[ 'pass' ] = null;
        $refParseUrl[ 'host' ] = null;
        $refParseUrl[ 'port' ] = null;

        $result = $this->link_build($refParseUrl);

        unset($refParseUrl);

        return $result;
    }


    public function url_current() : string
    {
        $urlHostCurrent = $this->host_current();
        $urlLinkCurrent = $this->link_current();

        $result = "{$urlHostCurrent}{$urlLinkCurrent}";

        return $result;
    }

    public function host_current() : string
    {
        if (! isset($_SERVER[ 'HTTP_HOST' ])) {
            throw new RuntimeException(
                [ 'The `SERVER[HTTP_HOST]` is required', $_SERVER ]
            );
        }

        $serverHttpHost = $_SERVER[ 'HTTP_HOST' ];

        $serverHttps = $_SERVER[ 'HTTPS' ] ?? null;
        $serverPhpAuthUser = $_SERVER[ 'PHP_AUTH_USER' ] ?? null;
        $serverPhpAuthPw = $_SERVER[ 'PHP_AUTH_PW' ] ?? null;
        $serverPort = $_SERVER[ 'SERVER_PORT' ] ?? null;

        $scheme = ($serverHttps && ($serverHttps !== 'off')) ? 'https' : 'http';
        $isScheme = '://';

        $user = $serverPhpAuthUser ?: '';
        $pass = $serverPhpAuthPw ?: '';
        $isPass = $pass ? ':' : '';
        $isUserAndPass = ($user || $pass) ? '@' : '';

        $host = $serverHttpHost ?: '';

        $port = in_array($serverPort, [ 80, 443 ]) ? '' : $serverPort;
        $isPort = $port ? ':' : '';

        $result = implode('', [
            $scheme,
            $isScheme,
            $user,
            $isPass,
            $pass,
            $isUserAndPass,
            $host,
            $isPort,
            $port,
        ]);

        return $result;
    }

    public function link_current() : string
    {
        if (! isset($_SERVER[ 'REQUEST_URI' ])) {
            throw new RuntimeException(
                [ 'The `SERVER[REQUEST_URI]` is required', $_SERVER ]
            );
        }

        $serverRequestUri = $_SERVER[ 'REQUEST_URI' ];

        $serverQueryString = $_SERVER[ 'QUERY_STRING' ] ?? null;

        $requestUri = $serverRequestUri ?: '';
        [ $requestUri, $queryString ] = explode('?', $requestUri, 2) + [ '', null ];

        $queryString = $serverQueryString ?? $queryString ?? '';
        $isQueryString = $queryString ? '?' : null;

        $result = implode('', [
            $requestUri,
            $isQueryString,
            $queryString,
        ]);

        return $result;
    }


    public function url_referrer(?string $url = null) : ?string
    {
        $result = $this->url($url ?? $_SERVER[ 'HTTP_REFERER' ] ?? '');

        return $result;
    }

    public function host_referrer(?string $url = null) : ?string
    {
        $result = $this->host($url ?? $_SERVER[ 'HTTP_REFERER' ] ?? '');

        return $result;
    }

    public function link_referrer(?string $url = null) : ?string
    {
        $result = $this->link($url ?? $_SERVER[ 'HTTP_REFERER' ] ?? '');

        return $result;
    }


    public function url_build(array $parseUrlResult) : string
    {
        $_parseUrlResult = []
            + $parseUrlResult
            + [
                'scheme'   => null,
                'user'     => null,
                'pass'     => null,
                'host'     => null,
                'port'     => null,
                'path'     => null,
                'query'    => null,
                'fragment' => null,
            ];

        $urlScheme = $_parseUrlResult[ 'scheme' ];
        $isUrlScheme = $urlScheme ? '://' : '//';

        $urlUser = $_parseUrlResult[ 'user' ];
        $urlPass = $_parseUrlResult[ 'pass' ];
        $isPass = $urlPass ? ':' : '';
        $isUserAndPass = ($urlUser || $urlPass) ? '@' : '';

        $urlHost = $_parseUrlResult[ 'host' ];

        $urlPort = $_parseUrlResult[ 'port' ];
        $port = in_array($urlPort, [ 80, 443 ]) ? '' : $urlPort;
        $isPort = $port ? ':' : '';

        $urlPath = $_parseUrlResult[ 'path' ] ?? '';
        [ $path ] = explode('?', $urlPath, 2);

        $urlQuery = $_parseUrlResult[ 'query' ];
        $isQuery = $urlQuery ? '?' : null;

        $urlFragment = $_parseUrlResult[ 'fragment' ] ?: '';
        $isFragment = $urlFragment ? '#' : null;

        $result = implode('', [
            $urlScheme,
            $isUrlScheme,
            $urlUser,
            $isPass,
            $urlPass,
            $isUserAndPass,
            $urlHost,
            $isPort,
            $port,
            $path,
            $isQuery,
            $urlQuery,
            $isFragment,
            $urlFragment,
        ]);

        return $result;
    }

    public function host_build(array $parseUrlResult) : string
    {
        $_parseUrlResult = []
            + $parseUrlResult
            + [
                'scheme' => null,
                'user'   => null,
                'pass'   => null,
                'host'   => null,
                'port'   => null,
            ];

        $urlScheme = $_parseUrlResult[ 'scheme' ];
        $isUrlScheme = $urlScheme ? '://' : '//';

        $urlUser = $_parseUrlResult[ 'user' ];
        $urlPass = $_parseUrlResult[ 'pass' ];
        $isPass = $urlPass ? ':' : '';
        $isUserAndPass = ($urlUser || $urlPass) ? '@' : '';

        $urlHost = $_parseUrlResult[ 'host' ];

        $urlPort = $_parseUrlResult[ 'port' ];
        $port = in_array($urlPort, [ 80, 443 ]) ? '' : $urlPort;
        $isPort = $port ? ':' : '';

        $result = implode('', [
            $urlScheme,
            $isUrlScheme,
            $urlUser,
            $isPass,
            $urlPass,
            $isUserAndPass,
            $urlHost,
            $isPort,
            $port,
        ]);

        return $result;
    }

    public function link_build(array $parseUrlResult) : string
    {
        $_parseUrlResult = $parseUrlResult
            + [
                'scheme'   => null,
                'path'     => null,
                'query'    => null,
                'fragment' => null,
            ];

        $urlScheme = $_parseUrlResult[ 'scheme' ];
        $isUrlScheme = $urlScheme ? ':' : '';

        $urlPath = $_parseUrlResult[ 'path' ] ?? '';
        [ $path ] = explode('?', $urlPath, 2);

        $urlQuery = $_parseUrlResult[ 'query' ];
        $isQuery = $urlQuery ? '?' : null;

        $urlFragment = $_parseUrlResult[ 'fragment' ] ?: '';
        $isFragment = $urlFragment ? '#' : null;

        $result = implode('', [
            $urlScheme,
            $isUrlScheme,
            $path,
            $isQuery,
            $urlQuery,
            $isFragment,
            $urlFragment,
        ]);

        return $result;
    }
}
