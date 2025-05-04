<?php

namespace Gzhegow\Lib\Modules\Debug\DebugBacktracer;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class DebugBacktracer implements DebugBacktracerInterface
{
    /**
     * @var array
     */
    protected $trace;

    /**
     * @var string
     */
    protected $dirRoot;

    /**
     * @var int
     */
    protected $options = DEBUG_BACKTRACE_IGNORE_ARGS;
    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var array
     */
    protected $of;
    /**
     * @var array
     */
    protected $ofStartsWith;

    /**
     * @var array
     */
    protected $filter;
    /**
     * @var array
     */
    protected $filterStartsWith;

    /**
     * @var array
     */
    protected $filterNot;
    /**
     * @var array
     */
    protected $filterNotStartsWith;


    /**
     * @return static
     */
    public function trace(?array $trace)
    {
        $this->trace = $trace;

        return $this;
    }


    /**
     * @return static
     */
    public function dirRoot(?string $dirRoot)
    {
        if (null !== $dirRoot) {
            if (! Lib::fs()->type_dirpath_realpath($realpath, $dirRoot)) {
                throw new LogicException(
                    [ 'The `rootDirectory` should be existing directory path', $dirRoot ]
                );
            }
        }

        $this->dirRoot = $realpath ?? null;

        return $this;
    }


    /**
     * @return static
     */
    public function options(?int $options)
    {
        if (null !== $options) {
            if ($options < 0) {
                $options = null;
            }
        }

        $this->options = $options ?? DEBUG_BACKTRACE_IGNORE_ARGS;

        return $this;
    }

    /**
     * @return static
     */
    public function limit(?int $limit)
    {
        if (null !== $limit) {
            if ($limit < 0) {
                $limit = null;
            }
        }

        $this->limit = $limit ?? 0;

        return $this;
    }


    /**
     * @return static
     */
    public function of(?array $of)
    {
        $_ofFunction = $of[ 'function' ] ?? $of[ 0 ] ?? null;
        $_ofClass = $of[ 'class' ] ?? $of[ 1 ] ?? null;
        $_ofFile = $of[ 'file' ] ?? $of[ 2 ] ?? null;

        $_ofType = $of[ 'type' ] ?? null;
        $_ofLine = $of[ 'line' ] ?? null;
        $_ofObject = $of[ 'object' ] ?? null;

        if (null !== $_ofFunction) $_ofFunction = strval($_ofFunction);
        if (null !== $_ofClass) $_ofClass = strval($_ofClass);
        if (null !== $_ofFile) $_ofFile = strval($_ofFile);
        if (null !== $_ofType) $_ofType = strval($_ofType);
        if (null !== $_ofLine) $_ofLine = intval($_ofLine);
        // if (null !== $_ofObject) $_ofObject = $_ofObject;

        $_of = [];
        $_of[ 'function' ] = $_ofFunction;
        $_of[ 'class' ] = $_ofClass;
        $_of[ 'file' ] = $_ofFile;
        $_of[ 'type' ] = $_ofType;
        $_of[ 'line' ] = $_ofLine;
        $_of[ 'object' ] = $_ofObject;

        $this->of = $_of;

        return $this;
    }

    /**
     * @return static
     */
    public function ofStartsWith(?array $of)
    {
        $_ofFunction = $of[ 'function' ] ?? $of[ 0 ] ?? null;
        $_ofClass = $of[ 'class' ] ?? $of[ 1 ] ?? null;
        $_ofFile = $of[ 'file' ] ?? $of[ 2 ] ?? null;

        if (null !== $_ofFunction) $_ofFunction = strval($_ofFunction);
        if (null !== $_ofClass) $_ofClass = strval($_ofClass);
        if (null !== $_ofFile) $_ofFile = strval($_ofFile);

        $_of = [];
        $_of[ 'function' ] = $_ofFunction ?? "\0";
        $_of[ 'class' ] = $_ofClass ?? "\0";
        $_of[ 'file' ] = $_ofFile ?? "\0";

        $this->ofStartsWith = $_of;

        return $this;
    }


    /**
     * @return static
     */
    public function filter(?array $filter)
    {
        $theType = Lib::type();


        $_filterFile = $filter[ 'file' ] ?? $filter[ 0 ] ?? [];
        $_filterClass = $filter[ 'class' ] ?? $filter[ 1 ] ?? [];
        $_filterFunction = $filter[ 'function' ] ?? $filter[ 2 ] ?? [];

        $_filterType = $filter[ 'type' ] ?? [];
        $_filterLine = $filter[ 'line' ] ?? [];
        $_filterObject = $filter[ 'object' ] ?? [];


        $_filterFunction = null
            ?? (is_array($_filterFunction) ? $_filterFunction : null)
            ?? ((is_string($_filterFunction) && strlen($_filterFunction)) ? [ $_filterFunction ] : null)
            ?? [];

        $_filterClass = null
            ?? (is_array($_filterClass) ? $_filterClass : null)
            ?? ((is_string($_filterClass) && strlen($_filterClass)) ? [ $_filterClass ] : null)
            ?? [];

        $_filterFile = null
            ?? (is_array($_filterFile) ? $_filterFile : null)
            ?? ((is_string($_filterFile) && strlen($_filterFile)) ? [ $_filterFile ] : null)
            ?? [];


        $_filterType = null
            ?? (is_array($_filterType) ? $_filterType : null)
            ?? ((is_string($_filterType) && strlen($_filterType)) ? [ $_filterType ] : null)
            ?? [];

        $_filterLine = null
            ?? (is_array($_filterLine) ? $_filterLine : null)
            ?? ($theType->numeric($var, $_filterLine) ? [ $var ] : null)
            ?? [];

        $_filterObject = null
            ?? (is_array($_filterObject) ? $_filterObject : null)
            ?? (is_object($_filterObject) ? [ $_filterObject ] : null)
            ?? [];


        $_filter = [];

        $_filter[ 'function' ] = array_map('strval', $_filterFunction);
        $_filter[ 'class' ] = array_map('strval', $_filterClass);
        $_filter[ 'file' ] = array_map('strval', $_filterFile);

        $_filter[ 'type' ] = array_map('strval', $_filterType);
        $_filter[ 'line' ] = array_map('intval', $_filterLine);
        $_filter[ 'object' ] = $_filterObject;


        $this->filter = $_filter;

        return $this;
    }

    /**
     * @return static
     */
    public function filterStartsWith(?array $filter)
    {
        $_filterFile = $filter[ 'file' ] ?? $filter[ 0 ] ?? [];
        $_filterClass = $filter[ 'class' ] ?? $filter[ 1 ] ?? [];
        $_filterFunction = $filter[ 'function' ] ?? $filter[ 2 ] ?? [];

        $_filterFunction = null
            ?? (is_array($_filterFunction) ? $_filterFunction : null)
            ?? ((is_string($_filterFunction) && strlen($_filterFunction)) ? [ $_filterFunction ] : null)
            ?? [];

        $_filterClass = null
            ?? (is_array($_filterClass) ? $_filterClass : null)
            ?? ((is_string($_filterClass) && strlen($_filterClass)) ? [ $_filterClass ] : null)
            ?? [];

        $_filterFile = null
            ?? (is_array($_filterFile) ? $_filterFile : null)
            ?? ((is_string($_filterFile) && strlen($_filterFile)) ? [ $_filterFile ] : null)
            ?? [];

        $_filterFunction = array_map('strval', $_filterFunction);
        $_filterClass = array_map('strval', $_filterClass);
        $_filterFile = array_map('strval', $_filterFile);

        foreach ( $_filterFunction as $i => $v ) {
            if ('' === $v) {
                $_filterFunction[ $i ] = "\0";
            }
        }
        foreach ( $_filterClass as $i => $v ) {
            if ('' === $v) {
                $_filterClass[ $i ] = "\0";
            }
        }
        foreach ( $_filterFile as $i => $v ) {
            if ('' === $v) {
                $_filterFile[ $i ] = "\0";
            }
        }

        $_filter = [];
        $_filter[ 'function' ] = $_filterFunction;
        $_filter[ 'class' ] = $_filterClass;
        $_filter[ 'file' ] = $_filterFile;

        $this->filterStartsWith = $_filter;

        return $this;
    }


    /**
     * @return static
     */
    public function filterNot(?array $filter)
    {
        $theType = Lib::type();


        $_filterFile = $filter[ 'file' ] ?? $filter[ 0 ] ?? [];
        $_filterClass = $filter[ 'class' ] ?? $filter[ 1 ] ?? [];
        $_filterFunction = $filter[ 'function' ] ?? $filter[ 2 ] ?? [];

        $_filterType = $filter[ 'type' ] ?? [];
        $_filterLine = $filter[ 'line' ] ?? [];
        $_filterObject = $filter[ 'object' ] ?? [];


        $_filterFunction = null
            ?? (is_array($_filterFunction) ? $_filterFunction : null)
            ?? ((is_string($_filterFunction) && strlen($_filterFunction)) ? [ $_filterFunction ] : null)
            ?? [];

        $_filterClass = null
            ?? (is_array($_filterClass) ? $_filterClass : null)
            ?? ((is_string($_filterClass) && strlen($_filterClass)) ? [ $_filterClass ] : null)
            ?? [];

        $_filterFile = null
            ?? (is_array($_filterFile) ? $_filterFile : null)
            ?? ((is_string($_filterFile) && strlen($_filterFile)) ? [ $_filterFile ] : null)
            ?? [];


        $_filterType = null
            ?? (is_array($_filterType) ? $_filterType : null)
            ?? ((is_string($_filterType) && strlen($_filterType)) ? [ $_filterType ] : null)
            ?? [];

        $_filterLine = null
            ?? (is_array($_filterLine) ? $_filterLine : null)
            ?? ($theType->numeric($var, $_filterLine) ? [ $var ] : null)
            ?? [];

        $_filterObject = null
            ?? (is_array($_filterObject) ? $_filterObject : null)
            ?? (is_object($_filterObject) ? [ $_filterObject ] : null)
            ?? [];


        $_filter = [];

        $_filter[ 'function' ] = array_map('strval', $_filterFunction);
        $_filter[ 'class' ] = array_map('strval', $_filterClass);
        $_filter[ 'file' ] = array_map('strval', $_filterFile);

        $_filter[ 'type' ] = array_map('strval', $_filterType);
        $_filter[ 'line' ] = array_map('intval', $_filterLine);
        $_filter[ 'object' ] = $_filterObject;


        $this->filterNot = $_filter;

        return $this;
    }

    /**
     * @return static
     */
    public function filterNotStartsWith(?array $filter)
    {
        $_filterFile = $filter[ 'file' ] ?? $filter[ 0 ] ?? [];
        $_filterClass = $filter[ 'class' ] ?? $filter[ 1 ] ?? [];
        $_filterFunction = $filter[ 'function' ] ?? $filter[ 2 ] ?? [];

        $_filterFunction = null
            ?? (is_array($_filterFunction) ? $_filterFunction : null)
            ?? ((is_string($_filterFunction) && strlen($_filterFunction)) ? [ $_filterFunction ] : null)
            ?? [];

        $_filterClass = null
            ?? (is_array($_filterClass) ? $_filterClass : null)
            ?? ((is_string($_filterClass) && strlen($_filterClass)) ? [ $_filterClass ] : null)
            ?? [];

        $_filterFile = null
            ?? (is_array($_filterFile) ? $_filterFile : null)
            ?? ((is_string($_filterFile) && strlen($_filterFile)) ? [ $_filterFile ] : null)
            ?? [];

        $_filterFunction = array_map('strval', $_filterFunction);
        $_filterClass = array_map('strval', $_filterClass);
        $_filterFile = array_map('strval', $_filterFile);

        foreach ( $_filterFunction as $i => $v ) {
            if ('' === $v) {
                $_filterFunction[ $i ] = "\0";
            }
        }
        foreach ( $_filterClass as $i => $v ) {
            if ('' === $v) {
                $_filterClass[ $i ] = "\0";
            }
        }
        foreach ( $_filterFile as $i => $v ) {
            if ('' === $v) {
                $_filterFile[ $i ] = "\0";
            }
        }

        $_filter = [];
        $_filter[ 'function' ] = $_filterFunction;
        $_filter[ 'class' ] = $_filterClass;
        $_filter[ 'file' ] = $_filterFile;

        $this->filterNotStartsWith = $_filter;

        return $this;
    }


    /**
     * @return array<int, array{
     *     file: string,
     *     line: string,
     *     class: string|null,
     *     function: string|null,
     *     type: string|null,
     *     object: object|null,
     *     args: array|null,
     * }>
     */
    public function get() : array
    {
        $trace = $this->execute();

        return $trace;
    }

    /**
     * @return array{ 0: string, 1: int }|null
     */
    public function getFileLine() : ?array
    {
        $trace = $this->execute();

        if ([] !== $trace) {
            return [
                $trace[ 0 ][ 'file' ],
                $trace[ 0 ][ 'line' ],
            ];
        }

        return null;
    }


    protected function execute() : array
    {
        $theFs = Lib::fs();

        $hasDirRoot = (null !== ($dirRoot = $this->dirRoot));

        $trace = $this->trace;

        $options = null;
        $limit = null;
        if (null === $trace) {
            $skip = 2;

            $options = $this->options;
            $limit = $this->limit;

            if ($limit > 0) {
                $limit += $skip;
            }

            $trace = debug_backtrace($options, $limit);

            array_splice($trace, 0, $skip);
        }

        $hasOf = (null !== ($of = $this->of));
        $hasOfStartsWith = (null !== ($ofStartsWith = $this->ofStartsWith));
        $hasFilter = (null !== ($filter = $this->filter));
        $hasFilterStartsWith = (null !== ($filterStartsWith = $this->filterStartsWith));
        $hasFilterNot = (null !== ($filterNot = $this->filterNot));
        $hasFilterNotStartsWith = (null !== ($filterNotStartsWith = $this->filterNotStartsWith));

        foreach ( $trace as $i => $t ) {
            $t[ 'file' ] = $t[ 'file' ] ?? null;
            $t[ 'line' ] = $t[ 'line' ] ?? null;
            $t[ 'class' ] = $t[ 'class' ] ?? null;
            $t[ 'function' ] = $t[ 'function' ] ?? null;
            $t[ 'type' ] = $t[ 'type' ] ?? null;
            $t[ 'object' ] = $t[ 'object' ] ?? null;

            if ($hasOf || $hasOfStartsWith) {
                $tFile = $t[ 'file' ];
                $tLine = $t[ 'line' ];
                $tClass = $t[ 'class' ];
                $tFunction = $t[ 'function' ];
                $tType = $t[ 'type' ];
                $tObject = $t[ 'object' ];

                if ($hasOf) {
                    $hasObject = (null !== $of[ 'object' ]);

                    if ($hasObject
                        && (null !== $options)
                        && ! ($options & DEBUG_BACKTRACE_PROVIDE_OBJECT)
                    ) {
                        throw new RuntimeException(
                            'Unable to `of` by object if DEBUG_BACKTRACE_PROVIDE_OBJECT is not in `options`'
                        );
                    }

                    if (
                        ((null !== $of[ 'function' ]) && ($of[ 'function' ] !== $tFunction))
                        || ((null !== $of[ 'class' ]) && ($of[ 'class' ] !== $tClass))
                        || ((null !== $of[ 'file' ]) && ($of[ 'file' ] !== $tFile))
                        || ((null !== $of[ 'type' ]) && ($of[ 'type' ] !== $tType))
                        || ((null !== $of[ 'line' ]) && ($of[ 'line' ] !== $tLine))
                        || ($hasObject && ($of[ 'object' ] !== $tObject))
                    ) {
                        unset($trace[ $i ]);

                        continue;
                    }
                }

                if ($hasOfStartsWith) {
                    if (
                        (0 !== stripos($tFunction, $ofStartsWith[ 'function' ]))
                        || (0 !== stripos($tClass, $ofStartsWith[ 'class' ]))
                        || (0 !== stripos($tFile, $ofStartsWith[ 'file' ]))
                    ) {
                        unset($trace[ $i ]);

                        continue;
                    }
                }

                $hasOf = false;
                $hasOfStartsWith = false;
            }

            if ($filter) {
                $hasObject = ([] !== $filter[ 'object' ]);

                if ($hasObject
                    && (null !== $options)
                    && ! ($options & DEBUG_BACKTRACE_PROVIDE_OBJECT)
                ) {
                    throw new RuntimeException(
                        'Unable to `filter` by object if DEBUG_BACKTRACE_PROVIDE_OBJECT is not in `options`'
                    );
                }

                $keys = [
                    'file',
                    'class',
                    'function',
                    'type',
                    'line',
                    'object',
                ];

                foreach ( $keys as $key ) {
                    if ([] === $filter[ $key ]) {
                        continue;
                    }

                    $isFound = false;
                    foreach ( $filter[ $key ] as $v ) {
                        if ($v === $t[ $key ]) {
                            $isFound = true;

                            break;
                        }
                    }

                    if (! $isFound) {
                        unset($trace[ $i ]);

                        continue 2;
                    }
                }
            }

            if ($filterStartsWith) {
                $keys = [
                    'file',
                    'class',
                    'function',
                ];

                foreach ( $keys as $key ) {
                    if ([] === $filterStartsWith[ $key ]) {
                        continue;
                    }

                    $isFound = false;
                    foreach ( $filterStartsWith[ $key ] as $v ) {
                        if (0 === stripos($t[ $key ], $v)) {
                            $isFound = true;

                            break;
                        }
                    }

                    if (! $isFound) {
                        unset($trace[ $i ]);

                        continue 2;
                    }
                }
            }

            if ($filterNot) {
                $hasObject = ([] !== $filterNot[ 'object' ]);

                if ($hasObject
                    && (null !== $options)
                    && ! ($options & DEBUG_BACKTRACE_PROVIDE_OBJECT)
                ) {
                    throw new RuntimeException(
                        'Unable to `filterNot` by object if DEBUG_BACKTRACE_PROVIDE_OBJECT is not in `options`'
                    );
                }

                $keys = [
                    'file',
                    'class',
                    'function',
                    'type',
                    'line',
                    'object',
                ];

                foreach ( $keys as $key ) {
                    if ([] === $filterNot[ $key ]) {
                        continue;
                    }

                    foreach ( $filterNot[ $key ] as $v ) {
                        if ($v === $t[ $key ]) {
                            unset($trace[ $i ]);

                            continue 3;
                        }
                    }
                }
            }

            if ($filterNotStartsWith) {
                $keys = [
                    'file',
                    'class',
                    'function',
                ];

                foreach ( $keys as $key ) {
                    if ([] === $filterNotStartsWith[ $key ]) {
                        continue;
                    }

                    foreach ( $filterNotStartsWith[ $key ] as $v ) {
                        if (0 === stripos($t[ $key ], $v)) {
                            unset($trace[ $i ]);

                            continue 3;
                        }
                    }
                }
            }

            if ($hasDirRoot) {
                $t[ 'file' ] = $theFs->path_relative($t[ 'file' ], $dirRoot);
            }

            $t += [
                'file'     => null,
                'line'     => null,
                'class'    => null,
                'function' => null,
                'type'     => null,
                'object'   => null,
                'args'     => null,
            ];

            $trace[ $i ] = $t;
        }

        return $trace;
    }
}
