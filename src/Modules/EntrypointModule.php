<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Arr\Map\Map;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Arr\Map\Base\AbstractMap;


class EntrypointModule
{
    /**
     * @var array{ 0: string, 1: string }
     */
    protected $isLocked = [];

    /**
     * @var array<string, mixed>
     */
    protected $mapInitial = [
        'errorReporting'       => null,
        'errorLog'             => null,
        'logErrors'            => null,
        'displayErrors'        => null,
        //
        'memoryLimit'          => null,
        //
        'maxExecutionTime'     => null,
        'maxInputTime'         => null,
        //
        'timezoneDefault'      => null,
        //
        'precision'            => null,
        //
        'umask'                => null,
        //
        'postMaxSize'          => null,
        //
        'sessionCookieParams'  => null,
        'sessionSavePath'      => null,
        'sessionSavePathMkdir' => null,
        //
        'uploadMaxFilesize'    => null,
        'uploadTmpDir'         => null,
        'uploadTmpDirMkdir'    => null,
        //
        'errorHandler'         => null,
        'exceptionHandler'     => null,
    ];
    /**
     * @var array<string, mixed>
     */
    protected $mapRecommended = [
        'errorReporting'       => null,
        'errorLog'             => null,
        'logErrors'            => null,
        'displayErrors'        => null,
        //
        'memoryLimit'          => null,
        //
        'maxExecutionTime'     => null,
        'maxInputTime'         => null,
        //
        'timezoneDefault'      => null,
        //
        'precision'            => null,
        //
        'umask'                => null,
        //
        'postMaxSize'          => null,
        //
        'sessionCookieParams'  => null,
        'sessionSavePath'      => null,
        'sessionSavePathMkdir' => null,
        //
        'uploadMaxFilesize'    => null,
        'uploadTmpDir'         => null,
        'uploadTmpDirMkdir'    => null,
        //
        'errorHandler'         => null,
        'exceptionHandler'     => null,
    ];

    /**
     * @var array<string, mixed>
     */
    protected $mapWasSet = [
        'dirRoot'              => false,
        'retTrace'             => false,
        //
        'errorReporting'       => false,
        'errorLog'             => false,
        'logErrors'            => false,
        'displayErrors'        => false,
        //
        'memoryLimit'          => false,
        //
        'maxExecutionTime'     => false,
        'maxInputTime'         => false,
        //
        'timezoneDefault'      => false,
        //
        'precision'            => false,
        //
        'umask'                => false,
        //
        'postMaxSize'          => false,
        //
        'sessionCookieParams'  => false,
        'sessionSavePath'      => false,
        'sessionSavePathMkdir' => false,
        //
        'uploadMaxFilesize'    => false,
        'uploadTmpDir'         => false,
        'uploadTmpDirMkdir'    => false,
        //
        'errorHandler'         => false,
        'exceptionHandler'     => false,
    ];

    /**
     * @var array{ 0?: string }
     */
    protected $dirRoot = [];
    /**
     * @var array{ 0?: bool }
     */
    protected $retTrace = [];

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
     * @var array{ 0?: array }
     */
    protected $sessionCookieParams = [];
    /**
     * @var array{ 0?: string }
     */
    protected $sessionSavePath = [];
    /**
     * @var array{ 0?: bool }
     */
    protected $sessionSavePathMkdir = [];

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
     * @var array{ 0?: callable|null }
     */
    protected $errorHandler = [];
    /**
     * @var array{ 0?: callable|null }
     */
    protected $exceptionHandler = [];

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
        $this->mapInitial = [
            'errorReporting'       => $this->getPhpErrorReporting(),
            'errorLog'             => $this->getPhpErrorLog(),
            'logErrors'            => $this->getPhpLogErrors(),
            'displayErrors'        => $this->getPhpDisplayErrors(),
            //
            'memoryLimit'          => $this->getPhpMemoryLimit(),
            //
            'maxExecutionTime'     => $this->getPhpMaxExecutionTime(),
            'maxInputTime'         => $this->getPhpMaxInputTime(),
            //
            'timezoneDefault'      => $this->getPhpTimezoneDefault(),
            //
            'precision'            => $this->getPhpPrecision(),
            //
            'umask'                => $this->getPhpUmask(),
            //
            'postMaxSize'          => $this->getPhpPostMaxSize(),
            //
            'sessionCookieParams'  => $this->getPhpSessionCookieParams(),
            'sessionSavePath'      => $this->getPhpSessionSavePath(),
            'sessionSavePathMkdir' => false,
            //
            'uploadMaxFilesize'    => $this->getPhpUploadMaxFilesize(),
            'uploadTmpDir'         => $this->getPhpUploadTmpDir(),
            'uploadTmpDirMkdir'    => false,
            //
            'errorHandler'         => $this->getPhpErrorHandler(),
            'exceptionHandler'     => $this->getPhpExceptionHandler(),
        ];

        $this->mapRecommended = [
            'errorReporting'       => (E_ALL | E_DEPRECATED | E_USER_DEPRECATED),
            'errorLog'             => (getcwd() . '/error_log'),
            'logErrors'            => 0,
            'displayErrors'        => 0,
            //
            'memoryLimit'          => '32M',
            //
            'maxExecutionTime'     => 10,
            'maxInputTime'         => -1,
            //
            'timezoneDefault'      => new \DateTimeZone('UTC'),
            //
            'precision'            => 16,
            //
            'umask'                => 0002,
            //
            'postMaxSize'          => '8M',
            //
            'sessionCookieParams'  => [
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ],
            'sessionSavePath'      => sys_get_temp_dir(),
            'sessionSavePathMkdir' => false,
            //
            'uploadMaxFilesize'    => '2M',
            'uploadTmpDir'         => sys_get_temp_dir(),
            'uploadTmpDirMkdir'    => false,
            //
            'errorHandler'         => [ $this, 'fnErrorHandler' ],
            'exceptionHandler'     => [ $this, 'fnExceptionHandler' ],
        ];

        foreach ( $this->mapRecommended as $key => $value ) {
            $this->{$key} = [ $value ];
        }

        $this->registerShutdownFunctionMap = Map::new();
    }


    public function isLocked(?array &$fileLine = null) : bool
    {
        $fileLine = null;

        if ([] !== $this->isLocked) {
            $fileLine = $this->isLocked;

            return true;
        }

        return false;
    }

    /**
     * @return static
     */
    public function lock(?bool $lock = null)
    {
        $lock = $lock ?? true;

        $theDebug = Lib::debug();

        if ($lock) {
            $this->isLocked = $theDebug->file_line();

        } else {
            $this->isLocked = [];
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
     * @param string|false|null $dirRoot
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

        if (! $var) {
            $this->{$key} = [ null ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->dirpath_realpath($var)->orThrow();

            $this->{$key} = [ $varValid ];
        }

        [ $current ] = $this->{$key};

        if (null !== $current) {
            DebugModule::staticDirRoot($dirRoot);
        }

        return $this;
    }


    public function isRetTrace() : bool
    {
        return $this->retTrace[ 0 ] ?? false;
    }

    /**
     * > собирает трейсы при добавлении каждой ошибки для отлова пути, где она произошла
     *
     * @param bool|null $retTrace
     *
     * @return static
     */
    public function setRetTrace($retTrace, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'retTrace';
        $var = $retTrace;

        if (false !== $this->mapWasSet[ $key ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ $key ] = true;
        }

        if (null === $var) {
            $this->{$key} = [ false ];

        } else {
            $this->{$key} = [ (bool) $retTrace ];
        }

        [ $current ] = $this->{$key};

        if (null !== $current) {
            Ret::staticWithTrace($current);
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
        return $this->errorHandler;
    }

    /**
     * @param callable|false|null $fnErrorHandler
     *
     * @return static
     */
    public function setErrorHandler($fnErrorHandler, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'errorHandler';
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

        if ([] !== $this->errorHandler) {
            $refLast = set_error_handler($this->errorHandler[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedErrorHandler(&$refLast = null)
    {
        $refLast = set_error_handler($this->mapRecommended[ 'errorHandler' ]);

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
        return $this->exceptionHandler;
    }

    /**
     * @param callable|false|null $fnExceptionHandler
     *
     * @return static
     */
    public function setExceptionHandler($fnExceptionHandler, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'exceptionHandler';
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

        if ([] !== $this->exceptionHandler) {
            $refLast = set_exception_handler($this->exceptionHandler[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedExceptionHandler(&$refLast = null)
    {
        $refLast = set_exception_handler($this->mapRecommended[ 'exceptionHandler' ]);

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
    public function getPhpErrorLog()
    {
        return ini_get('error_log');
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
    public function getPhpLogErrors()
    {
        return ini_get('log_errors');
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
    public function getPhpDisplayErrors()
    {
        return ini_get('display_errors');
    }

    /**
     * @param bool|false|null $displayErrors
     *
     * @return static
     */
    public function setDisplayErrors($displayErrors, ?bool $replace = null)
    {
        $this->assertNotLocked();

        if (false !== $this->mapWasSet[ 'displayErrors' ]) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ 'displayErrors' ] = true;
        }

        if (null === $displayErrors) {
            $this->displayErrors = [ $this->mapRecommended[ 'displayErrors' ] ];

        } elseif (false === $displayErrors) {
            $this->displayErrors = [ $this->mapInitial[ 'displayErrors' ] ];

        } else {
            $theType = Lib::type();

            $displayErrorsValid = $theType->bool($displayErrors)->orThrow();

            $this->displayErrors = [ (int) $displayErrorsValid ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useDisplayErrors(&$refLastDisplayErrors = null)
    {
        $refLastDisplayErrors = null;

        if ([] !== $this->displayErrors) {
            $refLastDisplayErrors = ini_set('display_errors', $this->displayErrors[ 0 ]);

            ini_set('display_startup_errors', $this->displayErrors[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedDisplayErrors(&$refLastDisplayErrors = null)
    {
        $refLastDisplayErrors = ini_set('display_errors', $this->mapRecommended[ 'displayErrors' ]);

        ini_set('display_startup_errors', $this->mapRecommended[ 'displayErrors' ]);

        return $this;
    }


    public function getPhpMemoryLimit() : string
    {
        return ini_get('memory_limit');
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
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            $theFormat = Lib::format();

            $varValidInt = $theFormat->bytes_decode([], $var);
            $varValidString = $theFormat->bytes_encode([], $varValidInt, 0, 1);

            $this->{$key} = [ $varValidString ];
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


    public function getPhpMaxExecutionTime() : string
    {
        return ini_get('max_execution_time');
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
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->int_non_negative($var)->orThrow();

            $this->{$key} = [ $varValid ];
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


    public function getPhpMaxInputTime() : string
    {
        return ini_get('max_input_time');
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
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->int_non_negative_or_minus_one($var)->orThrow();

            $this->{$key} = [ $varValid ];
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
        catch ( \Throwable $e ) {
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
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->timezone($var)->orThrow();

            $this->{$key} = [ $varValid ];
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


    public function getPhpPrecision() : string
    {
        return ini_get('precision');
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
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            $theType = Lib::type();

            $varValid = $theType->int_non_negative($var)->orThrow();

            $this->{$key} = [ $varValid ];
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


    public function getPhpUmask() : string
    {
        $umaskTmp = $umaskTmp ?? 0002;

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
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            if (! (($var >= 0) && ($var <= 0777))) {
                throw new LogicException(
                    [ 'The `umask` should be a valid `umask`', $umask ]
                );
            }

            $this->{$key} = [ $var ];
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


    public function getPhpPostMaxSize() : string
    {
        return ini_get('post_max_size');
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
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            $theFormat = Lib::format();

            $varValidInt = $theFormat->bytes_decode([], $var);
            $varValidString = $theFormat->bytes_encode([], $varValidInt, 0, 1);

            $this->{$key} = [ $varValidString ];
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


    public function getPhpSessionCookieParams() : array
    {
        $theHttpSession = Lib::httpSession();

        return $theHttpSession->session_get_cookie_params();
    }

    /**
     * @param array|false|null $sessionCookieParams
     *
     * @return static
     */
    public function setSessionCookieParams($sessionCookieParams, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $key = 'sessionCookieParams';
        $var = $sessionCookieParams;

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
            $sessionCookieParamsValid = Lib::type()->array($sessionCookieParams)->orThrow();

            $sessionCookieParamsAll = [
                'lifetime' => null,
                'path'     => null,
                'domain'   => null,
                'secure'   => null,
                'httponly' => null,
                'samesite' => null,
            ];

            if ($diff = array_diff_key($sessionCookieParams, $sessionCookieParamsAll)) {
                throw new RuntimeException(
                    [
                        ''
                        . 'The `sessionCookieParams` contains unexpected keys: '
                        . implode('|', array_keys($diff)),
                        //
                        $sessionCookieParams,
                    ]
                );
            }

            $this->{$key} = [ $sessionCookieParamsValid ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useSessionCookieParams(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->sessionCookieParams) {
            $theHttpSession = Lib::httpSession();

            $refLast = $theHttpSession->session_set_cookie_params($this->sessionCookieParams[ 0 ]);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedSessionCookieParams(&$refLast = null)
    {
        $theHttpSession = Lib::httpSession();

        $refLast = $theHttpSession->session_set_cookie_params($this->mapRecommended[ 'sessionCookieParams' ]);

        return $this;
    }


    public function getPhpSessionSavePath() : string
    {
        $theHttpSession = Lib::httpSession();

        return $theHttpSession->session_save_path();
    }

    /**
     * @param string|false|null $sessionSavePath
     * @param bool|false|null   $sessionSavePathMkdir
     *
     * @return static
     */
    public function setSessionSavePath(
        $sessionSavePath,
        $sessionSavePathMkdir,
        ?bool $replace = null
    )
    {
        $this->assertNotLocked();

        if (false
            || (false !== $this->mapWasSet[ 'sessionSavePath' ])
            || (false !== $this->mapWasSet[ 'sessionSavePathMkdir' ])
        ) {
            if (! $replace) {
                return $this;
            }

        } else {
            $this->mapWasSet[ 'sessionSavePath' ] = true;
            $this->mapWasSet[ 'sessionSavePathMkdir' ] = true;
        }

        if (null === $sessionSavePath) {
            $this->sessionSavePath = [ $this->mapRecommended[ 'sessionSavePath' ] ];
            $this->sessionSavePathMkdir = [ $this->mapRecommended[ 'sessionSavePathMkdir' ] ];

        } elseif (false === $sessionSavePath) {
            $this->sessionSavePath = [ $this->mapInitial[ 'sessionSavePath' ] ];
            $this->sessionSavePathMkdir = [ $this->mapInitial[ 'sessionSavePathMkdir' ] ];

        } else {
            $theType = Lib::type();

            $sessionSavePathMkdirValid = (bool) $sessionSavePathMkdir;

            if ($sessionSavePathMkdirValid) {
                $sessionSavePathValid = $theType->dirpath($sessionSavePath, true)->orThrow();

            } else {
                $sessionSavePathValid = $theType->dirpath_realpath($sessionSavePath)->orThrow();
            }

            $this->sessionSavePath = [ $sessionSavePathValid ];
            $this->sessionSavePathMkdir = [ $sessionSavePathValid ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useSessionSavePath(&$refLast = null)
    {
        $refLast = null;

        if ([] !== $this->sessionSavePath) {
            $sessionSavePathValid = $this->sessionSavePath[ 0 ];

            if ([] !== $this->sessionSavePathMkdir) {
                $sessionSavePathMkdirValid = (bool) $this->sessionSavePathMkdir[ 0 ];

                if ($sessionSavePathMkdirValid) {
                    if (! is_dir($sessionSavePathValid)) {
                        mkdir($sessionSavePathValid, 0775, true);
                    }
                }
            }

            $theHttpSession = Lib::httpSession();

            $refLast = $theHttpSession->session_save_path($sessionSavePathValid);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useRecommendedSessionSavePath(&$refLast = null)
    {
        $theHttpSession = Lib::httpSession();

        $refLast = $theHttpSession->session_save_path($this->mapRecommended[ 'sessionSavePath' ]);

        return $this;
    }


    public function getPhpUploadMaxFilesize() : string
    {
        return ini_get('upload_max_filesize');
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
            $this->{$key} = [ $this->mapRecommended[ $key ] ];

        } elseif (false === $var) {
            $this->{$key} = [ $this->mapInitial[ $key ] ];

        } else {
            $theFormat = Lib::format();

            $varValidInt = $theFormat->bytes_decode([], $var);
            $varValidString = $theFormat->bytes_encode([], $varValidInt, 0, 1);

            $this->{$key} = [ $varValidString ];
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
        return ini_get('upload_tmp_dir');
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
        $theDebugThrowabler = Lib::debugThrowabler();

        $lines = $theDebugThrowabler->getPreviousMessagesAllLines(
            $throwable,
            0
            //
            | _DEBUG_THROWABLER_WITH_CODE
            | _DEBUG_THROWABLER_WITH_INFO
            | _DEBUG_THROWABLER_WITH_TRACE
            //
            | _DEBUG_THROWABLER_INFO_WITH_FILE
            | _DEBUG_THROWABLER_INFO_WITH_OBJECT_CLASS
        );

        echo implode("\n", $lines);

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
    public function useAll(?bool $lock = null)
    {
        $lock = $lock ?? true;

        $map = $this->mapInitial;
        unset($map[ 'sessionSavePathMkdir' ]);
        unset($map[ 'uploadTmpDirMkdir' ]);

        foreach ( $map as $key => $value ) {
            $ukey = ucfirst($key);

            $this->{'use' . $ukey}();
        }

        if ($lock) {
            $this->lock(true);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useAllRecommended(?bool $lock = null)
    {
        $lock = $lock ?? true;

        $map = $this->mapRecommended;
        unset($map[ 'sessionSavePathMkdir' ]);
        unset($map[ 'uploadTmpDirMkdir' ]);

        foreach ( $map as $key => $value ) {
            $ukey = ucfirst($key);

            $this->{'useRecommended' . $ukey}();
        }

        if ($lock) {
            $this->lock(true);
        }

        return $this;
    }


    protected function assertNotLocked() : void
    {
        if ([] !== $this->isLocked) {
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
