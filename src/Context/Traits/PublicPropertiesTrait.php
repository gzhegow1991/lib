<?php

namespace Gzhegow\Lib\Context\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Context\AbstractContext;


/**
 * @mixin AbstractContext
 */
trait PublicPropertiesTrait
{
    protected static function __bootPublicPropertiesTrait()
    {
        static::__addFilter('isset', static::class . '::publicPropertiesTrait_isset');
        static::__addFilter('exists', static::class . '::publicPropertiesTrait_exists');
        static::__addFilter('has', static::class . '::publicPropertiesTrait_has');
        static::__addFilter('get', static::class . '::publicPropertiesTrait_get');
    }

    /**
     * @param object|static $self
     */
    protected static function publicPropertiesTrait_isset(object $self, string $name) : bool
    {
        return $self->exists($name);
    }

    /**
     * @param object|static $self
     */
    protected static function publicPropertiesTrait_exists(object $self, string $name) : bool
    {
        return Lib::php()->property_exists($self, $name);
    }

    /**
     * @param object|static $self
     */
    protected static function publicPropertiesTrait_has(object $self, string $name) : bool
    {
        return $self->exists($name);
    }

    /**
     * @param object|static $self
     */
    protected static function publicPropertiesTrait_get(object $self, string $name) : bool
    {
        return $self->exists($name);
    }
}
