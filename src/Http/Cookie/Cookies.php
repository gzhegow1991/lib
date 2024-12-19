<?php

namespace Gzhegow\Lib\Http\Cookie;

use Gzhegow\Lib\Lib;


class Cookies
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var array<int, array{0: string, 1: array}>
     */
    protected $cookiesList = [];
    /**
     * @var array<string, int>
     */
    protected $cookiesIndex = [];

    /**
     * @var bool
     */
    protected $modeDelay = false;


    public function isModeDelay() : bool
    {
        return $this->modeDelay;
    }

    public function setModeDelay(bool $modeDelay) : void
    {
        $this->modeDelay = $modeDelay;
    }


    /**
     * @return array<int, array{0: string, 1: array}>
     */
    public function getList() : array
    {
        return $this->cookiesList;
    }

    /**
     * @return array<string, int>
     */
    public function getIndex() : array
    {
        return $this->cookiesIndex;
    }


    public function add(
        array $setrawcookieArgs,
        string $cookieName, string $cookiePath, string $cookieDomain = null
    ) : void
    {
        $this->modeDelay
            ? $this->addCookie($setrawcookieArgs, $cookieName, $cookiePath, $cookieDomain)
            : $this->sendCookie($setrawcookieArgs);
    }

    public function addCookie(
        array $setrawcookieArgs,
        string $cookieName, string $cookiePath, string $cookieDomain = null
    ) : void
    {
        $id = $this->id++;
        $index = $this->indexCookie($cookieName, $cookiePath, $cookieDomain);

        $this->cookiesList[ $id ] = [ $index, $setrawcookieArgs ];
        $this->cookiesIndex[ $index ] = $id;
    }

    public function sendCookie(array $setrawcookieArgs) : void
    {
        call_user_func_array([ Lib::class, 'http_setrawcookie' ], $setrawcookieArgs);
    }


    public function delete(int $cookieId) : void
    {
        if (! $this->modeDelay) return;

        if (isset($this->cookiesList[ $cookieId ])) {
            [ $cookieIndex ] = $this->cookiesList[ $cookieId ];

            $cookieIndexId = $this->cookiesIndex[ $cookieIndex ] ?? null;

            if ((null !== $cookieIndexId)
                && ($cookieIndexId === $cookieId)
            ) {
                unset($this->cookiesIndex[ $cookieIndex ]);
            }
        }

        unset($this->cookiesList[ $cookieId ]);
    }

    public function remove(string $cookieName, string $cookiePath, string $cookieDomain = null) : void
    {
        if (! $this->modeDelay) return;

        $cookieIndex = $this->indexCookie($cookieName, $cookiePath, $cookieDomain);

        foreach ( $this->cookiesIndex as $index => $cookieId ) {
            if (null !== Lib::str_starts($index, $cookieIndex)) {
                $this->delete($cookieId);
            }
        }
    }


    public function flush() : array
    {
        $result = [];

        foreach ( $this->cookiesIndex as $cookieIndex => $cookieId ) {
            $result[] = $this->cookiesList[ $cookieId ][ 1 ];
        }

        $this->cookiesList = [];
        $this->cookiesIndex = [];

        return $result;
    }

    public function flushSend() : array
    {
        $result = $this->flush();

        foreach ( $result as $setrawcookieArgs ) {
            if (! headers_sent()) {
                call_user_func_array([ Lib::class, 'http_setrawcookie' ], $setrawcookieArgs);
            }
        }

        return $result;
    }


    protected function indexCookie(string $cookieName, string $cookiePath, string $cookieDomain = null) : string
    {
        $devnull = Lib::parse_string($cookieName) ?? Lib::php_throw('The `cookieName` should be non empty string');
        $devnull = Lib::parse_string($cookiePath) ?? Lib::php_throw('The `cookiePath` should be non empty string');
        $devnull = Lib::parse_string($cookieDomain) ?? Lib::php_throw('The `cookieDomain` should be non empty string');

        return "{$cookieDomain}\0{$cookiePath}\0{$cookieName}";
    }
}
