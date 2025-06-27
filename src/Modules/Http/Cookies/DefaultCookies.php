<?php

namespace Gzhegow\Lib\Modules\Http\Cookies;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Http\HttpCookie\HttpCookie;


class DefaultCookies implements CookiesInterface
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var array<int, HttpCookie>
     */
    protected $cookiesList = [];

    /**
     * @var array<int, string>
     */
    protected $cookiesIndexById = [];
    /**
     * @var array<string, array<int, bool>>
     */
    protected $cookiesIndexByIndex = [];

    /**
     * @var bool
     */
    protected $isHeadersSent = false;
    /**
     * @var bool
     */
    protected $isHeaderRegisterCallbackCalled = false;


    private function __construct()
    {
        $this->loadCookiesList();

        $this->isHeadersSent();

        $this->headerRegisterCallback();
    }


    protected function loadCookiesList() : void
    {
        $httpHeaders = Lib::http()->headers_list();

        foreach ( $httpHeaders as $httpHeader ) {
            if ('SET-COOKIE' !== $httpHeader->getName()) {
                continue;
            }

            $httpCookie = HttpCookie::fromObjectHttpHeader($httpHeader);
            $httpCookieName = $httpCookie->getName();
            $httpCookiePath = $httpCookie->getPath();
            $httpCookie->hasDomain($httpCookieDomain);

            $id = $this->id++;

            $index = $this->indexCookie($httpCookieName, $httpCookiePath, $httpCookieDomain);

            $this->cookiesIndexById[ $id ] = $index;
            $this->cookiesIndexByIndex[ $index ] = $id;

            $this->cookiesList[ $id ] = $httpCookie;
        }
    }


    protected function isHeadersSent() : bool
    {
        return $this->isHeadersSent = headers_sent();
    }


    /**
     * @return array<int, HttpCookie>
     */
    public function getList() : array
    {
        return $this->cookiesList;
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

        if (isset($this->cookiesIndexByIndex[ $cookieIndex ])) {
            $cookieIds = $this->cookiesIndexByIndex[ $cookieIndex ];
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

        if (isset($this->cookiesIndexByIndex[ $index ])) {
            $list = [];
            foreach ( $this->cookiesIndexByIndex[ $index ] as $cookieId => $bool ) {
                $list[ $cookieId ] = $this->cookiesList[ $cookieId ];
            }

            $refList = $list;

            return true;
        }

        return false;
    }


    public function getById(int $cookieId) : HttpCookie
    {
        return $this->cookiesList[ $cookieId ];
    }

    public function get(string $cookieName, string $cookiePath, ?string $cookieDomain = null) : HttpCookie
    {
        $status = $this->has($cookieName, $cookiePath, $cookieDomain, $cookieId);

        if (! $status) {
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

        return $this->cookiesList[ $cookieId ];
    }

    /**
     * @return array<int, HttpCookie>
     */
    public function getAll(string $cookieName, string $cookiePath, ?string $cookieDomain = null) : array
    {
        $this->hasAll($cookieName, $cookiePath, $cookieDomain, $cookieList);

        return $cookieList;
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
        if ($this->isHeadersSent()) {
            $this->setSendToResponse(func_get_args());

        } else {
            $this->setAddToQueue(func_get_args());
        }

        return $this;
    }

    protected function setAddToQueue(array $setrawcookieArgs = []) : void
    {
        $httpCookie = HttpCookie::fromArraySetrawcookieArgs($setrawcookieArgs);

        $id = $this->id++;

        $httpCookieName = $httpCookie->getName();
        $httpCookiePath = $httpCookie->getPath();
        $httpCookie->hasDomain($httpCookieDomain);

        $index = $this->indexCookie($httpCookieName, $httpCookiePath, $httpCookieDomain);

        $this->cookiesIndexById[ $id ] = $index;
        $this->cookiesIndexByIndex[ $index ][ $id ] = true;

        $this->cookiesList[ $id ] = $httpCookie;
    }

    protected function setSendToResponse(array $setrawcookieArgs = []) : void
    {
        $httpCookie = HttpCookie::fromArraySetrawcookieArgs($setrawcookieArgs);

        call_user_func_array(
            [ Lib::http(), 'setrawcookie' ],
            $setrawcookieArgs
        );

        $id = $this->id++;

        $httpCookieName = $httpCookie->getName();
        $httpCookiePath = $httpCookie->getPath();
        $httpCookie->hasDomain($httpCookieDomain);

        $index = $this->indexCookie($httpCookieName, $httpCookiePath, $httpCookieDomain);

        $this->cookiesIndexById[ $id ] = $index;
        $this->cookiesIndexByIndex[ $index ][ $id ] = true;

        $this->cookiesList[ $id ] = $httpCookie;
    }


    /**
     * @return static
     */
    public function deleteById(int $cookieId)
    {
        if (! isset($this->cookiesList[ $cookieId ])) {
            return null;
        }

        if (isset($this->cookiesIndexById[ $cookieId ])) {
            $cookieIndex = $this->cookiesIndexById[ $cookieId ];

            unset($this->cookiesIndexById[ $cookieId ]);
            unset($this->cookiesIndexByIndex[ $cookieIndex ][ $cookieId ]);
        }

        $httpCookie = $this->cookiesList[ $cookieId ];

        unset($this->cookiesList[ $cookieId ]);

        $isHeadersSent = $this->isHeadersSent();

        if ($isHeadersSent) {
            $setrawcookieArgs = $httpCookie->toArraySetrawcookieArgs();
            $setrawcookieArgs[ 1 ] = '';
            $setrawcookieArgs[ 2 ][ 'expires' ] = time() - 99999;

            setrawcookie(...$setrawcookieArgs);
        }

        return $this;
    }


    /**
     * @return static
     */
    public function delete(string $cookieName, string $cookiePath, ?string $cookieDomain = null)
    {
        $status = $this->has($cookieName, $cookiePath, $cookieDomain, $cookieId);

        if ($status) {
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
    public function flush() : array
    {
        $result = $this->cookiesList;

        $this->cookiesList = [];

        $this->cookiesIndexById = [];
        $this->cookiesIndexByIndex = [];

        return $result;
    }

    /**
     * @return HttpCookie[]
     */
    public function flushSend() : array
    {
        $result = $this->flush();

        foreach ( $result as $httpCookie ) {
            call_user_func_array(
                [ Lib::http(), 'setrawcookie' ],
                $httpCookie->toArraySetrawcookieArgs()
            );
        }

        return $result;
    }


    public function headerRegisterCallbackFn() : void
    {
        $this->flushSend();
    }

    public function headerRegisterCallback() : void
    {
        if (! $this->isHeaderRegisterCallbackCalled) {
            register_shutdown_function([ $this, 'headerRegisterCallbackFn' ]);

            $this->isHeaderRegisterCallbackCalled = true;
        }
    }


    protected function indexCookie(string $cookieName, string $cookiePath, ?string $cookieDomain = null) : string
    {
        $theType = Lib::type();

        if (! $theType->string_not_empty($var, $cookieName)) {
            throw new LogicException(
                'The `cookieName` should be a non-empty string'
            );
        }

        if (! $theType->string_not_empty($var, $cookiePath)) {
            throw new LogicException(
                'The `cookiePath` should be a non-empty string'
            );
        }

        if (null !== $cookieDomain) {
            if (! $theType->string_not_empty($var, $cookieDomain)) {
                throw new LogicException(
                    'The `cookieDomain` should be a non-empty string'
                );
            }
        }

        return "{$cookieDomain}\0{$cookiePath}\0{$cookieName}";
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
