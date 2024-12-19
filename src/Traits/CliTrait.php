<?php

namespace Gzhegow\Lib\Traits;

trait CliTrait
{
    public static function cli_pause() : void
    {
        echo '> Press ENTER to continue...' . PHP_EOL;
        $h = fopen('php://stdin', 'r');
        fgets($h);
        fclose($h);
    }
}
