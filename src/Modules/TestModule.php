<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
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


    public function newCase(\Closure $fn, array $args = []) : TestCaseInterface
    {
        $trace = Lib::debug()->trace([], 1);

        return TestCase::new()
            ->fn($fn, $args)
            ->setTrace($trace)
        ;
    }
}
