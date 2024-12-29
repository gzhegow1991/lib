<?php

namespace Gzhegow\Lib\Context\Traits;

use Gzhegow\Lib\Context\AbstractContext;


/**
 * @mixin AbstractContext
 */
trait WritableTrait
{
    protected static function __bootWritableTrait()
    {
        static::__addFilter('set', static::class . '::writableTrait_set');
        static::__addFilter('unset', static::class . '::writableTrait_unset');
        static::__addFilter('clear', static::class . '::writableTrait_clear');
    }

    /**
     * @param object|static $self
     */
    protected static function writableTrait_set(object $self, string $name) : bool
    {
        return true;
    }

    /**
     * @param object|static $self
     */
    protected static function writableTrait_unset(object $self, string $name) : bool
    {
        return true;
    }

    /**
     * @param object|static $self
     */
    protected static function writableTrait_clear(object $self, string $name) : bool
    {
        return $self->exists($name);
    }
}
