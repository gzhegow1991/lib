<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpErrorLogDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return ini_get('error_log');
    }

    public function getRecommended()
    {
        return null;
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        if ( null === $value ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->filepath($value, true)->orThrow();
        }

        $configSet[EntrypointModule::OPT_PHP_ERROR_LOG] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        if ( is_string($value) ) {
            $theFsFile = Lib::fsFile();
            $theFsFile->call_safe(static function ($ctx, $dirname) {
                if ( ! is_dir($dirname) ) {
                    mkdir($dirname, 0775, true);
                }
            }, [ $dirname = dirname($value) ]);
        }

        ini_set('error_log', $value);
    }
}
