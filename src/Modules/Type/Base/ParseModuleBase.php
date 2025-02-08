<?php

namespace Gzhegow\Lib\Modules\Type\Base;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\TypeModule;


abstract class ParseModuleBase
{
    /**
     * @var TypeModule
     */
    protected $theType;


    public function __construct(TypeModule $theType = null)
    {
        $this->theType = $theType ?? Lib::type();
    }
}
