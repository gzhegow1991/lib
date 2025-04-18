<?php

namespace Gzhegow\Lib\Modules\Bcmath;


class Bcnumber
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


    public function __construct(
        $original,
        //
        string $sign,
        string $int,
        string $frac,
        //
        int $scale
    )
    {
        $this->original = $original;

        $this->sign = $sign;
        $this->int = $int;
        $this->frac = $frac;

        $this->scale = $scale;

        $this->value = "{$this->sign}{$this->int}{$this->frac}";
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
