<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Cli\Process;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\Runtime\ComposerException;
use Gzhegow\Lib\Exception\Runtime\FilesystemException;


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
    protected function newSymfonyProcess(
        array $command,
        ?string $cwd = null,
        ?array $env = null,
        $input = null,
        ?int $timeoutMs = null
    ) : object
    {
        $commands = [
            'composer require symfony/process',
        ];

        $symfonyProcessClass = '\Symfony\Component\Process\Process';

        if (! class_exists($symfonyProcessClass)) {
            throw new ComposerException(
                [
                    ''
                    . 'Please, run following commands: '
                    . '[ ' . implode(' ][ ', $commands) . ' ]',
                ]
            );
        }

        $timeoutSeconds = null;
        if (null !== $timeoutMs) {
            if (! Lib::type()->int_positive($timeoutMsInt, $timeoutMs)) {
                throw new LogicException(
                    [ 'The `timeoutMs` should be positive integer or be null', $timeoutMs ]
                );
            }

            $timeoutSeconds = $timeoutMs / 1000;
        }

        $process = new $symfonyProcessClass(
            $command,
            $cwd,
            $env,
            $input,
            $timeoutSeconds
        );

        return $process;
    }


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


    /**
     * @param GenericProcess|null $result
     */
    public function spawn(
        &$result,
        array $cmd,
        ?string $cwd = null, ?array $env = null,
        $input = null
    ) : bool
    {
        if ($this->useSymfonyProcess) {
            $status = $this->spawnUsingSymfonyProcess($result, $cmd, $cwd, $env, $input);

        } else {
            $status = $this->spawnUsingProcOpen($result, $cmd, $cwd, $env, $input);
        }

        return $status;
    }


    protected function spawnUsingSymfonyProcess(
        ?GenericProcess &$genericProcess,
        array $cmd,
        ?string $cwd = null, ?array $env = null,
        $input = null
    ) : bool
    {
        $process = $this->newSymfonyProcess($cmd, $cwd, $env, $input);

        if (null !== $cwd) {
            $process->setWorkingDirectory($cwd);
        }

        $process->setOptions([
            // // > symfony team had disabled following options and uses them by default
            // 'suppress_errors'    => true,
            // 'bypass_shell'       => true,
            //
            'create_new_console' => true,
        ]);

        $process->start();

        $genericProcess = GenericProcess::fromSymfonyProcess($process);

        return true;
    }

    protected function spawnUsingProcOpen(
        ?GenericProcess &$genericProcess,
        array $cmd,
        ?string $cwd = null, ?array $env = null,
        $input = null
    ) : bool
    {
        $status = false
            || $this->spawnUsingProcOpenWindows($ph, $cmd, $cwd, $env, $input)
            || $this->spawnUsingProcOpenUnix($ph, $cmd, $cwd, $env, $input);

        if ($status) {
            $genericProcess = GenericProcess::fromProcOpenResource($ph);
        }

        return $status;
    }


    /**
     * @param resource $ph
     */
    protected function spawnUsingProcOpenWindows(
        &$ph,
        array $cmd,
        ?string $cwd = null, ?array $env = null,
        $input = null
    ) : bool
    {
        $theType = Lib::type();

        if (! $theType->dirpath_realpath($cwdRealpath, $cwd)) {
            throw new LogicException(
                [ 'The `cwd` should be existing directory', $cwd ]
            );
        }

        $cmdExeFile = getenv('SystemRoot') . "\\System32\\cmd.exe";

        if (! $theType->filepath_realpath($cmdExeFileRealpath, $cmdExeFile)) {
            throw new LogicException(
                [ 'The `cmdExeFile` should be existing file', $cmdExeFile ]
            );
        }

        $cmd = '(' . implode(' ', $cmd) . ')';

        $oscmd = [];
        $oscmd[] = '"' . $cmdExeFile . '"';
        $oscmd[] = "/D /C";
        $oscmd[] = $cmd;
        $oscmd[] = "1>NUL";
        $oscmd[] = "2>NUL";
        $oscmd = implode(' ', $oscmd);

        $spec = [
            [ 'pipe', 'r' ],
            [ 'file', 'NUL', 'w' ],
            [ 'file', 'NUL', 'w' ],
        ];

        $options = [
            'suppress_errors'    => true,
            'bypass_shell'       => true,
            'create_new_console' => true,
        ];

        $ph = @proc_open($oscmd, $spec, $pipes, $cwdRealpath, $env, $options);

        if (false === $ph) {
            throw new FilesystemException(
                [ 'Unable to create process', $oscmd ]
            );
        }

        return true;
    }

    /**
     * @param resource $ph
     */
    protected function spawnUsingProcOpenUnix(
        &$ph,
        array $cmd,
        ?string $cwd = null, ?array $env = null,
        $input = null
    ) : bool
    {
        $oscmd = $cmd;
        $oscmd[] = "> /dev/null";
        $oscmd[] = "&";

        $oscmd = implode(' ', $oscmd);

        $spec = [
            [ 'pipe', 'r' ],
            [ 'pipe', 'w' ],
            [ 'pipe', 'w' ],
        ];

        $options = [
            'suppress_errors'    => true,
            'bypass_shell'       => true,
            'create_new_console' => true,
        ];

        $ph = @proc_open($oscmd, $spec, $pipes, $cwd, $env, $options);

        if (false === $ph) {
            throw new FilesystemException(
                [ 'Unable to create process', $oscmd ]
            );
        }

        return true;
    }
}
