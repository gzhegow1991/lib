<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class DebugModule
{
    /**
     * @var callable
     */
    protected $dumpFn = 'var_dump';


    /**
     * @param callable $fn
     *
     * @return callable|null
     */
    public function dump_fn_static($fn = null) // : ?callable
    {
        if (null !== $fn) {
            $last = $this->dumpFn;

            $this->dumpFn = $fn;

            $result = $last;
        }

        $result = $result ?? $this->dumpFn;

        return $result;
    }

    public function d(?array $trace, $var, ...$vars) // : mixed
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $fn = $this->dump_fn_static();

        $fn($var, ...$vars);

        return $var;
    }

    public function dd(?array $trace, $var, ...$vars) : void
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $fn = $this->dump_fn_static();

        $fn($var, ...$vars);

        die();
    }

    public function ddd(?array $trace, ?int $limit, $var, ...$vars) // : mixed|void
    {
        static $current;

        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $limit = $limit ?? 1;
        if ($limit < 1) $limit = 1;

        $current = $current ?? $limit;

        $fn = $this->dump_fn_static();

        $fn($var, ...$vars);

        if (0 === --$current) {
            die();
        }

        return $var;
    }


    /**
     * @return string|float|int|null
     */
    public function var_dump($var, array $options = []) // : int|float|string|null
    {
        $output = null
            ?? $this->var_dump_output_null($var, $options)
            ?? $this->var_dump_output_bool($var, $options)
            ?? $this->var_dump_output_int($var, $options)
            ?? $this->var_dump_output_float($var, $options)
            ?? $this->var_dump_output_string($var, $options)
            ?? $this->var_dump_output_object($var, $options)
            ?? $this->var_dump_output_array($var, $options)
            ?? $this->var_dump_output_resource($var, $options);

        $withType = $options[ 'with_type' ] ?? true;
        $withId = $options[ 'with_id' ] ?? true;
        $withValue = $options[ 'with_value' ] ?? true;
        $withBraces = $options[ 'with_braces' ] ?? false;

        $maxArrayLevel = $options[ 'max_array_level' ] ?? null;
        $newline = $options[ 'newline' ] ?? null;

        $content = null;
        if (null !== $output) {
            $cnt = count($output);

            if ($cnt > 1) {
                $content = implode(" # ", $output);

            } elseif ($cnt === 1) {
                $content = $output[ 0 ];
            }
        }
        if (null === $content) {
            throw new RuntimeException(
                [ 'Unable to dump', $var ]
            );
        }

        if (null !== $newline) {
            if (false !== strpos($content, "\n")) {
                $lines = explode("\n", $content);

                foreach ( $lines as $i => $line ) {
                    $line = preg_replace('/\s+/', ' ', $line);
                    $line = trim($line, ' ');

                    $lines[ $i ] = $line;
                }

                $content = implode($newline, $lines);
            }
        }

        $forceBraces = $withBraces || $withType || $withId;

        $content = $forceBraces
            ? '{ ' . $content . ' }'
            : $content;

        return $content;
    }

    private function var_dump_output_null($var, array &$options = []) : ?array
    {
        if (! is_null($var)) return null;

        $output = [];

        $output[] = strtoupper(var_export($var, true));

        return $output;
    }

    private function var_dump_output_bool($var, array &$options = []) : ?array
    {
        if (! is_bool($var)) return null;

        $output = [];

        $output[] = strtoupper(var_export($var, true));

        return $output;
    }

    private function var_dump_output_int($var, array &$options = []) : ?array
    {
        if (! is_int($var)) return null;

        $withType = $options[ 'with_type' ] ?? true;
        $withId = $options[ 'with_id' ] ?? true;
        $withValue = $options[ 'with_value' ] ?? true;

        $type = gettype($var);

        $output = [];

        if ($withType) {
            $output[] = $type;
        }
        if ($withValue) {
            $output[] = is_finite($var)
                ? $var
                : (string) $var;
        }

        return $output;
    }

    private function var_dump_output_float($var, array &$options = []) : ?array
    {
        if (! is_float($var)) return null;

        $withType = $options[ 'with_type' ] ?? true;
        $withId = $options[ 'with_id' ] ?? true;
        $withValue = $options[ 'with_value' ] ?? true;

        $type = gettype($var);

        $output = [];

        if ($withType) {
            $output[] = $type;
        }
        if ($withValue) {
            $output[] = is_finite($var)
                ? $var
                : (string) $var;
        }

        return $output;
    }

    private function var_dump_output_string($var, array &$options = []) : ?array
    {
        if (! is_string($var)) return null;

        $withType = $options[ 'with_type' ] ?? true;
        $withId = $options[ 'with_id' ] ?? true;
        $withValue = $options[ 'with_value' ] ?? true;

        $type = gettype($var);

        $stringLen = strlen($var);

        $output = [];

        if ($withType) {
            $output[] = "{$type}({$stringLen})";
        }
        if ($withValue) {
            $_var = $var;
            $_var = str_replace('"', '\"', $_var);

            $mapSpaces = [
                "\n" => '\n',
                "\r" => '\r',
                "\t" => '\t',
                "\v" => '\v',
            ];

            $found = false;

            // $_var = preg_replace_callback('/[^[:print:]]/', static function ($m) use (
            $_var = preg_replace_callback('/\p{C}/u', static function ($m) use (
                &$mapSpaces,
                //
                &$found
            ) {
                $chr = $m[ 0 ];

                if (isset($mapSpaces[ $chr ])) {
                    return $mapSpaces[ $chr ] . $chr;
                }

                $res = ord($chr);
                $res = dechex($res);
                $res = str_pad($res, 2, '0', STR_PAD_LEFT);
                $res = '\x' . $res;

                $found = true;

                return $res;
            }, $_var);

            if ($found) {
                $_var = "b`{$_var}`";
            }

            $output[] = '"' . $_var . '"';
        }

        return $output;
    }

    private function var_dump_output_object($var, array &$options = []) : ?array
    {
        if (! is_object($var)) return null;

        $withId = $options[ 'with_id' ] ?? true;
        $withValue = $options[ 'with_value' ] ?? true;

        $type = gettype($var);

        $objectClass = get_class($var);
        if (0 === strpos($objectClass, 'class@anonymous')) {
            $objectClass = 'class@anonymous';
        }

        $objectId = spl_object_id($var);
        $objectSubtypeCountable = (($var instanceof \Countable) ? 'countable(' . count($var) . ')' : null);
        $objectSubtypeIterable = (is_iterable($var) ? 'iterable' : null);
        $objectSubtypeStringable = (method_exists($var, '__toString') ? 'stringable' : null);

        $subtype = [];
        if ($objectSubtypeCountable) $subtype[] = $objectSubtypeCountable;
        if ($objectSubtypeIterable) $subtype[] = $objectSubtypeIterable;
        if ($objectSubtypeStringable) $subtype[] = $objectSubtypeStringable;
        $subtype = implode(' ', $subtype);
        $subtype = ($subtype ? "({$subtype})" : null);

        $output = [];
        $output[] = "{$type}{$subtype}";
        $output[] = $objectClass;
        if ($withId) {
            $output[] = $objectId;
        }
        if ($withValue) {
            if (method_exists($var, '__toString')) {
                // ! recursion
                $output[] = $this->var_dump(
                    (string) $var,
                    [
                        'with_type'   => false,
                        'with_id'     => false,
                        'with_value'  => true,
                        'with_braces' => false,
                    ] + $options
                );
            }
        }

        $options[ 'with_braces' ] = true;

        return $output;
    }

    private function var_dump_output_array($var, array &$options = []) : ?array
    {
        if (! is_array($var)) return null;

        $theArr = Lib::arr();
        $theParse = Lib::parse();

        $withType = $options[ 'with_type' ] ?? true;
        $withId = $options[ 'with_id' ] ?? true;
        $withValue = $options[ 'with_value' ] ?? true;

        $maxArrayLevel = $options[ 'max_array_level' ] ?? null;

        $type = gettype($var);

        $arrayCopy = $var;
        $arrayCount = count($var);

        $dump = null;
        if ($withValue) {
            foreach ( $theArr->walk_it(
                $arrayCopy,
                ArrModule::WALK_WITH_EMPTY_ARRAYS | ArrModule::WALK_WITH_PARENTS
            ) as $path => &$value ) {
                /** @var array $path */

                if (count($path) < $maxArrayLevel) {
                    continue;
                }

                if (is_object($value)) {
                    // ! recursion
                    $value = $this->var_dump(
                        $value,
                        [
                            'with_type'  => true,
                            'with_value' => false,
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
                            'with_value'      => false,
                            //
                            'max_array_level' => 0,
                        ] + $options
                    );

                    continue;
                }

                if (null !== $theParse->resource($value)) {
                    // ! recursion
                    $value = $this->var_dump(
                        $value,
                        [
                            'with_type'  => true,
                            'with_value' => false,
                        ] + $options
                    );
                }
            }
            unset($value);

            $dump = $this->var_export(
                $arrayCopy,
                [ 'addcslashes' => false ]
            );
        }

        $output = [];
        if ($withType) {
            $output[] = "{$type}({$arrayCount})";
        }
        if ($withValue) {
            $output[] = $dump;
        }

        return $output;
    }

    private function var_dump_output_resource($var, array &$options = []) : ?array
    {
        if (null === Lib::parse()->resource($var)) return null;

        $withId = $options[ 'with_id' ] ?? true;

        $type = gettype($var);

        $resourceType = get_resource_type($var);
        $resourceId = PHP_VERSION_ID > 80000
            ? get_resource_id($var)
            : (int) $var;

        $output = [];
        $output[] = "{$type}({$resourceType})";
        if ($withId) {
            $output[] = $resourceId;
        }

        $options[ 'with_braces' ] = true;

        return $output;
    }


    /**
     * @return string|float|int|null
     */
    public function var_export($var, array $options = [], int $level = 0) // : string|float|int|null
    {
        $indent = $options[ 'indent' ] ?? "  ";
        $newline = $options[ 'newline' ] ?? "\n";
        $addcslashes = $options[ 'addcslashes' ] ?? true;

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
                    $line = str_repeat($indent, $level + 1);

                    if (! $isListIndexed) {
                        $line .= is_string($key) ? "\"{$key}\"" : $key;
                        $line .= " => ";
                    }

                    // ! recursion
                    $line .= $this->var_export($value, $options, $level + 1);

                    $lines[] = $line;
                }

                $result = ""
                    . (($level > 0) ? str_repeat($indent, $level - 1) : "") . "[" . $newline
                    . implode("," . $newline, $lines) . $newline
                    . str_repeat($indent, $level) . "]";

                break;

            default:
                $result = var_export($var, true);

                break;
        }

        return $result;
    }


    public function type($value, array $options = []) : string
    {
        $output = $this->var_dump($value,
            $options + [
                'with_type'       => true,
                'with_id'         => false,
                'with_value'      => false,
                'with_braces'     => false,
                'max_array_level' => 1,
                'newline'         => null,
            ]
        );

        return $output;
    }

    public function type_id($value, array $options = []) : string
    {
        $output = $this->var_dump($value,
            $options + [
                'with_type'       => true,
                'with_id'         => true,
                'with_value'      => false,
                'with_braces'     => false,
                'max_array_level' => 1,
                'newline'         => null,
            ]
        );

        return $output;
    }

    public function type_value($value, array $options = []) : string
    {
        $output = $this->var_dump($value,
            $options + [
                'with_type'       => true,
                'with_id'         => false,
                'with_value'      => true,
                'with_braces'     => false,
                'max_array_level' => 1,
                'newline'         => null,
            ]
        );

        return $output;
    }


    public function value($value, array $options = []) : string
    {
        $output = $this->var_dump($value,
            $options + [
                'with_type'       => false,
                'with_id'         => false,
                'with_value'      => true,
                'with_braces'     => false,
                'max_array_level' => 0,
                'newline'         => ' ',
            ]
        );

        return $output;
    }

    public function array($value, int $maxLevel = null, array $options = []) : string
    {
        $maxLevel = $maxLevel ?? 1;

        $output = $this->var_dump($value,
            $options + [
                'with_type'       => false,
                'with_id'         => false,
                'with_value'      => true,
                'with_braces'     => false,
                'max_array_level' => $maxLevel,
                'newline'         => ' ',
            ]
        );

        return $output;
    }


    public function value_multiline($value, array $options = []) : string
    {
        $output = $this->var_dump($value,
            $options + [
                'with_type'       => false,
                'with_id'         => false,
                'with_value'      => true,
                'with_braces'     => false,
                'max_array_level' => 0,
                'newline'         => null,
            ]
        );

        return $output;
    }

    public function array_multiline($value, int $maxLevel = null, array $options = []) : string
    {
        $maxLevel = $maxLevel ?? 1;

        $output = $this->var_dump($value,
            $options + [
                'with_type'       => false,
                'with_id'         => false,
                'with_value'      => true,
                'with_braces'     => false,
                'max_array_level' => $maxLevel,
                'newline'         => null,
            ]
        );

        return $output;
    }


    public function diff(string $actual, string $expect = null, string &$result = null) : bool
    {
        $result = null;

        $hasExpect = (null !== $expect);

        Lib::str()->eol($actual, $actualLines);

        $cnt = $cntA = count($actualLines);

        if ($hasExpect) {
            Lib::str()->eol($expect, $expectLines);

            $cnt = max($cntA, $cntB = count($expectLines));
        }

        $actualLinesNew = [];
        $expectLinesNew = [];

        $isDiff = false;
        for ( $i = 0; $i < $cnt; $i++ ) {
            $actualLine = $actualLines[ $i ] ?? ' ';
            $expectLine = $expectLines[ $i ] ?? ' ';

            if ('' === $actualLine) $actualLine = ' ';
            if ('' === $expectLine) $expectLine = ' ';

            if (! $hasExpect) {
                $actualLinesNew[] = $actualLine;

            } else {
                if ($actualLine === $expectLine) {
                    $actualLinesNew[] = "[{$i}] > " . $actualLine;
                    $expectLinesNew[] = "[{$i}] > " . $expectLine;

                    continue;
                }

                $expectLinesNew[] = "--- [{$i}] > " . $expectLine;
                $actualLinesNew[] = "+++ [{$i}] > " . $actualLine;
            }

            $isDiff = true;
        }

        if ($isDiff) {
            $lines[] = $expectLinesNew;
            $lines[] = [ '' ];
        }

        $lines[] = $actualLinesNew;

        $result = implode(PHP_EOL, array_merge(...$lines));

        return $isDiff;
    }

    public function diff_vars($actual, $expect = null, string &$result = null) : bool
    {
        ob_start();
        var_dump($actual);
        $aString = ob_get_clean();

        ob_start();
        var_dump($expect);
        $bString = ob_get_clean();

        $isDiff = $this->diff(
            $aString,
            $bString,
            $result
        );

        return $isDiff;
    }
}
