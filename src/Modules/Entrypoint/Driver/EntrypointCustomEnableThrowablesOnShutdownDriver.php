<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;
use Gzhegow\Lib\Exception\ErrorException;


class EntrypointCustomEnableThrowablesOnShutdownDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return true;
    }

    public function getRecommended()
    {
        return true;
    }


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        $valueValid = $theType->bool($value)->orThrow();

        $configCurrent[EntrypointModule::OPT_CUSTOM_ENABLE_THROWABLES_ON_SHUTDOWN] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        $theEntrypoint = Lib::entrypoint();

        if ( $value ) {
            $theEntrypoint->registerShutdownFunction([ $this, 'onShutdown_fatalOnShutdown' ]);
            $theEntrypoint->registerShutdownFunction([ $this, 'onShutdown_throwablesOnShutdown' ]);

        } else {
            $theEntrypoint->unregisterShutdownFunction([ $this, 'onShutdown_fatalOnShutdown' ]);
            $theEntrypoint->unregisterShutdownFunction([ $this, 'onShutdown_throwablesOnShutdown' ]);
        }
    }


    public function onShutdown_fatalOnShutdown() : void
    {
        $err = error_get_last();

        if ( null === $err ) {
            return;
        }

        if ( ! (error_reporting() & $err['type']) ) {
            return;
        }

        $theEntrypoint = Lib::entrypoint();
        $theEntrypoint->getThrowablesOnShutdown($refThrowablesOnShutdown);

        $e = new ErrorException($err['message'], -1, $err['type'], $err['file'], $err['line']);

        $refThrowablesOnShutdown[] = $e;
    }

    public function onShutdown_throwablesOnShutdown() : void
    {
        $theEntrypoint = Lib::entrypoint();
        $theEntrypoint->getThrowablesOnShutdown($refThrowablesOnShutdown);

        if ( [] === $refThrowablesOnShutdown ) return;

        foreach ( $refThrowablesOnShutdown as $e ) {
            $fn = set_exception_handler(null);

            if ( $fn ) {
                $fn($e);
            }
        }

        array_splice($refThrowablesOnShutdown, 0);
    }
}
