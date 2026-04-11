<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Type\Traits\TTrait;
use Gzhegow\Lib\Modules\Type\Interfaces\TInterface;


class TModule implements TInterface
{
    use TTrait;


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }
}
