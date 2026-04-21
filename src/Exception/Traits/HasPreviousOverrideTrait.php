<?php

namespace Gzhegow\Lib\Exception\Traits;


use Gzhegow\Lib\Exception\ExceptInterface;


/**
 * @see HasPreviousOverrideInterface
 */
trait HasPreviousOverrideTrait
{
    /**
     * @var \Throwable|ExceptInterface
     */
    protected $previousOverride;


    public function hasPreviousOverride() : bool
    {
        return null !== $this->previousOverride;
    }

    /**
     * @return null|\Throwable|ExceptInterface
     */
    public function getPreviousOverride() : ?object
    {
        $previous = $this->previousOverride ?? $this->getPrevious();

        return $previous;
    }

    /**
     * @return static
     */
    public function setPreviousOverride(?object $previous)
    {
        $this->previousOverride = $previous;

        return $this;
    }
}
