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


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        if ( $value === null ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->callable($value, null)->orThrow();
        }

        $configCurrent[EntrypointModule::OPT_PHP_EXCEPTION_HANDLER] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        set_exception_handler($value);
    }


    public function fnExceptionHandler(\Throwable $e) : void
    {
        $theDebugThrowabler = Lib::debugThrowabler();

        $lines = $theDebugThrowabler->getPreviousMessagesAllLines(
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

        echo "\n" . implode("\n", $lines) . "\n";
    }
}
