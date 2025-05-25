<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Test\Test;


class TestModule
{
    public function newTest() : Test
    {
        return new Test();
    }
}
