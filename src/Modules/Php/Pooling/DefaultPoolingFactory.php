<?php

namespace Gzhegow\Lib\Modules\Php\Pooling;

class DefaultPoolingFactory implements PoolingFactoryInterface
{
    public function newContext() : PoolingContext
    {
        return new PoolingContext();
    }
}
