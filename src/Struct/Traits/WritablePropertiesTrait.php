<?php

namespace Gzhegow\Lib\Struct\Traits;

use Gzhegow\Lib\Struct\AbstractGenericObject;


/**
 * @mixin AbstractGenericObject
 */
trait WritablePropertiesTrait
{
    public function __constructWritablePropertiesTrait()
    {
        static::__filter('clear', [ $this, 'writablePropertiesTraitClear' ]);
        static::__filter('set', [ $this, 'writablePropertiesTraitSet' ]);
        static::__filter('unset', [ $this, 'writablePropertiesTraitUnset' ]);
    }

    protected function writablePropertiesTraitClear(string $name) : bool
    {
        return $this->exists($name);
    }

    protected function writablePropertiesTraitSet(string $name) : bool
    {
        return true;
    }

    protected function writablePropertiesTraitUnset(string $name) : bool
    {
        return true;
    }
}
