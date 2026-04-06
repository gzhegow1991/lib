<?php

/** @noinspection PhpComposerExtensionStubsInspection */

namespace Gzhegow\Lib\Modules\Debug\Throwabler;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\ExceptInterface;
use Gzhegow\Lib\Exception\Iterator\ExceptionIterator;
use Gzhegow\Lib\Exception\Interfaces\HasMessageListInterface;
use Gzhegow\Lib\Exception\Interfaces\HasTraceOverrideInterface;
use Gzhegow\Lib\Exception\Iterator\PHP7\ExceptionIterator as ExceptionIteratorPHP7;


class DefaultThrowabler implements ThrowablerInterface
{
    /**
     * @template-covariant T of (\Throwable|ExceptInterface)
     *
     * @param \Throwable|ExceptInterface $throwable
     * @param class-string<T>|null       $throwableClass
     *
     * @return T|null
     */
    public function catchPrevious($throwable, ?string $throwableClass = null) : ?\Throwable
    {
        if ( null === $throwableClass ) {
            return $throwable;
        }

        $gen = $this->getPreviousIterator($throwable);

        foreach ( $gen as $e ) {
            if ( $e instanceof $throwableClass ) {
                return $e;
            }
        }

        return null;
    }


    /**
     * @return array<string, \Throwable|ExceptInterface>
     */
    public function getPreviousArray($throwable) : array
    {
        $it = $this->getPreviousIterator($throwable);

        $dot = [];

        foreach ( $it as $i => $e ) {
            $dot[$i] = $e;
        }

        return $dot;
    }

    /**
     * @return \Generator<string, (\Throwable|ExceptInterface)[]>
     */
    public function getPreviousIterator($throwable) : \Generator
    {
        $it = $this->getPreviousTrackIterator($throwable);

        $index = [];

        foreach ( $it as $track ) {
            foreach ( $track as $ii => $e ) {
                if ( isset($index[$ii]) ) {
                    continue;
                }

                yield $ii => $e;

                $index[$ii] = true;
            }
        }
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return \Traversable<string, (\Throwable|ExceptInterface)[]>
     */
    public function getPreviousTrackIterator($throwable) : \Traversable
    {
        $it = (PHP_VERSION_ID >= 80000)
            ? new ExceptionIterator([ $throwable ])
            : new ExceptionIteratorPHP7([ $throwable ]);

        $iit = new \RecursiveIteratorIterator($it);

        return $iit;
    }


    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getPreviousMessageFirstList($throwable, ?int $flags = null) : array
    {
        $messagesList = [];

        $array = $this->getPreviousArray($throwable);

        foreach ( $array as $dotpath => $e ) {
            $messagesList[$dotpath] = $this->getThrowableMessageFirstString($e, $flags);
        }

        return $messagesList;
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getPreviousMessageFirstLines($throwable, ?int $flags = null) : array
    {
        $lines = [];

        $array = $this->getPreviousArray($throwable);

        $first = true;
        foreach ( $array as $dotpath => $e ) {
            $messageLines = $this->getThrowableMessageFirstLines($e, $flags);
            $messageLinesCnt = count($messageLines);

            $messageLines[0] = "[ {$dotpath} ] {$messageLines[ 0 ]}";

            if ( ! $first && ($messageLinesCnt > 1) ) {
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

            if ( $first ) {
                $first = false;
            }
        }

        return $lines;
    }


    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getPreviousMessagesAllDict($throwable, ?int $flags = null) : array
    {
        $messagesLists = [];

        $array = $this->getPreviousArray($throwable);

        foreach ( $array as $dotpath => $e ) {
            $messagesLists[$dotpath] = $this->getThrowableMessagesAllList($e, $flags);
        }

        return $messagesLists;
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getPreviousMessagesAllLines($throwable, ?int $flags = null) : array
    {
        $flags = $this->flagsDefault($flags);

        $flagsNoInfoNoTrace = $flags;
        $flagsNoInfoNoTrace |= _DEBUG_THROWABLER_WITHOUT_INFO;
        $flagsNoInfoNoTrace |= _DEBUG_THROWABLER_WITHOUT_TRACE;
        $flagsNoInfoNoTrace &= ~_DEBUG_THROWABLER_WITH_INFO;
        $flagsNoInfoNoTrace &= ~_DEBUG_THROWABLER_WITH_TRACE;

        $isWithInfo = $flags & _DEBUG_THROWABLER_WITH_INFO;
        $isWithTrace = $flags & _DEBUG_THROWABLER_WITH_TRACE;

        $array = $this->getPreviousArray($throwable);

        $lines = [];

        $first = true;
        foreach ( $array as $dotpath => $e ) {
            $messagesLines = $this->getThrowableMessagesAllLines($e, $flagsNoInfoNoTrace);
            $messagesLinesCnt = count($messagesLines);

            $messagesWithInfoAndTraceLines = $messagesLines;

            if ( $isWithInfo ) {
                $linesInfo = $this->getThrowableInfoLines($e, $flags);

                $messagesWithInfoAndTraceLines = array_merge($messagesWithInfoAndTraceLines, $linesInfo);
            }

            if ( $isWithTrace ) {
                $linesTrace = $this->getThrowableTraceLines($e, $flags);

                if ( [] !== $linesTrace ) {
                    $messagesWithInfoAndTraceLines = array_merge(
                        $messagesWithInfoAndTraceLines,
                        [
                            '',
                            'Trace: ',
                        ],
                        $linesTrace
                    );
                }
            }

            $messagesWithInfoLinesCnt = count($messagesWithInfoAndTraceLines);

            if ( $messagesLinesCnt > 1 ) {
                array_unshift($messagesWithInfoAndTraceLines, "[ {$dotpath} >>> ]");

                $messagesWithInfoAndTraceLines[] = "[ {$dotpath} <<< ]";

            } elseif ( 1 === $messagesLinesCnt ) {
                $messagesWithInfoAndTraceLines[0] = "[ {$dotpath} ] {$messagesWithInfoAndTraceLines[ 0 ]}";

            } else {
                continue;
            }

            if ( ! $first && ($messagesWithInfoLinesCnt > 1) ) {
                array_unshift($messagesWithInfoAndTraceLines, '');
            }

            $level = substr_count($dotpath, '.');
            $messagesWithInfoAndTraceLines = $this->addPaddingToLines(
                $level,
                $messagesWithInfoAndTraceLines
            );

            $lines = array_merge(
                $lines,
                $messagesWithInfoAndTraceLines
            );

            if ( $first ) {
                $first = false;
            }
        }

        return $lines;
    }


    /**
     * @param \Throwable|ExceptInterface $throwable
     */
    public function getThrowableMessageFirstString($throwable, ?int $flags = null) : string
    {
        $eMessage = $throwable->getMessage();

        $throwableMap = [ $eMessage => $throwable ];

        $eMessages = $this->extractMessagesUtf8FromThrowableMap($throwableMap, $flags);

        return reset($eMessages);
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableMessageFirstLines($throwable, ?int $flags = null) : array
    {
        $flags = $this->flagsDefault($flags);

        $isWithCode = $flags & _DEBUG_THROWABLER_WITH_CODE;
        $isWithInfo = $flags & _DEBUG_THROWABLER_WITH_INFO;
        $isWithTrace = $flags & _DEBUG_THROWABLER_WITH_TRACE;

        $eMessage = $this->getThrowableMessageFirstString($throwable, $flags);

        $lines = [];

        if ( $isWithCode ) {
            $eMessage = 'CODE[' . $throwable->getCode() . '] ' . $eMessage;
        }

        $eMessageLines = explode("\n", $eMessage);

        foreach ( $eMessageLines as $line ) {
            $line = rtrim($line);
            if ( '' === $line ) {
                continue;
            }

            $lines[] = $line;
        }

        if ( $isWithInfo ) {
            $linesInfo = $this->getThrowableInfoLines($throwable, $flags);

            $lines = array_merge($lines, $linesInfo);
        }

        if ( $isWithTrace ) {
            $linesTrace = $this->getThrowableTraceLines($throwable, $flags);

            if ( [] !== $linesTrace ) {
                $lines = array_merge(
                    $lines,
                    [
                        '',
                        'Trace:',
                    ],
                    $linesTrace
                );
            }
        }

        return $lines;
    }


    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableMessagesAllList($throwable, ?int $flags = null) : array
    {
        if ( $throwable instanceof HasMessageListInterface ) {
            $throwableMap = array_fill_keys($throwable->getMessageList(), $throwable);

        } else {
            $throwableMap = [ $throwable->getMessage() => $throwable ];
        }

        $eMessages = $this->extractMessagesUtf8FromThrowableMap($throwableMap, $flags);

        return $eMessages;
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableMessagesAllLines($throwable, ?int $flags = null) : array
    {
        $flags = $this->flagsDefault($flags);

        $isWithCode = $flags & _DEBUG_THROWABLER_WITH_CODE;
        $isWithInfo = $flags & _DEBUG_THROWABLER_WITH_INFO;
        $isWithTrace = $flags & _DEBUG_THROWABLER_WITH_TRACE;

        $eMessages = $this->getThrowableMessagesAllList($throwable, $flags);

        $lines = [];

        foreach ( $eMessages as $eMessage ) {
            if ( $isWithCode ) {
                $eMessage = 'CODE[' . $throwable->getCode() . '] ' . $eMessage;
            }

            $eMessageLines = explode("\n", $eMessage);

            foreach ( $eMessageLines as $line ) {
                $line = rtrim($line);
                if ( '' === $line ) {
                    continue;
                }

                $lines[] = $line;
            }

            if ( count($eMessageLines) > 1 ) {
                $lines[] = '';
            }
        }

        if ( $isWithInfo ) {
            $linesInfo = $this->getThrowableInfoLines($throwable, $flags);

            $lines = array_merge($lines, $linesInfo);
        }

        if ( $isWithTrace ) {
            $linesTrace = $this->getThrowableTraceLines($throwable, $flags);

            if ( [] !== $linesTrace ) {
                $lines = array_merge(
                    $lines,
                    [
                        '',
                        'Trace:',
                    ],
                    $linesTrace
                );
            }
        }

        return $lines;
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     */
    public function getThrowableInfoArray($throwable, ?int $flags = null) : array
    {
        $theDebug = Lib::debug();
        $theFs = Lib::fs();

        if ( $throwable instanceof HasTraceOverrideInterface ) {
            $eFile = $throwable->getFileOverride();
            $eLine = $throwable->getLineOverride();

        } else {
            $eFile = $throwable->getFile();
            $eLine = $throwable->getLine();
        }

        $eObjectClass = get_class($throwable);
        $eObjectId = spl_object_id($throwable);

        if ( '' === $eFile ) {
            $eFile = '{file}';

        } else {
            $dirRoot = $theDebug->staticDirRoot();

            if ( null !== $dirRoot ) {
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

        if ( 0 >= $eLine ) {
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
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableInfoLines($throwable, ?int $flags = null) : array
    {
        $flags = $this->flagsInfoDefault($flags);

        $theDebug = Lib::debug();
        $theFs = Lib::fs();

        $isWithFile = $flags & _DEBUG_THROWABLER_INFO_WITH_FILE;
        $isWithObjectClass = $flags & _DEBUG_THROWABLER_INFO_WITH_OBJECT_CLASS;
        $isWithObjectId = $flags & _DEBUG_THROWABLER_INFO_WITH_OBJECT_ID;

        $lines = [];

        if ( $isWithObjectClass ) {
            $eObjectClass = get_class($throwable);

            $line = "object # {$eObjectClass}";

            if ( $isWithObjectId ) {
                $eObjectId = spl_object_id($throwable);

                $line .= " # {$eObjectId}";
            }

            $line = "{ {$line} }";

            $lines[] = $line;
        }

        if ( $isWithFile ) {
            if ( $throwable instanceof HasTraceOverrideInterface ) {
                $eFile = $throwable->getFileOverride();
                $eLine = $throwable->getLineOverride();

            } else {
                $eFile = $throwable->getFile();
                $eLine = $throwable->getLine();
            }

            if ( '' === $eFile ) {
                $eFile = '{file}';

            } else {
                $dirRoot = $theDebug->staticDirRoot();

                if ( null !== $dirRoot ) {
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

            if ( $eLine <= 0 ) {
                $eLine = -1;
            }

            $line = "{$eFile} : {$eLine}";

            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     */
    public function getThrowableTraceArray($throwable, ?int $flags = null) : array
    {
        $theDebug = Lib::debug();
        $theFs = Lib::fs();

        $eTrace = $throwable instanceof HasTraceOverrideInterface
            ? $throwable->getTraceOverride()
            : $throwable->getTrace();

        $dirRoot = $theDebug->staticDirRoot();

        if ( null !== $dirRoot ) {
            foreach ( $eTrace as $i => $t ) {
                if ( ! isset($t['file']) ) {
                    continue;
                }

                try {
                    $tFileRelative = $theFs->path_relative(
                        $t['file'],
                        $dirRoot,
                        '/'
                    );
                }
                catch ( \Throwable $throwable ) {
                    $tFileRelative = $t['file'];
                }

                $eTrace[$i]['file'] = $tFileRelative;
            }
        }

        return $eTrace;
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableTraceLines($throwable, ?int $flags = null) : array
    {
        $lines = [];

        $trace = $this->getThrowableTraceArray(
            $throwable, $flags
        );

        foreach ( $trace as $traceItem ) {
            $phpLine = $traceItem['line'] ?? 0;
            $phpFile = $traceItem['file'] ?? '{file}';

            $phpClass = $traceItem['class'] ?? '';
            $phpType = $traceItem['type'] ?? '';
            $phpFunction = $traceItem['function'] ?? '';

            $phpFn = array_filter([ $phpClass, $phpType, $phpFunction ]) ?: [];
            $phpFn = $phpFn ? implode('', $phpFn) : '{function}';

            $lines[] = "{$phpFile} : {$phpLine} : {$phpFn}";
        }

        return $lines;
    }


    /**
     * @param array<string, \Throwable|ExceptInterface> $throwableMap
     *
     * @return string[]
     *
     * @noinspection PhpDocSignatureInspection
     * @noinspection PhpUnusedParameterInspection
     */
    protected function extractMessagesUtf8FromThrowableMap(array $throwableMap, ?int $flags = null) : array
    {
        // > mbstring is required to convert to UTF-8
        $isMbstring = extension_loaded('mbstring');
        if ( ! $isMbstring ) {
            return array_keys($throwableMap);
        }

        // > unix/mac works well with non-utf8 strings in terminal
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        if ( ! $isWindows ) {
            return array_keys($throwableMap);
        }

        $isMbstringSupportsEncodingListWithCp1251 = (PHP_VERSION_ID >= 80100);

        foreach ( $throwableMap as $eMessage => $throwable ) {
            $isUtf8 = (1 === preg_match('//u', $eMessage));
            if ( $isUtf8 ) {
                continue;
            }

            $eMessageUtf8 = $eMessage;

            if ( $isMbstringSupportsEncodingListWithCp1251 ) {
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
                if ( $throwable instanceof \PDOException ) {
                    $eMessageUtf8 = mb_convert_encoding(
                        $eMessage,
                        'UTF-8',
                        'CP1251'
                    );
                }
            }

            unset($throwableMap[$eMessage]);

            $throwableMap[$eMessageUtf8] = $throwable;
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
            $lines[$i] = $padding . $line;
        }

        return $lines;
    }


    protected function flagsDefault(?int $flags) : int
    {
        $flags = $flags ?? 0;

        $flagGroups = [
            '_DEBUG_THROWABLER_WITH_CODE'  => [
                [
                    _DEBUG_THROWABLER_WITH_CODE,
                    _DEBUG_THROWABLER_WITHOUT_CODE,
                ],
                _DEBUG_THROWABLER_WITHOUT_CODE,
            ],
            //
            '_DEBUG_THROWABLER_WITH_INFO'  => [
                [
                    _DEBUG_THROWABLER_WITH_INFO,
                    _DEBUG_THROWABLER_WITHOUT_INFO,
                ],
                _DEBUG_THROWABLER_WITH_INFO,
            ],
            //
            '_DEBUG_THROWABLER_WITH_TRACE' => [
                [
                    _DEBUG_THROWABLER_WITH_TRACE,
                    _DEBUG_THROWABLER_WITHOUT_TRACE,
                ],
                _DEBUG_THROWABLER_WITHOUT_TRACE,
            ],
        ];

        foreach ( $flagGroups as $groupName => [$conflict, $default] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ( $flags & $flag ) {
                    $cnt++;
                }
            }

            if ( $cnt > 1 ) {
                throw new LogicException(
                    [ 'The `flags` conflict in group: ' . $groupName, $flags ]
                );

            } elseif ( 0 === $cnt ) {
                $flags |= $default;
            }
        }

        return $flags;
    }

    protected function flagsInfoDefault(?int $flags) : int
    {
        $flags = $flags ?? 0;

        $flagGroups = [
            '_DEBUG_THROWABLER_INFO_WITH_FILE'         => [
                [
                    _DEBUG_THROWABLER_INFO_WITH_FILE,
                    _DEBUG_THROWABLER_INFO_WITHOUT_FILE,
                ],
                _DEBUG_THROWABLER_INFO_WITH_FILE,
            ],
            //
            '_DEBUG_THROWABLER_INFO_WITH_OBJECT_CLASS' => [
                [
                    _DEBUG_THROWABLER_INFO_WITH_OBJECT_CLASS,
                    _DEBUG_THROWABLER_INFO_WITHOUT_OBJECT_CLASS,
                ],
                _DEBUG_THROWABLER_INFO_WITH_OBJECT_CLASS,
            ],
            //
            '_DEBUG_THROWABLER_INFO_WITH_OBJECT_ID'    => [
                [
                    _DEBUG_THROWABLER_INFO_WITH_OBJECT_ID,
                    _DEBUG_THROWABLER_INFO_WITHOUT_OBJECT_ID,
                ],
                _DEBUG_THROWABLER_INFO_WITHOUT_OBJECT_ID,
            ],
        ];

        foreach ( $flagGroups as $groupName => [$conflict, $default] ) {
            $cnt = 0;
            foreach ( $conflict as $flag ) {
                if ( $flags & $flag ) {
                    $cnt++;
                }
            }

            if ( $cnt > 1 ) {
                throw new LogicException(
                    [ 'The `flags` conflict in group: ' . $groupName, $flags ]
                );

            } elseif ( 0 === $cnt ) {
                $flags |= $default;
            }
        }

        return $flags;
    }
}
