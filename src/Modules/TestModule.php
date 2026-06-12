<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Test\TestCase\TestCase;
use Gzhegow\Lib\Modules\Test\TestCase\TestCaseInterface;


class TestModule
{
    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    public function newCase(?\Closure $fn = null, ?array $fnArgs = null) : TestCaseInterface
    {
        $testCase = TestCase::new();

        if ( null !== $fn ) {
            $testCase->fn($fn, $fnArgs);
        }

        return $testCase;
    }
}
