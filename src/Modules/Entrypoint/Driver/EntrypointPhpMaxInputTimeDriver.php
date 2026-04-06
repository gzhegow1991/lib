<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpMaxInputTimeDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return ini_get('max_input_time');
    }

    public function getRecommended()
    {
        return -1;
    }


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        $valueValid = $theType->int_non_negative_or_minus_one($value)->orThrow();

        $configCurrent[EntrypointModule::OPT_PHP_MAX_INPUT_TIME] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        ini_set('max_input_time', $value);
    }
}
