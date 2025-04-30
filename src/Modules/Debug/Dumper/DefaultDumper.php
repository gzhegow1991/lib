<?php
/**
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
    const SYMFONY_CLONER_INTERFACE = '\Symfony\Component\VarDumper\Cloner\ClonerInterface';
    const SYMFONY_CLONER           = '\Symfony\Component\VarDumper\Cloner\VarCloner';
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

    const DUMPER_ECHO        = 'echo';
    const DUMPER_ECHO_HTML   = 'echo_html';
    const DUMPER_STDOUT      = 'stdout';
    const DUMPER_STDOUT_HTML = 'stdout_html';
    const DUMPER_DEVTOOLS    = 'devtools';
    const DUMPER_PDO         = 'pdo';
    const DUMPER_LIST        = [
        self::DUMPER_ECHO        => true,
        self::DUMPER_ECHO_HTML   => true,
        self::DUMPER_STDOUT      => true,
        self::DUMPER_STDOUT_HTML => true,
        self::DUMPER_DEVTOOLS    => true,
        self::DUMPER_PDO         => true,
    ];


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
    protected $printerDefault;
    /**
     * @var string
     */
    protected $printer;
    /**
     * @var array
     */
    protected $printerOptions = [];

    /**
     * @var string
     */
    protected $dumperDefault;
    /**
     * @var string
     */
    protected $dumper;
    /**
     * @var array
     */
    protected $dumperOptions = [];


    public function __construct()
    {
        $printerDefault = interface_exists(static::SYMFONY_CLONER_INTERFACE)
            ? 'symfony'
            : 'var_dump';

        $dumperDefault = Lib::php()->is_terminal()
            ? 'stdout'
            : 'stdout_html';

        $this->printer = $this->printerDefault = $printerDefault;
        $this->dumper = $this->dumperDefault = $dumperDefault;
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
                        'The `symfonyCloner` should be instance of: ' . $interface,
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
    protected function newSymfonyCloner() : object
    {
        $commands = [
            'composer require symfony/var-dumper',
        ];

        if (! class_exists($symfonyCloner = static::SYMFONY_CLONER)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return new $symfonyCloner();
    }

    /**
     * @return \Symfony\Component\VarDumper\Cloner\ClonerInterface
     */
    protected function getSymfonyCloner() : object
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
                        'The `symfonyCliDumper` should be instance of: ' . $interface,
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
    protected function newSymfonyCliDumper() : object
    {
        $commands = [
            'composer require symfony/var-dumper',
        ];

        if (! class_exists($symfonyCliDumper = static::SYMFONY_CLI_DUMPER)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return new $symfonyCliDumper();
    }

    /**
     * @return \Symfony\Component\VarDumper\Dumper\CliDumper
     */
    protected function getSymfonyCliDumper() : object
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
                        'The `symfonyHtmlDumper` should be instance of: ' . $interface,
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
    protected function newSymfonyHtmlDumper() : object
    {
        $commands = [
            'composer require symfony/var-dumper',
        ];

        if (! class_exists($symfonyHtmlDumper = static::SYMFONY_HTML_DUMPER)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return new $symfonyHtmlDumper();
    }

    /**
     * @return \Symfony\Component\VarDumper\Dumper\HtmlDumper
     */
    protected function getSymfonyHtmlDumper() : object
    {
        return $this->symfonyHtmlDumper = null
            ?? $this->symfonyHtmlDumper
            ?? $this->newSymfonyHtmlDumper();
    }


    /**
     * @return static
     */
    public function printer(?string $printer, ?array $printerOptions = null)
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
            $this->printerOptions = $printerOptions;
        }

        return $this;
    }


    protected function printPrinter(...$vars) : string
    {
        switch ( $this->printer ):
            case static::PRINTER_SYMFONY:
                $content = $this->printPrinter_symfony(...$vars);

                break;

            case static::PRINTER_VAR_DUMP:
                $content = $this->printPrinter_var_dump(...$vars);
                break;

            case static::PRINTER_VAR_DUMP_NATIVE:
                $content = $this->printPrinter_var_dump_native(...$vars);
                break;

            case static::PRINTER_VAR_EXPORT:
                $content = $this->printPrinter_var_export(...$vars);
                break;

            case static::PRINTER_EXPORT_NATIVE:
                $content = $this->printPrint_var_export_native(...$vars);
                break;

            case static::PRINTER_PRINT_R:
                $content = $this->printPrinter_print_r(...$vars);
                break;

            case static::PRINTER_JSON_ENCODE:
                $content = $this->printPrinter_json_encode(...$vars);
                break;

            default:
                throw new RuntimeException(
                    [ 'Unknown `printer`', $this->printer ]
                );

        endswitch;

        return $content;
    }

    public function printPrinter_symfony(...$vars) : string
    {
        $cloner = $this->getSymfonyCloner();

        $dumper = Lib::php()->is_terminal()
            ? $this->getSymfonyCliDumper()
            : $this->getSymfonyHtmlDumper();

        $content = '';

        foreach ( $vars as $arg ) {
            $clonedVar = $cloner->cloneVar($arg);

            $content .= $dumper->dump($clonedVar);
        }

        return $content;
    }

    public function printPrinter_var_dump(...$vars) : string
    {
        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= PHP_EOL;
            }

            $content .= Lib::debug()->var_dump($arg);
        }

        return $content;
    }

    public function printPrinter_var_dump_native(...$vars) : string
    {
        ob_start();
        var_dump(...$vars);
        $content = ob_get_clean();

        return $content;
    }

    public function printPrinter_var_export(...$vars) : string
    {
        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= PHP_EOL;
            }

            $content .= Lib::debug()->var_export($arg);
        }

        return $content;
    }

    public function printPrint_var_export_native(...$vars) : string
    {
        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= PHP_EOL;
            }

            $content .= var_export($arg, true);
        }

        return $content;
    }

    public function printPrinter_print_r(...$vars) : string
    {
        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= PHP_EOL;
            }

            $content .= print_r($arg, true);
        }

        return $content;
    }

    public function printPrinter_json_encode(...$vars) : string
    {
        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= PHP_EOL;
            }

            $content .= json_encode($arg,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS
            );
        }

        return $content;
    }


    /**
     * @return static
     */
    public function dumper(?string $dumper, ?array $dumperOptions = null)
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
            $this->dumperOptions = $dumperOptions;
        }

        return $this;
    }


    protected function dumpDumper(...$vars) : void
    {
        switch ( $this->dumper ):
            case static::DUMPER_ECHO:
                $this->dumpDumper_echo(...$vars);
                break;

            case static::DUMPER_ECHO_HTML:
                $this->dumpDumper_echo_html(...$vars);
                break;

            case static::DUMPER_STDOUT:
                $this->dumpDumper_stdout(...$vars);
                break;

            case static::DUMPER_STDOUT_HTML:
                $this->dumpDumper_stdout_html(...$vars);
                break;

            case static::DUMPER_DEVTOOLS:
                $this->dumpDumper_devtools(...$vars);
                break;

            case static::DUMPER_PDO:
                $this->dumpDumper_pdo(...$vars);
                break;

            default:
                throw new RuntimeException(
                    [ 'Unknown `dumper`', $this->dumper ]
                );

        endswitch;
    }

    public function dumpDumper_echo(...$vars)
    {
        $content = $this->printPrinter(...$vars);

        $content .= PHP_EOL;

        echo $content;
    }

    public function dumpDumper_echo_html(...$vars)
    {
        $options = $this->dumperOptions;

        $throwIfHeadersSent = (bool) ($options[ 'throw_if_headers_sent' ] ?? true);

        $content = $this->printPrinter(...$vars);
        $content .= PHP_EOL;

        $htmlContent = nl2br($content);

        $isHeadersSent = headers_sent($file, $line);

        if (! $isHeadersSent) {
            header('Content-Type: text/html', true, 500);
        }

        echo $htmlContent;

        if ($isHeadersSent && $throwIfHeadersSent) {
            throw new RuntimeException(
                [ 'Headers already sent', $file, $line ]
            );
        }
    }

    public function dumpDumper_stdout(...$vars)
    {
        $options = $this->dumperOptions;

        $resource = $options[ 'stdout' ] ?? STDOUT;

        $content = $this->printPrinter(...$vars);
        $content .= PHP_EOL;

        fwrite($resource, $content);
    }

    public function dumpDumper_stdout_html(...$vars)
    {
        $options = $this->dumperOptions;

        $resource = $options[ 'stdout' ] ?? STDOUT;
        $throwIfHeadersSent = (bool) ($options[ 'throw_if_headers_sent' ] ?? true);

        $content = $this->printPrinter(...$vars);
        $content .= PHP_EOL;

        $htmlContent = nl2br($content);

        $isStdout = ($resource === STDOUT);
        $isHeadersSent = headers_sent($file, $line);

        if ($isStdout && ! $isHeadersSent) {
            header('Content-Type: text/html', true, 500);
        }

        fwrite($resource, $htmlContent);

        if ($isStdout && $isHeadersSent && $throwIfHeadersSent) {
            throw new RuntimeException(
                [ 'Headers already sent', $file, $line ]
            );
        }
    }

    public function dumpDumper_devtools(...$vars)
    {
        $options = $this->dumperOptions;

        $throwIfHeadersSent = (bool) ($options[ 'throw_if_headers_sent' ] ?? true);

        $content = $this->printPrinter(...$vars);

        $b64content = base64_encode($content);

        $htmlContent = "<script>console.log(window.atob('{$b64content}'));</script>" . PHP_EOL;

        $isHeadersSent = headers_sent($file, $line);

        if (! $isHeadersSent) {
            header('Content-Type: text/html', true, 500);
        }

        echo $htmlContent;

        if ($isHeadersSent && $throwIfHeadersSent) {
            throw new RuntimeException(
                [ 'Headers already sent', $file, $line ]
            );
        }
    }

    public function dumpDumper_pdo(...$vars)
    {
        $options = $this->dumperOptions;

        $pdo = $options[ 'pdo' ] ?? $options[ 0 ];
        $table = $options[ 'table' ] ?? $options[ 1 ];
        $column = $options[ 'column' ] ?? $options[ 2 ];

        if (! ($pdo instanceof \PDO)) {
            throw new LogicException(
                'The `options.pdo` should be instance of: ' . \PDO::class
            );
        }

        $_table = (string) $table;
        if ('' === $_table) {
            throw new LogicException(
                'The `options.table` should be non-empty string'
            );
        }

        $_column = (string) $column;
        if ('' === $_column) {
            throw new LogicException(
                'The `options.column` should be non-empty string'
            );
        }

        $content = $this->printPrinter(...$vars);

        $sql = "INSERT INTO {$_table} ({$_column}) VALUES (?);";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([ $content ]);
    }


    public function print(...$vars) : string
    {
        return $this->printPrinter(...$vars);
    }


    public function dump($var, ...$vars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->dumpTrace($trace, $var, ...$vars);

        return $var;
    }

    public function d($var, ...$vars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->dTrace($trace, $var, ...$vars);

        return $var;
    }

    public function dd(...$vars) : void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->ddTrace($trace, ...$vars);
    }

    public function ddd(?int $limit, $var, ...$vars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->dddTrace($trace, $limit, $var, ...$vars);

        return $var;
    }


    public function dumpTrace(?array $trace, $var, ...$vars)
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->doDumpTrace($trace, $var, ...$vars);

        return $var;
    }

    public function dTrace(?array $trace, $var, ...$vars)
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->doDumpTrace($trace, $var, ...$vars);

        return $var;
    }

    public function ddTrace(?array $trace, ...$vars) : void
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->doDumpTrace($trace, ...$vars);

        die();
    }

    public function dddTrace(?array $trace, ?int $limit, $var, ...$vars)
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        static $current;

        $limit = $limit ?? 1;
        if ($limit < 1) $limit = 1;

        $current = $current ?? $limit;

        $this->doDumpTrace($trace, $var, ...$vars);

        if (0 === --$current) {
            die();
        }

        return $var;
    }


    protected function doDumpTrace(array $trace, ...$vars)
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? -1;
        $traceWhereIs = "{$traceFile}: {$traceLine}";

        $this->dumpDumper($traceWhereIs, ...$vars);
    }
}
