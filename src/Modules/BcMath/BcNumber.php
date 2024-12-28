<?php

namespace Gzhegow\Lib\Modules\BcMath;


class BcNumber
{
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
    protected $integral;
    /**
     * @var string
     */
    protected $fractional;

    /**
     * @var int
     */
    protected $scale;


    public function __toString()
    {
        return $this->getValue();
    }


    public function __construct(
        $original,
        //
        string $minus,
        string $integral,
        string $fractional,
        //
        int $scale
    )
    {
        $this->original = $original;

        $this->minus = $minus;
        $this->integral = $integral;
        $this->fractional = $fractional;

        $this->scale = $scale;
    }


    public function getMinus() : string
    {
        return $this->minus;
    }

    public function getIntegral() : string
    {
        return $this->integral;
    }

    public function getFractional() : string
    {
        return $this->fractional;
    }


    public function getScale() : int
    {
        return $this->scale;
    }


    public function getValue() : string
    {
        return "{$this->minus}{$this->integral}{$this->fractional}";
    }

    public function getAbsolute() : string
    {
        return "{$this->integral}{$this->fractional}";
    }

    public function getFloor() : string
    {
        return "{$this->minus}{$this->integral}";
    }
}
