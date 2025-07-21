<?php

namespace Gzhegow\Lib\Modules\Debug\Throwabler;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\Iterator\ExceptionIterator;
use Gzhegow\Lib\Exception\Interfaces\HasMessageListInterface;
use Gzhegow\Lib\Exception\Iterator\PHP7\ExceptionIterator as ExceptionIteratorPHP7;


class DefaultThrowabler implements ThrowablerInterface
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
        $theType = Lib::type();

        if (null !== $dirRoot) {
            $dirRootRealpath = $theType->dirpath_realpath($dirRoot)->orThrow();
        }

        $this->dirRoot = $dirRootRealpath ?? null;

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
    public function getPreviousMessageFirstList(\Throwable $throwable, ?int $flags = null) : array
    {
        $lines = [];

        $array = $this->getPreviousArray($throwable);

        foreach ( $array as $dotpath => $e ) {
            $message = $this->getThrowableMessageFirst($e, $flags);
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
    public function getPreviousMessageFirstLines(\Throwable $throwable, ?int $flags = null) : array
    {
        $lines = [];

        $array = $this->getPreviousArray($throwable);

        $first = true;
        foreach ( $array as $dotpath => $e ) {
            $messageLines = $this->getThrowableMessageFirstLines($e, $flags);
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
    public function getPreviousMessagesAllList(\Throwable $throwable, ?int $flags = null) : array
    {
        $lines = [];

        $array = $this->getPreviousArray($throwable);

        $first = true;
        foreach ( $array as $dotpath => $e ) {
            $messagesLines = $this->getThrowableMessagesAllList($e, $flags);

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
    public function getPreviousMessagesAllLines(\Throwable $throwable, ?int $flags = null) : array
    {
        $lines = [];

        $array = $this->getPreviousArray($throwable);

        $first = true;
        foreach ( $array as $dotpath => $e ) {
            $messagesLines = $this->getThrowableMessagesAllLines($e, $flags);
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

        foreach ( $gen as $e ) {
            if ($e instanceof $throwableClass) {
                return $e;
            }
        }

        return null;
    }


    public function getThrowableMessageFirst(\Throwable $throwable, ?int $flags = null) : string
    {
        $eMessage = $throwable->getMessage();

        $throwableMap = [ $eMessage => $throwable ];

        $eMessages = $this->convertMessagesToUtf8($throwableMap, $flags);

        return reset($eMessages);
    }

    /**
     * @return string[]
     */
    public function getThrowableMessageFirstLines(\Throwable $throwable, ?int $flags = null) : array
    {
        $flags = $this->flagsDefault($flags);

        $withCode = $flags & _DEBUG_THROWABLE_WITH_CODE;

        $eMessage = $this->getThrowableMessageFirst($throwable, $flags);

        $lines = [];

        if ($withCode) {
            $eMessage = 'CODE[' . $throwable->getCode() . '] ' . $eMessage;
        }

        $eMessageLines = explode("\n", $eMessage);

        foreach ( $eMessageLines as $line ) {
            $line = trim($line);
            if ('' === $line) {
                continue;
            }

            $lines[] = $line;
        }

        $lines = array_merge(
            $lines,
            $this->getThrowableInfoLines($throwable, $flags)
        );

        return $lines;
    }


    /**
     * @return string[]
     */
    public function getThrowableMessagesAllList(\Throwable $throwable, ?int $flags = null) : array
    {
        if ($throwable instanceof HasMessageListInterface) {
            $throwableMap = array_fill_keys($throwable->getMessageList(), $throwable);

        } else {
            $throwableMap = [ $throwable->getMessage() => $throwable ];
        }

        $eMessages = $this->convertMessagesToUtf8($throwableMap, $flags);

        return $eMessages;
    }

    /**
     * @return string[]
     */
    public function getThrowableMessagesAllLines(\Throwable $throwable, ?int $flags = null) : array
    {
        $flags = $this->flagsDefault($flags);

        $withCode = $flags & _DEBUG_THROWABLE_WITH_CODE;

        $eMessages = $this->getThrowableMessagesAllList($throwable, $flags);

        $lines = [];

        foreach ( $eMessages as $eMessage ) {
            if ($withCode) {
                $eMessage = 'CODE[' . $throwable->getCode() . '] ' . $eMessage;
            }

            $eMessageLines = explode("\n", $eMessage);

            foreach ( $eMessageLines as $line ) {
                $line = rtrim($line);
                if ('' === $line) {
                    continue;
                }

                $lines[] = $line;
            }

            if (count($eMessageLines) > 1) {
                $lines[] = '';
            }
        }

        $lines = array_merge(
            $lines,
            $this->getThrowableInfoLines($throwable, $flags)
        );

        return $lines;
    }


    public function getThrowableInfo(\Throwable $throwable, ?int $flags = null) : array
    {
        $theDebug = Lib::debug();
        $theFs = Lib::fs();

        $eFile = $throwable->getFile();
        $eLine = $throwable->getLine();
        $eObjectClass = get_class($throwable);
        $eObjectId = spl_object_id($throwable);

        if ('' === $eFile) {
            $eFile = '{file}';

        } else {
            $dirRoot = $this->dirRoot ?? $theDebug->static_dir_root();

            if (null !== $dirRoot) {
                try {
                    $eFileRelative = $theFs->path_relative(
                        $eFile,
                        $dirRoot,
                        '/'
                    );
                }
                catch ( \Throwable $e ) {
                    $eFileRelative = $eFile;
                }

                $eFile = $eFileRelative;
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
    public function getThrowableInfoLines(\Throwable $throwable, ?int $flags = null) : array
    {
        $theDebug = Lib::debug();
        $theFs = Lib::fs();

        $lines = [];

        $flags = $this->flagsDefault($flags);

        $withFile = $flags & _DEBUG_THROWABLE_WITH_FILE;
        $withObjectClass = $flags & _DEBUG_THROWABLE_WITH_OBJECT_CLASS;
        $withObjectId = $flags & _DEBUG_THROWABLE_WITH_OBJECT_ID;

        if ($withFile) {
            $eFile = $throwable->getFile();
            $eLine = $throwable->getLine();

            if ('' === $eFile) {
                $eFile = '{file}';

            } else {
                $dirRoot = $this->dirRoot ?? $theDebug->static_dir_root();

                if (null !== $dirRoot) {
                    try {
                        $eFileRelative = $theFs->path_relative(
                            $eFile,
                            $dirRoot,
                            '/'
                        );
                    }
                    catch ( \Throwable $e ) {
                        $eFileRelative = $eFile;
                    }

                    $eFile = $eFileRelative;
                }
            }

            if ($eLine <= 0) {
                $eLine = -1;
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
            $line = "{$eFile} : {$eLine}";

            $lines[] = $line;
        }

        return $lines;
    }


    public function getThrowableTrace(\Throwable $e, ?int $flags = null) : array
    {
        $theDebug = Lib::debug();
        $theFs = Lib::fs();

        $eTrace = $e->getTrace();

        $dirRoot = $this->dirRoot ?? $theDebug->static_dir_root();

        if (null !== $dirRoot) {
            foreach ( $eTrace as $i => $t ) {
                if (! isset($t[ 'file' ])) {
                    continue;
                }

                try {
                    $tFileRelative = $theFs->path_relative(
                        $t[ 'file' ],
                        $dirRoot,
                        '/'
                    );
                }
                catch ( \Throwable $e ) {
                    $tFileRelative = $t[ 'file' ];
                }

                $eTrace[ $i ][ 'file' ] = $tFileRelative;
            }
        }

        return $eTrace;
    }

    /**
     * @return string[]
     */
    public function getThrowableTraceLines(\Throwable $throwable, ?int $flags = null) : array
    {
        $lines = [];

        $trace = $this->getThrowableTrace(
            $throwable, $flags
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
    protected function convertMessagesToUtf8(array $throwableMap, ?int $flags = null) : array
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
                array_unshift($mbEncodingList, 'CP866');

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


    protected function flagsDefault(?int $flags) : int
    {
        $flagsCurrent = $flags ?? 0;

        $flagGroups = [
            '_DEBUG_THROWABLE_WITH_FILE'         => [
                [
                    _DEBUG_THROWABLE_WITH_FILE,
                    _DEBUG_THROWABLE_WITHOUT_FILE,
                ],
                _DEBUG_THROWABLE_WITH_FILE,
            ],
            //
            '_DEBUG_THROWABLE_WITH_OBJECT_CLASS' => [
                [
                    _DEBUG_THROWABLE_WITH_OBJECT_CLASS,
                    _DEBUG_THROWABLE_WITHOUT_OBJECT_CLASS,
                ],
                _DEBUG_THROWABLE_WITH_OBJECT_CLASS,
            ],
            //
            '_DEBUG_THROWABLE_WITH_CODE'         => [
                [
                    _DEBUG_THROWABLE_WITH_CODE,
                    _DEBUG_THROWABLE_WITHOUT_CODE,
                ],
                _DEBUG_THROWABLE_WITHOUT_CODE,
            ],
            //
            '_DEBUG_THROWABLE_WITH_OBJECT_ID'    => [
                [
                    _DEBUG_THROWABLE_WITH_OBJECT_ID,
                    _DEBUG_THROWABLE_WITHOUT_OBJECT_ID,
                ],
                _DEBUG_THROWABLE_WITHOUT_OBJECT_ID,
            ],
            //
            '_DEBUG_THROWABLE_WITH_PARENTS'      => [
                [
                    _DEBUG_THROWABLE_WITH_PARENTS,
                    _DEBUG_THROWABLE_WITHOUT_PARENTS,
                ],
                _DEBUG_THROWABLE_WITHOUT_PARENTS,
            ],
        ];

        foreach ( $flagGroups as $groupName => [ $conflict, $default ] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ($flagsCurrent & $flag) {
                    $cnt++;
                }
            }

            if ($cnt > 1) {
                throw new LogicException(
                    [ 'The `flags` conflict in group: ' . $groupName, $flags ]
                );

            } elseif (0 === $cnt) {
                $flagsCurrent |= $default;
            }
        }

        return $flagsCurrent;
    }
}
