<?php

namespace Gzhegow\Lib\Modules\Net;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


class AddressIpV6
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
     * @return static|bool|null
     */
    public static function fromValid($from, array $refs = [])
    {
        $withErrors = array_key_exists(0, $refs);

        $refs[ 0 ] = $refs[ 0 ] ?? null;

        $instance = null
            ?? static::fromStatic($from, $refs)
            ?? static::fromValidString($from, $refs);

        if (! $withErrors) {
            if (null === $instance) {
                throw $refs[ 0 ];
            }
        }

        return $instance;
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, array $refs = [])
    {
        if ($from instanceof static) {
            return Lib::refsResult($refs, $from);
        }

        return Lib::refsError(
            $refs,
            new LogicException(
                [ 'The `from` must be instance of: ' . static::class, $from ]
            )
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromValidString($from, array $refs = [])
    {
        if (is_string($from)) {
            $instance = new static();
            $instance->value = $from;

            return Lib::refsResult($refs, $instance);
        }

        return Lib::refsError(
            $refs,
            new LogicException(
                [ 'The `from` must be string', $from ]
            )
        );
    }


    public function getValue() : string
    {
        return $this->value;
    }
}
