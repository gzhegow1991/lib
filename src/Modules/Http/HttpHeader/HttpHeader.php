<?php

namespace Gzhegow\Lib\Modules\Http\HttpHeader;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Result;
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
    protected $params = [];


    public function __toString()
    {
        return $this->toString();
    }


    public function toString(array $options = []) : string
    {
        return $this->getRaw();
    }


    /**
     * @return static|bool|null
     */
    public static function from($from, $ret = null)
    {
        $cur = Result::asValueNull();

        $instance = null
            ?? static::fromStatic($from, $cur)
            ?? static::fromArray($from, $cur)
            ?? static::fromString($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ret, $cur);
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, $ret = null)
    {
        if ($from instanceof static) {
            return Result::ok($ret, $from);
        }

        return Result::err(
            $ret,
            [ 'The `from` should be instance of ' . static::class, $from ],
            [ __FILE__, __LINE__ ],
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromArray($from, $ret = null)
    {
        $theStr = Lib::str();
        $theType = Lib::type();

        if (! is_array($from)) {
            return Result::err(
                $ret,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ],
            );
        }

        $name = $from[ 'name' ] ?? $from[ 0 ] ?? null;
        $value = $from[ 'value' ] ?? $from[ 1 ] ?? null;
        $params = $from[ 'params' ] ?? $from[ 2 ] ?? [];

        if (! $theType->string_not_empty($nameString, $name)) {
            return Result::err(
                $ret,
                [ 'The `from[name]` or `from[0]` should be a non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $nameUpper = $theStr->upper($nameString);

        if (! $theType->string_not_empty($valueString, $value)) {
            return Result::err(
                $ret,
                [ 'The `from[value]` or `from[1]` should be a non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! is_array($params)) {
            return Result::err(
                $ret,
                [ 'The `from[params]` or `from[2]` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $paramsLower = [];
        foreach ( $params as $param => $value ) {
            if (! $theType->trim($paramString, $param)) {
                return Result::err(
                    $ret,
                    [ "Each of `params` keys should be a non-empty string", $params ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $paramLower = $theStr->lower($paramString);

            $paramsLower[ $paramLower ] = $value;
        }

        $instance = new static();
        $instance->name = $nameUpper;
        $instance->value = $value;
        $instance->params = $paramsLower;

        $raw = "{$nameUpper}: {$value}";

        if ([] !== $paramsLower) {
            foreach ( $paramsLower as $param => $value ) {
                $raw .= "; {$param}={$value}";
            }
        }

        $instance->raw = $raw;

        return Result::ok($ret, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromString($from, $ret = null)
    {
        $theStr = Lib::str();
        $theType = Lib::type();

        if (! $theType->string_not_empty($fromString, $from)) {
            return Result::err(
                $ret,
                [ 'The `from` should be string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $raw = $fromString;

        $parts = array_map('trim', explode(';', $raw));

        $partsFirst = array_shift($parts);

        if (false === $partsFirst) {
            return Result::err(
                $ret,
                [ 'The `parts[0]` is required', $parts ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ $name, $value ] = array_map('trim', explode(':', $partsFirst));

        if (! $theType->string_not_empty($nameString, $name)) {
            return Result::err(
                $ret,
                [ 'The `partsFirst[0]` should be a non-empty string', $partsFirst ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->string_not_empty($valueString, $value)) {
            return Result::err(
                $ret,
                [ 'The `partsFirst[1]` should be a non-empty string', $partsFirst ],
                [ __FILE__, __LINE__ ]
            );
        }

        $paramsLower = [];
        foreach ( $parts as $i => $part ) {
            [ $param, $value ] = explode('=', $part) + [ '', true ];

            if (! $theType->trim($paramString, $param)) {
                return Result::err(
                    $ret,
                    [ "The `parts[{$i}][0]` should be a non-empty string", $parts ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $paramLower = $theStr->lower($paramString);

            $paramsLower[ $paramLower ] = $value;
        }

        $nameUpper = $theStr->upper($nameString);

        $instance = new static();
        $instance->raw = $raw;
        $instance->name = $nameUpper;
        $instance->value = $valueString;
        $instance->params = $paramsLower;

        return Result::ok($ret, $instance);
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
        return $this->params;
    }
}
