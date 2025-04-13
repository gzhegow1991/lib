<?php

namespace Gzhegow\Lib\Modules\Type;

use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToFloatInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIntegerInterface;


class Number implements
    ToIntegerInterface,
    ToFloatInterface,
    ToStringInterface
{
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
     * @var string
     */
    protected $exp;

    /**
     * @var int
     */
    protected $scale;

    /**
     * @var string
     */
    protected $value;


    private function __construct()
    {
    }


    public static function fromValid(array $valid)
    {
        $instance = new static();

        $instance->original = $valid[ 'original' ];

        $instance->sign = $valid[ 'sign' ];
        $instance->int = $valid[ 'int' ];
        $instance->frac = $valid[ 'frac' ];
        $instance->exp = $valid[ 'exp' ];

        $instance->scale = $valid[ 'scale' ];

        $instance->value = "{$instance->sign}{$instance->int}{$instance->frac}{$instance->exp}";

        return $instance;
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


    public function __toString()
    {
        return $this->value;
    }


    public function getOriginal() // : mixed
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

    public function getExp() : string
    {
        return $this->exp;
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
        return "{$this->int}{$this->frac}{$this->exp}";
    }

    public function getValueInteger() : string
    {
        return "{$this->sign}{$this->int}";
    }

    public function getValueAbsoluteInteger() : string
    {
        return $this->int;
    }
}
