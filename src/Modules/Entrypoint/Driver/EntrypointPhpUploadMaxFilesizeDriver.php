<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpUploadMaxFilesizeDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return ini_get('upload_max_filesize');
    }

    public function getRecommended()
    {
        return '0';
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theFormat = Lib::format();

        $valueValid = $value;
        $valueValid = $theFormat->bytes_decode([], $valueValid);
        $valueValid = $theFormat->bytes_encode([], $valueValid, 0, 1);

        $configSet[EntrypointModule::OPT_PHP_UPLOAD_MAX_FILESIZE] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        ini_set('upload_max_filesize', $value);
    }
}
