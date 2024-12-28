<?php

namespace Gzhegow\Lib\Modules;

class CliModule
{
    public function pause() : void
    {
        echo '> Press ENTER to continue...' . PHP_EOL;
        $h = fopen('php://stdin', 'r');
        fgets($h);
        fclose($h);
    }
}
