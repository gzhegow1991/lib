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
     * @var string
     */
    protected $linkCurrent;
    /**
     * @var string
     */
    protected $hostCurrent;
    /**
     * @var string
     */
    protected $domainCurrent;

    /**
     * @var string
     */
    protected $parseUrlUrlCurrent;
    /**
     * @var string
     */
    protected $parseUrlLinkCurrent;
    /**
     * @var string
     */
    protected $parseUrlHostCurrent;
    /**
     * @var string
     */
    protected $parseUrlDomainCurrent;


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    /**
     * > https://example.com:8080/example#example?example=1
     * > /example#example?example=1
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>|string
     */
    public function type_uri($fb,
        $url, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null,
        array $refs = []
    )
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        $ret = $this->parse_url_arguments(null,
            $url, $query, $fragment,
            $isHostIdnaAscii, $isLinkUrlencoded
        );

        if ( ! $ret->isOk([ &$parseUrlArguments ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $wasScheme = ('' !== ($parseUrlArguments['scheme'] ?? ''));
        $wasHost = ('' !== ($parseUrlArguments['host'] ?? ''));
        $wasPath = ('' !== ($parseUrlArguments['path'] ?? ''));

        if ( ! ($wasHost || $wasPath) ) {
            return Ret::throw(
                $fb,
                [ 'The `url` requires a host or a path', $url, $parseUrlArguments ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = ($wasScheme || $wasHost)
            ? $this->uri_build(null, $parseUrlArguments, [ &$refParseUrl ])
            : $this->link_build(null, $parseUrlArguments, [ &$refParseUrl ]);

        if ( ! $ret->isOk([ &$result ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }

    /**
     * > https://example.com:8080/example#example?example=1
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>|string
     */
    public function type_url($fb,
        $url, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null,
        array $refs = []
    )
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        $ret = $this->parse_url_arguments(null,
            $url, $query, $fragment,
            $isHostIdnaAscii, $isLinkUrlencoded
        );

        if ( ! $ret->isOk([ &$parseUrlArguments ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $wasHost = ('' !== ($parseUrlArguments['host'] ?? ''));

        if ( ! $wasHost ) {
            return Ret::throw(
                $fb,
                [ 'The `url` requires a host', $url, $parseUrlArguments ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->url_build(null, $parseUrlArguments, [ &$refParseUrl ]);

        if ( ! $ret->isOk([ &$result ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }

    /**
     * > /example#example?example=1
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>|string
     */
    public function type_link($fb,
        $url, $query = null, $fragment = null,
        ?int $isLinkUrlencoded = null,
        array $refs = []
    )
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        $ret = $this->parse_url_arguments(null,
            $url, $query, $fragment,
            null, $isLinkUrlencoded
        );

        if ( ! $ret->isOk([ &$parseUrlArguments ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $wasPath = ('' !== ($parseUrlArguments['path'] ?? ''));

        if ( ! $wasPath ) {
            return Ret::throw(
                $fb,
                [ 'The `url` requires a path', $url, $parseUrlArguments ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->link_build(null, $parseUrlArguments, [ &$refParseUrl ]);

        if ( ! $ret->isOk([ &$result ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }

    /**
     * > https://example.com:8080/
     *
     * @param string|true $url
     *
     * @return Ret<string>|string
     */
    public function type_host($fb,
        $url,
        ?int $isHostIdnaAscii = null,
        array $refs = []
    )
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        $ret = $this->parse_url_arguments(null,
            $url, null, null,
            $isHostIdnaAscii, null
        );

        if ( ! $ret->isOk([ &$parseUrlArguments ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $wasHost = ('' !== ($parseUrlArguments['host'] ?? ''));

        if ( ! $wasHost ) {
            return Ret::throw(
                $fb,
                [ 'The `url` requires a host', $url, $parseUrlArguments ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->host_build(null, $parseUrlArguments, [ &$refParseUrl ]);

        if ( ! $ret->isOk([ &$result ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }

    /**
     * > example.com
     *
     * @param string|true $url
     *
     * @return Ret<string>|string
     */
    public function type_domain($fb,
        $url,
        ?int $isHostIdnaAscii = null,
        array $refs = []
    )
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        $ret = $this->parse_url_arguments(null,
            $url, null, null,
            $isHostIdnaAscii, null
        );

        if ( ! $ret->isOk([ &$parseUrlArguments ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $wasHost = ('' !== ($parseUrlArguments['host'] ?? ''));

        if ( ! $wasHost ) {
            return Ret::throw(
                $fb,
                [ 'The `url` requires a host', $url, $parseUrlArguments ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->domain_build(null, $parseUrlArguments, [ &$refParseUrl ]);

        if ( ! $ret->isOk([ &$result ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }


    /**
     * > mysql:host=localhost;dbname=testdb;charset=utf8mb4
     * > sqlite::memory:
     *
     * @return Ret<string>|string
     */
    public function type_dsn_pdo($fb,
        $dsn,
        array $refs = []
    )
    {
        $withDsnParams = array_key_exists(0, $refs);
        $withParseUrl = array_key_exists(1, $refs);

        if ( $withDsnParams ) $refDsnParams =& $refs[0];
        if ( $withParseUrl ) $refParseUrl =& $refs[1];

        $refDsnParams = null;
        $refParseUrl = null;

        $ret = $this->parse_url_arguments(null,
            $dsn, null, null,
            null, null
        );

        if ( ! $ret->isOk([ &$parseUrlArguments ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $wasScheme = ('' !== ($parseUrlArguments['scheme'] ?? ''));
        $wasPath = ('' !== ($parseUrlArguments['path'] ?? ''));

        if ( ! $wasScheme ) {
            return Ret::throw(
                $fb,
                [ 'The `dsn` requires a `scheme`', $dsn ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $wasPath ) {
            return Ret::throw(
                $fb,
                [ 'The `dsn` requires a `path`', $dsn ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->dsn_pdo_build(null, $parseUrlArguments, [ &$refDsnParams, &$refParseUrl ]);

        if ( ! $ret->isOk([ &$result ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $result);
    }


    /**
     * > https://example.com:8080/example#example?example=1
     * > /example#example?example=1
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function to_uri(
        $url = true, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null,
        array $refs = []
    ) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        if ( null === $url ) {
            throw new LogicException(
                [ 'The `url` should not be null', $url ]
            );
        }

        if ( true === $url ) {
            $result = $this->url_current([ &$refParseUrl ]);

        } else {
            $theType = Lib::type();

            $urlStringNotEmpty = $theType->string_not_empty($url)->orThrow();

            $parseUrlTotal = $this->parse_url_arguments(
                [],
                $urlStringNotEmpty, $query, $fragment,
                $isHostIdnaAscii, $isLinkUrlencoded
            );

            $wasScheme = ('' !== ($parseUrlTotal['scheme'] ?? ''));
            $wasHost = ('' !== ($parseUrlTotal['host'] ?? ''));

            $result = ($wasScheme || $wasHost)
                ? $this->uri_build([], $parseUrlTotal, [ &$refParseUrl ])
                : $this->link_build([], $parseUrlTotal, [ &$refParseUrl ]);
        }

        return $result;
    }

    /**
     * > https://example.com:8080/example#example?example=1
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function to_url(
        $url = true, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null,
        array $refs = []
    ) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        if ( null === $url ) {
            throw new LogicException(
                [ 'The `url` should not be null', $url ]
            );
        }

        if ( true === $url ) {
            $result = $this->url_current([ &$refParseUrl ]);

        } else {
            $theType = Lib::type();

            $urlStringNotEmpty = $theType->string_not_empty($url)->orThrow();

            $parseUrlTotal = $this->parse_url_arguments(
                [],
                $urlStringNotEmpty, $query, $fragment,
                $isHostIdnaAscii, $isLinkUrlencoded
            );

            $wasScheme = ('' !== ($parseUrlTotal['scheme'] ?? ''));
            $wasHost = ('' !== ($parseUrlTotal['host'] ?? ''));

            if ( ! ($wasScheme || $wasHost) ) {
                $this->host_current([ &$refParseUrlHostCurrent ]);

                $parseUrlTotal = $refParseUrlHostCurrent + $parseUrlTotal;
            }

            $result = $this->url_build([], $parseUrlTotal, [ &$refParseUrl ]);
        }

        return $result;
    }

    /**
     * > /example#example?example=1
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function to_link(
        $url = true, $query = null, $fragment = null,
        ?int $isLinkUrlencoded = null,
        array $refs = []
    ) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        if ( null === $url ) {
            throw new LogicException(
                [ 'The `url` should not be null', $url ]
            );
        }

        if ( true === $url ) {
            $result = $this->link_current([ &$refParseUrl ]);

        } else {
            $theType = Lib::type();

            $urlStringNotEmpty = $theType->string_not_empty($url)->orThrow();

            $parseUrlTotal = $this->parse_url_arguments(
                [],
                $urlStringNotEmpty, $query, $fragment,
                null, $isLinkUrlencoded
            );

            $result = $this->link_build([], $parseUrlTotal, [ &$refParseUrl ]);
        }

        return $result;
    }

    /**
     * > https://example.com:8080/
     *
     * @param string|true $url
     */
    public function to_host(
        $url = true,
        ?int $isHostIdnaAscii = null,
        array $refs = []
    ) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        if ( null === $url ) {
            throw new LogicException(
                [ 'The `url` should not be null', $url ]
            );
        }

        if ( true === $url ) {
            $result = $this->host_current([ &$refParseUrl ]);

        } else {
            $theType = Lib::type();

            $urlStringNotEmpty = $theType->string_not_empty($url)->orThrow();

            $parseUrlTotal = $this->parse_url_arguments(
                [],
                $urlStringNotEmpty, null, null,
                $isHostIdnaAscii, null
            );

            $wasScheme = ('' !== ($parseUrlTotal['scheme'] ?? ''));
            $wasHost = ('' !== ($parseUrlTotal['host'] ?? ''));

            if ( ! ($wasScheme || $wasHost) ) {
                $this->host_current([ &$refParseUrlHostCurrent ]);

                $parseUrlTotal = $refParseUrlHostCurrent + $parseUrlTotal;
            }

            $result = $this->host_build([], $parseUrlTotal, [ &$refParseUrl ]);
        }

        return $result;
    }

    /**
     * > example.com
     *
     * @param string|true $url
     */
    public function to_domain(
        $url = true,
        ?int $isHostIdnaAscii = null,
        array $refs = []
    ) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        if ( null === $url ) {
            throw new LogicException(
                [ 'The `url` should not be null', $url ]
            );
        }

        if ( true === $url ) {
            $result = $this->domain_current([ &$refParseUrl ]);

        } else {
            $theType = Lib::type();

            $urlStringNotEmpty = $theType->string_not_empty($url)->orThrow();

            $parseUrlTotal = $this->parse_url_arguments(
                [],
                $urlStringNotEmpty, null, null,
                $isHostIdnaAscii, null
            );

            $wasScheme = ('' !== ($parseUrlTotal['scheme'] ?? ''));
            $wasHost = ('' !== ($parseUrlTotal['host'] ?? ''));

            if ( ! ($wasScheme || $wasHost) ) {
                $this->host_current([ &$refParseUrlHostCurrent ]);

                $parseUrlTotal = $refParseUrlHostCurrent + $parseUrlTotal;
            }

            $result = $this->domain_build([], $parseUrlTotal, [ &$refParseUrl ]);
        }

        return $result;
    }


    /**
     * > https://example.com:8080/example#example?example=1
     * * > /example#example?example=1
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function uri(
        $url = true, $query = null, $fragment = null,
        $isHostIdnaAscii = null, $isLinkUrlencoded = null,
        array $refs = []
    ) : string
    {
        return $this->to_uri(
            $url, $query, $fragment,
            $isHostIdnaAscii, $isLinkUrlencoded,
            $refs
        );
    }

    /**
     * > https://example.com:8080/example#example?example=1
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function url(
        $url = true, $query = null, $fragment = null,
        $isHostIdnaAscii = null, $isLinkUrlencoded = null,
        array $refs = []
    ) : string
    {
        return $this->to_url(
            $url, $query, $fragment,
            $isHostIdnaAscii, $isLinkUrlencoded,
            $refs
        );
    }

    /**
     * > /example#example?example=1
     *
     * @param string|true             $url
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     */
    public function link(
        $url = true, $query = null, $fragment = null,
        $isLinkUrlencoded = null,
        array $refs = []
    ) : string
    {
        return $this->to_link(
            $url, $query, $fragment,
            $isLinkUrlencoded,
            $refs
        );
    }

    /**
     * > https://example.com:8080/
     *
     * @param string|true $url
     */
    public function host(
        $url = true,
        $isHostIdnaAscii = null,
        array $refs = []
    ) : string
    {
        return $this->to_host(
            $url,
            $isHostIdnaAscii,
            $refs
        );
    }

    /**
     * > example.com
     *
     * @param string|true $url
     */
    public function domain(
        $url = true,
        $isHostIdnaAscii = null,
        array $refs = []
    ) : string
    {
        return $this->to_domain(
            $url,
            $isHostIdnaAscii,
            $refs
        );
    }


    public function clear_url_current() : void
    {
        $this->urlCurrent = null;
        $this->linkCurrent = null;
        $this->hostCurrent = null;
        $this->domainCurrent = null;

        $this->parseUrlUrlCurrent = null;
        $this->parseUrlLinkCurrent = null;
        $this->parseUrlHostCurrent = null;
        $this->parseUrlDomainCurrent = null;
    }

    public function url_current(array $refs = []) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        if ( ! array_key_exists('HTTP_HOST', $_SERVER) ) {
            throw new RuntimeException(
                [ 'Missing $_SERVER[HTTP_HOST] key', $_SERVER ]
            );
        }

        if ( ! array_key_exists('REQUEST_URI', $_SERVER) ) {
            throw new RuntimeException(
                [ 'Missing $_SERVER[REQUEST_URI] key', $_SERVER ]
            );
        }

        $this->parse_url_current();

        if ( $withParseUrl ) {
            $refParseUrl = $this->parseUrlUrlCurrent;
        }

        return $this->urlCurrent;
    }

    public function host_current(array $refs = []) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        if ( ! array_key_exists('HTTP_HOST', $_SERVER) ) {
            throw new RuntimeException(
                [ 'Missing $_SERVER[HTTP_HOST] key', $_SERVER ]
            );
        }

        $this->parse_url_current();

        if ( $withParseUrl ) {
            $refParseUrl = $this->parseUrlHostCurrent;
        }

        return $this->hostCurrent;
    }

    public function link_current(array $refs = []) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        if ( ! array_key_exists('REQUEST_URI', $_SERVER) ) {
            throw new RuntimeException(
                [ 'Missing $_SERVER[REQUEST_URI] key', $_SERVER ]
            );
        }

        $this->parse_url_current();

        if ( $withParseUrl ) {
            $refParseUrl = $this->parseUrlLinkCurrent;
        }

        return $this->linkCurrent;
    }

    public function domain_current(array $refs = []) : string
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        if ( ! array_key_exists('HTTP_HOST', $_SERVER) ) {
            throw new RuntimeException(
                [ 'Missing $_SERVER[HTTP_HOST] key', $_SERVER ]
            );
        }

        $this->parse_url_current();

        if ( $withParseUrl ) {
            $refParseUrl = $this->parseUrlDomainCurrent;
        }

        return $this->domainCurrent;
    }

    /**
     * @noinspection PhpIfWithCommonPartsInspection
     */
    protected function parse_url_current() : void
    {
        if ( null === $this->urlCurrent ) {
            $serverHttpHost = $_SERVER['HTTP_HOST'] ?? null;
            $serverServerPort = $_SERVER['SERVER_PORT'] ?? null;
            $serverHttps = $_SERVER['HTTPS'] ?? null;
            $serverHttpXForwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;

            $serverPhpAuthUser = $_SERVER['PHP_AUTH_USER'] ?? null;
            $serverPhpAuthPw = $_SERVER['PHP_AUTH_PW'] ?? null;

            $serverRequestUri = $_SERVER['REQUEST_URI'] ?? null;
            $serverQueryString = $_SERVER['QUERY_STRING'] ?? null;

            $serverHttpHostString = (string) $serverHttpHost;
            $serverServerPortInt = (int) $serverServerPort;
            $serverHttpsString = (string) $serverHttps;
            $serverHttpXForwardedProtoString = (string) $serverHttpXForwardedProto;

            $serverPhpAuthUserString = (string) $serverPhpAuthUser;
            $serverPhpAuthPwString = (string) $serverPhpAuthPw;

            $serverRequestUriString = (string) $serverRequestUri;
            $serverQueryStringString = (string) $serverQueryString;

            [
                $explodeRequestUriString,
                $explodeQueryStringString,
            ] = explode('?', $serverRequestUriString, 2) + [ '', '' ];

            $hasServerHttpHost = ('' !== $serverHttpHostString);
            $hasServerServerPort = (0 !== $serverServerPortInt);
            $hasServerHttps = ('' !== $serverHttpsString);

            $hasServerPhpAuthUser = ('' !== $serverPhpAuthUserString);
            $hasServerPhpAuthPw = ('' !== $serverPhpAuthPwString);

            $hasServerQueryString = ('' !== $serverRequestUriString);
            $hasExplodeQueryString = ('' !== $explodeQueryStringString);

            $resultRequestUri = null;
            $resultQueryString = null;
            if ( $hasExplodeQueryString ) {
                $resultRequestUri = $explodeRequestUriString;
                $resultQueryString = $explodeQueryStringString;

            } elseif ( $hasServerQueryString ) {
                $resultRequestUri = $serverRequestUriString;
                $resultQueryString = $serverQueryStringString;

            } else {
                $resultRequestUri = $serverRequestUriString;
                $resultQueryString = '';
            }

            $hasResultQueryString = ('' !== $resultQueryString);

            $scheme = null;
            $isScheme = null;
            if ( false
                || ($hasServerHttps && ($serverHttpsString !== 'off'))
                || ($serverHttpXForwardedProtoString === 'https')
                || ($serverServerPortInt === 443)
            ) {
                $scheme = 'https';
                $isScheme = '://';

            } else {
                $scheme = 'http';
                $isScheme = '://';
            }

            $user = $hasServerPhpAuthUser ? $serverPhpAuthUserString : '';
            $pass = $hasServerPhpAuthPw ? $serverPhpAuthPwString : '';

            $hasUser = ('' !== $user);
            $hasPass = ('' !== $pass);

            $isPass = ($hasPass) ? ':' : '';
            $isUserPass = ($hasUser || $hasPass) ? '@' : '';

            $host = $hasServerHttpHost ? $serverHttpHostString : '';
            $port = '';
            if ( $hasServerServerPort ) {
                if ( ($serverServerPortInt === 80) && ('http' === $scheme) ) {
                    $port = '';

                } elseif ( ($serverServerPortInt === 443) && ('https' === $scheme) ) {
                    $port = '';

                } else {
                    $port = (string) $serverServerPort;
                }
            }

            $hasHost = ('' !== $host);
            $hasPort = ('' !== $port);

            $isPort = ($hasPort) ? ':' : '';
            $isHostPort = ($hasHost || $hasPort) ? '/' : '';

            $requestUri = $resultRequestUri;
            if ( $isHostPort ) {
                $requestUri = ltrim($requestUri, '/');
            }

            $queryString = $resultQueryString;

            $isQueryString = ($hasResultQueryString) ? '?' : '';

            $urlCurrent = implode('', [
                $scheme,
                $isScheme,
                $user,
                $isPass,
                $pass,
                $isUserPass,
                $host,
                $isPort,
                $port,
                $isHostPort,
                $requestUri,
                $isQueryString,
                $queryString,
            ]);

            $linkCurrent = implode('', [
                $isHostPort,
                $requestUri,
                $isQueryString,
                $queryString,
            ]);

            $hostCurrent = implode('', [
                $scheme,
                $isScheme,
                $user,
                $isPass,
                $pass,
                $isUserPass,
                $host,
                $isPort,
                $port,
                $isHostPort,
            ]);

            $domainCurrent = implode('', [
                $host,
            ]);

            if ( '' === $urlCurrent ) $urlCurrent = 'http://localhost/';
            if ( '' === $linkCurrent ) $linkCurrent = '/';
            if ( '' === $hostCurrent ) $hostCurrent = 'http://localhost/';
            if ( '' === $domainCurrent ) $domainCurrent = 'localhost';

            $this->linkCurrent = $linkCurrent;
            $this->hostCurrent = $hostCurrent;
            $this->domainCurrent = $domainCurrent;

            $this->urlCurrent = $urlCurrent;
        }

        if ( null === $this->parseUrlUrlCurrent ) {
            $parseUrl = $this->parse_url([], $this->urlCurrent);

            $var = $parseUrl;
            unset(
                $var['scheme'],
                $var['is_scheme'],
                $var['is_uri'],
                $var['user'],
                $var['is_pass'],
                $var['pass'],
                $var['is_userpass'],
                $var['host'],
                $var['is_port'],
                $var['port'],
                // $var['is_hostport'],
                // $var['path'],
                // $var['is_query'],
                // $var['query'],
                // $var['is_fragment'],
                // $var['fragment']
            );
            $this->parseUrlLinkCurrent = $var;

            $var = $parseUrl;
            unset(
                // $var['scheme'],
                // $var['is_scheme'],
                // $var['is_uri'],
                // $var['user'],
                // $var['is_pass'],
                // $var['pass'],
                // $var['is_userpass'],
                // $var['host'],
                // $var['is_port'],
                // $var['port'],
                // $var['is_hostport'],
                $var['path'],
                $var['is_query'],
                $var['query'],
                $var['is_fragment'],
                $var['fragment']
            );
            $this->parseUrlHostCurrent = $var;

            $var = $parseUrl;
            unset(
                $var['scheme'],
                $var['is_scheme'],
                $var['is_uri'],
                $var['user'],
                $var['is_pass'],
                $var['pass'],
                $var['is_userpass'],
                // $var['host'],
                $var['is_port'],
                $var['port'],
                $var['is_hostport'],
                $var['path'],
                $var['is_query'],
                $var['query'],
                $var['is_fragment'],
                $var['fragment']
            );
            $this->parseUrlDomainCurrent = $var;

            $this->parseUrlUrlCurrent = $parseUrl;
        }
    }


    /**
     * > https://example.com:8080/example#example?example=1
     * > /example#example?example=1
     *
     * @return Ret<string>|string
     */
    public function uri_build($fb, array $parseUrlResult, array $refs = [])
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        $ret = $this->parse_url_array(null, $parseUrlResult);

        if ( ! $ret->isOk([ &$parseUrlResultTotal ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $hasHost = ('' !== $parseUrlResultTotal['host']);
        $hasIsHostPath = ('' !== $parseUrlResultTotal['path']);
        $hasPath = ('' !== $parseUrlResultTotal['path']);

        if ( ! ($hasHost || $hasIsHostPath || $hasPath) ) {
            return Ret::throw(
                $fb,
                [ 'The uri should have non-empty `host` or non-empty `path`', $parseUrlResult ],
                [ __FILE__, __LINE__ ]
            );
        }

        // unset(
        //     // $parseUrlResultTotal['scheme'],
        //     // $parseUrlResultTotal['is_scheme'],
        //     // $parseUrlResultTotal['is_uri'],
        //     // $parseUrlResultTotal['user'],
        //     // $parseUrlResultTotal['is_pass'],
        //     // $parseUrlResultTotal['pass'],
        //     // $parseUrlResultTotal['is_userpass'],
        //     // $parseUrlResultTotal['host']
        //     // $parseUrlResultTotal['is_port'],
        //     // $parseUrlResultTotal['port'],
        //     // $parseUrlResultTotal['is_hostport'],
        //     // $parseUrlResultTotal['path'],
        //     // $parseUrlResultTotal['is_query'],
        //     // $parseUrlResultTotal['query'],
        //     // $parseUrlResultTotal['is_fragment'],
        //     // $parseUrlResultTotal['fragment'],
        // );

        $result = implode('', $parseUrlResultTotal);

        $refParseUrl = $parseUrlResultTotal;

        return Ret::ok($fb, $result);
    }

    /**
     * > https://example.com:8080/example#example?example=1
     *
     * @return Ret<string>|string
     */
    public function url_build($fb, array $parseUrlResult, array $refs = [])
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        $ret = $this->parse_url_array(null, $parseUrlResult);

        if ( ! $ret->isOk([ &$parseUrlResultTotal ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $hasHost = ('' !== $parseUrlResultTotal['host']);

        if ( ! $hasHost ) {
            return Ret::throw(
                $fb,
                [ 'The url should have non-empty `host`', $parseUrlResult ],
                [ __FILE__, __LINE__ ]
            );
        }

        // unset(
        //     // $parseUrlResultTotal['scheme'],
        //     // $parseUrlResultTotal['is_scheme'],
        //     // $parseUrlResultTotal['is_uri'],
        //     // $parseUrlResultTotal['user'],
        //     // $parseUrlResultTotal['is_pass'],
        //     // $parseUrlResultTotal['pass'],
        //     // $parseUrlResultTotal['is_userpass'],
        //     // $parseUrlResultTotal['host']
        //     // $parseUrlResultTotal['is_port'],
        //     // $parseUrlResultTotal['port'],
        //     // $parseUrlResultTotal['is_hostport'],
        //     // $parseUrlResultTotal['path'],
        //     // $parseUrlResultTotal['is_query'],
        //     // $parseUrlResultTotal['query'],
        //     // $parseUrlResultTotal['is_fragment'],
        //     // $parseUrlResultTotal['fragment'],
        // );

        $result = implode('', $parseUrlResultTotal);

        $refParseUrl = $parseUrlResultTotal;

        return Ret::ok($fb, $result);
    }

    /**
     * > /example#example?example=1
     *
     * @return Ret<string>|string
     */
    public function link_build($fb, array $parseUrlResult, array $refs = [])
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        $ret = $this->parse_url_array(null, $parseUrlResult);

        if ( ! $ret->isOk([ &$parseUrlResultTotal ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $hasIsHostPort = ('' !== $parseUrlResultTotal['is_hostport']);
        $hasPath = ('' !== $parseUrlResultTotal['path']);

        if ( ! ($hasIsHostPort || $hasPath) ) {
            return Ret::throw(
                $fb,
                [ 'The link should have non-empty `path`', $parseUrlResult ],
                [ __FILE__, __LINE__ ]
            );
        }

        $hasScheme = ('' !== $parseUrlResultTotal['scheme']);
        $hasHost = ('' !== $parseUrlResultTotal['host']);

        if ( $hasScheme && ! $hasHost ) {
            return Ret::throw(
                $fb,
                [ 'The link should have `host` if `scheme` is present, otherwise it is uri or dsn', $parseUrlResult ],
                [ __FILE__, __LINE__ ]
            );
        }

        unset(
            $parseUrlResultTotal['scheme'],
            $parseUrlResultTotal['is_scheme'],
            $parseUrlResultTotal['is_uri'],
            $parseUrlResultTotal['user'],
            $parseUrlResultTotal['is_pass'],
            $parseUrlResultTotal['pass'],
            $parseUrlResultTotal['is_userpass'],
            $parseUrlResultTotal['host'],
            $parseUrlResultTotal['is_port'],
            $parseUrlResultTotal['port'],
            // $parseUrlResultTotal['is_hostport'],
            // $parseUrlResultTotal['path'],
            // $parseUrlResultTotal['is_query'],
            // $parseUrlResultTotal['query'],
            // $parseUrlResultTotal['is_fragment'],
            // $parseUrlResultTotal['fragment'],
        );

        $result = implode('', $parseUrlResultTotal);

        $refParseUrl = $parseUrlResultTotal;

        return Ret::ok($fb, $result);
    }

    /**
     * > https://example.com:8080/
     *
     * @return Ret<string>|string
     */
    public function host_build($fb, array $parseUrlResult, array $refs = [])
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        $ret = $this->parse_url_array(null, $parseUrlResult);

        if ( ! $ret->isOk([ &$parseUrlResultTotal ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $hasHost = ('' !== $parseUrlResultTotal['host']);

        if ( ! $hasHost ) {
            return Ret::throw(
                $fb,
                [ 'The host should have non-empty `host`', $parseUrlResult ],
                [ __FILE__, __LINE__ ]
            );
        }

        unset(
            // $parseUrlResultTotal['scheme'],
            // $parseUrlResultTotal['is_scheme'],
            // $parseUrlResultTotal['is_uri'],
            // $parseUrlResultTotal['user'],
            // $parseUrlResultTotal['is_pass'],
            // $parseUrlResultTotal['pass'],
            // $parseUrlResultTotal['is_userpass'],
            // $parseUrlResultTotal['host']
            // $parseUrlResultTotal['is_port'],
            // $parseUrlResultTotal['port'],
            // $parseUrlResultTotal['is_hostport'],
            $parseUrlResultTotal['path'],
            $parseUrlResultTotal['is_query'],
            $parseUrlResultTotal['query'],
            $parseUrlResultTotal['is_fragment'],
            $parseUrlResultTotal['fragment'],
        );

        $result = implode('', $parseUrlResultTotal);

        $refParseUrl = $parseUrlResultTotal;

        return Ret::ok($fb, $result);
    }

    /**
     * > example.com
     *
     * @return Ret<string>|string
     */
    public function domain_build($fb, array $parseUrlResult, array $refs = [])
    {
        $withParseUrl = array_key_exists(0, $refs);
        if ( $withParseUrl ) $refParseUrl =& $refs[0];
        $refParseUrl = null;

        $ret = $this->parse_url_array(null, $parseUrlResult);

        if ( ! $ret->isOk([ &$parseUrlResultTotal ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $hasHost = ('' !== $parseUrlResultTotal['host']);

        if ( ! $hasHost ) {
            return Ret::throw(
                $fb,
                [ 'The domain should have non-empty `host`', $parseUrlResult ],
                [ __FILE__, __LINE__ ]
            );
        }

        unset(
            $parseUrlResultTotal['scheme'],
            $parseUrlResultTotal['is_scheme'],
            $parseUrlResultTotal['is_uri'],
            $parseUrlResultTotal['user'],
            $parseUrlResultTotal['is_pass'],
            $parseUrlResultTotal['pass'],
            $parseUrlResultTotal['is_userpass'],
            // $parseUrlResultTotal['host']
            $parseUrlResultTotal['is_port'],
            $parseUrlResultTotal['port'],
            $parseUrlResultTotal['is_hostport'],
            $parseUrlResultTotal['path'],
            $parseUrlResultTotal['is_query'],
            $parseUrlResultTotal['query'],
            $parseUrlResultTotal['is_fragment'],
            $parseUrlResultTotal['fragment'],
        );

        $result = $parseUrlResultTotal['host'];

        $refParseUrl = $parseUrlResultTotal;

        return Ret::ok($fb, $result);
    }


    /**
     * > mysql:host=localhost;dbname=testdb;charset=utf8mb4
     * > sqlite::memory:
     */
    public function dsn_pdo_build($fb, array $parseUrlResult, array $refs = [])
    {
        $withDsnParams = array_key_exists(0, $refs);
        $withParseUrl = array_key_exists(1, $refs);

        if ( $withDsnParams ) $refDsnParams =& $refs[0];
        if ( $withParseUrl ) $refParseUrl =& $refs[1];

        $refDsnParams = null;
        $refParseUrl = null;

        $theType = Lib::type();

        $ret = $this->parse_url_array(null, $parseUrlResult);

        if ( ! $ret->isOk([ &$parseUrlResultTotal ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $hasScheme = ('' !== ($parseUrlResultTotal['scheme'] ?? ''));
        $hasPath = ('' !== ($parseUrlResultTotal['path'] ?? ''));

        if ( ! $hasScheme ) {
            return Ret::throw(
                $fb,
                [ 'The `dsn` requires a `scheme`', $parseUrlResult ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $hasPath ) {
            return Ret::throw(
                $fb,
                [ 'The `dsn` requires a `path`', $parseUrlResult ],
                [ __FILE__, __LINE__ ]
            );
        }

        unset(
            // $parseUrlResultTotal['scheme'],
            // $parseUrlResultTotal['is_scheme'],
            $parseUrlResultTotal['is_uri'],
            $parseUrlResultTotal['user'],
            $parseUrlResultTotal['is_pass'],
            $parseUrlResultTotal['pass'],
            $parseUrlResultTotal['is_userpass'],
            $parseUrlResultTotal['host'],
            $parseUrlResultTotal['is_port'],
            $parseUrlResultTotal['port'],
            $parseUrlResultTotal['is_hostport'],
            // $parseUrlResult['path'],
            $parseUrlResultTotal['is_query'],
            $parseUrlResultTotal['query'],
            $parseUrlResultTotal['is_fragment'],
            $parseUrlResultTotal['fragment'],
        );

        $result = implode('', $parseUrlResultTotal);

        $refParseUrl = $parseUrlResultTotal;

        $refDsnParams = [];
        $refDsnParams['scheme'] = $parseUrlResultTotal['scheme'];

        $params = explode(';', $parseUrlResultTotal['path']);

        foreach ( $params as $p ) {
            [ $key, $value ] = explode('=', $p, 2) + [ '', null ];

            $ret = $theType->string_not_empty($key);

            if ( ! $ret->isOk([ &$keyString ]) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `dsn` param key should be string, not empty', $parseUrlResult, $p, $params ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( $value === null ) {
                $refDsnParams[] = $key;

            } else {
                $refDsnParams[$key] = $value;
            }
        }

        return Ret::ok($fb, $result);
    }


    /**
     * @return Ret<array>|array{
     *     scheme: string,
     *     is_scheme: string,
     *     is_uri: string,
     *     user: string,
     *     is_pass: string,
     *     pass: string,
     *     is_userpass: string,
     *     host: string,
     *     is_port: string,
     *     port: string,
     *     is_hostport: string,
     *     path: string,
     *     is_query: string,
     *     query: string,
     *     is_fragment: string,
     *     fragment: string,
     * }
     */
    public function parse_url(
        $fb,
        $url, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null
    )
    {
        $ret = $this->parse_url_arguments(null,
            $url, $query, $fragment,
            $isHostIdnaAscii, $isLinkUrlencoded
        );

        if ( ! $ret->isOk([ &$parseUrlArguments ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $this->parse_url_dict(null, $parseUrlArguments);

        if ( ! $ret->isOk([ &$parseUrlResult ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $parseUrlResult);
    }

    /**
     * @return Ret<array>|array{
     *     scheme: string,
     *     is_scheme: string,
     *     is_uri: string,
     *     user: string,
     *     is_pass: string,
     *     pass: string,
     *     is_userpass: string,
     *     host: string,
     *     is_port: string,
     *     port: string,
     *     is_hostport: string,
     *     path: string,
     *     is_query: string,
     *     query: string,
     *     is_fragment: string,
     *     fragment: string,
     * }
     */
    public function parse_url_array($fb, $parseUrlResult)
    {
        $ret = $this->parse_url_dict(null, $parseUrlResult);

        if ( ! $ret->isOk([ &$parseUrlTotal ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $parseUrlTotal);
    }


    /**
     * @return Ret<array>|array{
     *     scheme: string,
     *     is_scheme: string,
     *     is_uri: string,
     *     user: string,
     *     is_pass: string,
     *     pass: string,
     *     is_userpass: string,
     *     host: string,
     *     is_port: string,
     *     port: string,
     *     is_hostport: string,
     *     path: string,
     *     is_query: string,
     *     query: string,
     *     is_fragment: string,
     *     fragment: string,
     * }
     */
    protected function parse_url_arguments(
        $fb,
        $url, $query = null, $fragment = null,
        ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null
    )
    {
        $isHostIdnaAscii = $isHostIdnaAscii ?? 0;
        $isLinkUrlencoded = $isLinkUrlencoded ?? 0;

        $theHttp = Lib::http();
        $theType = Lib::type();

        $hasQuery = (null !== $query);
        $hasFragment = (null !== $fragment);

        if ( null === $url ) {
            return Ret::throw(
                $fb,
                [ 'The `url` should not be null', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $theType->string_not_empty($url);

        if ( ! $ret->isOk([ &$urlStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `url` should string, not empty', $url ],
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

        if ( $hasQuery && (null === $_query) ) {
            return Ret::throw(
                $fb,
                [ 'The `query` should be a string, an array or a false', $query ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $hasFragment && (null === $_fragment) ) {
            return Ret::throw(
                $fb,
                [ 'The `fragment` should be a string or the FALSE', $fragment ],
                [ __FILE__, __LINE__ ]
            );
        }

        $resultParseUrlArguments = parse_url($urlStringNotEmpty);

        if ( false === $resultParseUrlArguments ) {
            return Ret::throw(
                $fb,
                [ 'The `url` should be valid url', $url ],
                [ __FILE__, __LINE__ ]
            );
        }

        $wasHost = ('' !== ($resultParseUrlArguments['host'] ?? ''));
        $wasPath = ('' !== ($resultParseUrlArguments['path'] ?? ''));

        if ( ! ($wasHost || $wasPath) ) {
            return Ret::throw(
                $fb,
                [ 'The `url` requires at least one `host` or `path`' ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $wasHost ) {
            if ( false
                || (-2 === $isHostIdnaAscii)
                || (-1 === $isHostIdnaAscii)
                || (1 === $isHostIdnaAscii)
                || (2 === $isHostIdnaAscii)
            ) {
                if ( -2 === $isHostIdnaAscii ) {
                    $utf8 = $theHttp->idn_to_utf8($resultParseUrlArguments['host']);

                    if ( false === $utf8 ) {
                        return Ret::throw(
                            $fb,
                            [ 'Cannot encode `url` host to UTF8 using `idn_to_utf8`', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    $resultParseUrlArguments['host'] = $utf8;

                } elseif ( -1 === $isHostIdnaAscii ) {
                    $test = $resultParseUrlArguments['host'];

                    if ( $theHttp->idn_to_utf8($test) !== $test ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `url` host should be valid UTF8 idn', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                } elseif ( 1 === $isHostIdnaAscii ) {
                    $test = $resultParseUrlArguments['host'];

                    if ( $theHttp->idn_to_ascii($test) !== $test ) {
                        return Ret::throw(
                            $fb,
                            [ 'The `url` host should be valid ASCII idn', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                } elseif ( 2 === $isHostIdnaAscii ) {
                    $ascii = $theHttp->idn_to_ascii($resultParseUrlArguments['host']);

                    if ( false === $ascii ) {
                        return Ret::throw(
                            $fb,
                            [ 'Cannot encode `url` host to ASCII using `idn_to_ascii`', $url ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    $resultParseUrlArguments['host'] = $ascii;
                }
            }
        }

        if ( $wasPath ) {
            if ( 1 === $isLinkUrlencoded ) {
                $test = str_replace('/', '', $resultParseUrlArguments['path']);

                if ( urlencode($test) !== $test ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `url` path should already be URL-encoded', $url ],
                        [ __FILE__, __LINE__ ]
                    );
                }

            } elseif ( 2 === $isLinkUrlencoded ) {
                $resultParseUrlArguments['path'] = urlencode($resultParseUrlArguments['path']);
                $resultParseUrlArguments['path'] = str_replace('%2F', '/', $resultParseUrlArguments['path']);
            }
        }

        $wasQuery = ('' !== ($resultParseUrlArguments['query'] ?? ''));
        $wasFragment = ('' !== ($resultParseUrlArguments['fragment'] ?? ''));

        if ( false === $_query ) {
            $resultParseUrlArguments['is_query'] = '';
            $resultParseUrlArguments['query'] = '';

        } else {
            $httpQuery = '';

            if ( $hasQuery && $wasQuery ) {
                $httpQuery = $theHttp->http_build_query_array($resultParseUrlArguments['query'], $_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ( $hasQuery ) {
                $httpQuery = $theHttp->http_build_query_array($_query);
                $httpQuery = http_build_query($httpQuery);

            } elseif ( $wasQuery ) {
                $httpQuery = $resultParseUrlArguments['query'];
            }

            if ( '' !== $httpQuery ) {
                $resultParseUrlArguments['is_query'] = '?';
                $resultParseUrlArguments['query'] = $httpQuery;
            }
        }

        if ( false === $_fragment ) {
            $resultParseUrlArguments['is_fragment'] = '';
            $resultParseUrlArguments['fragment'] = '';

        } else {
            $httpFragment = '';

            if ( $hasFragment && $wasFragment ) {
                $httpFragment = $_fragment;

            } elseif ( $hasFragment ) {
                $httpFragment = $_fragment;

            } elseif ( $wasFragment ) {
                $httpFragment = $resultParseUrlArguments['fragment'];
            }

            if ( '' !== $httpFragment ) {
                $resultParseUrlArguments['is_fragment'] = '#';
                $resultParseUrlArguments['fragment'] = $httpFragment;
            }
        }

        return Ret::ok($fb, $resultParseUrlArguments);
    }

    /**
     * @return Ret<array>|array{
     *     scheme: string,
     *     is_scheme: string,
     *     is_uri: string,
     *     user: string,
     *     is_pass: string,
     *     pass: string,
     *     is_userpass: string,
     *     host: string,
     *     is_port: string,
     *     port: string,
     *     is_hostport: string,
     *     path: string,
     *     is_query: string,
     *     query: string,
     *     is_fragment: string,
     *     fragment: string,
     * }
     */
    protected function parse_url_dict($fb, $parseUrlResult)
    {
        if ( ! is_array($parseUrlResult) ) {
            return Ret::throw(
                $fb,
                [ 'The `parseUrlResult` should be array', $parseUrlResult ],
                [ __FILE__, __LINE__ ]
            );
        }

        $parseUrlResultScheme = [
            'scheme'      => '',
            'is_scheme'   => null,
            'is_uri'      => null,
            'user'        => '',
            'is_pass'     => null,
            'pass'        => '',
            'is_userpass' => null,
            'host'        => '',
            'is_port'     => null,
            'port'        => '',
            'is_hostport' => null,
            'path'        => '',
            'is_query'    => null,
            'query'       => '',
            'is_fragment' => null,
            'fragment'    => '',
        ];

        $resultParseUrlDict = [];
        foreach ( $parseUrlResultScheme as $key => $val ) {
            $resultParseUrlDict[$key] = $parseUrlResult[$key] ?? $val;
        }

        $urlScheme = $resultParseUrlDict['scheme'];
        $urlUser = $resultParseUrlDict['user'];
        $urlPass = $resultParseUrlDict['pass'];
        $urlHost = $resultParseUrlDict['host'];
        $urlPort = $resultParseUrlDict['port'];
        $urlPath = $resultParseUrlDict['path'];
        $urlQuery = $resultParseUrlDict['query'];
        $urlFragment = $resultParseUrlDict['fragment'];

        $hasUrlScheme = ('' !== $urlScheme);
        $hasUrlUser = ('' !== $urlUser);
        $hasUrlPass = ('' !== $urlPass);
        $hasUrlHost = ('' !== $urlHost);
        $hasUrlPort = ('' !== $urlPort);
        $hasUrlPath = ('' !== $urlPath);
        $hasUrlQuery = ('' !== $urlQuery);
        $hasUrlFragment = ('' !== $urlFragment);

        $urlSchemeString = (string) $urlScheme;
        $urlPortInt = (int) $urlPort;
        $urlPathString = (string) $urlPath;

        if ( $hasUrlScheme ) {
            if ( $hasUrlPort ) {
                if ( false
                    || (('http' === $urlSchemeString) && (80 === $urlPortInt))
                    || (('https' === $urlSchemeString) && (443 === $urlPortInt))
                ) {
                    $resultParseUrlDict['port'] = '';
                    $resultParseUrlDict['is_port'] = '';
                }
            }
        }

        if ( null === $resultParseUrlDict['is_scheme'] ) {
            if ( $hasUrlScheme ) {
                $resultParseUrlDict['is_scheme'] = ':';
            }
        }

        if ( null === $resultParseUrlDict['is_uri'] ) {
            if ( true
                && ($hasUrlScheme)
                && (in_array($resultParseUrlDict['scheme'], [ 'data', 'javascript', 'mailto', 'tel', 'urn' ]))
            ) {
                $resultParseUrlDict['is_uri'] = '';

            } elseif ( false
                || ('' !== $resultParseUrlDict['user'])
                || ('' !== $resultParseUrlDict['pass'])
                || ('' !== $resultParseUrlDict['host'])
                || ('' !== $resultParseUrlDict['port'])
            ) {
                $resultParseUrlDict['is_uri'] = '//';
            }
        }

        if ( null === $resultParseUrlDict['is_pass'] ) {
            if ( $hasUrlPass ) {
                $resultParseUrlDict['is_pass'] = ':';
            }
        }

        if ( null === $resultParseUrlDict['is_userpass'] ) {
            if ( $hasUrlUser || $hasUrlPass ) {
                $resultParseUrlDict['is_userpass'] = '@';
            }
        }

        if ( null === $resultParseUrlDict['is_port'] ) {
            if ( $hasUrlPort ) {
                $resultParseUrlDict['is_port'] = ':';
            }
        }

        if ( null === $resultParseUrlDict['is_hostport'] ) {
            if ( $hasUrlHost || $hasUrlPort ) {
                $resultParseUrlDict['is_hostport'] = '/';
            }
        }

        if ( null !== $resultParseUrlDict['is_hostport'] ) {
            if ( $hasUrlPath ) {
                if ( '/' === substr($urlPathString, 0, 1) ) {
                    $resultParseUrlDict['path'] = ltrim($resultParseUrlDict['path'], '/');
                }
            }
        }

        [
            $explodeUrlPath,
            $explodeUrlQuery,
        ] = explode('?', $resultParseUrlDict['path'], 2) + [ null, null ];

        $hasExplodeUrlQuery = (null !== $explodeUrlQuery);

        if ( $hasExplodeUrlQuery ) {
            $resultParseUrlDict['path'] = $explodeUrlPath;
            $resultParseUrlDict['query'] = $explodeUrlQuery;
        }

        if ( null === $resultParseUrlDict['is_query'] ) {
            if ( $hasExplodeUrlQuery ) {
                $resultParseUrlDict['is_query'] = '?';

            } elseif ( $hasUrlQuery ) {
                $resultParseUrlDict['is_query'] = '?';
            }
        }

        if ( null === $resultParseUrlDict['is_fragment'] ) {
            if ( $hasUrlFragment ) {
                $resultParseUrlDict['is_fragment'] = '#';
            }
        }

        foreach ( $resultParseUrlDict as $key => $value ) {
            if ( null === $value ) {
                $resultParseUrlDict[$key] = '';
            }
        }

        return Ret::ok($fb, $resultParseUrlDict);
    }
}
