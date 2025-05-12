<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Test\TestRunner\TestRunner;


class TestModule
{
    public function test() : TestRunner
    {
        return new TestRunner();
    }
}
