<?php

namespace Gzhegow\Lib\Exception;

trait ExceptionTrait
{
    /**
     * @var \Throwable
     */
    public $previous;

    public function _getPrevious() : ?\Throwable
    {
        if (null === $this->previous) {
            return $this->getPrevious();
        }

        return $this->previous;
    }


    /**
     * @var array
     */
    public $trace;

    public function _getTrace() : array
    {
        if (null === $this->trace) {
            return $this->getTrace();
        }

        return $this->trace;
    }

    public function _getTraceAsString() : string
    {
        if (null === $this->trace) {
            return $this->getTraceAsString();
        }

        $rtn = "";
        $count = 0;
        foreach ( $this->trace as $frame ) {
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


    /**
     * @var \Throwable[]
     */
    protected $previousList = [];

    /**
     * @return \Throwable[]
     */
    public function getPreviousList() : array
    {
        return $this->previousList;
    }

    /**
     * @return static
     */
    public function setPreviousList(array $previousList)
    {
        $this->previousList = [];

        foreach ( $previousList as $previous ) {
            $this->addPrevious($previous);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function addPrevious(\Throwable $previous) // : static
    {
        $this->previousList[] = $previous;

        return $this;
    }
}
