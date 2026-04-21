<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\Except;
use Gzhegow\Lib\Modules\Type\Ret;
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


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        $valueValid = $theType->bool($value)->orThrow();

        $configCurrent[EntrypointModule::OPT_CUSTOM_SHOULD_TRACE] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        DebugModule::staticShouldTrace($value);
    }
}
