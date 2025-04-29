<?php

namespace Gzhegow\Lib\Modules\Php\ErrorBag;

class Error
{
    /**
     * @var mixed
     */
    public $error;

    /**
     * @var array<string, bool>
     */
    public $tags = [];

    /**
     * @var array{
     *     file?: string,
     *     line?: int,
     * }
     */
    public $trace = [];
}
