<?php

namespace Gzhegow\Lib\Struct\Traits;

use Gzhegow\Lib\Struct\AbstractGenericObject;


/**
 * @mixin AbstractGenericObject
 */
trait ProtectedPropertiesTrait
{
    public function __constructProtectedPropertiesTrait()
    {
        static::__filter('isset', [ $this, 'protectedPropertiesTraitIsset' ]);
        static::__filter('exists', [ $this, 'protectedPropertiesTraitExists' ]);
    }

    protected function protectedPropertiesTraitIsset(string $name) : bool
    {
        return true
            && isset($this, $name);
    }

    protected function protectedPropertiesTraitExists(string $name) : bool
    {
        return property_exists($this, $name);
    }
}
