<?php

namespace Gzhegow\Lib\Modules\Func\Pipe;


class FuncPipeContext
{
    /**
     * @var FuncPipe
     */
    protected $pipe;

    /**
     * @var array
     */
    public $payload;


    public function __construct(?array &$payload = null)
    {
        $this->payload =& $payload;
    }


    public function getPipe() : FuncPipe
    {
        return $this->pipe;
    }

    /**
     * @return static
     */
    public function setPipe(FuncPipe $pipe)
    {
        $this->pipe = $pipe;

        return $this;
    }
}
