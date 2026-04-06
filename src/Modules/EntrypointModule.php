<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpUmaskDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointDriverInterface;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpErrorLogDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpLogErrorsDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpPrecisionDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointCustomDirRootDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpMemoryLimitDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpPostMaxSizeDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpMaxInputTimeDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpUploadTmpDirDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpErrorHandlerDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpDisplayErrorsDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpErrorReportingDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointCustomShouldTraceDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpSessionSavePathDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpMaxExecutionTimeDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpExceptionHandlerDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpUploadMaxFilesizeDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpDateTimezoneDefaultDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpSessionCookieParamsDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointPhpDisplayStartupErrorsDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointCustomErrorHandlerOnShutdownDriver;
use Gzhegow\Lib\Modules\Entrypoint\Driver\EntrypointCustomEnableThrowablesOnShutdownDriver;


class EntrypointModule
{
    const OPT_PHP_ERROR_HANDLER     = 'PHP_ERROR_HANDLER';
    const OPT_PHP_EXCEPTION_HANDLER = 'PHP_EXCEPTION_HANDLER';

    const OPT_CUSTOM_ENABLE_THROWABLES_ON_SHUTDOWN = 'CUSTOM_ENABLE_THROWABLES_ON_SHUTDOWN';
    const OPT_CUSTOM_ERROR_HANDLER_ON_SHUTDOWN     = 'CUSTOM_ERROR_HANDLER_ON_SHUTDOWN';

    const OPT_CUSTOM_DIR_ROOT = 'CUSTOM_DIR_ROOT';

    const OPT_PHP_DISPLAY_ERRORS         = 'PHP_DISPLAY_ERRORS';
    const OPT_PHP_DISPLAY_STARTUP_ERRORS = 'PHP_DISPLAY_STARTUP_ERRORS';
    const OPT_PHP_ERROR_LOG              = 'PHP_ERROR_LOG';
    const OPT_PHP_ERROR_REPORTING        = 'PHP_ERROR_REPORTING';
    const OPT_PHP_LOG_ERRORS             = 'PHP_LOG_ERRORS';

    const OPT_PHP_MEMORY_LIMIT = 'PHP_MEMORY_LIMIT';

    const OPT_PHP_MAX_EXECUTION_TIME = 'PHP_MAX_EXECUTION_TIME';
    const OPT_PHP_MAX_INPUT_TIME     = 'PHP_MAX_INPUT_TIME';

    const OPT_PHP_DATE_TIMEZONE_DEFAULT = 'PHP_DATE_TIMEZONE_DEFAULT';

    const OPT_PHP_PRECISION = 'PHP_PRECISION';

    const OPT_PHP_UMASK = 'PHP_UMASK';

    const OPT_PHP_POST_MAX_SIZE = 'PHP_POST_MAX_SIZE';

    const OPT_PHP_SESSION_COOKIE_PARAMS = 'PHP_SESSION_COOKIE_PARAMS';
    const OPT_PHP_SESSION_SAVE_PATH     = 'PHP_SESSION_SAVE_PATH';

    const OPT_PHP_UPLOAD_MAX_FILESIZE = 'PHP_UPLOAD_MAX_FILESIZE';
    const OPT_PHP_UPLOAD_TMP_DIR      = 'PHP_UPLOAD_TMP_DIR';

    const OPT_CUSTOM_SHOULD_TRACE = 'CUSTOM_SHOULD_TRACE';

    const LIST_OPT = [
        self::OPT_PHP_ERROR_HANDLER                    => true,
        self::OPT_PHP_EXCEPTION_HANDLER                => true,
        //
        self::OPT_CUSTOM_ENABLE_THROWABLES_ON_SHUTDOWN => true,
        self::OPT_CUSTOM_ERROR_HANDLER_ON_SHUTDOWN     => true,
        //
        self::OPT_CUSTOM_DIR_ROOT                      => true,
        //
        self::OPT_PHP_DISPLAY_ERRORS                   => true,
        self::OPT_PHP_DISPLAY_STARTUP_ERRORS           => true,
        self::OPT_PHP_ERROR_LOG                        => true,
        self::OPT_PHP_ERROR_REPORTING                  => true,
        self::OPT_PHP_LOG_ERRORS                       => true,
        //
        self::OPT_PHP_MEMORY_LIMIT                     => true,
        //
        self::OPT_PHP_MAX_EXECUTION_TIME               => true,
        self::OPT_PHP_MAX_INPUT_TIME                   => true,
        //
        self::OPT_PHP_DATE_TIMEZONE_DEFAULT            => true,
        //
        self::OPT_PHP_PRECISION                        => true,
        //
        self::OPT_PHP_UMASK                            => true,
        //
        self::OPT_PHP_POST_MAX_SIZE                    => true,
        //
        self::OPT_PHP_SESSION_COOKIE_PARAMS            => true,
        self::OPT_PHP_SESSION_SAVE_PATH                => true,
        //
        self::OPT_PHP_UPLOAD_MAX_FILESIZE              => true,
        self::OPT_PHP_UPLOAD_TMP_DIR                   => true,
        //
        self::OPT_CUSTOM_SHOULD_TRACE                  => true,
    ];


    /**
     * @var EntrypointDriverInterface[]
     */
    protected $drivers = [];

    /**
     * @var array<string, mixed>
     */
    protected $configInitial = [];
    /**
     * @var array<string, mixed>
     */
    protected $configCurrent = [];

    /**
     * @var array
     */
    protected $throwablesOnShutdown = [];

    /**
     * @var bool
     */
    protected $functionsOnShutdownEnabled = false;
    /**
     * @var array<callable>
     */
    protected $functionsOnShutdown = [];


    public function __construct()
    {
        $this->drivers = [];
        //
        $this->drivers[self::OPT_PHP_ERROR_HANDLER] = new EntrypointPhpErrorHandlerDriver();
        $this->drivers[self::OPT_PHP_EXCEPTION_HANDLER] = new EntrypointPhpExceptionHandlerDriver();
        //
        $this->drivers[self::OPT_CUSTOM_ENABLE_THROWABLES_ON_SHUTDOWN] = new EntrypointCustomEnableThrowablesOnShutdownDriver();
        $this->drivers[self::OPT_CUSTOM_ERROR_HANDLER_ON_SHUTDOWN] = new EntrypointCustomErrorHandlerOnShutdownDriver();
        //
        $this->drivers[self::OPT_CUSTOM_DIR_ROOT] = new EntrypointCustomDirRootDriver();
        //
        $this->drivers[self::OPT_PHP_DISPLAY_ERRORS] = new EntrypointPhpDisplayErrorsDriver();
        $this->drivers[self::OPT_PHP_DISPLAY_STARTUP_ERRORS] = new EntrypointPhpDisplayStartupErrorsDriver();
        $this->drivers[self::OPT_PHP_ERROR_LOG] = new EntrypointPhpErrorLogDriver();
        $this->drivers[self::OPT_PHP_ERROR_REPORTING] = new EntrypointPhpErrorReportingDriver();
        $this->drivers[self::OPT_PHP_LOG_ERRORS] = new EntrypointPhpLogErrorsDriver();
        //
        $this->drivers[self::OPT_PHP_MEMORY_LIMIT] = new EntrypointPhpMemoryLimitDriver();
        //
        $this->drivers[self::OPT_PHP_MAX_EXECUTION_TIME] = new EntrypointPhpMaxExecutionTimeDriver();
        $this->drivers[self::OPT_PHP_MAX_INPUT_TIME] = new EntrypointPhpMaxInputTimeDriver();
        //
        $this->drivers[self::OPT_PHP_DATE_TIMEZONE_DEFAULT] = new EntrypointPhpDateTimezoneDefaultDriver();
        //
        $this->drivers[self::OPT_PHP_PRECISION] = new EntrypointPhpPrecisionDriver();
        //
        $this->drivers[self::OPT_PHP_UMASK] = new EntrypointPhpUmaskDriver();
        //
        $this->drivers[self::OPT_PHP_POST_MAX_SIZE] = new EntrypointPhpPostMaxSizeDriver();
        //
        $this->drivers[self::OPT_PHP_SESSION_COOKIE_PARAMS] = new EntrypointPhpSessionCookieParamsDriver();
        $this->drivers[self::OPT_PHP_SESSION_SAVE_PATH] = new EntrypointPhpSessionSavePathDriver();
        //
        $this->drivers[self::OPT_PHP_UPLOAD_MAX_FILESIZE] = new EntrypointPhpUploadMaxFilesizeDriver();
        $this->drivers[self::OPT_PHP_UPLOAD_TMP_DIR] = new EntrypointPhpUploadTmpDirDriver();
        //
        $this->drivers[self::OPT_CUSTOM_SHOULD_TRACE] = new EntrypointCustomShouldTraceDriver();

        foreach ( $this->drivers as $opt => $driver ) {
            $valueInitial = $driver->getInitial();

            $this->configInitial[$opt] = $valueInitial;
            $this->configCurrent[$opt] = $valueInitial;
        }
    }

    public function __initialize()
    {
        return $this;
    }


    public function setAllInitial()
    {
        foreach ( $this->drivers as $opt => $driver ) {
            $valueInitial = $this->configInitial[$opt];

            $driver->setValue($valueInitial, $this->configCurrent);
        }

        return $this;
    }

    public function useAllInitial()
    {
        foreach ( $this->drivers as $opt => $driver ) {
            $valueInitial = $this->configInitial[$opt];

            $driver->useValue($valueInitial, $this->configCurrent);
        }

        return $this;
    }

    public function useInitial(string $opt)
    {
        $valueInitial = $this->configInitial[$opt];

        $driver = $this->drivers[$opt];
        $driver->useValue($valueInitial, $this->configCurrent);

        return $this;
    }

    public function getInitial(string $opt)
    {
        $theType = Lib::type();

        $value = $theType->key_exists($opt, $this->configInitial)->orThrow();

        return $value;
    }


    public function setAllRecommended()
    {
        foreach ( $this->drivers as $driver ) {
            $valueRecommended = $driver->getRecommended();

            $driver->setValue($valueRecommended, $this->configCurrent);
        }

        return $this;
    }

    public function useAllRecommended()
    {
        foreach ( $this->drivers as $driver ) {
            $valueRecommended = $driver->getRecommended();

            $driver->useValue($valueRecommended, $this->configCurrent);
        }

        return $this;
    }

    public function useRecommended(string $opt)
    {
        $driver = $this->drivers[$opt];

        $valueRecommended = $driver->getRecommended();

        $driver->useValue($valueRecommended, $this->configCurrent);

        return $this;
    }

    public function getRecommended(string $opt)
    {
        $driver = $this->drivers[$opt];

        $valueRecommended = $driver->getRecommended();

        return $valueRecommended;
    }


    public function useAll()
    {
        foreach ( $this->drivers as $opt => $driver ) {
            $valueCurrent = $this->configCurrent[$opt];

            $driver->useValue($valueCurrent, $this->configCurrent);
        }

        return $this;
    }

    public function use(string $opt)
    {
        $valueCurrent = $this->configCurrent[$opt];

        $driver = $this->drivers[$opt];
        $driver->useValue($valueCurrent, $this->configCurrent);

        return $this;
    }


    public function getOpt(string $opt)
    {
        $theType = Lib::type();

        $value = $theType->key_exists($opt, $this->configCurrent)->orThrow();

        return $value;
    }

    public function setOpt(string $opt, $value)
    {
        $valueNew = $value;

        $driver = $this->drivers[$opt];
        $driver->setValue($valueNew, $this->configCurrent);

        return $this;
    }

    public function unsetOpt(string $opt)
    {
        $valueInitial = $this->configInitial[$opt];

        $driver = $this->drivers[$opt];
        $driver->setValue($valueInitial, $this->configCurrent);

        return $this;
    }


    public function getThrowablesOnShutdown(&$ref)
    {
        return $ref = $this->throwablesOnShutdown;
    }


    public function registerShutdownFunction($callable)
    {
        if ( ! in_array($callable, $this->functionsOnShutdown, true) ) {
            $theType = Lib::type();
            $theType->callable($callable, null)->orThrow();

            $this->functionsOnShutdown[] = $callable;
        }

        if ( ! $this->functionsOnShutdownEnabled ) {
            register_shutdown_function([ $this, 'onShutdown' ]);

            $this->functionsOnShutdownEnabled = true;
        }

        return $this;
    }

    public function unregisterShutdownFunction($callable)
    {
        $key = array_search($callable, $this->functionsOnShutdown, true);

        if ( false !== $key ) {
            unset($this->functionsOnShutdown[$key]);
        }

        return $this;
    }

    public function onShutdown() : void
    {
        if ( count($this->functionsOnShutdown) > 0 ) {
            $args = func_get_args();

            foreach ( $this->functionsOnShutdown as $fn ) {
                $fn(...$args);
            }
        }
    }
}
