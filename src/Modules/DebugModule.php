<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class DebugModule
{
    /**
     * @var callable
     */
    protected $dumperFn;
    /**
     * @var callable
     */
    protected $dumpFn;
    /**
     * @var array
     */
    protected $varDumpOptions = [];


    public function __construct()
    {
        $this->dumperFn = [ $this, 'dumper_var_dump' ];
        $this->dumpFn = [ $this, 'dump_stdout' ];
    }


    /**
     * @param callable $fnDumper
     *
     * @return callable|null
     */
    public function static_dumper_fn($fnDumper = null) // : ?callable
    {
        if (null !== $fnDumper) {
            $last = $this->dumperFn;

            if ($last !== $fnDumper) {
                $isValid = false;

                $erf = null;
                $erm = null;

                $reflectionArgs = is_array($fnDumper)
                    ? $fnDumper
                    : [ $fnDumper ];

                if (! $isValid) {
                    try {
                        $rf = new \ReflectionFunction(...$reflectionArgs);
                        $rfParameters = $rf->getParameters();
                        $rfReturnType = $rf->getReturnType();
                        if (true
                            && (count($rfParameters) === 1)
                            && ($rfParameters[ 0 ]->isVariadic())
                            && ($rfReturnType !== null)
                            && ($rfReturnType->isBuiltin())
                            && (! $rfReturnType->allowsNull())
                            && ($rfReturnType->getName() === 'string')
                        ) {
                            $isValid = true;
                        }
                    }
                    catch ( \Throwable $erf ) {
                    }
                }

                if (! $isValid) {
                    try {
                        $rm = new \ReflectionMethod(...$reflectionArgs);
                        $rmParameters = $rm->getParameters();
                        $rmReturnType = $rm->getReturnType();
                        if (true
                            && (count($rmParameters) === 1)
                            && ($rmParameters[ 0 ]->isVariadic())
                            && ($rmReturnType !== null)
                            && ($rmReturnType->isBuiltin())
                            && (! $rmReturnType->allowsNull())
                            && ($rmReturnType->getName() === 'string')
                        ) {
                            $isValid = true;
                        }
                    }
                    catch ( \Throwable $erm ) {
                    }
                }

                if (! $isValid) {
                    throw new LogicException('Invalid `dumperFn`', $erf, $erm);
                }

                $this->dumperFn = $fnDumper;
            }

            $result = $last;
        }

        $result = $result ?? $this->dumperFn;

        return $result;
    }

    /**
     * @param callable $fnDump
     *
     * @return callable|null
     */
    public function static_dump_fn($fnDump = null) // : ?callable
    {
        if (null !== $fnDump) {
            $last = $this->dumpFn;

            if ($last !== $fnDump) {
                $erf = null;
                $erm = null;

                $reflectionArgs = is_array($fnDump)
                    ? $fnDump
                    : [ $fnDump ];

                $isValid = false;

                if (! $isValid) {
                    if (count($reflectionArgs) === 1) {
                        try {
                            $rf = new \ReflectionFunction(...$reflectionArgs);
                            $rfParameters = $rf->getParameters();
                            $rfReturnType = $rf->getReturnType();
                            if (true
                                && (count($rfParameters) === 2)
                                && ($rfParameters[ 0 ]->isArray())
                                && ($rfParameters[ 1 ]->isVariadic())
                                && (($rfReturnType === null) || ($rfReturnType->getName() === 'void'))
                            ) {
                                $isValid = true;
                            }
                        }
                        catch ( \Throwable $erf ) {
                        }
                    }
                }

                if (! $isValid) {
                    try {
                        $rm = new \ReflectionMethod(...$reflectionArgs);
                        $rmParameters = $rm->getParameters();
                        $rmReturnType = $rm->getReturnType();
                        if (true
                            && (count($rmParameters) === 2)
                            && ($rmParameters[ 0 ]->isArray())
                            && ($rmParameters[ 1 ]->isVariadic())
                            && (($rmReturnType === null) || ($rmReturnType->getName() === 'void'))
                        ) {
                            $isValid = true;
                        }
                    }
                    catch ( \Throwable $erm ) {
                    }
                }

                if (! $isValid) {
                    throw new LogicException('Invalid `dumpFn`', $erf, $erm);
                }

                $this->dumpFn = $fnDump;
            }

            $result = $last;
        }

        $result = $result ?? $this->dumpFn;

        return $result;
    }

    public function static_var_dump_options(array $varDumpOptions = null) : ?array
    {
        if (null !== $varDumpOptions) {
            $last = $this->varDumpOptions;

            $this->varDumpOptions = $varDumpOptions;

            $result = $last;
        }

        $result = $result ?? $this->varDumpOptions;

        return $result;
    }


    public function dumper_var_dump(...$vars) : string
    {
        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= PHP_EOL;
            }

            $content .= $this->var_dump($arg);
        }

        return $content;
    }

    public function dumper_var_export(...$vars) : string
    {
        $content = '';

        foreach ( $vars as $arg ) {
            if ($content) {
                $content .= PHP_EOL;
            }

            $content .= $this->var_export($arg);
        }

        return $content;
    }

    public function dumper_print_r(...$vars) : string
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

    public function dumper_var_dump_native(...$vars) : string
    {
        ob_start();
        var_dump(...$vars);
        $content = ob_get_clean();

        return $content;
    }

    public function dumper_var_export_native(...$vars) : string
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

    public function dumper_json_encode(...$vars) : string
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
     * @noinspection PhpFullyQualifiedNameUsageInspection
     * @noinspection PhpUndefinedClassInspection
     * @noinspection PhpUndefinedNamespaceInspection
     */
    public function dumper_symfony(...$vars) : string
    {
        if (! class_exists('\Symfony\Component\VarDumper\VarDumper')) {
            throw new RuntimeException(
                'Please run: `composer require symfony/var-dumper`'
            );
        }

        $cloner = new \Symfony\Component\VarDumper\Cloner\VarCloner();
        $dumper = Lib::php()->is_terminal()
            ? new \Symfony\Component\VarDumper\Dumper\CliDumper()
            : new \Symfony\Component\VarDumper\Dumper\HtmlDumper();

        $content = '';

        foreach ( $vars as $arg ) {
            $clonedVar = $cloner->cloneVar($arg);

            $content .= $dumper->dump($clonedVar);
        }

        return $content;
    }


    public function dump_echo(array $options, ...$vars)
    {
        $fn = $this->static_dumper_fn();

        $content = $fn(...$vars);
        $content .= PHP_EOL;

        echo $content;
    }

    public function dump_stdout(array $options, ...$vars)
    {
        $resource = $options[ 'stdout' ] ?? $options[ 0 ] ?? STDOUT;

        $fn = $this->static_dumper_fn();

        $content = $fn(...$vars);
        $content .= PHP_EOL;

        fwrite($resource, $content);
    }

    public function dump_stdout_html(array $options, ...$vars)
    {
        $resource = $options[ 'stdout' ] ?? $options[ 0 ] ?? STDOUT;

        $fn = $this->static_dumper_fn();

        $content = $fn(...$vars);
        $content .= PHP_EOL;

        $htmlContent = nl2br($content);

        if (! headers_sent()) {
            header('Content-Type: text/html');
        }

        fwrite($resource, $htmlContent);
    }

    public function dump_browser_console(array $options, ...$vars)
    {
        $fn = $this->static_dumper_fn();

        $content = $fn(...$vars);

        $b64content = base64_encode($content);

        $htmlContent = "<script>console.log(window.atob('{$b64content}'));</script>" . PHP_EOL;

        if (! headers_sent()) {
            header('Content-Type: text/html');
        }

        fwrite(STDOUT, $htmlContent);
    }

    public function dump_pdo(array $options, ...$vars)
    {
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

        $fn = $this->static_dumper_fn();

        $content = $fn(...$vars);

        $sql = "INSERT INTO {$_table} ({$_column}) VALUES (?);";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([ $content ]);
    }


    public function dump(?array $trace, ?array $options, $var, ...$vars) // : mixed
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? 0;
        $traceWhereIs = "{$traceFile}: {$traceLine}";

        $options = $options ?? [];

        $fn = $this->static_dump_fn();

        $fn($options, $traceWhereIs, $var, ...$vars);

        return $var;
    }

    public function d(?array $trace, ?array $options, $var, ...$vars) // : mixed
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->dump($trace, $options, $var, ...$vars);

        return $var;
    }

    public function dd(?array $trace, ?array $options, $var, ...$vars) : void
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->dump($trace, $options, $var, ...$vars);

        die();
    }

    public function ddd(?array $trace, ?array $options, ?int $limit, $var, ...$vars) // : mixed|void
    {
        static $current;

        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $limit = $limit ?? 1;
        if ($limit < 1) $limit = 1;

        $current = $current ?? $limit;

        $this->dump($trace, $options, $var, ...$vars);

        if (0 === --$current) {
            die();
        }

        return $var;
    }


    public function var_dump($var, array $options = [], array &$context = []) : string
    {
        $options = []
            + $options
            + $this->static_var_dump_options()
            + [
                'with_type'        => true,
                'with_id'          => true,
                'with_value'       => true,
                'with_braces'      => null,
                'array_level_max'  => 1,
                'array_indent'     => '',
                'array_newline'    => ' ',
                'multiline_escape' => '###',
            ];

        $output = $this->var_dump_output(
            $var,
            $options,
            $context
        );

        $withType = $options[ 'with_type' ];
        $withId = $options[ 'with_id' ];
        $withValue = $options[ 'with_value' ];

        $printableType = '';
        if (array_key_exists('type', $output)) {
            $printableType .= $output[ 'type' ];
        }
        if (array_key_exists('subtype', $output)) {
            $printableType .= '(' . $output[ 'subtype' ] . ')';
        }
        if ($withId) {
            if (array_key_exists('class_id', $output)) {
                $printableType .= ' # ' . $output[ 'class_id' ];
            }
            if (array_key_exists('id', $output)) {
                $printableType .= ' &' . $output[ 'id' ];
            }
        } else {
            if (array_key_exists('class', $output)) {
                $printableType .= ' # ' . $output[ 'class' ];
            }
        }

        $content = [];
        if ($withType) {
            $content[] = $printableType;
        }
        if ($withValue) {
            if (array_key_exists('value', $output)) {
                $content[] = $output[ 'value' ];

            } elseif (! $withType) {
                $forceBraces = true;

                $content[] = $printableType;
            }
        }

        $content = implode(' # ', $content);

        $withBraces = null
            ?? $options[ 'with_braces' ]
            ?? ($withId ? true : null)
            ?? ($withType ? true : null)
            ?? $forceBraces
            ?? false;

        if ($withBraces) {
            $content = '{ ' . $content . ' }';
        }

        return $content;
    }

    protected function var_dump_output($var, array $options = [], array &$context = []) : array
    {
        $output = null
            ?? $this->var_dump_output_null($var, $options, $context)
            ?? $this->var_dump_output_bool($var, $options, $context)
            ?? $this->var_dump_output_int($var, $options, $context)
            ?? $this->var_dump_output_float($var, $options, $context)
            ?? $this->var_dump_output_string($var, $options, $context)
            ?? $this->var_dump_output_object($var, $options, $context)
            ?? $this->var_dump_output_array($var, $options, $context)
            ?? $this->var_dump_output_resource($var, $options, $context);

        return $output;
    }

    private function var_dump_output_null($var, array $options = [], array &$context = []) : ?array
    {
        if (! is_null($var)) return null;

        $phpType = gettype($var);
        $printableValue = strtoupper(var_export($var, true));

        $output = [];
        $output[ 'type' ] = $phpType;
        $output[ 'value' ] = $printableValue;

        return $output;
    }

    private function var_dump_output_bool($var, array $options = [], array &$context = []) : ?array
    {
        if (! is_bool($var)) return null;

        $phpType = gettype($var);
        $printableValue = strtoupper(var_export($var, true));

        $output = [];
        $output[ 'type' ] = $phpType;
        $output[ 'value' ] = $printableValue;

        return $output;
    }

    private function var_dump_output_int($var, array $options = [], array &$context = []) : ?array
    {
        if (! is_int($var)) return null;

        $output = [];
        $output[ 'type' ] = gettype($var);
        $output[ 'value' ] = is_finite($var)
            ? $var
            : (string) $var;

        return $output;
    }

    private function var_dump_output_float($var, array $options = [], array &$context = []) : ?array
    {
        if (! is_float($var)) return null;

        $phpType = gettype($var);
        $printableValue = is_finite($var)
            ? $var
            : (string) $var;

        $output = [];
        $output[ 'type' ] = $phpType;
        $output[ 'value' ] = $printableValue;

        return $output;
    }

    private function var_dump_output_string($var, array $options = [], array &$context = []) : ?array
    {
        if (! is_string($var)) return null;

        $withValue = $options[ 'with_value' ] ?? true;

        $phpType = gettype($var);
        $phpStrlen = strlen($var);

        $printableValue = null;
        if ($withValue) {
            $theStr = Lib::str();

            $foundBinary = false;

            $asciiControlsNoTrims = $theStr->loadAsciiControlsNoTrims();
            $trims = $theStr->loadTrims();

            $_var = $var;

            $_var = str_replace('"', '\"', $_var);

            $count = 0;

            $_var = str_replace(
                array_keys($asciiControlsNoTrims),
                array_values($asciiControlsNoTrims),
                $_var,
                $count
            );
            if ($count) {
                $foundBinary = true;
            }

            if ($isUtf8 = $theStr->is_utf8($_var)) {
                $invisibles = $theStr->loadInvisibles();

                $count = 0;

                $_var = str_replace(
                    array_keys($invisibles),
                    array_values($invisibles),
                    $_var,
                    $count
                );

                if ($count) {
                    $foundBinary = true;
                }

            } else {
                $_varUtf8 = $theStr->utf8_encode($_var);

                if ($_varUtf8 !== $_var) {
                    $_var = $_varUtf8;

                    $foundBinary = true;
                }
            }

            if ($foundBinary) {
                $_var = "b`{$_var}`";
            }

            foreach ( $trims as $i => $v ) {
                if ($i === "\n") {
                    $trims[ $i ] .= $i;
                }
            }
            $_var = str_replace(
                array_keys($trims),
                array_values($trims),
                $_var
            );

            $printableValue = '"' . $_var . '"';
        }

        $output = [];
        $output[ 'type' ] = "{$phpType}({$phpStrlen})";

        if ($withValue) {
            $output[ 'value' ] = $printableValue;
        }

        return $output;
    }

    private function var_dump_output_object($var, array $options = [], array &$context = []) : ?array
    {
        if (! is_object($var)) return null;

        $phpType = gettype($var);

        $objectClassId = get_class($var);
        $objectClass = $objectClassId;
        $objectId = spl_object_id($var);

        if (false !== ($pos = strpos($objectClass, $needle = '@anonymous'))) {
            $objectClass = substr($objectClass, 0, $pos + strlen($needle));
        }

        $objectSubtypeCountable = (($var instanceof \Countable) ? 'countable(' . count($var) . ')' : null);
        $objectSubtypeIterable = (is_iterable($var) ? 'iterable' : null);
        $objectSubtypeStringable = (method_exists($var, '__toString') ? 'stringable' : null);
        $objectSubtypeInvokable = (method_exists($var, '__invoke') ? 'invokable' : null);

        $objectSubtype = [];
        if ($objectSubtypeCountable) $objectSubtype[] = $objectSubtypeCountable;
        if ($objectSubtypeIterable) $objectSubtype[] = $objectSubtypeIterable;
        if ($objectSubtypeStringable) $objectSubtype[] = $objectSubtypeStringable;
        if ($objectSubtypeInvokable) $objectSubtype[] = $objectSubtypeInvokable;

        $output = [];
        $output[ 'type' ] = $phpType;
        if ($objectSubtype) {
            $objectSubtype = implode(' ', $objectSubtype);

            $output[ 'subtype' ] = $objectSubtype;
        }
        $output[ 'class' ] = $objectClass;
        $output[ 'class_id' ] = $objectClassId;
        $output[ 'id' ] = $objectId;

        return $output;
    }

    private function var_dump_output_array($var, array $options = [], array &$context = []) : ?array
    {
        if (! is_array($var)) return null;

        $theArr = Lib::arr();
        $theType = Lib::type();

        $withValue = $options[ 'with_value' ] ?? true;

        $arrayLevelMax = $options[ 'array_level_max' ] ?? null;
        $arrayIndent = $options[ 'array_indent' ] ?? null;
        $arrayNewline = $options[ 'array_newline' ] ?? null;

        $arrayLevelMax = (int) $arrayLevelMax;
        if ($arrayLevelMax < 0) $arrayLevelMax = 0;

        $phpType = gettype($var);

        $arrayCopy = $var;
        $arrayCount = count($var);

        $printableValue = null;
        if ($withValue) {
            $gen = $theArr->walk_it(
                $arrayCopy,
                ArrModule::WALK_WITH_EMPTY_ARRAYS | ArrModule::WALK_WITH_PARENTS
            );

            foreach ( $gen as $path => &$value ) {
                /**
                 * @var array $path
                 */

                if (count($path) < $arrayLevelMax) {
                    continue;
                }

                if (is_string($value)) {
                    // ! recursion
                    $value = $this->var_dump(
                        $value,
                        [
                            'with_type'       => false,
                            'with_id'         => false,
                            'with_value'      => true,
                            //
                            'array_level_max' => 0,
                        ] + $options
                    );

                    $value = substr($value, 1, -1);

                    continue;
                }

                if (false
                    || is_object($value)
                    || $theType->resource($_value, $value)
                ) {
                    // ! recursion
                    $value = $this->var_dump(
                        $value,
                        [
                            'with_type'       => true,
                            'with_id'         => false,
                            'with_value'      => false,
                            //
                            'array_level_max' => 0,
                        ] + $options
                    );

                    continue;
                }

                if (is_array($value)) {
                    // ! recursion
                    $value = $this->var_dump(
                        $value,
                        [
                            'with_type'       => true,
                            'with_id'         => false,
                            'with_value'      => false,
                            //
                            'array_level_max' => 0,
                        ] + $options
                    );

                    continue;
                }
            }
            unset($value);

            $printableValue = $this->var_export(
                $arrayCopy,
                [
                    'addcslashes' => false,
                    'indent'      => $arrayIndent,
                    'newline'     => $arrayNewline,
                ]
            );
        }

        $output = [];
        $output[ 'type' ] = "{$phpType}({$arrayCount})";

        if ($withValue) {
            $output[ 'value' ] = $printableValue;
        }

        return $output;
    }

    private function var_dump_output_resource($var, array $options = [], array &$context = []) : ?array
    {
        if (! Lib::type()->resource($_var, $var)) {
            return null;
        }

        $phpType = gettype($_var);

        $resourceType = get_resource_type($_var);
        $resourceId = PHP_VERSION_ID > 80000
            ? get_resource_id($_var)
            : (int) $_var;

        $output = [];
        $output[ 'type' ] = $phpType;
        $output[ 'subtype' ] = $resourceType;
        $output[ 'id' ] = $resourceId;

        return $output;
    }


    public function type($value, array $options = [], array &$context = []) : string
    {
        $options = []
            + [
                'with_type'     => true,
                'with_id'       => false,
                'with_value'    => false,
                'array_indent'  => '',
                'array_newline' => ' ',
            ]
            + $options
            + $this->static_var_dump_options()
            + [
                'array_level_max'  => 1,
                'multiline_escape' => '###',
                'with_braces'      => true,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $context
        );

        $content = '';
        if (array_key_exists('type', $output)) {
            $content .= $output[ 'type' ];
        }
        if (array_key_exists('subtype', $output)) {
            $content .= '(' . $output[ 'subtype' ] . ')';
        }
        if (array_key_exists('class', $output)) {
            $content .= ' # ' . $output[ 'class' ];
        }

        $withBraces = null
            ?? $options[ 'with_braces' ]
            ?? true;

        if ($withBraces) {
            $content = '{ ' . $content . ' }';
        }

        return $content;
    }

    public function type_id($value, array $options = [], array &$context = []) : string
    {
        $options = []
            + [
                'with_type'     => true,
                'with_id'       => true,
                'with_value'    => false,
                'array_indent'  => '',
                'array_newline' => ' ',
            ]
            + $options
            + $this->static_var_dump_options()
            + [
                'array_level_max'  => 1,
                'multiline_escape' => '###',
                'with_braces'      => true,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $context
        );

        $content = '';
        if (array_key_exists('type', $output)) {
            $content .= $output[ 'type' ];
        }
        if (array_key_exists('subtype', $output)) {
            $content .= '(' . $output[ 'subtype' ] . ')';
        }
        if (array_key_exists('class_id', $output)) {
            $content .= ' # ' . $output[ 'class_id' ];
        }
        if (array_key_exists('id', $output)) {
            $content .= ' &' . $output[ 'id' ];
        }

        $withBraces = null
            ?? $options[ 'with_braces' ]
            ?? true;

        if ($withBraces) {
            $content = '{ ' . $content . ' }';
        }

        return $content;
    }


    public function type_value($value, array $options = [], array &$context = []) : string
    {
        $options = []
            + [
                'with_type'     => true,
                'with_id'       => false,
                'with_value'    => true,
                'array_indent'  => '',
                'array_newline' => ' ',
            ]
            + $options
            + $this->static_var_dump_options()
            + [
                'array_level_max'  => 1,
                'multiline_escape' => '###',
                'with_braces'      => true,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $context
        );

        $content = '';
        if (array_key_exists('type', $output)) {
            $content .= $output[ 'type' ];
        }
        if (array_key_exists('subtype', $output)) {
            $content .= '(' . $output[ 'subtype' ] . ')';
        }
        if (array_key_exists('class', $output)) {
            $content .= ' # ' . $output[ 'class' ];
        }
        if (array_key_exists('value', $output)) {
            $content .= ' # ' . $output[ 'value' ];
        }

        $withBraces = null
            ?? $options[ 'with_braces' ]
            ?? true;

        if ($withBraces) {
            $content = '{ ' . $content . ' }';
        }

        return $content;
    }

    public function type_value_multiline($value, array $options = [], array &$context = []) : string
    {
        $options = []
            + [
                'with_type'     => true,
                'with_id'       => false,
                'with_value'    => true,
                'array_indent'  => '  ',
                'array_newline' => "\n",
            ]
            + $options
            + $this->static_var_dump_options()
            + [
                'array_level_max'  => 0,
                'multiline_escape' => '###',
                'with_braces'      => true,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $context
        );

        $printableType = '';
        if (array_key_exists('type', $output)) {
            $printableType .= $output[ 'type' ];
        }
        if (array_key_exists('subtype', $output)) {
            $printableType .= '(' . $output[ 'subtype' ] . ')';
        }
        if (array_key_exists('class', $output)) {
            $printableType .= ' # ' . $output[ 'class' ];
        }

        $printableValue = '';
        if (array_key_exists('value', $output)) {
            $printableValue .= $output[ 'value' ];
        }

        $withBraces = null
            ?? $options[ 'with_braces' ]
            ?? false;

        if ($withBraces) {
            $printableType = '{ ' . $printableType . ' }';
        }

        $content = ''
            . $options[ 'multiline_escape' ] . "\n"
            . $printableType . "\n"
            . $printableValue . "\n"
            . $options[ 'multiline_escape' ];

        return $content;
    }


    public function value($value, array $options = [], array &$context = []) : string
    {
        $options = []
            + [
                'with_type'     => false,
                'with_id'       => false,
                'with_value'    => true,
                'array_indent'  => '',
                'array_newline' => ' ',
            ]
            + $options
            + $this->static_var_dump_options()
            + [
                'array_level_max'  => 0,
                'multiline_escape' => '###',
                'with_braces'      => null,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $context
        );

        $content = '';
        if (array_key_exists('value', $output)) {
            $content .= $output[ 'value' ];

        } else {
            $forceBraces = true;

            if (array_key_exists('type', $output)) {
                $content .= $output[ 'type' ];
            }
            if (array_key_exists('subtype', $output)) {
                $content .= '(' . $output[ 'subtype' ] . ')';
            }
            if (array_key_exists('class', $output)) {
                $content .= ' # ' . $output[ 'class' ];
            }
        }

        $withBraces = null
            ?? $options[ 'with_braces' ]
            ?? $forceBraces
            ?? false;

        if ($withBraces) {
            $content = '{ ' . $content . ' }';
        }

        return $content;
    }

    public function value_multiline($value, array $options = [], array &$context = []) : string
    {
        $options = []
            + [
                'with_type'     => false,
                'with_id'       => false,
                'with_value'    => true,
                'array_indent'  => '  ',
                'array_newline' => "\n",
            ]
            + $options
            + $this->static_var_dump_options()
            + [
                'array_level_max'  => 0,
                'multiline_escape' => '###',
                'with_braces'      => null,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $context
        );

        $content = '';
        if (array_key_exists('value', $output)) {
            $content .= $output[ 'value' ];

        } else {
            $forceBraces = true;

            if (array_key_exists('type', $output)) {
                $content .= $output[ 'type' ];
            }
            if (array_key_exists('subtype', $output)) {
                $content .= '(' . $output[ 'subtype' ] . ')';
            }
            if (array_key_exists('class', $output)) {
                $content .= ' # ' . $output[ 'class' ];
            }
        }

        $withBraces = null
            ?? $options[ 'with_braces' ]
            ?? $forceBraces
            ?? false;

        if ($withBraces) {
            $printableType = '{ ' . $content . ' }';
        }

        $content = ''
            . $options[ 'multiline_escape' ] . "\n"
            . $content . "\n"
            . $options[ 'multiline_escape' ];

        return $content;
    }


    public function value_array($value, int $levelMax = null, array $options = [], array &$context = []) : string
    {
        $levelMax = $levelMax ?? 1;
        if ($levelMax < 0) $levelMax = 0;

        $options[ 'array_level_max' ] = $levelMax;

        $content = $this->value($value, $options, $context);

        return $content;
    }

    public function value_array_multiline($value, int $levelMax = null, array $options = [], array &$context = []) : string
    {
        $levelMax = $levelMax ?? 1;
        if ($levelMax < 0) $levelMax = 0;

        $options[ 'array_level_max' ] = $levelMax;

        $content = $this->value_multiline($value, $options, $context);

        return $content;
    }


    public function types($separator = null, array $options = [], ...$values) : string
    {
        $_separator = $separator ?? ' | ';
        $_separator = (string) $_separator;

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->type($value, $options);
        }

        $content = implode($_separator, $list);

        return $content;
    }

    public function type_ids($separator = null, array $options = [], ...$values) : string
    {
        $_separator = $separator ?? ' | ';
        $_separator = (string) $_separator;

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->type_id($value, $options);
        }

        $content = implode($_separator, $list);

        return $content;
    }

    public function type_values($separator = null, array $options = [], ...$values) : string
    {
        $_separator = $separator ?? ' | ';
        $_separator = (string) $_separator;

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->type_value($value, $options);
        }

        $content = implode($_separator, $list);

        return $content;
    }


    public function values($separator = null, array $options = [], ...$values) : string
    {
        $_separator = $separator ?? ' | ';
        $_separator = (string) $_separator;

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->value($value, $options);
        }

        $content = implode($_separator, $list);

        return $content;
    }


    /**
     * @return string|float|int|null
     */
    public function var_export($var, array $options = [], int $level = 0) // : string|float|int|null
    {
        $addcslashes = $options[ 'addcslashes' ] ?? true;
        $indent = $options[ 'indent' ] ?? "  ";
        $newline = $options[ 'newline' ] ?? "\n";

        $indent = (string) $indent;
        $newline = (string) $newline;

        switch ( gettype($var) ) {
            case "NULL":
                $result = "NULL";
                break;

            case "boolean":
                $result = ($var === true) ? "TRUE" : "FALSE";
                break;

            case "integer":
            case "double":
                $result = $var;
                break;

            case "string":
                $result = $addcslashes
                    ? addcslashes($var, "\\\$\"\r\n\t\v\f")
                    : $var;

                $result = "\"{$result}\"";

                break;

            case "array":
                $keys = array_keys($var);

                foreach ( $keys as $key ) {
                    if (is_string($key)) {
                        $isList = false;

                        break;
                    }
                }
                $isList = $isList ?? true;

                $isListIndexed = $isList
                    && ($keys === range(0, count($var) - 1));

                $lines = [];
                foreach ( $var as $key => $value ) {
                    $rowIndent = str_repeat($indent, $level + 1);

                    $keyString = '';
                    if (! $isListIndexed) {
                        $keyString = is_string($key)
                            ? "\"{$key}\""
                            : $key;

                        $keyString .= " => ";
                    }

                    // ! recursion
                    $valueString = $this->var_export($value, $options, $level + 1);

                    $line = ''
                        . $rowIndent
                        . $keyString
                        . $valueString;

                    $lines[] = $line;
                }

                $arrayStartIndent = '';
                $arrayEndIndent = '';
                if ($level > 0) {
                    $arrayStartIndent = str_repeat($indent, $level - 1);
                    $arrayEndIndent = str_repeat($indent, $level);
                }

                $result = ""
                    . $arrayStartIndent
                    . "[" . $newline
                    . implode("," . $newline, $lines) . $newline
                    . $arrayEndIndent
                    . "]";

                break;

            default:
                $result = var_export($var, true);

                break;
        }

        return $result;
    }


    public function print_table(array $table, bool $return = null) : ?string
    {
        if (! $cnt = count($table)) {
            return null;
        }

        $rowKeys = array_fill_keys(
            array_keys($table),
            true
        );

        $colKeys = [];
        foreach ( $rowKeys as $rowKey => $bool ) {
            if (! is_array($table[ $rowKey ])) {
                throw new RuntimeException('The `table` should be array of arrays');
            }

            foreach ( array_keys($table[ $rowKey ]) as $colKey ) {
                if (! isset($colKeys[ $colKey ])) {
                    $colKeys[ $colKey ] = true;
                }
            }
        }

        $thWidth = max(
            array_map('strlen', array_keys($rowKeys))
        );
        $tdWidths = array_combine(
            $list = array_keys($colKeys),
            array_map('strlen', $list)
        );

        foreach ( $table as $rowKey => $row ) {
            foreach ( $row as $colKey => $colValue ) {
                $tdWidths[ $colKey ] = max(
                    $tdWidths[ $colKey ] ?? 0,
                    strlen((string) $colValue)
                );
            }
        }

        if ($return) {
            ob_start();
        }

        $fnDrawLine = function () use ($thWidth, $tdWidths) {
            echo '+';
            echo str_repeat('-', $thWidth + 2) . '+';
            foreach ( $tdWidths as $tdWidth ) {
                echo str_repeat('-', $tdWidth + 2) . '+';
            }
            echo "\n";
        };

        $fnDrawLine();

        echo '|';
        echo ' ' . str_pad('', $thWidth) . ' |';
        foreach ( $colKeys as $colKey => $bool ) {
            echo ' ' . str_pad($colKey, $tdWidths[ $colKey ]) . ' |';
        }
        echo "\n";

        $fnDrawLine();

        foreach ( $table as $rowKey => $row ) {
            echo '|';
            echo ' ' . str_pad($rowKey, $thWidth) . ' |';
            foreach ( $colKeys as $colKey => $bool ) {
                echo ' ' . str_pad($row[ $colKey ] ?? 'NULL', $tdWidths[ $colKey ]) . ' |';
            }
            echo "\n";
        }

        $fnDrawLine();

        if ($return) {
            $content = ob_get_clean();

            return $content;
        }

        return null;
    }


    /**
     * @param array{ 0: string[]|null } $refs
     */
    public function diff(
        string $new, string $old,
        array $refs = []
    ) : bool
    {
        $theStr = Lib::str();

        $withResultLines = array_key_exists(0, $refs);

        $refResultLines = null;
        if ($withResultLines) {
            $refResultLines =& $refs[ 0 ];
            $refResultLines = null;
        }

        $oldLines = $theStr->lines($old);
        $newLines = $theStr->lines($new);

        $oldCnt = count($oldLines);
        $newCnt = count($newLines);

        $oldLinesIndex = array_flip($oldLines);
        $newLinesIndex = array_flip($newLines);

        $matrix = [];
        for ( $iOld = 0; $iOld <= $oldCnt; $iOld++ ) {
            for ( $iNew = 0; $iNew <= $newCnt; $iNew++ ) {
                $matrix[ $iOld ][ $iNew ] = 0;
            }
        }

        for ( $iOld = 1; $iOld <= $oldCnt; $iOld++ ) {
            for ( $iNew = 1; $iNew <= $newCnt; $iNew++ ) {
                if ($oldLines[ $iOld - 1 ] === $newLines[ $iNew - 1 ]) {
                    $matrix[ $iOld ][ $iNew ] = $matrix[ $iOld - 1 ][ $iNew - 1 ] + 1;

                } else {
                    $matrix[ $iOld ][ $iNew ] = max(
                        $matrix[ $iOld - 1 ][ $iNew ],
                        $matrix[ $iOld ][ $iNew - 1 ]
                    );
                }
            }
        }

        $isDiff = false;
        $diffLines = [];

        $iOld = $oldCnt;
        $iNew = $newCnt;
        while ( true ) {
            $iOldGt0 = $iOld > 0;
            $iNewGt0 = $iNew > 0;
            $iOldEq0 = $iOld === 0;
            $iNewEq0 = $iNew === 0;

            if (! ($iOldGt0 || $iNewGt0)) {
                break;
            }

            if (true
                && $iOldGt0
                && $iNewGt0
                && ($oldLines[ $iOld - 1 ] === $newLines[ $iNew - 1 ])
            ) {
                $line = $oldLines[ $iOld - 1 ];
                $line = ('' === $line) ? '~' : $line;
                // $line = $line;

                $lineNumber = $iOld;

                $diffLines[] = [ $line, $lineNumber, null ];

                $iNew--;
                $iOld--;

            } elseif (true
                && $iOldGt0
                && (false
                    || $iNewEq0
                    || ($matrix[ $iOld ][ $iNew - 1 ] < $matrix[ $iOld - 1 ][ $iNew ])
                )
            ) {
                $line = $oldLines[ $iOld - 1 ];
                $line = ('' === $line) ? '~' : $line;
                $line = '--- > ' . $line;

                $lineNumber = $iOld;

                $diffLines[] = [ $line, $lineNumber, false ];

                $iOld--;

                $isDiff = true;

            } elseif (true
                && $iNewGt0
                && (false
                    || $iOldEq0
                    || ($matrix[ $iOld ][ $iNew - 1 ] >= $matrix[ $iOld - 1 ][ $iNew ])
                )
            ) {
                $line = $newLines[ $iNew - 1 ];
                $line = ('' === $line) ? '~' : $line;
                $line = '+++ > ' . $line;

                $lineNumber = $iNew;

                $diffLines[] = [ $line, $lineNumber, true ];

                $iNew--;

                $isDiff = true;
            }
        }

        if ($diffLines) {
            foreach ( $diffLines as $i => [ $line, $lineNumber, $isLineDiff ] ) {
                $line = (null === $isLineDiff)
                    ? $line
                    : "[ {$lineNumber} ] {$line}";

                $diffLines[ $i ] = $line;
            }

            $diffLines = array_reverse($diffLines);
        }

        if ($withResultLines) {
            $refResultLines = $diffLines;
        }

        unset($refResultLines);

        return $isDiff;
    }

    /**
     * @param array{ 0: string[]|null } $refs
     */
    public function diff_vars(
        $new = null, $old = null,
        array $refs = []
    ) : bool
    {
        ob_start();
        var_dump($new);
        $newString = ob_get_clean();

        ob_start();
        var_dump($old);
        $oldString = ob_get_clean();

        $isDiff = $this->diff(
            $newString, $oldString,
            $refs
        );

        return $isDiff;
    }
}
