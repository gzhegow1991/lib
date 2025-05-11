<?php

namespace Gzhegow\Lib\Modules\Net;

use Gzhegow\Lib\Lib;
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
     * @return static|bool|null
     */
    public static function fromValid($from, $ctx = null)
    {
        $ctxCur = Result::parse();

        $instance = null
            ?? static::fromStatic($from, $ctxCur)
            ?? static::fromValidString($from, $ctxCur);

        if ($ctxCur->isErr()) {
            return Result::err($ctx, $ctxCur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, $ctx = null)
    {
        if ($from instanceof static) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` must be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromValidString($from, $ctx = null)
    {
        if (is_string($from)) {
            $instance = new static();
            $instance->value = $from;

            return Result::ok($ctx, $instance);
        }

        return Result::err(
            $ctx,
            [ 'The `from` must be string', $from ],
            [ __FILE__, __LINE__ ]
        );
    }


    public function getValue() : string
    {
        return $this->value;
    }
}
