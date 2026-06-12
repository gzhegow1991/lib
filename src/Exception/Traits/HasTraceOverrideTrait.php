<?php

namespace Gzhegow\Lib\Exception\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\Interfaces\HasTraceOverrideInterface;


/**
 * @see HasTraceOverrideInterface
 */
trait HasTraceOverrideTrait
{
    /**
     * @var string
     */
    protected $fileOverride;
    /**
     * @var int
     */
    protected $lineOverride;
    /**
     * @var array
     */
    protected $traceOverride;


    public function hasFileOverride() : bool
    {
        return null !== $this->fileOverride;
    }

    public function getFileOverride(?string $dirRoot = null) : ?string
    {
        $file = $this->fileOverride;

        if ( null !== $file ) {
            $theDebug = Lib::debug();

            $file = $theDebug->file_for_trace($file, $dirRoot);
        }

        return $file;
    }

    /**
     * @return static
     */
    public function setFileOverride(?string $file)
    {
        $this->fileOverride = $file;

        return $this;
    }


    public function hasLineOverride() : bool
    {
        return null !== $this->lineOverride;
    }

    /**
     * @return int
     */
    public function getLineOverride() : ?int
    {
        $line = $this->lineOverride;

        return $line;
    }

    /**
     * @return static
     */
    public function setLineOverride(?int $line)
    {
        $this->lineOverride = $line;

        return $this;
    }


    public function hasTraceOverride() : bool
    {
        return null !== $this->traceOverride;
    }

    public function getTraceOverride(?string $dirRoot = null) : ?array
    {
        $trace = $this->traceOverride;

        if ( null !== $trace ) {
            $theDebug = Lib::debug();

            foreach ( $trace as $i => $t ) {
                $trace[$i]['file'] = $theDebug->file_for_trace($t['file'] ?? null, $dirRoot);
                $trace[$i]['line'] = $theDebug->line_for_trace($t['line'] ?? null);
            }
        }

        return $trace;
    }

    public function getTraceAsStringOverride(?string $dirRoot = null) : ?string
    {
        $trace = $this->traceOverride;
        $traceAsString = null;

        if ( null !== $trace ) {
            if ( [] === $trace ) {
                $traceAsString = "#0 {main}";

            } else {
                $theDebug = Lib::debug();

                $index = 0;
                foreach ( $trace as $t ) {
                    $tArgs = "";

                    $tFile = $theDebug->file_for_trace($t['file'] ?? null, $dirRoot);
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
        }

        return $traceAsString;
    }

    /**
     * @return static
     */
    public function setTraceOverride(?array $trace)
    {
        $this->traceOverride = $trace;

        return $this;
    }
}
