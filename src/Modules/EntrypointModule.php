<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Arr\Map\Map;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Arr\Map\Base\AbstractMap;


class EntrypointModule
{
    /**
     * @var string
     */
    protected $dirRoot;

    /**
     * @var string
     */
    protected $memoryLimit = '32M';
    /**
     * @var int
     */
    protected $timeLimit = 30;

    /**
     * @var int
     */
    protected $errorReporting;

    /**
     * @var callable|null
     */
    protected $fnErrorHandler;
    /**
     * @var callable|null
     */
    protected $fnExceptionHandler;

    /**
     * @var bool
     */
    protected $signalIgnoreShutdownFunction = false;
    /**
     * @var AbstractMap
     */
    protected $registerShutdownFunctionMap;


    public function __construct()
    {
        $this->errorReporting = (E_ALL | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED);

        $this->fnErrorHandler = [ $this, 'fnErrorHandler' ];
        $this->fnExceptionHandler = [ $this, 'fnExceptionHandler' ];

        $this->registerShutdownFunctionMap = Map::new();
    }


    public function getDirRoot() : ?string
    {
        return $this->dirRoot;
    }

    /**
     * @return static
     */
    public function setDirRoot(?string $dirRoot)
    {
        if (null !== $dirRoot) {
            if (! Lib::fs()->type_dirpath_realpath($realpath, $dirRoot)) {
                throw new LogicException(
                    [ 'The `dirRoot` should be existing directory path', $dirRoot ]
                );
            }
        }

        $this->dirRoot = $realpath ?? null;

        return $this;
    }


    public function getPhpMemoryLimit(string $memoryLimitTmp = '32M') : string
    {
        $before = ini_set('memory_limit', $memoryLimitTmp);

        ini_set('memory_limit', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setMemoryLimit(?string $memoryLimit = null)
    {
        $this->memoryLimit = $memoryLimit;

        return $this;
    }

    /**
     * @return static
     */
    public function useMemoryLimit(&$last = null)
    {
        if (null === $this->memoryLimit) {
            return $this;
        }

        $last = ini_set('memory_limit', $this->memoryLimit);

        return $this;
    }


    public function getPhpTimeLimit(int $timeLimitTmp = 30) : string
    {
        $before = ini_set('max_execution_time', $timeLimitTmp);

        ini_set('max_execution_time', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setTimeLimit(?int $timeLimit = null)
    {
        $this->timeLimit = $timeLimit;

        return $this;
    }

    /**
     * @return static
     */
    public function useTimeLimit(&$last = null)
    {
        if (null === $this->timeLimit) {
            return $this;
        }

        $last = ini_set('max_execution_time', $this->timeLimit);
        set_time_limit($this->timeLimit);

        return $this;
    }


    /**
     * @return int|null
     */
    public function getPhpErrorReporting()
    {
        $errorReporting = error_reporting();

        return $errorReporting;
    }

    /**
     * @return static
     */
    public function setErrorReporting(?int $errorReporting = -1)
    {
        if (null !== $errorReporting) {
            if (-1 === $errorReporting) {
                $errorReporting = (E_ALL | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED);

            } elseif (($errorReporting & ~(E_ALL | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED)) !== 0) {
                throw new LogicException(
                    [ 'The `errorReporting` should be valid error_reporting flag', $errorReporting ]
                );
            }
        }

        $this->errorReporting = $errorReporting;

        return $this;
    }

    /**
     * @return static
     */
    public function useErrorReporting(&$last = null)
    {
        if (null === $this->errorReporting) {
            return $this;
        }

        $last = error_reporting($this->errorReporting);

        return $this;
    }


    /**
     * @return callable|null
     */
    public function getPhpErrorHandler()
    {
        $handler = set_error_handler(static function () { });
        restore_error_handler();

        return $handler;
    }

    /**
     * @return callable|null
     */
    public function getErrorHandler()
    {
        return $this->fnErrorHandler;
    }

    /**
     * @return static
     * @var callable $fnErrorHandler
     *
     */
    public function setErrorHandler($fnErrorHandler = '')
    {
        if (null !== $fnErrorHandler) {
            if ('' === $fnErrorHandler) {
                $fnErrorHandler = [ $this, 'fnErrorHandler' ];

            } elseif (! is_callable($fnErrorHandler)) {
                throw new LogicException(
                    [ 'The `fnErrorHandler` should be callable', $fnErrorHandler ]
                );
            }
        }

        $this->fnErrorHandler = $fnErrorHandler;

        return $this;
    }

    /**
     * @param callable|null $last
     *
     * @return static
     */
    public function useErrorHandler(&$last = null)
    {
        if (null === $this->fnErrorHandler) {
            return $this;
        }

        $last = set_error_handler($this->fnErrorHandler);

        return $this;
    }


    /**
     * @return callable|null
     */
    public function getPhpExceptionHandler()
    {
        $handler = set_exception_handler(static function () { });
        restore_exception_handler();

        return $handler;
    }

    /**
     * @return callable|null
     */
    public function getExceptionHandler()
    {
        return $this->fnExceptionHandler;
    }

    /**
     * @return static
     * @var callable $fnExceptionHandler
     *
     */
    public function setExceptionHandler($fnExceptionHandler = '')
    {
        if (null !== $fnExceptionHandler) {
            if ('' === $fnExceptionHandler) {
                $fnExceptionHandler = [ $this, 'fnErrorHandler' ];

            } elseif (! is_callable($fnExceptionHandler)) {
                throw new LogicException(
                    [ 'The `fnExceptionHandler` should be callable', $fnExceptionHandler ]
                );
            }
        }

        $this->fnExceptionHandler = $fnExceptionHandler;

        return $this;
    }

    /**
     * @param callable|null $last
     *
     * @return static
     */
    public function useExceptionHandler(&$last = null)
    {
        if (null === $this->fnExceptionHandler) {
            return $this;
        }

        $last = set_exception_handler($this->fnExceptionHandler);

        return $this;
    }


    /**
     * @throws \ErrorException
     */
    public function fnErrorHandler($errno, $errstr, $errfile, $errline) : void
    {
        if (error_reporting() & $errno) {
            throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
        }
    }

    public function fnExceptionHandler(\Throwable $throwable) : void
    {
        $tManager = Lib::debug()
            ->cloneThrowableManager()
            ->setDirRoot($this->dirRoot)
        ;

        $messageLines = $tManager->getPreviousMessagesLines(
            $throwable,
            [
                'dir_root'       => $this->dirRoot,
                //
                'with_code'      => true,
                //
                'with_file'      => true,
                'with_file_line' => true,
                //
                'with_object'    => true,
                'with_object_id' => false,
                //
                'with_parents'   => true,
            ]
        );

        $traceLines = $tManager->getThrowableTraceLines(
            $throwable,
            [
                'dir_root' => $this->dirRoot,
            ]
        );

        if ([] !== $messageLines) {
            foreach ( $messageLines as $line ) {
                echo $line . "\n";
            }
        }

        if ([] !== $traceLines) {
            echo "\n";

            echo 'Trace: ' . "\n";

            foreach ( $traceLines as $line ) {
                echo $line . "\n";
            }
        }

        exit(1);
    }


    /**
     * @param int|string $status
     */
    public function die($status, ?bool $ignoreShutdownFunction = null)
    {
        $status = $status ?? '';
        $ignoreShutdownFunction = $ignoreShutdownFunction ?? true;

        $this->signalIgnoreShutdownFunction = $ignoreShutdownFunction;

        die($status);
    }

    /**
     * @param int|string $status
     */
    public function exit($status, ?bool $ignoreShutdownFunction = null)
    {
        $status = $status ?? '';
        $ignoreShutdownFunction = $ignoreShutdownFunction ?? true;

        $this->signalIgnoreShutdownFunction = $ignoreShutdownFunction;

        exit($status);
    }

    /**
     * > проверяет наличие функции в списке перед тем, как ее регистрировать, если регистрация функций происходит в цикле
     *
     * @param callable $fn
     */
    public function registerShutdownFunction($fn) : void
    {
        if (! $this->registerShutdownFunctionMap->exists($fn)) {
            $this->registerShutdownFunctionMap->add($fn, true);

            $fnWithSignal = function () use ($fn) {
                if ($this->signalIgnoreShutdownFunction) return;

                call_user_func($fn);
            };

            register_shutdown_function($fnWithSignal);
        }
    }
}
