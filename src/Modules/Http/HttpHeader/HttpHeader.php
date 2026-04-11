<?php

namespace Gzhegow\Lib\Modules\Http\HttpHeader;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;


class HttpHeader implements ToStringInterface
{
    /**
     * @var string
     */
    protected $raw;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $value;
    /**
     * @var array<string, string>
     */
    protected $headerParams = [];


    public function __toString()
    {
        return $this->toString();
    }


    public function toString(array $options = []) : string
    {
        return $this->getRaw();
    }


    /**
     * @return Ret<static>|static
     */
    public static function from($from, $fb = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromArray($from)->orNull($ret)
            ?? static::fromString($from)->orNull($ret);

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $instance);
    }

    /**
     * @return Ret<static>|static
     */
    public static function fromStatic($from, $fb = null)
    {
        if ( $from instanceof static ) {
            return Ret::ok($fb, $from);
        }

        return Ret::throw(
            $fb,
            [ 'The `from` should be instance of ' . static::class, $from ],
            [ __FILE__, __LINE__ ],
        );
    }

    /**
     * @return Ret<static>|static
     */
    public static function fromArray($from, $fb = null)
    {
        $theStr = Lib::str();
        $theType = Lib::type();

        if ( ! is_array($from) ) {
            return Ret::throw(
                $fb,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ],
            );
        }

        $name = $from['name'] ?? $from[0] ?? null;
        $value = $from['value'] ?? $from[1] ?? null;
        $params = $from['params'] ?? $from[2] ?? [];

        $ret = $theType->string_not_empty($name);

        if ( ! $ret->isOk([ &$nameStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `from[name]` or `from[0]` should be a non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $nameUpper = $theStr->upper($nameStringNotEmpty);

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `from[value]` or `from[1]` should be a non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! is_array($params) ) {
            return Ret::throw(
                $fb,
                [ 'The `from[params]` or `from[2]` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $paramsLower = [];
        foreach ( $params as $param => $value ) {
            $ret = $theType->trim($param);

            if ( ! $ret->isOk([ &$paramStringNotEmpty ]) ) {
                return Ret::throw(
                    $fb,
                    [ "Each of `params` keys should be a non-empty string", $params ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $paramLower = $theStr->lower($paramStringNotEmpty);

            $paramsLower[$paramLower] = $value;
        }

        $instance = new static();
        $instance->name = $nameUpper;
        $instance->value = $valueStringNotEmpty;
        $instance->headerParams = $paramsLower;

        $raw = "{$nameUpper}: {$value}";

        if ( [] !== $paramsLower ) {
            foreach ( $paramsLower as $param => $value ) {
                $raw .= "; {$param}={$value}";
            }
        }

        $instance->raw = $raw;

        return Ret::ok($fb, $instance);
    }

    /**
     * @return Ret<static>|static
     */
    public static function fromString($from, $fb = null)
    {
        $theStr = Lib::str();
        $theType = Lib::type();

        $ret = $theType->string_not_empty($from);

        if ( ! $ret->isOk([ &$fromStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $raw = $fromStringNotEmpty;

        $parts = explode(';', $raw);
        $parts = array_map('trim', $parts);

        $partsFirst = array_shift($parts);

        if ( false === $partsFirst ) {
            return Ret::throw(
                $fb,
                [ 'The `parts[0]` is required', $parts ],
                [ __FILE__, __LINE__ ]
            );
        }

        $partsNameValue = explode(':', $partsFirst);
        $partsNameValue = array_map('trim', $partsNameValue);

        [ $name, $value ] = $partsNameValue;

        $ret = $theType->string_not_empty($name);

        if ( ! $ret->isOk([ &$nameStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `partsFirst[0]` should be a non-empty string', $partsFirst ],
                [ __FILE__, __LINE__ ]
            );
        }

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                [ 'The `partsFirst[1]` should be a non-empty string', $partsFirst ],
                [ __FILE__, __LINE__ ]
            );
        }

        $paramsLower = [];
        foreach ( $parts as $i => $part ) {
            [ $param, $value ] = explode('=', $part) + [ '', true ];

            $ret = $theType->trim($param);

            if ( ! $ret->isOk([ &$paramTrim ]) ) {
                return Ret::throw(
                    $fb,
                    [ "The `parts[{$i}][0]` should be a non-empty string", $parts ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $paramLower = $theStr->lower($paramTrim);

            $paramsLower[$paramLower] = $value;
        }

        $nameUpper = $theStr->upper($nameStringNotEmpty);

        $instance = new static();
        $instance->raw = $raw;
        $instance->name = $nameUpper;
        $instance->value = $valueStringNotEmpty;
        $instance->headerParams = $paramsLower;

        return Ret::ok($fb, $instance);
    }



    public function getRaw() : string
    {
        return $this->raw;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function getParams() : array
    {
        return $this->headerParams;
    }
}
