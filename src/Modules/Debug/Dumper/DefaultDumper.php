<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Debug\Dumper;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\ComposerException;


class DefaultDumper implements DumperInterface
{
    const SYMFONY_VAR_DUMPER       = '\Symfony\Component\VarDumper\VarDumper';
    const SYMFONY_CLONER_INTERFACE = '\Symfony\Component\VarDumper\Cloner\ClonerInterface';
    const SYMFONY_VAR_CLONER       = '\Symfony\Component\VarDumper\Cloner\VarCloner';
    const SYMFONY_CLI_DUMPER       = '\Symfony\Component\VarDumper\Dumper\CliDumper';
    const SYMFONY_HTML_DUMPER      = '\Symfony\Component\VarDumper\Dumper\HtmlDumper';

    const PRINTER_JSON_ENCODE       = 'json_encode';
    const PRINTER_PRINT_R           = 'print_r';
    const PRINTER_SYMFONY           = 'symfony';
    const PRINTER_VAR_DUMP          = 'var_dump';
    const PRINTER_VAR_DUMP_NATIVE   = 'var_dump_native';
    const PRINTER_VAR_EXPORT        = 'var_export';
    const PRINTER_VAR_EXPORT_NATIVE = 'var_export_native';
    const PRINTER_LIST              = [
        self::PRINTER_JSON_ENCODE       => true,
        self::PRINTER_PRINT_R           => true,
        self::PRINTER_SYMFONY           => true,
        self::PRINTER_VAR_DUMP          => true,
        self::PRINTER_VAR_DUMP_NATIVE   => true,
        self::PRINTER_VAR_EXPORT        => true,
        self::PRINTER_VAR_EXPORT_NATIVE => true,
    ];

    const DUMPER_ECHO              = 'echo';
    const DUMPER_ECHO_DEVTOOLS     = 'echo_devtools';
    const DUMPER_ECHO_HTML         = 'echo_html';
    const DUMPER_ECHO_TEXT         = 'echo_text';
    const DUMPER_PDO               = 'pdo';
    const DUMPER_RESOURCE          = 'resource';
    const DUMPER_RESOURCE_DEVTOOLS = 'resource_devtools';
    const DUMPER_RESOURCE_HTML     = 'resource_html';
    const DUMPER_RESOURCE_TEXT     = 'resource_text';
    const DUMPER_LIST              = [
        self::DUMPER_ECHO              => true,
        self::DUMPER_ECHO_DEVTOOLS     => true,
        self::DUMPER_ECHO_HTML         => true,
        self::DUMPER_ECHO_TEXT         => true,
        self::DUMPER_PDO               => true,
        self::DUMPER_RESOURCE          => true,
        self::DUMPER_RESOURCE_DEVTOOLS => true,
        self::DUMPER_RESOURCE_HTML     => true,
        self::DUMPER_RESOURCE_TEXT     => true,
    ];


    /**
     * @var array{ 0?: string|false }
     */
    protected static $debugContentType = [];

    /**
     * @param array{ 0?: string|false }|null $contentType
     *
     * @return array{ 0?: string|false }
     */
    public static function staticDebugContentType(?array $contentType = null) : array
    {
        $last = static::$debugContentType;

        if (null !== $contentType) {
            $contentTypeValue = $contentType[ 0 ];

            if (false === $contentTypeValue) {
                static::$debugContentType = [ false ];

            } elseif (is_string($contentTypeValue) && ('' !== $contentTypeValue)) {
                static::$debugContentType = [ $contentTypeValue ];

            } else {
                throw new LogicException(
                    [ 'The `contentType` should be non-empty string or be FALSE', $contentType ]
                );
            }
        }

        static::$debugContentType = static::$debugContentType ?? [];

        return $last;
    }


    /**
     * @var \Symfony\Component\VarDumper\Cloner\ClonerInterface
     */
    protected $symfonyCloner;
    /**
     * @var \Symfony\Component\VarDumper\Dumper\CliDumper
     */
    protected $symfonyCliDumper;
    /**
     * @var \Symfony\Component\VarDumper\Dumper\HtmlDumper
     */
    protected $symfonyHtmlDumper;

    /**
     * @var resource
     */
    protected $symfonyOutputLineDumpResource;

    /**
     * @var string
     */
    protected $printer = 'var_dump';
    /**
     * @var string
     */
    protected $printerDefault = 'var_dump';
    /**
     * @var array
     */
    protected $printerOptions = [];

    /**
     * @var string
     */
    protected $dumper = 'echo';
    /**
     * @var string
     */
    protected $dumperDefault = 'echo';
    /**
     * @var array
     */
    protected $dumperOptions = [];


    public function hasSymfonyVarDumper() : bool
    {
        return class_exists(static::SYMFONY_VAR_DUMPER);
    }


    /**
     * @param null|object|\Symfony\Component\VarDumper\Cloner\ClonerInterface $symfonyCloner
     *
     * @return object
     */
    public function withSymfonyCloner(?object $symfonyCloner) : object
    {
        if (null !== $symfonyCloner) {
            if (! is_a($symfonyCloner, $interface = static::SYMFONY_CLONER_INTERFACE)) {
                throw new RuntimeException(
                    [
                        'The `symfonyCloner` should be an instance of: ' . $interface,
                        $symfonyCloner,
                    ]
                );
            }
        }

        $this->symfonyCloner = $symfonyCloner;

        return $this;
    }

    /**
     * @return \Symfony\Component\VarDumper\Cloner\ClonerInterface
     */
    public function newSymfonyCloner(...$args) : object
    {
        $commands = [
            'composer require symfony/var-dumper',
        ];

        if (! class_exists($symfonyClonerClass = static::SYMFONY_VAR_CLONER)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return new $symfonyClonerClass(...$args);
    }

    /**
     * @return \Symfony\Component\VarDumper\Cloner\ClonerInterface
     */
    public function getSymfonyCloner() : object
    {
        return $this->symfonyCloner = null
            ?? $this->symfonyCloner
            ?? $this->newSymfonyCloner();
    }


    /**
     * @param null|object|\Symfony\Component\VarDumper\Dumper\CliDumper $symfonyCliDumper
     *
     * @return object
     */
    public function withSymfonyCliDumper(?object $symfonyCliDumper) : object
    {
        if (null !== $symfonyCliDumper) {
            if (! is_a($symfonyCliDumper, $interface = static::SYMFONY_CLI_DUMPER)) {
                throw new RuntimeException(
                    [
                        'The `symfonyCliDumper` should be an instance of: ' . $interface,
                        $symfonyCliDumper,
                    ]
                );
            }
        }

        $this->symfonyCliDumper = $symfonyCliDumper;

        return $this;
    }

    /**
     * @return \Symfony\Component\VarDumper\Dumper\CliDumper
     */
    public function newSymfonyCliDumper() : object
    {
        $commands = [
            'composer require symfony/var-dumper',
        ];

        if (! class_exists($symfonyCliDumperClass = static::SYMFONY_CLI_DUMPER)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return new $symfonyCliDumperClass();
    }

    /**
     * @return \Symfony\Component\VarDumper\Dumper\CliDumper
     */
    public function getSymfonyCliDumper() : object
    {
        return $this->symfonyCliDumper = null
            ?? $this->symfonyCliDumper
            ?? $this->newSymfonyCliDumper();
    }


    /**
     * @param null|object|\Symfony\Component\VarDumper\Dumper\HtmlDumper $symfonyHtmlDumper
     *
     * @return object
     */
    public function withSymfonyHtmlDumper(?object $symfonyHtmlDumper) : object
    {
        if (null !== $symfonyHtmlDumper) {
            if (! is_a($symfonyHtmlDumper, $interface = static::SYMFONY_HTML_DUMPER)) {
                throw new RuntimeException(
                    [
                        'The `symfonyHtmlDumper` should be an instance of: ' . $interface,
                        $symfonyHtmlDumper,
                    ]
                );
            }
        }

        $this->symfonyCliDumper = $symfonyHtmlDumper;

        return $this;
    }

    /**
     * @return \Symfony\Component\VarDumper\Dumper\HtmlDumper
     */
    public function newSymfonyHtmlDumper() : object
    {
        $commands = [
            'composer require symfony/var-dumper',
        ];

        if (! class_exists($symfonyHtmlDumperClass = static::SYMFONY_HTML_DUMPER)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return new $symfonyHtmlDumperClass();
    }

    /**
     * @return \Symfony\Component\VarDumper\Dumper\HtmlDumper
     */
    public function getSymfonyHtmlDumper() : object
    {
        return $this->symfonyHtmlDumper = null
            ?? $this->symfonyHtmlDumper
            ?? $this->newSymfonyHtmlDumper();
    }


    /**
     * @return static
     */
    public function selectPrinter(?string $printer, ?array $printerOptions = null)
    {
        if (null !== $printer) {
            if (! isset(static::PRINTER_LIST[ $printer ])) {
                throw new LogicException(
                    [
                        ''
                        . 'The `printer` should be one of: '
                        . '[ ' . implode(' ][ ', array_keys(static::PRINTER_LIST)) . ' ]',
                        //
                        $printer,
                    ]
                );
            }
        }

        $this->printer = $printer ?? $this->printerDefault;

        if (null !== $printerOptions) {
            $this->printerOptions[ $this->printer ] = $printerOptions;
        }

        return $this;
    }

    public function printerPrint(...$vars) : string
    {
        switch ( $this->printer ):
            case static::PRINTER_JSON_ENCODE:
                $content = $this->printerPrint_json_encode(...$vars);
                break;

            case static::PRINTER_PRINT_R:
                $content = $this->printerPrint_print_r(...$vars);
                break;

            case static::PRINTER_SYMFONY:
                $content = $this->printerPrint_symfony(...$vars);

                break;

            case static::PRINTER_VAR_DUMP:
                $content = $this->printerPrint_var_dump(...$vars);
                break;

            case static::PRINTER_VAR_DUMP_NATIVE:
                $content = $this->printerPrint_var_dump_native(...$vars);
                break;

            case static::PRINTER_VAR_EXPORT:
                $content = $this->printerPrint_var_export(...$vars);
                break;

            case static::PRINTER_VAR_EXPORT_NATIVE:
                $content = $this->printerPrint_var_export_native(...$vars);
                break;

            default:
                throw new RuntimeException(
                    [ 'Unknown `printer`', $this->printer ]
                );

        endswitch;

        return $content;
    }

    public function printerPrint_json_encode(...$vars) : string
    {
        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= "\n";
            }

            $content .= json_encode($arg,
                0
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_LINE_TERMINATORS
            );
        }

        return $content;
    }

    public function printerPrint_print_r(...$vars) : string
    {
        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= "\n";
            }

            $content .= print_r($arg, true);
        }

        return $content;
    }

    public function printerPrint_symfony(...$vars) : string
    {
        $thePhp = Lib::php();

        $printerOptions = $this->printerOptions[ $this->printer ] ?? [];

        $clonerCasters = $printerOptions[ 'casters' ] ?? null;
        $cloner = (null === $clonerCasters)
            ? $this->getSymfonyCloner()
            : $this->newSymfonyCloner($clonerCasters);

        $isTerminal = $thePhp->is_terminal();
        $dumper = $isTerminal
            ? $this->getSymfonyCliDumper()
            : $this->getSymfonyHtmlDumper();

        $dumper->setColors(true);
        $dumper->setOutput([ $this, 'doSymfonyPrinterOutputDumpLine' ]);

        $content = [];
        foreach ( $vars as $var ) {
            $h = $this->symfonyOutputLineDumpResource = fopen('php://memory', 'wb');

            $dumper->dump($cloner->cloneVar($var));

            rewind($h);

            $contentVar = stream_get_contents($h);

            fclose($h);

            $contentVar = trim($contentVar);

            $content[] = $contentVar;

            $this->symfonyOutputLineDumpResource = null;
        }

        $content = implode("\n", $content);

        return $content;
    }

    public function printerPrint_var_dump(...$vars) : string
    {
        $theDebug = Lib::debug();

        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= "\n";
            }

            $content .= $theDebug->var_dump($arg);
        }

        return $content;
    }

    public function printerPrint_var_dump_native(...$vars) : string
    {
        ob_start();
        var_dump(...$vars);
        $content = ob_get_clean();

        return $content;
    }

    public function printerPrint_var_export(...$vars) : string
    {
        $theDebug = Lib::debug();

        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= "\n";
            }

            $content .= $theDebug->var_export($arg);
        }

        return $content;
    }

    public function printerPrint_var_export_native(...$vars) : string
    {
        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= "\n";
            }

            $content .= var_export($arg, true);
        }

        return $content;
    }


    /**
     * @return static
     */
    public function selectDumper(?string $dumper, ?array $dumperOptions = null)
    {
        if (null !== $dumper) {
            if (! isset(static::DUMPER_LIST[ $dumper ])) {
                throw new LogicException(
                    [
                        ''
                        . 'The `dumper` should be one of: '
                        . '[ ' . implode(' ][ ', array_keys(static::DUMPER_LIST)) . ' ]',
                        //
                        $dumper,
                    ]
                );
            }
        }

        $this->dumper = $dumper ?? $this->dumperDefault;

        if (null !== $dumperOptions) {
            $this->dumperOptions[ $this->dumper ] = $dumperOptions;
        }

        return $this;
    }

    public function dumperEcho(...$vars) : void
    {
        switch ( $this->dumper ):
            case static::DUMPER_ECHO:
                $this->dumperEcho_echo(...$vars);
                break;

            case static::DUMPER_ECHO_DEVTOOLS:
                $this->dumperEcho_echo_devtools(...$vars);
                break;

            case static::DUMPER_ECHO_HTML:
                $this->dumperEcho_echo_html(...$vars);
                break;

            case static::DUMPER_ECHO_TEXT:
                $this->dumperEcho_echo_text(...$vars);
                break;

            case static::DUMPER_PDO:
                $this->dumperEcho_pdo(...$vars);
                break;

            case static::DUMPER_RESOURCE:
                $this->dumperEcho_resource(...$vars);
                break;

            case static::DUMPER_RESOURCE_DEVTOOLS:
                $this->dumperEcho_resource_devtools(...$vars);
                break;

            case static::DUMPER_RESOURCE_HTML:
                $this->dumperEcho_resource_html(...$vars);
                break;

            case static::DUMPER_RESOURCE_TEXT:
                $this->dumperEcho_resource_text(...$vars);
                break;

            default:
                throw new RuntimeException(
                    [ 'Unknown `dumper`', $this->dumper ]
                );

        endswitch;
    }

    public function dumperEcho_echo(...$vars) : void
    {
        $thePhp = Lib::php();

        $thePhp->is_terminal()
            ? $this->dumperEcho_echo_text(...$vars)
            : $this->dumperEcho_echo_html(...$vars);
    }

    public function dumperEcho_echo_devtools(...$vars) : void
    {
        $content = $this->printerPrint(...$vars);
        $content = rtrim($content);

        $content = base64_encode($content);

        $content = "<script>console.log(window.atob('{$content}'));</script>" . "\n";

        $this->sendDebugContentTypeOnShutdown('text/html');

        echo $content;
        flush();
    }

    public function dumperEcho_echo_html(...$vars) : void
    {
        $content = $this->printerPrint(...$vars);
        $content = rtrim($content) . "\n";

        $this->sendDebugContentTypeOnShutdown('text/html');

        echo $content;
        flush();
    }

    public function dumperEcho_echo_text(...$vars) : void
    {
        $content = $this->printerPrint(...$vars);
        $content = rtrim($content) . "\n";

        $this->sendDebugContentTypeOnShutdown('text/plain');

        echo $content;
        flush();
    }

    public function dumperEcho_pdo(...$vars) : void
    {
        $dumperOptions = $this->dumperOptions[ $this->dumper ] ?? [];

        $pdo = $dumperOptions[ 'pdo' ] ?? $dumperOptions[ 0 ];
        $table = $dumperOptions[ 'table' ] ?? $dumperOptions[ 1 ];
        $column = $dumperOptions[ 'column' ] ?? $dumperOptions[ 2 ];

        if (! ($pdo instanceof \PDO)) {
            throw new LogicException(
                [ 'The `options.pdo` should be an instance of: ' . \PDO::class, $dumperOptions ]
            );
        }

        $tableString = (string) $table;
        $columnString = (string) $column;

        if ('' === $tableString) {
            throw new LogicException(
                [ 'The `options.table` should be a non-empty string', $dumperOptions ]
            );
        }

        if ('' === $columnString) {
            throw new LogicException(
                [ 'The `options.column` should be a non-empty string', $dumperOptions ]
            );
        }

        $content = $this->printerPrint(...$vars);
        $content = rtrim($content);

        $sql = "INSERT INTO {$tableString} ({$columnString}) VALUES (?);";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([ $content ]);
    }

    public function dumperEcho_resource(...$vars) : void
    {
        $thePhp = Lib::php();

        $thePhp->is_terminal()
            ? $this->dumperEcho_resource_text(...$vars)
            : $this->dumperEcho_resource_html(...$vars);
    }

    public function dumperEcho_resource_devtools(...$vars) : void
    {
        $thePhp = Lib::php();

        $dumperOptions = $this->dumperOptions[ $this->dumper ] ?? [];

        $resource = $dumperOptions[ 'resource' ] ?? $thePhp->hOutput();

        $content = $this->printerPrint(...$vars);
        $content = rtrim($content);

        $b64content = base64_encode($content);

        $htmlContent = "<script>console.log(window.atob('{$b64content}'));</script>" . "\n";

        $this->sendDebugContentTypeOnShutdown('text/html');

        fwrite($resource, $htmlContent);
        fflush($resource);
    }

    public function dumperEcho_resource_html(...$vars) : void
    {
        $thePhp = Lib::php();

        $dumperOptions = $this->dumperOptions[ $this->dumper ] ?? [];

        $resource = $dumperOptions[ 'resource' ] ?? $thePhp->hOutput();

        $content = $this->printerPrint(...$vars);
        $content = rtrim($content) . "\n";

        $this->sendDebugContentTypeOnShutdown('text/html');

        fwrite($resource, $content);
        fflush($resource);
    }

    public function dumperEcho_resource_text(...$vars) : void
    {
        $thePhp = Lib::php();

        $dumperOptions = $this->dumperOptions[ $this->dumper ] ?? [];

        $resource = $dumperOptions[ 'resource' ] ?? $thePhp->hOutput();

        $content = $this->printerPrint(...$vars);
        $content = rtrim($content) . "\n";

        $this->sendDebugContentTypeOnShutdown('text/plain');

        fwrite($resource, $content);
        fflush($resource);
    }


    public function dp(?array $debugBacktraceOverride, $var, ...$vars) : string
    {
        $debugBacktraceOverride = $debugBacktraceOverride ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return $this->doPrinterPrintTrace($debugBacktraceOverride, $var, ...$vars);
    }

    public function fnDP(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure
    {
        /**
         * @return string
         */
        return function ($var, ...$vars) use ($limit, $debugBacktraceOverride) {
            $t = $this->file_line($limit, $debugBacktraceOverride);

            return $this->dp([ $t ], $var, ...$vars);
        };
    }


    /**
     * @return mixed
     */
    public function d(?array $debugBacktraceOverride, $var, ...$vars)
    {
        $debugBacktraceOverride = $debugBacktraceOverride ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->doDumperEchoTrace($debugBacktraceOverride, $var, ...$vars);

        return $var;
    }

    /**
     * @return mixed|void
     */
    public function dd(?array $debugBacktraceOverride, ...$vars)
    {
        $debugBacktraceOverride = $debugBacktraceOverride ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->doDumperEchoTrace($debugBacktraceOverride, ...$vars);

        die();
    }

    /**
     * @return mixed|void
     */
    public function ddd(?array $debugBacktraceOverride, int $times, $var, ...$vars)
    {
        if ($times < 1) {
            throw new LogicException(
                [ 'The `times` should be positive integer', $times ]
            );
        }

        static $current;

        $current = $current ?? $times;

        $debugBacktraceOverride = $debugBacktraceOverride ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->doDumperEchoTrace($debugBacktraceOverride, $var, ...$vars);

        if (0 === --$current) {
            die();
        }

        return $var;
    }


    public function fnD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure
    {
        /**
         * @return mixed
         */
        return function ($var, ...$vars) use ($limit, $debugBacktraceOverride) {
            $t = $this->file_line($limit, $debugBacktraceOverride);

            return $this->d([ $t ], $var, ...$vars);
        };
    }

    public function fnDD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure
    {
        /**
         * @return mixed|void
         */
        return function (...$vars) use ($limit, $debugBacktraceOverride) {
            $t = $this->file_line($limit, $debugBacktraceOverride);

            return $this->dd([ $t ], ...$vars);
        };
    }

    public function fnDDD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure
    {
        /**
         * @return mixed|void
         */
        return function (?int $times, $var, ...$vars) use ($limit, $debugBacktraceOverride) {
            $t = $this->file_line($limit, $debugBacktraceOverride);

            return $this->ddd([ $t ], $times, $var, ...$vars);
        };
    }


    /**
     * @return mixed|void
     */
    public function td(?array $debugBacktraceOverride, int $throttleMs, $var, ...$vars)
    {
        if ($throttleMs < 0) {
            throw new LogicException(
                [ 'The `throttleMs` should be a non-negative integer', $throttleMs ]
            );
        }

        static $last;

        $last = $last ?? [];

        $debugBacktraceOverride = $debugBacktraceOverride ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        if (! (isset($debugBacktrace[ 0 ]) && is_array($debugBacktrace[ 0 ]))) {
            throw new LogicException(
                [ 'The `debugBacktraceOverride` should be valid result of `debug_backtrace` function', $debugBacktrace ]
            );
        }

        $traceFile = $debugBacktraceOverride[ 0 ][ 'file' ] ?? $debugBacktraceOverride[ 0 ][ 0 ] ?? '{file}';
        $traceLine = $debugBacktraceOverride[ 0 ][ 'line' ] ?? $debugBacktraceOverride[ 0 ][ 1 ] ?? -1;

        $t = [ $traceFile, $traceLine ];

        $key = implode(':', $t);

        $now = microtime(true);

        $last[ $key ] = $last[ $key ] ?? 0;

        if (($now - $last[ $key ]) > ($throttleMs / 1000)) {
            $last[ $key ] = $now;

            $this->doDumperEchoTrace([ $t ], $var, ...$vars);
        }

        return $var;
    }

    public function fnTD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure
    {
        /**
         * @return mixed|void
         */
        return function (int $throttleMs, $var, ...$vars) use ($limit, $debugBacktraceOverride) {
            $t = $this->file_line($limit, $debugBacktraceOverride);

            return $this->td([ $t ], $throttleMs, $var, ...$vars);
        };
    }


    protected function doPrinterPrintTrace(array $debugBacktrace, ...$vars) : string
    {
        if (! (isset($debugBacktrace[ 0 ]) && is_array($debugBacktrace[ 0 ]))) {
            throw new LogicException(
                [ 'The `debugBacktrace` should be valid result of `debug_backtrace` function', $debugBacktrace ]
            );
        }

        $traceFile = $debugBacktrace[ 0 ][ 'file' ] ?? $debugBacktrace[ 0 ][ 0 ] ?? '{file}';
        $traceLine = $debugBacktrace[ 0 ][ 'line' ] ?? $debugBacktrace[ 0 ][ 1 ] ?? -1;

        $traceWhereIs = "[ {$traceFile} ({$traceLine}) ]";

        $content = $this->printerPrint($traceWhereIs, ...$vars);

        return $content;
    }

    protected function doDumperEchoTrace(array $debugBacktrace, ...$vars) : void
    {
        if (! (isset($debugBacktrace[ 0 ]) && is_array($debugBacktrace[ 0 ]))) {
            throw new LogicException(
                [ 'The `debugBacktrace` should be valid result of `debug_backtrace` function', $debugBacktrace ]
            );
        }

        $traceFile = $debugBacktrace[ 0 ][ 'file' ] ?? $debugBacktrace[ 0 ][ 0 ] ?? '{file}';
        $traceLine = $debugBacktrace[ 0 ][ 'line' ] ?? $debugBacktrace[ 0 ][ 1 ] ?? -1;

        $traceWhereIs = "[ {$traceFile} ({$traceLine}) ]";

        $this->dumperEcho($traceWhereIs, ...$vars);
    }


    protected function sendDebugContentTypeOnShutdown(string $contentType) : void
    {
        $debugContentType = $this->staticDebugContentType();

        if ([] !== $debugContentType) {
            return;
        }

        $thePhp = Lib::php();
        $theType = Lib::type();

        $contentTypeValid = $theType->string_not_empty($contentType)->orThrow();
        $contentTypeValid = strtolower($contentTypeValid);

        $isTerminal = $thePhp->is_terminal();

        if ($isTerminal) {
            $this->staticDebugContentType([ false ]);

            return;
        }

        $level = ob_get_level();
        for ( $i = $level; $i > 0; $i-- ) {
            ob_flush();
        }

        if (headers_sent($file, $line)) {
            throw new RuntimeException(
                [ 'Headers already sent', $file, $line ]
            );
        }

        $headerSentContentType = null;
        foreach ( headers_list() as $header ) {
            if (0 === stripos($header, 'Content-Type:')) {
                $headerSentContentType = substr($header, strlen('Content-Type:'));
                $headerSentContentType = explode(';', $headerSentContentType)[ 0 ];
                $headerSentContentType = strtolower(trim($headerSentContentType));
            }
        }

        if (null === $headerSentContentType) {
            $this->staticDebugContentType([ $contentTypeValid ]);

        } elseif ($contentTypeValid === $headerSentContentType) {
            $this->staticDebugContentType([ $contentTypeValid ]);
        }

        $this->registerShutdownFunction();
    }

    public function registerShutdownFunction() : void
    {
        $theEntrypoint = Lib::entrypoint();

        $theEntrypoint->registerShutdownFunction([ $this, 'onShutdownEnsureDebugContentType' ]);
    }

    public function onShutdownEnsureDebugContentType() : void
    {
        $debugContentType = $this->staticDebugContentType();

        if ([] !== $debugContentType) {
            $debugContentTypeCurrentValue = $debugContentType[ 0 ];

            if (false === $debugContentTypeCurrentValue) {
                return;
            }

            if (! headers_sent()) {
                header("Content-Type: {$debugContentTypeCurrentValue}", true, 418);
            }
        }
    }


    /**
     * @return array{ 0: string, 1: string }
     */
    protected function file_line(?int $limit = null, ?array $debugBacktraceOverride = null) : array
    {
        $limit = $limit ?? 1;

        if (null === $debugBacktraceOverride) {
            $limit++;

            $debugBacktraceOverride = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit);
        }

        $i = $limit - 1;

        if (! isset($debugBacktraceOverride[ $i ])) {
            throw new LogicException(
                [ 'The key is not exists in trace: ' . $i, $debugBacktraceOverride ]
            );
        }

        $fileLine = [
            $debugBacktraceOverride[ $i ][ 'file' ] ?? '{{file}}',
            $debugBacktraceOverride[ $i ][ 'line' ] ?? '{{line}}',
        ];

        return $fileLine;
    }


    public function doSymfonyPrinterOutputDumpLine($line, $depth, $indentPad) : void
    {
        if ($depth !== -1) {
            fwrite($this->symfonyOutputLineDumpResource, str_repeat($indentPad, $depth) . $line . "\n");

        } else {
            $h = $this->symfonyOutputLineDumpResource;

            fseek($h, -1, SEEK_CUR);
            fwrite($h, $line);
        }
    }
}
