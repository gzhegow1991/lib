<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Php\Process;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\ComposerException;


class DefaultProcessManager implements ProcessManagerInterface
{
    const SYMFONY_PROCESS_CLASS = '\Symfony\Component\Process\Process';


    /**
     * @var bool
     */
    protected $useSymfonyProcess = false;
    /**
     * @var bool
     */
    protected $useProcOpen = true;


    /**
     * @return \Symfony\Component\Process\Process
     */
    public function newSymfonyProcess(ProcessProc $proc) : object
    {
        $process = $proc->newSymfonyProcess();

        return $process;
    }


    /**
     * @return static
     */
    public function useSymfonyProcess(?bool $useSymfonyProcess = null)
    {
        $useSymfonyProcess = $useSymfonyProcess ?? true;

        if ( $useSymfonyProcess ) {
            $classExists = class_exists(static::SYMFONY_PROCESS_CLASS);

            if ( ! $classExists ) {
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

        $this->useProcOpen = false;
        //
        $this->useSymfonyProcess = $useSymfonyProcess;

        return $this;
    }

    /**
     * @return static
     */
    public function useProcOpen(?bool $useProcOpen = null)
    {
        $useProcOpen = $useProcOpen ?? true;

        $this->useSymfonyProcess = false;
        //
        $this->useProcOpen = $useProcOpen;

        return $this;
    }


    public function newProc() : ProcessProc
    {
        $processSpawn = new ProcessProc();

        return $processSpawn;
    }

    public function newProcNormal() : ProcessProc
    {
        $processSpawn = new ProcessProc();

        $processSpawn->setIsBackground(false);

        return $processSpawn;
    }

    public function newProcBackground() : ProcessProc
    {
        $processSpawn = new ProcessProc();

        $processSpawn->setIsBackground(true);

        return $processSpawn;
    }


    /**
     * @return static
     */
    public function spawn(ProcessProc $proc)
    {
        $thePhp = Lib::php();

        if ( $this->useSymfonyProcess ) {
            $proc->spawnUsingSymfonyProcess();

        } elseif ( $this->useProcOpen ) {
            if ( $thePhp->is_os_windows() ) {
                $proc->spawnUsingProcOpenWindows();

            } else {
                $proc->spawnUsingProcOpenUnix();
            }

        } else {
            throw new RuntimeException(
                [ 'You must set `useSymfonyProcess` or `useProcOpen`', $this ]
            );
        }

        return $this;
    }

    /**
     * @return static
     */
    public function spawnNormal(ProcessProc $proc)
    {
        $thePhp = Lib::php();

        $proc->setIsBackground(false);

        if ( $this->useSymfonyProcess ) {
            $proc->spawnUsingSymfonyProcess();

        } elseif ( $this->useProcOpen ) {
            if ( $thePhp->is_os_windows() ) {
                $proc->spawnUsingProcOpenWindows();

            } else {
                $proc->spawnUsingProcOpenUnix();
            }

        } else {
            throw new RuntimeException(
                [ 'You must set `useSymfonyProcess` or `useProcOpen`', $this ]
            );
        }

        return $this;
    }

    /**
     * @return static
     */
    public function spawnBackground(ProcessProc $proc)
    {
        $thePhp = Lib::php();

        $proc->setIsBackground(true);

        if ( $this->useSymfonyProcess ) {
            $proc->spawnUsingSymfonyProcess();

        } elseif ( $this->useProcOpen ) {
            if ( $thePhp->is_os_windows() ) {
                $proc->spawnUsingProcOpenWindows();

            } else {
                $proc->spawnUsingProcOpenUnix();
            }

        } else {
            throw new RuntimeException(
                [ 'You must set `useSymfonyProcess` or `useProcOpen`', $this ]
            );
        }

        return $this;
    }
}
