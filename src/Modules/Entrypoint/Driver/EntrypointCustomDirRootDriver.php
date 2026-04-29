<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\DebugModule;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointCustomDirRootDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return null;
    }

    public function getRecommended()
    {
        return getcwd();
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        if ( null === $value ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->dirpath_realpath($value)->orThrow();
        }

        $configSet[EntrypointModule::OPT_CUSTOM_DIR_ROOT] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        if ( null === $value ) {
            DebugModule::staticDirRoot(false);

        } else {
            DebugModule::staticDirRoot($value);
        }
    }
}
