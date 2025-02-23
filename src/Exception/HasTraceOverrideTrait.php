<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


/**
 * @mixin \Throwable
 *
 * @mixin HasTraceOverrideInterface
 */
trait HasTraceOverrideTrait
{
    /**
     * @var string
     */
    public $file;
    /**
     * @var int|string
     */
    public $line;

    /**
     * @var array
     */
    public $trace;


    public function getFileOverride(string $fileRoot = null) : string
    {
        $file = $this->file ?? $this->getFile();

        if (null !== $fileRoot) {
            $file = Lib::fs()->relative($file, $fileRoot);
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


    public function getTraceOverride(string $fileRoot = null) : array
    {
        $trace = $this->trace ?? $this->getTrace();

        if (null !== $fileRoot) {
            $theFs = Lib::fs();

            foreach ( $trace as $i => $frame ) {
                if (isset($frame[ 'file' ])) {
                    $trace[ $i ][ 'file' ] = $theFs->relative($frame[ 'file' ], $fileRoot);
                }
            }
        }

        return $trace;
    }

    public function getTraceAsStringOverride(string $fileRoot = null) : string
    {
        if (null === $this->trace) {
            $traceAsString = $this->getTraceAsString();

            if (null !== $fileRoot) {
                $fileRootRealpath = realpath($fileRoot) ?: null;

                $traceAsString = str_replace($fileRootRealpath . DIRECTORY_SEPARATOR, '', $traceAsString);
            }

            return $traceAsString;
        }

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

                    } elseif (is_resource($arg)) {
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
