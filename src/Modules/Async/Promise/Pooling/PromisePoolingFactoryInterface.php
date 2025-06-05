<?php

namespace Gzhegow\Lib\Modules\Async\Promise\Pooling;

interface PromisePoolingFactoryInterface
{
    public function newContext() : PromisePoolingContext;
}
