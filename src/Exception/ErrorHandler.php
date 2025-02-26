<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


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
    public function useErrorReporting(&$last = null) // : static
    {
        $last = error_reporting($this->errorReporting);

        return $this;
    }

    /**
     * @param callable|null $last
     *
     * @return static
     */
    public function useErrorHandler(&$last = null) // : static
    {
        $last = set_error_handler($this->errorHandler);

        return $this;
    }

    /**
     * @param callable|null $last
     *
     * @return static
     */
    public function useExceptionHandler(&$last = null) // : static
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

        $shouldConvertToUtf8 = extension_loaded('mbstring');
        $shouldConvertFromCp1251 = (PHP_VERSION_ID < 80100);

        $theMb = null;
        $thePhp = null;
        if ($shouldConvertToUtf8) {
            $theMb = Lib::mb();
            $thePhp = Lib::php();

            $shouldConvertToUtf8 &= $thePhp->is_windows();
        }

        $it = Lib::new8(ExceptionIterator::class, [ $throwable ]);
        $iit = new \RecursiveIteratorIterator($it);

        $messageLines = [];
        foreach ( $iit as $track ) {
            foreach ( $track as $i => $e ) {
                $phpClass = get_class($e);
                $phpId = spl_object_id($e);
                $phpFile = $e->getFile() ?? '{file}';
                $phpLine = $e->getLine() ?? 0;
                $phpMessage = $e->getMessage() ?? '{message}';

                if ($shouldConvertToUtf8) {
                    if (! $theMb->is_utf8($phpMessage)) {
                        if (true
                            && $shouldConvertFromCp1251
                            && $e instanceof \PDOException
                        ) {
                            $phpMessage = $theMb->convert_encoding(
                                $phpMessage,
                                'UTF-8',
                                'CP1251'
                            );

                        } else {
                            $mbEncodingList = mb_list_encodings();
                            array_unshift($mbEncodingList, 'CP1251');

                            $phpMessage = $theMb->convert_encoding(
                                $phpMessage,
                                'UTF-8',
                                $mbEncodingList
                            );
                        }
                    }
                }

                $messageLines[] = "[ {$i} ] {$phpMessage}";
                $messageLines[] = "{ object # {$phpClass} # {$phpId} } ";
                $messageLines[] = "{$phpFile} : {$phpLine}";
            }
        }

        $traceLines = [];
        foreach ( $throwable->getTrace() as $traceItem ) {
            $phpFile = $traceItem[ 'file' ] ?? '{file}';
            $phpLine = $traceItem[ 'line' ] ?? 0;

            $traceLines[] = "{$phpFile} : {$phpLine}";
        }

        foreach ( $messageLines as $line ) {
            echo $line . PHP_EOL;
        }

        echo PHP_EOL;

        if (count($traceLines)) {
            echo 'Trace: ' . PHP_EOL;

            foreach ( $traceLines as $line ) {
                echo $line . PHP_EOL;
            }
        }
    }
}
