<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\Iterator\ExceptionIterator;
use Gzhegow\Lib\Exception\Iterator\PHP7\ExceptionIterator as ExceptionIteratorPHP7;


class ErrorHandler
{
    /**
     * @var string
     */
    protected $dirRoot;
    /**
     * @var bool
     */
    protected $forceExit;

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
    }


    /**
     * @return static
     */
    public function setDirRoot(?string $dirRoot)
    {
        if (! Lib::fs()->type_dirpath_realpath($realpath, $dirRoot)) {
            throw new LogicException(
                [ 'The `dirRoot` should be existing directory', $dirRoot ]
            );
        }

        $this->dirRoot = $realpath;

        return $this;
    }

    /**
     * @return static
     */
    public function setForceExit(?bool $forceExit)
    {
        $this->forceExit = $forceExit ?? false;

        return $this;
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
    public function useErrorReporting(&$last = null)
    {
        $last = error_reporting($this->errorReporting);

        return $this;
    }

    /**
     * @param callable|null $last
     *
     * @return static
     */
    public function useErrorHandler(&$last = null)
    {
        $fn = $this->errorHandler ?? $this->fnErrorHandler();

        $last = set_error_handler($fn);

        return $this;
    }

    /**
     * @param callable|null $last
     *
     * @return static
     */
    public function useExceptionHandler(&$last = null)
    {
        $fn = $this->exceptionHandler ?? $this->fnExceptionHandler();

        $last = set_exception_handler($fn);

        return $this;
    }


    public function fnErrorHandler() : \Closure
    {
        return function ($errno, $errstr, $errfile, $errline) {
            if (error_reporting() & $errno) {
                throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
            }
        };
    }

    public function fnExceptionHandler() : \Closure
    {
        $dirRoot = $this->dirRoot;
        $forceExit = $this->forceExit;

        return static function (\Throwable $throwable) use ($dirRoot, $forceExit) {
            $messageLines = static::getThrowableMessageListLines(
                $throwable,
                true, true,
                $dirRoot
            );

            $traceLines = static::getThrowableTraceLines(
                $throwable,
                $dirRoot
            );

            if ([] !== $messageLines) {
                foreach ( $messageLines as $line ) {
                    echo $line . PHP_EOL;
                }
            }

            if ([] !== $traceLines) {
                echo PHP_EOL;

                echo 'Trace: ' . PHP_EOL;

                foreach ( $traceLines as $line ) {
                    echo $line . PHP_EOL;
                }
            }

            if ($forceExit) {
                exit();
            }
        };
    }


    /**
     * @return \Traversable<string, \Throwable[]>
     */
    public static function getThrowableIterator(\Throwable $throwable) : \Traversable
    {
        $it = (PHP_VERSION_ID >= 80000)
            ? new ExceptionIterator([ $throwable ])
            : new ExceptionIteratorPHP7([ $throwable ]);

        $iit = new \RecursiveIteratorIterator($it);

        return $iit;
    }

    /**
     * @return array<string, \Throwable>
     */
    public static function getThrowableArrayDot(\Throwable $throwable) : array
    {
        $iit = static::getThrowableIterator($throwable);

        $tree = [];

        foreach ( $iit as $i => $track ) {
            foreach ( $track as $ii => $e ) {
                if (isset($tree[ $ii ])) {
                    continue;
                }

                $tree[ $ii ] = $e;
            }
        }

        return $tree;
    }


    /**
     * @return string[]
     */
    public static function getThrowableMessageListLines(
        \Throwable $throwable,
        //
        ?bool $withFile = null,
        ?bool $withId = null,
        //
        ?string $dirRoot = null
    ) : array
    {
        $arrayDot = static::getThrowableArrayDot($throwable);

        $messageLines = [];

        $first = true;
        foreach ( $arrayDot as $dotpath => $e ) {
            $level = substr_count($dotpath, '.');

            $lines = static::getThrowableMessageLines(
                $e,
                $withFile, $withId,
                $dirRoot
            );
            $linesCnt = count($lines);

            $lines[ 0 ] = "[ {$dotpath} ] " . $lines[ 0 ];

            if (! $first && ($linesCnt > 1)) {
                array_unshift($lines, '');
            }

            foreach ( array_keys($lines) as $i ) {
                $padding = ($level > 0)
                    ? str_repeat('--', $level) . ' '
                    : '';

                $lines[ $i ] = $padding . $lines[ $i ];
            }

            $messageLines = array_merge(
                $messageLines,
                $lines
            );

            if ($first) {
                $first = false;
            }
        }

        return $messageLines;
    }


    public static function getThrowableMessage(
        \Throwable $throwable
    ) : string
    {
        $eMessage = $throwable->getMessage();

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            $shouldTryConvertToUtf8 = extension_loaded('mbstring');

            if ($shouldTryConvertToUtf8) {
                $isUtf8 = (1 === preg_match('//u', $eMessage));

                if (! $isUtf8) {
                    $shouldTryConvertFromCp1251 = (PHP_VERSION_ID < 80100);

                    if ($shouldTryConvertFromCp1251) {
                        // > gzhegow, 2025-02-26, case is happened only with \PDOException
                        if ($throwable instanceof \PDOException) {
                            $eMessage = mb_convert_encoding(
                                $eMessage,
                                'UTF-8',
                                'CP1251'
                            );
                        }

                    } else {
                        $mbEncodingList = mb_list_encodings();
                        array_unshift($mbEncodingList, 'CP1251');

                        $eMessage = mb_convert_encoding(
                            $eMessage,
                            'UTF-8',
                            $mbEncodingList
                        );
                    }
                }
            }
        }

        return $eMessage;
    }

    /**
     * @return string[]
     */
    public static function getThrowableMessageLines(
        \Throwable $throwable,
        //
        ?bool $withFile = null,
        ?bool $withId = null,
        //
        ?string $dirRoot = null
    ) : array
    {
        $withFile = $withFile ?? true;
        $withId = $withId ?? false;

        $phpClass = get_class($throwable);
        $phpFile = $throwable->getFile() ?? '{file}';
        $phpLine = $throwable->getLine() ?? 0;

        $phpMessage = static::getThrowableMessage($throwable);

        $messageLines[] = $phpMessage;

        if ($withId) {
            $phpId = spl_object_id($throwable);

            $messageLines[] = "{ object # {$phpClass} # {$phpId} }";

        } else {
            $messageLines[] = "{ object # {$phpClass} }";
        }

        if ($withFile) {
            if (null !== $dirRoot) {
                $theFs = Lib::fs();

                $phpFile = $theFs->path_relative(
                    $phpFile, $dirRoot, '/'
                );
            }

            $messageLines[] = "{$phpFile} : {$phpLine}";
        }

        return $messageLines;
    }


    public static function getThrowableTrace(
        \Throwable $e,
        //
        ?string $dirRoot = null
    ) : array
    {
        $trace = $e->getTrace();

        if (null !== $dirRoot) {
            $theFs = Lib::fs();

            foreach ( $trace as $i => $frame ) {
                if (! isset($frame[ 'file' ])) {
                    continue;
                }

                $fileRelative = $theFs->path_relative(
                    $frame[ 'file' ], $dirRoot, '/'
                );

                $trace[ $i ][ 'file' ] = $fileRelative;
            }
        }

        return $trace;
    }

    /**
     * @return string[]
     */
    public static function getThrowableTraceLines(
        \Throwable $throwable,
        //
        ?string $dirRoot = null
    ) : array
    {
        $trace = static::getThrowableTrace(
            $throwable,
            $dirRoot
        );

        $traceLines = [];
        foreach ( $trace as $traceItem ) {
            $phpFile = $traceItem[ 'file' ] ?? '{file}';
            $phpLine = $traceItem[ 'line' ] ?? 0;

            $traceLines[] = "{$phpFile} : {$phpLine}";
        }

        return $traceLines;
    }
}
