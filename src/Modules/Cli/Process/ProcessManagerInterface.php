<?php

namespace Gzhegow\Lib\Modules\Cli\Process;

interface ProcessManagerInterface
{
    /**
     * @return static
     */
    public function useSymfonyProcess(?bool $useSymfonyProcess = null);


    public function spawn(
        &$result,
        array $cmd, ?string $cwd = null, ?array $env = null, $input = null
    ) : bool;
}
