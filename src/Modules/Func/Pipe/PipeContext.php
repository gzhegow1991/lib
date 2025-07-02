<?php

namespace Gzhegow\Lib\Modules\Func\Pipe;

class PipeContext
{
    /**
     * @var Pipe
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


    public function getPipe() : Pipe
    {
        return $this->pipe;
    }

    /**
     * @return static
     */
    public function setPipe(Pipe $pipe)
    {
        $this->pipe = $pipe;

        return $this;
    }
}
