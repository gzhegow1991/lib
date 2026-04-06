<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpErrorLogDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return ini_get('error_log');
    }

    public function getRecommended()
    {
        return null;
    }


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        if ( null === $value ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->filepath($value, true)->orThrow();
        }

        $configCurrent[EntrypointModule::OPT_PHP_ERROR_LOG] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        ini_set('error_log', $value);
    }
}
