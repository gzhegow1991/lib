<?php

namespace Gzhegow\Lib\Exception;

/**
 * @mixin \Throwable
 */
trait ThrowableTrait
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
     * @var string
     */
    public $message;
    /**
     * @var int
     */
    public $code;

    /**
     * @var array
     */
    public $trace;

    /**
     * @var \Throwable
     */
    public $previous;
    /**
     * @var \Throwable[]
     */
    public $previousList = [];


    public function _getFile() : string
    {
        return $this->file;
    }

    public function _getLine() : int
    {
        return $this->line;
    }


    public function _getPrevious() : \Throwable
    {
        if (null === $this->previous) {
            return $this->getPrevious();
        }

        return $this->previous;
    }


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
        foreach ( $this->_getTrace() as $frame ) {
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
     * @return \Throwable[]
     */
    public function _getPreviousList() : array
    {
        return $this->previousList;
    }

    /**
     * @return static
     */
    public function _setPreviousList(array $previousList)
    {
        $this->previousList = [];

        foreach ( $previousList as $previous ) {
            $this->_addPreviousToList($previous);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function _addPreviousToList(\Throwable $previous) // : static
    {
        $this->previousList[] = $previous;

        return $this;
    }
}
