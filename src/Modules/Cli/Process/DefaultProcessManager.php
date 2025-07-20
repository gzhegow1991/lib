<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Cli\Process;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\Runtime\ComposerException;


class DefaultProcessManager implements ProcessManagerInterface
{
    const SYMFONY_PROCESS_CLASS = '\Symfony\Component\Process\Process';


    /**
     * @var bool
     */
    protected $useSymfonyProcess = false;


    /**
     * @return \Symfony\Component\Process\Process
     */
    public function newSymfonyProcess(Proc $proc) : object
    {
        $process = $proc->newSymfonyProcess();

        return $process;
    }


    /**
     * @return static
     */
    public function useSymfonyProcess(?bool $useSymfonyProcess = null)
    {
        $classExists = class_exists(static::SYMFONY_PROCESS_CLASS);

        $useSymfonyProcess = $useSymfonyProcess ?? $classExists;

        if ($useSymfonyProcess) {
            if (! $classExists) {
                $commands = [
                    'composer require symfony/process',
                ];

                throw new ComposerException(
                    [
                        ''
                        . 'Please, run following commands: '
                        . '[ ' . implode(' ][ ', $commands) . ' ]',
                    ]
                );
            }
        }

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
        $thePhp = Lib::$php;

        $isWindows = $thePhp->is_windows();

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
        $thePhp = Lib::$php;

        $proc->setIsBackground(true);

        $isWindows = $thePhp->is_windows();

        $devnull = null
            ?? ($this->useSymfonyProcess ? $proc->spawnUsingSymfonyProcess() : null)
            ?? ($isWindows ? $proc->spawnUsingProcOpenWindows() : null)
            ?? ($proc->spawnUsingProcOpenUnix());

        return $this;
    }
}
