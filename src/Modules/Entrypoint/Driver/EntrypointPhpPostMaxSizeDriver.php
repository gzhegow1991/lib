<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpPostMaxSizeDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return ini_get('post_max_size');
    }

    public function getRecommended()
    {
        return '1M';
    }


    public function setValue($value, array &$configCurrent) : void
    {
        $theFormat = Lib::format();

        $valueValid = $value;
        $valueValid = $theFormat->bytes_decode([], $valueValid);
        $valueValid = $theFormat->bytes_encode([], $valueValid, 0, 1);

        $configCurrent[EntrypointModule::OPT_PHP_POST_MAX_SIZE] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        ini_set('post_max_size', $value);
    }
}
