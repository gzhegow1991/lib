<?php

namespace Gzhegow\Lib\Modules\Async\Promise\Pooling;

class DefaultPromisePoolingFactory implements PromisePoolingFactoryInterface
{
    public function newContext() : PromisePoolingContext
    {
        return new PromisePoolingContext();
    }
}
