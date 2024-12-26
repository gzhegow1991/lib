<?php

namespace Gzhegow\Lib\Exception;

class ErrorHandler
{
    /**
     * @var int
     */
    protected $errorReporting;
    /**
     * @var callable
     */
    protected $errorHandler;
    /**
     * @var callable
     */
    protected $exceptionHandler;


    public function __construct()
    {
        $this->errorReporting = E_ALL;
        $this->errorHandler = [ static::class, 'error_handler' ];
        $this->exceptionHandler = [ static::class, 'exception_handler' ];
    }


    /**
     * @param int $errorReporting
     *
     * @return static
     */
    public function setErrorReporting(int $errorReporting)
    {
        $this->errorReporting = $errorReporting;

        return $this;
    }

    /**
     * @param callable $fn
     *
     * @return static
     */
    public function setErrorHandler($fn)
    {
        $this->errorHandler = $fn;

        return $this;
    }

    /**
     * @param callable $fn
     *
     * @return static
     */
    public function setExceptionHandler($fn)
    {
        $this->exceptionHandler = $fn;

        return $this;
    }


    /**
     * @return static
     */
    public function useErrorReporting() // : static
    {
        $last = error_reporting($this->errorReporting);

        return $this;
    }

    /**
     * @return static
     */
    public function useErrorHandler() // : static
    {
        $last = set_error_handler($this->errorHandler);

        return $this;
    }

    /**
     * @return static
     */
    public function useExceptionHandler() // : static
    {
        $last = set_exception_handler($this->exceptionHandler);

        return $this;
    }


    /**
     * @throws \ErrorException
     */
    public static function error_handler($errno, $errstr, $errfile, $errline) : void
    {
        if (error_reporting() & $errno) {
            throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
        }
    }

    public static function exception_handler(\Throwable $e) : void
    {
        $current = $e;
        do {
            echo "\n";

            $gettype = gettype($current);
            $getClass = get_class($current);
            $splObjectId = spl_object_id($current);
            echo "{ {$gettype} # {$getClass} # {$splObjectId} }" . PHP_EOL;

            echo $current->getMessage() . PHP_EOL;

            $file = $current->getFile() ?? '{file}';
            $line = $current->getLine() ?? '{line}';
            echo "{$file} : {$line}" . PHP_EOL;

            foreach ( $e->getTrace() as $traceItem ) {
                $file = $traceItem[ 'file' ] ?? '{file}';
                $line = $traceItem[ 'line' ] ?? '{line}';

                echo "{$file} : {$line}" . PHP_EOL;
            }

            echo PHP_EOL;
        } while ( $current = $current->getPrevious() );

        die();
    }
}
