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


    public function setValue($value, array &$configCurrent) : void
    {
        $theType = Lib::type();

        if ( null === $value ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->dirpath($value, true)->orThrow();
        }

        $configCurrent[EntrypointModule::OPT_PHP_SESSION_SAVE_PATH] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        $theHttpSession = Lib::httpSession();

        if ( null !== $value ) {
            $theFsFile = Lib::fsFile();

            $theFsFile->mkdirp($value, 0775, true);
        }

        $theHttpSession->session_save_path($value);
    }
}
