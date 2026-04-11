<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Net\SubnetV4;
use Gzhegow\Lib\Modules\Net\SubnetV6;
use Gzhegow\Lib\Modules\Net\AddressIpV4;
use Gzhegow\Lib\Modules\Net\AddressIpV6;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class NetModule
{
    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        if ( ! extension_loaded('filter') ) {
            throw new ExtensionException(
                [ 'Missing PHP extension: filter' ]
            );
        }

        return $this;
    }


    /**
     * @return Ret<AddressIpV4|AddressIpV6>|AddressIpV4|AddressIpV6
     */
    public function type_address_ip($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueFiltered = filter_var($valueStringNotEmpty, FILTER_VALIDATE_IP);
        if ( false === $valueFiltered ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid address IP', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueFiltered);
    }

    /**
     * @return Ret<AddressIpV4>|AddressIpV4
     */
    public function type_address_ip_v4($fb, $value)
    {
        if ( $value instanceof AddressIpV4 ) {
            return Ret::ok($fb, $value);
        }

        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueFiltered = filter_var($valueStringNotEmpty, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if ( false === $valueFiltered ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid address IP v4', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $valueAddressIpV4 = AddressIpV4::fromValidString($valueFiltered)->orThrow();

        return Ret::ok($fb, $valueAddressIpV4);
    }

    /**
     * @return Ret<AddressIpV6>|AddressIpV6
     */
    public function type_address_ip_v6($fb, $value)
    {
        if ( $value instanceof AddressIpV6 ) {
            return Ret::ok($fb, $value);
        }

        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueFiltered = filter_var($valueStringNotEmpty, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        if ( false === $valueFiltered ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid address IP v6', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $valueAddressIpV6 = AddressIpV6::fromValidString($valueFiltered)->orThrow();

        return Ret::ok($fb, $valueAddressIpV6);
    }


    /**
     * @return Ret<string>|string
     */
    public function type_address_mac($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $status = preg_match(
            '/^([0-9A-Fa-f]{2}([-:])){5}([0-9A-Fa-f]{2})$/',
            $valueStringNotEmpty
        );

        if ( false
            || (false === $status)
            || (0 === $status)
        ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid address MAC', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueStringNotEmpty);
    }


    /**
     * @return Ret<SubnetV4|SubnetV6>|SubnetV4|SubnetV6
     */
    public function type_subnet($fb, $value, ?string $ipFallback = null)
    {
        $ret = Ret::new();

        $statusSubnet = false
            || $this->type_subnet_v4($ret, $value, $ipFallback)
            || $this->type_subnet_v6($ret, $value, $ipFallback);

        if ( ! $ret->isOk([ &$valueSubnet ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueSubnet);
    }

    /**
     * @return Ret<SubnetV4>|SubnetV4
     */
    public function type_subnet_v4($fb, $value, ?string $ipFallback = null)
    {
        if ( $value instanceof SubnetV4 ) {
            return Ret::ok($fb, $value);
        }

        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        [ $addressPart, $subnetPart ] = explode('/', $valueStringNotEmpty, 2) + [ '', null ];

        $hasSlash = ($subnetPart !== null);

        $addressIpString = null;
        $subnetInt = null;

        if ( $hasSlash ) {
            if ( '' === $subnetPart ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` subnet part should be non-empty string', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $ret = Ret::new();

            $subnetInt = null
                ?? $this->type_subnet_v4_iplike(null, $subnetPart)->orNull($ret)
                ?? $theType->numeric_int($subnetPart)->orNull($ret);

            if ( ! $ret->isOk() ) {
                return Ret::throw(
                    $fb,
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( ($subnetInt < 0) || ($subnetInt > 32) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` subnet integer should be between 0 and 32', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( $addressPart === '' ) {
                $addressIpString = $ipFallback;

            } else {
                $ret = $this->type_address_ip_v4(null, $addressPart);

                if ( ! $ret->isOk([ &$addressIpString ]) ) {
                    return Ret::throw(
                        $fb,
                        $ret,
                        [ __FILE__, __LINE__ ]
                    );
                }
            }

        } else {
            if ( $addressPart === '' ) {
                return Ret::throw(
                    $fb,
                    [ 'The `value` address part should be non empty string', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $ret = $this->type_subnet_v4_iplike(null, $addressPart);

            if ( $ret->isOk([ &$subnetInt ]) ) {
                if ( ($subnetInt < 0) || ($subnetInt > 32) ) {
                    return Ret::throw(
                        $fb,
                        [ 'The `value` subnet integer should be between 0 and 32', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }

                $addressIpString = $ipFallback;

            } else {
                $ret = $this->type_address_ip_v4(null, $addressPart);

                if ( $ret->isOk([ &$addressIpString ]) ) {
                    $subnetInt = 32;
                }
            }
        }

        if ( ! filter_var($addressIpString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
            return Ret::throw(
                $fb,
                [ 'The `ipFallback` should be valid IP v4 address', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! (true
            && (null !== $addressIpString)
            && (null !== $subnetInt)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid subnet IP v4', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $subnetV4 = "{$addressIpString}/{$subnetInt}";

        $valueSubnetV4 = SubnetV4::fromValidString($subnetV4)->orThrow();

        return Ret::ok($fb, $valueSubnetV4);
    }

    /**
     * @return Ret<SubnetV6>|SubnetV6
     */
    public function type_subnet_v6($fb, $value, ?string $ipFallback = null)
    {
        if ( $value instanceof SubnetV6 ) {
            return Ret::ok($fb, $value);
        }

        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        [ $addressPart, $subnetPart ] = explode('/', $valueStringNotEmpty, 2) + [ '', null ];

        if ( null === $subnetPart ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should contain slash because it is only valid IP v6 subnet', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( '' === $subnetPart ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should contain valid subnet after slash', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $theType->numeric_int($subnetPart);

        if ( ! $ret->isOk([ &$subnetNumericInt ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ($subnetNumericInt < 0) || ($subnetNumericInt > 128) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` subnet integer should be between 0 and 128', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $addressIpString = null;

        if ( $addressPart === '' ) {
            $addressIpString = $ipFallback;

            if ( ! filter_var($addressIpString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
                return Ret::throw(
                    $fb,
                    [ 'The `ipFallback` should be valid IP v6 address', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

        } else {
            $ret = $this->type_address_ip_v6(null, $addressPart);

            if ( ! $ret->isOk([ &$addressIpString ]) ) {
                return Ret::throw(
                    $fb,
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        if ( ! (true
            && (null !== $addressIpString)
            && (null !== $subnetNumericInt)
        ) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid subnet IP v6', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $subnetV6 = "{$addressIpString}/{$subnetNumericInt}";

        $valueSubnetV6 = SubnetV6::fromValidString($subnetV6)->orThrow();

        return Ret::ok($fb, $valueSubnetV6);
    }

    /**
     * @return Ret<int>|int
     */
    protected function type_subnet_v4_iplike($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ipStringNotEmpty = $valueStringNotEmpty;

        if ( false === strpos($ipStringNotEmpty, '.') ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should contain at least one dot', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! filter_var($ipStringNotEmpty, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid address IP v4', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ipLong = ip2long($ipStringNotEmpty);

        if ( false === $ipLong ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid address IP v4', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $inverted = ~$ipLong & 0xFFFFFFFF;
        $isSubnetMask = (($inverted + 1) & $inverted) === 0;

        if ( $isSubnetMask ) {
            $subnetInt = $ipLong;
            $subnetInt = decbin($subnetInt);
            $subnetInt = rtrim($subnetInt, '0');
            $subnetInt = strlen($subnetInt);

        } else {
            $subnetInt = 32;
        }

        return Ret::ok($fb, $subnetInt);
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
            || $this->type_address_ip_v4(null, $addressIp)->isOk([ &$addressIpV4 ])
            || $this->type_address_ip_v6(null, $addressIp)->isOk([ &$addressIpV6 ]);

        if ( ! $statusIp ) {
            throw new LogicException(
                [
                    'The `addressIp` should be a valid IPv4 or IPv6 address',
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
                || ($isV4 && $this->type_subnet_v4(null, $subnet)->isOk([ &$subnetV4 ]))
                || ($isV6 && $this->type_subnet_v4(null, $subnet)->isOk([ &$subnetV6 ]));

            if ( ! $statusSubnet ) {
                throw new LogicException(
                    [
                        'Each of `subnets` should be a valid IPv4 or IPv6 subnet',
                        $subnet,
                        $i,
                    ]
                );
            }

            if ( $isV4 ) {
                $subnetsV4[$i] = $subnetV4;

            } elseif ( $isV6 ) {
                $subnetsV6[$i] = $subnetV6;
            }
        }

        if ( $isV4 && ([] !== $subnetsV4) ) {
            foreach ( $subnetsV4 as $subnetV4 ) {
                if ( $this->is_ip_in_subnet_v4($addressIpV4, $subnetV4) ) {
                    return true;
                }
            }

        } elseif ( $isV6 && ([] !== $subnetsV6) ) {
            foreach ( $subnetsV6 as $subnetV6 ) {
                if ( $this->is_ip_in_subnet_v6($addressIpV6, $subnetV6) ) {
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
        $ret = $this->type_address_ip_v4(null, $addressIpV4);

        if ( ! $ret->isOk([ &$addressIpV4Object ]) ) {
            return false;
        }

        foreach ( $subnetsV4 as $subnetV4 ) {
            $subnetV4Object = $this->type_subnet_v4([], $subnetV4);

            if ( $this->is_ip_in_subnet_v4($addressIpV4Object, $subnetV4Object) ) {
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
        $ret = $this->type_address_ip_v6(null, $addressIpV6);

        if ( ! $ret->isOk([ &$addressIpV6Object ]) ) {
            return false;
        }

        foreach ( $subnetsV6 as $subnetV6 ) {
            $subnetV6Object = $this->type_subnet_v6([], $subnetV6);

            if ( $this->is_ip_in_subnet_v6($addressIpV6Object, $subnetV6Object) ) {
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
            || $this->type_address_ip_v4(null, $addressIp)->isOk([ &$addressIpV4 ])
            || $this->type_address_ip_v6(null, $addressIp)->isOk([ &$addressIpV6 ]);

        if ( ! $statusIp ) {
            throw new LogicException(
                [
                    'The `addressIp` should be a valid IPv4 or IPv6 address',
                    $addressIp,
                ]
            );
        }

        $subnetV4 = null;
        $subnetV6 = null;

        $statusSubnet = false
            || $this->type_subnet_v4(null, $subnet)->isOk([ &$subnetV4 ])
            || $this->type_subnet_v6(null, $subnet)->isOk([ &$subnetV6 ]);

        if ( ! $statusSubnet ) {
            throw new LogicException(
                [
                    'The `subnet` should be a valid IPv4 or IPv6 subnet',
                    $addressIp,
                ]
            );
        }

        $isV4 = (null !== $addressIpV4) && (null !== $subnetV4);
        $isV6 = (null !== $addressIpV6) && (null !== $subnetV6);

        if ( $isV4 ) {
            $status = $this->is_ip_in_subnet_v4($addressIpV4, $subnetV4);

        } elseif ( $isV6 ) {
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
        $ret = $this->type_address_ip_v4(null, $addressIpV4);

        if ( ! $ret->isOk([ &$addressIpV4Object ]) ) {
            return false;
        }

        $subnetV4Object = $this->type_subnet_v4([], $subnetV4);

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
        $ret = $this->type_address_ip_v6(null, $addressIpV6);

        if ( ! $ret->isOk([ &$addressIpV6Object ]) ) {
            return false;
        }

        $subnetV6Object = $this->type_subnet_v6([], $subnetV6);

        [ $subnetIpString, $subnetBitsInt ] = explode('/', $subnetV6Object->getValue(), 2);

        $ipBin = inet_pton($addressIpV6Object->getValue());
        $subnetIpBin = inet_pton($subnetIpString);
        $subnetBitsInt = (int) $subnetBitsInt;

        $ipBits = unpack('H*', $ipBin)[1];
        $subnetIpBits = unpack('H*', $subnetIpBin)[1];

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

        $status = substr($ipBin, 0, $subnetBitsInt)
            === substr($subnetIpBin, 0, $subnetBitsInt);

        return $status;
    }


    public function the_ip_any() : string
    {
        return '0.0.0.0';
    }

    public function the_ip_localhost() : string
    {
        return '127.0.0.1';
    }

    public function ip_client() : string
    {
        return $this->type_address_ip([], $_SERVER['REMOTE_ADDR'] ?? null);
    }

    public function ip_client_proxy() : string
    {
        $ip = null
            ?? $_SERVER['REMOTE_ADDR']
            ?? $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? null;

        return $this->type_address_ip([], $ip);
    }


    public function the_user_agent_browser() : string
    {
        return 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36';
    }

    public function the_user_agent_php() : string
    {
        return 'PHP ' . phpversion();
    }

    public function user_agent_client() : ?string
    {
        $theType = Lib::type();

        return $theType->string_not_empty($_SERVER['HTTP_USER_AGENT'] ?? null)->orThrow();
    }
}
