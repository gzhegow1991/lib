<?php

namespace Gzhegow\Lib\Modules\Cli\Process;

interface ProcessManagerInterface
{
    /**
     * @return static
     */
    public function useSymfonyProcess(?bool $useSymfonyProcess = null);


    public function newProc() : Proc;

    public function newProcBackground() : Proc;


    /**
     * @return static
     */
    public function spawn(Proc $proc);

    /**
     * @return static
     */
    public function spawnBackground(Proc $proc);
}
