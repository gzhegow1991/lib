<?php

namespace Gzhegow\Lib\Modules\Type;

class Number
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
     * @var string
     */
    protected $exp;

    /**
     * @var int
     */
    protected $scale;


    public function __construct(
        $original,
        //
        string $sign,
        string $int,
        string $frac,
        string $exp,
        //
        int $scale
    )
    {
        $this->original = $original;

        $this->sign = $sign;
        $this->int = $int;
        $this->frac = $frac;
        $this->exp = $exp;

        $this->scale = $scale;

        $this->value = "{$this->sign}{$this->int}{$this->frac}{$this->exp}";
    }


    public function __toString()
    {
        return $this->getValue();
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
        return "{$this->int}";
    }
}
