<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Net\SubnetV4;
use Gzhegow\Lib\Modules\Net\SubnetV6;
use Gzhegow\Lib\Modules\Net\AddressIpV4;
use Gzhegow\Lib\Modules\Net\AddressIpV6;
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
     * @param AddressIpV4|AddressIpV6|null $result
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
     * @param AddressIpV4|null $result
     */
    public function type_address_ip_v4(&$result, $value) : bool
    {
        $result = null;

        if ($value instanceof AddressIpV4) {
            $result = $value;

            return true;
        }

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (false === ($addressIpV4 = filter_var($_value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))) {
            return false;
        }

        $result = AddressIpV4::fromValid($addressIpV4);

        return true;
    }

    /**
     * @param AddressIpV6|null $result
     */
    public function type_address_ip_v6(&$result, $value) : bool
    {
        $result = null;

        if ($value instanceof AddressIpV6) {
            $result = $value;

            return true;
        }

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (false === ($addressIpV6 = filter_var($_value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))) {
            return false;
        }

        $result = AddressIpV6::fromValid($addressIpV6);

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
     * @param SubnetV4|SubnetV6|null $result
     */
    public function type_subnet(&$result, $value, ?string $ipFallback = null) : bool
    {
        $result = null;

        $status = false
            || $this->type_subnet_v4($_value, $value, $ipFallback)
            || $this->type_subnet_v6($_value, $value, $ipFallback);

        if ($status) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param SubnetV4|null $result
     */
    public function type_subnet_v4(&$result, $value, ?string $ipFallback = null) : bool
    {
        $result = null;

        if ($value instanceof SubnetV4) {
            $result = $value;

            return true;
        }

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
                $addressIpString = $ipFallback;

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

                $addressIpString = $ipFallback;

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
            $subnetV4 = "{$addressIpString}/{$subnetInt}";

            $result = SubnetV4::fromValid($subnetV4);

            return true;
        }

        return false;
    }

    /**
     * @param SubnetV6|null $result
     */
    public function type_subnet_v6(&$result, $value, ?string $ipFallback = null) : bool
    {
        $result = null;

        if ($value instanceof SubnetV6) {
            $result = $value;

            return true;
        }

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
            $addressIpString = $ipFallback;

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
            $subnetV6 = "{$addressIpString}/{$subnetInt}";

            $result = SubnetV6::fromValid($subnetV6);

            return true;
        }

        return false;
    }

    /**
     * @param int|null $result
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


    /**
     * @param string|AddressIpV4|AddressIpV6  $addressIp
     * @param array<string|SubnetV4|SubnetV6> $subnets
     */
    public function is_ip_in_subnets($addressIp, array $subnets) : bool
    {
        $addressIpV4 = null;
        $addressIpV6 = null;

        $statusIp = false
            || $this->type_address_ip_v4($addressIpV4, $addressIp)
            || $this->type_address_ip_v6($addressIpV6, $addressIp);

        if (! $statusIp) {
            throw new LogicException(
                [
                    'The `addressIp` should be valid IPv4 or IPv6 address',
                    $addressIp,
                ]
            );
        }

        $isV4 = (null !== $addressIpV4);
        $isV6 = (null !== $addressIpV6);

        $subnetsV4 = [];
        $subnetsV6 = [];
        foreach ( $subnets as $i => $subnet ) {
            $subnetV4 = null;
            $subnetV6 = null;

            $statusSubnet = false
                || ($isV4 && $this->type_subnet_v4($subnetV4, $subnet))
                || ($isV6 && $this->type_subnet_v4($subnetV6, $subnet));

            if (! $statusSubnet) {
                throw new LogicException(
                    [
                        'Each of `subnets` should be valid IPv4 or IPv6 subnet',
                        $subnet,
                        $i,
                    ]
                );
            }

            if ($isV4) {
                $subnetsV4[ $i ] = $subnetV4;

            } elseif ($isV6) {
                $subnetsV6[ $i ] = $subnetV6;
            }
        }

        if ($isV4 && (0 !== count($subnetsV4))) {
            foreach ( $subnetsV4 as $subnetV4 ) {
                if ($this->is_ip_in_subnet_v4($addressIpV4, $subnetV4)) {
                    return true;
                }
            }

        } elseif ($isV6 && (0 !== count($subnetsV6))) {
            foreach ( $subnetsV6 as $subnetV6 ) {
                if ($this->is_ip_in_subnet_v6($addressIpV6, $subnetV6)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string|AddressIpV4     $addressIpV4
     * @param array<string|SubnetV4> $subnetsV4
     */
    public function is_ip_in_subnets_v4($addressIpV4, array $subnetsV4) : bool
    {
        if (! $this->type_address_ip_v4($addressIpV4Object, $addressIpV4)) {
            throw new LogicException(
                [ 'The `addressIpV4` should be valid IPv4 address', $addressIpV4 ]
            );
        }

        foreach ( $subnetsV4 as $subnetV4 ) {
            if (! $this->type_subnet_v4($subnetV4Object, $subnetV4)) {
                throw new LogicException(
                    [ 'The `subnetV4` should be valid IPv4 subnet', $subnetV4 ]
                );
            }

            if ($this->is_ip_in_subnet_v4($addressIpV4Object, $subnetV4Object)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|AddressIpV6     $addressIpV6
     * @param array<string|SubnetV6> $subnetsV6
     */
    public function is_ip_in_subnets_v6($addressIpV6, array $subnetsV6) : bool
    {
        if (! $this->type_address_ip_v6($addressIpV6Object, $addressIpV6)) {
            throw new LogicException(
                [ 'The `addressIpV6` should be valid IPv6 address', $addressIpV6 ]
            );
        }

        foreach ( $subnetsV6 as $subnetV6 ) {
            if (! $this->type_subnet_v6($subnetV6Object, $subnetV6)) {
                throw new LogicException(
                    [ 'The `subnetV6` should be valid IPv6 subnet', $subnetV6 ]
                );
            }

            if ($this->is_ip_in_subnet_v6($addressIpV6Object, $subnetV6Object)) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param string|AddressIpV4|AddressIpV6 $addressIp
     * @param string|SubnetV4|SubnetV6       $subnet
     */
    public function is_ip_in_subnet($addressIp, $subnet) : bool
    {
        $addressIpV4 = null;
        $addressIpV6 = null;

        $statusIp = false
            || $this->type_address_ip_v4($addressIpV4, $addressIp)
            || $this->type_address_ip_v6($addressIpV6, $addressIp);

        if (! $statusIp) {
            throw new LogicException(
                [
                    'The `addressIp` should be valid IPv4 or IPv6 address',
                    $addressIp,
                ]
            );
        }

        $subnetV4 = null;
        $subnetV6 = null;

        $statusSubnet = false
            || $this->type_subnet_v4($subnetV4, $subnet)
            || $this->type_subnet_v6($subnetV6, $subnet);

        if (! $statusSubnet) {
            throw new LogicException(
                [
                    'The `subnet` should be valid IPv4 or IPv6 subnet',
                    $addressIp,
                ]
            );
        }

        $isV4 = (null !== $addressIpV4) && (null !== $subnetV4);
        $isV6 = (null !== $addressIpV6) && (null !== $subnetV6);

        if ($isV4) {
            $status = $this->is_ip_in_subnet_v4($addressIpV4, $subnetV4);

        } elseif ($isV6) {
            $status = $this->is_ip_in_subnet_v6($addressIpV6, $subnetV6);

        } else {
            $status = false;
        }

        return $status;
    }

    /**
     * @param string|AddressIpV4 $addressIpV4
     * @param string|SubnetV4    $subnetV4
     */
    public function is_ip_in_subnet_v4($addressIpV4, $subnetV4) : bool
    {
        if (! $this->type_address_ip_v4($addressIpV4Object, $addressIpV4)) {
            throw new LogicException(
                [ 'The `addressIpV4` should be valid IPv4 address', $addressIpV4 ]
            );
        }

        if (! $this->type_subnet_v4($subnetV4Object, $subnetV4)) {
            throw new LogicException(
                [ 'The `subnetV4` should be valid IPv4 subnet', $subnetV4 ]
            );
        }

        [ $subnetIpString, $subnetBitsInt ] = explode('/', $subnetV4Object->getValue(), 2);

        $ipLong = ip2long($addressIpV4Object->getValue());
        $subnetIpLong = ip2long($subnetIpString);
        $subnetBitsInt = (int) $subnetBitsInt;

        $subnetBitsIntLong = ~((1 << (32 - $subnetBitsInt)) - 1);

        $status = ($ipLong & $subnetBitsIntLong) === ($subnetIpLong & $subnetBitsIntLong);

        return $status;
    }

    /**
     * @param string|AddressIpV6 $addressIpV6
     * @param string|SubnetV6    $subnetV6
     */
    public function is_ip_in_subnet_v6($addressIpV6, $subnetV6) : bool
    {
        if (! $this->type_address_ip_v6($addressIpV6Object, $addressIpV6)) {
            throw new LogicException(
                [ 'The `addressIpV6` should be valid IPv6 address', $addressIpV6 ]
            );
        }

        if (! $this->type_subnet_v6($subnetV6Object, $subnetV6)) {
            throw new LogicException(
                [ 'The `subnetV6` should be valid IPv6 subnet', $subnetV6 ]
            );
        }

        [ $subnetIpString, $subnetBitsInt ] = explode('/', $subnetV6Object->getValue(), 2);

        $ipBin = inet_pton($addressIpV6Object->getValue());
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


    public function ip_client() : ?string
    {
        $status = $this->type_address_ip($ip, $_SERVER[ 'REMOTE_ADDR' ] ?? null);

        if ($status) {
            return $ip;
        }

        return null;
    }

    public function ip_client_proxy() : ?string
    {
        $status = false
            || $this->type_address_ip($ip, $_SERVER[ 'REMOTE_ADDR' ] ?? null)
            || $this->type_address_ip($ip, $_SERVER[ 'HTTP_CLIENT_IP' ] ?? null)
            || $this->type_address_ip($ip, $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ?? null);

        if ($status) {
            return $ip;
        }

        return null;
    }

    public function ip_localhost() : string
    {
        return '127.0.0.1';
    }


    public function user_agent_client() : ?string
    {
        $_userAgent = Lib::parse()->string_not_empty($_SERVER[ 'HTTP_USER_AGENT' ] ?? null);

        return $_userAgent;
    }

    public function user_agent_browser() : string
    {
        return 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36';
    }

    public function user_agent_php() : string
    {
        return 'PHP ' . phpversion();
    }
}
