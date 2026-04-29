<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpDisplayStartupErrorsDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return ini_get('display_startup_errors');
    }

    public function getRecommended()
    {
        return 0;
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        $valueValid = $theType->bool($value)->orThrow();
        $valueValid = (int) $valueValid;

        $configSet[EntrypointModule::OPT_PHP_DISPLAY_STARTUP_ERRORS] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        ini_set('display_startup_errors', $value);
    }
}
