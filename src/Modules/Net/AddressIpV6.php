<?php

namespace Gzhegow\Lib\Modules\Net;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Php\Result\Result;


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
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromValid($from, $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? static::fromStatic($from, $retCur)
            ?? static::fromValidString($from, $retCur);

        if ($retCur->isErr()) {
            return Result::err($ret, $retCur);
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromStatic($from, $ret = null)
    {
        if ($from instanceof static) {
            return Result::ok($ret, $from);
        }

        return Result::err(
            $ret,
            [ 'The `from` should be an instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromValidString($from, $ret = null)
    {
        if (is_string($from)) {
            $instance = new static();
            $instance->value = $from;

            return Result::ok($ret, $instance);
        }

        return Result::err(
            $ret,
            [ 'The `from` should be a string', $from ],
            [ __FILE__, __LINE__ ]
        );
    }


    public function getValue() : string
    {
        return $this->value;
    }
}
