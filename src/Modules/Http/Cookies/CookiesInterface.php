<?php

namespace Gzhegow\Lib\Modules\Http\Cookies;

use Gzhegow\Lib\Modules\Http\HttpCookie\HttpCookie;


interface CookiesInterface
{
    /**
     * @return array<int, HttpCookie>
     */
    public function getCookieList() : array;


    /**
     * @param HttpCookie $refId
     */
    public function has(string $cookieName, string $cookiePath, ?string $cookieDomain = null, &$refId = null) : bool;

    /**
     * @param array<int, HttpCookie> $refList
     */
    public function hasAll(string $cookieName, string $cookiePath, ?string $cookieDomain = null, &$refList = null) : bool;


    public function getById(int $cookieId) : HttpCookie;

    public function get(string $cookieName, string $cookiePath, ?string $cookieDomain = null) : HttpCookie;

    /**
     * @return array<int, HttpCookie>
     */
    public function getAll(string $cookieName, string $cookiePath, ?string $cookieDomain = null) : array;


    /**
     * @return static
     */
    public function set(
        string $name, ?string $value = null,
        $expires_or_options = null,
        ?string $path = null, ?string $domain = null,
        ?bool $secure = null, ?bool $httponly = null
    );


    /**
     * @return static
     */
    public function deleteById(int $cookieId);

    /**
     * @return static
     */
    public function delete(string $cookieName, string $cookiePath, ?string $cookieDomain = null);

    /**
     * @return static
     */
    public function deleteAll(string $cookieName, string $cookiePath, ?string $cookieDomain = null);


    /**
     * @return HttpCookie[]
     */
    public function endClean() : array;

    /**
     * @return HttpCookie[]
     */
    public function endFlush() : array;


    /**
     * @return static
     */
    public static function getInstance();
}
