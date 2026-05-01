<?php

namespace Gzhegow\Lib\Exception\Traits;


/**
 * @see HasTraceInterface
 */
trait HasTraceTrait
{
    /**
     * @var string
     */
    protected $file;
    /**
     * @var int
     */
    protected $line;
    /**
     * @var array
     */
    protected $trace;


    public function getFile() : string
    {
        $file = $this->file ?: '{{file}}';

        return $file;
    }

    public function getLine() : int
    {
        $line = $this->line ?: -1;

        return $line;
    }


    public function getTrace() : array
    {
        $trace = $this->trace ?? [];

        return $trace;
    }

    public function getTraceAsString() : string
    {
        $traceAsString = "";

        $trace = $this->getTrace();

        if ( [] === $trace ) {
            $traceAsString = "#0 {main}";

        } else {
            $index = 0;
            foreach ( $trace as $frame ) {
                $args = "";

                $file = (($frame['file'] ?? null) ?: '{{file}}');
                $line = (($frame['line'] ?? null) ?: -1);

                if ( isset($frame['args']) ) {
                    $args = [];

                    foreach ( $frame['args'] as $arg ) {
                        if ( is_null($arg) ) {
                            $args[] = '{ NULL }';

                        } elseif ( is_bool($arg) ) {
                            $args[] = ($arg) ? "{ TRUE }" : "{ FALSE }";

                        } elseif ( is_string($arg) ) {
                            $args[] = '"' . $arg . '"';

                        } elseif ( is_array($arg) ) {
                            $args[] = "{ array(" . count($arg) . ") }";

                        } elseif ( is_object($arg) ) {
                            $args[] = get_class($arg);

                        } elseif ( false
                            || is_resource($arg)
                            || ('resource (closed)' === gettype($arg))
                        ) {
                            $args[] = get_resource_type($arg);

                        } else {
                            $args[] = $arg;
                        }
                    }

                    $args = join(", ", $args);
                }

                $traceAsString .= sprintf(
                    "#%s %s(%s): %s%s%s(%s)\n",
                    //
                    $index,
                    //
                    $file,
                    $line,
                    //
                    $frame['class'] ?? '', // > className
                    $frame['type'] ?? '',  // > "->" or "::"
                    $frame['function'],    // > function_name
                    //
                    $args
                );

                $index++;
            }
        }

        return $traceAsString;
    }
}
