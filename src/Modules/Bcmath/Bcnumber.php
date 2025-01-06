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
    protected $minus;
    /**
     * @var string
     */
    protected $integerPart;
    /**
     * @var string
     */
    protected $fractionalPart;

    /**
     * @var int
     */
    protected $scale;


    public function __construct(
        $original,
        //
        string $minus,
        string $integerPart,
        string $fractionalPart,
        //
        int $scale
    )
    {
        $this->original = $original;

        $this->minus = $minus;
        $this->integerPart = $integerPart;
        $this->fractionalPart = $fractionalPart;

        $this->scale = $scale;

        $this->value = "{$this->minus}{$this->integerPart}{$this->fractionalPart}";
    }


    public function __toString()
    {
        return $this->getValue();
    }


    public function getOriginal() // : mixed
    {
        return $this->original;
    }


    public function getMinus() : string
    {
        return $this->minus;
    }

    public function getIntegerPart() : string
    {
        return $this->integerPart;
    }

    public function getFractionalPart() : string
    {
        return $this->fractionalPart;
    }


    public function getScale() : int
    {
        return $this->scale;
    }


    public function getValue() : string
    {
        return $this->value;
    }

    public function getAbsolute() : string
    {
        return "{$this->integerPart}{$this->fractionalPart}";
    }

    public function getInteger() : string
    {
        return "{$this->minus}{$this->integerPart}";
    }
}
