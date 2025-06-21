<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Arr\Map\Map;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Arr\Map\Base\AbstractMap;


class EntrypointModule
{
    /**
     * @var bool
     */
    protected $isLocked = false;

    /**
     * @var string
     */
    protected $dirRoot;

    /**
     * @var int
     */
    protected $errorReporting;
    /**
     * @var int
     */
    protected $displayErrors = 0;

    /**
     * @var string
     */
    protected $memoryLimit = '32M';

    /**
     * @var int
     */
    protected $maxExecutionTime = 30;
    /**
     * @var int
     */
    protected $maxInputTime = -1;
    /**
     * @var \DateTimeZone
     */
    protected $timezoneDefault;

    /**
     * @var string
     */
    protected $postMaxSize = '5M';

    /**
     * @var string
     */
    protected $uploadMaxFilesize = '2M';
    /**
     * @var string
     */
    protected $uploadTmpDir;

    /**
     * @var int
     */
    protected $umask = 0002;

    /**
     * @var int
     */
    protected $precision = 16;

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
        $this->errorReporting = (E_ALL | E_DEPRECATED | E_USER_DEPRECATED);

        $this->timezoneDefault = new \DateTimeZone('UTC');

        $this->uploadTmpDir = sys_get_temp_dir();

        $this->fnErrorHandler = [ $this, 'fnErrorHandler' ];
        $this->fnExceptionHandler = [ $this, 'fnExceptionHandler' ];

        $this->registerShutdownFunctionMap = Map::new();
    }


    /**
     * @param bool $isLocked
     *
     * @return static
     */
    public function lock(bool $isLocked)
    {
        $this->isLocked = $isLocked;

        return $this;
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
        $this->assertNotLocked();

        if (null !== $dirRoot) {
            if (! Lib::fs()->type_dirpath_realpath($realpath, $dirRoot)) {
                throw new LogicException(
                    [ 'The `dirRoot` should be an existing directory path', $dirRoot ]
                );
            }
        }

        $this->dirRoot = $realpath ?? null;

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
        $this->assertNotLocked();

        if (null !== $errorReporting) {
            if (-1 === $errorReporting) {
                $errorReporting = (E_ALL | E_DEPRECATED | E_USER_DEPRECATED);

            } elseif (($errorReporting & ~(E_ALL | E_DEPRECATED | E_USER_DEPRECATED)) !== 0) {
                throw new LogicException(
                    [ 'The `errorReporting` should be a valid `error_reporting` flag', $errorReporting ]
                );
            }
        }

        $this->errorReporting = $errorReporting;

        return $this;
    }

    /**
     * @return static
     */
    public function useErrorReporting(&$refLast = null)
    {
        if (null === $this->errorReporting) {
            return $this;
        }

        $refLast = error_reporting($this->errorReporting);

        return $this;
    }


    /**
     * @return string|false
     */
    public function getPhpDisplayErrors(string $displayErrorsTmp = '0')
    {
        $before = ini_set('display_errors', $displayErrorsTmp);

        ini_set('display_errors', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setDisplayErrors(?bool $displayErrors = null)
    {
        $this->assertNotLocked();

        if (null !== $displayErrors) {
            $displayErrors = (int) $displayErrors;
        }

        $this->displayErrors = $displayErrors;

        return $this;
    }

    /**
     * @return static
     */
    public function useDisplayErrors(
        &$refLastDisplayErrors = null,
        &$refLastDisplayStartupErrors = null
    )
    {
        if (null === $this->displayErrors) {
            return $this;
        }

        $refLastDisplayErrors = ini_set('display_errors', $this->displayErrors);
        $refLastDisplayStartupErrors = ini_set('display_startup_errors', $this->displayErrors);

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
        $this->assertNotLocked();

        if (null !== $memoryLimit) {
            $theFormat = Lib::format();

            $bytes = $theFormat->bytes_decode($memoryLimit);
            $bytesString = $theFormat->bytes_encode($bytes, 0, 1);

            $memoryLimit = $bytesString;
        }

        $this->memoryLimit = $memoryLimit;

        return $this;
    }

    /**
     * @return static
     */
    public function useMemoryLimit(&$refLast = null)
    {
        if (null === $this->memoryLimit) {
            return $this;
        }

        $refLast = ini_set('memory_limit', $this->memoryLimit);

        return $this;
    }


    public function getPhpMaxExecutionTime(int $maxInputTimeTmp = 30) : string
    {
        $before = ini_set('max_execution_time', $maxInputTimeTmp);

        ini_set('max_execution_time', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setMaxExecutionTime(?int $maxExecutionTime = null)
    {
        $this->assertNotLocked();

        if (null !== ($maxExecutionTimeInt = $maxExecutionTime)) {
            Lib::typeThrow()->int_non_negative($maxExecutionTimeInt, $maxExecutionTime);
        }

        $this->maxExecutionTime = $maxExecutionTimeInt;

        return $this;
    }

    /**
     * @return static
     */
    public function useMaxExecutionTime(&$refLast = null)
    {
        if (null === $this->maxExecutionTime) {
            return $this;
        }

        $refLast = ini_set('max_execution_time', $this->maxExecutionTime);

        return $this;
    }


    public function getPhpMaxInputTime(int $maxInputTimeTmp = 30) : string
    {
        $before = ini_set('max_input_time', $maxInputTimeTmp);

        ini_set('max_input_time', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setMaxInputTime(?int $maxInputTime = null)
    {
        $this->assertNotLocked();

        if (null !== ($maxInputTimeInt = $maxInputTime)) {
            Lib::typeThrow()->int_non_negative_or_minus_one($maxInputTimeInt, $maxInputTime);
        }

        $this->maxInputTime = $maxInputTimeInt;

        return $this;
    }

    /**
     * @return static
     */
    public function useMaxInputTime(&$refLast = null)
    {
        if (null === $this->maxInputTime) {
            return $this;
        }

        $refLast = ini_set('max_input_time', $this->maxInputTime);

        return $this;
    }


    public function getPhpTimezoneDefault() : string
    {
        return date_default_timezone_get();
    }

    /**
     * @return static
     */
    public function setTimezoneDefault($timezoneDefault = null)
    {
        $this->assertNotLocked();

        if (null !== $timezoneDefault) {
            Lib::typeThrow()->timezone($timezoneDefaultTz, $timezoneDefault);
        }

        $this->timezoneDefault = $timezoneDefaultTz ?? $timezoneDefault;

        return $this;
    }

    /**
     * @return static
     */
    public function useTimezoneDefault(&$refLast = null)
    {
        if (null === $this->timezoneDefault) {
            return $this;
        }

        $refLast = date_default_timezone_set($this->timezoneDefault->getName());

        return $this;
    }


    public function getPhpPostMaxSize(string $postMaxSizeTmp = '5M') : string
    {
        $before = ini_set('post_max_size', $postMaxSizeTmp);

        ini_set('post_max_size', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setPostMaxSize(?string $postMaxSize = null)
    {
        $this->assertNotLocked();

        if (null !== $postMaxSize) {
            $theFormat = Lib::format();

            $bytes = $theFormat->bytes_decode($postMaxSize);
            $bytesString = $theFormat->bytes_encode($bytes, 0, 1);

            $postMaxSize = $bytesString;
        }

        $this->postMaxSize = $postMaxSize;

        return $this;
    }

    /**
     * @return static
     */
    public function usePostMaxSize(&$refLast = null)
    {
        if (null === $this->postMaxSize) {
            return $this;
        }

        $refLast = ini_set('post_max_size', $this->postMaxSize);

        return $this;
    }


    public function getPhpUploadMaxFilesize(string $uploadMaxFilesizeTmp = '2M') : string
    {
        $before = ini_set('upload_max_filesize', $uploadMaxFilesizeTmp);

        ini_set('upload_max_filesize', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setUploadMaxFilesize(?string $uploadMaxFilesize = null)
    {
        $this->assertNotLocked();

        if (null !== $uploadMaxFilesize) {
            $theFormat = Lib::format();

            $bytes = $theFormat->bytes_decode($uploadMaxFilesize);
            $bytesString = $theFormat->bytes_encode($bytes, 0, 1);

            $uploadMaxFilesize = $bytesString;
        }

        $this->uploadMaxFilesize = $uploadMaxFilesize;

        return $this;
    }

    /**
     * @return static
     */
    public function useUploadMaxFilesize(&$refLast = null)
    {
        if (null === $this->uploadMaxFilesize) {
            return $this;
        }

        $refLast = ini_set('upload_max_filesize', $this->uploadMaxFilesize);

        return $this;
    }


    public function getPhpUploadTmpDir() : string
    {
        $before = ini_set('upload_tmp_dir', sys_get_temp_dir());

        ini_set('upload_tmp_dir', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setUploadTmpDir(?string $uploadTmpDir = null)
    {
        $this->assertNotLocked();

        if (null !== $uploadTmpDir) {
            if (! Lib::fs()->type_dirpath_realpath($realpath, $uploadTmpDir)) {
                throw new LogicException(
                    [ 'The `uploadTmpDir` should be an existing directory path', $uploadTmpDir ]
                );
            }
        }

        $this->uploadTmpDir = $realpath ?? null;

        return $this;
    }

    /**
     * @return static
     */
    public function useUploadTmpDir(&$refLast = null)
    {
        if (null === $this->uploadTmpDir) {
            return $this;
        }

        $refLast = ini_set('upload_tmp_dir', $this->uploadTmpDir);

        return $this;
    }


    public function getPhpPrecision(int $precisionTmp = 16) : string
    {
        $before = ini_set('precision', $precisionTmp);

        ini_set('precision', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setPrecision(?int $precision = null)
    {
        $this->assertNotLocked();

        if (null !== ($precisionInt = $precision)) {
            Lib::typeThrow()->int_non_negative($precisionInt, $precision);
        }

        $this->precision = $precisionInt;

        return $this;
    }

    /**
     * @return static
     */
    public function usePrecision(&$refLast = null)
    {
        if (null === $this->precision) {
            return $this;
        }

        $refLast = ini_set('precision', $this->precision);

        return $this;
    }


    public function getPhpUmask(int $umaskTmp = 0002) : string
    {
        $before = umask($umaskTmp);

        umask($before);

        return $before;
    }

    /**
     * @return static
     */
    public function setUmask(?int $umask = null)
    {
        $this->assertNotLocked();

        if (null !== $umask) {
            if (! (($umask >= 0) && ($umask <= 0777))) {
                throw new LogicException(
                    [ 'The `umask` should be a valid `umask`', $umask ]
                );
            }
        }

        $this->umask = $umask;

        return $this;
    }

    /**
     * @return static
     */
    public function useUmask(&$refLast = null)
    {
        if (null === $this->umask) {
            return $this;
        }

        $refLast = umask($this->umask);

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
        $this->assertNotLocked();

        if (null !== $fnErrorHandler) {
            if ('' === $fnErrorHandler) {
                $fnErrorHandler = [ $this, 'fnErrorHandler' ];

            } elseif (! is_callable($fnErrorHandler)) {
                throw new LogicException(
                    [ 'The `fnErrorHandler` should be a callable', $fnErrorHandler ]
                );
            }
        }

        $this->fnErrorHandler = $fnErrorHandler;

        return $this;
    }

    /**
     * @param callable|null $refLast
     *
     * @return static
     */
    public function useErrorHandler(&$refLast = null)
    {
        if (null === $this->fnErrorHandler) {
            return $this;
        }

        $refLast = set_error_handler($this->fnErrorHandler);

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
        $this->assertNotLocked();

        if (null !== $fnExceptionHandler) {
            if ('' === $fnExceptionHandler) {
                $fnExceptionHandler = [ $this, 'fnErrorHandler' ];

            } elseif (! is_callable($fnExceptionHandler)) {
                throw new LogicException(
                    [ 'The `fnExceptionHandler` should be a callable', $fnExceptionHandler ]
                );
            }
        }

        $this->fnExceptionHandler = $fnExceptionHandler;

        return $this;
    }

    /**
     * @param callable|null $refLast
     *
     * @return static
     */
    public function useExceptionHandler(&$refLast = null)
    {
        if (null === $this->fnExceptionHandler) {
            return $this;
        }

        $refLast = set_exception_handler($this->fnExceptionHandler);

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
            0
            | _DEBUG_THROWABLE_WITH_CODE
            | _DEBUG_THROWABLE_WITH_FILE
            | _DEBUG_THROWABLE_WITH_OBJECT_CLASS
            | _DEBUG_THROWABLE_WITH_PARENTS
        );

        $traceLines = $tManager->getThrowableTraceLines($throwable);

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
     * @return static
     */
    public function useAllErrorReporting()
    {
        $this
            ->useDisplayErrors()
            ->useErrorReporting()
            ->useErrorHandler()
            ->useExceptionHandler()
        ;

        return $this;
    }


    protected function assertNotLocked() : void
    {
        if ($this->isLocked) {
            throw new RuntimeException('Unable to change `entrypoint` due to it was locked before');
        }
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
