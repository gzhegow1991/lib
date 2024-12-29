<?php

namespace Gzhegow\Lib\Context\Traits;

use Gzhegow\Lib\Context\AbstractContext;


/**
 * @mixin AbstractContext
 */
trait AnyPropertiesTrait
{
    protected static function __bootAnyPropertiesTrait()
    {
        static::__addFilter('isset', static::class . '::anyPropertiesTrait_isset');
        static::__addFilter('exists', static::class . '::anyPropertiesTrait_exists');
        static::__addFilter('has', static::class . '::anyPropertiesTrait_has');
        static::__addFilter('get', static::class . '::anyPropertiesTrait_get');
    }

    /**
     * @param object|static $self
     */
    protected static function anyPropertiesTrait_isset(object $self, string $name) : bool
    {
        return true;
    }

    /**
     * @param object|static $self
     */
    protected static function anyPropertiesTrait_exists(object $self, string $name) : bool
    {
        return true;
    }

    /**
     * @param object|static $self
     */
    protected static function anyPropertiesTrait_has(object $self, string $name) : bool
    {
        return $self->exists($name);
    }

    /**
     * @param object|static $self
     */
    protected static function anyPropertiesTrait_get(object $self, string $name) : bool
    {
        return $self->exists($name);
    }
}
