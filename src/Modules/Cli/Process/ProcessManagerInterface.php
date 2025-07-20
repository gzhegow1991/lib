<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Cli\Process;

interface ProcessManagerInterface
{
    /**
     * @return \Symfony\Component\Process\Process
     */
    public function newSymfonyProcess(Proc $proc) : object;

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
