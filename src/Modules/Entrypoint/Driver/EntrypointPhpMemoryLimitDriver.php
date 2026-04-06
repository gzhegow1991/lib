<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpMemoryLimitDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return ini_get('memory_limit');
    }

    public function getRecommended()
    {
        return '32M';
    }


    public function setValue($value, array &$configCurrent) : void
    {
        $theFormat = Lib::format();

        $valueValid = $value;
        $valueValid = $theFormat->bytes_decode([], $valueValid);
        $valueValid = $theFormat->bytes_encode([], $valueValid, 0, 1);

        $configCurrent[EntrypointModule::OPT_PHP_MEMORY_LIMIT] = $valueValid;
    }

    public function useValue($value, array $configCurrent) : void
    {
        ini_set('memory_limit', $value);
    }
}
