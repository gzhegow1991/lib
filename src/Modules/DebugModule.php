<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Debug\Dumper\DefaultDebugDumper;
use Gzhegow\Lib\Modules\Debug\Dumper\DebugDumperInterface;
use Gzhegow\Lib\Modules\Debug\Throwabler\DefaultDebugThrowabler;
use Gzhegow\Lib\Modules\Debug\Backtracer\DefaultDebugBacktracer;
use Gzhegow\Lib\Modules\Debug\Throwabler\DebugThrowablerInterface;
use Gzhegow\Lib\Modules\Debug\Backtracer\DebugBacktracerInterface;


class DebugModule
{
    const FILE_DEFAULT = '{{file}}';
    const LINE_DEFAULT = -1;

    const VAR_DUMP_OPT_ARRAY_INDENT     = 'array_indent';
    const VAR_DUMP_OPT_ARRAY_LEVEL_MAX  = 'array_level_max';
    const VAR_DUMP_OPT_ARRAY_NEWLINE    = 'array_newline';
    const VAR_DUMP_OPT_MULTILINE_ESCAPE = 'multiline_escape';
    const VAR_DUMP_OPT_WITH_BRACES      = 'with_braces';
    const VAR_DUMP_OPT_WITH_ID          = 'with_id';
    const VAR_DUMP_OPT_WITH_TYPE        = 'with_type';
    const VAR_DUMP_OPT_WITH_VALUE       = 'with_value';

    const VAR_EXPORT_OPT_WITH_ADDCSLASHES = 'with_addcslashes';
    const VAR_EXPORT_OPT_INDENT           = 'indent';
    const VAR_EXPORT_OPT_NEWLINE          = 'newline';

    const LIST_VAR_DUMP_OPT = [
        self::VAR_DUMP_OPT_ARRAY_INDENT     => true,
        self::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => true,
        self::VAR_DUMP_OPT_ARRAY_NEWLINE    => true,
        self::VAR_DUMP_OPT_MULTILINE_ESCAPE => true,
        self::VAR_DUMP_OPT_WITH_BRACES      => true,
        self::VAR_DUMP_OPT_WITH_ID          => true,
        self::VAR_DUMP_OPT_WITH_TYPE        => true,
        self::VAR_DUMP_OPT_WITH_VALUE       => true,
    ];

    const LIST_VAR_EXPORT_OPT = [
        self::VAR_EXPORT_OPT_WITH_ADDCSLASHES => true,
        self::VAR_EXPORT_OPT_INDENT           => true,
        self::VAR_EXPORT_OPT_NEWLINE          => true,
    ];


    /**
     * @var string
     */
    protected $stateDirRoot;
    /**
     * @var bool
     */
    protected $stateShouldTrace;
    /**
     * @var array
     */
    protected $stateVarExportOptionsDefault;
    /**
     * @var array
     */
    protected $stateVarDumpOptionsDefault;

    /**
     * @param string|false|null $dirRoot
     */
    public function stateDirRoot($dirRoot = null) : ?string
    {
        $last = null;

        if ( $isChange = (null !== $dirRoot) ) {
            $last = $this->stateDirRoot;

            if ( false === $dirRoot ) {
                $this->stateDirRoot = null;

            } else {
                $theType = Lib::type();

                $dirRootRealpath = $theType->dirpath_realpath($dirRoot)->orThrow();

                $this->stateDirRoot = $dirRootRealpath;
            }
        }

        // > null is the default
        // if (null === $this->stateDirRoot) {
        //     $this->stateDirRoot = null;
        // }

        return $isChange ? $last : $this->stateDirRoot;
    }

    public function stateShouldTrace(?bool $shouldTrace = null) : ?bool
    {
        $last = null;

        if ( $isChange = (null !== $shouldTrace) ) {
            $last = $this->stateShouldTrace;

            if ( false === $shouldTrace ) {
                $this->stateShouldTrace = null;

            } else {
                $this->stateShouldTrace = (bool) $shouldTrace;
            }
        }

        if ( null === $this->stateShouldTrace ) {
            $this->stateShouldTrace = false;
        }

        return $isChange ? $last : $this->stateShouldTrace;
    }

    /**
     * @param array|false|null $varExportOptionsDefault
     */
    public function stateVarExportOptionsDefault($varExportOptionsDefault = null) : ?array
    {
        $last = null;

        if ( $isChange = (null !== $varExportOptionsDefault) ) {
            $last = $this->stateVarExportOptionsDefault;

            if ( false === $varExportOptionsDefault ) {
                $this->stateVarExportOptionsDefault = null;

            } else {
                $theType = Lib::type();

                $theType->array($varExportOptionsDefault)->orThrow();

                $this->stateVarExportOptionsDefault = []
                    + $varExportOptionsDefault
                    + [
                        static::VAR_EXPORT_OPT_WITH_ADDCSLASHES => null,
                        static::VAR_EXPORT_OPT_INDENT           => "  ",
                        static::VAR_EXPORT_OPT_NEWLINE          => "\n",
                    ];
            }
        }

        if ( null === $this->stateVarExportOptionsDefault ) {
            $this->stateVarExportOptionsDefault = [
                static::VAR_EXPORT_OPT_WITH_ADDCSLASHES => null,
                static::VAR_EXPORT_OPT_INDENT           => "  ",
                static::VAR_EXPORT_OPT_NEWLINE          => "\n",
            ];
        }

        return $isChange ? $last : $this->stateVarExportOptionsDefault;
    }

    /**
     * @param array|false|null $varDumpOptionsDefault
     */
    public function stateVarDumpOptionsDefault($varDumpOptionsDefault = null) : ?array
    {
        $last = null;

        if ( $isChange = (null !== $varDumpOptionsDefault) ) {
            $last = $this->stateVarDumpOptionsDefault;

            if ( false === $varDumpOptionsDefault ) {
                $this->stateVarDumpOptionsDefault = null;

            } else {
                $theType = Lib::type();

                $theType->array($varDumpOptionsDefault)->orThrow();

                $this->stateVarDumpOptionsDefault = []
                    + $varDumpOptionsDefault
                    + [
                        static::VAR_DUMP_OPT_ARRAY_INDENT     => '',
                        static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 1,
                        static::VAR_DUMP_OPT_ARRAY_NEWLINE    => ' ',
                        static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                        static::VAR_DUMP_OPT_WITH_BRACES      => null,
                        static::VAR_DUMP_OPT_WITH_ID          => null,
                        static::VAR_DUMP_OPT_WITH_TYPE        => null,
                        static::VAR_DUMP_OPT_WITH_VALUE       => null,
                    ];
            }
        }

        if ( null === $this->stateVarDumpOptionsDefault ) {
            $this->stateVarDumpOptionsDefault = [
                static::VAR_DUMP_OPT_ARRAY_INDENT     => '',
                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 1,
                static::VAR_DUMP_OPT_ARRAY_NEWLINE    => ' ',
                static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                static::VAR_DUMP_OPT_WITH_BRACES      => null,
                static::VAR_DUMP_OPT_WITH_ID          => null,
                static::VAR_DUMP_OPT_WITH_TYPE        => null,
                static::VAR_DUMP_OPT_WITH_VALUE       => null,
            ];
        }

        return $isChange ? $last : $this->stateVarDumpOptionsDefault;
    }


    /**
     * @var DebugDumperInterface
     */
    protected $dumper;
    /**
     * @var DebugThrowablerInterface
     */
    protected $throwabler;
    /**
     * @var DebugBacktracerInterface
     */
    protected $backtracer;


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    public function newBacktracer() : DebugBacktracerInterface
    {
        $instance = new DefaultDebugBacktracer();

        return $instance;
    }

    public function cloneBacktracer() : DebugBacktracerInterface
    {
        return clone $this->backtracer();
    }

    public function backtracer(?DebugBacktracerInterface $backtracer = null) : DebugBacktracerInterface
    {
        return $this->backtracer = null
            ?? $backtracer
            ?? $this->backtracer
            ?? $this->newBacktracer();
    }


    public function newDumper() : DebugDumperInterface
    {
        $instance = new DefaultDebugDumper();

        return $instance;
    }

    public function cloneDumper() : DebugDumperInterface
    {
        return clone $this->dumper();
    }

    public function dumper(?DebugDumperInterface $dumper = null) : DebugDumperInterface
    {
        return $this->dumper = null
            ?? $dumper
            ?? $this->dumper
            ?? $this->newDumper();
    }


    public function newThrowabler() : DebugThrowablerInterface
    {
        $instance = new DefaultDebugThrowabler();

        return $instance;
    }

    public function cloneThrowabler() : DebugThrowablerInterface
    {
        return clone $this->throwabler();
    }

    public function throwabler(?DebugThrowablerInterface $throwabler = null) : DebugThrowablerInterface
    {
        return $this->throwabler = null
            ?? $throwabler
            ?? $this->throwabler
            ?? $this->newThrowabler();
    }


    public function debug_backtrace(
        ?int $options = -1,
        ?int $limit = -1
    ) : DebugBacktracerInterface
    {
        $backtracer = $this->cloneBacktracer();

        if ( (null === $options) || ($options >= 0) ) {
            $backtracer->options($options);
        }

        if ( (null === $limit) || ($limit >= 0) ) {
            $backtracer->limit($limit);
        }

        return $backtracer;
    }


    public function file_for_trace(?string $file, ?string $dirRoot = null) : string
    {
        if ( null === $file ) {
            return static::FILE_DEFAULT;
        }

        if ( '' === $file ) {
            return static::FILE_DEFAULT;
        }

        $dirRootString = $dirRoot ?? Lib::debug()->stateDirRoot() ?? '';

        $fileString = str_replace('\\', '/', $file);

        if ( '' === $dirRootString ) {
            return $fileString;
        }

        $dirRootString = str_replace('\\', '/', $dirRootString);

        $fileString = str_replace([ $dirRootString . '/', $dirRootString ], '', $fileString);

        return $fileString;
    }

    public function line_for_trace(?int $line)
    {
        if ( null === $line ) {
            return static::LINE_DEFAULT;
        }

        if ( $line < 1 ) {
            return static::LINE_DEFAULT;
        }

        return $line;
    }


    /**
     * @return string|float|int|null
     */
    public function var_export($var, ?array $options = null, ?int $level = null)
    {
        $options = $options ?? [];
        $level = $level ?? 0;

        $options = $this->_var_export_options(
            null,
            $options,
            null
        );

        $optWithAddcslashes = $options[static::VAR_EXPORT_OPT_WITH_ADDCSLASHES];
        $optIndent = $options[static::VAR_EXPORT_OPT_INDENT];
        $optNewline = $options[static::VAR_EXPORT_OPT_NEWLINE];

        $withAddcslashes = null
            ?? $optWithAddcslashes
            ?? true;

        $indent = null
            ?? $optIndent
            ?? "  ";

        $newline = null
            ?? $optNewline
            ?? "\n";

        switch ( gettype($var) ) {
            case "NULL":
                $result = "NULL";

                break;

            case "boolean":
                $result = ($var === true)
                    ? "TRUE"
                    : "FALSE";

                break;

            case "integer":
            case "double":
                $result = $var;

                break;

            case "string":
                $result = $withAddcslashes
                    ? addcslashes($var, "\\\$\"\r\n\t\v\f")
                    : $var;

                $result = "\"{$result}\"";

                break;

            case "array":
                if ( [] === $var ) {
                    $result = "[]";

                } else {
                    $isShort = (array_keys($var) === range(0, count($var) - 1));

                    $lines = [];
                    foreach ( $var as $key => $vvar ) {
                        $rowIndent = str_repeat($indent, $level + 1);

                        $keyString = '';
                        if ( ! $isShort ) {
                            $keyString = is_string($key)
                                ? "\"{$key}\" => "
                                : "{$key} => ";
                        }

                        if ( [] === $vvar ) {
                            $vvarString = '[]';

                        } else {
                            // > ! recursion
                            $vvarString = $this->var_export(
                                $vvar,
                                $options, $level + 1
                            );
                        }

                        $line = ''
                            . $rowIndent
                            . $keyString
                            . $vvarString;

                        $lines[] = $line;
                    }

                    $arrayEndIndent = str_repeat($indent, $level);

                    $result = implode("," . $newline, $lines);

                    $result = implode($newline, [
                        "[",
                        $result,
                        $arrayEndIndent . "]",
                    ]);
                }

                break;

            case "object":
                if ( $var instanceof \stdClass ) {
                    $vvar = get_object_vars($var);

                    // > ! recursion
                    $result = $this->var_export(
                        $vvar,
                        $options, $level,
                    );

                    $result = "(object) {$result}";

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

    protected function _var_export_options(
        ?array $optionsForce = null,
        ?array $options = null,
        ?array $optionsDefault = null
    ) : array
    {
        $theType = Lib::type();

        $optionsTotal = []
            + ($optionsForce ?? [])
            + ($options ?? [])
            + ($optionsDefault ?? [])
            + $this->stateVarExportOptionsDefault();

        if ( null !== ($var =& $optionsTotal[static::VAR_EXPORT_OPT_WITH_ADDCSLASHES]) ) $var = $theType->bool($var)->orThrow();
        if ( null !== ($var =& $optionsTotal[static::VAR_EXPORT_OPT_INDENT]) ) $var = $theType->string($var)->orThrow();
        if ( null !== ($var =& $optionsTotal[static::VAR_EXPORT_OPT_NEWLINE]) ) $var = $theType->string($var)->orThrow();

        return $optionsTotal;
    }


    public function var_dump($var, ?array $options = null, ?array &$refContext = null) : string
    {
        $options = $options ?? [];
        $refContext = $refContext ?? [];

        $options = $this->_var_dump_options(
            null,
            $options,
            [
                static::VAR_DUMP_OPT_ARRAY_INDENT     => '',
                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 1,
                static::VAR_DUMP_OPT_ARRAY_NEWLINE    => ' ',
                static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                static::VAR_DUMP_OPT_WITH_BRACES      => null,
                static::VAR_DUMP_OPT_WITH_ID          => true,
                static::VAR_DUMP_OPT_WITH_TYPE        => true,
                static::VAR_DUMP_OPT_WITH_VALUE       => true,
            ]
        );

        $optWithType = $options[static::VAR_DUMP_OPT_WITH_TYPE];
        $optWithId = $options[static::VAR_DUMP_OPT_WITH_ID];
        $optWithValue = $options[static::VAR_DUMP_OPT_WITH_VALUE];
        $optWithBraces = $options[static::VAR_DUMP_OPT_WITH_BRACES];

        $output = $this->var_dump_output(
            $var,
            $options,
            $refContext
        );

        $content = [];

        $forceBraces = false;

        $printableType = '';
        if ( array_key_exists('type', $output) ) {
            $printableType .= $output['type'];
        }
        if ( array_key_exists('subtype', $output) ) {
            $printableType .= '(' . $output['subtype'] . ')';
        }

        $withId = null
            ?? $optWithId
            ?? false;

        if ( $withId ) {
            if ( array_key_exists('class_original', $output) ) {
                $printableType .= ' # ' . $output['class_original'];
            }
            if ( array_key_exists('id', $output) ) {
                $printableType .= ' @' . $output['id'];
            }

        } else {
            if ( array_key_exists('class', $output) ) {
                $printableType .= ' # ' . $output['class'];
            }
        }

        $withType = null
            ?? $optWithType
            ?? false;

        if ( $withType ) {
            $content[] = $printableType;
        }

        $withValue = null
            ?? $optWithValue
            ?? true;

        if ( $withValue ) {
            if ( array_key_exists('value', $output) ) {
                $content[] = $output['value'];

            } else {
                if ( ! $optWithType ) {
                    $forceBraces = true;

                    $content[] = $printableType;
                }
            }
        }

        $content = implode(' # ', $content);

        $withBraces = null
            ?? $optWithBraces
            ?? ($optWithId ? true : null) // > only if true
            ?? ($optWithType ? true : null) // > only if true
            ?? $forceBraces
            ?? false;

        if ( $withBraces ) {
            $content = '{ ' . $content . ' }';
        }

        return $content;
    }

    protected function _var_dump_options(
        ?array $optionsForce = null,
        ?array $options = null,
        ?array $optionsDefault = null
    ) : array
    {
        $theType = Lib::type();

        $optionsTotal = []
            + ($optionsForce ?? [])
            + ($options ?? [])
            + ($optionsDefault ?? [])
            + $this->stateVarDumpOptionsDefault();

        if ( null !== ($var =& $optionsTotal[static::VAR_DUMP_OPT_ARRAY_INDENT]) ) $var = $theType->string($var)->orThrow();
        if ( null !== ($var =& $optionsTotal[static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX]) ) $var = $theType->int_non_negative($var)->orThrow();
        if ( null !== ($var =& $optionsTotal[static::VAR_DUMP_OPT_ARRAY_NEWLINE]) ) $var = $theType->string($var)->orThrow();
        if ( null !== ($var =& $optionsTotal[static::VAR_DUMP_OPT_MULTILINE_ESCAPE]) ) $var = $theType->string($var)->orThrow();
        if ( null !== ($var =& $optionsTotal[static::VAR_DUMP_OPT_WITH_BRACES]) ) $var = $theType->bool($var)->orThrow();
        if ( null !== ($var =& $optionsTotal[static::VAR_DUMP_OPT_WITH_ID]) ) $var = $theType->bool($var)->orThrow();
        if ( null !== ($var =& $optionsTotal[static::VAR_DUMP_OPT_WITH_TYPE]) ) $var = $theType->bool($var)->orThrow();
        if ( null !== ($var =& $optionsTotal[static::VAR_DUMP_OPT_WITH_VALUE]) ) $var = $theType->bool($var)->orThrow();

        return $optionsTotal;
    }


    protected function var_dump_output($var, array $options = [], ?array &$refContext = []) : array
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
        if ( ! is_null($var) ) return null;

        $output = [];
        $output['type'] = gettype($var);
        $output['value'] = strtoupper(var_export($var, true));

        return $output;
    }

    protected function var_dump_output_bool($var, array $options = [], array &$refContext = []) : ?array
    {
        if ( ! is_bool($var) ) return null;

        $output = [];
        $output['type'] = gettype($var);
        $output['value'] = strtoupper(var_export($var, true));

        return $output;
    }

    protected function var_dump_output_int($var, array $options = [], array &$refContext = []) : ?array
    {
        if ( ! is_int($var) ) return null;

        $map = [
            ' ' . PHP_INT_MIN => ((string) PHP_INT_MIN),
        ];

        $varString = (string) $var;

        $output = [];
        $output['type'] = gettype($var);
        $output['value'] = null
            ?? $map[' ' . $varString]
            ?? var_export($var, true);

        return $output;
    }

    protected function var_dump_output_float($var, array $options = [], array &$refContext = []) : ?array
    {
        if ( ! is_float($var) ) return null;

        $output = [];
        $output['type'] = gettype($var);
        $output['value'] = var_export($var, true);

        return $output;
    }

    protected function var_dump_output_string($var, array $options = [], array &$refContext = []) : ?array
    {
        if ( ! is_string($var) ) return null;

        $theStr = Lib::str();

        $optWithValue = $options[static::VAR_DUMP_OPT_WITH_VALUE];

        $phpType = gettype($var);
        $phpStrlen = strlen($var);

        $printableValue = [];

        $withValue = null
            ?? $optWithValue
            ?? true;

        if ( $withValue ) {
            $printableValue = str_replace('"', '\"', $var);
            $printableValue = $theStr->dump_encode($printableValue);
            $printableValue = '"' . $printableValue . '"';
            $printableValue = [ $printableValue ];
        }

        $output = [];
        $output['type'] = "{$phpType}({$phpStrlen})";

        if ( $withValue ) {
            if ( [] !== $printableValue ) {
                $output['value'] = $printableValue[0];
            }
        }

        return $output;
    }

    protected function var_dump_output_object($var, array $options = [], array &$refContext = []) : ?array
    {
        if ( ! is_object($var) ) return null;

        $theDate = Lib::date();

        $optWithValue = $options[static::VAR_DUMP_OPT_WITH_VALUE];

        $phpType = gettype($var);

        $objectClassOriginal = get_class($var);
        $objectClass = $objectClassOriginal;
        $objectId = spl_object_id($var);

        if ( false !== ($pos = strpos($objectClass, $needle = '@anonymous')) ) {
            $objectClass = substr($objectClass, 0, $pos + strlen($needle));
        }

        $objectSubtypeCountable = (($var instanceof \Countable) ? 'countable(' . count($var) . ')' : null);
        $objectSubtypeInvokable = (method_exists($var, '__invoke') ? 'invokable' : null);
        $objectSubtypeIterable = (is_iterable($var) ? 'iterable' : null);
        $objectSubtypeSerializable = ((($var instanceof \Serializable) || method_exists($var, '__serialize')) ? 'serializable' : null);
        $objectSubtypeStringable = (method_exists($var, '__toString') ? 'stringable' : null);

        $objectSubtype = [];
        if ( $objectSubtypeCountable ) $objectSubtype[] = $objectSubtypeCountable;
        if ( $objectSubtypeInvokable ) $objectSubtype[] = $objectSubtypeInvokable;
        if ( $objectSubtypeIterable ) $objectSubtype[] = $objectSubtypeIterable;
        if ( $objectSubtypeSerializable ) $objectSubtype[] = $objectSubtypeSerializable;
        if ( $objectSubtypeStringable ) $objectSubtype[] = $objectSubtypeStringable;

        $withValue = null
            ?? $optWithValue
            ?? false;

        $printableValue = [];
        if ( $withValue ) {
            if ( $var instanceof \DateTimeInterface ) {
                $printableValue = [ '"' . $var->format('Y-m-d\TH:i:s.uP') . '"' ];

            } elseif ( $var instanceof \DateTimeZone ) {
                $printableValue = [ '"' . $var->getName() . '"' ];

            } elseif ( $var instanceof \DateInterval ) {
                $printableValue = [ '"' . $theDate->interval_encode($var) . '"' ];

            } elseif ( $var instanceof \Throwable ) {
                $printableValue = [ '"' . $var->getMessage() . '"' ];
            }
        }

        $output = [];
        $output['type'] = $phpType;
        if ( $objectSubtype ) {
            $objectSubtype = implode(' ', $objectSubtype);

            $output['subtype'] = $objectSubtype;
        }
        $output['class_original'] = $objectClassOriginal;
        $output['class'] = $objectClass;
        $output['id'] = $objectId;

        if ( $withValue ) {
            if ( [] !== $printableValue ) {
                $output['value'] = $printableValue[0];
            }
        }

        return $output;
    }

    protected function var_dump_output_array($var, array $options = [], array &$refContext = []) : ?array
    {
        if ( ! is_array($var) ) return null;

        $theArr = Lib::arr();
        $theType = Lib::type();

        $optWithValue = $options[static::VAR_DUMP_OPT_WITH_VALUE];

        $optArrayIndent = $options[static::VAR_DUMP_OPT_ARRAY_INDENT];
        $optArrayLevelMax = $options[static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX];
        $optArrayNewline = $options[static::VAR_DUMP_OPT_ARRAY_NEWLINE];

        $arrayLevelMax = $optArrayLevelMax ?? 0;
        $arrayLevelMax = (int) $arrayLevelMax;
        if ( $arrayLevelMax < 0 ) {
            $arrayLevelMax = 0;
        }

        $phpType = gettype($var);

        $arrayCopy = $var;
        $arrayCount = count($var);

        $withValue = null
            ?? $optWithValue
            ?? true;

        $printableValue = [];
        if ( $withValue ) {
            $gen = $theArr->walk_it(
                $arrayCopy,
                _ARR_WALK_WITH_EMPTY_ARRAYS | _ARR_WALK_WITH_PARENTS
            );

            foreach ( $gen as $path => &$value ) {
                if ( false
                    || $theType->object($value)->isOk()
                    || $theType->resource($value)->isOk()
                ) {
                    $optionsCurrent = []
                        + [
                            static::VAR_DUMP_OPT_WITH_TYPE       => true,
                            static::VAR_DUMP_OPT_WITH_ID         => false,
                            static::VAR_DUMP_OPT_WITH_VALUE      => false,
                            //
                            static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX => 0,
                        ]
                        + $options;

                    // > ! recursion
                    $value = $this->var_dump(
                        $value,
                        $optionsCurrent
                    );

                    continue;
                }

                $shouldVarDumpInsteadOfVarExport = (count($path) >= $arrayLevelMax);

                if ( $shouldVarDumpInsteadOfVarExport ) {
                    if ( is_string($value) ) {
                        $optionsCurrent = []
                            + [
                                static::VAR_DUMP_OPT_WITH_TYPE       => false,
                                static::VAR_DUMP_OPT_WITH_ID         => false,
                                static::VAR_DUMP_OPT_WITH_VALUE      => true,
                                //
                                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX => 0,
                            ]
                            + $options;

                        // > ! recursion
                        $value = $this->var_dump(
                            $value,
                            $optionsCurrent
                        );

                        $value = substr($value, 1, -1);

                        continue;
                    }

                    if ( is_array($value) ) {
                        if ( [] !== $value ) {
                            $optionsCurrent = []
                                + [
                                    static::VAR_DUMP_OPT_WITH_TYPE       => true,
                                    static::VAR_DUMP_OPT_WITH_ID         => false,
                                    static::VAR_DUMP_OPT_WITH_VALUE      => false,
                                    //
                                    static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX => 0,
                                ]
                                + $options;

                            // > ! recursion
                            $value = $this->var_dump(
                                $value,
                                $optionsCurrent
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
                    static::VAR_EXPORT_OPT_WITH_ADDCSLASHES => false,
                    static::VAR_EXPORT_OPT_INDENT           => $optArrayIndent,
                    static::VAR_EXPORT_OPT_NEWLINE          => $optArrayNewline,
                ]
            );

            $printableValue = [ $printableValue ];
        }

        $output = [];
        $output['type'] = "{$phpType}({$arrayCount})";

        if ( $withValue ) {
            if ( [] !== $printableValue ) {
                $output['value'] = $printableValue[0];
            }
        }

        return $output;
    }

    protected function var_dump_output_resource($var, array $options = [], array &$refContext = []) : ?array
    {
        $isResourceOpened = (is_resource($var));
        $isResourceClosed = ('resource (closed)' === gettype($var));
        if ( ! ($isResourceOpened || $isResourceClosed) ) {
            return null;
        }

        $optWithValue = $options[static::VAR_DUMP_OPT_WITH_VALUE];

        $phpType = 'resource';

        $resourceType = $isResourceOpened
            ? 'opened'
            : 'closed';

        $withValue = null
            ?? $optWithValue
            ?? true;

        $printableValue = [];
        if ( $withValue ) {
            $printableValue = $isResourceOpened
                ? [ get_resource_type($var) ]
                : [];
        }

        $resourceId = (PHP_VERSION_ID > 80000)
            ? get_resource_id($var)
            : (int) $var;

        $output = [];
        $output['type'] = $phpType;
        $output['subtype'] = $resourceType;
        $output['id'] = $resourceId;

        if ( $withValue ) {
            if ( [] !== $printableValue ) {
                $output['value'] = $printableValue[0];
            }
        }

        return $output;
    }


    public function print_type($value, ?array $options = null, ?array &$refContext = null) : string
    {
        $options = $options ?? [];
        $refContext = $refContext ?? [];

        $options = $this->_var_dump_options(
            [
                static::VAR_DUMP_OPT_WITH_TYPE     => true,
                static::VAR_DUMP_OPT_WITH_ID       => false,
                static::VAR_DUMP_OPT_WITH_VALUE    => false,
                static::VAR_DUMP_OPT_ARRAY_INDENT  => '',
                static::VAR_DUMP_OPT_ARRAY_NEWLINE => ' ',
            ],
            $options,
            [
                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 0,
                static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                static::VAR_DUMP_OPT_WITH_BRACES      => true,
            ]
        );

        $optWithBraces = $options[static::VAR_DUMP_OPT_WITH_BRACES];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
        );

        $printableType = '';
        if ( array_key_exists('type', $output) ) {
            $printableType .= $output['type'];
        }
        if ( array_key_exists('subtype', $output) ) {
            $printableType .= '(' . $output['subtype'] . ')';
        }
        if ( array_key_exists('class', $output) ) {
            $printableType .= ' # ' . $output['class'];
        }

        $withBraces = null
            ?? $optWithBraces
            ?? true;

        if ( $withBraces ) {
            $printableType = '{ ' . $printableType . ' }';
        }

        $content = $printableType;

        return $content;
    }

    public function print_all_type(array $values, ?string $delimiter = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $delimiter = $delimiter ?? ' | ';

        $theType = Lib::type();

        $delimiterString = $theType->string_not_empty($delimiter)->orThrow();

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->print_type($value, $options, $refContext);
        }

        $content = implode($delimiterString, $list);

        return $content;
    }


    public function print_type_id($value, ?array $options = null, ?array &$refContext = null) : string
    {
        $options = $options ?? [];
        $refContext = $refContext ?? [];

        $options = $this->_var_dump_options(
            [
                static::VAR_DUMP_OPT_WITH_TYPE     => true,
                static::VAR_DUMP_OPT_WITH_ID       => true,
                static::VAR_DUMP_OPT_WITH_VALUE    => false,
                static::VAR_DUMP_OPT_ARRAY_INDENT  => '',
                static::VAR_DUMP_OPT_ARRAY_NEWLINE => ' ',
            ],
            $options,
            [
                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 0,
                static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                static::VAR_DUMP_OPT_WITH_BRACES      => true,
            ]
        );

        $optWithBraces = $options[static::VAR_DUMP_OPT_WITH_BRACES];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
        );

        $printableType = '';
        if ( array_key_exists('type', $output) ) {
            $printableType .= $output['type'];
        }
        if ( array_key_exists('subtype', $output) ) {
            $printableType .= '(' . $output['subtype'] . ')';
        }
        if ( array_key_exists('class_original', $output) ) {
            $printableType .= ' # ' . $output['class_original'];
        }
        if ( array_key_exists('id', $output) ) {
            $printableType .= ' &' . $output['id'];
        }

        $withBraces = null
            ?? $optWithBraces
            ?? true;

        if ( $withBraces ) {
            $printableType = '{ ' . $printableType . ' }';
        }

        $content = $printableType;

        return $content;
    }

    public function print_all_type_id(array $values, ?string $delimiter = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $delimiter = $delimiter ?? ' | ';

        $theType = Lib::type();

        $delimiterString = $theType->string_not_empty($delimiter)->orThrow();

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->print_type_id($value, $options, $refContext);
        }

        $content = implode($delimiterString, $list);

        return $content;
    }


    public function print_value($value, ?array $options = null, ?array &$refContext = null) : string
    {
        $options = $options ?? [];
        $refContext = $refContext ?? [];

        $options = $this->_var_dump_options(
            [
                static::VAR_DUMP_OPT_WITH_TYPE     => false,
                static::VAR_DUMP_OPT_WITH_ID       => false,
                static::VAR_DUMP_OPT_WITH_VALUE    => true,
                static::VAR_DUMP_OPT_ARRAY_INDENT  => '',
                static::VAR_DUMP_OPT_ARRAY_NEWLINE => ' ',
            ],
            $options,
            [
                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 0,
                static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                static::VAR_DUMP_OPT_WITH_BRACES      => null,
            ]
        );

        $optWithBraces = $options[static::VAR_DUMP_OPT_WITH_BRACES];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
        );

        $forceBraces = false;

        $hasValue = array_key_exists('value', $output);

        $isObject = is_object($value);
        $isResource = is_resource($value) || ('resource (closed)' === gettype($value));

        $printableType = '';
        $printableValue = '';

        if ( false
            || ($isObject || $isResource)
            || (! $hasValue)
        ) {
            $forceBraces = true;

            if ( array_key_exists('type', $output) ) {
                $printableType .= $output['type'];
            }
            if ( array_key_exists('subtype', $output) ) {
                $printableType .= '(' . $output['subtype'] . ')';
            }
            if ( array_key_exists('class', $output) ) {
                $printableType .= ' # ' . $output['class'];
            }
        }

        if ( $hasValue ) {
            $printableValue = $output['value'];
        }

        $withBraces = null
            ?? $optWithBraces
            ?? $forceBraces
            ?? false;

        $content = [];
        if ( '' !== $printableType ) $content[] = $printableType;
        if ( '' !== $printableValue ) $content[] = $printableValue;
        $content = implode(' # ', $content);

        if ( $withBraces ) {
            $content = '{ ' . $content . ' }';
        }

        return $content;
    }

    public function print_all_value(array $values, ?string $delimiter = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $delimiter = $delimiter ?? ' | ';

        $theType = Lib::type();

        $delimiterString = $theType->string_not_empty($delimiter)->orThrow();

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->print_value($value, $options, $refContext);
        }

        $content = implode($delimiterString, $list);

        return $content;
    }


    public function print_type_value($value, ?array $options = null, ?array &$refContext = null) : string
    {
        $options = $options ?? [];
        $refContext = $refContext ?? [];

        $options = $this->_var_dump_options(
            [
                static::VAR_DUMP_OPT_WITH_TYPE     => true,
                static::VAR_DUMP_OPT_WITH_ID       => false,
                static::VAR_DUMP_OPT_WITH_VALUE    => true,
                static::VAR_DUMP_OPT_ARRAY_INDENT  => '',
                static::VAR_DUMP_OPT_ARRAY_NEWLINE => ' ',
            ],
            $options,
            [
                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 0,
                static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                static::VAR_DUMP_OPT_WITH_BRACES      => true,
            ]
        );

        $optWithBraces = $options[static::VAR_DUMP_OPT_WITH_BRACES];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
        );

        $forceBraces = false;

        $printableType = '';
        $printableValue = '';

        if ( array_key_exists('type', $output) ) {
            $printableType .= $output['type'];
        }
        if ( array_key_exists('subtype', $output) ) {
            $printableType .= '(' . $output['subtype'] . ')';
        }
        if ( array_key_exists('class', $output) ) {
            $printableType .= ' # ' . $output['class'];
        }

        if ( array_key_exists('value', $output) ) {
            $printableValue = $output['value'];
        }

        $withBraces = null
            ?? $optWithBraces
            ?? $forceBraces
            ?? false;

        $content = [];
        if ( '' !== $printableType ) $content[] = $printableType;
        if ( '' !== $printableValue ) $content[] = $printableValue;
        $content = implode(' # ', $content);

        if ( $withBraces ) {
            $content = '{ ' . $content . ' }';
        }

        return $content;
    }

    public function print_all_type_value(array $values, ?string $delimiter = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $delimiter = $delimiter ?? ' | ';

        $theType = Lib::type();

        $delimiterString = $theType->string_not_empty($delimiter)->orThrow();

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->print_type_value($value, $options, $refContext);
        }

        $content = implode($delimiterString, $list);

        return $content;
    }


    public function print_type_id_value($value, ?array $options = null, ?array &$refContext = null) : string
    {
        $options = $options ?? [];
        $refContext = $refContext ?? [];

        $options = $this->_var_dump_options(
            [
                static::VAR_DUMP_OPT_WITH_TYPE     => true,
                static::VAR_DUMP_OPT_WITH_ID       => true,
                static::VAR_DUMP_OPT_WITH_VALUE    => true,
                static::VAR_DUMP_OPT_ARRAY_INDENT  => '',
                static::VAR_DUMP_OPT_ARRAY_NEWLINE => ' ',
            ],
            $options,
            [
                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 0,
                static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                static::VAR_DUMP_OPT_WITH_BRACES      => true,
            ]
        );

        $optWithBraces = $options[static::VAR_DUMP_OPT_WITH_BRACES];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
        );

        $forceBraces = false;

        $printableType = '';
        $printableValue = '';

        if ( array_key_exists('type', $output) ) {
            $printableType .= $output['type'];
        }
        if ( array_key_exists('subtype', $output) ) {
            $printableType .= '(' . $output['subtype'] . ')';
        }
        if ( array_key_exists('class_original', $output) ) {
            $printableType .= ' # ' . $output['class_original'];
        }
        if ( array_key_exists('id', $output) ) {
            $printableType .= ' &' . $output['id'];
        }

        if ( array_key_exists('value', $output) ) {
            $printableValue = $output['value'];
        }

        $withBraces = null
            ?? $optWithBraces
            ?? $forceBraces
            ?? false;

        $content = [];
        if ( '' !== $printableType ) $content[] = $printableType;
        if ( '' !== $printableValue ) $content[] = $printableValue;
        $content = implode(' # ', $content);

        if ( $withBraces ) {
            $content = '{ ' . $content . ' }';
        }

        return $content;
    }

    public function print_all_type_id_value(array $values, ?string $delimiter = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $delimiter = $delimiter ?? ' | ';

        $theType = Lib::type();

        $delimiterString = $theType->string_not_empty($delimiter)->orThrow();

        $list = [];
        foreach ( $values as $value ) {
            $list[] = $this->print_type_id_value($value, $options, $refContext);
        }

        $content = implode($delimiterString, $list);

        return $content;
    }


    public function print_value_multiline($value, ?array $options = null, ?array &$refContext = null) : string
    {
        $options = $options ?? [];
        $refContext = $refContext ?? [];

        $options = $this->_var_dump_options(
            [
                static::VAR_DUMP_OPT_WITH_TYPE     => false,
                static::VAR_DUMP_OPT_WITH_ID       => false,
                static::VAR_DUMP_OPT_WITH_VALUE    => true,
                static::VAR_DUMP_OPT_ARRAY_INDENT  => '  ',
                static::VAR_DUMP_OPT_ARRAY_NEWLINE => "\n",
            ],
            $options,
            [
                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 0,
                static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                static::VAR_DUMP_OPT_WITH_BRACES      => null,
            ]
        );

        $optWithBraces = $options[static::VAR_DUMP_OPT_WITH_BRACES];
        $optMultilineEscape = $options[static::VAR_DUMP_OPT_MULTILINE_ESCAPE];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
        );

        $forceBraces = false;

        $hasValue = array_key_exists('value', $output);

        $isObject = is_object($value);
        $isResource = is_resource($value) || ('resource (closed)' === gettype($value));

        $printableType = '';
        $printableValue = '';

        if ( false
            || ($isObject || $isResource)
            || (! $hasValue)
        ) {
            $forceBraces = true;

            if ( array_key_exists('type', $output) ) {
                $printableType .= $output['type'];
            }
            if ( array_key_exists('subtype', $output) ) {
                $printableType .= '(' . $output['subtype'] . ')';
            }
            if ( array_key_exists('class', $output) ) {
                $printableType .= ' # ' . $output['class'];
            }
        }

        if ( $hasValue ) {
            $printableValue = $output['value'];
        }

        $withBraces = null
            ?? $optWithBraces
            ?? $forceBraces
            ?? false;

        if ( $withBraces ) {
            $printableType = '{ ' . $printableType . ' }';
        }

        $content = [];
        if ( '' !== $printableType ) $content[] = $printableType;
        if ( '' !== $printableValue ) $content[] = $printableValue;
        $content = implode("\n", $content);

        $content = implode("\n", [
            $optMultilineEscape,
            $content,
            $optMultilineEscape,
        ]);

        return $content;
    }

    public function print_type_value_multiline($value, ?array $options = null, ?array &$refContext = null) : string
    {
        $options = $options ?? [];
        $refContext = $refContext ?? [];

        $options = $this->_var_dump_options(
            [
                static::VAR_DUMP_OPT_WITH_TYPE     => true,
                static::VAR_DUMP_OPT_WITH_ID       => false,
                static::VAR_DUMP_OPT_WITH_VALUE    => true,
                static::VAR_DUMP_OPT_ARRAY_INDENT  => '  ',
                static::VAR_DUMP_OPT_ARRAY_NEWLINE => "\n",
            ],
            $options,
            [
                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 0,
                static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                static::VAR_DUMP_OPT_WITH_BRACES      => true,
            ]
        );

        $optWithBraces = $options[static::VAR_DUMP_OPT_WITH_BRACES];
        $optMultilineEscape = $options[static::VAR_DUMP_OPT_MULTILINE_ESCAPE];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
        );

        $forceBraces = false;

        $printableType = '';
        $printableValue = '';

        if ( array_key_exists('type', $output) ) {
            $printableType .= $output['type'];
        }
        if ( array_key_exists('subtype', $output) ) {
            $printableType .= '(' . $output['subtype'] . ')';
        }
        if ( array_key_exists('class', $output) ) {
            $printableType .= ' # ' . $output['class'];
        }

        if ( array_key_exists('value', $output) ) {
            $printableValue = $output['value'];
        }

        $withBraces = null
            ?? $optWithBraces
            ?? $forceBraces
            ?? false;

        if ( $withBraces ) {
            $printableType = '{ ' . $printableType . ' }';
        }

        $content = [];
        if ( '' !== $printableType ) $content[] = $printableType;
        if ( '' !== $printableValue ) $content[] = $printableValue;
        $content = implode("\n", $content);

        $content = implode("\n", [
            $optMultilineEscape,
            $content,
            $optMultilineEscape,
        ]);

        return $content;
    }

    public function print_type_id_value_multiline($value, ?array $options = null, ?array &$refContext = null) : string
    {
        $options = $options ?? [];
        $refContext = $refContext ?? [];

        $options = $this->_var_dump_options(
            [
                static::VAR_DUMP_OPT_WITH_TYPE     => true,
                static::VAR_DUMP_OPT_WITH_ID       => true,
                static::VAR_DUMP_OPT_WITH_VALUE    => true,
                static::VAR_DUMP_OPT_ARRAY_INDENT  => '  ',
                static::VAR_DUMP_OPT_ARRAY_NEWLINE => "\n",
            ],
            $options,
            [
                static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX  => 0,
                static::VAR_DUMP_OPT_MULTILINE_ESCAPE => '###',
                static::VAR_DUMP_OPT_WITH_BRACES      => true,
            ]
        );

        $optWithBraces = $options[static::VAR_DUMP_OPT_WITH_BRACES];
        $optMultilineEscape = $options[static::VAR_DUMP_OPT_MULTILINE_ESCAPE];

        $output = $this->var_dump_output(
            $value,
            $options,
            $refContext
        );

        $forceBraces = false;

        $printableType = '';
        $printableValue = '';

        if ( array_key_exists('type', $output) ) {
            $printableType .= $output['type'];
        }
        if ( array_key_exists('subtype', $output) ) {
            $printableType .= '(' . $output['subtype'] . ')';
        }
        if ( array_key_exists('class_original', $output) ) {
            $printableType .= ' # ' . $output['class_original'];
        }
        if ( array_key_exists('id', $output) ) {
            $printableType .= ' &' . $output['id'];
        }

        if ( array_key_exists('value', $output) ) {
            $printableValue = $output['value'];
        }

        $withBraces = null
            ?? $optWithBraces
            ?? $forceBraces
            ?? false;

        if ( $withBraces ) {
            $printableType = '{ ' . $printableType . ' }';
        }

        $content = [];
        if ( '' !== $printableType ) $content[] = $printableType;
        if ( '' !== $printableValue ) $content[] = $printableValue;
        $content = implode("\n", $content);

        $content = implode("\n", [
            $optMultilineEscape,
            $content,
            $optMultilineEscape,
        ]);

        return $content;
    }


    public function print_array(array $value, ?int $levelMax = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $levelMax = $levelMax ?? 1;

        $options[static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX] = $levelMax;

        $content = $this->print_value($value, $options, $refContext);

        return $content;
    }

    public function print_type_array(array $value, ?int $levelMax = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $levelMax = $levelMax ?? 1;

        $options[static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX] = $levelMax;

        $content = $this->print_type_value($value, $options, $refContext);

        return $content;
    }

    public function print_type_id_array(array $value, ?int $levelMax = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $levelMax = $levelMax ?? 1;

        $options[static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX] = $levelMax;

        $content = $this->print_type_id_value($value, $options, $refContext);

        return $content;
    }


    public function print_array_multiline(array $value, ?int $levelMax = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $levelMax = $levelMax ?? 1;

        $options[static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX] = $levelMax;

        $content = $this->print_value_multiline($value, $options, $refContext);

        return $content;
    }

    public function print_type_array_multiline(array $value, ?int $levelMax = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $levelMax = $levelMax ?? 1;

        $options[static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX] = $levelMax;

        $content = $this->print_type_value_multiline($value, $options, $refContext);

        return $content;
    }

    public function print_type_id_array_multiline(array $value, ?int $levelMax = null, ?array $options = null, ?array &$refContext = null) : string
    {
        $levelMax = $levelMax ?? 1;

        $options[static::VAR_DUMP_OPT_ARRAY_LEVEL_MAX] = $levelMax;

        $content = $this->print_type_id_value_multiline($value, $options, $refContext);

        return $content;
    }


    public function print_var_export($value, ?array $options = null, ?int $level = null) : string
    {
        $content = implode("\n", [
            '###',
            $this->var_export($value, $options, $level),
            '###',
        ]);

        return $content;
    }


    public function print_table(array $table) : string
    {
        if ( [] === $table ) {
            return '';
        }

        $rowKeys = [];
        foreach ( $table as $key => $devnull ) {
            $rowKeys[$key] = true;
        }

        $colKeys = [];
        foreach ( $rowKeys as $rowKey => $bool ) {
            if ( ! is_array($table[$rowKey]) ) {
                throw new RuntimeException(
                    [ 'The `table` should be array of arrays', $table[$rowKey] ]
                );
            }

            foreach ( $table[$rowKey] as $colKey => $devnull ) {
                if ( ! isset($colKeys[$colKey]) ) {
                    $colKeys[$colKey] = true;
                }
            }
        }

        $list = array_keys($rowKeys);
        $thWidth = max(
            array_map('strlen', $list)
        );

        $list = array_keys($colKeys);
        $tdWidths = array_combine(
            $list,
            array_map('strlen', $list)
        );

        foreach ( $table as $row ) {
            foreach ( $row as $colKey => $colValue ) {
                $tdWidths[$colKey] = max(
                    $tdWidths[$colKey] ?? 0,
                    strlen((string) $colValue)
                );
            }
        }

        ob_start();

        $fnDrawLine = static function () use ($thWidth, $tdWidths) {
            echo '+';
            echo str_repeat('-', $thWidth + 2);
            echo '+';

            foreach ( $tdWidths as $tdWidth ) {
                // echo '';
                echo str_repeat('-', $tdWidth + 2);
                echo '+';
            }

            echo "\n";
        };

        call_user_func($fnDrawLine);

        echo '| ';
        echo str_pad('', $thWidth);
        echo ' |';

        foreach ( $colKeys as $colKey => $bool ) {
            echo ' ';
            echo str_pad($colKey, $tdWidths[$colKey]);
            echo ' |';
        }

        echo "\n";

        call_user_func($fnDrawLine);

        foreach ( $table as $rowKey => $row ) {
            echo '| ';
            echo str_pad($rowKey, $thWidth);
            echo ' |';

            foreach ( $colKeys as $colKey => $bool ) {
                echo ' ';
                echo str_pad(
                    $row[$colKey] ?? 'NULL',
                    $tdWidths[$colKey]
                );
                echo ' |';
            }

            echo "\n";
        }

        call_user_func($fnDrawLine);

        $content = ob_get_clean();

        return $content;
    }


    public function dump_type($value, $options = null, &$refContext = null) : void
    {
        echo $this->print_type($value, $options, $refContext);
        echo "\n";
    }

    public function dump_all_type($values, $delimiter = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_all_type($values, $delimiter, $options, $refContext);
        echo "\n";
    }


    public function dump_type_id($value, $options = null, &$refContext = null) : void
    {
        echo $this->print_type_id($value, $options, $refContext);
        echo "\n";
    }

    public function dump_all_type_id($values, $delimiter = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_all_type_id($values, $delimiter, $options, $refContext);
        echo "\n";
    }


    public function dump_value($value, $options = null, &$refContext = null) : void
    {
        echo $this->print_value($value, $options, $refContext);
        echo "\n";
    }

    public function dump_all_value($values, $delimiter = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_all_value($values, $delimiter, $options, $refContext);
        echo "\n";
    }


    public function dump_type_value($value, $options = null, &$refContext = null) : void
    {
        echo $this->print_type_value($value, $options, $refContext);
        echo "\n";
    }

    public function dump_all_type_value($values, $delimiter = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_all_type_value($values, $delimiter, $options, $refContext);
        echo "\n";
    }


    public function dump_type_id_value($value, $options = null, &$refContext = null) : void
    {
        echo $this->print_type_id_value($value, $options, $refContext);
        echo "\n";
    }

    public function dump_all_type_id_value($values, $delimiter = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_all_type_id_value($values, $delimiter, $options, $refContext);
        echo "\n";
    }


    public function dump_value_multiline($value, $options = null, &$refContext = null) : void
    {
        echo $this->print_value_multiline($value, $options, $refContext);
        echo "\n";
    }

    public function dump_type_value_multiline($value, $options = null, &$refContext = null) : void
    {
        echo $this->print_type_value_multiline($value, $options, $refContext);
        echo "\n";
    }

    public function dump_type_id_value_multiline($value, $options = null, &$refContext = null) : void
    {
        echo $this->print_type_id_value_multiline($value, $options, $refContext);
        echo "\n";
    }


    public function dump_array($value, $levelMax = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_array($value, $levelMax, $options, $refContext);
        echo "\n";
    }

    public function dump_type_array($value, $levelMax = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_type_array($value, $levelMax, $options, $refContext);
        echo "\n";
    }

    public function dump_type_id_array($value, $levelMax = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_type_id_array($value, $levelMax, $options, $refContext);
        echo "\n";
    }


    public function dump_array_multiline($value, $levelMax = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_array_multiline($value, $levelMax, $options, $refContext);
        echo "\n";
    }

    public function dump_type_array_multiline($value, $levelMax = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_type_array_multiline($value, $levelMax, $options, $refContext);
        echo "\n";
    }

    public function dump_type_id_array_multiline($value, $levelMax = null, $options = null, &$refContext = null) : void
    {
        echo $this->print_type_id_array_multiline($value, $levelMax, $options, $refContext);
        echo "\n";
    }


    public function dump_var_export($value, $options = null, $level = null) : void
    {
        echo $this->print_var_export($value, $options, $level);
        echo "\n";
    }


    public function dump_table($table) : void
    {
        echo $this->print_table($table);
        echo "\n";
    }


    public function diff(
        string $new, string $old,
        array $refs = []
    ) : bool
    {
        $theStr = Lib::str();

        $withDiffLines = array_key_exists(0, $refs);
        if ( $withDiffLines ) {
            $refDiffLines =& $refs[0];
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
                $matrix[$iOld][$iNew] = 0;
            }
        }

        for ( $iOld = 1; $iOld <= $oldCnt; $iOld++ ) {
            for ( $iNew = 1; $iNew <= $newCnt; $iNew++ ) {
                if ( $oldLines[$iOld - 1] === $newLines[$iNew - 1] ) {
                    $matrix[$iOld][$iNew] = $matrix[$iOld - 1][$iNew - 1] + 1;

                } else {
                    $matrix[$iOld][$iNew] = max(
                        $matrix[$iOld - 1][$iNew],
                        $matrix[$iOld][$iNew - 1]
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

            if ( ! ($iOldGt0 || $iNewGt0) ) {
                break;
            }

            if ( true
                && $iOldGt0
                && $iNewGt0
                && ($oldLines[$iOld - 1] === $newLines[$iNew - 1])
            ) {
                $line = $oldLines[$iOld - 1];
                $line = ('' === $line) ? '~' : $line;
                // $line = $line;

                $lineNumber = $iOld;

                $diffLines[] = [ $line, $lineNumber, null ];

                $iNew--;
                $iOld--;

            } elseif ( true
                && $iOldGt0
                && (false
                    || $iNewEq0
                    || ($matrix[$iOld][$iNew - 1] < $matrix[$iOld - 1][$iNew])
                )
            ) {
                $line = $oldLines[$iOld - 1];
                $line = ('' === $line) ? '~' : $line;
                $line = '--- > ' . $line;

                $lineNumber = $iOld;

                $diffLines[] = [ $line, $lineNumber, false ];

                $iOld--;

                $isDiff = true;

            } elseif ( true
                && $iNewGt0
                && (false
                    || $iOldEq0
                    || ($matrix[$iOld][$iNew - 1] >= $matrix[$iOld - 1][$iNew])
                )
            ) {
                $line = $newLines[$iNew - 1];
                $line = ('' === $line) ? '~' : $line;
                $line = '+++ > ' . $line;

                $lineNumber = $iNew;

                $diffLines[] = [ $line, $lineNumber, true ];

                $iNew--;

                $isDiff = true;
            }
        }

        if ( $diffLines ) {
            foreach ( $diffLines as $i => [$line, $lineNumber, $isLineDiff] ) {
                $lineNumber = str_pad(
                    $lineNumber,
                    $maxCntLen, ' ', STR_PAD_LEFT
                );

                $line = (null === $isLineDiff)
                    ? $line
                    : "[ {$lineNumber} ] {$line}";

                $diffLines[$i] = $line;
            }

            $diffLines = array_reverse($diffLines);
        }

        if ( $withDiffLines ) {
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


    public function printerPrint(...$vars) : string
    {
        return $this->dumper()->printerPrint(...$vars);
    }

    public function dumperDump(...$vars) : void
    {
        $this->dumper()->dumperDump(...$vars);
    }


    public function dp($var, ...$vars) : string
    {
        $fileLine = Lib::file_line();

        return $this->dumper()->dp($fileLine, $var, ...$vars);
    }

    public function fnDP(?int $traceShift = null, ?array $trace = null) : \Closure
    {
        return $this->dumper()->fnDP($traceShift, $trace);
    }


    /**
     * @return mixed
     */
    public function d($var, ...$vars)
    {
        $fileLine = Lib::file_line();

        return $this->dumper()->d($fileLine, $var, ...$vars);
    }

    /**
     * @return mixed|void
     */
    public function dd(...$vars)
    {
        $fileLine = Lib::file_line();

        return $this->dumper()->dd($fileLine, ...$vars);
    }

    public function fnD(?int $traceShift = null, ?array $trace = null) : \Closure
    {
        return $this->dumper()->fnD($traceShift, $trace);
    }

    public function fnDD(?int $traceShift = null, ?array $trace = null) : \Closure
    {
        return $this->dumper()->fnDD($traceShift, $trace);
    }


    /**
     * @return mixed|void
     */
    public function td(int $throttleMs, $var, ...$vars)
    {
        $fileLine = Lib::file_line();

        return $this->dumper()->td($fileLine, $throttleMs, $var, ...$vars);
    }

    public function fnTD(?int $traceShift = null, ?array $trace = null) : \Closure
    {
        return $this->dumper()->fnTd($traceShift, $trace);
    }


    /**
     * @return mixed|void
     */
    public function zd(?int $zTimes, $var, ...$vars)
    {
        $fileLine = Lib::file_line();

        return $this->dumper()->zd($fileLine, $zTimes, $var, ...$vars);
    }

    public function fnZD(?int $traceShift = null, ?array $trace = null) : \Closure
    {
        return $this->dumper()->fnZD($traceShift, $trace);
    }
}
