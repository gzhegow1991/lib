<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Php\Process;

interface ProcessManagerInterface
{
    /**
     * @return \Symfony\Component\Process\Process
     */
    public function newSymfonyProcess(ProcessProc $proc) : object;

    /**
     * @return static
     */
    public function useSymfonyProcess(?bool $useSymfonyProcess = null);


    public function newProc() : ProcessProc;

    public function newProcNormal() : ProcessProc;

    public function newProcBackground() : ProcessProc;


    /**
     * @return static
     */
    public function spawn(ProcessProc $proc);

    /**
     * @return static
     */
    public function spawnNormal(ProcessProc $proc);

    /**
     * @return static
     */
    public function spawnBackground(ProcessProc $proc);
}
