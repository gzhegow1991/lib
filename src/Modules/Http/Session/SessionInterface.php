<?php

namespace Gzhegow\Lib\Modules\Http\Session;


interface SessionInterface
{
    /**
     * @return static
     */
    public function disableNativeSession(?bool $disableNativeSession = null);

    /**
     * @return static
     */
    public function useNativeSession(?bool $useNativeSession = null);


    /**
     * @param string $refValue
     */
    public function has(string $key, &$refValue = null) : bool;

    public function get(string $key) : string;

    /**
     * @return static
     */
    public function set(string $key, string $value);

    /**
     * @return static
     */
    public function unset(string $key);


    /**
     * @return static
     */
    public static function getInstance();
}
