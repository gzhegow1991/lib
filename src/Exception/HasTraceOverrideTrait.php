<?php

namespace Gzhegow\Lib\Exception;

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
     * @var int
     */
    public $line;

    /**
     * @var array
     */
    public $trace;


    public function getFileOverride() : string
    {
        return $this->file;
    }

    public function getLineOverride() : int
    {
        return $this->line;
    }


    public function getTraceOverride() : array
    {
        if (null === $this->trace) {
            return $this->getTrace();
        }

        return $this->trace;
    }

    public function getTraceAsStringOverride() : string
    {
        if (null === $this->trace) {
            return $this->getTraceAsString();
        }

        $rtn = "";
        $count = 0;
        foreach ( $this->getTraceOverride() as $frame ) {
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
