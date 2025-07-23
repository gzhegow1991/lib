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
     * @var array<string, mixed>
     */
    protected $mapRecommended = [
        'dirRoot'               => null,
        //
        'fnErrorHandler'        => null,
        'fnExceptionHandler'    => null,
        //
        'errorReporting'        => null,
        'errorLog'              => null,
        'logErrors'             => null,
        'displayErrors'         => null,
        'displayStartupErrors'  => null,
        //
        'memoryLimit'           => null,
        //
        'maxExecutionTime'      => null,
        'maxInputTime'          => null,
        //
        'timezoneDefault'       => null,
        //
        'precision'             => null,
        //
        'umask'                 => null,
        //
        'postMaxSize'           => null,
        //
        'uploadMaxFilesize'     => null,
        'uploadTmpDir'          => null,
        'uploadTmpDirMkdir'     => null,
        //
        'obImplicitFlush'       => null,
        'obImplicitFlushCommit' => null,
    ];
    /**
     * @var array<string, mixed>
     */
    protected $mapInitial = [
        'dirRoot'               => null,
        //
        'fnErrorHandler'        => null,
        'fnExceptionHandler'    => null,
        //
        'errorReporting'        => null,
        'errorLog'              => null,
        'logErrors'             => null,
        'displayErrors'         => null,
        'displayStartupErrors'  => null,
        //
        'memoryLimit'           => null,
        //
        'maxExecutionTime'      => null,
        'maxInputTime'          => null,
        //
        'timezoneDefault'       => null,
        //
        'precision'             => null,
        //
        'umask'                 => null,
        //
        'postMaxSize'           => null,
        //
        'uploadMaxFilesize'     => null,
        'uploadTmpDir'          => null,
        'uploadTmpDirMkdir'     => null,
        //
        'obImplicitFlush'       => null,
        'obImplicitFlushCommit' => null,
    ];
    /**
     * @var array<string, mixed>
     */
    protected $mapWasSet = [
        'dirRoot'               => false,
        //
        'fnErrorHandler'        => false,
        'fnExceptionHandler'    => false,
        //
        'errorReporting'        => false,
        'errorLog'              => false,
        'logErrors'             => false,
        'displayErrors'         => false,
        'displayStartupErrors'  => false,
        //
        'memoryLimit'           => false,
        //
        'maxExecutionTime'      => false,
        'maxInputTime'          => false,
        //
        'timezoneDefault'       => false,
        //
        'precision'             => false,
        //
        'umask'                 => false,
        //
        'postMaxSize'           => false,
        //
        'uploadMaxFilesize'     => false,
        'uploadTmpDir'          => false,
        'uploadTmpDirMkdir'     => false,
        //
        'obImplicitFlush'       => false,
        'obImplicitFlushCommit' => false,
    ];

    /**
     * @var string
     */
    protected $dirRoot = [];

    /**
     * @var array{ 0?: callable|null }
     */
    protected $fnErrorHandler = [];
    /**
     * @var array{ 0?: callable|null }
     */
    protected $fnExceptionHandler = [];

    /**
     * @var array{ 0?: int }
     */
    protected $errorReporting = [];
    /**
     * @var array{ 0?: string }
     */
    protected $errorLog = [];
    /**
     * @var array{ 0?: string }
     */
    protected $logErrors = [];
    /**
     * @var array{ 0?: int }
     */
    protected $displayErrors = [];
    /**
     * @var array{ 0?: int }
     */
    protected $displayStartupErrors = [];

    /**
     * @var array{ 0?: string }
     */
    protected $memoryLimit = [];

    /**
     * @var array{ 0?: int }
     */
    protected $maxExecutionTime = [];
    /**
     * @var array{ 0?: int }
     */
    protected $maxInputTime = [];

    /**
     * @var array{ 0?: \DateTimeZone }
     */
    protected $timezoneDefault = [];

    /**
     * @var array{ 0?: int }
     */
    protected $precision = [];

    /**
     * @var array{ 0?: int }
     */
    protected $umask = [];

    /**
     * @var array{ 0?: string }
     */
    protected $postMaxSize = [];

    /**
     * @var array{ 0?: string }
     */
    protected $uploadMaxFilesize = [];
    /**
     * @var array{ 0?: string }
     */
    protected $uploadTmpDir = [];
    /**
     * @var array{ 0?: bool }
     */
    protected $uploadTmpDirMkdir = [];

    /**
     * @var array{ 0?: bool }
     */
    protected $obImplicitFlush = [];
    /**
     * @var array{ 0?: int }
     */
    protected $obImplicitFlushCommit = [];

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
        $this->mapRecommended = [
            'dirRoot'               => null,
            //
            'fnErrorHandler'        => [ $this, 'fnErrorHandler' ],
            'fnExceptionHandler'    => [ $this, 'fnExceptionHandler' ],
            //
            'errorReporting'        => (E_ALL | E_DEPRECATED | E_USER_DEPRECATED),
            'errorLog'              => null,
            'logErrors'             => 0,
            'displayErrors'         => 0,
            'displayStartupErrors'  => 0,
            //
            'memoryLimit'           => '32M',
            //
            'maxExecutionTime'      => 10,
            'maxInputTime'          => -1,
            //
            'timezoneDefault'       => new \DateTimeZone('UTC'),
            //
            'precision'             => 16,
            //
            'umask'                 => 0002,
            //
            'postMaxSize'           => '8M',
            //
            'uploadMaxFilesize'     => '2M',
            'uploadTmpDir'          => null,
            'uploadTmpDirMkdir'     => false,
            //
            'obImplicitFlush'       => false,
            'obImplicitFlushCommit' => 0,
        ];

        $this->mapInitial = [
            'dirRoot'               => null,
            //
            'fnErrorHandler'        => $this->getPhpErrorHandler(),
            'fnExceptionHandler'    => $this->getPhpExceptionHandler(),
            //
            'errorReporting'        => $this->getPhpErrorReporting(),
            'errorLog'              => $this->getPhpErrorLog(),
            'logErrors'             => $this->getPhpLogErrors(),
            'displayErrors'         => $this->getPhpDisplayErrors(),
            'displayStartupErrors'  => $this->getPhpDisplayStartupErrors(),
            //
            'memoryLimit'           => $this->getPhpMemoryLimit(),
            //
            'maxExecutionTime'      => $this->getPhpMaxExecutionTime(),
            'maxInputTime'          => $this->getPhpMaxInputTime(),
            //
            'timezoneDefault'       => $this->getPhpTimezoneDefault(),
            //
            'precision'             => $this->getPhpPrecision(),
            //
            'umask'                 => $this->getPhpUmask(),
            //
            'postMaxSize'           => $this->getPhpPostMaxSize(),
            //
            'uploadMaxFilesize'     => $this->getPhpUploadMaxFilesize(),
            'uploadTmpDir'          => $this->getPhpUploadTmpDir(),
            'uploadTmpDirMkdir'     => false,
            //
            'obImplicitFlush'       => false,
            'obImplicitFlushCommit' => 0,
        ];

        foreach ( $this->mapRecommended as $key => $value ) {
            $this->{$key} = [ $value ];
        }

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

        $theDebug = Lib::debug();

        if ($isLocked) {
            $this->isLocked = $theDebug->file_line();

        } else {
            $this->isLocked = null;
        }

        return $this;
    }


    /**
     * @param string $refResult
     */
    public function hasDirRoot(&$refResult = null) : bool
    {
        $refResult = null;

        if ([] !== $this->dirRoot) {
            $refResult = $this->dirRoot[ 0 ];

            return true;
        }

        return false;
    }

    public function getDirRoot() : string
    {
        return $this->dirRoot[ 0 ];
    }

    /**
     * > частично удаляет путь файла из каждой строки `trace` (`trace[i][file]`) при обработке исключений
     *
     * @param string|bool|false $dirRoot
     *
     * @return static
     */
    public function setDirRoot($dirRoot, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'dirRoot';
        $var = $dirRoot;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$key} = [ $this->mapRecommended[ 'dirRoot' ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ 'dirRoot' ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->dirpath_realpath($var)->orThrow();

            $this->{$key} = [ $varValid ];
        }

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
     * @param callable|false|null $fnErrorHandler
     *
     * @return static
     */
    public function setErrorHandler($fnErrorHandler, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'fnErrorHandler';
        $var = $fnErrorHandler;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            if (! is_callable($var)) {
                throw new LogicException(
                    [ 'The `fnErrorHandler` should be a callable', $var ]
                );
            }

            $this->{$key} = [ $var ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useErrorHandler(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->fnErrorHandler) {
            $refLast = set_error_handler($this->fnErrorHandler[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedErrorHandler(&$refLast = null)
    {
        $refLast = set_error_handler($this->mapRecommended[ 'fnErrorHandler' ]);

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
     * @param callable|false|null $fnExceptionHandler
     *
     * @return static
     */
    public function setExceptionHandler($fnExceptionHandler, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'fnExceptionHandler';
        $var = $fnExceptionHandler;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            if (! is_callable($var)) {
                throw new LogicException(
                    [ 'The `fnExceptionHandler` should be a callable', $var ]
                );
            }

            $this->{$key} = [ $var ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useExceptionHandler(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->fnExceptionHandler) {
            $refLast = set_exception_handler($this->fnExceptionHandler[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedExceptionHandler(&$refLast = null)
    {
        $refLast = set_exception_handler($this->mapRecommended[ 'fnExceptionHandler' ]);

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
     * @param int|false|null $errorReporting
     *
     * @return static
     */
    public function setErrorReporting($errorReporting, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'errorReporting';
        $var = $errorReporting;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            if (0 !== ($errorReporting & ~(E_ALL | E_DEPRECATED | E_USER_DEPRECATED))) {
                throw new LogicException(
                    [ 'The `errorReporting` should be valid flag', $var ]
                );
            }

            $this->{$key} = [ $var ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useErrorReporting(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->errorReporting) {
            $refLast = error_reporting($this->errorReporting[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedErrorReporting(&$refLast = null)
    {
        $refLast = error_reporting($this->mapRecommended[ 'errorReporting' ]);

        return $this;
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
     * @param string|false|null $errorLog
     *
     * @return static
     */
    public function setErrorLog($errorLog, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'errorLog';
        $var = $errorLog;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->filepath($errorLog, true)->orThrow();

            $this->{$key} = [ $varValid ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useErrorLog(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->errorLog) {
            $refLast = ini_set('error_log', $this->errorLog[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedErrorLog(&$refLast = null)
    {
        $refLast = ini_set('error_log', $this->mapRecommended[ 'errorLog' ]);

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
     * @param bool|false|null $logErrors
     *
     * @return static
     */
    public function setLogErrors($logErrors, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'logErrors';
        $var = $logErrors;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->bool($var)->orThrow();

            $this->{$key} = [ (int) $varValid ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useLogErrors(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->logErrors) {
            $refLast = ini_set('log_errors', $this->logErrors[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedLogErrors(&$refLast = null)
    {
        $refLast = ini_set('log_errors', $this->mapRecommended[ 'logErrors' ]);

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
     * @param bool|false|null $displayErrors
     *
     * @return static
     */
    public function setDisplayErrors($displayErrors, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (false
            || (false !== $this->mapWasSet[ 'displayErrors' ])
            || (false !== $this->mapWasSet[ 'displayStartupErrors' ])
        ) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ 'displayErrors' ] = true;
            $this->mapWasSet[ 'displayStartupErrors' ] = true;
        }

        if (null === $displayErrors) {
            $this->displayErrors = [ $this->mapRecommended[ 'displayErrors' ] ];
            $this->displayStartupErrors = [ $this->mapRecommended[ 'displayStartupErrors' ] ];

        } elseif (false === $displayErrors) {
            $this->displayErrors = [ $this->mapInitial[ 'displayErrors' ] ];
            $this->displayStartupErrors = [ $this->mapInitial[ 'displayStartupErrors' ] ];

        } else {
            $theType = Lib::type();

            $displayErrorsValid = $theType->bool($displayErrors)->orThrow();

            $this->displayErrors = [ (int) $displayErrorsValid ];
            $this->displayStartupErrors = [ (int) $displayErrorsValid ];
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
        $refLastDisplayErrors = null;
        $refLastDisplayStartupErrors = null;

        if ([] !== $this->displayErrors) {
            $refLastDisplayErrors = ini_set('display_errors', $this->displayErrors[ 0 ]);
        }

        if ([] !== $this->displayStartupErrors) {
            $refLastDisplayStartupErrors = ini_set('display_startup_errors', $this->displayStartupErrors[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedDisplayErrors(
        &$refLastDisplayErrors = null,
        &$refLastDisplayStartupErrors = null
    )
    {
        $refLastDisplayErrors = ini_set('display_errors', $this->mapRecommended[ 'displayErrors' ]);
        $refLastDisplayStartupErrors = ini_set('display_startup_errors', $this->mapRecommended[ 'displayStartupErrors' ]);

        return $this;
    }


    public function getPhpMemoryLimit(string $memoryLimitTmp = '32M') : string
    {
        $before = ini_set('memory_limit', $memoryLimitTmp);

        ini_set('memory_limit', $before);

        return $before;
    }

    /**
     * @param string|false|null $memoryLimit
     *
     * @return static
     */
    public function setMemoryLimit($memoryLimit, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'memoryLimit';
        $var = $memoryLimit;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$var} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$var} = [ $this->mapInitial[ $key ] ];

        } else {
            $theFormat = Lib::format();

            $varValidInt = $theFormat->bytes_decode([], $var);
            $varValidString = $theFormat->bytes_encode([], $varValidInt, 0, 1);

            $this->{$var} = [ $varValidString ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useMemoryLimit(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->memoryLimit) {
            $refLast = ini_set('memory_limit', $this->memoryLimit[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedMemoryLimit(&$refLast = null)
    {
        $refLast = ini_set('memory_limit', $this->mapRecommended[ 'memoryLimit' ]);

        return $this;
    }


    public function getPhpMaxExecutionTime(int $maxInputTimeTmp = 30) : string
    {
        $before = ini_set('max_execution_time', $maxInputTimeTmp);

        ini_set('max_execution_time', $before);

        return $before;
    }

    /**
     * @param int|false|null $maxExecutionTime
     *
     * @return static
     */
    public function setMaxExecutionTime($maxExecutionTime, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'maxExecutionTime';
        $var = $maxExecutionTime;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$var} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$var} = [ $this->mapInitial[ $key ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->int_non_negative($var)->orThrow();

            $this->{$var} = [ $varValid ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useMaxExecutionTime(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->maxExecutionTime) {
            $refLast = ini_set('max_execution_time', $this->maxExecutionTime[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedMaxExecutionTime(&$refLast = null)
    {
        $refLast = ini_set('max_execution_time', $this->mapRecommended[ 'maxExecutionTime' ]);

        return $this;
    }


    public function getPhpMaxInputTime(int $maxInputTimeTmp = -1) : string
    {
        $before = ini_set('max_input_time', $maxInputTimeTmp);

        ini_set('max_input_time', $before);

        return $before;
    }

    /**
     * @param int|bool|null $maxInputTime
     *
     * @return static
     */
    public function setMaxInputTime($maxInputTime, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'maxInputTime';
        $var = $maxInputTime;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$var} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$var} = [ $this->mapInitial[ $key ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->int_non_negative_or_minus_one($var)->orThrow();

            $this->{$var} = [ $varValid ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useMaxInputTime(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->maxInputTime) {
            $refLast = ini_set('max_input_time', $this->maxInputTime[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedMaxInputTime(&$refLast = null)
    {
        $refLast = ini_set('max_input_time', $this->mapRecommended[ 'maxInputTime' ]);

        return $this;
    }


    public function getPhpTimezoneDefault() : \DateTimeZone
    {
        try {
            $timezone = new \DateTimeZone(date_default_timezone_get());
        }
        catch ( \Exception $e ) {
            throw new RuntimeException($e);
        }

        return $timezone;
    }

    /**
     * @param \DateTimeZone|string|false|null $timezoneDefault
     *
     * @return static
     */
    public function setTimezoneDefault($timezoneDefault, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'timezoneDefault';
        $var = $timezoneDefault;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$var} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$var} = [ $this->mapInitial[ $key ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->timezone($var)->orThrow();

            $this->{$var} = [ $varValid ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useTimezoneDefault(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->timezoneDefault) {
            $refLast = date_default_timezone_set($this->timezoneDefault[ 0 ]->getName());
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedTimezoneDefault(&$refLast = null)
    {
        $refLast = date_default_timezone_set($this->mapRecommended[ 'timezoneDefault' ]->getName());

        return $this;
    }


    public function getPhpPrecision(int $precisionTmp = 16) : string
    {
        $before = ini_set('precision', $precisionTmp);

        ini_set('precision', $before);

        return $before;
    }

    /**
     * @param int|false|null $precision
     *
     * @return static
     */
    public function setPrecision($precision, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'precision';
        $var = $precision;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$var} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$var} = [ $this->mapInitial[ $key ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->int_non_negative($var)->orThrow();

            $this->{$var} = [ $varValid ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function usePrecision(&$refLast = null)
    {
        if ([] !== $this->precision) {
            $refLast = ini_set('precision', $this->precision[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedPrecision(&$refLast = null)
    {
        $refLast = ini_set('precision', $this->mapRecommended[ 'precision' ]);

        return $this;
    }


    public function getPhpUmask(int $umaskTmp = 0002) : string
    {
        $before = umask($umaskTmp);

        umask($before);

        return $before;
    }

    /**
     * @param int|false|null $umask
     *
     * @return static
     */
    public function setUmask($umask, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'umask';
        $var = $umask;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$var} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$var} = [ $this->mapInitial[ $key ] ];

        } else {
            if (! (($var >= 0) && ($var <= 0777))) {
                throw new LogicException(
                    [ 'The `umask` should be a valid `umask`', $umask ]
                );
            }

            $this->{$var} = [ $var ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useUmask(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->umask) {
            $refLast = umask($this->umask[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedUmask(&$refLast = null)
    {
        $refLast = umask($this->mapRecommended[ 'umask' ]);

        return $this;
    }


    public function getPhpPostMaxSize(string $postMaxSizeTmp = '8M') : string
    {
        $before = ini_set('post_max_size', $postMaxSizeTmp);

        ini_set('post_max_size', $before);

        return $before;
    }

    /**
     * @param string|false|null $postMaxSize
     *
     * @return static
     */
    public function setPostMaxSize($postMaxSize, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'postMaxSize';
        $var = $postMaxSize;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$var} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$var} = [ $this->mapInitial[ $key ] ];

        } else {
            $theFormat = Lib::format();

            $varValidInt = $theFormat->bytes_decode([], $var);
            $varValidString = $theFormat->bytes_encode([], $varValidInt, 0, 1);

            $this->{$var} = [ $varValidString ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function usePostMaxSize(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->postMaxSize) {
            $refLast = ini_set('post_max_size', $this->postMaxSize[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedPostMaxSize(&$refLast = null)
    {
        $refLast = ini_set('post_max_size', $this->mapRecommended[ 'postMaxSize' ]);

        return $this;
    }


    public function getPhpUploadMaxFilesize(string $uploadMaxFilesizeTmp = '2M') : string
    {
        $before = ini_set('upload_max_filesize', $uploadMaxFilesizeTmp);

        ini_set('upload_max_filesize', $before);

        return $before;
    }

    /**
     * @param string|false|null $uploadMaxFilesize
     *
     * @return static
     */
    public function setUploadMaxFilesize($uploadMaxFilesize, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'uploadMaxFilesize';
        $var = $uploadMaxFilesize;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$var} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$var} = [ $this->mapInitial[ $key ] ];

        } else {
            $theFormat = Lib::format();

            $varValidInt = $theFormat->bytes_decode([], $var);
            $varValidString = $theFormat->bytes_encode([], $varValidInt, 0, 1);

            $this->{$var} = [ $varValidString ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useUploadMaxFilesize(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->uploadMaxFilesize) {
            $refLast = ini_set('upload_max_filesize', $this->uploadMaxFilesize[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedUploadMaxFilesize(&$refLast = null)
    {
        $refLast = ini_set('upload_max_filesize', $this->mapRecommended[ 'uploadMaxFilesize' ]);

        return $this;
    }


    public function getPhpUploadTmpDir() : string
    {
        $before = ini_set('upload_tmp_dir', sys_get_temp_dir());

        ini_set('upload_tmp_dir', $before);

        return $before;
    }

    /**
     * @param string|false|null $uploadTmpDir
     * @param bool|false|null   $uploadTmpDirMkdir
     *
     * @return static
     */
    public function setUploadTmpDir(
        $uploadTmpDir,
        $uploadTmpDirMkdir,
        ?bool $replace = null
    )
    {
        $this->assertNotLocked();

        if (false
            || (false !== $this->mapWasSet[ 'uploadTmpDir' ])
            || (false !== $this->mapWasSet[ 'uploadTmpDirMkdir' ])
        ) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ 'uploadTmpDir' ] = true;
            $this->mapWasSet[ 'uploadTmpDirMkdir' ] = true;
        }

        if (null === $uploadTmpDir) {
            $this->uploadTmpDir = [ $this->mapRecommended[ 'uploadTmpDir' ] ];
            $this->uploadTmpDirMkdir = [ $this->mapRecommended[ 'uploadTmpDirMkdir' ] ];

        } elseif (false === $uploadTmpDir) {
            $this->uploadTmpDir = [ $this->mapInitial[ 'uploadTmpDir' ] ];
            $this->uploadTmpDirMkdir = [ $this->mapInitial[ 'uploadTmpDirMkdir' ] ];

        } else {
            $theType = Lib::type();

            $uploadTmpDirMkdirValid = (bool) $uploadTmpDirMkdir;

            if ($uploadTmpDirMkdirValid) {
                $uploadTmpDirValid = $theType->dirpath($uploadTmpDir, true)->orThrow();

            } else {
                $uploadTmpDirValid = $theType->dirpath_realpath($uploadTmpDir)->orThrow();
            }

            $this->uploadTmpDir = [ $uploadTmpDirValid ];
            $this->uploadTmpDirMkdir = [ $uploadTmpDirMkdirValid ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useUploadTmpDir(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->uploadTmpDir) {
            $uploadTmpDirValid = $this->uploadTmpDir[ 0 ];

            if ([] !== $this->uploadTmpDirMkdir) {
                $uploadTmpDirMkdirValid = (bool) $this->uploadTmpDirMkdir[ 0 ];

                if ($uploadTmpDirMkdirValid) {
                    if (! is_dir($uploadTmpDirValid)) {
                        mkdir($uploadTmpDirValid, 0775, true);
                    }
                }
            }

            $refLast = ini_set('upload_tmp_dir', $uploadTmpDirValid);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedUploadTmpDir(&$refLast = null)
    {
        $refLast = ini_set('upload_tmp_dir', $this->mapRecommended[ 'uploadTmpDir' ]);

        return $this;
    }


    /**
     * > устанавливает немедленный вывод echo напрямую в браузер в runtime
     *
     * @param bool|false|null $obImplicitFlush
     * @param int|false|null  $obImplicitFlushCommit
     *
     * @return static
     */
    public function setObImplicitFlush(
        $obImplicitFlush,
        $obImplicitFlushCommit,
        ?bool $replace = null
    )
    {
        $this->assertNotLocked();

        if (false
            || (false !== $this->mapWasSet[ 'obImplicitFlush' ])
            || (false !== $this->mapWasSet[ 'obImplicitFlushCommit' ])
        ) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ 'obImplicitFlush' ] = true;
            $this->mapWasSet[ 'obImplicitFlushCommit' ] = true;
        }

        if (null === $obImplicitFlush) {
            $this->obImplicitFlush = [ $this->mapRecommended[ 'obImplicitFlush' ] ];

        } elseif (false === $obImplicitFlush) {
            $this->obImplicitFlush = [ $this->mapInitial[ 'obImplicitFlush' ] ];

        } else {
            $obImplicitFlushValid = (bool) $obImplicitFlush;

            $this->obImplicitFlush = $obImplicitFlushValid;
        }

        if (null === $obImplicitFlushCommit) {
            $this->obImplicitFlushCommit = [ $this->mapRecommended[ 'obImplicitFlushCommit' ] ];

        } elseif (false === $obImplicitFlushCommit) {
            $this->obImplicitFlushCommit = [ $this->mapInitial[ 'obImplicitFlushCommit' ] ];

        } else {
            $theType = Lib::type();

            $obImplicitFlushCommitValid = $theType->int($obImplicitFlushCommit)->orThrow();

            if ($obImplicitFlushCommitValid > 1) {
                $obImplicitFlushCommitValid = 1;

            } elseif ($obImplicitFlushCommitValid < 1) {
                $obImplicitFlushCommitValid = -1;
            }

            $this->obImplicitFlushCommit = $obImplicitFlushCommitValid;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useObImplicitFlush()
    {
        if ([] !== $this->obImplicitFlushCommit) {
            $obImplicitFlushCommitValid = $this->obImplicitFlushCommit[ 0 ];

            if (1 === $obImplicitFlushCommitValid) {
                while ( ob_get_level() ) {
                    ob_end_flush();
                }

            } elseif (-1 === $obImplicitFlushCommitValid) {
                while ( ob_get_level() ) {
                    ob_end_clean();
                }
            }
        }

        if ([] !== $this->obImplicitFlush) {
            ob_implicit_flush($this->obImplicitFlush[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedObImplicitFlush()
    {
        ob_implicit_flush($this->mapRecommended[ 'obImplicitFlush' ]);

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
        $theDebug = Lib::debug();
        $theDebugThrowabler = $theDebug->throwabler();

        if ($this->hasDirRoot($refDirRoot)) {
            $theDebugThrowabler->setDirRoot($refDirRoot);
        }

        $messageLines = $theDebugThrowabler->getPreviousMessagesAllLines(
            $throwable,
            0
            | _DEBUG_THROWABLE_WITH_CODE
            | _DEBUG_THROWABLE_WITH_FILE
            | _DEBUG_THROWABLE_WITH_OBJECT_CLASS
            | _DEBUG_THROWABLE_WITH_PARENTS
        );

        $traceLines = $theDebugThrowabler->getThrowableTraceLines($throwable);

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
    public function setAllRecommended()
    {
        $this->assertNotLocked();

        foreach ( $this->mapRecommended as $key => $value ) {
            $this->{$key} = [ $value ];

            $this->mapWasSet[ $key ] = true;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function setAllInitial()
    {
        $this->assertNotLocked();

        foreach ( $this->mapInitial as $key => $value ) {
            $this->{$key} = [ $value ];

            $this->mapWasSet[ $key ] = true;
        }

        return $this;
    }


    /**
     * @return static
     */
    public function useAll()
    {
        $this
            ->useErrorHandler()
            ->useExceptionHandler()
            //
            ->useErrorReporting()
            ->useErrorLog()
            ->useLogErrors()
            ->useDisplayErrors()
            //
            ->useMemoryLimit()
            //
            ->useMaxExecutionTime()
            ->useMaxInputTime()
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

    /**
     * @return static
     */
    public function useAllDefault()
    {
        $this
            ->useRecommendedErrorHandler()
            ->useRecommendedExceptionHandler()
            //
            ->useRecommendedErrorReporting()
            ->useRecommendedErrorLog()
            ->useRecommendedLogErrors()
            ->useRecommendedDisplayErrors()
            //
            ->useRecommendedMemoryLimit()
            //
            ->useRecommendedMaxExecutionTime()
            ->useRecommendedMaxInputTime()
            //
            ->useRecommendedTimezoneDefault()
            //
            ->useRecommendedPrecision()
            //
            ->useRecommendedUmask()
            //
            ->useRecommendedPostMaxSize()
            //
            ->useRecommendedUploadMaxFilesize()
            ->useRecommendedUploadTmpDir()
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
