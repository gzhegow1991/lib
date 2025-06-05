<?php

namespace Gzhegow\Lib\Modules\Async\Promise\Pooling;

class DefaultPoolingFactory implements PoolingFactoryInterface
{
    public function newContext() : PoolingContext
    {
        return new PoolingContext();
    }
}
