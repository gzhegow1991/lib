<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class UrlModule
{
    /**
     * @param string|null             $r
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function type_url(
        &$r,
        $url, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null,
        array $refs = []
    ) : bool
    {
        $r = null;

        $isHostIdnaAscii = $isHostIdnaAscii ?? 0;
        $isLinkUrlencoded = $isLinkUrlencoded ?? 0;

        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        $hasQuery = (null !== $query);
        $hasFragment = (null !== $fragment);

        if (null === $url) {
            return false;
        }

        if (true === $url) {
            $urlString = $this->url_current();

        } elseif (! $theType->string_not_empty($urlString, $url)) {
            return false;
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

        $refParseUrl = parse_url($urlString);

        if (false === $refParseUrl) {
            return false;
        }

        if (empty($refParseUrl[ 'host' ])) {
            if (! isset($_SERVER[ 'HTTP_HOST' ])) {
                return false;
            }

            $urlString = $this->host_current() . '/' . ltrim($urlString, '/');

            $refParseUrl = parse_url($urlString);

            if (false === $refParseUrl) {
                return false;
            }
        }

        if (isset($refParseUrl[ 'host' ])) {
            if (false
                || (-2 === $isHostIdnaAscii)
                || (-1 === $isHostIdnaAscii)
                || (1 === $isHostIdnaAscii)
                || (2 === $isHostIdnaAscii)
            ) {
                if (! extension_loaded('intl')) {
                    throw new ExtensionException(
                        'Missing PHP extension: intl'
                    );
                }

                if (-2 === $isHostIdnaAscii) {
                    $utf8 = idn_to_utf8($refParseUrl[ 'host' ]);

                    if (false === $utf8) {
                        return false;
                    }

                    $refParseUrl[ 'host' ] = $utf8;

                } elseif (-1 === $isHostIdnaAscii) {
                    $test = $refParseUrl[ 'host' ];

                    if (idn_to_utf8($test) !== $test) {
                        return false;
                    }

                } elseif (1 === $isHostIdnaAscii) {
                    $test = $refParseUrl[ 'host' ];

                    if (idn_to_ascii($test) !== $test) {
                        return false;
                    }

                } elseif (2 === $isHostIdnaAscii) {
                    $ascii = idn_to_ascii($refParseUrl[ 'host' ]);

                    if (false === $ascii) {
                        return false;
                    }

                    $refParseUrl[ 'host' ] = $ascii;
                }
            }
        }

        if (isset($refParseUrl[ 'path' ])) {
            if (1 === $isLinkUrlencoded) {
                $test = str_replace('/', '', $refParseUrl[ 'path' ]);

                if (urlencode($test) !== $test) {
                    return false;
                }

            } elseif (2 === $isLinkUrlencoded) {
                $refParseUrl[ 'path' ] = urlencode($refParseUrl[ 'path' ]);
                $refParseUrl[ 'path' ] = str_replace('%2F', '/', $refParseUrl[ 'path' ]);
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

        $r = $result;

        return true;
    }

    /**
     * @param string|null $r
     *
     * @param string|true $url
     */
    public function type_host(
        &$r,
        $url,
        ?int $isIdnaAscii = null,
        array $refs = []
    ) : bool
    {
        $r = null;

        $isIdnaAscii = $isIdnaAscii ?? 0;

        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $url) {
            return false;
        }

        if (true === $url) {
            $urlString = $this->url_current();

        } elseif (! $theType->string_not_empty($urlString, $url)) {
            return false;
        }

        $refParseUrl = parse_url($urlString);

        if (false === $refParseUrl) {
            return false;
        }

        if (empty($refParseUrl[ 'host' ])) {
            if (! isset($_SERVER[ 'HTTP_HOST' ])) {
                return false;
            }

            $urlString = $this->host_current() . '/' . ltrim($urlString, '/');

            $refParseUrl = parse_url($urlString);

            if (false === $refParseUrl) {
                return false;
            }
        }

        if (false
            || (-2 === $isIdnaAscii)
            || (-1 === $isIdnaAscii)
            || (1 === $isIdnaAscii)
            || (2 === $isIdnaAscii)
        ) {
            if (! extension_loaded('intl')) {
                throw new ExtensionException(
                    'Missing PHP extension: intl'
                );
            }

            if (-2 === $isIdnaAscii) {
                $utf8 = idn_to_utf8($refParseUrl[ 'host' ]);

                if (false === $utf8) {
                    return false;
                }

                $refParseUrl[ 'host' ] = $utf8;

            } elseif (-1 === $isIdnaAscii) {
                $test = $refParseUrl[ 'host' ];

                if (idn_to_utf8($test) !== $test) {
                    return false;
                }

            } elseif (1 === $isIdnaAscii) {
                $test = $refParseUrl[ 'host' ];

                if (idn_to_ascii($test) !== $test) {
                    return false;
                }

            } elseif (2 === $isIdnaAscii) {
                $ascii = idn_to_ascii($refParseUrl[ 'host' ]);

                if (false === $ascii) {
                    return false;
                }

                $refParseUrl[ 'host' ] = $ascii;
            }
        }

        $refParseUrl[ 'path' ] = null;
        $refParseUrl[ 'query' ] = null;
        $refParseUrl[ 'fragment' ] = null;

        $result = $this->host_build($refParseUrl);

        unset($refParseUrl);

        $r = $result;

        return true;
    }

    /**
     * @param string|null             $r
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function type_link(
        &$r,
        $url, $query = null, $fragment = null,
        ?int $isUrlencoded = null,
        array $refs = []
    ) : bool
    {
        $r = null;

        $isUrlencoded = $isUrlencoded ?? 0;

        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $url) {
            return false;
        }

        if (true === $url) {
            $urlString = $this->link_current();

        } elseif (! $theType->string_not_empty($urlString, $url)) {
            return false;
        }

        $_query = null
            ?? ((false === $query) ? false : null)
            ?? (is_array($query) ? $query : null)
            ?? (is_string($query) ? [ $query ] : null);

        $_fragment = null
            ?? ((false === $fragment) ? false : null)
            ?? (is_string($fragment) ? $fragment : null);

        $hasQuery = (null !== $query);

        if ($hasQuery && (null === $_query)) {
            throw new LogicException(
                [ 'The `query` should be a string, an array or a false', $query ]
            );
        }

        $hasFragment = (null !== $fragment);

        if ($hasFragment && (null === $_fragment)) {
            throw new LogicException(
                [ 'The `fragment` should be a string or the FALSE', $fragment ]
            );
        }

        $refParseUrl = parse_url($urlString);

        if (false === $refParseUrl) {
            return false;
        }

        if (! isset($refParseUrl[ 'path' ])) {
            return false;
        }

        if (1 === $isUrlencoded) {
            $test = str_replace('/', '', $refParseUrl[ 'path' ]);

            if (urlencode($test) !== $test) {
                return false;
            }

        } elseif (2 === $isUrlencoded) {
            $refParseUrl[ 'path' ] = urlencode($refParseUrl[ 'path' ]);
            $refParseUrl[ 'path' ] = str_replace('%2F', '/', $refParseUrl[ 'path' ]);
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

        $r = $result;

        return true;
    }


    /**
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function url(
        $url = true, $query = null, $fragment = null,
        ?int $toHostIdnaAscii = null, ?int $toLinkUrlencoded = null
    ) : string
    {
        $this->type_url(
            $urlString,
            $url, $query, $fragment,
            $toHostIdnaAscii, $toLinkUrlencoded
        );

        return $urlString;
    }

    /**
     * @param string|true $url
     */
    public function host(
        $url = true,
        ?int $toIdnaAscii = null
    ) : string
    {
        $this->type_host(
            $hostString,
            $url,
            $toIdnaAscii
        );

        return $hostString;
    }

    /**
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function link(
        $url = true, $query = null, $fragment = null,
        ?int $toLinkUrlencoded = null
    ) : ?string
    {
        $this->type_link(
            $linkString,
            $url, $query, $fragment,
            $toLinkUrlencoded
        );

        return $linkString;
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


    public function url_referrer(
        $url = null, $query = null, $fragment = null,
        ?int $toHostIdnaAscii = null, ?int $toLinkUrlencoded = null
    ) : string
    {
        $url = $url ?? $_SERVER[ 'HTTP_REFERER' ] ?? true;

        $result = $this->url(
            $url, $query, $fragment,
            $toHostIdnaAscii, $toLinkUrlencoded
        );

        return $result;
    }

    public function host_referrer(
        $url = null,
        ?int $toIdnaAscii = null
    ) : string
    {
        $url = $url ?? $_SERVER[ 'HTTP_REFERER' ] ?? true;

        $result = $this->host(
            $url,
            $toIdnaAscii
        );

        return $result;
    }

    public function link_referrer(
        $url = null, $query = null, $fragment = null,
        ?int $toUrlencoded = null
    ) : string
    {
        $url = $url ?? $_SERVER[ 'HTTP_REFERER' ] ?? true;

        $result = $this->link(
            $url, $query, $fragment,
            $toUrlencoded
        );

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
