<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class UrlModule
{
    /**
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>
     */
    public function type_url(
        $url, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null,
        array $refs = []
    )
    {
        $isHostIdnaAscii = $isHostIdnaAscii ?? 0;
        $isLinkUrlencoded = $isLinkUrlencoded ?? 0;

        $theHttp = Lib::http();
        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        $hasQuery = (null !== $query);
        $hasFragment = (null !== $fragment);

        if (null === $url) {
            return Ret::err(
                [ 'The `url` should not be null', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (true === $url) {
            $urlStringNotEmpty = $this->url_current();

        } elseif (! $theType->string_not_empty($url)->isOk([ &$urlStringNotEmpty, &$ret ])) {
            return $ret;
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

        $refParseUrl = parse_url($urlStringNotEmpty);

        if (false === $refParseUrl) {
            return Ret::err(
                [ 'The `url` should be valid url', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (empty($refParseUrl[ 'host' ])) {
            if (! isset($_SERVER[ 'HTTP_HOST' ])) {
                return Ret::err(
                    [ 'The `url` requires host (domain) prefix', $url ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $urlStringNotEmpty = $this->host_current() . '/' . ltrim($urlStringNotEmpty, '/');

            $refParseUrl = parse_url($urlStringNotEmpty);

            if (false === $refParseUrl) {
                return Ret::err(
                    [ 'The `url` should be valid url', $url ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        if (isset($refParseUrl[ 'host' ])) {
            if (false
                || (-2 === $isHostIdnaAscii)
                || (-1 === $isHostIdnaAscii)
                || (1 === $isHostIdnaAscii)
                || (2 === $isHostIdnaAscii)
            ) {
                if (-2 === $isHostIdnaAscii) {
                    $utf8 = $theHttp->idn_to_utf8($refParseUrl[ 'host' ]);

                    if (false === $utf8) {
                        return Ret::err(
                            [ 'Cannot encode `url` host to UTF8 using `idn_to_utf8`', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    $refParseUrl[ 'host' ] = $utf8;

                } elseif (-1 === $isHostIdnaAscii) {
                    $test = $refParseUrl[ 'host' ];

                    if ($theHttp->idn_to_utf8($test) !== $test) {
                        return Ret::err(
                            [ 'The `url` host should be valid UTF8 idn', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                } elseif (1 === $isHostIdnaAscii) {
                    $test = $refParseUrl[ 'host' ];

                    if ($theHttp->idn_to_ascii($test) !== $test) {
                        return Ret::err(
                            [ 'The `url` host should be valid ASCII idn', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                } elseif (2 === $isHostIdnaAscii) {
                    $ascii = $theHttp->idn_to_ascii($refParseUrl[ 'host' ]);

                    if (false === $ascii) {
                        return Ret::err(
                            [ 'Cannot encode `url` host to ASCII using `idn_to_ascii`', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    $refParseUrl[ 'host' ] = $ascii;
                }
            }
        }

        if (isset($refParseUrl[ 'path' ])) {
            if (1 === $isLinkUrlencoded) {
                $test = str_replace('/', '', $refParseUrl[ 'path' ]);

                if (urlencode($test) !== $test) {
                    return Ret::err(
                        [ 'The `url` path should already be URL-encoded', $url ],
                        [ __FILE__, __LINE__ ]
                    );
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
            $httpQuery = null;
            if ($hasQuery && $wasQuery) {
                $httpQuery = $theHttp->http_build_query_array($refParseUrl[ 'query' ], $_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ($hasQuery) {
                $httpQuery = $theHttp->http_build_query_array($_query);
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

        return Ret::val($result);
    }

    /**
     * @param string|true $url
     *
     * @return Ret<string>
     */
    public function type_host(
        $url,
        ?int $isHostIdnaAscii = null,
        array $refs = []
    )
    {
        $isHostIdnaAscii = $isHostIdnaAscii ?? 0;

        $theHttp = Lib::http();
        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $url) {
            return Ret::err(
                [ 'The `url` should not be null', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (true === $url) {
            $urlStringNotEmpty = $this->url_current();

        } elseif (! $theType->string_not_empty($url)->isOk([ &$urlStringNotEmpty, &$ret ])) {
            return $ret;
        }

        $refParseUrl = parse_url($urlStringNotEmpty);

        if (false === $refParseUrl) {
            return Ret::err(
                [ 'The `url` should be valid url', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (empty($refParseUrl[ 'host' ])) {
            if (! isset($_SERVER[ 'HTTP_HOST' ])) {
                return Ret::err(
                    [ 'The `url` requires host (domain) prefix', $url ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $urlStringNotEmpty = $this->host_current() . '/' . ltrim($urlStringNotEmpty, '/');

            $refParseUrl = parse_url($urlStringNotEmpty);

            if (false === $refParseUrl) {
                return Ret::err(
                    [ 'The `url` should be valid url', $url ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        if (isset($refParseUrl[ 'host' ])) {
            if (false
                || (-2 === $isHostIdnaAscii)
                || (-1 === $isHostIdnaAscii)
                || (1 === $isHostIdnaAscii)
                || (2 === $isHostIdnaAscii)
            ) {
                if (-2 === $isHostIdnaAscii) {
                    $utf8 = $theHttp->idn_to_utf8($refParseUrl[ 'host' ]);

                    if (false === $utf8) {
                        return Ret::err(
                            [ 'Cannot encode `url` host to UTF8 using `idn_to_utf8`', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    $refParseUrl[ 'host' ] = $utf8;

                } elseif (-1 === $isHostIdnaAscii) {
                    $test = $refParseUrl[ 'host' ];

                    if ($theHttp->idn_to_utf8($test) !== $test) {
                        return Ret::err(
                            [ 'The `url` host should be valid UTF8 idn', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                } elseif (1 === $isHostIdnaAscii) {
                    $test = $refParseUrl[ 'host' ];

                    if ($theHttp->idn_to_ascii($test) !== $test) {
                        return Ret::err(
                            [ 'The `url` host should be valid ASCII idn', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                } elseif (2 === $isHostIdnaAscii) {
                    $ascii = $theHttp->idn_to_ascii($refParseUrl[ 'host' ]);

                    if (false === $ascii) {
                        return Ret::err(
                            [ 'Cannot encode `url` host to ASCII using `idn_to_ascii`', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    $refParseUrl[ 'host' ] = $ascii;
                }
            }
        }

        $refParseUrl[ 'path' ] = null;
        $refParseUrl[ 'query' ] = null;
        $refParseUrl[ 'fragment' ] = null;

        $result = $this->host_build($refParseUrl);

        return Ret::val($result);
    }

    /**
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>
     */
    public function type_link(
        $url, $query = null, $fragment = null,
        ?int $isLinkUrlencoded = null,
        array $refs = []
    )
    {
        $isLinkUrlencoded = $isLinkUrlencoded ?? 0;

        $theHttp = Lib::http();
        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $url) {
            return Ret::err(
                [ 'The `url` should not be null', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (true === $url) {
            $urlStringNotEmpty = $this->url_current();

        } elseif (! $theType->string_not_empty($url)->isOk([ &$urlStringNotEmpty, &$ret ])) {
            return $ret;
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

        $refParseUrl = parse_url($urlStringNotEmpty);

        if (false === $refParseUrl) {
            return Ret::err(
                [ 'The `url` should be valid url', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($refParseUrl[ 'path' ])) {
            return Ret::err(
                [ 'The `url` should have path', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (1 === $isLinkUrlencoded) {
            $test = str_replace('/', '', $refParseUrl[ 'path' ]);

            if (urlencode($test) !== $test) {
                return Ret::err(
                    [ 'The `url` path should already be URL-encoded', $url ],
                    [ __FILE__, __LINE__ ]
                );
            }

        } elseif (2 === $isLinkUrlencoded) {
            $refParseUrl[ 'path' ] = urlencode($refParseUrl[ 'path' ]);
            $refParseUrl[ 'path' ] = str_replace('%2F', '/', $refParseUrl[ 'path' ]);
        }

        $wasQuery = isset($refParseUrl[ 'query' ]);

        if (false === $_query) {
            unset($refParseUrl[ 'query' ]);

        } else {
            $httpQuery = null;
            if ($hasQuery && $wasQuery) {
                $httpQuery = $theHttp->http_build_query_array($refParseUrl[ 'query' ], $_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ($hasQuery) {
                $httpQuery = $theHttp->http_build_query_array($_query);
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

        return Ret::val($result);
    }


    /**
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function url(
        $url = true, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null
    ) : string
    {
        $args = [
            $url,
            $query,
            $fragment,
            $isHostIdnaAscii,
            $isLinkUrlencoded,
        ];

        $result = $this->type_url(...$args)->orThrow();

        return $result;
    }

    /**
     * @param string|true $url
     */
    public function host(
        $url = true,
        ?int $isHostIdnaAscii = null
    ) : string
    {
        $args = [
            $url,
            $isHostIdnaAscii
        ];

        $result = $this->type_host(...$args)->orThrow();

        return $result;
    }

    /**
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function link(
        $url = true, $query = null, $fragment = null,
        ?int $isLinkUrlencoded = null
    ) : string
    {
        $args = [
            $url,
            $query,
            $fragment,
            $isLinkUrlencoded,
        ];

        $result = $this->type_link(...$args)->orThrow();

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
