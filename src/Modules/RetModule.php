<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Type\Traits\RetTrait;


class RetModule
{
    use RetTrait;


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }
}
