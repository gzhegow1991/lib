<?php

namespace Gzhegow\Lib\Traits;

trait DebugTrait
{
    public static function debug_var_dump($var, array $options = []) // : int|float|string
    {
        $withType = $options[ 'with_type' ] ?? true;
        $withId = $options[ 'with_id' ] ?? true;
        $withValue = $options[ 'with_value' ] ?? true;

        $newline = $options[ 'newline' ] ?? "\n";

        $maxArrayLevel = $options[ 'max_array_level' ] ?? null;

        $output = null;

        $withBraces = false;

        $type = gettype($var);

        if (null === $output) {
            if (false
                || is_null($var)
                || is_bool($var)
            ) {
                $output = [];
                $output[] = strtoupper(var_export($var, true));
            }
        }

        if (null === $output) {
            if (false
                || is_numeric($var)
            ) {
                $output = [];
                if ($withType) {
                    $output[] = $type;
                }
                if ($withValue) {
                    $output[] = $var;
                }
            }
        }

        if (null === $output) {
            if (is_string($var)) {
                $stringLen = strlen($var);

                $output = [];
                if ($withType) {
                    $output[] = "{$type}({$stringLen})";
                }
                if ($withValue) {
                    $_var = $var;
                    $_var = str_replace('"', '\"', $_var);

                    $output[] = '"' . $_var . '"';
                }
            }
        }

        if (null === $output) {
            if (is_array($var)) {
                $arrayCopy = $var;
                $arrayCount = count($var);

                $dump = null;
                if ($withValue) {
                    foreach ( static::array_walk(
                        $arrayCopy,
                        _ARRAY_WALK_WITH_EMPTY_ARRAYS | _ARRAY_WALK_WITH_PARENTS
                    ) as $path => &$value ) {
                        /** @var array $path */

                        if (count($path) <= $maxArrayLevel) {
                            continue;
                        }

                        if (! is_array($value)) {
                            continue;
                        }

                        $value = static::debug_var_dump($value,
                            [
                                'with_type'       => true,
                                'with_value'      => false,
                                'max_array_level' => 0,
                            ] + $options
                        );
                    }
                    unset($value);

                    $dump = static::debug_var_export(
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
            }
        }

        if (null === $output) {
            if (is_object($var)) {
                $objectClass = get_class($var);
                $objectId = spl_object_id($var);
                $objectSubtypeIterable = (is_iterable($var) ? 'iterable' : null);
                $objectSubtypeCountable = (is_a($var, \Countable::class) ? 'countable(' . count($var) . ')' : null);

                $subtype = [];
                if ($objectSubtypeIterable) $subtype[] = $objectSubtypeIterable;
                if ($objectSubtypeCountable) $subtype[] = $objectSubtypeCountable;
                $subtype = implode(' ', $subtype);
                $subtype = ($subtype ? "({$subtype})" : null);

                $output = [];
                $output[] = "{$type}{$subtype}";
                $output[] = $objectClass;
                if ($withId) {
                    $output[] = $objectId;
                }

                $withBraces = true;
            }
        }

        if (null === $output) {
            if (is_resource($var)) {
                $resourceType = get_resource_type($var);
                $resourceId = PHP_VERSION_ID > 80000
                    ? get_resource_id($var)
                    : (int) $var;

                $output = [];
                $output[] = "{$type}({$resourceType})";
                if ($withId) {
                    $output[] = $resourceId;
                }

                $withBraces = true;
            }
        }

        $cnt = count($output);
        if ($cnt > 1) {
            $output = implode(" # ", $output);

        } elseif ($cnt === 1) {
            $output = $output[ 0 ];
        }

        if ("\n" !== $newline) {
            if (false !== strpos($output, "\n")) {
                $lines = explode("\n", $output);

                foreach ( $lines as $i => $line ) {
                    $line = preg_replace('/\s+/', ' ', $line);
                    $line = trim($line, ' ');

                    $lines[ $i ] = $line;
                }

                $output = implode($newline, $lines);
            }
        }

        $withBraces = $withBraces || $withType || $withId;

        $output = $withBraces
            ? '{ ' . $output . ' }'
            : $output;

        return $output;
    }

    public static function debug_var_export($var, array $options = [], int $level = 0) : ?string
    {
        $indent = $options[ 'indent' ] ?? "  ";
        $newline = $options[ 'newline' ] ?? "\n";
        $addcslashes = $options[ 'addcslashes' ] ?? true;

        switch ( gettype($var) ) {
            case "null":
                $result = "NULL";
                break;

            case "boolean":
                $result = ($var === true) ? "TRUE" : "FALSE";
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
                    $line .= static::debug_var_export($value, $options, $level + 1);

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


    public static function debug_type($value, array $options = []) : string
    {
        $output = static::debug_var_dump($value,
            $options + [
                'with_type'       => true,
                'with_id'         => false,
                'with_value'      => false,
                'max_array_level' => 1,
            ]
        );

        return $output;
    }

    public static function debug_type_id($value, array $options = []) : string
    {
        $output = static::debug_var_dump($value,
            $options + [
                'with_type'       => true,
                'with_id'         => true,
                'with_value'      => false,
                'max_array_level' => 1,
            ]
        );

        return $output;
    }

    public static function debug_type_value($value, array $options = []) : string
    {
        $output = static::debug_var_dump($value,
            $options + [
                'with_type'       => true,
                'with_id'         => false,
                'with_value'      => true,
                'max_array_level' => 1,
            ]
        );

        return $output;
    }


    public static function debug_value($value, array $options = []) : string
    {
        $output = static::debug_var_dump($value,
            $options + [
                'with_type'       => false,
                'with_id'         => false,
                'with_value'      => true,
                'newline'         => ' ',
                'max_array_level' => 0,
            ]
        );

        return $output;
    }

    public static function debug_array($value, array $options = []) : string
    {
        $output = static::debug_var_dump($value,
            $options + [
                'with_type'       => false,
                'with_id'         => false,
                'with_value'      => true,
                'newline'         => ' ',
                'max_array_level' => 1,
            ]
        );

        return $output;
    }


    public static function debug_value_multiline($value, array $options = []) : string
    {
        $output = static::debug_var_dump($value,
            $options + [
                'with_type'       => false,
                'with_id'         => false,
                'with_value'      => true,
                'max_array_level' => 0,
            ]
        );

        return $output;
    }

    public static function debug_array_multiline($value, array $options = []) : string
    {
        $output = static::debug_var_dump($value,
            $options + [
                'with_type'       => false,
                'with_id'         => false,
                'with_value'      => true,
                'max_array_level' => 1,
            ]
        );

        return $output;
    }



    public static function debug_diff(string $a, string $b = null, string &$result = null) : bool
    {
        $result = null;

        $hasB = (null !== $b);

        static::str_eol($a, $aLines);

        $cnt = $cntA = count($aLines);

        if ($hasB) {
            static::str_eol($b, $bLines);

            $cnt = max($cntA, $cntB = count($bLines));
        }

        $linesA = [];
        $linesB = [];

        $isDiff = false;
        for ( $i = 0; $i < $cnt; $i++ ) {
            $aLine = ($aLines[ $i ] ?? ' ') ?: '""';
            $bLine = ($bLines[ $i ] ?? ' ') ?: '""';

            if (! $hasB) {
                $linesA[] = $aLine;

            } else {
                if ($aLine === $bLine) {
                    $linesA[] = $aLine;

                    continue;
                }

                $linesA[] = "[{$i}] " . '--- ' . $aLine;
                $linesB[] = "[{$i}] " . '+++ ' . $bLine;
            }

            $isDiff = true;
        }

        $result = implode(PHP_EOL, array_merge($linesA, [ '' ], $linesB));

        return $isDiff;
    }

    public static function debug_diff_vars($a, $b, string &$result = null) : bool
    {
        ob_start();
        var_dump($a);
        $aString = ob_get_clean();

        ob_start();
        var_dump($b);
        $bString = ob_get_clean();

        $isDiff = static::debug_diff(
            $aString,
            $bString,
            $result
        );

        return $isDiff;
    }
}
