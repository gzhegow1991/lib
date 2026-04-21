<?php

namespace Gzhegow\Lib\Exception\Traits;

use Gzhegow\Lib\Exception\ExceptInterface;


/**
 * @see HasTraceInterface
 */
trait HasPreviousTrait
{
    /**
     * @var \Throwable|ExceptInterface
     */
    protected $previous;


    public function hasPrevious() : bool
    {
        return (null !== $this->previous);
    }

    /**
     * @return null|\Throwable|ExceptInterface
     */
    public function getPrevious() : ?object
    {
        return $this->previous;
    }
}
