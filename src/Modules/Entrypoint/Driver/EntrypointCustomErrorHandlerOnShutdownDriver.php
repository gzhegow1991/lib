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


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        if ( $value === null ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->callable($value, null)->orThrow();
        }

        $configCurrent[EntrypointModule::OPT_CUSTOM_ERROR_HANDLER_ON_SHUTDOWN] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        // > works with PhpErrorHandler driver
    }


    public function fnErrorHandlerOnShutdown($errno, $errstr, $errfile, $errline)
    {
        if ( ! (error_reporting() & $errno) ) {
            return null;
        }

        $theEntrypoint = Lib::entrypoint();

        $isEnableThrowablesOnShutdown = $theEntrypoint->get($theEntrypoint::OPT_CUSTOM_ENABLE_THROWABLES_ON_SHUTDOWN);
        if ( ! $isEnableThrowablesOnShutdown ) {
            return null;
        }

        $isErrorHeadersAlreadySent = (false !== strpos($errstr, 'Cannot modify header information'));
        if ( ! (false
            || $isErrorHeadersAlreadySent
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
