<?php

namespace Gzhegow\Lib\Modules\Net;

class SubnetV4
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


    public static function fromValid(string $subnetV4)
    {
        $instance = new static();
        $instance->value = $subnetV4;

        return $instance;
    }


    public function getValue() : string
    {
        return $this->value;
    }
}
