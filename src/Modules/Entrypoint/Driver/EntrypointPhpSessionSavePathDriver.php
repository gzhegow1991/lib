<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpSessionSavePathDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        $theHttpSession = Lib::httpSession();

        return $theHttpSession->session_save_path();
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
            $valueValid = $theType->dirpath($value, true)->orThrow();
        }

        $configSet[EntrypointModule::OPT_PHP_SESSION_SAVE_PATH] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        $theHttpSession = Lib::httpSession();

        if ( is_string($value) ) {
            $theFsFile = Lib::fsFile();
            $theFsFile->call_safe(static function ($ctx, $dirname) {
                if ( ! is_dir($dirname) ) {
                    mkdir($dirname, 0775, true);
                }
            }, [ $dirname = $value ]);
        }

        $theHttpSession->session_save_path($value);
    }
}
