<?php

namespace Gzhegow\Lib\Modules\Debug\ThrowableManager;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\Iterator\ExceptionIterator;
use Gzhegow\Lib\Exception\Interfaces\HasMessageListInterface;
use Gzhegow\Lib\Exception\Iterator\PHP7\ExceptionIterator as ExceptionIteratorPHP7;


class ThrowableManager implements ThrowableManagerInterface
{
    /**
     * @var string
     */
    protected $dirRoot;


    /**
     * @return static
     */
    public function setDirRoot(?string $dirRoot)
    {
        if (null !== $dirRoot) {
            if (! Lib::fs()->type_dirpath_realpath($realpath, $dirRoot)) {
                throw new LogicException(
                    [ 'The `dirRoot` should be existing directory path', $dirRoot ]
                );
            }
        }

        $this->dirRoot = $realpath ?? null;

        return $this;
    }


    /**
     * @return array<string, \Throwable>
     */
    public function getPreviousArray(\Throwable $throwable) : array
    {
        $it = $this->getPreviousIterator($throwable);

        $dot = [];

        foreach ( $it as $i => $e ) {
            $dot[ $i ] = $e;
        }

        return $dot;
    }

    /**
     * @return \Generator<string, \Throwable[]>
     */
    public function getPreviousIterator(\Throwable $throwable) : \Generator
    {
        $it = $this->getPreviousTrackIterator($throwable);

        $index = [];

        foreach ( $it as $track ) {
            foreach ( $track as $ii => $e ) {
                if (isset($index[ $ii ])) {
                    continue;
                }

                yield $ii => $e;

                $index[ $ii ] = true;
            }
        }
    }

    /**
     * @return \Traversable<string, \Throwable[]>
     */
    public function getPreviousTrackIterator(\Throwable $throwable) : \Traversable
    {
        $it = (PHP_VERSION_ID >= 80000)
            ? new ExceptionIterator([ $throwable ])
            : new ExceptionIteratorPHP7([ $throwable ]);

        $iit = new \RecursiveIteratorIterator($it);

        return $iit;
    }


    /**
     * @return string[]
     */
    public function getPreviousMessageList(
        \Throwable $throwable, array $options = []
    ) : array
    {
        $lines = [];

        $array = $this->getPreviousArray($throwable);

        foreach ( $array as $dotpath => $e ) {
            $message = $this->getThrowableMessage($e, $options);
            $message = "[ {$dotpath} ] {$message}";

            $level = substr_count($dotpath, '.');
            $messageLines = $this->addPaddingToLines(
                $level,
                [ $message ]
            );

            $lines = array_merge(
                $lines,
                $messageLines
            );
        }

        return $lines;
    }

    /**
     * @return string[]
     */
    public function getPreviousMessageLines(
        \Throwable $throwable, array $options = []
    ) : array
    {
        $lines = [];

        $array = $this->getPreviousArray($throwable);

        $first = true;
        foreach ( $array as $dotpath => $e ) {
            $messageLines = $this->getThrowableMessageLines($e, $options);
            $messageLinesCnt = count($messageLines);

            $messageLines[ 0 ] = "[ {$dotpath} ] {$messageLines[ 0 ]}";

            if (! $first && ($messageLinesCnt > 1)) {
                array_unshift($messageLines, '');
            }

            $level = substr_count($dotpath, '.');
            $messageLines = $this->addPaddingToLines(
                $level,
                $messageLines
            );

            $lines = array_merge(
                $lines,
                $messageLines
            );

            if ($first) {
                $first = false;
            }
        }

        return $lines;
    }


    /**
     * @return string[]
     */
    public function getPreviousMessagesList(
        \Throwable $throwable, array $options = []
    ) : array
    {
        $lines = [];

        $array = $this->getPreviousArray($throwable);

        $first = true;
        foreach ( $array as $dotpath => $e ) {
            $messagesLines = $this->getThrowableMessages($e, $options);

            if (! $first) {
                array_unshift($messagesLines, '');
            }

            $level = substr_count($dotpath, '.');
            $messagesLines = $this->addPaddingToLines(
                $level,
                $messagesLines
            );

            $lines = array_merge(
                $lines,
                $messagesLines
            );

            if ($first) {
                $first = false;
            }
        }

        return $lines;
    }

    /**
     * @return string[]
     */
    public function getPreviousMessagesLines(
        \Throwable $throwable, array $options = []
    ) : array
    {
        $lines = [];

        $array = $this->getPreviousArray($throwable);

        $first = true;
        foreach ( $array as $dotpath => $e ) {
            $messagesLines = $this->getThrowableMessagesLines($e, $options);
            $messagesLinesCnt = count($messagesLines);

            $messagesLines[ 0 ] = "[ {$dotpath} ] {$messagesLines[ 0 ]}";

            if (! $first && ($messagesLinesCnt > 1)) {
                array_unshift($messagesLines, '');
            }

            $level = substr_count($dotpath, '.');
            $messagesLines = $this->addPaddingToLines(
                $level,
                $messagesLines
            );

            $lines = array_merge(
                $lines,
                $messagesLines
            );

            if ($first) {
                $first = false;
            }
        }

        return $lines;
    }


    /**
     * @template-covariant T of \Throwable
     *
     * @param \Throwable      $throwable
     * @param class-string<T> $throwableClass
     *
     * @return T|null
     */
    public function catchPrevious(\Throwable $throwable, string $throwableClass = '') : ?\Throwable
    {
        if ('' === $throwableClass) {
            return $throwable;
        }

        $gen = $this->getPreviousIterator($throwable);

        foreach ( $gen as $i => $e ) {
            if ($e instanceof $throwableClass) {
                return $e;
            }
        }

        return null;
    }


    public function getThrowableMessage(\Throwable $throwable, array $options = []) : string
    {
        $eMessage = $throwable->getMessage();

        $throwableMap = [ $eMessage => $throwable ];

        $eMessages = $this->convertMessagesToUtf8($throwableMap, $options);

        return reset($eMessages);
    }

    /**
     * @return string[]
     */
    public function getThrowableMessageLines(\Throwable $throwable, array $options = []) : array
    {
        $withCode = $options[ 'with_code' ] ?? false;

        $eMessage = $this->getThrowableMessage($throwable, $options);

        $lines = [];

        if ($withCode) {
            $eMessage = 'CODE[' . $throwable->getCode() . '] ' . $eMessage;
        }

        foreach ( explode("\n", $eMessage) as $line ) {
            $line = trim($line);
            if ('' === $line) {
                continue;
            }

            $lines[] = $line;
        }

        $lines = array_merge(
            $lines,
            $this->getThrowableInfoLines($throwable, $options)
        );

        return $lines;
    }


    /**
     * @return string[]
     */
    public function getThrowableMessages(\Throwable $throwable, array $options = []) : array
    {
        $throwableMap = [];
        if ($throwable instanceof HasMessageListInterface) {
            $throwableMap = array_fill_keys($throwable->getMessageList(), $throwable);

        } else {
            $throwableMap = [ $throwable->getMessage() => $throwable ];
        }

        $eMessages = $this->convertMessagesToUtf8($throwableMap, $options);

        return $eMessages;
    }

    /**
     * @return string[]
     */
    public function getThrowableMessagesLines(\Throwable $throwable, array $options = []) : array
    {
        $withCode = $options[ 'with_code' ] ?? false;

        $eMessages = $this->getThrowableMessages($throwable, $options);

        $lines = [];

        foreach ( $eMessages as $eMessage ) {
            if ($withCode) {
                $eMessage = 'CODE[' . $throwable->getCode() . '] ' . $eMessage;
            }

            foreach ( explode("\n", $eMessage) as $line ) {
                $line = trim($line);
                if ('' === $line) {
                    continue;
                }

                $lines[] = $line;
            }
        }

        $lines = array_merge(
            $lines,
            $this->getThrowableInfoLines($throwable, $options)
        );

        return $lines;
    }


    public function getThrowableInfo(\Throwable $throwable, array $options = []) : array
    {
        $eFile = $throwable->getFile();
        $eLine = $throwable->getLine();
        $eObjectClass = get_class($throwable);
        $eObjectId = spl_object_id($throwable);

        if ('' === $eFile) {
            $eFile = '{file}';

        } else {
            $dirRoot = $options[ 'dir_root' ] ?? $this->dirRoot;

            if (null !== $dirRoot) {
                $theFs = Lib::fs();

                if (! $theFs->type_dirpath_realpath($dirRootRealpath, $dirRoot)) {
                    throw new LogicException(
                        [ 'The `options[dir_root]` should be existing directory', $dirRoot ]
                    );
                }

                $eFile = $theFs->path_relative(
                    $eFile,
                    $dirRoot,
                    '/'
                );
            }
        }

        if (0 >= $eLine) {
            $eLine = -1;
        }

        $info = [
            'file'         => $eFile,
            'line'         => $eLine,
            'object_class' => $eObjectClass,
            'object_id'    => $eObjectId,
        ];

        return $info;
    }

    /**
     * @return string[]
     */
    public function getThrowableInfoLines(\Throwable $throwable, array $options = []) : array
    {
        $lines = [];

        $withFile = boolval($options[ 'with_file' ] ?? true);
        $withFileLine = boolval($options[ 'with_file_line' ] ?? true);
        $withObjectClass = boolval($options[ 'with_object_class' ] ?? true);
        $withObjectId = boolval($options[ 'with_object_id' ] ?? false);

        if ($withFile) {
            $eFile = $throwable->getFile();

            if ('' === $eFile) {
                $eFile = '{file}';

            } else {
                $dirRoot = $options[ 'dir_root' ] ?? $this->dirRoot;

                if (null !== $dirRoot) {
                    $theFs = Lib::fs();

                    if (! $theFs->type_dirpath_realpath($dirRootRealpath, $dirRoot)) {
                        throw new LogicException(
                            [ 'The `options[dir_root]` should be existing directory', $dirRoot ]
                        );
                    }

                    $eFile = $theFs->path_relative(
                        $eFile,
                        $dirRoot,
                        '/'
                    );
                }
            }

            if ($withFileLine) {
                $eLine = $throwable->getLine();

                if (0 >= $eLine) {
                    $eLine = -1;
                }
            }
        }

        if ($withObjectClass) {
            $eObjectClass = get_class($throwable);

            $line = "object # {$eObjectClass}";

            if ($withObjectId) {
                $eObjectId = spl_object_id($throwable);

                $line .= " # {$eObjectId}";
            }

            $line = "{ {$line} }";

            $lines[] = $line;
        }

        if ($withFile) {
            $line = $eFile;

            if ($withFileLine) {
                $line .= " : {$eLine}";
            }

            $lines[] = $line;
        }

        return $lines;
    }


    public function getThrowableTrace(
        \Throwable $e, array $options = []
    ) : array
    {
        $dirRoot = $options[ 'dir_root' ] ?? $this->dirRoot;

        $eTrace = $e->getTrace();

        if (null !== $dirRoot) {
            $theFs = Lib::fs();

            foreach ( $eTrace as $i => $frame ) {
                if (! isset($frame[ 'file' ])) {
                    continue;
                }

                $fileRelative = $theFs->path_relative(
                    $frame[ 'file' ], $dirRoot, '/'
                );

                $eTrace[ $i ][ 'file' ] = $fileRelative;
            }
        }

        return $eTrace;
    }

    /**
     * @return string[]
     */
    public function getThrowableTraceLines(
        \Throwable $throwable, array $options = []
    ) : array
    {
        $lines = [];

        $trace = $this->getThrowableTrace(
            $throwable, $options
        );

        foreach ( $trace as $traceItem ) {
            $phpFile = $traceItem[ 'file' ] ?? '{file}';
            $phpLine = $traceItem[ 'line' ] ?? 0;

            $lines[] = "{$phpFile} : {$phpLine}";
        }

        return $lines;
    }


    /**
     * @param array<string, \Throwable> $throwableMap
     *
     * @return string[]
     */
    protected function convertMessagesToUtf8(array $throwableMap, array $options = []) : array
    {
        // > mbstring is required to convert to UTF-8
        $isMbstring = extension_loaded('mbstring');
        if (! $isMbstring) {
            return array_keys($throwableMap);
        }

        // > unix/mac works well with non-utf8 strings in terminal
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        if (! $isWindows) {
            return array_keys($throwableMap);
        }

        $isMbstringSupportsEncodingListWithCp1251 = (PHP_VERSION_ID >= 80100);

        foreach ( $throwableMap as $eMessage => $throwable ) {
            $isUtf8 = (1 === preg_match('//u', $eMessage));
            if ($isUtf8) {
                continue;
            }

            $eMessageUtf8 = $eMessage;

            if ($isMbstringSupportsEncodingListWithCp1251) {
                $mbEncodingList = mb_list_encodings();

                array_unshift($mbEncodingList, 'CP1251');

                $eMessageUtf8 = mb_convert_encoding(
                    $eMessage,
                    'UTF-8',
                    $mbEncodingList
                );

            } else {
                // > gzhegow, 2025-02-26, case is happened only with \PDOException
                if ($throwable instanceof \PDOException) {
                    $eMessageUtf8 = mb_convert_encoding(
                        $eMessage,
                        'UTF-8',
                        'CP1251'
                    );
                }
            }

            unset($throwableMap[ $eMessage ]);

            $throwableMap[ $eMessageUtf8 ] = $throwable;
        }

        return array_keys($throwableMap);
    }

    /**
     * @return string[]
     */
    protected function addPaddingToLines(int $level, array $linesSource) : array
    {
        $lines = [];

        $padding = ($level > 0)
            ? str_repeat('--', $level) . ' '
            : '';

        foreach ( $linesSource as $i => $line ) {
            $lines[ $i ] = $padding . $line;
        }

        return $lines;
    }
}
