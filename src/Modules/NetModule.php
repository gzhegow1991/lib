<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class NetModule
{
    public function __construct()
    {
        if (! extension_loaded('filter')) {
            throw new RuntimeException(
                'Missing PHP extension: filter'
            );
        }
    }


    /**
     * @param string|null $result
     */
    public function type_ip(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (false === ($_value = filter_var($_value, FILTER_VALIDATE_IP))) {
            return false;
        }

        $result = $_value;

        return true;
    }


    public function ip() : string
    {
        $_ip = $this->ip_localhost();

        return $_ip;
    }

    public function ip_client() : ?string
    {
        $_ip = Lib::parse()->ip($_SERVER[ 'REMOTE_ADDR' ] ?? null);

        return $_ip;
    }

    public function ip_localhost() : ?string
    {
        $_ip = null
            ?? Lib::parse()->ip($_SERVER[ 'REMOTE_ADDR' ] ?? null)
            ?? '127.0.0.1';

        return $_ip;
    }

    public function ip_client_proxy()
    {
        $_ip = null
            ?? Lib::parse()->ip($_SERVER[ 'REMOTE_ADDR' ] ?? null)
            ?? Lib::parse()->ip($_SERVER[ 'HTTP_CLIENT_IP' ] ?? null)
            ?? Lib::parse()->ip($_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ?? null);

        return $_ip;
    }


    public function user_agent() : string
    {
        $_ip = $this->user_agent_php();

        return $_ip;
    }

    public function user_agent_client() : string
    {
        $_userAgent = Lib::parse()->string_not_empty($_SERVER[ 'HTTP_USER_AGENT' ] ?? null);

        return $_userAgent;
    }

    public function user_agent_php() : string
    {
        $_userAgent = null
            ?? $this->user_agent_client()
            ?? 'PHP ' . phpversion();

        return $_userAgent;
    }

    public function user_agent_browser() : string
    {
        $_userAgent = null
            ?? $this->user_agent_client()
            ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36';

        return $_userAgent;
    }
}
