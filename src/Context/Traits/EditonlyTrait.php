<?php

namespace Gzhegow\Lib\Context\Traits;

use Gzhegow\Lib\Context\AbstractContext;


/**
 * @mixin AbstractContext
 */
trait EditonlyTrait
{
    protected static function __bootEditonlyTrait()
    {
        static::__addFilter('set', static::class . '::editonlyTrait_set');
        static::__addFilter('unset', static::class . '::editonlyTrait_unset');
        static::__addFilter('clear', static::class . '::editonlyTrait_clear');
    }

    /**
     * @param object|static $self
     */
    protected static function editonlyTrait_set(object $self, string $name) : bool
    {
        return $self->exists($name);
    }

    /**
     * @param object|static $self
     */
    protected static function editonlyTrait_unset(object $self, string $name) : bool
    {
        return false;
    }

    /**
     * @param object|static $self
     */
    protected static function editonlyTrait_clear(object $self, string $name) : bool
    {
        return $self->exists($name);
    }
}
