<?php

namespace Gzhegow\Lib\Struct\Traits;

use Gzhegow\Lib\Struct\AbstractGenericObject;


/**
 * @mixin AbstractGenericObject
 */
trait ReadonlyPropertiesTrait
{
    public function __constructReadonlyPropertiesTrait()
    {
        static::__filter('clear', [ $this, 'readonlyPropertiesTraitClear' ]);
        static::__filter('set', [ $this, 'readonlyPropertiesTraitSet' ]);
        static::__filter('unset', [ $this, 'readonlyPropertiesTraitUnset' ]);
    }

    protected function readonlyPropertiesTraitClear(string $name) : bool
    {
        return false;
    }

    protected function readonlyPropertiesTraitSet(string $name) : bool
    {
        return true
            && $this->exists($name)
            && ! isset($this->{$name});
    }

    protected function readonlyPropertiesTraitUnset(string $name) : bool
    {
        return false;
    }
}
