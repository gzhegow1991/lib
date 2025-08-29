<?php

namespace Gzhegow\Lib\Modules\Http\Cookies;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Http\HttpCookie\HttpCookie;


class DefaultCookies implements CookiesInterface
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var bool
     */
    protected $useQueueMode = true;

    /**
     * @var array<int, HttpCookie>
     */
    protected $cookiesList = [];
    /**
     * @var array<int, HttpCookie>
     */
    protected $cookiesListAlreadySent = [];

    /**
     * @var array<int, string>
     */
    protected $cookiesIndexById = [];
    /**
     * @var array<string, array<int, bool>>
     */
    protected $cookiesIndexByIndex = [];


    private function __construct()
    {
        $this->loadCookiesList();

        $this->registerShutdownFunction();
    }

    protected function loadCookiesList() : void
    {
        $theHttp = Lib::http();

        $httpHeaders = $theHttp->headers_list();

        foreach ( $httpHeaders as $httpHeader ) {
            if ( 'SET-COOKIE' !== $httpHeader->getName() ) {
                continue;
            }

            $httpCookie = HttpCookie::fromObjectHttpHeader($httpHeader)->orThrow();

            $this->registerCookie($httpCookie, $alreadySent = true);
        }
    }


    /**
     * @return static
     */
    public function useQueueMode(?bool $useQueueMode = null)
    {
        $useQueueMode = $useQueueMode ?? true;

        $this->useQueueMode = $useQueueMode;

        if ( ! $this->useQueueMode ) {
            if ( [] !== $this->cookiesList ) {
                $this->endFlush();
            }
        }

        return $this;
    }


    /**
     * @return array<int, HttpCookie>
     */
    public function getCookieList() : array
    {
        return $this->cookiesList;
    }

    /**
     * @return array<int, HttpCookie>
     */
    public function getCookiesListAlreadySent() : array
    {
        return $this->cookiesListAlreadySent;
    }


    /**
     * @param int $refId
     */
    public function has(
        string $cookieName, string $cookiePath, ?string $cookieDomain = null,
        &$refId = null
    ) : bool
    {
        $refId = null;

        $cookieIndex = $this->indexCookie($cookieName, $cookiePath, $cookieDomain);

        if ( isset($this->cookiesIndexByIndex[$cookieIndex]) ) {
            $cookieIds = $this->cookiesIndexByIndex[$cookieIndex];
            $cookieId = key($cookieIds);

            $refId = $cookieId;

            return true;
        }

        return false;
    }

    /**
     * @param array<int, HttpCookie> $refList
     */
    public function hasAll(
        string $cookieName, string $cookiePath, ?string $cookieDomain = null,
        &$refList = null
    ) : bool
    {
        $refList = [];

        $index = $this->indexCookie($cookieName, $cookiePath, $cookieDomain);

        if ( isset($this->cookiesIndexByIndex[$index]) ) {
            $list = [];
            foreach ( $this->cookiesIndexByIndex[$index] as $cookieId => $bool ) {
                $list[$cookieId] = $this->cookiesList[$cookieId];
            }

            $refList = $list;

            return true;
        }

        return false;
    }

    public function hasById(
        int $cookieId,
        ?HttpCookie &$cookie = null
    ) : bool
    {
        $cookie = null;

        if ( isset($this->cookiesList[$cookieId]) ) {
            $cookie = $this->cookiesList[$cookieId];

            return true;
        }

        return false;
    }


    public function get(string $cookieName, string $cookiePath, ?string $cookieDomain = null) : HttpCookie
    {
        $status = $this->has($cookieName, $cookiePath, $cookieDomain, $cookieId);

        if ( ! $status ) {
            throw new RuntimeException(
                [
                    ''
                    . 'Cookie not found: '
                    . '[ ' . implode(' ][ ', [ $cookieName, $cookiePath, $cookieDomain ]) . ' ]',
                    //
                    $cookieName,
                    $cookiePath,
                    $cookieDomain,
                ]
            );
        }

        return $this->cookiesList[$cookieId];
    }

    /**
     * @return array<int, HttpCookie>
     */
    public function getAll(string $cookieName, string $cookiePath, ?string $cookieDomain = null) : array
    {
        $this->hasAll($cookieName, $cookiePath, $cookieDomain, $cookieList);

        return $cookieList;
    }

    public function getById(int $cookieId) : HttpCookie
    {
        return $this->cookiesList[$cookieId];
    }


    /**
     * @return static
     */
    public function set(
        string $name, ?string $value = null,
        $expires_or_options = null,
        ?string $path = null, ?string $domain = null,
        ?bool $secure = null, ?bool $httponly = null
    )
    {
        $setrawcookieArgs = func_get_args();

        $httpCookie = HttpCookie::fromArraySetrawcookieArgs($setrawcookieArgs)->orThrow();

        $cookieId = $this->registerCookie($httpCookie);

        if ( ! $this->useQueueMode ) {
            $theHttp = Lib::http();

            call_user_func_array(
                [ $theHttp, 'setrawcookie' ],
                $setrawcookieArgs
            );

            $this->cookiesListAlreadySent[$cookieId] = $httpCookie;
        }

        return $this;
    }


    /**
     * @return static
     */
    public function deleteById(int $cookieId)
    {
        if ( ! isset($this->cookiesList[$cookieId]) ) {
            return null;
        }

        if ( isset($this->cookiesIndexById[$cookieId]) ) {
            $cookieIndex = $this->cookiesIndexById[$cookieId];

            unset($this->cookiesIndexById[$cookieId]);
            unset($this->cookiesIndexByIndex[$cookieIndex][$cookieId]);
        }

        $httpCookie = $this->cookiesList[$cookieId];

        unset($this->cookiesList[$cookieId]);

        if ( isset($this->cookiesListAlreadySent[$cookieId]) ) {
            $theHttp = Lib::http();

            $setrawcookieArgs = $httpCookie->toArraySetrawcookieArgs();
            $setrawcookieArgs[1] = '';
            $setrawcookieArgs[2]['expires'] = time() - 99999;

            call_user_func_array(
                [ $theHttp, 'setrawcookie' ],
                $setrawcookieArgs
            );
        }

        return $this;
    }


    /**
     * @return static
     */
    public function delete(string $cookieName, string $cookiePath, ?string $cookieDomain = null)
    {
        $status = $this->has($cookieName, $cookiePath, $cookieDomain, $cookieId);

        if ( $status ) {
            $this->deleteById($cookieId);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function deleteAll(string $cookieName, string $cookiePath, ?string $cookieDomain = null)
    {
        $this->hasAll($cookieName, $cookiePath, $cookieDomain, $cookieList);

        foreach ( $cookieList as $cookieId => $httpCookie ) {
            $this->deleteById($cookieId);
        }

        return $this;
    }


    /**
     * @return HttpCookie[]
     */
    public function endClean() : array
    {
        $cookieList = $this->cookiesList;

        $this->cookiesList = [];

        $this->cookiesIndexById = [];
        $this->cookiesIndexByIndex = [];

        return $cookieList;
    }

    /**
     * @return HttpCookie[]
     */
    public function endFlush() : array
    {
        $theHttp = Lib::http();

        $cookieList = $this->cookiesList;

        $this->cookiesList = [];

        $this->cookiesIndexById = [];
        $this->cookiesIndexByIndex = [];

        $isHeadersSent = headers_sent();

        if ( ! $isHeadersSent ) {
            foreach ( $cookieList as $cookieId => $httpCookie ) {
                if ( isset($this->cookiesListAlreadySent[$cookieId]) ) {
                    continue;
                }

                call_user_func_array(
                    [ $theHttp, 'setrawcookie' ],
                    $httpCookie->toArraySetrawcookieArgs()
                );

                $this->cookiesListAlreadySent[$cookieId] = $httpCookie;
            }
        }

        return $cookieList;
    }


    public function registerShutdownFunction() : void
    {
        $theEntrypoint = Lib::entrypoint();

        $theEntrypoint->registerShutdownFunction([ $this, 'onShutdownSendCookiesRegistered' ]);
    }

    public function onShutdownSendCookiesRegistered() : void
    {
        $this->endFlush();
    }


    protected function registerCookie(HttpCookie $httpCookie, ?bool $alreadySent = null) : int
    {
        $alreadySent = $alreadySent ?? false;

        $id = $this->id++;

        $httpCookieName = $httpCookie->getName();
        $httpCookiePath = $httpCookie->getPath();
        $httpCookie->hasDomain($httpCookieDomain);

        $index = $this->indexCookie($httpCookieName, $httpCookiePath, $httpCookieDomain);

        $this->cookiesIndexById[$id] = $index;
        $this->cookiesIndexByIndex[$index][$id] = true;

        $this->cookiesList[$id] = $httpCookie;

        if ( $alreadySent ) {
            $this->cookiesListAlreadySent[$id] = $httpCookie;
        }

        return $id;
    }

    protected function indexCookie(string $cookieName, string $cookiePath, ?string $cookieDomain = null) : string
    {
        $theType = Lib::type();

        $cookieNameStringNotEmpty = $theType->string_not_empty($cookieName)->orThrow();
        $cookiePathStringNotEmpty = $theType->string_not_empty($cookiePath)->orThrow();

        $cookieDomainStringNotEmpty = '';
        if ( null !== $cookieDomain ) {
            $cookieDomainStringNotEmpty = $theType->string_not_empty($cookieDomain)->orThrow();
        }

        return "{$cookieDomainStringNotEmpty}\0{$cookiePathStringNotEmpty}\0{$cookieNameStringNotEmpty}";
    }


    /**
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance = static::$instance ?? new static();
    }

    protected static $instance;
}
