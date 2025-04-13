<?php

namespace Gzhegow\Lib\Modules\Net;

class AddressIpV4
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


    public static function fromValid(string $addressIpV4)
    {
        $instance = new static();
        $instance->value = $addressIpV4;

        return $instance;
    }


    public function getValue() : string
    {
        return $this->value;
    }
}
