<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
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
    public function type_address_ip(&$result, $value) : bool
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

    /**
     * @param string|null $result
     */
    public function type_address_ip_v4(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (false === ($_value = filter_var($_value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_address_ip_v6(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (false === ($_value = filter_var($_value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function type_address_mac(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        $status = preg_match(
            '/^([0-9A-Fa-f]{2}([-:])){5}([0-9A-Fa-f]{2})$/',
            $_value
        );

        if (! $status) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function type_subnet(&$result, $value, string $ipDefault = null) : bool
    {
        $result = null;

        $status = false
            || $this->type_subnet_v4($_value, $value, $ipDefault)
            || $this->type_subnet_v6($_value, $value, $ipDefault);

        if ($status) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function type_subnet_v4(&$result, $value, string $ipDefault = null) : bool
    {
        $result = null;

        $theType = Lib::type();

        if (! $theType->string_not_empty($_value, $value)) {
            return false;
        }

        [ $subnetOrIp, $subnet ] = explode('/', $_value, 2) + [ '', null ];

        $hasSlash = (null !== $subnet);

        if ($hasSlash) {
            if ('' === $subnet) {
                return false;
            }

            $status = false
                || $this->type_subnet_v4_iplike($subnetInt, $subnet)
                || $theType->numeric_int($subnetInt, $subnet);

            if (! $status) {
                return false;
            }

            if (($subnetInt < 0) || ($subnetInt > 32)) {
                return false;
            }

            if ('' === $subnetOrIp) {
                $addressIpString = $ipDefault;

                if (null === $addressIpString) {
                    return false;

                } elseif (! filter_var($addressIpString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return false;
                }

            } else {
                if (! $this->type_address_ip_v4($addressIpString, $subnetOrIp)) {
                    return false;
                }
            }

        } else {
            if ('' === $subnetOrIp) {
                return false;
            }

            if ($this->type_subnet_v4_iplike($subnetInt, $subnetOrIp)) {
                if (($subnetInt < 0) || ($subnetInt > 32)) {
                    return false;
                }

                $addressIpString = $ipDefault;

                if (null === $addressIpString) {
                    return false;

                } elseif (! filter_var($addressIpString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return false;
                }

            } elseif ($this->type_address_ip_v4($addressIpString, $subnetOrIp)) {
                $subnetInt = 32;
            }
        }

        $hasIpString = (null !== $addressIpString);
        $hasSubnetInt = (null !== $subnetInt);

        if ($hasIpString && $hasSubnetInt) {
            $result = "{$addressIpString}/{$subnetInt}";

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function type_subnet_v6(&$result, $value, string $ipDefault = null) : bool
    {
        $result = null;

        $theType = Lib::type();

        if (! $theType->string_not_empty($_value, $value)) {
            return false;
        }

        [ $ip, $subnet ] = explode('/', $_value, 2) + [ '', null ];

        $hasSlash = (null !== $subnet);

        if (! $hasSlash) {
            return false;
        }

        if ('' === $subnet) {
            return false;
        }

        $status = $theType->numeric_int($subnetInt, $subnet);
        if (! $status) {
            return false;
        }

        if (($subnetInt < 0) || ($subnetInt > 128)) {
            return false;
        }

        if ('' === $ip) {
            $addressIpString = $ipDefault;

            if (! filter_var($addressIpString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return false;
            }

        } else {
            if (! $this->type_address_ip_v6($addressIpString, $ip)) {
                return false;
            }
        }

        $hasIpString = (null !== $addressIpString);
        $hasSubnetInt = (null !== $subnetInt);

        if ($hasIpString && $hasSubnetInt) {
            $result = "{$addressIpString}/{$subnetInt}";

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    protected function type_subnet_v4_iplike(&$result, $subnet) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_subnet, $subnet)) {
            return false;
        }

        if (false === strpos($_subnet, '.')) {
            return false;
        }

        if (! filter_var($_subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $long = ip2long($_subnet);
        $inv = ~$long & 0xFFFFFFFF;

        $status = (0 === (($inv + 1) & $inv));
        if ($status) {
            $subnetInt = decbin($long);
            $subnetInt = rtrim($subnetInt, '0');
            $subnetInt = strlen($subnetInt);

            $result = $subnetInt;

            return true;
        }

        return false;
    }


    public function is_ip_in_subnet(string $ip, string $subnet, string $subnetIpDefault = null) : bool
    {
        $ipV4String = null;
        $ipV6String = null;
        $subnetV4String = null;
        $subnetV6String = null;

        $statusIp = false
            || $this->type_address_ip_v4($ipV4String, $ip)
            || $this->type_address_ip_v6($ipV6String, $ip);

        if (! $statusIp) {
            throw new LogicException(
                [
                    'The `ip` should be valid IPv4 or IPv6 address',
                    $ip,
                ]
            );
        }

        $statusSubnet = false
            || $this->type_subnet_v4($subnetV4String, $subnet, $subnetIpDefault)
            || $this->type_subnet_v6($subnetV6String, $subnet, $subnetIpDefault);

        if (! $statusSubnet) {
            throw new LogicException(
                [
                    'The `subnet` should be valid IPv4 or IPv6 mask',
                    $subnet,
                    $subnetIpDefault,
                ]
            );
        }

        $isIpV4 = (null !== $ipV4String) && (null !== $subnetV4String);
        $isIpV6 = (null !== $ipV6String) && (null !== $subnetV6String);

        if ($isIpV4) {
            $status = $this->is_ip_in_subnet_v4($ipV4String, $subnetV4String);

        } elseif ($isIpV6) {
            $status = $this->is_ip_in_subnet_v6($ipV6String, $subnetV6String);

        } else {
            $status = false;
        }

        return $status;
    }

    public function is_ip_in_subnet_v4(string $ip, string $subnet, string $subnetIpDefault = null) : bool
    {
        $status = $this->type_address_ip_v4($ipString, $ip);
        if (! $status) {
            throw new LogicException(
                [
                    'The `ip` should be valid IPv4 address',
                    $ip,
                ]
            );
        }

        $status = $this->type_subnet_v4($subnetString, $subnet, $subnetIpDefault);
        if (! $status) {
            throw new LogicException(
                [
                    'The `subnet` should be valid IPv4 mask',
                    $subnet,
                    $subnetIpDefault,
                ]
            );
        }

        [ $subnetIpString, $subnetBitsInt ] = explode('/', $subnetString, 2);

        $ipLong = ip2long($ipString);
        $subnetIpLong = ip2long($subnetIpString);
        $subnetBitsInt = (int) $subnetBitsInt;

        $subnetBitsIntLong = ~((1 << (32 - $subnetBitsInt)) - 1);

        $status = ($ipLong & $subnetBitsIntLong) === ($subnetIpLong & $subnetBitsIntLong);

        return $status;
    }

    public function is_ip_in_subnet_v6(string $ip, string $subnet, string $subnetIpDefault = null) : bool
    {
        $status = $this->type_address_ip_v6($ipString, $ip);
        if (! $status) {
            throw new LogicException(
                [
                    'The `ip` should be valid IPv6 address',
                    $ip,
                ]
            );
        }

        $status = $this->type_subnet_v6($subnetString, $subnet, $subnetIpDefault);
        if (! $status) {
            throw new LogicException(
                [
                    'The `subnet` should be valid IPv6 mask',
                    $subnet,
                    $subnetIpDefault,
                ]
            );
        }

        [ $subnetIpString, $subnetBitsInt ] = explode('/', $subnetString, 2);

        $ipBin = inet_pton($ipString);
        $subnetIpBin = inet_pton($subnetIpString);
        $subnetBitsInt = (int) $subnetBitsInt;

        $ipBits = unpack('H*', $ipBin)[ 1 ];
        $subnetIpBits = unpack('H*', $subnetIpBin)[ 1 ];

        $len = strlen($ipBits);
        $ipBin = '';
        for ( $i = 0; $i < $len; $i++ ) {
            $bin = substr($ipBits, $i, 1);
            $bin = base_convert($bin, 16, 2);
            $bin = str_pad($bin, 4, '0', STR_PAD_LEFT);

            $ipBin .= $bin;
        }

        $len = strlen($subnetIpBits);
        $subnetIpBin = '';
        for ( $i = 0; $i < $len; $i++ ) {
            $bin = substr($subnetIpBits, $i, 1);
            $bin = base_convert($bin, 16, 2);
            $bin = str_pad($bin, 4, '0', STR_PAD_LEFT);

            $subnetIpBin .= $bin;
        }

        $status = substr($ipBin, 0, $subnetBitsInt) === substr($subnetIpBin, 0, $subnetBitsInt);

        return $status;
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
