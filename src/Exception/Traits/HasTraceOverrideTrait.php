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

        $file = $this->fileOverride ?? $this->getFile();

        if ( null !== $dirRoot ) {
            if ( '{{file}}' !== $file ) {
                $theFs = Lib::fs();

                $file = $theFs->path_relative(
                    $file,
                    $dirRoot,
                    '/'
                );
            }
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
        $trace = $this->prepareTraceOverride($dirRoot);

        return $trace;
    }

    public function getTraceAsStringOverride(?string $dirRoot = null) : string
    {
        if ( null === $dirRoot ) {
            $theDebug = Lib::debug();

            $dirRoot = $theDebug::staticDirRoot();
        }

        $trace = $this->traceOverride;
        $traceAsString = '';

        if ( null === $trace ) {
            $traceAsString = $this->getTraceAsString();

            if ( null !== $dirRoot ) {
                $theType = Lib::type();

                $dirRootRealpath = $theType->realpath($dirRoot)->orThrow();

                $traceAsString = str_replace(
                    $dirRootRealpath . DIRECTORY_SEPARATOR,
                    '',
                    $traceAsString
                );
            }

        } else {
            if ( [] === $trace ) {
                $traceAsString = "#0 {main}";

            } else {
                $index = 0;
                foreach ( $trace as $frame ) {
                    $args = "";

                    $file = $frame['file'] ?? '{{file}}';
                    $line = $frame['line'] ?? -1;

                    if ( null !== $dirRoot ) {
                        if ( '{{file}}' !== $file ) {
                            $theFs = $theFs ?? Lib::fs();

                            $file = $theFs->path_relative(
                                $file,
                                $dirRoot,
                                '/'
                            );
                        }
                    }

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


    protected function prepareTraceOverride(?string $dirRoot = null) : array
    {
        if ( null === $dirRoot ) {
            $theDebug = Lib::debug();

            $dirRoot = $theDebug::staticDirRoot();
        }

        $trace = $this->traceOverride;

        if ( null === $trace ) {
            $trace = $this->getTrace();

            if ( null !== $dirRoot ) {
                foreach ( $trace as $i => $frame ) {
                    $file = $frame['file'] ?? '{{file}}';

                    if ( '{{file}}' !== $file ) {
                        $theFs = $theFs ?? Lib::fs();

                        $file = $theFs->path_relative(
                            $file,
                            $dirRoot,
                            '/'
                        );
                    }

                    $trace[$i]['file'] = $file;
                }
            }
        }

        return $trace;
    }
}
