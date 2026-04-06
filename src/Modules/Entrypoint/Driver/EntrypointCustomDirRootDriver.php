<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\DebugModule;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointCustomDirRootDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return getcwd();
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
            $valueValid = $theType->dirpath_realpath($value)->orThrow();
        }

        $configCurrent[EntrypointModule::OPT_CUSTOM_DIR_ROOT] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        if ( null === $value ) {
            DebugModule::staticDirRoot(false);

        } else {
            DebugModule::staticDirRoot($value);
        }
    }
}
