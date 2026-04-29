<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;
use Gzhegow\Lib\Exception\ErrorException;


class EntrypointCustomErrorHandlerOnShutdownDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return [ $this, 'fnErrorHandlerOnShutdown' ];
    }

    public function getRecommended()
    {
        return [ $this, 'fnErrorHandlerOnShutdown' ];
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        if ( $value === null ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->callable($value, null)->orThrow();
        }

        $configSet[EntrypointModule::OPT_CUSTOM_ERROR_HANDLER_ON_SHUTDOWN] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        // > works with PhpErrorHandler driver
    }


    public function fnErrorHandlerOnShutdown($errno, $errstr, $errfile, $errline)
    {
        if ( ! (error_reporting() & $errno) ) {
            return null;
        }

        $theEntrypoint = Lib::entrypoint();

        $isEnableThrowablesOnShutdown = $theEntrypoint->getOpt($theEntrypoint::OPT_CUSTOM_ENABLE_THROWABLES_ON_SHUTDOWN);
        if ( ! $isEnableThrowablesOnShutdown ) {
            return null;
        }

        $isErrorCannotModifyHeaderInformation = (false !== stripos($errstr, 'cannot modify header information'));
        $isErrorHeadersHaveAlreadyBeenSent = (false !== stripos($errstr, 'headers have already been sent'));
        if ( ! (false
            || $isErrorCannotModifyHeaderInformation
            || $isErrorHeadersHaveAlreadyBeenSent
        ) ) {
            return null;
        }

        $e = new ErrorException($errstr, -1, $errno, $errfile, $errline);
        $eTrace = $e->getTrace();

        // > remove first frame
        array_shift($eTrace);
        $e->setTraceOverride($eTrace);
        // < remove first frame

        $theEntrypoint->getThrowablesOnShutdown($refThrowablesOnShutdown);
        $refThrowablesOnShutdown[] = $e;

        return $this;
    }
}
