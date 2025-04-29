<?php

namespace Gzhegow\Lib\Modules\Bcmath;

use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToFloatInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIntegerInterface;


class Bcnumber implements
    ToIntegerInterface,
    ToFloatInterface,
    ToStringInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var mixed
     */
    protected $original;

    /**
     * @var string
     */
    protected $sign;
    /**
     * @var string
     */
    protected $int;
    /**
     * @var string
     */
    protected $frac;

    /**
     * @var int
     */
    protected $scale;


    private function __construct()
    {
    }


    public function __toString()
    {
        return $this->getValue();
    }


    /**
     * @return static|bool|null
     */
    public static function fromValid($from, $ctx = null)
    {
        $ctxCur = Result::nullchain();

        $instance = null
            ?? static::fromStatic($from, $ctxCur)
            ?? static::fromValidArray($from, $ctxCur);

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
    public static function fromValidArray($from, $ctx = null)
    {
        if (! is_array($from)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();

        [
            'original' => $instance->original,
            'sign'     => $instance->sign,
            'int'      => $instance->int,
            'frac'     => $instance->frac,
            'scale'    => $instance->scale,
        ] = $from;

        $instance->value = "{$instance->sign}{$instance->int}{$instance->frac}";

        return Result::ok(
            $ctx,
            $instance
        );
    }


    public function isInteger() : bool
    {
        return $this->value === $this->getValueInteger();
    }


    public function toInteger(array $options = []) : int
    {
        if (! $this->isInteger()) {
            throw new RuntimeException(
                [ 'This number cannot be converted to an integer', $this ]
            );
        }

        return (int) $this->value;
    }

    public function toFloat(array $options = []) : float
    {
        return (float) $this->value;
    }

    public function toString(array $options = []) : string
    {
        return $this->value;
    }


    public function getOriginal()
    {
        return $this->original;
    }


    public function getSign() : string
    {
        return $this->sign;
    }

    public function getInt() : string
    {
        return $this->int;
    }

    public function getFrac() : string
    {
        return $this->frac;
    }


    public function getScale() : int
    {
        return $this->scale;
    }


    public function getValue() : string
    {
        return $this->value;
    }

    public function getValueAbsolute() : string
    {
        return "{$this->int}{$this->frac}";
    }

    public function getValueInteger() : string
    {
        return "{$this->sign}{$this->int}";
    }

    public function getValueAbsoluteInteger() : string
    {
        return "{$this->int}";
    }
}
