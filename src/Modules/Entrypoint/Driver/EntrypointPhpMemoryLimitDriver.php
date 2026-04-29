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


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theFormat = Lib::format();

        $valueValid = $value;
        $valueValid = $theFormat->bytes_decode([], $valueValid);
        $valueValid = $theFormat->bytes_encode([], $valueValid, 0, 1);

        $configSet[EntrypointModule::OPT_PHP_MEMORY_LIMIT] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        ini_set('memory_limit', $value);
    }
}
