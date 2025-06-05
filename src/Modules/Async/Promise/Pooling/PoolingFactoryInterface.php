<?php

namespace Gzhegow\Lib\Modules\Async\Promise\Pooling;

interface PoolingFactoryInterface
{
    public function newContext() : PoolingContext;
}
