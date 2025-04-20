<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


class UrlModule
{
    /**
     * @param string|null       $result
     * @param string            $url
     * @param string|array|null $query
     * @param string|null       $fragment
     */
    public function type_url(
        &$result,
        $url, $query = null, $fragment = null,
        array $refs = []
    ) : bool
    {
        $result = null;

        $_value = $this->url($url, $query, $fragment, $refs);

        if (null !== $_value) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     * @param string      $url
     */
    public function type_host(
        &$result,
        $url,
        array $refs = []
    ) : bool
    {
        $result = null;

        $_value = $this->host($url, $refs);

        if (null !== $_value) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null       $result
     * @param string            $url
     * @param string|array|null $query
     * @param string|null       $fragment
     */
    public function type_link(
        &$result,
        $url, $query = null, $fragment = null,
        array $refs = []
    ) : bool
    {
        $result = null;

        $_value = $this->link($url, $query, $fragment, $refs);

        if (null !== $_value) {
            $result = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string            $url
     * @param string|array|null $query
     * @param string|null       $fragment
     */
    public function url(
        $url = '', $query = null, $fragment = null,
        array $refs = []
    ) : ?string
    {
        $withParseUrl = array_key_exists(0, $refs);

        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }

        $refParseUrl = null;

        $hasQuery = (null !== $query);
        $hasFragment = (null !== $fragment);

        if ('' === $url) {
            $url = $this->url_current();

        } elseif (null === $url) {
            return null;
        }

        if (! Lib::type()->string_not_empty($_url, $url)) {
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
                [ 'The `query` should be string, array or false', $query ]
            );
        }

        if ($hasFragment && (null === $_fragment)) {
            throw new LogicException(
                [ 'The `fragment` should be string or false', $fragment ]
            );
        }

        if (null === $refParseUrl) {
            $refParseUrl = parse_url($_url);

            if (false === $refParseUrl) {
                return null;
            }
        }

        if (empty($refParseUrl[ 'host' ])) {
            $_url = $this->host_current() . '/' . ltrim($_url, '/');

            $refParseUrl = parse_url($_url);

            if (false === $refParseUrl) {
                return null;
            }
        }

        if (! isset($refParseUrl[ 'path' ])) {
            return null;
        }

        $test = str_replace('/', '', $refParseUrl[ 'path' ]);

        if (urlencode($test) !== $test) {
            return null;
        }

        $wasQuery = isset($refParseUrl[ 'query' ]);
        $wasFragment = isset($refParseUrl[ 'fragment' ]);

        if (false === $_query) {
            unset($refParseUrl[ 'query' ]);

        } else {
            $httpQuery = null;
            if ($hasQuery && $wasQuery) {
                $httpQuery = Lib::http()->build_query_array($refParseUrl[ 'query' ], $_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ($hasQuery) {
                $httpQuery = Lib::http()->build_query_array($_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ($wasQuery) {
                $httpQuery = $refParseUrl[ 'query' ];
            }

            $_parseUrlResult[ 'query' ] = $httpQuery;
        }

        if (false === $_fragment) {
            unset($refParseUrl[ 'fragment' ]);

        } else {
            if ($hasFragment) {
                $refParseUrl[ 'fragment' ] = $_fragment;
            }
        }

        $_url = $this->url_build($refParseUrl);

        unset($refParseUrl);

        return $_url;
    }

    /**
     * @param string $url
     */
    public function host(
        $url = '',
        array $refs = []
    ) : ?string
    {
        $withParseUrl = array_key_exists(0, $refs);

        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }

        $refParseUrl = null;

        if ('' === $url) {
            $url = $this->host_current();

        } elseif (null === $url) {
            return null;
        }

        if (! Lib::type()->string_not_empty($_url, $url)) {
            return null;
        }

        if (null === $refParseUrl) {
            $refParseUrl = parse_url($_url);

            if (false === $refParseUrl) {
                return null;
            }
        }

        if (empty($refParseUrl[ 'host' ])) {
            $_url = $this->host_current() . '/' . ltrim($_url, '/');

            $refParseUrl = parse_url($_url);

            if (false === $refParseUrl) {
                return null;
            }
        }

        $refParseUrl[ 'path' ] = null;
        $refParseUrl[ 'query' ] = null;
        $refParseUrl[ 'fragment' ] = null;

        $_url = $this->url_build($refParseUrl);

        unset($refParseUrl);

        return $_url;
    }

    /**
     * @param string            $url
     * @param string|array|null $query
     * @param string|null       $fragment
     */
    public function link(
        $url = '', $query = null, $fragment = null,
        array $refs = []
    ) : ?string
    {
        $withParseUrl = array_key_exists(0, $refs);

        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }

        $refParseUrl = null;

        $hasQuery = (null !== $query);
        $hasFragment = (null !== $fragment);

        if ('' === $url) {
            $url = $this->link_current();

        } elseif (null === $url) {
            return null;
        }

        if (! Lib::type()->string_not_empty($_url, $url)) {
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
                [ 'The `query` should be string, array or false', $query ]
            );
        }

        if ($hasFragment && (null === $_fragment)) {
            throw new LogicException(
                [ 'The `fragment` should be string or false', $fragment ]
            );
        }

        if (null === $refParseUrl) {
            $refParseUrl = parse_url($_url);

            if (false === $refParseUrl) {
                return null;
            }
        }

        if (! isset($refParseUrl[ 'path' ])) {
            return null;
        }

        $test = str_replace('/', '', $refParseUrl[ 'path' ]);

        if (urlencode($test) !== $test) {
            return null;
        }

        $wasQuery = isset($refParseUrl[ 'query' ]);
        $wasFragment = isset($refParseUrl[ 'fragment' ]);

        if (false === $_query) {
            unset($refParseUrl[ 'query' ]);

        } else {
            $httpQuery = null;
            if ($hasQuery && $wasQuery) {
                $httpQuery = Lib::http()->build_query_array($refParseUrl[ 'query' ], $_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ($hasQuery) {
                $httpQuery = Lib::http()->build_query_array($_query);
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

        $_link = $this->link_build($refParseUrl);

        unset($refParseUrl);

        return $_link;
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
        $serverHttps = $_SERVER[ 'HTTPS' ] ?? null;
        $scheme = ($serverHttps && ($serverHttps !== 'off')) ? 'https' : 'http';
        $isScheme = '://';

        $serverPhpAuthUser = $_SERVER[ 'PHP_AUTH_USER' ] ?? null;
        $serverPhpAuthPw = $_SERVER[ 'PHP_AUTH_PW' ] ?? null;
        $user = $serverPhpAuthUser ?: '';
        $pass = $serverPhpAuthPw ?: '';
        $isPass = $pass ? ':' : '';
        $isUserAndPass = ($user || $pass) ? '@' : '';

        $serverHttpHost = $_SERVER[ 'HTTP_HOST' ] ?? null;
        $host = $serverHttpHost ?: '';

        $serverPort = $_SERVER[ 'SERVER_PORT' ] ?? null;
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
        $serverRequestUri = $_SERVER[ 'REQUEST_URI' ] ?? null;
        $requestUri = $serverRequestUri ?: '';
        [ $requestUri, $queryString ] = explode('?', $requestUri, 2) + [ '', null ];

        $serverQueryString = $_SERVER[ 'QUERY_STRING' ] ?? null;
        $queryString = $serverQueryString ?? $queryString ?? '';
        $isQueryString = $queryString ? '?' : null;

        $result = implode('', [
            $requestUri,
            $isQueryString,
            $queryString,
        ]);

        return $result;
    }


    public function url_referrer($url = '') : ?string
    {
        $result = $this->url($_SERVER[ 'HTTP_REFERER' ] ?? $url);

        return $result;
    }

    public function host_referrer($url = '') : ?string
    {
        $result = $this->host($_SERVER[ 'HTTP_REFERER' ] ?? $url);

        return $result;
    }

    public function link_referrer($url = '') : ?string
    {
        $result = $this->link($_SERVER[ 'HTTP_REFERER' ] ?? $url);

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
