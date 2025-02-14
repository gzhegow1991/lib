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
            $theStr = Lib::str();

            $foundBinary = false;

            $asciiControlsNoTrims = $theStr->loadAsciiControlsNoTrims();
            $trims = $theStr->loadTrims();

            $_var = $var;

            $_var = str_replace('"', '\"', $_var);

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
                $trims[ $i ] .= $i;
            }
            $_var = str_replace(
                array_keys($trims),
                array_values($trims),
                $_var
            );

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
        if (! Lib::type()->resource($_var, $var)) {
            return null;
        }

        $withId = $options[ 'with_id' ] ?? true;

        $type = gettype($_var);

        $resourceType = get_resource_type($_var);
        $resourceId = PHP_VERSION_ID > 80000
            ? get_resource_id($_var)
            : (int) $_var;

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


    public function types($separator = '', array $options = [], ...$values) : string
    {
        $_separator = (string) $separator;
        if ('' === $_separator) {
            $_separator = ' | ';
        }

        $list = [];

        foreach ( $values as $value ) {
            $list[] = $this->type($value, $options);
        }

        $content = implode($_separator, $list);

        return $content;
    }

    public function type_ids($separator = '', array $options = [], ...$values) : string
    {
        $_separator = (string) $separator;
        if ('' === $_separator) {
            $_separator = ' | ';
        }

        $list = [];

        foreach ( $values as $value ) {
            $list[] = $this->type_id($value, $options);
        }

        $content = implode($_separator, $list);

        return $content;
    }

    public function type_values($separator = '', array $options = [], ...$values) : string
    {
        $_separator = (string) $separator;
        if ('' === $_separator) {
            $_separator = ' | ';
        }

        $list = [];

        foreach ( $values as $value ) {
            $list[] = $this->type_value($value, $options);
        }

        $content = implode($_separator, $list);

        return $content;
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

    public function values($separator = '', array $options = [], ...$values) : string
    {
        $_separator = (string) $separator;
        if ('' === $_separator) {
            $_separator = ' | ';
        }

        $list = [];

        foreach ( $values as $value ) {
            $list[] = $this->value($value, $options);
        }

        $content = implode($_separator, $list);

        return $content;
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


    public function value_array($value, int $maxLevel = null, array $options = []) : string
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

    public function value_array_multiline($value, int $maxLevel = null, array $options = []) : string
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


    public function diff(
        string $new, string $old,
        array $refs = null
    ) : bool
    {
        $refs = $refs ?? [];

        $withResultLines = array_key_exists(0, $refs);
        $withResultString = array_key_exists(1, $refs);

        $oldLines = Lib::str()->lines($old);
        $newLines = Lib::str()->lines($new);

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

        $iOld = $oldCnt;
        $iNew = $newCnt;
        $diffLines = [];

        $isDiff = false;

        $isRemovePrevious = [];
        $isAddPrevious = [];
        $isRemove = [];
        $isAdd = [];
        while ( true ) {
            $iOldGt0 = $iOld > 0;
            $iNewGt0 = $iNew > 0;
            $iOldEq0 = $iOld === 0;
            $iNewEq0 = $iNew === 0;

            if (! ($iOldGt0 || $iNewGt0)) {
                break;
            }

            $isRemovePrevious = $isRemove;
            $isAddPrevious = $isAdd;

            $isSame = [];
            $isRemove = [];
            $isAdd = [];

            if (true
                && $iOldGt0
                && $iNewGt0
                && ($oldLines[ $iOld - 1 ] === $newLines[ $iNew - 1 ])
            ) {
                $isSame = [ $oldLines[ $iOld - 1 ], $iOld ];

                $iNew--;
                $iOld--;

            } elseif (true
                && $iOldGt0
                && (false
                    || $iNewEq0
                    || ($matrix[ $iOld ][ $iNew - 1 ] < $matrix[ $iOld - 1 ][ $iNew ])
                )
            ) {
                $isRemove = [ $oldLines[ $iOld - 1 ], $iOld ];

                $iOld--;

            } elseif (true
                && $iNewGt0
                && (false
                    || $iOldEq0
                    || ($matrix[ $iOld ][ $iNew - 1 ] >= $matrix[ $iOld - 1 ][ $iNew ])
                )
            ) {
                $isAdd = [ $newLines[ $iNew - 1 ], $iNew ];

                $iNew--;
            }

            if ($isSame) {
                $diffLines[] = $isSame[ 0 ];

            } elseif ($isAdd) {
                if (count($isRemovePrevious)) {
                    $diffLines[ count($diffLines) - 1 ] = ""
                        . "[ {$isAdd[ 1 ]} ] +++ > {$isAdd[ 0 ]}"
                        . ' @ '
                        . "--- {$isRemovePrevious[ 0 ]}";

                    $isAdd = [];

                } else {
                    $diffLines[] = "[ {$isAdd[ 1 ]} ] +++ > {$isAdd[ 0 ]}";
                }

                $isDiff = true;

            } elseif (count($isRemove)) {
                if (count($isAddPrevious)) {
                    $diffLines[ count($diffLines) - 1 ] = ""
                        . "[ {$isAddPrevious[ 1 ]} ] +++ > {$isAddPrevious[ 0 ]}"
                        . ' @ '
                        . "--- {$isRemove[ 0 ]}";

                    $isRemove = [];

                } else {
                    $diffLines[] = "[ {$isRemove[ 1 ]} ] --- > {$isRemove[ 0 ]}";
                }

                $isDiff = true;
            }
        }

        $diffLines = array_reverse($diffLines);

        if ($withResultLines) {
            $ref =& $refs[ 0 ];
            $ref = $diffLines;
            unset($ref);
        }

        if ($withResultString) {
            $diffString = implode(PHP_EOL, $diffLines);

            $ref =& $refs[ 1 ];
            $ref = $diffString;
            unset($ref);
        }

        return $isDiff;
    }

    public function diff_vars(
        $new = null, $old = null,
        array $results = null
    ) : bool
    {
        ob_start();
        var_dump($new);
        $aString = ob_get_clean();

        ob_start();
        var_dump($old);
        $bString = ob_get_clean();

        $isDiff = $this->diff(
            $aString, $bString,
            $results
        );

        return $isDiff;
    }
}
