<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\DebugModule;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointCustomShouldTraceDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return false;
    }

    public function getRecommended()
    {
        return false;
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        $valueValid = $theType->bool($value)->orThrow();

        $configSet[EntrypointModule::OPT_CUSTOM_SHOULD_TRACE] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        DebugModule::staticShouldTrace($value);
    }
}
