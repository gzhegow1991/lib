<?php

namespace Gzhegow\Lib\Modules\Net;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


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


    public static function fromInstance($from, array $refs = [])
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
