<?php

namespace Gzhegow\Lib\Exception\Traits;


use Gzhegow\Lib\Lib;


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
        $theDebug = Lib::debug();

        $file = $theDebug->file_for_trace($this->file);

        return $file;
    }

    public function getLine() : int
    {
        $theDebug = Lib::debug();

        $line = $theDebug->line_for_trace($this->line);

        return $line;
    }


    public function getTrace() : array
    {
        $trace = $this->trace ?? [];

        if ( [] !== $trace ) {
            $theDebug = Lib::debug();

            foreach ( $trace as $i => $t ) {
                $trace[$i]['file'] = $theDebug->file_for_trace($t['file'] ?? null);
                $trace[$i]['line'] = $theDebug->line_for_trace($t['line'] ?? null);
            }
        }

        return $trace;
    }

    public function getTraceAsString() : string
    {
        $traceAsString = "";

        $trace = $this->getTrace();

        if ( [] === $trace ) {
            $traceAsString = "#0 {main}";

        } else {
            $theDebug = Lib::debug();

            $index = 0;
            foreach ( $trace as $t ) {
                $tArgs = "";

                $tFile = $theDebug->file_for_trace($t['file'] ?? null);
                $tLine = $theDebug->line_for_trace($t['line'] ?? null);

                if ( isset($t['args']) ) {
                    $tArgs = [];

                    foreach ( $t['args'] as $arg ) {
                        if ( is_null($arg) ) {
                            $tArgs[] = '{ NULL }';

                        } elseif ( is_bool($arg) ) {
                            $tArgs[] = ($arg) ? "{ TRUE }" : "{ FALSE }";

                        } elseif ( is_string($arg) ) {
                            $tArgs[] = '"' . $arg . '"';

                        } elseif ( is_array($arg) ) {
                            $tArgs[] = "{ array(" . count($arg) . ") }";

                        } elseif ( is_object($arg) ) {
                            $tArgs[] = get_class($arg);

                        } elseif ( false
                            || is_resource($arg)
                            || ('resource (closed)' === gettype($arg))
                        ) {
                            $tArgs[] = get_resource_type($arg);

                        } else {
                            $tArgs[] = $arg;
                        }
                    }

                    $tArgs = join(", ", $tArgs);
                }

                $traceAsString .= sprintf(
                    "#%s %s(%s): %s%s%s(%s)\n",
                    //
                    $index,
                    //
                    $tFile,
                    $tLine,
                    //
                    $t['class'] ?? '', // > className
                    $t['type'] ?? '',  // > "->" or "::"
                    $t['function'],    // > function_name
                    //
                    $tArgs
                );

                $index++;
            }
        }

        return $traceAsString;
    }
}
