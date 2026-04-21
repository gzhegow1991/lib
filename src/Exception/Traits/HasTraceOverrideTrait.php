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

    public function getFileOverride(?string $dirRoot = null) : string
    {
        if ( null === $dirRoot ) {
            $theDebug = Lib::debug();

            $dirRoot = $theDebug::staticDirRoot();
        }

        $theFs = Lib::fs();

        $file = $this->fileOverride ?? $this->getFile();

        if ( null !== $dirRoot ) {
            $file = $theFs->path_relative(
                $file,
                $dirRoot,
                '/'
            );
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
    public function getLineOverride() : int
    {
        $line = $this->lineOverride ?? $this->getLine();

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

    public function getTraceOverride(?string $dirRoot = null) : array
    {
        $this->prepareTraceOverride($dirRoot);

        return $this->traceOverride;
    }

    public function getTraceAsStringOverride(?string $dirRoot = null) : string
    {
        if ( null === $dirRoot ) {
            $theDebug = Lib::debug();

            $dirRoot = $theDebug::staticDirRoot();
        }

        if ( null === $this->traceOverride ) {
            $traceAsString = $this->getTraceAsString();

            if ( null !== $dirRoot ) {
                $fileRootRealpath = realpath($dirRoot);
                if ( false === $fileRootRealpath ) {
                    throw new \LogicException('The `dirRoot` should be realpath: ' . $dirRoot);
                }

                $traceAsString = str_replace(
                    $fileRootRealpath . DIRECTORY_SEPARATOR,
                    '',
                    $traceAsString
                );
            }

        } else {
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
                        $frame['file'] ?? '{{file}}', // > filename
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


    protected function prepareTraceOverride(?string $dirRoot = null) : void
    {
        if ( null === $dirRoot ) {
            $theDebug = Lib::debug();

            $dirRoot = $theDebug::staticDirRoot();
        }

        if ( null === $this->traceOverride ) {
            $trace = $this->getTrace();

            if ( null !== $dirRoot ) {
                $theFs = Lib::fs();

                foreach ( $trace as $i => $frame ) {
                    if ( ! isset($frame['file']) ) {
                        continue;
                    }

                    $trace[$i]['file'] = $theFs->path_relative(
                        $frame['file'],
                        $dirRoot,
                        '/'
                    );
                }
            }

            $this->traceOverride = $trace;
        }
    }
}
