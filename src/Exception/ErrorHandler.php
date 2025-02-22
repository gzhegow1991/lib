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

    public static function exception_handler(\Throwable $throwable, bool $exit = null) : void
    {
        $exit = $exit ?? true;

        $it = new ExceptionIterator([ $throwable ]);
        $iit = new \RecursiveIteratorIterator($it);

        $messageLines = [];
        foreach ( $iit as $track ) {
            foreach ( $track as $i => $e ) {
                $phpClass = get_class($e);
                $phpId = spl_object_id($e);
                $phpFile = $e->getFile() ?? '{file}';
                $phpLine = $e->getLine() ?? '{line}';

                $messageLines[] = "[ {$i} ] {$e->getMessage()}";
                $messageLines[] = "{ object # {$phpClass} # {$phpId} } ";
                $messageLines[] = "{$phpFile} : {$phpLine}";
                $messageLines[] = '';
            }
        }

        $traceLines = [];
        foreach ( $throwable->getTrace() as $traceItem ) {
            $phpFile = $traceItem[ 'file' ] ?? '{file}';
            $phpLine = $traceItem[ 'line' ] ?? '{line}';

            $traceLines[] = "{$phpFile} : {$phpLine}";
        }

        foreach ( $messageLines as $line ) {
            echo $line . PHP_EOL;
        }

        echo PHP_EOL;

        foreach ( $traceLines as $line ) {
            echo $line . PHP_EOL;
        }
    }
}
