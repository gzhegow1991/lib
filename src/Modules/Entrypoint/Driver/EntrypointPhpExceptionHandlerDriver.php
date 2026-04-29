<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpExceptionHandlerDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return set_exception_handler(null);
    }

    public function getRecommended()
    {
        return [ $this, 'fnExceptionHandler' ];
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        if ( $value === null ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->callable($value, null)->orThrow();
        }

        $configSet[EntrypointModule::OPT_PHP_EXCEPTION_HANDLER] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        set_exception_handler($value);
    }


    public function fnExceptionHandler(\Throwable $e) : void
    {
        $theDebugThrowabler = Lib::debugThrowabler();

        try {
            $lines = $theDebugThrowabler->getLines(
                $e,
                0
                //
                | _DEBUG_THROWABLER_WITH_CODE
                | _DEBUG_THROWABLER_WITH_INFO
                | _DEBUG_THROWABLER_WITH_TRACE
                //
                | _DEBUG_THROWABLER_INFO_WITH_FILE
                | _DEBUG_THROWABLER_INFO_WITH_OBJECT_CLASS
                | _DEBUG_THROWABLER_INFO_WITHOUT_OBJECT_ID
            );
        } catch (\Throwable $e) {
            dd($e);
        }

        echo "\n" . implode("\n", $lines) . "\n";
    }
}
