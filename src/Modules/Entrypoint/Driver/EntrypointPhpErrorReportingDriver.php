<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpErrorReportingDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return error_reporting();
    }

    public function getRecommended()
    {
        return (E_ALL | E_DEPRECATED | E_USER_DEPRECATED);
    }


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        $bool = (0 === ($value & ~(E_ALL | E_DEPRECATED | E_USER_DEPRECATED)));
        $theType->bool_true($bool)->orThrow();

        $configCurrent[EntrypointModule::OPT_PHP_ERROR_REPORTING] = $value;
    }

    public function useValue($value, array $configCurrent) : void
    {
        if ( null === $value ) return;

        error_reporting($value);
    }
}
