<?php

namespace Gzhegow\Lib\Modules\Net;

use Gzhegow\Lib\Modules\Type\Ret;


class AddressIpV4
{
    /**
     * @var string
     */
    protected $value;


    private function __construct()
    {
    }


    public function __toString()
    {
        return $this->value;
    }


    /**
     * @return static|Ret<static>
     */
    public static function fromValid($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromValidString($from)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
            return Ret::ok($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromValidString($from, ?array $fallback = null)
    {
        if (is_string($from)) {
            $instance = new static();
            $instance->value = $from;

            return Ret::ok($fallback, $instance);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be a string', $from ],
            [ __FILE__, __LINE__ ]
        );
    }


    public function getValue() : string
    {
        return $this->value;
    }
}
