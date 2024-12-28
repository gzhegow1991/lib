<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


class UrlModule
{
    public function url(
        $url = '', $query = null, $fragment = null,
        array &$parseUrlResult = null
    ) : ?string
    {
        if ('' === $url) {
            $url = $this->current();

        } elseif (null === $url) {
            return null;
        }

        if (null === ($_url = Lib::parse()->string_not_empty($url))) {
            return null;
        }

        $_parseUrlResult = $parseUrlResult ?? null;

        $_query = null
            ?? ((false === $query) ? false : null)
            ?? (is_array($query) ? $query : null)
            ?? (is_string($query) ? [ $query ] : null);

        $_fragment = null
            ?? ((false === $fragment) ? false : null)
            ?? (is_string($fragment) ? $fragment : null);

        if ((null !== $query) && (null === $_query)) {
            throw new LogicException(
                [ 'The `query` should be string, array or false', $query ]
            );
        }

        if ((null !== $fragment) && (null === $_fragment)) {
            throw new LogicException(
                [ 'The `fragment` should be string or false', $fragment ]
            );
        }

        if (! $_parseUrlResult) {
            $_parseUrlResult = parse_url($_url);

            if (empty($_parseUrlResult[ 'host' ])) {
                $_url = $this->host_current() . '/' . ltrim($_url, '/');

                $_parseUrlResult = parse_url($_url);
            }
        }

        if (false === $_query) {
            unset($_parseUrlResult[ 'query' ]);

        } elseif ($_query || isset($_parseUrlResult[ 'query' ])) {
            $httpQuery = Lib::http()->build_query_array($_parseUrlResult[ 'query' ] ?? null, $_query);

            $httpQuery = http_build_query($httpQuery);

            $_parseUrlResult[ 'query' ] = $httpQuery;
        }

        if (false === $_fragment) {
            unset($_parseUrlResult[ 'fragment' ]);

        } else {
            $_parseUrlResult[ 'fragment' ] = $_fragment;
        }

        $_url = $this->build($_parseUrlResult);

        $parseUrlResult = $_parseUrlResult;

        return $_url;
    }

    public function host(
        $url = '',
        array &$parseUrlResult = null
    ) : ?string
    {
        if ('' === $url) {
            $url = $this->host_current();

        } elseif (null === $url) {
            return null;
        }

        if (null === ($_url = Lib::parse()->string_not_empty($url))) {
            return null;
        }

        $_parseUrlResult = $parseUrlResult ?? null;

        if (! $_parseUrlResult) {
            $_parseUrlResult = parse_url($_url);

            if (empty($_parseUrlResult[ 'host' ])) {
                $_url = $this->host_current();

                $_parseUrlResult = parse_url($_url);
            }
        }

        $_parseUrlResult = []
            + [
                'path'     => null,
                'query'    => null,
                'fragment' => null,
            ]
            + $_parseUrlResult;

        $_url = $this->build($_parseUrlResult);

        $parseUrlResult = $_parseUrlResult;

        return $_url;
    }

    public function link(
        $url = '', $query = null, $fragment = null,
        array &$parseUrlResult = null
    ) : ?string
    {
        if ('' === $url) {
            $url = $this->link_current();

        } elseif (null === $url) {
            return null;
        }

        if (null === ($_link = Lib::parse()->string_not_empty($url))) {
            return null;
        }

        $_parseUrlResult = $parseUrlResult ?? null;

        $_query = null
            ?? ((false === $query) ? false : null)
            ?? (is_array($query) ? $query : null)
            ?? (is_string($query) ? [ $query ] : null);

        $_fragment = null
            ?? ((false === $fragment) ? false : null)
            ?? (is_string($fragment) ? $fragment : null);

        if ((null !== $query) && (null === $_query)) {
            throw new LogicException(
                [ 'The `query` should be string, array or false', $query ]
            );
        }

        if ((null !== $fragment) && (null === $_fragment)) {
            throw new LogicException(
                [ 'The `fragment` should be string or false', $fragment ]
            );
        }

        $_parseUrlResult = $_parseUrlResult ?? parse_url($_link);

        if (false === $_query) {
            unset($_parseUrlResult[ 'query' ]);

        } elseif ($_query || isset($_parseUrlResult[ 'query' ])) {
            $httpQuery = Lib::http()->build_query_array($_parseUrlResult[ 'query' ] ?? null, $_query);

            $httpQuery = http_build_query($httpQuery);

            $_parseUrlResult[ 'query' ] = $httpQuery;
        }

        if (false === $_fragment) {
            unset($_parseUrlResult[ 'fragment' ]);

        } else {
            $_parseUrlResult[ 'fragment' ] = $_fragment;
        }

        $_parseUrlResult = []
            + [
                'scheme' => null,
                'user'   => null,
                'pass'   => null,
                'host'   => null,
                'port'   => null,
            ]
            + $_parseUrlResult;

        $_link = $this->link_build($_parseUrlResult);

        $parseUrlResult = $_parseUrlResult;

        return $_link;
    }


    public function current() : string
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


    public function referrer($url = '') : ?string
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


    public function build(array $parseUrlResult) : string
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

        $urlPath = $_parseUrlResult[ 'path' ];
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

        $urlPath = $_parseUrlResult[ 'path' ];
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
