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


    public function hasFile() : bool
    {
        return null !== $this->file;
    }

    public function getFile() : ?string
    {
        return $this->file;
    }

    /**
     * @return static
     */
    public function setFile(?string $file)
    {
        $this->file = $file;

        return $this;
    }


    public function hasLine() : bool
    {
        return null !== $this->line;
    }

    public function getLine() : ?int
    {
        return $this->line;
    }

    public function setLine(?int $line)
    {
        $this->line = $line;

        return $this;
    }


    public function hasTrace() : bool
    {
        return null !== $this->trace;
    }

    public function getTrace() : array
    {
        return $this->trace ?? [];
    }

    public function getTraceAsString() : string
    {
        $traceAsString = "";

        $trace = $this->getTrace();

        if ( [] === $trace ) {
            $traceAsString = "#0 {main}";

        } else {
            $index = 0;
            foreach ( $this->getTrace() as $frame ) {
                $args = "";

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
                    $frame['file'] ?? '{file}', // > filename
                    $frame['line'] ?? -1,
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

    /**
     * @return static
     */
    public function setTrace(?array $trace)
    {
        $this->trace = $trace;

        return $this;
    }
}
