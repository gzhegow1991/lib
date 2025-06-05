<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Cli\Process;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Async\Promise\Promise;
use Gzhegow\Lib\Exception\Runtime\ComposerException;
use Gzhegow\Lib\Exception\Runtime\FilesystemException;


class Proc
{
    const SYMFONY_PROCESS_CLASS = '\Symfony\Component\Process\Process';


    /**
     * @var object|\Symfony\Component\Process\Process
     */
    protected $symfonyProcess;
    /**
     * @var resource
     */
    protected $procOpenResource;

    /**
     * @var bool
     */
    protected $isBackground = false;

    /**
     * @var array|null
     */
    protected $cmd;

    /**
     * @var string|null
     */
    protected $cwd;
    /**
     * @var array|null
     */
    protected $env;

    /**
     * @var array|null
     */
    protected $options = [
        'suppress_errors' => true,
        'bypass_shell'    => true,
    ];
    /**
     * @var array|null
     */
    protected $optionsForce = [
        'suppress_errors' => true,
        'bypass_shell'    => true,
    ];

    /**
     * @var string|null
     */
    protected $stdinFile;
    /**
     * @var resource|null
     */
    protected $stdinResource;
    /**
     * @var iterable|null
     */
    protected $stdinIterable;

    /**
     * @var string|null
     */
    protected $stdoutFile;
    /**
     * @var string|null
     */
    protected $stdoutRef;
    /**
     * @var string|null
     */
    protected $stdoutRefFile;
    /**
     * @var string|null
     */
    protected $stdoutRefFileResource;
    /**
     * @var resource|null
     */
    protected $stdoutResource;

    /**
     * @var string|null
     */
    protected $stderrFile;
    /**
     * @var string|null
     */
    protected $stderrRef;
    /**
     * @var string|null
     */
    protected $stderrRefFile;
    /**
     * @var string|null
     */
    protected $stderrRefFileResource;
    /**
     * @var resource|null
     */
    protected $stderrResource;

    /**
     * @var int|null
     */
    protected $timeoutMs;

    /**
     * @var array{ 0: resource, 1: resource, 2: resource }
     */
    protected $procOpenPipes;


    /**
     * @return \Symfony\Component\Process\Process
     */
    protected function newSymfonyProcess() : object
    {
        $commands = [
            'composer require symfony/process',
        ];

        if (! class_exists($symfonyProcessClass = static::SYMFONY_PROCESS_CLASS)) {
            throw new ComposerException(
                [
                    ''
                    . 'Please, run following commands: '
                    . '[ ' . implode(' ][ ', $commands) . ' ]',
                ]
            );
        }

        $timeoutSeconds = null;
        if (null !== $this->timeoutMs) {
            $timeoutSeconds = $this->timeoutMs / 1000;
        }

        $input = $this->stdinResource ?? $this->stdinFile ?? $this->stdinIterable;

        $process = new $symfonyProcessClass(
            $this->cmd,
            $this->cwd,
            $this->env,
            $input,
            $timeoutSeconds
        );

        if (null !== $this->cwd) {
            $process->setWorkingDirectory($this->cwd);
        }

        $options = $this->options;

        // > symfony team had disabled following options and uses them by default
        unset($options[ 'suppress_errors' ]);
        unset($options[ 'bypass_shell' ]);

        $process->setOptions($options);

        return $process;
    }


    /**
     * @return static
     */
    public function setIsBackground(?bool $isBackground)
    {
        if (false
            || $this->stdoutRefFile
            || $this->stdoutResource
            //
            || $this->stderrRefFile
            || $this->stderrResource
        ) {
            throw new LogicException(
                [ 'The `stdout`/`stderr` cannot be reference or resource if `isBackground` is set to true', $this ]
            );
        }

        $this->isBackground = $isBackground ?? false;

        return $this;
    }


    /**
     * @return static
     */
    public function setCmd($cmd)
    {
        $theType = Lib::type();

        $cmdList = [];
        foreach ( (array) $cmd as $i => $c ) {
            if (! $theType->trim($cString, $c)) {
                throw new LogicException(
                    [ 'Unable to cast command to string', $c, $i, $cmd ]
                );
            }

            $cmdList[] = $c;
        }

        $this->cmd = $cmdList;

        return $this;
    }


    /**
     * @return static
     */
    public function setCwd(?string $cwd)
    {
        if (null !== ($cwdRealpath = $cwd)) {
            if (! Lib::type()->dirpath_realpath($cwdRealpath, $cwd, true)) {
                throw new LogicException(
                    [ 'The `cwd` should be existing directory', $cwd ]
                );
            }
        }

        $this->cwd = $cwd;

        return $this;
    }

    /**
     * @return static
     */
    public function setEnv(?array $env)
    {
        if (null !== $env) {
            if (! is_array($env)) {
                throw new LogicException(
                    [ 'The `env` should be array', $env ]
                );
            }
        }

        $this->env = $env;

        return $this;
    }


    /**
     * @return static
     */
    public function setOptions(?array $options)
    {
        if (null !== $options) {
            if (! is_array($options)) {
                throw new LogicException(
                    [ 'The `options` should be array', $options ]
                );
            }
        }

        $this->options = []
            + $this->optionsForce
            + ($options ?? []);

        return $this;
    }


    /**
     * @param resource|string|null $stdin
     *
     * @return static
     */
    public function setStdin($stdin)
    {
        if (is_resource($stdin)) {
            $this->stdinResource = $stdin;

        } elseif (is_iterable($stdin)) {
            $this->stdinIterable = $stdin;

        } elseif (Lib::type()->filepath_realpath($stdinPath, $stdin, true)) {
            $this->stdinFile = $stdinPath;

        } elseif (Lib::type()->string($stdinString, $stdin)) {
            $this->stdinIterable = [ $stdinString ];

        } else {
            throw new LogicException(
                [ 'The `stdin` should be resource, filepath or string', $stdin ]
            );
        }

        return $this;
    }


    /**
     * @param string|null $stdoutFile
     *
     * @return static
     */
    public function setStdoutFile($stdoutFile)
    {
        if (null !== ($stdoutFilePath = $stdoutFile)) {
            if ($this->isBackground) {
                throw new LogicException(
                    [ 'The `stdout`/`stderr` cannot be reference or resource if `isBackground` is set to true', $this ]
                );
            }

            if (! Lib::type()->filepath($stdoutFilePath, $stdoutFile, true)) {
                throw new LogicException(
                    [ 'The `stdout` should be resource', $stdoutFile ]
                );
            }

            $stdoutFilePath = Lib::fs()->path_normalize($stdoutFilePath, DIRECTORY_SEPARATOR);
        }

        $this->stdoutFile = $stdoutFilePath;

        return $this;
    }

    /**
     * @param string|null $refStdout
     *
     * @return static
     */
    public function setStdoutRef(&$refStdout = null)
    {
        $refStdout = null;

        if ($this->isBackground) {
            throw new LogicException(
                [ 'The `stdout`/`stderr` cannot be reference or resource if `isBackground` is set to true', $this ]
            );
        }

        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proc_open_stdout_' . ((new \DateTime())->format('Ymd_His_u')) . '.log';
        $tmpFile = Lib::fs()->path_normalize($tmpFile, DIRECTORY_SEPARATOR);

        $this->stdoutRef =& $refStdout;
        $this->stdoutRefFile = $tmpFile;

        return $this;
    }

    /**
     * @param resource|null $stdoutResource
     *
     * @return static
     */
    public function setStdoutResource($stdoutResource)
    {
        if (null !== $stdoutResource) {
            if ($this->isBackground) {
                throw new LogicException(
                    [ 'The `stdout` cannot be reference if `isBackground` is set to true', $stdoutResource ]
                );
            }

            if (! is_resource($stdoutResource)) {
                throw new LogicException(
                    [ 'The `stdout` should be resource', $stdoutResource ]
                );
            }
        }

        $this->stdoutResource = $stdoutResource;

        return $this;
    }


    /**
     * @param string|null $stderrFile
     *
     * @return static
     */
    public function setStderrFile($stderrFile)
    {
        if (null !== ($stderrFilePath = $stderrFile)) {
            if ($this->isBackground) {
                throw new LogicException(
                    [ 'The `stdout`/`stderr` cannot be reference or resource if `isBackground` is set to true', $this ]
                );
            }

            if (! Lib::type()->filepath($stderrFilePath, $stderrFile, true)) {
                throw new LogicException(
                    [ 'The `stdout` should be resource', $stderrFile ]
                );
            }

            $stderrFilePath = Lib::fs()->path_normalize($stderrFilePath, DIRECTORY_SEPARATOR);
        }

        $this->stderrFile = $stderrFilePath;

        return $this;
    }

    /**
     * @param string|null $refStderr
     *
     * @return static
     */
    public function setStderrRef(&$refStderr = null)
    {
        $refStderr = null;

        if ($this->isBackground) {
            throw new LogicException(
                [ 'The `stdout`/`stderr` cannot be reference or resource if `isBackground` is set to true', $this ]
            );
        }

        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proc_open_stderr_' . ((new \DateTime())->format('Ymd_His_u')) . '.log';
        $tmpFile = Lib::fs()->path_normalize($tmpFile, DIRECTORY_SEPARATOR);

        $this->stderrRef =& $refStderr;
        $this->stderrRefFile = $tmpFile;

        return $this;
    }

    /**
     * @param resource|null $stderrResource
     *
     * @return static
     */
    public function setStderrResource($stderrResource)
    {
        if (null !== $stderrResource) {
            if ($this->isBackground) {
                throw new LogicException(
                    [ 'The `stdout` cannot be reference if `isBackground` is set to true', $stderrResource ]
                );
            }

            if (! is_resource($stderrResource)) {
                throw new LogicException(
                    [ 'The `stdout` should be resource', $stderrResource ]
                );
            }
        }

        $this->stdoutResource = $stderrResource;

        return $this;
    }


    /**
     * @param int|null $timeoutMs
     *
     * @return static
     */
    public function setTimeoutMs(?int $timeoutMs = null)
    {
        if (null !== ($timeoutMsInt = $timeoutMs)) {
            if (! Lib::type()->int_positive($timeoutMsInt, $timeoutMs)) {
                throw new LogicException(
                    [ 'The `timeoutMs` should be integer positive', $timeoutMs ]
                );
            }
        }

        $this->timeoutMs = $timeoutMsInt;

        return $this;
    }


    /**
     * @return \Symfony\Component\Process\Process
     */
    public function spawnUsingSymfonyProcess()
    {
        $this->validateSpawn();

        $process = $this->newSymfonyProcess();

        $process->start();

        $this->symfonyProcess = $process;

        return $process;
    }

    /**
     * @return resource|null
     */
    public function spawnUsingProcOpenWindows()
    {
        $this->validateSpawn();

        $theType = Lib::type();

        $dirSystemRoot = getenv('SystemRoot');

        if (! $theType->dirpath_realpath($dirSystemRootRealpath, $dirSystemRoot)) {
            throw new LogicException(
                [ 'The `dirSystemRoot` should be existing directory', $dirSystemRoot ]
            );
        }

        $cmdExeFile = $dirSystemRootRealpath . "\\System32\\cmd.exe";

        if (! $theType->filepath_realpath($cmdExeFileRealpath, $cmdExeFile)) {
            throw new LogicException(
                [ 'The `cmdExeFile` should be existing file', $cmdExeFile ]
            );
        }

        $cmdString = implode(' ', $this->cmd);

        $oscmd = [];
        $oscmd[] = '"' . $cmdExeFile . '" /D /C';
        $oscmd[] = '(' . $cmdString . ')';

        if (false
            || $this->stdoutFile
            || $this->stdoutRefFile
        ) {
            $file = $this->stdoutFile ?? $this->stdoutRefFile;

            $oscmd[] = '1>"' . $file . '"';

        } elseif ($this->isBackground) {
            $oscmd[] = "1>NUL";
        }

        if (false
            || $this->stderrFile
            || $this->stderrRefFile
        ) {
            $file = $this->stderrFile ?? $this->stderrRefFile;

            $oscmd[] = '2>"' . $file . '"';

        } elseif ($this->isBackground) {
            $oscmd[] = "2>NUL";
        }

        $oscmd = implode(' ', $oscmd);

        $spec = [];
        $pipes = [];

        if ($this->stdinResource) {
            $spec[ 0 ] = [ 'pipe', 'r' ];

        } elseif ($this->stdinFile) {
            $spec[ 0 ] = [ 'file', $this->stdinFile, 'r' ];

        } elseif ($this->stdinIterable) {
            $spec[ 0 ] = [ 'pipe', 'r' ];

        } else {
            $spec[ 0 ] = [ 'pipe', 'r' ];
        }

        if ($this->stdoutResource) {
            $spec[ 1 ] = [ 'pipe', 'w' ];

        } elseif ($this->stdoutRef) {
            $spec[ 1 ] = [ 'file', $this->stdoutRefFile, 'w' ];

        } elseif ($this->stdoutFile) {
            $spec[ 1 ] = [ 'file', $this->stdoutFile, 'w' ];

        } elseif ($this->isBackground) {
            $spec[ 1 ] = [ 'file', 'NUL', 'w' ];

        } else {
            $spec[ 1 ] = [ 'pipe', 'w' ];
        }

        if ($this->stderrResource) {
            $spec[ 2 ] = [ 'pipe', 'w' ];

        } elseif ($this->stderrRef) {
            $spec[ 2 ] = [ 'file', $this->stderrRefFile, 'w' ];

        } elseif ($this->stderrFile) {
            $spec[ 2 ] = [ 'file', $this->stderrFile, 'w' ];

        } elseif ($this->isBackground) {
            $spec[ 2 ] = [ 'file', 'NUL', 'w' ];

        } else {
            $spec[ 2 ] = [ 'pipe', 'w' ];
        }

        $cwd = $this->cwd;
        $env = $this->env;
        $options = $this->options;

        try {
            $ph = Lib::func()->safe_call(
                'proc_open',
                [ $oscmd, $spec, &$pipes, $cwd, $env, $options ],
            );

            $this->procOpenResource = $ph;
            $this->procOpenPipes =& $pipes;

            if ($this->stdinResource) {
                stream_copy_to_stream($this->stdinResource, $pipes[ 0 ]);

            } elseif ($this->stdinIterable) {
                foreach ( $this->stdinIterable as $line ) {
                    if (! $theType->string($lineString, $line)) {
                        throw new LogicException(
                            [ 'The `stdinIterable` should be iterable of strings', $line ]
                        );
                    }

                    fwrite($pipes[ 0 ], $line);
                }

                fclose($pipes[ 0 ]);
            }
        }
        catch ( \Throwable $e ) {
            throw new FilesystemException(
                [ 'Unable to create process: ' . $e->getMessage(), $this ], $e
            );
        }

        return $ph;
    }

    /**
     * @return resource|null
     */
    public function spawnUsingProcOpenUnix()
    {
        $this->validateSpawn();

        $theType = Lib::type();

        $cmdString = implode(' ', $this->cmd);

        $oscmd = [];
        $oscmd[] = $cmdString;

        if (false
            || $this->stdoutFile
            || $this->stdoutRefFile
        ) {
            $file = $this->stdoutFile ?? $this->stdoutRefFile;

            $oscmd[] = '> "' . $file . '"';

        } elseif ($this->isBackground) {
            $oscmd[] = "> /dev/null";
        }

        if (false
            || $this->stderrFile
            || $this->stderrRefFile
        ) {
            $file = $this->stderrFile ?? $this->stderrRefFile;

            $oscmd[] = '2> "' . $file . '"';

        } elseif ($this->isBackground) {
            $oscmd[] = "2> /dev/null";
        }

        if ($this->isBackground) {
            $oscmd[] = "&";
        }

        $oscmd = implode(' ', $oscmd);

        $spec = [];
        $pipes = [];

        if ($this->stdinResource) {
            $spec[ 0 ] = [ 'pipe', 'r' ];

        } elseif ($this->stdinFile) {
            $spec[ 0 ] = [ 'file', $this->stdinFile, 'r' ];

        } elseif ($this->stdinIterable) {
            $spec[ 0 ] = [ 'pipe', 'r' ];

        } else {
            $spec[ 0 ] = [ 'pipe', 'r' ];
        }

        if ($this->stdoutResource) {
            $spec[ 1 ] = [ 'pipe', 'w' ];

        } elseif ($this->stdoutRef) {
            $spec[ 1 ] = [ 'file', $this->stdoutRefFile, 'w' ];

        } elseif ($this->stdoutFile) {
            $spec[ 1 ] = [ 'file', $this->stdoutFile, 'w' ];

        } else {
            $spec[ 1 ] = [ 'pipe', 'w' ];
        }

        if ($this->stderrResource) {
            $spec[ 2 ] = [ 'pipe', 'w' ];

        } elseif ($this->stderrRef) {
            $spec[ 2 ] = [ 'file', $this->stderrRefFile, 'w' ];

        } elseif ($this->stderrFile) {
            $spec[ 2 ] = [ 'file', $this->stderrFile, 'w' ];

        } else {
            $spec[ 2 ] = [ 'pipe', 'w' ];
        }

        $cwd = $this->cwd;
        $env = $this->env;
        $options = $this->options;

        try {
            $ph = Lib::func()->safe_call(
                'proc_open',
                [ $oscmd, $spec, &$pipes, $cwd, $env, $options ],
            );

            $this->procOpenResource = $ph;
            $this->procOpenPipes =& $pipes;

            if ($this->stdinResource) {
                stream_copy_to_stream($this->stdinResource, $pipes[ 0 ]);

            } elseif ($this->stdinIterable) {
                foreach ( $this->stdinIterable as $line ) {
                    if (! $theType->string($lineString, $line)) {
                        throw new LogicException(
                            [ 'The `stdinIterable` should be iterable of strings', $line ]
                        );
                    }

                    fwrite($pipes[ 0 ], $line);
                }

                fclose($pipes[ 0 ]);
            }
        }
        catch ( \Throwable $e ) {
            throw new FilesystemException(
                [ 'Unable to create process: ' . $e->getMessage(), $this ], $e
            );
        }

        return $ph;
    }


    /**
     * @param \Symfony\Component\Process\Process|null $refSymfonyProcess
     */
    public function hasSymfonyProcess(&$refSymfonyProcess = null) : bool
    {
        if (null !== $this->symfonyProcess) {
            $refSymfonyProcess = $this->symfonyProcess;

            return true;
        }

        return false;
    }

    /**
     * @return \Symfony\Component\Process\Process
     */
    public function getSymfonyProcess() : object
    {
        return $this->symfonyProcess;
    }


    /**
     * @param resource|null $refProcOpenRsource
     */
    public function hasProcOpenResource(&$refProcOpenRsource = null) : bool
    {
        if (null !== $this->procOpenResource) {
            $refProcOpenRsource = $this->procOpenResource;

            return true;
        }

        return false;
    }

    /**
     * @return resource
     */
    public function getProcOpenResource()
    {
        if (null !== $this->procOpenResource) {
            throw new RuntimeException(
                [ 'The `procOpenResource` should be resource' ]
            );
        }

        return $this->procOpenResource;
    }


    public function isRunning() : bool
    {
        if ($this->symfonyProcess) {
            return $this->symfonyProcess->isRunning();

        } elseif ($this->procOpenResource) {
            $status = proc_get_status($this->procOpenResource);

            return $status[ 'running' ];
        }

        return false;
    }


    public function wait($tickUsleep = null, $timeoutMs = null, ?\Closure $fnWait = null) : int
    {
        $tickUsleep = $tickUsleep ?? 100000;

        if ($this->symfonyProcess) {
            $process = $this->symfonyProcess;

            $fnTick = function ($ctx) use ($fnWait, $process) {
                if ($this->stdoutRefFile) {
                    $this->stdoutRef .= $process->getIncrementalOutput();

                } elseif ($this->stdoutResource) {
                    fwrite($this->stdoutResource, $process->getIncrementalOutput());
                }

                if ($this->stderrRefFile) {
                    $this->stderrRef = $process->getIncrementalErrorOutput();

                } elseif ($this->stderrResource) {
                    fwrite($this->stderrResource, $process->getIncrementalErrorOutput());
                }


                if (null !== $fnWait) {
                    call_user_func_array($fnWait, [ $process ]);
                }

                if ($process->isRunning()) {
                    return;
                }


                if ($this->stdoutFile) {
                    file_put_contents($this->stdoutFile, $process->getOutput());
                }

                if ($this->stderrFile) {
                    file_put_contents($this->stderrFile, $process->getErrorOutput());
                }


                $ctx->setResult($process->getExitCode());
            };

        } else {
            $ph = $this->procOpenResource;

            $fnTick = function ($ctx) use ($fnWait, $ph) {
                $status = proc_get_status($ph);


                if ($this->stdoutRefFile && is_file($this->stdoutRefFile)) {
                    $this->stdoutRefFileResource = $this->stdoutRefFileResource ?? fopen($this->stdoutRefFile, 'r');
                    $this->stdoutRef .= stream_get_contents($this->stdoutRefFileResource);
                }

                if ($this->stderrRefFile && is_file($this->stderrRefFile)) {
                    $this->stderrRefFileResource = $this->stderrRefFileResource ?? fopen($this->stderrRefFile, 'r');
                    $this->stderrRef .= stream_get_contents($this->stderrRefFileResource);
                }


                if (null !== $fnWait) {
                    call_user_func_array($fnWait, [ $ph, $status ]);
                }

                if ($status[ 'running' ]) {
                    return;
                }


                if ($this->stdoutRefFile && is_file($this->stdoutRefFile)) {
                    fclose($this->stderrRefFileResource);
                    unlink($this->stdoutRefFile);

                    unset($this->stdoutRefFile);
                }

                if ($this->stderrRefFile && is_file($this->stderrRefFile)) {
                    unlink($this->stderrRefFile);

                    unset($this->stderrRefFile);
                }


                $ctx->setResult($status[ 'exitcode' ]);
            };
        }

        $exitCode = Lib::php()->poolingSync(
            $tickUsleep, $timeoutMs,
            //
            $fnTick
        );

        return $exitCode;
    }

    public function waitAsync($tickMs = null, $timeoutMs = null, ?\Closure $fnWait = null) : Promise
    {
        $tickMs = $tickMs ?? 100;

        if ($this->symfonyProcess) {
            $process = $this->symfonyProcess;

            $fnTick = function ($fnResolve) use ($fnWait, $process) {
                if ($this->stdoutRefFile) {
                    $this->stdoutRef .= $process->getIncrementalOutput();

                } elseif ($this->stdoutResource) {
                    fwrite($this->stdoutResource, $process->getIncrementalOutput());
                }

                if ($this->stderrRefFile) {
                    $this->stderrRef = $process->getIncrementalErrorOutput();

                } elseif ($this->stderrResource) {
                    fwrite($this->stderrResource, $process->getIncrementalErrorOutput());
                }


                if (null !== $fnWait) {
                    call_user_func_array($fnWait, [ $process ]);
                }

                if ($process->isRunning()) {
                    return;
                }


                if ($this->stdoutFile) {
                    file_put_contents($this->stdoutFile, $process->getOutput());
                }

                if ($this->stderrFile) {
                    file_put_contents($this->stderrFile, $process->getErrorOutput());
                }


                $fnResolve($process->getExitCode());
            };

        } else {
            $ph = $this->procOpenResource;

            $fnTick = function ($ctx) use ($fnWait, $ph) {
                $status = proc_get_status($ph);


                if ($this->stdoutRefFile && is_file($this->stdoutRefFile)) {
                    $this->stdoutRefFileResource = $this->stdoutRefFileResource ?? fopen($this->stdoutRefFile, 'r');
                    $this->stdoutRef .= stream_get_contents($this->stdoutRefFileResource);
                }

                if ($this->stderrRefFile && is_file($this->stderrRefFile)) {
                    $this->stderrRefFileResource = $this->stderrRefFileResource ?? fopen($this->stderrRefFile, 'r');
                    $this->stderrRef .= stream_get_contents($this->stderrRefFileResource);
                }


                if (null !== $fnWait) {
                    call_user_func_array($fnWait, [ $ph, $status ]);
                }

                if ($status[ 'running' ]) {
                    return;
                }


                $ctx->setResult($status[ 'exitcode' ]);
            };
        }

        return Promise::pooling($tickMs, $timeoutMs, $fnTick);
    }


    protected function validateSpawn() : void
    {
        if ($this->isBackground
            && (false
                || $this->stdoutRef
                || $this->stdoutResource
            )
        ) {
            throw new LogicException(
                [ 'The `stdout` cannot be reference or file when `isBackground` is set to TRUE', $this ]
            );
        }

        if ($this->isBackground
            && (false
                || $this->stderrRef
                || $this->stderrResource
            )
        ) {
            throw new LogicException(
                [ 'The `stderr` cannot be reference or file when `isBackground` is set to TRUE', $this ]
            );
        }
    }
}
