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

    const PRINTER_SYMFONY         = 'symfony';
    const PRINTER_VAR_DUMP        = 'var_dump';
    const PRINTER_VAR_DUMP_NATIVE = 'var_dump_native';
    const PRINTER_VAR_EXPORT      = 'var_export';
    const PRINTER_EXPORT_NATIVE   = 'var_export_native';
    const PRINTER_PRINT_R         = 'print_r';
    const PRINTER_JSON_ENCODE     = 'json_encode';
    const PRINTER_LIST            = [
        self::PRINTER_SYMFONY         => true,
        self::PRINTER_VAR_DUMP        => true,
        self::PRINTER_VAR_DUMP_NATIVE => true,
        self::PRINTER_VAR_EXPORT      => true,
        self::PRINTER_EXPORT_NATIVE   => true,
        self::PRINTER_PRINT_R         => true,
        self::PRINTER_JSON_ENCODE     => true,
    ];

    const DUMPER_ECHO              = 'echo';
    const DUMPER_ECHO_TEXT         = 'echo_text';
    const DUMPER_ECHO_HTML         = 'echo_html';
    const DUMPER_ECHO_DEVTOOLS     = 'echo_devtools';
    const DUMPER_RESOURCE          = 'resource';
    const DUMPER_RESOURCE_TEXT     = 'resource_text';
    const DUMPER_RESOURCE_HTML     = 'resource_html';
    const DUMPER_RESOURCE_DEVTOOLS = 'resource_devtools';
    const DUMPER_PDO               = 'pdo';
    const DUMPER_LIST              = [
        self::DUMPER_ECHO              => true,
        self::DUMPER_ECHO_TEXT         => true,
        self::DUMPER_ECHO_HTML         => true,
        self::DUMPER_ECHO_DEVTOOLS     => true,
        self::DUMPER_RESOURCE          => true,
        self::DUMPER_RESOURCE_TEXT     => true,
        self::DUMPER_RESOURCE_HTML     => true,
        self::DUMPER_RESOURCE_DEVTOOLS => true,
        self::DUMPER_PDO               => true,
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
    protected $dumper = 'resource';
    /**
     * @var string
     */
    protected $dumperDefault = 'resource';
    /**
     * @var array
     */
    protected $dumperOptions = [];


    public function __construct()
    {
        $thePhp = Lib::php();

        $dumperDefault = $thePhp->is_terminal()
            ? static::DUMPER_RESOURCE
            : static::DUMPER_RESOURCE_HTML;

        $this->dumper = $this->dumperDefault = $dumperDefault;
    }


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

            case static::PRINTER_EXPORT_NATIVE:
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

        $casters = $printerOptions[ 'casters' ] ?? null;

        $cloner = (null === $casters)
            ? $this->getSymfonyCloner()
            : $this->newSymfonyCloner($casters);

        $dumper = $thePhp->is_terminal()
            ? $this->getSymfonyCliDumper()
            : $this->getSymfonyHtmlDumper();

        $content = '';

        foreach ( $vars as $arg ) {
            $clonedVar = $cloner->cloneVar($arg);

            $content .= $dumper->dump($clonedVar);
        }

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
            case static::DUMPER_RESOURCE_DEVTOOLS:
                $this->dumperEcho_echo_devtools(...$vars);
                break;

            case static::DUMPER_ECHO:
                $this->dumperEcho_echo(...$vars);
                break;

            case static::DUMPER_ECHO_TEXT:
                $this->dumperEcho_echo_text(...$vars);
                break;

            case static::DUMPER_ECHO_HTML:
                $this->dumperEcho_echo_html(...$vars);
                break;

            case static::DUMPER_PDO:
                $this->dumperEcho_pdo(...$vars);
                break;

            case static::DUMPER_RESOURCE:
                $this->dumperEcho_resource(...$vars);
                break;

            case static::DUMPER_RESOURCE_TEXT:
                $this->dumperEcho_resource_text(...$vars);
                break;

            case static::DUMPER_RESOURCE_HTML:
                $this->dumperEcho_resource_html(...$vars);
                break;

            default:
                throw new RuntimeException(
                    [ 'Unknown `dumper`', $this->dumper ]
                );

        endswitch;
    }

    public function dumperEcho_echo(...$vars) : void
    {
        $content = $this->printerPrint(...$vars);
        $content .= "\n";

        echo $content;
    }

    public function dumperEcho_echo_text(...$vars) : void
    {
        $content = $this->printerPrint(...$vars);
        $content .= "\n";

        $this->sendDebugContentTypeOnShutdown('text/plain');

        echo $content;
    }

    public function dumperEcho_echo_html(...$vars) : void
    {
        $content = $this->printerPrint(...$vars);
        $content .= "\n";

        $htmlContent = nl2br($content);

        $this->sendDebugContentTypeOnShutdown('text/html');

        echo $htmlContent;
    }

    public function dumperEcho_echo_devtools(...$vars) : void
    {
        $content = $this->printerPrint(...$vars);

        $b64content = base64_encode($content);

        $htmlContent = "<script>console.log(window.atob('{$b64content}'));</script>" . "\n";

        $this->sendDebugContentTypeOnShutdown('text/html');

        echo $htmlContent;
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
        if ('' === $tableString) {
            throw new LogicException(
                [ 'The `options.table` should be a non-empty string', $dumperOptions ]
            );
        }

        $columnString = (string) $column;
        if ('' === $columnString) {
            throw new LogicException(
                [ 'The `options.column` should be a non-empty string', $dumperOptions ]
            );
        }

        $content = $this->printerPrint(...$vars);

        $sql = "INSERT INTO {$tableString} ({$columnString}) VALUES (?);";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([ $content ]);
    }

    public function dumperEcho_resource(...$vars) : void
    {
        $thePhp = Lib::php();

        $dumperOptions = $this->dumperOptions[ $this->dumper ] ?? [];

        $resource = $dumperOptions[ 'resource' ] ?? $thePhp->output();

        $content = $this->printerPrint(...$vars);
        $content .= "\n";

        fwrite($resource, $content);
        fflush($resource);
    }

    public function dumperEcho_resource_text(...$vars) : void
    {
        $thePhp = Lib::php();

        $dumperOptions = $this->dumperOptions[ $this->dumper ] ?? [];

        $resource = $dumperOptions[ 'resource' ] ?? $thePhp->output();

        $content = $this->printerPrint(...$vars);
        $content .= "\n";

        $this->sendDebugContentTypeOnShutdown('text/plain');

        fwrite($resource, $content);
        fflush($resource);
    }

    public function dumperEcho_resource_html(...$vars) : void
    {
        $thePhp = Lib::php();

        $dumperOptions = $this->dumperOptions[ $this->dumper ] ?? [];

        $resource = $dumperOptions[ 'resource' ] ?? $thePhp->output();

        $content = $this->printerPrint(...$vars);
        $content .= "\n";

        $htmlContent = nl2br($content);

        $this->sendDebugContentTypeOnShutdown('text/html');

        fwrite($resource, $htmlContent);
        fflush($resource);
    }

    public function dumperEcho_resource_devtools(...$vars) : void
    {
        $thePhp = Lib::php();

        $dumperOptions = $this->dumperOptions[ $this->dumper ] ?? [];

        $resource = $dumperOptions[ 'resource' ] ?? $thePhp->output();

        $content = $this->printerPrint(...$vars);

        $b64content = base64_encode($content);

        $htmlContent = "<script>console.log(window.atob('{$b64content}'));</script>" . "\n";

        $this->sendDebugContentTypeOnShutdown('text/html');

        fwrite($resource, $htmlContent);
        fflush($resource);
    }


    public function dp(?array $trace, $var, ...$vars) : string
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return $this->doPrinterPrintTrace($trace, $var, ...$vars);
    }


    public function d(?array $trace, $var, ...$vars)
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->doDumperEchoTrace($trace, $var, ...$vars);

        return $var;
    }

    public function dd(?array $trace, ...$vars)
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->doDumperEchoTrace($trace, ...$vars);

        die();
    }

    public function ddd(?array $trace, ?int $limit, $var, ...$vars)
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        static $current;

        $limit = $limit ?? 1;
        if ($limit < 1) $limit = 1;

        $current = $current ?? $limit;

        $this->doDumperEchoTrace($trace, $var, ...$vars);

        if (0 === --$current) {
            die();
        }

        return $var;
    }


    protected function doPrinterPrintTrace(array $trace, ...$vars) : string
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? $trace[ 0 ][ 0 ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? $trace[ 0 ][ 1 ] ?? -1;

        $traceWhereIs = "{$traceFile}: {$traceLine}";

        $content = $this->printerPrint($traceWhereIs, ...$vars);

        return $content;
    }

    protected function doDumperEchoTrace(array $trace, ...$vars) : void
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? $trace[ 0 ][ 0 ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? $trace[ 0 ][ 1 ] ?? -1;

        $traceWhereIs = "{$traceFile}: {$traceLine}";

        $this->dumperEcho($traceWhereIs, ...$vars);
    }


    protected function sendDebugContentTypeOnShutdown(string $contentType) : void
    {
        $debugContentType = $this->staticDebugContentType();

        if ([] !== $debugContentType) {
            return;
        }

        $theEntrypoint = Lib::entrypoint();
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

        $headerSentContentType = null;
        if (headers_sent($file, $line)) {
            foreach ( headers_list() as $header ) {
                if (0 === stripos($header, 'Content-Type:')) {
                    $headerSentContentType = substr($header, strlen('Content-Type:'));
                    $headerSentContentType = explode(';', $headerSentContentType)[ 0 ];
                    $headerSentContentType = strtolower(trim($headerSentContentType));
                }
            }
        }

        if (null === $headerSentContentType) {
            $this->staticDebugContentType([ $contentTypeValid ]);

        } elseif ($contentTypeValid === $headerSentContentType) {
            $this->staticDebugContentType([ $contentTypeValid ]);

        } else {
            throw new RuntimeException(
                [ 'Headers already sent', $file, $line ]
            );
        }

        $theEntrypoint->registerShutdownFunction(
            [ static::class, 'onShutdownEnsureDebugContentType' ]
        );
    }

    public static function onShutdownEnsureDebugContentType() : void
    {
        $debugContentType = static::staticDebugContentType();

        if ([] !== $debugContentType) {
            $debugContentTypeCurrentValue = $debugContentType[ 0 ];

            if (false !== $debugContentTypeCurrentValue) {
                header("Content-Type: {$debugContentTypeCurrentValue}", true, 418);
            }
        }
    }
}
