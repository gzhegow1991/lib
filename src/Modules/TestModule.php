<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Test\TestCase;


class TestModule
{
    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    public function newTestCase() : TestCase
    {
        $instance = new TestCase();

        return $instance;
    }
}
