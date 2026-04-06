<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;
use Gzhegow\Lib\Exception\ErrorException;


class EntrypointPhpErrorHandlerDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return set_error_handler(null);
    }

    public function getRecommended()
    {
        return [ $this, 'fnErrorHandlerDefault' ];
    }


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        if ( $value === null ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->callable($value, null)->orThrow();
        }

        $configCurrent[EntrypointModule::OPT_PHP_ERROR_HANDLER] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        set_error_handler([ $this, 'fnErrorHandler' ]);
    }


    public function fnErrorHandler($errno, $errstr, $errfile, $errline) : void
    {
        $theEntrypoint = Lib::entrypoint();

        $fnCustomErrorHandlerOnShutdown = $theEntrypoint->getOpt($theEntrypoint::OPT_CUSTOM_ERROR_HANDLER_ON_SHUTDOWN);
        $fnPhpErrorHandler = $theEntrypoint->getOpt($theEntrypoint::OPT_PHP_ERROR_HANDLER);

        $devnull = null
            ?? ($fnCustomErrorHandlerOnShutdown ? $fnCustomErrorHandlerOnShutdown($errno, $errstr, $errfile, $errline) : null)
            ?? ($fnPhpErrorHandler ? $fnPhpErrorHandler($errno, $errstr, $errfile, $errline) : null);
    }

    /**
     * @throws ErrorException
     */
    public function fnErrorHandlerDefault($errno, $errstr, $errfile, $errline)
    {
        if ( ! (error_reporting() & $errno) ) {
            return null;
        }

        throw new ErrorException($errstr, -1, $errno, $errfile, $errline);
    }
}
