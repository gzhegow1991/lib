<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper;
use Gzhegow\Lib\Modules\Debug\Dumper\DumperInterface;
use Gzhegow\Lib\Modules\Debug\Throwabler\DefaultThrowabler;
use Gzhegow\Lib\Modules\Debug\Throwabler\ThrowablerInterface;
use Gzhegow\Lib\Modules\Debug\Backtracer\DefaultBacktracer;
use Gzhegow\Lib\Modules\Debug\Backtracer\BacktracerInterface;


class DebugModule
{
    /**
     * @var string
     */
    protected static $dirRoot;
    /**
     * @var array
     */
    protected static $varDumpOptions = [];

    public static function staticDirRoot(?string $dirRoot = null) : ?string
    {
        $last = static::$dirRoot;

        if (null !== $dirRoot) {
            $theType = Lib::type();

            $dirRootRealpath = $theType->dirpath_realpath($dirRoot)->orThrow();

            static::$dirRoot = $dirRootRealpath;
        }

        static::$dirRoot = static::$dirRoot ?? null;

        return $last;
    }

    public static function staticVarDumpOptions(?array $varDumpOptions = null) : array
    {
        $last = static::$varDumpOptions;

        if (null !== $varDumpOptions) {
            static::$varDumpOptions = $varDumpOptions;
        }

        static::$varDumpOptions = static::$varDumpOptions ?? [];

        return $last;
    }


    /**
     * @var DumperInterface
     */
    protected $dumper;
    /**
     * @var ThrowablerInterface
     */
    protected $throwabler;
    /**
     * @var BacktracerInterface
     */
    protected $backtracer;


    public function newBacktracer() : BacktracerInterface
    {
        return new DefaultBacktracer();
    }

    public function cloneBacktracer() : BacktracerInterface
    {
        return clone $this->backtracer();
    }

    public function backtracer(?BacktracerInterface $backtracer = null) : BacktracerInterface
    {
        return $this->backtracer = null
            ?? $backtracer
            ?? $this->backtracer
            ?? $this->newBacktracer();
    }


    public function newDumper() : DumperInterface
    {
        return new DefaultDumper();
    }

    public function cloneDumper() : DumperInterface
    {
        return clone $this->dumper();
    }

    public function dumper(?DumperInterface $dumper = null) : DumperInterface
    {
        return $this->dumper = null
            ?? $dumper
            ?? $this->dumper
            ?? $this->newDumper();
    }


    public function newThrowabler() : ThrowablerInterface
    {
        return new DefaultThrowabler();
    }

    public function cloneThrowabler() : ThrowablerInterface
    {
        return clone $this->throwabler();
    }

    public function throwabler(?ThrowablerInterface $throwabler = null) : ThrowablerInterface
    {
        return $this->throwabler = null
            ?? $throwabler
            ?? $this->throwabler
            ?? $this->newThrowabler();
    }


    /**
     * @return Ret<array{ 0: string, 1: int }>
     */
    public function type_fileline($value)
    {
        if (! is_array($value)) {
            return Ret::err(
                [ 'The `value` should be array', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $file = $value[ 'file' ] ?? $value[ 0 ] ?? '{{file}}';
        $line = $value[ 'line' ] ?? $value[ 1 ] ?? -1;

        if ('{{file}}' !== $file) {
            $fileRealpath = realpath($file);

            if (false === $fileRealpath) {
                return Ret::err(
                    [ 'The `value[0]` should be realpath', $file, $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $file = $fileRealpath;
        }

        if (-1 !== $line) {
            if (is_int($lineInt = $line) && ($line > 0)) {
                return Ret::err(
                    [ 'The `value[1]` should be positive integer', $line, $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $line = $lineInt;
        }

        return Ret::val([ $file, $line ]);
    }


    /**
     * @return array{ 0: string, 1: string }
     */
    public function file_line(?array $trace = null, int $step = -2) : array
    {
        $i = 0;

        if (null === $trace) {
            if ($step >= 0) {
                throw new LogicException(
                    [ 'The `step` should be negative integer', $step ]
                );
            }

            $i = -$step - 1;

            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, -$step);
        }

        $t = [
            $trace[ $i ][ 'file' ] ?? '{{file}}',
            $trace[ $i ][ 'line' ] ?? '{{line}}',
        ];

        return $t;
    }


    public function debug_backtrace(
        ?int $options = -1,
        ?int $limit = -1,
        ?string $dirRoot = ''
    ) : BacktracerInterface
    {
        $backtracer = $this->cloneBacktracer();

        if ((null === $options) || ($options >= 0)) {
            $backtracer->options($options);
        }

        if ((null === $limit) || ($limit >= 0)) {
            $backtracer->limit($limit);
        }

        if ('' !== $dirRoot) {
            $backtracer->dirRoot($dirRoot);
        }

        return $backtracer;
    }


    public function print(...$vars) : string
    {
        return $this->dumper()->printerPrint(...$vars);
    }


    public function dp($var, ...$vars) : string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return $this->dumper()->dp($trace, $var, ...$vars);
    }

    public function fnDP() : \Closure
    {
        return function ($var, ...$vars) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

            return $this->dumper()->dp($trace, $var, ...$vars);
        };
    }


    /**
     * @return mixed
     */
    public function d($var, ...$vars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return $this->dumper()->d($trace, $var, ...$vars);
    }

    /**
     * @return mixed|void
     */
    public function dd(...$vars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return $this->dumper()->dd($trace, ...$vars);
    }

    /**
     * @return mixed|void
     */
    public function ddd(?int $limit, $var, ...$vars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return $this->dumper()->ddd($trace, $limit, $var, ...$vars);
    }


    public function fnD() : \Closure
    {
        return function ($var, ...$vars) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

            return $this->dumper()->d($trace, $var, ...$vars);
        };
    }

    public function fnDD() : \Closure
    {
        return function (...$vars) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

            return $this->dumper()->dd($trace, ...$vars);
        };
    }

    public function fnDDD() : \Closure
    {
        return function (?int $limit, $var, ...$vars) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

            return $this->dumper()->ddd($trace, $limit, $var, ...$vars);
        };
    }


    public function fnTD(int $throttleMs) : \Closure
    {
        if ($throttleMs < 0) {
            throw new LogicException(
                [ 'The `throttleMs` should be a non-negative integer', $throttleMs ]
            );
        }

        return function ($var, ...$vars) use ($throttleMs) {
            static $last;

            $last = $last ?? [];

            $t = $this->file_line();

            $key = implode(':', $t);

            $last[ $key ] = $last[ $key ] ?? 0;

            $now = microtime(true);

            if (($now - $last[ $key ]) > ($throttleMs / 1000)) {
                $last[ $key ] = $now;

                $this->dumper()->d([ $t ], $var, ...$vars);
            }

            return $var;
        };
    }


    public function dump_type($value, array $options = [], array &$refContext = []) : string
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
            + $this->staticVarDumpOptions()
            + [
                'array_level_max'  => 1,
                'multiline_escape' => '###',
                'with_braces'      => true,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
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

    public function dump_type_id($value, array $options = [], array &$refContext = []) : string
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
            + $this->staticVarDumpOptions()
            + [
                'array_level_max'  => 1,
                'multiline_escape' => '###',
                'with_braces'      => true,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
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


    public function dump_type_value($value, array $options = [], array &$refContext = []) : string
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
            + $this->staticVarDumpOptions()
            + [
                'array_level_max'  => 1,
                'multiline_escape' => '###',
                'with_braces'      => true,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
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

    public function dump_type_value_multiline($value, array $options = [], array &$refContext = []) : string
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
            + $this->staticVarDumpOptions()
            + [
                'array_level_max'  => 0,
                'multiline_escape' => '###',
                'with_braces'      => true,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
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


    public function dump_value($value, array $options = [], array &$refContext = []) : string
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
            + $this->staticVarDumpOptions()
            + [
                'array_level_max'  => 0,
                'multiline_escape' => '###',
                'with_braces'      => null,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
        );

        $hasValue = array_key_exists('value', $output);

        $isObject = is_object($value);
        $isResource = is_resource($value) || ('resource(closed)' === gettype($value));

        $content = '';
        if ($isObject || $isResource) {
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
            if (array_key_exists('value', $output)) {
                $content .= ' # ' . $output[ 'value' ];
            }

        } elseif ($hasValue) {
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

    public function dump_value_multiline($value, array $options = [], array &$refContext = []) : string
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
            + $this->staticVarDumpOptions()
            + [
                'array_level_max'  => 0,
                'multiline_escape' => '###',
                'with_braces'      => null,
            ];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
        );

        $hasValue = array_key_exists('value', $output);

        $isObject = is_object($value);
        $isResource = is_resource($value) || ('resource(closed)' === gettype($value));

        $content = '';
        if ($isObject || $isResource) {
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
            if (array_key_exists('value', $output)) {
                $content .= ' # ' . $output[ 'value' ];
            }

        } elseif ($hasValue) {
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

        $printableType = $content;
        if ($withBraces) {
            $printableType = '{ ' . $content . ' }';
        }

        $content = ''
            . $options[ 'multiline_escape' ] . "\n"
            . $printableType . "\n"
            . $options[ 'multiline_escape' ];

        return $content;
    }


    public function dump_value_array($value, ?int $levelMax = null, array $options = [], array &$refContext = []) : string
    {
        $levelMax = $levelMax ?? 1;
        if ($levelMax < 0) $levelMax = 0;

        $options[ 'array_level_max' ] = $levelMax;

        $content = $this->dump_value($value, $options, $refContext);

        return $content;
    }

    public function dump_value_array_multiline($value, ?int $levelMax = null, array $options = [], array &$refContext = []) : string
    {
        $levelMax = $levelMax ?? 1;
        if ($levelMax < 0) $levelMax = 0;

        $options[ 'array_level_max' ] = $levelMax;

        $content = $this->dump_value_multiline($value, $options, $refContext);

        return $content;
    }


    public function dump_types(array $options = [], ?string $delimiter = null, ...$values) : string
    {
        $theType = Lib::type();

        $delimiterString = $theType->string_not_empty($delimiter ?? ' | ')->orThrow();

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->dump_type($value, $options);
        }

        $content = implode($delimiterString, $list);

        return $content;
    }

    public function dump_type_ids(array $options = [], ?string $delimiter = null, ...$values) : string
    {
        $theType = Lib::type();

        $delimiterString = $theType->string_not_empty($delimiter ?? ' | ')->orThrow();

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->dump_type_id($value, $options);
        }

        $content = implode($delimiterString, $list);

        return $content;
    }

    public function dump_type_values(array $options = [], ?string $delimiter = null, ...$values) : string
    {
        $theType = Lib::type();

        $delimiterString = $theType->string_not_empty($delimiter ?? ' | ')->orThrow();

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->dump_type_value($value, $options);
        }

        $content = implode($delimiterString, $list);

        return $content;
    }


    public function dump_values(array $options = [], ?string $delimiter = null, ...$values) : string
    {
        $theType = Lib::type();

        $delimiterString = $theType->string_not_empty($delimiter ?? ' | ')->orThrow();

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->dump_value($value, $options);
        }

        $content = implode($delimiterString, $list);

        return $content;
    }


    public function var_dump($var, array $options = [], array &$refContext = []) : string
    {
        $options = []
            + $options
            + $this->staticVarDumpOptions()
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
            $refContext
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
                $printableType .= ' @' . $output[ 'id' ];
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

    protected function var_dump_output($var, array $options = [], array &$refContext = []) : array
    {
        $output = null
            ?? $this->var_dump_output_null($var, $options, $refContext)
            ?? $this->var_dump_output_bool($var, $options, $refContext)
            ?? $this->var_dump_output_int($var, $options, $refContext)
            ?? $this->var_dump_output_float($var, $options, $refContext)
            ?? $this->var_dump_output_string($var, $options, $refContext)
            ?? $this->var_dump_output_object($var, $options, $refContext)
            ?? $this->var_dump_output_array($var, $options, $refContext)
            ?? $this->var_dump_output_resource($var, $options, $refContext);

        return $output;
    }

    protected function var_dump_output_null($var, array $options = [], array &$refContext = []) : ?array
    {
        if (! is_null($var)) return null;

        $output = [];
        $output[ 'type' ] = gettype($var);
        $output[ 'value' ] = strtoupper(var_export($var, true));

        return $output;
    }

    protected function var_dump_output_bool($var, array $options = [], array &$refContext = []) : ?array
    {
        if (! is_bool($var)) return null;

        $output = [];
        $output[ 'type' ] = gettype($var);
        $output[ 'value' ] = strtoupper(var_export($var, true));

        return $output;
    }

    protected function var_dump_output_int($var, array $options = [], array &$refContext = []) : ?array
    {
        if (! is_int($var)) return null;

        $map = [
            ' ' . PHP_INT_MIN => ((string) PHP_INT_MIN),
        ];

        $varString = (string) $var;

        $output = [];
        $output[ 'type' ] = gettype($var);
        $output[ 'value' ] = null
            ?? $map[ ' ' . $varString ]
            ?? var_export($var, true);

        return $output;
    }

    protected function var_dump_output_float($var, array $options = [], array &$refContext = []) : ?array
    {
        if (! is_float($var)) return null;

        $output = [];
        $output[ 'type' ] = gettype($var);
        $output[ 'value' ] = var_export($var, true);

        return $output;
    }

    protected function var_dump_output_string($var, array $options = [], array &$refContext = []) : ?array
    {
        if (! is_string($var)) return null;

        $theStr = Lib::str();

        $withValue = $options[ 'with_value' ] ?? true;

        $phpType = gettype($var);
        $phpStrlen = strlen($var);

        $printableValue = [];
        if ($withValue) {
            $printableValue = str_replace('"', '\"', $var);
            $printableValue = $theStr->dump_encode($printableValue);
            $printableValue = '"' . $printableValue . '"';
            $printableValue = [ $printableValue ];
        }

        $output = [];
        $output[ 'type' ] = "{$phpType}({$phpStrlen})";

        if ($withValue) {
            if ([] !== $printableValue) {
                $output[ 'value' ] = $printableValue[ 0 ];
            }
        }

        return $output;
    }

    protected function var_dump_output_object($var, array $options = [], array &$refContext = []) : ?array
    {
        if (! is_object($var)) return null;

        $theDate = Lib::date();

        $phpType = gettype($var);

        $withValue = $options[ 'with_value' ] ?? false;

        $objectClassId = get_class($var);
        $objectClass = $objectClassId;
        $objectId = spl_object_id($var);

        if (false !== ($pos = strpos($objectClass, $needle = '@anonymous'))) {
            $objectClass = substr($objectClass, 0, $pos + strlen($needle));
        }

        $objectSubtypeCountable = (($var instanceof \Countable) ? 'countable(' . count($var) . ')' : null);
        $objectSubtypeInvokable = (method_exists($var, '__invoke') ? 'invokable' : null);
        $objectSubtypeIterable = (is_iterable($var) ? 'iterable' : null);
        $objectSubtypeSerializable = ((($var instanceof \Serializable) || method_exists($var, '__serialize')) ? 'serializable' : null);
        $objectSubtypeStringable = (method_exists($var, '__toString') ? 'stringable' : null);

        $objectSubtype = [];
        if ($objectSubtypeCountable) $objectSubtype[] = $objectSubtypeCountable;
        if ($objectSubtypeInvokable) $objectSubtype[] = $objectSubtypeInvokable;
        if ($objectSubtypeIterable) $objectSubtype[] = $objectSubtypeIterable;
        if ($objectSubtypeSerializable) $objectSubtype[] = $objectSubtypeSerializable;
        if ($objectSubtypeStringable) $objectSubtype[] = $objectSubtypeStringable;

        $printableValue = [];
        if ($withValue) {
            if ($var instanceof \DateTimeInterface) {
                $printableValue = [ '"' . $var->format('Y-m-d\TH:i:s.uP') . '"' ];

            } elseif ($var instanceof \DateTimeZone) {
                $printableValue = [ '"' . $var->getName() . '"' ];

            } elseif ($var instanceof \DateInterval) {
                $printableValue = [ '"' . $theDate->interval_encode($var) . '"' ];

            } elseif ($var instanceof \Throwable) {
                $printableValue = [ '"' . $var->getMessage() . '"' ];
            }
        }

        $output = [];
        $output[ 'type' ] = $phpType;
        if ($objectSubtype) {
            $objectSubtype = implode(' ', $objectSubtype);

            $output[ 'subtype' ] = $objectSubtype;
        }
        $output[ 'class' ] = $objectClass;
        $output[ 'class_id' ] = $objectClassId;
        $output[ 'id' ] = $objectId;

        if ($withValue) {
            if ([] !== $printableValue) {
                $output[ 'value' ] = $printableValue[ 0 ];
            }
        }

        return $output;
    }

    protected function var_dump_output_array($var, array $options = [], array &$refContext = []) : ?array
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

        $printableValue = [];
        if ($withValue) {
            $gen = $theArr->walk_it(
                $arrayCopy,
                _ARR_WALK_WITH_EMPTY_ARRAYS | _ARR_WALK_WITH_PARENTS
            );

            foreach ( $gen as $path => &$value ) {
                if (false
                    || is_object($value)
                    || $theType->resource($value)->isOk()
                ) {
                    // > ! recursion
                    $value = $this->var_dump(
                        $value,
                        [
                            'with_type'  => true,
                            'with_id'    => false,
                            'with_value' => false,
                        ] + $options
                    );

                    continue;
                }

                $shouldVarDumpInsteadOfVarExport = (count($path) >= $arrayLevelMax);

                if ($shouldVarDumpInsteadOfVarExport) {
                    if (is_string($value)) {
                        // > ! recursion
                        $value = $this->var_dump(
                            $value,
                            [
                                'with_type'  => false,
                                'with_id'    => false,
                                'with_value' => true,
                            ] + $options
                        );

                        $value = substr($value, 1, -1);

                        continue;
                    }

                    if (is_array($value)) {
                        if ([] !== $value) {
                            // > ! recursion
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
                        }

                        // continue;
                    }
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

            $printableValue = [ $printableValue ];
        }

        $output = [];
        $output[ 'type' ] = "{$phpType}({$arrayCount})";

        if ($withValue) {
            if ([] !== $printableValue) {
                $output[ 'value' ] = $printableValue[ 0 ];
            }
        }

        return $output;
    }

    protected function var_dump_output_resource($var, array $options = [], array &$refContext = []) : ?array
    {
        $isResourceOpened = (is_resource($var));
        $isResourceClosed = ('resource (closed)' === gettype($var));
        if (! ($isResourceOpened || $isResourceClosed)) {
            return null;
        }

        $withValue = $options[ 'with_value' ] ?? true;

        $phpType = 'resource';

        $resourceType = $isResourceOpened
            ? 'opened'
            : 'closed';

        $printableValue = [];
        if ($withValue) {
            $printableValue = $isResourceOpened
                ? [ get_resource_type($var) ]
                : [];
        }

        $resourceId = PHP_VERSION_ID > 80000
            ? get_resource_id($var)
            : (int) $var;

        $output = [];
        $output[ 'type' ] = $phpType;
        $output[ 'subtype' ] = $resourceType;
        $output[ 'id' ] = $resourceId;

        if ($withValue) {
            if ([] !== $printableValue) {
                $output[ 'value' ] = $printableValue[ 0 ];
            }
        }

        return $output;
    }


    /**
     * @return string|float|int|null
     */
    public function var_export($var, array $options = [], ?int $level = null)
    {
        $level = $level ?? 0;

        $theType = Lib::type();

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
                if ([] === $var) {
                    $result = "[]";

                } else {
                    $isListSorted = $theType->list_sorted($var)->isOk();

                    $lines = [];
                    foreach ( $var as $key => $value ) {
                        $rowIndent = str_repeat($indent, $level + 1);

                        $keyString = '';
                        if (! $isListSorted) {
                            $keyString = is_string($key)
                                ? "\"{$key}\" => "
                                : "{$key} => ";
                        }

                        if ([] === $value) {
                            $valueString = '[]';

                        } else {
                            // > ! recursion
                            $valueString = $this->var_export(
                                $value,
                                $options,
                                $level + 1
                            );
                        }

                        $line = ''
                            . $rowIndent
                            . $keyString
                            . $valueString;

                        $lines[] = $line;
                    }

                    $arrayEndIndent = '';
                    if ($level > 0) {
                        $arrayEndIndent = str_repeat($indent, $level);
                    }

                    $result = ""
                        . "[" . $newline
                        . implode("," . $newline, $lines) . $newline
                        . $arrayEndIndent
                        . "]";
                }

                break;

            case "object":
                if ($var instanceof \stdClass) {
                    $result = ''
                        . '(object) '
                        . $this->var_export(
                            get_object_vars($var),
                            $options,
                            $level,
                        );

                } else {
                    $result = var_export($var, true);
                }

                break;

            default:
                $result = var_export($var, true);

                break;
        }

        return $result;
    }


    public function diff(
        string $new, string $old,
        array $refs = []
    ) : bool
    {
        $theStr = Lib::str();

        $withDiffLines = array_key_exists(0, $refs);
        if ($withDiffLines) {
            $refDiffLines =& $refs[ 0 ];
        }
        $refDiffLines = null;

        $oldLines = $theStr->lines($old);
        $newLines = $theStr->lines($new);

        $oldCnt = count($oldLines);
        $newCnt = count($newLines);

        $maxCnt = max($oldCnt, $newCnt);
        $maxCntLen = strlen($maxCnt);

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
                $lineNumber = str_pad(
                    $lineNumber,
                    $maxCntLen, ' ', STR_PAD_LEFT
                );

                $line = (null === $isLineDiff)
                    ? $line
                    : "[ {$lineNumber} ] {$line}";

                $diffLines[ $i ] = $line;
            }

            $diffLines = array_reverse($diffLines);
        }

        if ($withDiffLines) {
            $refDiffLines = $diffLines;
        }

        unset($refDiffLines);

        return $isDiff;
    }

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


    public function print_table(array $table, ?bool $return = null) : ?string
    {
        if ([] === $table) {
            return null;
        }

        $rowKeys = array_fill_keys(
            array_keys($table),
            true
        );

        $colKeys = [];
        foreach ( $rowKeys as $rowKey => $bool ) {
            if (! is_array($table[ $rowKey ])) {
                throw new RuntimeException(
                    [
                        'The `table` should be array of arrays',
                        $table[ $rowKey ],
                    ]
                );
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

        foreach ( $table as $row ) {
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

        $fnDrawLine = static function () use ($thWidth, $tdWidths) {
            echo '+';
            echo str_repeat('-', $thWidth + 2) . '+';
            foreach ( $tdWidths as $tdWidth ) {
                echo str_repeat('-', $tdWidth + 2) . '+';
            }
            echo "\n";
        };

        call_user_func($fnDrawLine);

        echo '|';
        echo ' ' . str_pad('', $thWidth) . ' |';
        foreach ( $colKeys as $colKey => $bool ) {
            echo ' ' . str_pad($colKey, $tdWidths[ $colKey ]) . ' |';
        }
        echo "\n";

        call_user_func($fnDrawLine);

        foreach ( $table as $rowKey => $row ) {
            echo '|';
            echo ' ' . str_pad($rowKey, $thWidth) . ' |';
            foreach ( $colKeys as $colKey => $bool ) {
                echo ' ' . str_pad($row[ $colKey ] ?? 'NULL', $tdWidths[ $colKey ]) . ' |';
            }
            echo "\n";
        }

        call_user_func($fnDrawLine);

        if ($return) {
            $content = ob_get_clean();

            return $content;
        }

        return null;
    }
}
