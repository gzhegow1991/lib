<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpPrecisionDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return ini_get('precision');
    }

    public function getRecommended()
    {
        return 16;
    }


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        $valueValid = $theType->int_non_negative($value)->orThrow();

        $configCurrent[EntrypointModule::OPT_PHP_PRECISION] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        ini_set('precision', $value);
    }
}
