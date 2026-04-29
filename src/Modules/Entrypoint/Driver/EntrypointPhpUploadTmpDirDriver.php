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


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        if ( null === $value ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->dirpath($value, true)->orThrow();
        }

        $configSet[EntrypointModule::OPT_PHP_UPLOAD_TMP_DIR] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        if ( is_string($value) ) {
            $theFsFile = Lib::fsFile();
            $theFsFile->call_safe(static function ($ctx, $dirname) {
                if ( ! is_dir($dirname) ) {
                    mkdir($dirname, 0775, true);
                }
            }, [ $dirname = $value ]);
        }

        ini_set('upload_tmp_dir', $value);
    }
}
