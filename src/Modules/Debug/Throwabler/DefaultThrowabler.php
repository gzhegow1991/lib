<?php

/** @noinspection PhpComposerExtensionStubsInspection */

namespace Gzhegow\Lib\Modules\Debug\Throwabler;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\ExceptInterface;
use Gzhegow\Lib\Exception\Iterator\ExceptionIterator;
use Gzhegow\Lib\Exception\Interfaces\HasMessageListInterface;
use Gzhegow\Lib\Exception\Interfaces\HasTraceOverrideInterface;


class DefaultThrowabler implements ThrowablerInterface
{
    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return \Traversable<string, (\Throwable|ExceptInterface)[]>
     */
    public function getPreviousIterator($throwable) : \Traversable
    {
        $it = ExceptionIterator::new([ $throwable ]);

        $iit = new \RecursiveIteratorIterator($it);

        foreach ( $iit as $track ) {
            foreach ( $track as $dotpath => $e ) {
                yield $dotpath => $e;
            }
        }
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return \Traversable<string, (\Throwable|ExceptInterface)[]>
     */
    public function getPreviousTreeIterator($throwable) : \Traversable
    {
        $it = ExceptionIterator::new([ $throwable ]);

        $iit = new \RecursiveIteratorIterator($it);

        $visited = [];

        foreach ( $iit as $track ) {
            foreach ( $track as $dotpath => $e ) {
                if ( isset($visited[$dotpath]) ) {
                    continue;
                }

                yield $dotpath => $e;

                $visited[$dotpath] = true;
            }
        }
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return \Traversable<string, (\Throwable|ExceptInterface)[]>
     */
    public function getPreviousUniqueIterator($throwable) : \Traversable
    {
        $it = ExceptionIterator::new([ $throwable ]);

        $iit = new \RecursiveIteratorIterator($it);

        $visitedMap = new \SplObjectStorage();

        foreach ( $iit as $track ) {
            foreach ( $track as $dotpath => $e ) {
                if ( $visitedMap->contains($e) ) {
                    continue;
                }

                yield $dotpath => $e;

                $visitedMap->attach($e);
            }
        }
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return \Traversable<int, array<string, (\Throwable|ExceptInterface)[]>>
     */
    public function getPreviousTrackIterator($throwable) : \Traversable
    {
        $it = ExceptionIterator::new([ $throwable ]);

        $iit = new \RecursiveIteratorIterator($it);

        return $iit;
    }


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

        $it = $this->getPreviousUniqueIterator($throwable);

        foreach ( $it as $e ) {
            if ( $e instanceof $throwableClass ) {
                return $e;
            }
        }

        return null;
    }


    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getArray($throwable, ?int $flags = null) : array
    {
        $it = $this->getPreviousIterator($throwable);

        $array = [];

        foreach ( $it as $dotpath => $e ) {
            $array[$dotpath] = $this->getThrowableArray($e, $flags);
        }

        return $array;
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getLines($throwable, ?int $flags = null) : array
    {
        $flags = $this->flagsDefault($flags);

        $isWithInfo = $flags & _DEBUG_THROWABLER_WITH_INFO;
        $isWithTrace = $flags & _DEBUG_THROWABLER_WITH_TRACE;

        $lines = [];

        $array = $this->getPreviousTreeIterator($throwable);

        foreach ( $array as $dotpath => $e ) {
            $messagesLines = $this->getThrowableMessagesLines($e, $flags);

            if ( [] === $messagesLines ) {
                continue;
            }

            if ( count($messagesLines) > 1 ) {
                $messagesLines = array_merge(
                    [ "[ >>> {$dotpath} ]" ],
                    $messagesLines,
                    [ "[ <<< {$dotpath} ]" ],
                );

            } else {
                $messagesLines = [ "[ {$dotpath} ] {$messagesLines[ 0 ]}" ];
            }

            if ( $isWithInfo ) {
                $linesInfo = $this->getThrowableInfoLines($e, $flags);

                $messagesLines = array_merge(
                    $messagesLines,
                    $linesInfo
                );
            }

            if ( $isWithTrace ) {
                $linesTrace = $this->getThrowableTraceLines($e, $flags);

                if ( [] !== $linesTrace ) {
                    if ( [] !== $messagesLines ) {
                        $messagesLines[] = '';
                    }

                    $messagesLines[] = 'Trace:';
                    $messagesLines = array_merge(
                        $messagesLines,
                        $linesTrace
                    );
                }
            }

            if ( $isWithInfo || $isWithTrace ) {
                if ( [] !== $lines ) {
                    array_unshift($messagesLines, '');
                }
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
        }

        return $lines;
    }


    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getMessagesArray($throwable, ?int $flags = null) : array
    {
        $it = $this->getPreviousIterator($throwable);

        $array = [];

        foreach ( $it as $dotpath => $e ) {
            $array[$dotpath] = $this->getThrowableMessagesArray($e, $flags);
        }

        return $array;
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getMessagesLines($throwable, ?int $flags = null) : array
    {
        $lines = [];

        $array = $this->getPreviousTreeIterator($throwable);

        foreach ( $array as $dotpath => $e ) {
            $messagesLines = $this->getThrowableMessagesLines($e, $flags);

            if ( [] === $messagesLines ) {
                continue;
            }

            if ( count($messagesLines) > 1 ) {
                $messagesLines = array_merge(
                    [ "[ >>> {$dotpath} ]" ],
                    $messagesLines,
                    [ "[ <<< {$dotpath} ]" ],
                );

            } else {
                $messagesLines[0] = "[ {$dotpath} ] {$messagesLines[ 0 ]}";
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
        }

        return $lines;
    }


    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableArray($throwable, ?int $flags = null) : array
    {
        $array = [
            'messages' => $this->getThrowableMessagesArray($throwable, $flags),
            'info'     => $this->getThrowableInfoArray($throwable, $flags),
            'trace'    => $this->getThrowableTraceArray($throwable, $flags),
        ];

        return $array;
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableLines($throwable, ?int $flags = null) : array
    {
        $flags = $this->flagsDefault($flags);

        $isWithInfo = $flags & _DEBUG_THROWABLER_WITH_INFO;
        $isWithTrace = $flags & _DEBUG_THROWABLER_WITH_TRACE;


        $lines = [];


        $linesMessages = $this->getThrowableMessagesLines($throwable, $flags);

        $lines = array_merge(
            $lines,
            $linesMessages
        );


        if ( $isWithInfo ) {
            $linesInfo = $this->getThrowableInfoLines($throwable, $flags);

            if ( [] !== $linesInfo ) {
                $lines = array_merge(
                    $lines,
                    $linesInfo
                );
            }
        }


        if ( $isWithTrace ) {
            $linesTrace = $this->getThrowableTraceLines($throwable, $flags);

            if ( [] !== $linesTrace ) {
                if ( [] !== $lines ) {
                    $lines[] = '';
                }

                $lines[] = 'Trace:';

                $lines = array_merge(
                    $lines,
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
    public function getThrowableMessagesArray($throwable, ?int $flags = null) : array
    {
        if ( $throwable instanceof HasMessageListInterface ) {
            $throwableMap = array_fill_keys(
                $throwable->getMessageList(),
                $throwable
            );

        } else {
            $throwableMap = [
                $throwable->getMessage() => $throwable,
            ];
        }

        $eMessages = $this->extractMessagesUtf8FromThrowableMap($throwableMap, $flags);

        return $eMessages;
    }

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableMessagesLines($throwable, ?int $flags = null) : array
    {
        $flags = $this->flagsDefault($flags);

        $isWithCode = $flags & _DEBUG_THROWABLER_WITH_CODE;

        $lines = [];

        $eMessages = $this->getThrowableMessagesArray($throwable, $flags);

        foreach ( $eMessages as $eMessage ) {
            if ( $isWithCode ) {
                $eMessage = ''
                    . 'CODE[' . $throwable->getCode() . ']'
                    . ' '
                    . $eMessage;
            }

            $linesCur = explode("\n", $eMessage);
            $linesCur = array_map('rtrim', $linesCur);
            $linesCur = array_filter($linesCur);

            if ( count($linesCur) > 1 ) {
                if ( [] !== $lines ) {
                    $lines[] = '';
                }
            }

            $lines = array_merge(
                $lines,
                $linesCur
            );
        }

        return $lines;
    }


    /**
     * @param \Throwable|ExceptInterface $throwable
     */
    public function getThrowableInfoArray($throwable, ?int $flags = null) : array
    {
        $theDebug = Lib::debug();

        $dirRoot = $theDebug::staticDirRoot();

        if ( $throwable instanceof HasTraceOverrideInterface ) {
            $eFile = $throwable->getFileOverride($dirRoot) ?? $throwable->getFile();
            $eLine = $throwable->getLineOverride() ?? $throwable->getLine();

        } else {
            $eFile = $throwable->getFile();
            $eLine = $throwable->getLine();
        }

        $eObjectClass = get_class($throwable);
        $eObjectId = spl_object_id($throwable);

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
            $theDebug = Lib::debug();

            $dirRoot = $theDebug::staticDirRoot();

            if ( $throwable instanceof HasTraceOverrideInterface ) {
                $eFile = $throwable->getFileOverride($dirRoot) ?? $throwable->getFile();
                $eLine = $throwable->getLineOverride() ?? $throwable->getLine();

            } else {
                $eFile = $throwable->getFile();
                $eLine = $throwable->getLine();
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

        $dirRoot = $theDebug::staticDirRoot();

        if ( $throwable instanceof HasTraceOverrideInterface ) {
            $array = $throwable->getTraceOverride($dirRoot) ?? $throwable->getTrace();

        } else {
            $array = $throwable->getTrace();
        }

        return $array;
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
            $phpFile = $traceItem['file'] ?? '{{file}}';
            $phpLine = $traceItem['line'] ?? -1;

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
