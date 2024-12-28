<?php

namespace Gzhegow\Lib\Struct\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Struct\AbstractGenericObject;


/**
 * @mixin AbstractGenericObject
 */
trait PublicPropertiesTrait
{
    public function __constructPublicPropertiesTrait()
    {
        static::__filter('isset', [ $this, 'publicPropertiesTraitIsset' ]);
        static::__filter('exists', [ $this, 'publicPropertiesTraitExists' ]);
    }

    protected function publicPropertiesTraitIsset(string $name) : bool
    {
        return true
            && Lib::php()->property_exists($this, $name)
            && isset($this, $name);
    }

    protected function publicPropertiesTraitExists(string $name) : bool
    {
        return Lib::php()->property_exists($this, $name);
    }
}
