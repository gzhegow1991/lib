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
    public function __construct()
    {
        if ( ! extension_loaded('filter') ) {
            throw new ExtensionException(
                [ 'Missing PHP extension: filter' ]
            );
        }
    }


    /**
     * @return Ret<AddressIpV4|AddressIpV6>
     */
    public function type_address_ip($value)
    {
        $theType = Lib::type();

        if ( ! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueFiltered = filter_var($valueStringNotEmpty, FILTER_VALIDATE_IP);
        if ( false === $valueFiltered ) {
            return Ret::err(
                [ 'The `value` should be valid address IP', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueFiltered);
    }

    /**
     * @return Ret<AddressIpV4>
     */
    public function type_address_ip_v4($value)
    {
        if ( $value instanceof AddressIpV4 ) {
            return Ret::val($value);
        }

        $theType = Lib::type();

        if ( ! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueFiltered = filter_var($valueStringNotEmpty, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if ( false === $valueFiltered ) {
            return Ret::err(
                [ 'The `value` should be valid address IP v4', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $valueAddressIpV4 = AddressIpV6::fromValidString($valueFiltered)->orThrow();

        return Ret::val($valueAddressIpV4);
    }

    /**
     * @return Ret<AddressIpV6>
     */
    public function type_address_ip_v6($value)
    {
        if ( $value instanceof AddressIpV6 ) {
            return Ret::val($value);
        }

        $theType = Lib::type();

        if ( ! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueFiltered = filter_var($valueStringNotEmpty, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        if ( false === $valueFiltered ) {
            return Ret::err(
                [ 'The `value` should be valid address IP v6', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $valueAddressIpV6 = AddressIpV6::fromValidString($valueFiltered)->orThrow();

        return Ret::val($valueAddressIpV6);
    }


    /**
     * @return Ret<string>
     */
    public function type_address_mac($value)
    {
        $theType = Lib::type();

        if ( ! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
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
            return Ret::err(
                [ 'The `value` should be valid address MAC', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueStringNotEmpty);
    }


    /**
     * @return Ret<SubnetV4|SubnetV6>
     */
    public function type_subnet($value, ?string $ipFallback = null)
    {
        $ret = Ret::new();

        $valueSubnet = null
            ?? $this->type_subnet_v4($value, $ipFallback)->orNull($ret)
            ?? $this->type_subnet_v6($value, $ipFallback)->orNull($ret);

        if ( $ret->isFail() ) {
            return Ret::err([ __FILE__, __LINE__ ]);
        }

        return Ret::val($valueSubnet);
    }

    /**
     * @return Ret<SubnetV4>
     */
    public function type_subnet_v4($value, ?string $ipFallback = null)
    {
        if ( $value instanceof SubnetV4 ) {
            return Ret::val($value);
        }

        $theType = Lib::type();

        if ( ! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
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
                return Ret::err(
                    [ 'The `value` subnet part should be non-empty string', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $ret = Ret::new();

            $subnetInt = null
                ?? $this->type_subnet_v4_iplike($subnetPart)->orNull($ret)
                ?? $theType->numeric_int($subnetPart)->orNull($ret);

            if ( $ret->isFail() ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( ($subnetInt < 0) || ($subnetInt > 32) ) {
                return Ret::err(
                    [ 'The `value` subnet integer should be between 0 and 32', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( $addressPart === '' ) {
                $addressIpString = $ipFallback;

            } else {
                if ( ! $this->type_address_ip_v4($addressPart)->isOk([ &$addressIpString, &$ret ]) ) {
                    return Ret::err(
                        $ret,
                        [ __FILE__, __LINE__ ]
                    );
                }
            }

        } else {
            if ( $addressPart === '' ) {
                return Ret::err(
                    [ 'The `value` address part should be non empty string', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( $this->type_subnet_v4_iplike($addressPart)->isOk([ &$subnetInt, &$ret ]) ) {
                if ( ($subnetInt < 0) || ($subnetInt > 32) ) {
                    return Ret::err(
                        [ 'The `value` subnet integer should be between 0 and 32', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }

                $addressIpString = $ipFallback;

            } elseif ( $this->type_address_ip_v4($addressPart)->isOk([ &$addressIpString ]) ) {
                $subnetInt = 32;
            }
        }

        if ( ! filter_var($addressIpString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
            return Ret::err(
                [ 'The `ipFallback` should be valid IP v4 address', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( true
            && (null !== $addressIpString)
            && (null !== $subnetInt)
        ) {
            $subnetV4 = "{$addressIpString}/{$subnetInt}";

            $valueSubnetV4 = SubnetV4::fromValidString($subnetV4)->orThrow();

            return Ret::val($valueSubnetV4);
        }

        return Ret::err(
            [ 'The `value` should be valid subnet IP v4', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<SubnetV6>
     */
    public function type_subnet_v6($value, ?string $ipFallback = null)
    {
        if ( $value instanceof SubnetV6 ) {
            return Ret::val($value);
        }

        $theType = Lib::type();

        if ( ! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        [ $addressPart, $subnetPart ] = explode('/', $valueStringNotEmpty, 2) + [ '', null ];

        if ( null === $subnetPart ) {
            return Ret::err(
                [ 'The `value` should contain slash because it is only valid IP v6 subnet', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( '' === $subnetPart ) {
            return Ret::err(
                [ 'The `value` should contain valid subnet after slash', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! $theType->numeric_int($subnetPart)->isOk([ &$subnetNumericInt, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ($subnetNumericInt < 0) || ($subnetNumericInt > 128) ) {
            return Ret::err(
                [ 'The `value` subnet integer should be between 0 and 128', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $addressIpString = null;

        if ( $addressPart === '' ) {
            $addressIpString = $ipFallback;

            if ( ! filter_var($addressIpString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
                return Ret::err(
                    [ 'The `ipFallback` should be valid IP v6 address', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

        } else {
            if ( ! $this->type_address_ip_v6($addressPart)->isOk([ &$addressIpString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        if ( $addressIpString !== null && $subnetNumericInt !== null ) {
            $subnetV6 = "{$addressIpString}/{$subnetNumericInt}";

            $valueSubnetV6 = SubnetV6::fromValidString($subnetV6)->orThrow();

            return Ret::val($valueSubnetV6);
        }

        return Ret::err(
            [ 'The `value` should be valid subnet IP v6', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<int>
     */
    protected function type_subnet_v4_iplike($value)
    {
        $theType = Lib::type();

        if ( ! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $ipStringNotEmpty = $valueStringNotEmpty;

        if ( false === strpos($ipStringNotEmpty, '.') ) {
            return Ret::err(
                [ 'The `value` should contain at least one dot', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! filter_var($ipStringNotEmpty, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
            return Ret::err(
                [ 'The `value` should be valid address IP v4', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ipLong = ip2long($ipStringNotEmpty);
        if ( false === $ipLong ) {
            return Ret::err(
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

        return Ret::val($subnetInt);
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
            || $this->type_address_ip_v4($addressIp)->isOk([ &$addressIpV4 ])
            || $this->type_address_ip_v6($addressIp)->isOk([ &$addressIpV6 ]);

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
                || ($isV4 && $this->type_subnet_v4($subnet)->isOk([ &$subnetV4 ]))
                || ($isV6 && $this->type_subnet_v4($subnet)->isOk([ &$subnetV6 ]));

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
        if ( ! $this->type_address_ip_v4($addressIpV4)->isOk([ &$addressIpV4Object ]) ) {
            return false;
        }

        foreach ( $subnetsV4 as $subnetV4 ) {
            $subnetV4Object = $this->type_subnet_v4($subnetV4)->orThrow();

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
        if ( ! $this->type_address_ip_v6($addressIpV6)->isOk([ &$addressIpV6Object ]) ) {
            return false;
        }

        foreach ( $subnetsV6 as $subnetV6 ) {
            $subnetV6Object = $this->type_subnet_v6($subnetV6)->orThrow();

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
            || $this->type_address_ip_v4($addressIp)->isOk([ &$addressIpV4 ])
            || $this->type_address_ip_v6($addressIp)->isOk([ &$addressIpV6 ]);

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
            || $this->type_subnet_v4($subnet)->isOk([ &$subnetV4 ])
            || $this->type_subnet_v6($subnet)->isOk([ &$subnetV6 ]);

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
        if ( ! $this->type_address_ip_v4($addressIpV4)->isOk([ &$addressIpV4Object ]) ) {
            return false;
        }

        $subnetV4Object = $this->type_subnet_v4($subnetV4)->orThrow();

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
        if ( ! $this->type_address_ip_v6($addressIpV6)->isOk([ &$addressIpV6Object ]) ) {
            return false;
        }

        $subnetV6Object = $this->type_subnet_v6($subnetV6)->orThrow();

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


    public function ip_client() : string
    {
        return $this->type_address_ip($_SERVER['REMOTE_ADDR'] ?? null)->orThrow();
    }

    public function ip_client_proxy() : string
    {
        $ip = null
            ?? $_SERVER['REMOTE_ADDR']
            ?? $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? null;

        return $this->type_address_ip($ip)->orThrow();
    }

    public function ip_localhost() : string
    {
        return '127.0.0.1';
    }


    public function user_agent_client() : ?string
    {
        $theType = Lib::type();

        return $theType->string_not_empty($_SERVER['HTTP_USER_AGENT'] ?? null)->orThrow();
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
