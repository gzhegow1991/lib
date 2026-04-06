<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpMaxExecutionTimeDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return ini_get('max_execution_time');
    }

    public function getRecommended()
    {
        return 10;
    }


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        $valueValid = $theType->int_non_negative($value)->orThrow();

        $configCurrent[EntrypointModule::OPT_PHP_MAX_EXECUTION_TIME] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        ini_set('max_execution_time', $value);
    }
}
