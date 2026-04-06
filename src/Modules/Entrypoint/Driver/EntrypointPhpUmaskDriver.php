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


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        $bool = (($value >= 0) && ($value <= 0777));
        $theType->true($bool)->orThrow();

        $configCurrent[EntrypointModule::OPT_PHP_UMASK] = $value;
    }

    public function useValue($value, array $configCurrent) : void
    {
        umask($value);
    }
}
