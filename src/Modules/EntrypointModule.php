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
     * @var array{ 0: string, 1: string }
     */
    protected $isLocked;
    /**
     * @var array<string, bool>
     */
    protected $mapSet = [];

    /**
     * @var string
     */
    protected $dirRoot;

    /**
     * @var int
     */
    protected $errorReporting;
    /**
     * @var string
     */
    protected $errorLog;
    /**
     * @var string
     */
    protected $logErrors = 0;
    /**
     * @var int
     */
    protected $displayErrors = 0;
    /**
     * @var int
     */
    protected $displayStartupErrors = 0;

    /**
     * @var string
     */
    protected $memoryLimit = '32M';

    /**
     * @var int
     */
    protected $maxExecutionTime = 10;
    /**
     * @var int
     */
    protected $maxInputTime = -1;

    /**
     * @var bool
     */
    protected $obImplicitFlush = false;
    /**
     * @var int
     */
    protected $obImplicitFlushCommit = 0;

    /**
     * @var \DateTimeZone
     */
    protected $timezoneDefault;

    /**
     * @var string
     */
    protected $postMaxSize = '8M';

    /**
     * @var string
     */
    protected $uploadMaxFilesize = '2M';
    /**
     * @var string
     */
    protected $uploadTmpDir;
    /**
     * @var bool
     */
    protected $uploadTmpDirMkdir = false;

    /**
     * @var int
     */
    protected $precision = 16;

    /**
     * @var int
     */
    protected $umask = 0002;

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
        $this->errorLog = getcwd() . '/error_log';

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
    public function lock(?bool $isLocked = null)
    {
        $isLocked = $isLocked ?? true;

        if ($isLocked) {
            $this->isLocked = Lib::debug()->file_line();

        } else {
            $this->isLocked = null;
        }

        return $this;
    }


    public function getDirRoot() : ?string
    {
        return $this->dirRoot;
    }

    /**
     * @return static
     */
    public function setDirRoot(?string $dirRoot, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $dirRoot) {
            $this->dirRoot = null;

        } else {
            Lib::typeThrow()->dirpath_realpath($dirRootRealpath, $dirRoot);

            $this->dirRoot = $dirRootRealpath;
        }

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
    public function setErrorReporting(?int $errorReporting, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $errorReporting) {
            $this->errorReporting = (E_ALL | E_DEPRECATED | E_USER_DEPRECATED);

        } else {
            if (-1 === $errorReporting) {
                $errorReporting = (E_ALL | E_DEPRECATED | E_USER_DEPRECATED);

            } elseif (($errorReporting & ~(E_ALL | E_DEPRECATED | E_USER_DEPRECATED)) !== 0) {
                throw new LogicException(
                    [ 'The `errorReporting` should be a valid `error_reporting` flag', $errorReporting ]
                );
            }

            $this->errorReporting = $errorReporting;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useErrorReporting(&$refLast = null)
    {
        $refLast = error_reporting($this->errorReporting);

        return $this;
    }


    /**
     * @return string|false
     */
    public function getPhpLogErrors(string $logErrorsTmp = '0')
    {
        $before = ini_set('log_errors', $logErrorsTmp);

        ini_set('log_errors', $before);

        return $before;
    }

    /**
     * @return string|false
     */
    public function getPhpErrorLog(string $errorLogTmp = '')
    {
        $before = ini_set('error_log', $errorLogTmp);

        ini_set('error_log', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setErrorLog(?string $errorLog, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $errorLog) {
            $this->errorLog = getcwd() . '/error_log';
            $this->logErrors = 0;

        } else {
            Lib::typeThrow()->filepath($errorLogString, $errorLog, true);

            $this->errorLog = $errorLogString;
            $this->logErrors = 1;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useErrorLog(
        &$refLastErrorLog = null,
        &$refLastLogErrors = null
    )
    {
        $refLastErrorLog = ini_set('error_log', $this->errorLog);
        $refLastLogErrors = ini_set('log_errors', $this->logErrors);

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
     * @return string|false
     */
    public function getPhpDisplayStartupErrors(string $displayStartupErrorsTmp = '0')
    {
        $before = ini_set('display_startup_errors', $displayStartupErrorsTmp);

        ini_set('display_startup_errors', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setDisplayErrors(?bool $displayErrors, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $displayErrors) {
            $this->displayErrors = 0;
            $this->displayStartupErrors = 0;

        } else {
            $displayErrorsInt = (int) $displayErrors;

            $this->displayErrors = $displayErrorsInt;
            $this->displayStartupErrors = $displayErrorsInt;
        }

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
    public function setMemoryLimit(?string $memoryLimit, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $memoryLimit) {
            $this->memoryLimit = '32M';

        } else {
            $theFormat = Lib::format();

            $bytesInt = $theFormat->bytes_decode($memoryLimit);
            $bytesString = $theFormat->bytes_encode($bytesInt, 0, 1);

            $this->memoryLimit = $bytesString;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useMemoryLimit(&$refLast = null)
    {
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
    public function setMaxExecutionTime(?int $maxExecutionTime, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $maxExecutionTime) {
            $this->maxExecutionTime = 10;

        } else {
            Lib::typeThrow()->int_non_negative($maxExecutionTimeInt, $maxExecutionTime);

            $this->maxExecutionTime = $maxExecutionTimeInt;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useMaxExecutionTime(&$refLast = null)
    {
        $refLast = ini_set('max_execution_time', $this->maxExecutionTime);

        return $this;
    }


    public function getPhpMaxInputTime(int $maxInputTimeTmp = -1) : string
    {
        $before = ini_set('max_input_time', $maxInputTimeTmp);

        ini_set('max_input_time', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setMaxInputTime(?int $maxInputTime, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $maxInputTime) {
            $this->maxInputTime = -1;

        } else {
            Lib::typeThrow()->int_non_negative_or_minus_one($maxInputTimeInt, $maxInputTime);

            $this->maxInputTime = $maxInputTimeInt;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useMaxInputTime(&$refLast = null)
    {
        $refLast = ini_set('max_input_time', $this->maxInputTime);

        return $this;
    }


    /**
     * @return static
     */
    public function setObImplicitFlush(
        ?bool $obImplicitFlush,
        ?int $obImplicitFlushCommit = null,
        ?bool $replace = null
    )
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $obImplicitFlush) {
            $this->obImplicitFlush = false;

        } else {
            $this->obImplicitFlush = $obImplicitFlush;
        }

        if (null === $obImplicitFlushCommit) {
            $this->obImplicitFlushCommit = 0;

        } else {
            Lib::typeThrow()->int($obImplicitFlushCommitInt, $obImplicitFlushCommit);

            if ($obImplicitFlushCommitInt > 1) {
                $obImplicitFlushCommitInt = 1;

            } elseif ($obImplicitFlushCommitInt < 1) {
                $obImplicitFlushCommitInt = -1;
            }

            $this->obImplicitFlushCommit = $obImplicitFlushCommitInt;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useObImplicitFlush()
    {
        if (1 === $this->obImplicitFlushCommit) {
            while ( ob_get_level() ) {
                ob_end_flush();
            }

        } elseif (-1 === $this->obImplicitFlushCommit) {
            while ( ob_get_level() ) {
                ob_end_clean();
            }
        }

        ob_implicit_flush($this->obImplicitFlush);

        return $this;
    }


    public function getPhpTimezoneDefault() : string
    {
        return date_default_timezone_get();
    }

    /**
     * @param string|\DateTimeZone $timezoneDefault
     *
     * @return static
     */
    public function setTimezoneDefault($timezoneDefault, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $timezoneDefault) {
            try {
                $this->timezoneDefault = new \DateTimeZone(date_default_timezone_get());
            }
            catch ( \Exception $e ) {
                throw new RuntimeException($e);
            }

        } else {
            Lib::typeThrow()->timezone($timezoneDefaultObject, $timezoneDefault);

            $this->timezoneDefault = $timezoneDefaultObject;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useTimezoneDefault(&$refLast = null)
    {
        $refLast = date_default_timezone_set($this->timezoneDefault->getName());

        return $this;
    }


    public function getPhpPostMaxSize(string $postMaxSizeTmp = '8M') : string
    {
        $before = ini_set('post_max_size', $postMaxSizeTmp);

        ini_set('post_max_size', $before);

        return $before;
    }

    /**
     * @return static
     */
    public function setPostMaxSize(?string $postMaxSize, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $postMaxSize) {
            $this->postMaxSize = '8M';

        } else {
            $theFormat = Lib::format();

            $bytesInt = $theFormat->bytes_decode($postMaxSize);
            $bytesString = $theFormat->bytes_encode($bytesInt, 0, 1);

            $this->postMaxSize = $bytesString;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function usePostMaxSize(&$refLast = null)
    {
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
    public function setUploadMaxFilesize(?string $uploadMaxFilesize, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $uploadMaxFilesize) {
            $this->uploadMaxFilesize = '2M';

        } else {
            $theFormat = Lib::format();

            $bytesInt = $theFormat->bytes_decode($uploadMaxFilesize);
            $bytesString = $theFormat->bytes_encode($bytesInt, 0, 1);

            $this->uploadMaxFilesize = $bytesString;
        }

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
    public function setUploadTmpDir(
        ?string $uploadTmpDir,
        ?bool $uploadTmpDirMkdir = null,
        ?bool $replace = null
    )
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $uploadTmpDirMkdir) {
            $this->uploadTmpDirMkdir = false;

        } else {
            $this->uploadTmpDirMkdir = $uploadTmpDirMkdir;
        }

        if (null === $uploadTmpDir) {
            $this->uploadTmpDir = sys_get_temp_dir();

        } else {
            if ($this->uploadTmpDirMkdir) {
                Lib::typeThrow()->dirpath($uploadTmpDirPath, $uploadTmpDir);

            } else {
                Lib::typeThrow()->dirpath_realpath($uploadTmpDirRealpath, $uploadTmpDir);
            }

            $this->uploadTmpDir = $uploadTmpDirRealpath ?? $uploadTmpDirPath ?? null;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useUploadTmpDir(&$refLast = null)
    {
        if (null !== $this->uploadTmpDir) {
            if ($this->uploadTmpDirMkdir) {
                if (! is_dir($this->uploadTmpDir)) {
                    mkdir($this->uploadTmpDir, 0775, true);
                }
            }

            $refLast = ini_set('upload_tmp_dir', $this->uploadTmpDir);
        }

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
    public function setPrecision(?int $precision, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $precision) {
            $this->precision = 16;

        } else {
            Lib::typeThrow()->int_non_negative($precisionInt, $precision);

            $this->precision = $precisionInt;
        }


        return $this;
    }

    /**
     * @return static
     */
    public function usePrecision(&$refLast = null)
    {
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
    public function setUmask(?int $umask, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

        if (null === $umask) {
            $this->umask = 0002;

        } else {
            if (! (($umask >= 0) && ($umask <= 0777))) {
                throw new LogicException(
                    [ 'The `umask` should be a valid `umask`', $umask ]
                );
            }

            $this->umask = $umask;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useUmask(&$refLast = null)
    {
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
    public function setErrorHandler($fnErrorHandler, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

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
        $refLast = null;

        if (null !== $this->fnErrorHandler) {
            $refLast = set_error_handler($this->fnErrorHandler);
        }

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
    public function setExceptionHandler($fnExceptionHandler, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (isset($this->mapSet[ $mapSetKey = __FUNCTION__ ])) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapSet[ $mapSetKey ] = true;
        }

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
        $refLast = null;

        if (null !== $this->fnExceptionHandler) {
            $refLast = set_exception_handler($this->fnExceptionHandler);
        }

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
        $theThrowabler = Lib::debugThrowabler();

        $theThrowabler->setDirRoot($this->dirRoot);

        $messageLines = $theThrowabler->getPreviousMessagesLines(
            $throwable,
            0
            | _DEBUG_THROWABLE_WITH_CODE
            | _DEBUG_THROWABLE_WITH_FILE
            | _DEBUG_THROWABLE_WITH_OBJECT_CLASS
            | _DEBUG_THROWABLE_WITH_PARENTS
        );

        $traceLines = $theThrowabler->getThrowableTraceLines($throwable);

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
    public function setAllDefault()
    {
        $this
            ->setDirRoot(null)
            //
            ->setErrorReporting(E_ALL | E_DEPRECATED | E_USER_DEPRECATED)
            ->setErrorLog(getcwd() . '/error_log')
            ->setDisplayErrors(0)
            ->setErrorHandler([ $this, 'fnErrorHandler' ])
            ->setExceptionHandler([ $this, 'fnExceptionHandler' ])
            //
            ->setMemoryLimit('32M')
            //
            ->setMaxExecutionTime(10)
            ->setMaxInputTime(-1)
            //
            ->setObImplicitFlush(false, 0)
            //
            ->setTimezoneDefault(date_default_timezone_get())
            //
            ->setPostMaxSize('8M')
            //
            ->setUploadMaxFilesize('2M')
            ->setUploadTmpDir(sys_get_temp_dir())
            //
            ->setPrecision(16)
            //
            ->setUmask(0002)
        ;

        return $this;
    }

    /**
     * @return static
     */
    public function useAll()
    {
        $this
            ->useErrorReporting()
            ->useErrorLog()
            ->useDisplayErrors()
            ->useErrorHandler()
            ->useExceptionHandler()
            //
            ->useMemoryLimit()
            //
            ->useMaxExecutionTime()
            ->useMaxInputTime()
            //
            ->useObImplicitFlush()
            //
            ->useTimezoneDefault()
            //
            ->usePostMaxSize()
            //
            ->useUploadMaxFilesize()
            ->useUploadTmpDir()
            //
            ->usePrecision()
            //
            ->useUmask()
        ;

        return $this;
    }


    protected function assertNotLocked() : void
    {
        if (null !== $this->isLocked) {
            throw new RuntimeException(
                [
                    'Unable to change entrypoint parameters due to it was locked before',
                    $this->isLocked,
                ]
            );
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
