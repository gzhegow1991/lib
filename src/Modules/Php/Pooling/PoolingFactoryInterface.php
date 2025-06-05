<?php

namespace Gzhegow\Lib\Modules\Php\Pooling;

interface PoolingFactoryInterface
{
    public function newContext() : PoolingContext;
}
