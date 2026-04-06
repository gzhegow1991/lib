<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpUploadTmpDirDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return ini_get('upload_tmp_dir');
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

        $configCurrent[EntrypointModule::OPT_PHP_UPLOAD_TMP_DIR] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        if ( null !== $value ) {
            $theFsFile = Lib::fsFile();

            $theFsFile->mkdirp($value, 0775, true);
        }

        ini_set('upload_tmp_dir', $value);
    }
}
