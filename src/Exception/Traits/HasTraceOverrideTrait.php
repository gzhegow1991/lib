<?php

namespace Gzhegow\Lib\Exception\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\Interfaces\HasTraceOverrideInterface;


/**
 * @mixin \Throwable
 *
 * @mixin HasTraceOverrideInterface
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
    public function setFile(string $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return static
     */
    public function setLine(int $line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * @return static
     */
    public function setTrace(array $trace)
    {
        $this->trace = $trace;

        return $this;
    }


    public function getFileOverride(?string $fileRoot = null) : string
    {
        $file = $this->file ?? $this->getFile();

        if (null !== $fileRoot) {
            $file = Lib::php()->relative($file, $fileRoot);
        }

        return $file;
    }

    /**
     * @return int|string
     */
    public function getLineOverride()
    {
        /** @var int|string $line */

        $line = $this->line ?? $this->getLine();

        return $line;
    }


    public function getTraceOverride(?string $fileRoot = null) : array
    {
        $trace = $this->trace ?? $this->getTrace();

        if (null !== $fileRoot) {
            $thePhp = Lib::php();

            foreach ( $trace as $i => $frame ) {
                if (! isset($frame[ 'file' ])) {
                    continue;
                }

                $trace[ $i ][ 'file' ] = $thePhp->relative($frame[ 'file' ], $fileRoot);
            }
        }

        return $trace;
    }

    public function getTraceAsStringOverride(?string $fileRoot = null) : string
    {
        if (null === $this->trace) {
            $traceAsString = $this->getTraceAsString();

            if (null !== $fileRoot) {
                $fileRootRealpath = realpath($fileRoot) ?: null;

                $traceAsString = str_replace($fileRootRealpath . DIRECTORY_SEPARATOR, '', $traceAsString);
            }

            return $traceAsString;
        }

        $theType = Lib::type();

        $rtn = "";
        $count = 0;
        foreach ( $this->getTraceOverride($fileRoot) as $frame ) {
            $args = "";

            if (isset($frame[ 'args' ])) {
                $args = [];

                foreach ( $frame[ 'args' ] as $arg ) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";

                    } elseif (is_array($arg)) {
                        $args[] = "Array";

                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';

                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";

                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);

                    } elseif ($theType->resource($var, $arg)) {
                        $args[] = get_resource_type($arg);

                    } else {
                        $args[] = $arg;
                    }
                }

                $args = join(", ", $args);
            }

            $rtn .= sprintf(
                "#%s %s(%s): %s%s%s(%s)\n",
                $count,
                $frame[ 'file' ],
                $frame[ 'line' ],
                $frame[ 'class' ] ?? '',
                $frame[ 'type' ] ?? '', // "->" or "::"
                $frame[ 'function' ],
                $args
            );

            $count++;
        }

        return $rtn;
    }
}
