<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Cli\Process;

use Gzhegow\Lib\Lib;


class DefaultProcessManager implements ProcessManagerInterface
{
    const SYMFONY_PROCESS_CLASS = '\Symfony\Component\Process\Process';


    /**
     * @var bool
     */
    protected $useSymfonyProcess = false;


    /**
     * @return static
     */
    public function useSymfonyProcess(?bool $useSymfonyProcess = null)
    {
        $classExists = class_exists(static::SYMFONY_PROCESS_CLASS);

        $useSymfonyProcess = $useSymfonyProcess ?? $classExists;

        $this->useSymfonyProcess = $useSymfonyProcess;

        return $this;
    }


    public function newProc() : Proc
    {
        $processSpawn = new Proc();

        return $processSpawn;
    }

    public function newProcBackground() : Proc
    {
        $processSpawn = new Proc();
        $processSpawn->setIsBackground(true);

        return $processSpawn;
    }


    /**
     * @return static
     */
    public function spawn(Proc $proc)
    {
        $isWindows = Lib::php()->is_windows();

        $devnull = null
            ?? ($this->useSymfonyProcess ? $proc->spawnUsingSymfonyProcess() : null)
            ?? ($isWindows ? $proc->spawnUsingProcOpenWindows() : null)
            ?? ($proc->spawnUsingProcOpenUnix());

        return $this;
    }

    /**
     * @return static
     */
    public function spawnBackground(Proc $proc)
    {
        $proc->setIsBackground(true);

        $isWindows = Lib::php()->is_windows();

        $devnull = null
            ?? ($this->useSymfonyProcess ? $proc->spawnUsingSymfonyProcess() : null)
            ?? ($isWindows ? $proc->spawnUsingProcOpenWindows() : null)
            ?? ($proc->spawnUsingProcOpenUnix());

        return $this;
    }
}
