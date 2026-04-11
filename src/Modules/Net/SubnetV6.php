<?php

namespace Gzhegow\Lib\Modules\Net;

use Gzhegow\Lib\Modules\Type\Ret;


class SubnetV6
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
     * @return Ret<static>|static
     */
    public static function fromValid($from, $fb = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromValidString($from)->orNull($ret);

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
            [ 'The `from` should be an instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<static>|static
     */
    public static function fromValidString($from, $fb = null)
    {
        if ( is_string($from) ) {
            $instance = new static();
            $instance->value = $from;

            return Ret::ok($fb, $instance);
        }

        return Ret::throw(
            $fb,
            [ 'The `from` should be a string', $from ],
            [ __FILE__, __LINE__ ]
        );
    }


    public function getValue() : string
    {
        return $this->value;
    }
}
