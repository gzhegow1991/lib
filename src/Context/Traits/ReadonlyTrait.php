<?php

namespace Gzhegow\Lib\Context\Traits;

use Gzhegow\Lib\Context\AbstractContext;


/**
 * @mixin AbstractContext
 */
trait ReadonlyTrait
{
    protected static function __bootReadonlyTrait()
    {
        static::__addFilter('set', static::class . '::readonlyTrait_set');
        static::__addFilter('unset', static::class . '::readonlyTrait_unset');
        static::__addFilter('clear', static::class . '::readonlyTrait_clear');
    }

    /**
     * @param object|static $self
     */
    protected static function readonlyTrait_set(object $self, string $name) : bool
    {
        return ! isset($self->{$name});
    }

    /**
     * @param object|static $self
     */
    protected static function readonlyTrait_unset(object $self, string $name) : bool
    {
        return false;
    }

    /**
     * @param object|static $self
     */
    protected static function readonlyTrait_clear(object $self, string $name) : bool
    {
        return false;
    }
}
