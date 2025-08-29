<?php

namespace Gzhegow\Lib\Exception\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\Interfaces\HasTraceOverrideInterface;


/**
 * @mixin \Throwable
 *
 * @see HasTraceOverrideInterface
 */
trait HasTraceOverrideTrait
{
    /**
     * @var array
     */
    protected $trace;


    /**
     * @return static
     */
    public function setFile(?string $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return static
     */
    public function setLine(?int $line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * @return static
     */
    public function setTrace(?array $trace)
    {
        $this->trace = $trace;

        return $this;
    }


    public function getFileOverride(?string $dirRoot = null) : string
    {
        $theFs = Lib::fs();

        $dirRoot = $dirRoot ?? Lib::debug()->staticDirRoot();

        $file = $this->file ?? $this->getFile();

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
     * @return int
     */
    public function getLineOverride() : int
    {
        $line = $this->line ?? $this->getLine();

        return $line;
    }


    public function getTraceOverride(?string $dirRoot = null) : array
    {
        $theFs = Lib::fs();

        $dirRoot = $dirRoot ?? Lib::debug()->staticDirRoot();

        $trace = $this->trace ?? $this->getTrace();

        if ( null !== $dirRoot ) {
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

        return $trace;
    }

    public function getTraceAsStringOverride(?string $dirRoot = null) : string
    {
        $theType = Lib::type();

        $dirRoot = $dirRoot ?? Lib::debug()->staticDirRoot();

        if ( null === $this->trace ) {
            $traceAsString = $this->getTraceAsString();

            if ( null !== $dirRoot ) {
                $fileRootRealpath = $theType->realpath($dirRoot)->orThrow();

                $traceAsString = str_replace($fileRootRealpath . DIRECTORY_SEPARATOR, '', $traceAsString);
            }

            return $traceAsString;
        }

        $rtn = "";
        $count = 0;
        foreach ( $this->getTraceOverride($dirRoot) as $frame ) {
            $args = "";

            if ( isset($frame['args']) ) {
                $args = [];

                foreach ( $frame['args'] as $arg ) {
                    if ( is_string($arg) ) {
                        $args[] = "'" . $arg . "'";

                    } elseif ( is_array($arg) ) {
                        $args[] = "Array";

                    } elseif ( is_null($arg) ) {
                        $args[] = 'NULL';

                    } elseif ( is_bool($arg) ) {
                        $args[] = ($arg) ? "true" : "false";

                    } elseif ( is_object($arg) ) {
                        $args[] = get_class($arg);

                    } elseif ( $theType->resource($arg)->isOk([ &$ref ]) ) {
                        $args[] = get_resource_type($ref);

                    } else {
                        $args[] = $arg;
                    }
                }

                $args = join(", ", $args);
            }

            $rtn .= sprintf(
                "#%s %s(%s): %s%s%s(%s)\n",
                $count,
                $frame['file'],
                $frame['line'],
                $frame['class'] ?? '',
                $frame['type'] ?? '', // "->" or "::"
                $frame['function'],
                $args
            );

            $count++;
        }

        return $rtn;
    }
}
