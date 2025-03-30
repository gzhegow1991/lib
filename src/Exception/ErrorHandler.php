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

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        $shouldTryConvertToUtf8 = true
            && $isWindows
            && extension_loaded('mbstring');

        $shouldTryConvertFromCp1251 = (PHP_VERSION_ID < 80100);

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

                if ($shouldTryConvertToUtf8) {
                    $isUtf8 = (1 === preg_match('//u', $phpMessage));

                    if (! $isUtf8) {
                        if ($shouldTryConvertFromCp1251) {
                            // > gzhegow, 2025-02-26, case is happened only with \PDOException
                            if ($e instanceof \PDOException) {
                                $phpMessage = mb_convert_encoding(
                                    $phpMessage,
                                    'UTF-8',
                                    'CP1251'
                                );
                            }

                        } else {
                            $mbEncodingList = mb_list_encodings();
                            array_unshift($mbEncodingList, 'CP1251');

                            $phpMessage = mb_convert_encoding(
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

        if (0 !== count($traceLines)) {
            echo 'Trace: ' . PHP_EOL;

            foreach ( $traceLines as $line ) {
                echo $line . PHP_EOL;
            }
        }
    }
}
