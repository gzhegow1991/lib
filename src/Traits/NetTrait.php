<?php

namespace Gzhegow\Lib\Traits;


trait NetTrait
{
    public static function net_ip() : string
    {
        $_ip = static::net_ip_localhost();

        return $_ip;
    }

    public static function net_ip_client() : ?string
    {
        $_ip = static::parse_ip($_SERVER[ 'REMOTE_ADDR' ] ?? null);

        return $_ip;
    }

    public static function net_ip_localhost() : ?string
    {
        $_ip = null
            ?? static::parse_ip($_SERVER[ 'REMOTE_ADDR' ] ?? null)
            ?? '127.0.0.1';

        return $_ip;
    }

    public static function net_ip_client_proxy()
    {
        $_ip = null
            ?? static::parse_ip($_SERVER[ 'REMOTE_ADDR' ] ?? null)
            ?? static::parse_ip($_SERVER[ 'HTTP_CLIENT_IP' ] ?? null)
            ?? static::parse_ip($_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ?? null);

        return $_ip;
    }


    public static function net_user_agent() : string
    {
        $_ip = static::net_user_agent_php();

        return $_ip;
    }

    public static function net_user_agent_client() : string
    {
        $_userAgent = static::parse_string_not_empty($_SERVER[ 'HTTP_USER_AGENT' ] ?? null);

        return $_userAgent;
    }

    public static function net_user_agent_php() : string
    {
        $_userAgent = null
            ?? static::net_user_agent_client()
            ?? 'PHP ' . phpversion();

        return $_userAgent;
    }

    public static function net_user_agent_browser() : string
    {
        $_userAgent = null
            ?? static::net_user_agent_client()
            ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36';

        return $_userAgent;
    }
}
