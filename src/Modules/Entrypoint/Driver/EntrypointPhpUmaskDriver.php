<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpUmaskDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        $before = umask(0002);

        umask($before);

        return $before;
    }

    public function getRecommended()
    {
        return 0002;
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        $bool = (($value >= 0) && ($value <= 0777));
        $theType->bool_true($bool)->orThrow();

        $configSet[EntrypointModule::OPT_PHP_UMASK] = $value;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        umask($value);
    }
}
