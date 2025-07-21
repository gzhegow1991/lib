<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Test\TestCase;


class TestModule
{
    public function newTestCase() : TestCase
    {
        return new TestCase();
    }
}
