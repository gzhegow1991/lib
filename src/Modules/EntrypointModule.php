<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Arr\Map\Map;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\ErrorException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Arr\Map\Base\AbstractMap;


class EntrypointModule
{
    const OPT_ERROR_HANDLER             = 'errorHandler';
    const OPT_ERROR_HANDLER_ON_SHUTDOWN = 'errorHandlerOnShutdown';

    const OPT_EXCEPTION_HANDLER = 'exceptionHandler';
    const OPT_THROWABLE_HANDLER = 'throwableHandler';

    const OPT_DIR_ROOT = 'dirRoot';

    const OPT_ERROR_REPORTING = 'errorReporting';
    const OPT_ERROR_LOG       = 'errorLog';
    const OPT_LOG_ERRORS      = 'logErrors';
    const OPT_DISPLAY_ERRORS  = 'displayErrors';

    const OPT_MEMORY_LIMIT = 'memoryLimit';

    const OPT_MAX_EXECUTION_TIME = 'maxExecutionTime';
    const OPT_MAX_INPUT_TIME     = 'maxInputTime';

    const OPT_TIMEZONE_DEFAULT = 'timezoneDefault';

    const OPT_PRECISION = 'precision';

    const OPT_UMASK = 'umask';

    const OPT_POST_MAX_SIZE = 'postMaxSize';

    const OPT_SESSION_COOKIE_PARAMS = 'sessionCookieParams';
    const OPT_SESSION_SAVE_PATH     = 'sessionSavePath';

    const OPT_UPLOAD_MAX_FILESIZE = 'uploadMaxFilesize';
    const OPT_UPLOAD_TMP_DIR      = 'uploadTmpDir';

    const OPT_RET_COLLECT_TRACE = 'retCollectTrace';

    const LIST_OPT = [
        self::OPT_ERROR_HANDLER             => true,
        self::OPT_ERROR_HANDLER_ON_SHUTDOWN => true,
        //
        self::OPT_EXCEPTION_HANDLER         => true,
        self::OPT_THROWABLE_HANDLER         => true,
        //
        self::OPT_DIR_ROOT                  => true,
        //
        self::OPT_ERROR_REPORTING           => true,
        self::OPT_ERROR_LOG                 => true,
        self::OPT_LOG_ERRORS                => true,
        self::OPT_DISPLAY_ERRORS            => true,
        //
        self::OPT_MEMORY_LIMIT              => true,
        //
        self::OPT_MAX_EXECUTION_TIME        => true,
        self::OPT_MAX_INPUT_TIME            => true,
        //
        self::OPT_TIMEZONE_DEFAULT          => true,
        //
        self::OPT_PRECISION                 => true,
        //
        self::OPT_UMASK                     => true,
        //
        self::OPT_POST_MAX_SIZE             => true,
        //
        self::OPT_SESSION_COOKIE_PARAMS     => true,
        self::OPT_SESSION_SAVE_PATH         => true,
        //
        self::OPT_UPLOAD_MAX_FILESIZE       => true,
        self::OPT_UPLOAD_TMP_DIR            => true,
        //
        self::OPT_RET_COLLECT_TRACE         => true,
    ];


    /**
     * @var array{ 0: string, 1: string }
     */
    protected $isLocked = [];

    /**
     * @var array<string, mixed>
     */
    protected $mapInitial = [];
    /**
     * @var array<string, mixed>
     */
    protected $mapRecommended = [];
    /**
     * @var array<string, mixed>
     */
    protected $mapSet = [];
    /**
     * @var array<string, mixed>
     */
    protected $mapCurrent = [];

    /**
     * @var bool
     */
    protected $signalIgnoreShutdownFunctions = false;
    /**
     * @var AbstractMap
     */
    protected $registerShutdownFunctionMap;

    /**
     * @var \Throwable[]
     */
    protected $throwablesOnShutdown = [];


    public function __construct()
    {
        $this->registerShutdownFunctionMap = Map::new();

        $this->mapInitial = [
            static::OPT_ERROR_HANDLER             => $this->getPhpErrorHandler(),
            static::OPT_ERROR_HANDLER_ON_SHUTDOWN => null,
            //
            static::OPT_EXCEPTION_HANDLER         => $this->getPhpExceptionHandler(),
            static::OPT_THROWABLE_HANDLER         => null,
            //
            static::OPT_DIR_ROOT                  => null,
            //
            static::OPT_ERROR_REPORTING           => $this->getPhpErrorReporting(),
            static::OPT_ERROR_LOG                 => $this->getPhpErrorLog(),
            static::OPT_LOG_ERRORS                => $this->getPhpLogErrors(),
            static::OPT_DISPLAY_ERRORS            => $this->getPhpDisplayErrors(),
            //
            static::OPT_MEMORY_LIMIT              => $this->getPhpMemoryLimit(),
            //
            static::OPT_MAX_EXECUTION_TIME        => $this->getPhpMaxExecutionTime(),
            static::OPT_MAX_INPUT_TIME            => $this->getPhpMaxInputTime(),
            //
            static::OPT_TIMEZONE_DEFAULT          => $this->getPhpTimezoneDefault(),
            //
            static::OPT_PRECISION                 => $this->getPhpPrecision(),
            //
            static::OPT_UMASK                     => $this->getPhpUmask(),
            //
            static::OPT_POST_MAX_SIZE             => $this->getPhpPostMaxSize(),
            //
            static::OPT_SESSION_COOKIE_PARAMS     => $this->getPhpSessionCookieParams(),
            static::OPT_SESSION_SAVE_PATH         => $this->getPhpSessionSavePath(),
            //
            static::OPT_UPLOAD_MAX_FILESIZE       => $this->getPhpUploadMaxFilesize(),
            static::OPT_UPLOAD_TMP_DIR            => $this->getPhpUploadTmpDir(),
            //
            static::OPT_RET_COLLECT_TRACE         => false,
        ];

        $this->mapRecommended = [
            static::OPT_ERROR_HANDLER             => [ $this, 'fnErrorHandler' ],
            static::OPT_ERROR_HANDLER_ON_SHUTDOWN => [ $this, 'fnErrorHandlerOnShutdown' ],
            //
            static::OPT_EXCEPTION_HANDLER         => [ $this, 'fnExceptionHandler' ],
            static::OPT_THROWABLE_HANDLER         => [ $this, 'fnThrowableHandler' ],
            //
            static::OPT_DIR_ROOT                  => null,
            //
            static::OPT_ERROR_REPORTING           => (E_ALL | E_DEPRECATED | E_USER_DEPRECATED),
            static::OPT_ERROR_LOG                 => null,
            static::OPT_LOG_ERRORS                => 0,
            static::OPT_DISPLAY_ERRORS            => 0,
            //
            static::OPT_MEMORY_LIMIT              => '32M',
            //
            static::OPT_MAX_EXECUTION_TIME        => 10,
            static::OPT_MAX_INPUT_TIME            => -1,
            //
            static::OPT_TIMEZONE_DEFAULT          => new \DateTimeZone('UTC'),
            //
            static::OPT_PRECISION                 => 16,
            //
            static::OPT_UMASK                     => 0002,
            //
            static::OPT_POST_MAX_SIZE             => '1M',
            //
            static::OPT_SESSION_COOKIE_PARAMS     => [
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ],
            static::OPT_SESSION_SAVE_PATH         => null,
            //
            static::OPT_UPLOAD_MAX_FILESIZE       => '0',
            static::OPT_UPLOAD_TMP_DIR            => null,
            //
            static::OPT_RET_COLLECT_TRACE         => false,
        ];

        foreach ( $this->mapInitial as $key => $value ) {
            $this->mapCurrent[$key] = $value;
        }
    }

    public function __initialize()
    {
        return $this;
    }


    protected function hasOpt(string $opt, &$refValue = null) : bool
    {
        $refValue = null;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $refValue = $this->mapCurrent[$opt];

            return true;
        }

        return false;
    }

    protected function setOpt(string $opt, $value, ?bool $replace = null)
    {
        $this->assertNotLocked();

        $isAlreadySet = array_key_exists($opt, $this->mapSet);
        if ( $isAlreadySet ) {
            if ( true === $replace ) {
                //

            } elseif ( false === $replace ) {
                throw new RuntimeException(
                    [ 'The `opt` is already set: ' . $opt, $opt, $this->mapSet[ $opt ] ],
                );

            } else {
                return $this;
            }
        }

        if ( null === $value ) {
            if ( array_key_exists($opt, $this->mapRecommended) ) {
                $this->mapCurrent[$opt] = $this->mapRecommended[$opt];
            }

        } elseif ( false === $value ) {
            if ( array_key_exists($opt, $this->mapInitial) ) {
                $this->mapCurrent[$opt] = $this->mapInitial[$opt];
            }

        } else {
            $fn = '_set' . ucfirst($opt);

            $varValid = $this->{$fn}($value);

            $this->mapSet[$opt] = $varValid;

            $this->mapCurrent[$opt] = $varValid;
        }

        return $this;
    }

    protected function useOpt(string $opt, &$refLast = null)
    {
        $refLast = $this->mapCurrent[$opt] ?? null;

        if ( array_key_exists($opt, $this->mapSet) ) {
            $this->mapCurrent[$opt] = $this->mapSet[$opt];
        }

        if ( ! array_key_exists($opt, $this->mapCurrent) ) {
            return $this;
        }

        $fn = '_use' . ucfirst($opt);

        $this->{$fn}();

        return $this;
    }

    protected function useRecommendedOpt(string $opt, &$refLast = null)
    {
        $refLast = $this->mapCurrent[$opt] ?? null;

        if ( ! array_key_exists($opt, $this->mapRecommended) ) {
            return $this;
        }

        $this->mapCurrent[$opt] = $this->mapRecommended[$opt];

        $fn = '_use' . ucfirst($opt);

        $this->{$fn}();

        return $this;
    }


    /**
     * @param string|null $refValue
     */
    public function hasDirRoot(&$refValue = null) : bool
    {
        $opt = static::OPT_DIR_ROOT;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param string|false|null $value
     *
     * @return static
     */
    public function setDirRoot($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_DIR_ROOT;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useDirRoot(&$refLast = null)
    {
        $opt = static::OPT_DIR_ROOT;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedDirRoot(&$refLast = null)
    {
        $opt = static::OPT_DIR_ROOT;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setDirRoot($var)
    {
        $theType = Lib::type();

        $varValid = $theType->dirpath_realpath($var)->orThrow();

        return $varValid;
    }

    protected function _useDirRoot()
    {
        $opt = static::OPT_DIR_ROOT;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            if ( null === $var ) {
                DebugModule::staticDirRoot(false);

            } else {
                DebugModule::staticDirRoot($var);
            }
        }
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
     * @param int|null $refValue
     */
    public function hasErrorReporting(&$refValue = null) : bool
    {
        $opt = static::OPT_ERROR_REPORTING;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param int|false|null $value
     *
     * @return static
     */
    public function setErrorReporting($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_ERROR_REPORTING;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useErrorReporting(&$refLast = null)
    {
        $opt = static::OPT_ERROR_REPORTING;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedErrorReporting(&$refLast = null)
    {
        $opt = static::OPT_ERROR_REPORTING;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setErrorReporting($var)
    {
        if ( 0 !== ($var & ~(E_ALL | E_DEPRECATED | E_USER_DEPRECATED)) ) {
            throw new LogicException(
                [ 'The `errorReporting` should be valid flag', $var ]
            );
        }

        return $var;
    }

    protected function _useErrorReporting()
    {
        $opt = static::OPT_ERROR_REPORTING;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            error_reporting($var);
        }
    }


    /**
     * @return string|false
     */
    public function getPhpErrorLog()
    {
        return ini_get('error_log');
    }

    /**
     * @param string|null $refValue
     */
    public function hasErrorLog(&$refValue = null) : bool
    {
        $opt = static::OPT_ERROR_LOG;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param string|false|null $value
     *
     * @return static
     */
    public function setErrorLog($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_ERROR_LOG;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useErrorLog(&$refLast = null)
    {
        $opt = static::OPT_ERROR_LOG;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedErrorLog(&$refLast = null)
    {
        $opt = static::OPT_ERROR_LOG;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setErrorLog($var)
    {
        $theType = Lib::type();

        $varValid = $theType->filepath($var, true)->orThrow();

        return $varValid;
    }

    protected function _useErrorLog()
    {
        $opt = static::OPT_ERROR_LOG;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            ini_set('error_log', $var);
        }
    }


    /**
     * @return string|false
     */
    public function getPhpLogErrors()
    {
        return ini_get('log_errors');
    }

    /**
     * @param bool|null $refValue
     */
    public function hasLogErrors(&$refValue = null) : bool
    {
        $opt = static::OPT_LOG_ERRORS;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param bool|false|null $value
     *
     * @return static
     */
    public function setLogErrors($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_LOG_ERRORS;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useLogErrors(&$refLast = null)
    {
        $opt = static::OPT_LOG_ERRORS;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedLogErrors(&$refLast = null)
    {
        $opt = static::OPT_LOG_ERRORS;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setLogErrors($var)
    {
        $theType = Lib::type();

        $varValid = $theType->bool($var)->orThrow();

        return (int) $varValid;
    }

    protected function _useLogErrors()
    {
        $opt = static::OPT_LOG_ERRORS;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            ini_set('log_errors', $var);
        }
    }


    /**
     * @return string|false
     */
    public function getPhpDisplayErrors()
    {
        return ini_get('display_errors');
    }

    /**
     * @param bool|null $refValue
     */
    public function hasDisplayErrors(&$refValue = null) : bool
    {
        $opt = static::OPT_DISPLAY_ERRORS;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param bool|false|null $value
     *
     * @return static
     */
    public function setDisplayErrors($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_DISPLAY_ERRORS;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useDisplayErrors(&$refLast = null)
    {
        $opt = static::OPT_DISPLAY_ERRORS;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedDisplayErrors(&$refLast = null)
    {
        $opt = static::OPT_DISPLAY_ERRORS;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setDisplayErrors($var)
    {
        $theType = Lib::type();

        $varValid = $theType->bool($var)->orThrow();

        return (int) $varValid;
    }

    protected function _useDisplayErrors()
    {
        $opt = static::OPT_DISPLAY_ERRORS;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            ini_set('display_errors', $var);
            ini_set('display_startup_errors', $var);
        }
    }


    public function getPhpMemoryLimit() : string
    {
        return ini_get('memory_limit');
    }

    /**
     * @param string|null $refValue
     */
    public function hasMemoryLimit(&$refValue = null) : bool
    {
        $opt = static::OPT_MEMORY_LIMIT;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param string|false|null $value
     *
     * @return static
     */
    public function setMemoryLimit($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_MEMORY_LIMIT;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useMemoryLimit(&$refLast = null)
    {
        $opt = static::OPT_MEMORY_LIMIT;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedMemoryLimit(&$refLast = null)
    {
        $opt = static::OPT_MEMORY_LIMIT;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setMemoryLimit($var)
    {
        $theFormat = Lib::format();

        $varValidInt = $theFormat->bytes_decode([], $var);
        $varValidString = $theFormat->bytes_encode([], $varValidInt, 0, 1);

        return $varValidString;
    }

    protected function _useMemoryLimit()
    {
        $opt = static::OPT_MEMORY_LIMIT;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            ini_set('memory_limit', $var);
        }
    }


    public function getPhpMaxExecutionTime() : string
    {
        return ini_get('max_execution_time');
    }

    /**
     * @param int|null $refValue
     */
    public function hasMaxExecutionTime(&$refValue = null) : bool
    {
        $opt = static::OPT_MAX_EXECUTION_TIME;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param int|false|null $value
     *
     * @return static
     */
    public function setMaxExecutionTime($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_MAX_EXECUTION_TIME;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useMaxExecutionTime(&$refLast = null)
    {
        $opt = static::OPT_MAX_EXECUTION_TIME;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedMaxExecutionTime(&$refLast = null)
    {
        $opt = static::OPT_MAX_EXECUTION_TIME;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setMaxExecutionTime($var)
    {
        $theType = Lib::type();

        $varValid = $theType->int_non_negative($var)->orThrow();

        return $varValid;
    }

    protected function _useMaxExecutionTime()
    {
        $opt = static::OPT_MAX_EXECUTION_TIME;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            ini_set('max_execution_time', $var);
        }
    }


    public function getPhpMaxInputTime() : string
    {
        return ini_get('max_input_time');
    }

    /**
     * @param int|null $refValue
     */
    public function hasMaxInputTime(&$refValue = null) : bool
    {
        $opt = static::OPT_MAX_INPUT_TIME;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param int|false|null $value
     *
     * @return static
     */
    public function setMaxInputTime($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_MAX_INPUT_TIME;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useMaxInputTime(&$refLast = null)
    {
        $opt = static::OPT_MAX_INPUT_TIME;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedMaxInputTime(&$refLast = null)
    {
        $opt = static::OPT_MAX_INPUT_TIME;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setMaxInputTime($var)
    {
        $theType = Lib::type();

        $varValid = $theType->int_non_negative_or_minus_one($var)->orThrow();

        return $varValid;
    }

    protected function _useMaxInputTime()
    {
        $opt = static::OPT_MAX_INPUT_TIME;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            ini_set('max_input_time', $var);
        }
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
     * @param \DateTimeZone|null $refValue
     */
    public function hasTimezoneDefault(&$refValue = null) : bool
    {
        $opt = static::OPT_TIMEZONE_DEFAULT;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param string|\DateTimeZone|false|null $value
     *
     * @return static
     */
    public function setTimezoneDefault($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_TIMEZONE_DEFAULT;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useTimezoneDefault(&$refLast = null)
    {
        $opt = static::OPT_TIMEZONE_DEFAULT;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedTimezoneDefault(&$refLast = null)
    {
        $opt = static::OPT_TIMEZONE_DEFAULT;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setTimezoneDefault($var)
    {
        $theType = Lib::type();

        $varValid = $theType->timezone($var)->orThrow();

        return $varValid;
    }

    protected function _useTimezoneDefault()
    {
        $opt = static::OPT_TIMEZONE_DEFAULT;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            if ( null !== $var ) {
                date_default_timezone_set($var->getName());
            }
        }
    }


    public function getPhpPrecision() : string
    {
        return ini_get('precision');
    }

    /**
     * @param string|null $refValue
     */
    public function hasPrecision(&$refValue = null) : bool
    {
        $opt = static::OPT_PRECISION;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param int|false|null $value
     *
     * @return static
     */
    public function setPrecision($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_PRECISION;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function usePrecision(&$refLast = null)
    {
        $opt = static::OPT_PRECISION;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedPrecision(&$refLast = null)
    {
        $opt = static::OPT_PRECISION;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setPrecision($var)
    {
        $theType = Lib::type();

        $varValid = $theType->int_non_negative($var)->orThrow();

        return $varValid;
    }

    protected function _usePrecision()
    {
        $opt = static::OPT_PRECISION;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            ini_set('precision', $var);
        }
    }


    public function getPhpUmask() : string
    {
        $umaskTmp = $umaskTmp ?? 0002;

        $before = umask($umaskTmp);

        umask($before);

        return $before;
    }

    /**
     * @param int|null $refValue
     */
    public function hasUmask(&$refValue = null) : bool
    {
        $opt = static::OPT_UMASK;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param int|false|null $value
     *
     * @return static
     */
    public function setUmask($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_UMASK;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useUmask(&$refLast = null)
    {
        $opt = static::OPT_UMASK;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedUmask(&$refLast = null)
    {
        $opt = static::OPT_UMASK;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setUmask($var)
    {
        if ( ! (($var >= 0) && ($var <= 0777)) ) {
            throw new LogicException(
                [ 'The `umask` should be a valid `umask`', $var ]
            );
        }

        return $var;
    }

    protected function _useUmask()
    {
        $opt = static::OPT_UMASK;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            umask($var);
        }
    }


    public function getPhpPostMaxSize() : string
    {
        return ini_get('post_max_size');
    }

    /**
     * @param string|null $refValue
     */
    public function hasPostMaxSize(&$refValue = null) : bool
    {
        $opt = static::OPT_POST_MAX_SIZE;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param string|false|null $value
     *
     * @return static
     */
    public function setPostMaxSize($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_POST_MAX_SIZE;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function usePostMaxSize(&$refLast = null)
    {
        $opt = static::OPT_POST_MAX_SIZE;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedPostMaxSize(&$refLast = null)
    {
        $opt = static::OPT_POST_MAX_SIZE;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setPostMaxSize($var)
    {
        $theFormat = Lib::format();

        $varValidInt = $theFormat->bytes_decode([], $var);
        $varValidString = $theFormat->bytes_encode([], $varValidInt, 0, 1);

        return $varValidString;
    }

    protected function _usePostMaxSize()
    {
        $opt = static::OPT_POST_MAX_SIZE;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            ini_set('post_max_size', $var);
        }
    }


    public function getPhpSessionCookieParams() : array
    {
        $theHttpSession = Lib::httpSession();

        return $theHttpSession->session_get_cookie_params();
    }

    /**
     * @param array|null $refValue
     */
    public function hasSessionCookieParams(&$refValue = null) : bool
    {
        $opt = static::OPT_SESSION_COOKIE_PARAMS;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param array|false|null $value
     *
     * @return static
     */
    public function setSessionCookieParams($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_SESSION_COOKIE_PARAMS;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useSessionCookieParams(&$refLast = null)
    {
        $opt = static::OPT_SESSION_COOKIE_PARAMS;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedSessionCookieParams(&$refLast = null)
    {
        $opt = static::OPT_SESSION_COOKIE_PARAMS;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setSessionCookieParams($var)
    {
        $theType = Lib::type();

        $varValid = $theType->array($var)->orThrow();
        $varValidScheme = [
            'lifetime' => null,
            'path'     => null,
            'domain'   => null,
            'secure'   => null,
            'httponly' => null,
            'samesite' => null,
        ];

        if ( $diff = array_diff_key($varValid, $varValidScheme) ) {
            throw new RuntimeException(
                [
                    ''
                    . 'The `sessionCookieParams` contains unexpected keys: '
                    . implode('|', array_keys($diff)),
                    //
                    $varValid,
                ]
            );
        }

        return $varValid;
    }

    protected function _useSessionCookieParams()
    {
        $opt = static::OPT_SESSION_COOKIE_PARAMS;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $theHttpSession = Lib::httpSession();

            $var = $this->mapCurrent[$opt];

            $theHttpSession->session_set_cookie_params($var);
        }
    }


    public function getPhpSessionSavePath() : string
    {
        $theHttpSession = Lib::httpSession();

        return $theHttpSession->session_save_path();
    }

    /**
     * @param string|null $refValue
     */
    public function hasSessionSavePath(&$refValue = null) : bool
    {
        $opt = static::OPT_SESSION_SAVE_PATH;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param string|false|null $value
     *
     * @return static
     */
    public function setSessionSavePath($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_SESSION_SAVE_PATH;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useSessionSavePath(&$refLast = null)
    {
        $opt = static::OPT_SESSION_SAVE_PATH;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedSessionSavePath(&$refLast = null)
    {
        $opt = static::OPT_SESSION_SAVE_PATH;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setSessionSavePath($var)
    {
        $theType = Lib::type();

        $varValid = $theType->dirpath($var, true)->orThrow();

        return $varValid;
    }

    protected function _useSessionSavePath()
    {
        $opt = static::OPT_SESSION_SAVE_PATH;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $theFsFile = Lib::fsFile();
            $theHttpSession = Lib::httpSession();

            $var = $this->mapCurrent[$opt];

            if ( null !== $var ) {
                $theFsFile->mkdirp($var, 0775, true);
            }

            $theHttpSession->session_save_path($var);
        }
    }


    public function getPhpUploadMaxFilesize() : string
    {
        return ini_get('upload_max_filesize');
    }

    /**
     * @param string|null $refValue
     */
    public function hasUploadMaxFilesize(&$refValue = null) : bool
    {
        $opt = static::OPT_UPLOAD_MAX_FILESIZE;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param string|false|null $value
     *
     * @return static
     */
    public function setUploadMaxFilesize($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_UPLOAD_MAX_FILESIZE;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useUploadMaxFilesize(&$refLast = null)
    {
        $opt = static::OPT_UPLOAD_MAX_FILESIZE;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedUploadMaxFilesize(&$refLast = null)
    {
        $opt = static::OPT_UPLOAD_MAX_FILESIZE;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setUploadMaxFilesize($var)
    {
        $theFormat = Lib::format();

        $varValidInt = $theFormat->bytes_decode([], $var);
        $varValidString = $theFormat->bytes_encode([], $varValidInt, 0, 1);

        return $varValidString;
    }

    protected function _useUploadMaxFilesize()
    {
        $opt = static::OPT_UPLOAD_MAX_FILESIZE;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            ini_set('upload_max_filesize', $var);
        }
    }


    public function getPhpUploadTmpDir() : string
    {
        return ini_get('upload_tmp_dir');
    }

    /**
     * @param string|null $refValue
     */
    public function hasUploadTmpDir(&$refValue = null) : bool
    {
        $opt = static::OPT_UPLOAD_TMP_DIR;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param string|false|null $value
     *
     * @return static
     */
    public function setUploadTmpDir($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_UPLOAD_TMP_DIR;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useUploadTmpDir(&$refLast = null)
    {
        $opt = static::OPT_UPLOAD_TMP_DIR;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedUploadTmpDir(&$refLast = null)
    {
        $opt = static::OPT_UPLOAD_TMP_DIR;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setUploadTmpDir($var)
    {
        $theType = Lib::type();

        $varValid = $theType->dirpath($var, true)->orThrow();

        return $varValid;
    }

    protected function _useUploadTmpDir()
    {
        $opt = static::OPT_UPLOAD_TMP_DIR;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $theFsFile = Lib::fsFile();

            $var = $this->mapCurrent[$opt];

            if ( null !== $var ) {
                $theFsFile->mkdirp($var, 0775, true);
            }

            ini_set('upload_tmp_dir', $var);
        }
    }


    /**
     * @param bool|null $refValue
     */
    public function hasRetCollectTrace(&$refValue = null) : bool
    {
        $opt = static::OPT_RET_COLLECT_TRACE;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param callable|false|null $value
     *
     * @return static
     */
    public function setRetCollectTrace($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_RET_COLLECT_TRACE;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useRetCollectTrace(&$refLast = null)
    {
        $opt = static::OPT_RET_COLLECT_TRACE;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedRetCollectTrace(&$refLast = null)
    {
        $opt = static::OPT_RET_COLLECT_TRACE;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setRetCollectTrace($var)
    {
        return (bool) $var;
    }

    protected function _useRetCollectTrace()
    {
        //
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
     * @param callable|null $refValue
     */
    public function hasErrorHandler(&$refValue = null) : bool
    {
        $opt = static::OPT_ERROR_HANDLER;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param callable|false|null $value
     *
     * @return static
     */
    public function setErrorHandler($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_ERROR_HANDLER;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useErrorHandler(&$refLast = null)
    {
        $opt = static::OPT_ERROR_HANDLER;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedErrorHandler(&$refLast = null)
    {
        $opt = static::OPT_ERROR_HANDLER;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setErrorHandler($var)
    {
        if ( ! is_callable($var) ) {
            throw new LogicException(
                [ 'The `fnErrorHandler` should be a callable', $var ]
            );
        }

        return $var;
    }

    protected function _useErrorHandler()
    {
        $opt = static::OPT_ERROR_HANDLER;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            set_error_handler([ $this, 'fnErrorHandlerMain' ]);

            $this->registerShutdownFunction([ $this, 'onShutdown_fatalErrorOnShutdown' ]);
            $this->registerShutdownFunction([ $this, 'onShutdown_throwablesOnShutdown' ]);
        }
    }


    /**
     * @param callable|null $refValue
     */
    public function hasErrorHandlerOnShutdown(&$refValue = null) : bool
    {
        $opt = static::OPT_ERROR_HANDLER_ON_SHUTDOWN;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param callable|false|null $value
     *
     * @return static
     */
    public function setErrorHandlerOnShutdown($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_ERROR_HANDLER_ON_SHUTDOWN;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useErrorHandlerOnShutdown(&$refLast = null)
    {
        $opt = static::OPT_ERROR_HANDLER_ON_SHUTDOWN;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedErrorHandlerOnShutdown(&$refLast = null)
    {
        $opt = static::OPT_ERROR_HANDLER_ON_SHUTDOWN;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setErrorHandlerOnShutdown($var)
    {
        if ( ! is_callable($var) ) {
            throw new LogicException(
                [ 'The `fnErrorHandlerOnShutdown` should be a callable', $var ]
            );
        }

        return $var;
    }

    protected function _useErrorHandlerOnShutdown()
    {
        $opt = static::OPT_ERROR_HANDLER_ON_SHUTDOWN;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            set_error_handler([ $this, 'fnErrorHandlerMain' ]);

            $this->registerShutdownFunction([ $this, 'onShutdown_fatalErrorOnShutdown' ]);
            $this->registerShutdownFunction([ $this, 'onShutdown_throwablesOnShutdown' ]);
        }
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
     * @param callable|null $refValue
     */
    public function hasExceptionHandler(&$refValue = null) : bool
    {
        $opt = static::OPT_EXCEPTION_HANDLER;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param callable|false|null $value
     *
     * @return static
     */
    public function setExceptionHandler($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_EXCEPTION_HANDLER;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useExceptionHandler(&$refLast = null)
    {
        $opt = static::OPT_EXCEPTION_HANDLER;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedExceptionHandler(&$refLast = null)
    {
        $opt = static::OPT_EXCEPTION_HANDLER;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setExceptionHandler($var)
    {
        if ( ! is_callable($var) ) {
            throw new LogicException(
                [ 'The `fnExceptionHandler` should be a callable', $var ]
            );
        }

        return $var;
    }

    protected function _useExceptionHandler()
    {
        $opt = static::OPT_EXCEPTION_HANDLER;

        if ( array_key_exists($opt, $this->mapCurrent) ) {
            $var = $this->mapCurrent[$opt];

            set_exception_handler($var);
        }
    }


    /**
     * @param callable|null $refValue
     */
    public function hasThrowableHandler(&$refValue = null) : bool
    {
        $opt = static::OPT_THROWABLE_HANDLER;

        return $this->hasOpt($opt, $refValue);
    }

    /**
     * @param callable|false|null $value
     *
     * @return static
     */
    public function setThrowableHandler($value, ?bool $replace = null)
    {
        $replace = $replace ?? false;

        $opt = static::OPT_THROWABLE_HANDLER;

        $this->setOpt($opt, $value, $replace);

        return $this;
    }

    public function useThrowableHandler(&$refLast = null)
    {
        $opt = static::OPT_THROWABLE_HANDLER;

        $this->useOpt($opt, $refLast);

        return $this;
    }

    public function useRecommendedThrowableHandler(&$refLast = null)
    {
        $opt = static::OPT_THROWABLE_HANDLER;

        $this->useRecommendedOpt($opt, $refLast);

        return $this;
    }

    protected function _setThrowableHandler($var)
    {
        if ( ! is_callable($var) ) {
            throw new LogicException(
                [ 'The `fnThrowableHandler` should be a callable', $var ]
            );
        }

        return $var;
    }

    protected function _useThrowableHandler()
    {
        //
    }


    /**
     * @throws ErrorException
     */
    public function fnErrorHandlerMain($errno, $errstr, $errfile, $errline) : void
    {
        $e = null;

        if ( null === $e ) {
            if ( $this->hasErrorHandlerOnShutdown($fn) ) {
                if ( null !== $fn ) {
                    $e = $fn($errno, $errstr, $errfile, $errline);
                }
            }
        }

        if ( null === $e ) {
            if ( $this->hasErrorHandler($fn) ) {
                if ( null !== $fn ) {
                    $e = $fn($errno, $errstr, $errfile, $errline);
                }
            }
        }
    }

    public function fnErrorHandlerOnShutdown($errno, $errstr, $errfile, $errline)
    {
        $isErrorHeadersAlreadySent = (false !== strpos($errstr, 'Cannot modify header information'));

        $isThrowableOnShutdown = false
            || $isErrorHeadersAlreadySent;

        if ( $isThrowableOnShutdown ) {
            $e = new ErrorException($errstr, -1, $errno, $errfile, $errline);

            $trace = debug_backtrace();

            array_shift($trace);

            $e->setTrace($trace);

            $this->addThrowableOnShutdown($e);

            return $this;
        }

        return null;
    }

    /**
     * @throws ErrorException
     */
    public function fnErrorHandler($errno, $errstr, $errfile, $errline) : void
    {
        if ( ! (error_reporting() & $errno) ) {
            return;
        }

        throw new ErrorException($errstr, -1, $errno, $errfile, $errline);
    }


    public function fnExceptionHandler(\Throwable $e) : void
    {
        if ( $this->hasThrowableHandler($fn) ) {
            if ( null !== $fn ) {
                $fn($e);
            }
        }

        exit(1);
    }


    public function fnThrowableHandler(\Throwable $e) : void
    {
        $theDebugThrowabler = Lib::debugThrowabler();

        $lines = $theDebugThrowabler->getPreviousMessagesAllLines(
            $e,
            0
            //
            | _DEBUG_THROWABLER_WITH_CODE
            | _DEBUG_THROWABLER_WITH_INFO
            | _DEBUG_THROWABLER_WITH_TRACE
            //
            | _DEBUG_THROWABLER_INFO_WITH_FILE
            | _DEBUG_THROWABLER_INFO_WITH_OBJECT_CLASS
            | _DEBUG_THROWABLER_INFO_WITHOUT_OBJECT_ID
        );

        echo "\n" . implode("\n", $lines) . "\n";
    }

    public function onShutdown_fatalErrorOnShutdown() : void
    {
        $err = error_get_last();
        if ( null === $err ) return;
        if ( ! in_array($err['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ]) ) return;

        $e = new ErrorException($err['message'], -1, $err['type'], $err['file'], $err['line']);

        $this->addThrowableOnShutdown($e);
    }


    /**
     * @return static
     */
    public function setAllInitial(?bool $replace = null)
    {
        $replace = $replace ?? true;

        $this->assertNotLocked();

        foreach ( $this->mapInitial as $opt => $value ) {
            if ( $isAlreadySet = array_key_exists($opt, $this->mapSet) ) {
                if ( true === $replace ) {
                    //

                } elseif ( false === $replace ) {
                    throw new RuntimeException(
                        [ 'The `opt` is already set: ' . $opt, $opt, $this->mapSet[ $opt ] ],
                    );

                } else {
                    continue;
                }
            }

            $this->mapCurrent[$opt] = $value;
            $this->mapSet[$opt] = $value;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function setAllRecommended(?bool $replace = null)
    {
        $replace = $replace ?? true;

        $this->assertNotLocked();

        foreach ( $this->mapRecommended as $opt => $value ) {
            if ( $isAlreadySet = array_key_exists($opt, $this->mapSet) ) {
                if ( true === $replace ) {
                    //

                } elseif ( false === $replace ) {
                    throw new RuntimeException(
                        [ 'The `opt` is already set: ' . $opt, $opt, $this->mapSet[ $opt ] ],
                    );

                } else {
                    continue;
                }
            }

            $this->mapCurrent[$opt] = $value;
            $this->mapSet[$opt] = $value;
        }

        return $this;
    }


    /**
     * @return static
     */
    public function useAll(?bool $lock = null)
    {
        $lock = $lock ?? true;

        foreach ( array_keys($this->mapCurrent) as $key ) {
            $fn = 'use' . ucfirst($key);

            $this->{$fn}();
        }

        if ( $lock ) {
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

        foreach ( array_keys($this->mapRecommended) as $key ) {
            $fn = 'useRecommended' . ucfirst($key);

            $this->{$fn}();
        }

        if ( $lock ) {
            $this->lock(true);
        }

        return $this;
    }


    /**
     * @return \Throwable[]
     */
    public function getThrowablesOnShutdown() : array
    {
        return $this->throwablesOnShutdown;
    }

    /**
     * @return static
     */
    protected function addThrowableOnShutdown(\Throwable $e)
    {
        $this->throwablesOnShutdown[] = $e;

        $this->registerShutdownFunction([ $this, 'onShutdown_throwablesOnShutdown' ]);

        return $this;
    }

    public function onShutdown_throwablesOnShutdown() : void
    {
        if ( [] === $this->throwablesOnShutdown ) {
            return;
        }

        if ( $this->hasThrowableHandler($fn) ) {
            if ( null !== $fn ) {
                foreach ( $this->throwablesOnShutdown as $e ) {
                    $fn($e);
                }
            }
        }
    }


    public function isLocked(?array &$fileLine = null) : bool
    {
        $fileLine = null;

        if ( [] !== $this->isLocked ) {
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

        if ( $lock ) {
            $this->isLocked = $theDebug->file_line();

        } else {
            $this->isLocked = [];
        }

        return $this;
    }

    protected function assertNotLocked() : void
    {
        if ( [] !== $this->isLocked ) {
            throw new RuntimeException(
                [
                    'Unable to change entrypoint parameters due to it was locked before',
                    $this->isLocked,
                ]
            );
        }
    }


    /**
     * > проверяет наличие функции в списке перед тем, как ее регистрировать, например, если регистрация функций происходит в цикле
     * > также учитывает глобальный сигнал на игнор таких функций, чтобы отключить их единым переключателем
     * > переопределяет функцию, если по уже зарегистрированному ключу приходит другой callable
     *
     * @param callable $fn
     */
    public function registerShutdownFunction($fn, ?string $name = null) : void
    {
        $mapKey = (null === $name)
            ? $fn
            : $name;

        $isExists = $this->registerShutdownFunctionMap->exists($mapKey, $mapValue);

        if ( $isExists ) {
            if ( $fn !== $mapValue['fn'] ) {
                $mapValue['active'] = true;
                $mapValue['fn'] = $fn;

                $this->registerShutdownFunctionMap->replace($mapKey, $mapValue);
            }

        } else {
            $fnWrapper = function () use (&$fnWrapper, $fn, $mapKey) {
                if ( $this->signalIgnoreShutdownFunctions ) {
                    return;
                }

                $mapValue = $this->registerShutdownFunctionMap->get($mapKey);

                [
                    'active' => $active,
                    'fn'     => $fn,
                ] = $mapValue;

                if ( ! $active ) {
                    return;
                }

                if ( null !== $fn ) {
                    call_user_func($fn);
                }
            };

            $mapValue = [
                'active' => true,
                'fn'     => $fn,
            ];

            $this->registerShutdownFunctionMap->add($mapKey, $mapValue);

            register_shutdown_function($fnWrapper);
        }
    }

    /**
     * > отключает ранее зарегистрированную shutdown-функцию
     *
     * @param callable|string $fnOrName
     */
    public function unregisterShutdownFunction($fnOrName) : void
    {
        $mapKey = $fnOrName;

        if ( ! $this->registerShutdownFunctionMap->exists($mapKey, $mapValue) ) {
            return;
        }

        $mapValue['active'] = false;
        $mapValue['fn'] = null;

        $this->registerShutdownFunctionMap->replace($fnOrName, $mapValue);
    }


    /**
     * @param int|string $status
     */
    public function die($status, ?bool $ignoreShutdownFunctions = null)
    {
        $status = $status ?? '';
        $ignoreShutdownFunctions = $ignoreShutdownFunctions ?? true;

        $this->signalIgnoreShutdownFunctions = $ignoreShutdownFunctions;

        die($status);
    }

    /**
     * @param int|string $status
     */
    public function exit($status, ?bool $ignoreShutdownFunctions = null)
    {
        $status = $status ?? '';
        $ignoreShutdownFunctions = $ignoreShutdownFunctions ?? true;

        $this->signalIgnoreShutdownFunctions = $ignoreShutdownFunctions;

        exit($status);
    }
}
