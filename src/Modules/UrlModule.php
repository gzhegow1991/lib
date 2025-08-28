<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class UrlModule
{
    /**
     * @var string
     */
    protected $urlCurrent;
    /**
     * @var array
     */
    protected $urlCurrentParseUrl;

    /**
     * @var string
     */
    protected $hostCurrent;
    /**
     * @var array
     */
    protected $hostCurrentParseUrl;

    /**
     * @var string
     */
    protected $linkCurrent;
    /**
     * @var array
     */
    protected $linkCurrentParseUrl;


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

        if (! $theType->string_not_empty($url)->isOk([ &$urlStringNotEmpty, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $_query = null
            ?? ((false === $query) ? false : null)
            ?? (is_array($query) ? $query : null)
            ?? (is_string($query) ? [ $query ] : null);

        $_fragment = null
            ?? ((false === $fragment) ? false : null)
            ?? (is_string($fragment) ? $fragment : null);

        if ($hasQuery && (null === $_query)) {
            return Ret::err(
                [ 'The `query` should be a string, an array or a false', $query ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ($hasFragment && (null === $_fragment)) {
            return Ret::err(
                [ 'The `fragment` should be a string or the FALSE', $fragment ],
                [ __FILE__, __LINE__ ]
            );
        }

        $refParseUrl = parse_url($urlStringNotEmpty);

        if (false === $refParseUrl) {
            return Ret::err(
                [ 'The `url` should be valid url', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        $refParseUrl = array_replace(
            [
                'scheme'   => '',
                'user'     => '',
                'pass'     => '',
                'host'     => '',
                'port'     => '',
                'path'     => '',
                'query'    => '',
                'fragment' => '',
            ],
            $refParseUrl
        );

        $wasHost = ('' !== $refParseUrl[ 'host' ]);
        $wasPath = ('' !== $refParseUrl[ 'path' ]);

        if (! $wasHost) {
            return Ret::err(
                [ 'The `url` requires a host', $url, $refParseUrl ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $wasPath) {
            $refParseUrl[ 'path' ] = '/';
        }

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

        $wasQuery = ('' !== $refParseUrl[ 'query' ]);

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
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>
     */
    public function type_uri(
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

        if (! $theType->string_not_empty($url)->isOk([ &$urlStringNotEmpty, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $_query = null
            ?? ((false === $query) ? false : null)
            ?? (is_array($query) ? $query : null)
            ?? (is_string($query) ? [ $query ] : null);

        $_fragment = null
            ?? ((false === $fragment) ? false : null)
            ?? (is_string($fragment) ? $fragment : null);

        if ($hasQuery && (null === $_query)) {
            return Ret::err(
                [ 'The `query` should be a string, an array or a false', $query ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ($hasFragment && (null === $_fragment)) {
            return Ret::err(
                [ 'The `fragment` should be a string or the FALSE', $fragment ],
                [ __FILE__, __LINE__ ]
            );
        }

        $refParseUrl = parse_url($urlStringNotEmpty);

        if (false === $refParseUrl) {
            return Ret::err(
                [ 'The `url` should be valid url', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        $refParseUrl = array_replace(
            [
                'scheme'   => '',
                'user'     => '',
                'pass'     => '',
                'host'     => '',
                'port'     => '',
                'path'     => '',
                'query'    => '',
                'fragment' => '',
            ],
            $refParseUrl
        );

        $wasHost = ('' !== $refParseUrl[ 'host' ]);
        $wasPath = ('' !== $refParseUrl[ 'path' ]);

        if (! $wasPath && $wasHost) {
            $refParseUrl[ 'path' ] = '/';

            $wasPath = true;
        }

        if (! ($wasHost || $wasPath)) {
            return Ret::err(
                [ 'The `url` requires at least one `host` or `path`' ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ($wasHost) {
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

        if ($wasPath) {
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

        $wasQuery = ('' !== $refParseUrl[ 'query' ]);

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

        $result = $wasHost
            ? $this->url_build($refParseUrl)
            : $this->link_build($refParseUrl);

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

        if (! $theType->string_not_empty($url)->isOk([ &$urlStringNotEmpty, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $refParseUrl = parse_url($urlStringNotEmpty);

        if (false === $refParseUrl) {
            return Ret::err(
                [ 'The `url` should be valid url', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        $refParseUrl = array_replace(
            [
                'scheme'   => '',
                'user'     => '',
                'pass'     => '',
                'host'     => '',
                'port'     => '',
                'path'     => '',
                'query'    => '',
                'fragment' => '',
            ],
            $refParseUrl
        );

        $wasHost = ('' !== $refParseUrl[ 'host' ]);

        if (! $wasHost) {
            return Ret::err(
                [ 'The `url` requires a host', $url, $refParseUrl ],
                [ __FILE__, __LINE__ ]
            );
        }

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

        $hasQuery = (null !== $query);
        $hasFragment = (null !== $fragment);

        if (null === $url) {
            return Ret::err(
                [ 'The `url` should not be null', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->string_not_empty($url)->isOk([ &$urlStringNotEmpty, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $_query = null
            ?? ((false === $query) ? false : null)
            ?? (is_array($query) ? $query : null)
            ?? (is_string($query) ? [ $query ] : null);

        $_fragment = null
            ?? ((false === $fragment) ? false : null)
            ?? (is_string($fragment) ? $fragment : null);


        if ($hasQuery && (null === $_query)) {
            return Ret::err(
                [ 'The `query` should be a string, an array or a false', $query ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ($hasFragment && (null === $_fragment)) {
            return Ret::err(
                [ 'The `fragment` should be a string or the FALSE', $fragment ],
                [ __FILE__, __LINE__ ]
            );
        }

        $refParseUrl = parse_url($urlStringNotEmpty);

        if (false === $refParseUrl) {
            return Ret::err(
                [ 'The `url` should be valid url', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        $refParseUrl = array_replace(
            [
                'scheme'   => '',
                'user'     => '',
                'pass'     => '',
                'host'     => '',
                'port'     => '',
                'path'     => '',
                'query'    => '',
                'fragment' => '',
            ],
            $refParseUrl
        );

        $wasPath = ('' !== $refParseUrl[ 'path' ]);

        if (! $wasPath) {
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

        $wasQuery = ('' !== $refParseUrl[ 'query' ]);

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

        $result = $this->link_build($refParseUrl);

        return Ret::val($result);
    }


    /**
     * @return Ret<string>
     */
    public function type_dsn_pdo($dsn, array $refs = [])
    {
        $theType = Lib::type();

        $withDsnParams = array_key_exists(0, $refs);
        if ($withDsnParams) {
            $refDsnParams =& $refs[ 0 ];
        }
        $refDsnParams = null;

        $withParseUrl = array_key_exists(1, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 1 ];
        }
        $refParseUrl = null;

        if (! $theType->string_not_empty($dsn)->isOk([ &$dsnStringNotEmpty, &$ret ])) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $refParseUrl = parse_url($dsnStringNotEmpty);

        if (false === $refParseUrl) {
            return Ret::err(
                [ 'The `dsn` should be pass `parse_url` check', $dsn ],
                [ __FILE__, __LINE__ ]
            );
        }

        $refParseUrl = array_replace(
            [
                'scheme'   => '',
                'user'     => '',
                'pass'     => '',
                'host'     => '',
                'port'     => '',
                'path'     => '',
                'query'    => '',
                'fragment' => '',
            ],
            $refParseUrl
        );

        $wasScheme = ('' !== $refParseUrl[ 'scheme' ]);
        $wasPath = ('' !== $refParseUrl[ 'path' ]);

        if (! $wasScheme) {
            return Ret::err(
                [ 'The `dsn` requires a `scheme`', $dsn, $refParseUrl ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $wasPath) {
            return Ret::err(
                [ 'The `dsn` requires a `path`', $dsn, $refParseUrl ],
                [ __FILE__, __LINE__ ]
            );
        }

        $params = explode(';', $refParseUrl[ 'path' ]);

        foreach ( $params as $p ) {
            [ $key, $value ] = explode('=', $p, 2) + [ '', '' ];

            if (! $theType->string_not_empty($key)->isOk([ &$keyString, &$ret ])) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $refDsnParams[ $key ] = $value;
        }

        $result = $this->dsn_pdo_build($refParseUrl);

        return Ret::val($result);
    }


    /**
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function url(
        $url = true, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null,
        array $refs = []
    ) : string
    {
        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $url) {
            throw new LogicException(
                [ 'The `url` should not be null', $url ]
            );
        }

        if (true === $url) {
            $urlStringNotEmpty = $this->url_current();

        } else {
            $urlStringNotEmpty = $theType->string_not_empty($url)->orThrow();
        }

        $refParseUrl = parse_url($urlStringNotEmpty);

        if (false === $refParseUrl) {
            throw new LogicException(
                [ 'The `url` should be valid url', $url ]
            );
        }

        $refParseUrl = array_replace(
            [
                'scheme'   => '',
                'user'     => '',
                'pass'     => '',
                'host'     => '',
                'port'     => '',
                'path'     => '',
                'query'    => '',
                'fragment' => '',
            ],
            $refParseUrl
        );

        $wasHost = ('' !== $refParseUrl[ 'host' ]);

        if (! $wasHost) {
            $this->host_current([ &$refHostCurrentParseUrl ]);

            $refParseUrl = $refHostCurrentParseUrl + $refParseUrl;
            $refParseUrl[ 'path' ] = '/' . ltrim($refParseUrl[ 'path' ] ?? '', '/');

            $urlStringNotEmpty = $this->url_build($refParseUrl);
        }

        $args = [
            $urlStringNotEmpty,
            $query,
            $fragment,
            $isHostIdnaAscii,
            $isLinkUrlencoded,
            $refs,
        ];

        $result = $this->type_url(...$args)->orThrow();

        return $result;
    }

    /**
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function uri(
        $url = true, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null,
        array $refs = []
    ) : string
    {
        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $url) {
            throw new LogicException(
                [ 'The `url` should not be null', $url ]
            );
        }

        if (true === $url) {
            $urlStringNotEmpty = $this->url_current();

        } else {
            $urlStringNotEmpty = $theType->string_not_empty($url)->orThrow();
        }

        $args = [
            $urlStringNotEmpty,
            $query,
            $fragment,
            $isHostIdnaAscii,
            $isLinkUrlencoded,
            $refs,
        ];

        $result = $this->type_uri(...$args)->orThrow();

        return $result;
    }

    /**
     * @param string|true $url
     */
    public function host(
        $url = true,
        ?int $isHostIdnaAscii = null,
        array $refs = []
    ) : string
    {
        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $url) {
            throw new LogicException(
                [ 'The `url` should not be null', $url ]
            );
        }

        if (true === $url) {
            $urlStringNotEmpty = $this->url_current();

        } else {
            $urlStringNotEmpty = $theType->string_not_empty($url)->orThrow();
        }

        $refParseUrl = parse_url($urlStringNotEmpty);

        if (false === $refParseUrl) {
            throw new LogicException(
                [ 'The `url` should be valid url', $url ]
            );
        }

        $refParseUrl = array_replace(
            [
                'scheme'   => '',
                'user'     => '',
                'pass'     => '',
                'host'     => '',
                'port'     => '',
                'path'     => '',
                'query'    => '',
                'fragment' => '',
            ],
            $refParseUrl
        );

        $wasHost = ('' !== $refParseUrl[ 'host' ]);

        if (! $wasHost) {
            $this->host_current([ &$refHostCurrentParseUrl ]);

            $refParseUrl = $refHostCurrentParseUrl + $refParseUrl;
            $refParseUrl[ 'path' ] = '/' . ltrim($refParseUrl[ 'path' ] ?? '', '/');

            $urlStringNotEmpty = $this->url_build($refParseUrl);
        }

        $args = [
            $urlStringNotEmpty,
            $isHostIdnaAscii,
            $refs,
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
        ?int $isLinkUrlencoded = null,
        array $refs = []
    ) : string
    {
        $theType = Lib::type();

        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $url) {
            throw new LogicException(
                [ 'The `url` should not be null', $url ]
            );
        }

        if (true === $url) {
            $urlStringNotEmpty = $this->url_current();

        } else {
            $urlStringNotEmpty = $theType->string_not_empty($url)->orThrow();
        }

        $args = [
            $urlStringNotEmpty,
            $query,
            $fragment,
            $isLinkUrlencoded,
            $refs,
        ];

        $result = $this->type_link(...$args)->orThrow();

        return $result;
    }


    public function url_current(array $refs = []) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $this->urlCurrent) {
            $urlHostCurrent = $this->host_current();
            $urlLinkCurrent = $this->link_current();

            $this->urlCurrent = "{$urlHostCurrent}{$urlLinkCurrent}";
        }

        if ($withParseUrl) {
            if (null !== $this->urlCurrentParseUrl) {
                $this->urlCurrentParseUrl = parse_url($this->urlCurrent);
            }

            $refParseUrl = $this->urlCurrentParseUrl;
        }

        return $this->urlCurrent;
    }

    public function host_current(array $refs = []) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $this->hostCurrent) {
            if (! isset($_SERVER[ 'HTTP_HOST' ])) {
                throw new RuntimeException(
                    [ 'The `SERVER[HTTP_HOST]` is required', $_SERVER ]
                );
            }

            $serverHttpHost = $_SERVER[ 'HTTP_HOST' ];

            $serverHttps = $_SERVER[ 'HTTPS' ] ?? null;
            $serverPhpAuthUser = $_SERVER[ 'PHP_AUTH_USER' ] ?? null;
            $serverPhpAuthPw = $_SERVER[ 'PHP_AUTH_PW' ] ?? null;
            $serverServerPort = $_SERVER[ 'SERVER_PORT' ] ?? null;

            $serverHttpHost = (string) $serverHttpHost;
            $serverHttps = (string) $serverHttps;
            $serverPhpAuthUser = (string) $serverPhpAuthUser;
            $serverPhpAuthPw = (string) $serverPhpAuthPw;
            $serverServerPort = (string) $serverServerPort;

            $hasServerHttpHost = ('' !== $serverHttpHost);
            $hasServerPhpAuthUser = ('' !== $serverPhpAuthUser);
            $hasServerPhpAuthPw = ('' !== $serverPhpAuthPw);

            $scheme = ($serverHttps && ($serverHttps !== 'off')) ? 'https' : 'http';
            $isScheme = '://';

            $user = $hasServerPhpAuthUser ? $serverPhpAuthUser : '';
            $pass = $hasServerPhpAuthPw ? $serverPhpAuthPw : '';
            $isPass = $hasServerPhpAuthPw ? ':' : '';
            $isUserAndPass = ($hasServerPhpAuthUser || $hasServerPhpAuthPw) ? '@' : '';

            $host = $hasServerHttpHost ? $serverHttpHost : '';

            $port = in_array($serverServerPort, [ 80, 443 ]) ? '' : $serverServerPort;
            $hasPort = ('' !== $port);
            $isPort = $hasPort ? ':' : '';

            $this->hostCurrent = implode('', [
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
        }

        if ($withParseUrl) {
            if (null !== $this->hostCurrentParseUrl) {
                $this->hostCurrentParseUrl = parse_url($this->hostCurrent);
            }

            $refParseUrl = $this->hostCurrentParseUrl;
        }

        return $this->hostCurrent;
    }

    public function link_current(array $refs = []) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ($withParseUrl) {
            $refParseUrl =& $refs[ 0 ];
        }
        $refParseUrl = null;

        if (null === $this->linkCurrent) {
            if (! isset($_SERVER[ 'REQUEST_URI' ])) {
                throw new RuntimeException(
                    [ 'The `SERVER[REQUEST_URI]` is required', $_SERVER ]
                );
            }

            $serverRequestUri = $_SERVER[ 'REQUEST_URI' ];

            $serverQueryString = $_SERVER[ 'QUERY_STRING' ] ?? null;

            $serverRequestUri = (string) $serverRequestUri;
            $serverQueryString = (string) $serverQueryString;

            [
                $serverRequestUri,
                $serverRequestUriQueryString,
            ] = explode('?', $serverRequestUri, 2) + [ '', '' ];

            $hasServerQueryString = ('' !== $serverQueryString);
            $hasServerRequestUriQueryString = ('' !== $serverRequestUriQueryString);

            $newQueryString = null
                ?? ($hasServerQueryString ? $serverQueryString : null)
                ?? ($hasServerRequestUriQueryString ? $serverRequestUriQueryString : null)
                ?? '';

            $hasNewQueryString = ('' !== $newQueryString);

            $isQueryString = $hasNewQueryString ? '?' : '';

            $this->linkCurrent = implode('', [
                $serverRequestUri,
                $isQueryString,
                $newQueryString,
            ]);
        }

        if ($withParseUrl) {
            if (null !== $this->linkCurrentParseUrl) {
                $this->linkCurrentParseUrl = parse_url($this->linkCurrent);
            }

            $refParseUrl = $this->linkCurrentParseUrl;
        }

        return $this->linkCurrent;
    }


    public function url_build(array $parseUrlResult) : string
    {
        $parseUrlResultValid = []
            + array_map('strval', $parseUrlResult)
            + [
                'scheme'   => '',
                'user'     => '',
                'pass'     => '',
                'host'     => '',
                'port'     => '',
                'path'     => '',
                'query'    => '',
                'fragment' => '',
            ];

        $urlScheme = $parseUrlResultValid[ 'scheme' ];
        $hasUrlScheme = ('' !== $urlScheme);
        $isUrlScheme = $hasUrlScheme ? '://' : '//';

        $urlUser = $parseUrlResultValid[ 'user' ];
        $urlPass = $parseUrlResultValid[ 'pass' ];
        $hasUrlUser = ('' !== $urlUser);
        $hasUrlPass = ('' !== $urlPass);

        $isPass = $hasUrlPass ? ':' : '';
        $isUserAndPass = ($hasUrlUser || $hasUrlPass) ? '@' : '';

        $urlHost = $parseUrlResultValid[ 'host' ];

        $urlPort = $parseUrlResultValid[ 'port' ];
        $urlPort = in_array($urlPort, [ 80, 443 ]) ? '' : $urlPort;
        $hasUrlPort = ('' !== $urlPort);
        $isPort = $hasUrlPort ? ':' : '';

        $urlPath = $parseUrlResultValid[ 'path' ];
        [ $urlPath ] = explode('?', $urlPath, 2);

        $urlQuery = $parseUrlResultValid[ 'query' ];
        $hasUrlQuery = ('' !== $urlQuery);
        $isQuery = $hasUrlQuery ? '?' : null;

        $urlFragment = $parseUrlResultValid[ 'fragment' ] ?: '';
        $hasUrlFragment = ('' !== $urlFragment);
        $isFragment = $hasUrlFragment ? '#' : null;

        $result = implode('', [
            $urlScheme,
            $isUrlScheme,
            $urlUser,
            $isPass,
            $urlPass,
            $isUserAndPass,
            $urlHost,
            $isPort,
            $urlPort,
            $urlPath,
            $isQuery,
            $urlQuery,
            $isFragment,
            $urlFragment,
        ]);

        return $result;
    }

    public function host_build(array $parseUrlResult) : string
    {
        $parseUrlResultValid = []
            + array_map('strval', $parseUrlResult)
            + [
                'scheme' => '',
                'user'   => '',
                'pass'   => '',
                'host'   => '',
                'port'   => '',
            ];

        $urlScheme = $parseUrlResultValid[ 'scheme' ];
        $hasUrlScheme = ('' !== $urlScheme);
        $isUrlScheme = $hasUrlScheme ? '://' : '//';

        $urlUser = $parseUrlResultValid[ 'user' ];
        $urlPass = $parseUrlResultValid[ 'pass' ];
        $hasUrlUser = ('' !== $urlUser);
        $hasUrlPass = ('' !== $urlPass);

        $isPass = $hasUrlPass ? ':' : '';
        $isUserAndPass = ($hasUrlUser || $hasUrlPass) ? '@' : '';

        $urlHost = $parseUrlResultValid[ 'host' ];

        $urlPort = $parseUrlResultValid[ 'port' ];
        $urlPort = in_array($urlPort, [ 80, 443 ]) ? '' : $urlPort;
        $hasUrlPort = ('' !== $urlPort);
        $isPort = $hasUrlPort ? ':' : '';

        $result = implode('', [
            $urlScheme,
            $isUrlScheme,
            $urlUser,
            $isPass,
            $urlPass,
            $isUserAndPass,
            $urlHost,
            $isPort,
            $urlPort,
        ]);

        return $result;
    }

    public function link_build(array $parseUrlResult) : string
    {
        $parseUrlResultValid = []
            + array_map('strval', $parseUrlResult)
            + [
                'scheme'   => '',
                'path'     => '',
                'query'    => '',
                'fragment' => '',
            ];

        $urlScheme = $parseUrlResultValid[ 'scheme' ];
        if (in_array($urlScheme, [ 'http', 'https' ])) {
            $urlScheme = '';
        }

        $hasUrlScheme = ('' !== $urlScheme);
        $isUrlScheme = $hasUrlScheme ? ':' : '';

        $urlPath = $parseUrlResultValid[ 'path' ];
        [ $urlPath ] = explode('?', $urlPath, 2);

        $urlQuery = $parseUrlResultValid[ 'query' ];
        $hasUrlQuery = ('' !== $urlQuery);
        $isQuery = $hasUrlQuery ? '?' : null;

        $urlFragment = $parseUrlResultValid[ 'fragment' ] ?: '';
        $hasUrlFragment = ('' !== $urlFragment);
        $isFragment = $hasUrlFragment ? '#' : null;

        $result = implode('', [
            $urlScheme,
            $isUrlScheme,
            $urlPath,
            $isQuery,
            $urlQuery,
            $isFragment,
            $urlFragment,
        ]);

        return $result;
    }


    public function dsn_pdo_build(array $parseUrlResult) : string
    {
        $parseUrlResultValid = []
            + array_map('strval', $parseUrlResult)
            + [
                'scheme' => '',
                'path'   => '',
            ];

        $urlScheme = $parseUrlResultValid[ 'scheme' ];
        $hasUrlScheme = ('' !== $urlScheme);
        $isUrlScheme = $hasUrlScheme ? ':' : '';

        $urlPath = $parseUrlResultValid[ 'path' ];
        [ $urlPath ] = explode('?', $urlPath, 2);

        $result = implode('', [
            $urlScheme,
            $isUrlScheme,
            $urlPath,
        ]);

        return $result;
    }
}
