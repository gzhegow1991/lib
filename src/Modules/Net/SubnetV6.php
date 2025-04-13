<?php

namespace Gzhegow\Lib\Modules\Net;

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


    public static function fromValid(string $subnetV6)
    {
        $instance = new static();
        $instance->value = $subnetV6;

        return $instance;
    }


    public function getValue() : string
    {
        return $this->value;
    }
}
