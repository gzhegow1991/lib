<?php

namespace Gzhegow\Lib\Modules\Func\Pipe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\PipeException;


class Pipe
{
    /**
     * @var array{ ?: mixed }
     */
    protected $valueInitial = [];
    /**
     * @var array
     */
    protected $argsInitial = [];

    /**
     * @var bool
     */
    protected $hasValueInitial = false;
    /**
     * @var int|null
     */
    protected $keyValueInitial;

    /**
     * @var array{ ?: mixed }
     */
    protected $valueCurrent = [];
    /**
     * @var \Throwable|null
     */
    protected $throwableCurrent;

    /**
     * @var int
     */
    protected $queueId = -1;
    /**
     * @var array<int, array{ 0: callable, 1: array }>
     */
    protected $tapQueue = [];
    /**
     * @var array<int, array{ 0: callable, 1: array }>
     */
    protected $mapQueue = [];
    /**
     * @var array<int, array{ 0: callable, 1: array }>
     */
    protected $filterQueue = [];
    /**
     * @var array<int, array{ 0: callable, 1: array }>
     */
    protected $catchQueue = [];
    /**
     * @var array<int, array{ 0: \Throwable|null, 1: array{ 0?: mixed }, 2: class-string<\Throwable>|null }>
     */
    protected $catchToQueue = [];


    public function __invoke($value, ...$args)
    {
        if ([] !== $args) {
            array_unshift($args, null);

            unset($args[ 0 ]);
        }

        $result = $this->run([ 0 => $value ], $args);

        return $result;
    }


    protected function initialize(?array $value, ?array $args) : void
    {
        if (null !== $value) {
            $_value = self::sanitizeValue($value);

            $this->hasValueInitial = true;
            $this->keyValueInitial = key($_value);
            $this->valueInitial = $_value;
        }

        if (null !== $args) {
            $_args = self::sanitizeArgs($args);

            if (
                $this->hasValueInitial
                && (array_key_exists($this->keyValueInitial, $args))
            ) {
                $isResolved = false;

                if ($isZeroKey = (0 === $this->keyValueInitial)) {
                    if (Lib::arr()->type_list_sorted($listSorted, $_args)) {
                        array_unshift($_args, null);

                        unset($_args[ 0 ]);

                        $isResolved = true;
                    }
                }

                if (! $isResolved) {
                    throw new RuntimeException(
                        [
                            'Arguments intersection detected',
                            $value,
                            $args,
                        ]
                    );
                }
            }

            $this->argsInitial = $_args;
        }
    }


    /**
     * @return static
     */
    public function tap(callable $fn, array $args = [])
    {
        $_args = $this->sanitizeArgs($args);

        $this->queueId++;

        $this->tapQueue[ $this->queueId ] = [ $fn, $_args ];

        return $this;
    }

    /**
     * @return static
     */
    public function map(callable $fn, array $args = [])
    {
        $_args = $this->sanitizeArgs($args);

        $this->queueId++;

        $this->mapQueue[ $this->queueId ] = [ $fn, $_args ];

        return $this;
    }

    /**
     * @return static
     */
    public function filter(callable $fn, array $args = [])
    {
        $_args = $this->sanitizeArgs($args);

        $this->queueId++;

        $this->filterQueue[ $this->queueId ] = [ $fn, $_args ];

        return $this;
    }

    /**
     * @return static
     */
    public function catch(callable $fn, array $args = [])
    {
        $_args = $this->sanitizeArgs($args);

        if ([] !== $_args) {
            if (Lib::arr()->type_list_sorted($list, $_args)) {
                array_unshift($_args, null);

                unset($_args[ 0 ]);
            }
        }

        $this->queueId++;

        $this->catchQueue[ $this->queueId ] = [ $fn, $_args ];

        return $this;
    }

    /**
     * @template-covariant T of \Throwable
     *
     * @param T|null               $e
     * @param class-string<T>|null $throwableClass
     *
     * @return static
     */
    public function catchTo(?\Throwable &$e, array $result = [], ?string $throwableClass = null)
    {
        $resultSanitized = $this->sanitizeResult($result);

        if (null !== $throwableClass) {
            if (! is_subclass_of($throwableClass, \Throwable::class)) {
                throw new LogicException(
                    [ 'The `throwableClass` should be class-string of: ' . \Throwable::class ]
                );
            }
        }

        $this->queueId++;

        $this->catchToQueue[ $this->queueId ] = [ &$e, $resultSanitized, $throwableClass ];

        return $this;
    }


    /**
     * @return mixed
     */
    public function run(?array $value = null, ?array $args = null)
    {
        $this->initialize($value, $args);

        $this->valueCurrent = $this->valueInitial;
        $this->throwableCurrent = null;

        $before = error_reporting(0);

        for ( $i = 0; $i <= $this->queueId; $i++ ) {
            try {
                error_clear_last();

                $status = null
                    ?? (isset($this->tapQueue[ $i ]) ? $this->runTap($i) : null)
                    ?? (isset($this->mapQueue[ $i ]) ? $this->runMap($i) : null)
                    ?? (isset($this->filterQueue[ $i ]) ? $this->runFilter($i) : null)
                    ?? (isset($this->catchQueue[ $i ]) ? $this->runCatch($i) : null)
                    ?? (isset($this->catchToQueue[ $i ]) ? $this->runCatchTo($i) : null);

                if (null === $status) {
                    throw new PipeException(
                        [ 'Unable to run pipe', $i ]
                    );
                }

                $e = error_get_last();

                if (null !== $e) {
                    $this->valueCurrent = [];

                    $this->throwableCurrent = new \ErrorException(
                        $e[ 'message' ], -1,
                        $e[ 'type' ], $e[ 'file' ], $e[ 'line' ],
                    );
                }
            }
            catch ( PipeException $e ) {
                error_reporting($before);

                throw $e;
            }
            catch ( \Throwable $e ) {
                $this->valueCurrent = [];

                $this->throwableCurrent = $e;
            }
        }

        error_reporting($before);

        if ($this->throwableCurrent) {
            throw new PipeException(
                [ 'Unhandled exception in pipe' ], $this->throwableCurrent
            );
        }

        if ($this->hasValueInitial) {
            return $this->valueCurrent[ $this->keyValueInitial ] ?? null;
        }

        return null;
    }

    protected function runTap(int $i) : bool
    {
        if (null !== $this->throwableCurrent) {
            return false;
        }

        [ $fn, $argsFn ] = $this->tapQueue[ $i ];

        $argsValue = count($this->valueCurrent)
            ? $this->valueCurrent
            : [ $this->keyValueInitial => null ];

        [ $argsNew ] = Lib::func()->func_args_unique($argsFn, $argsValue, $this->argsInitial);

        call_user_func_array($fn, $argsNew);

        return true;
    }

    protected function runMap(int $i) : bool
    {
        if (! $this->hasValueInitial) {
            throw new PipeException(
                [ 'Unable to `' . __FUNCTION__ . '` due to no initial value' ]
            );
        }

        if (null !== $this->throwableCurrent) {
            return false;
        }

        [ $fn, $argsFn ] = $this->mapQueue[ $i ];

        $argsValue = count($this->valueCurrent)
            ? $this->valueCurrent
            : [ $this->keyValueInitial => null ];

        [ $argsNew ] = Lib::func()->func_args_unique($argsFn, $argsValue, $this->argsInitial);

        $result = call_user_func_array($fn, $argsNew);

        $this->valueCurrent[ $this->keyValueInitial ] = $result;

        return true;
    }

    protected function runFilter(int $i) : bool
    {
        if (! $this->hasValueInitial) {
            throw new PipeException(
                [ 'Unable to `' . __FUNCTION__ . '` due to no initial value' ]
            );
        }

        if (null !== $this->throwableCurrent) {
            return false;
        }

        [ $fn, $argsFn ] = $this->filterQueue[ $i ];

        $argsValue = count($this->valueCurrent)
            ? $this->valueCurrent
            : [ $this->keyValueInitial => null ];

        [ $argsNew ] = Lib::func()->func_args_unique($argsFn, $argsValue, $this->argsInitial);

        $status = (bool) call_user_func_array($fn, $argsNew);

        if (! $status) {
            $this->valueCurrent = [];
        }

        return true;
    }

    protected function runCatch(int $i) : bool
    {
        if (null === $this->throwableCurrent) {
            return false;
        }

        [ $fn, $argsFn ] = $this->catchQueue[ $i ];

        $argsThrowable = [ 0 => $this->throwableCurrent ];

        [ $argsNew ] = Lib::func()->func_args_unique($argsFn, $argsThrowable);

        $result = call_user_func_array($fn, $argsNew);

        if (! is_a($result, \Throwable::class)) {
            if ($this->hasValueInitial) {
                $this->valueCurrent[ $this->keyValueInitial ] = $result;
            }

            $this->throwableCurrent = null;
        }

        return true;
    }

    protected function runCatchTo(int $i) : bool
    {
        if (null === $this->throwableCurrent) {
            return false;
        }

        [ &$ref, $result, $throwableClass ] = $this->catchToQueue[ $i ];

        if (
            (null === $throwableClass)
            || ($this->throwableCurrent instanceof $throwableClass)
        ) {
            $ref = $this->throwableCurrent;

            if ($this->hasValueInitial) {
                if ([] === $result) {
                    $this->valueCurrent = [];

                } else {
                    $this->valueCurrent[ $this->keyValueInitial ] = $result;
                }
            }

            $this->throwableCurrent = null;
        }

        unset($ref);

        return true;
    }


    private function sanitizeValue($value) : array
    {
        if ([] === $value) {
            return [];
        }

        if (! is_array($value)) {
            throw new LogicException(
                [ 'The `value` should be array', $value ]
            );
        }

        $valueKey = array_key_first($value);

        if (is_string($valueKey)) {
            throw new LogicException(
                [ 'Each key of `args` should be integer', $valueKey, $value ]
            );
        }

        if ($valueKey < 0) {
            throw new LogicException(
                [ 'Each key of `args` should be positive integer', $valueKey, $value ]
            );
        }

        return [ $valueKey => $value[ $valueKey ] ];
    }

    private function sanitizeArgs($args) : array
    {
        if ([] === $args) {
            return [];
        }

        if (! is_array($args)) {
            throw new LogicException(
                [ 'The `args` should be array', $args ]
            );
        }

        foreach ( array_keys($args) as $i ) {
            if (is_string($i)) {
                throw new LogicException(
                    [ 'Each key of `args` should be integer', $i, $args ]
                );
            }

            if ($i < 0) {
                throw new LogicException(
                    [ 'Each key of `args` should be positive integer', $i, $args ]
                );
            }
        }

        return $args;
    }

    private function sanitizeResult($result) : array
    {
        if ([] === $result) {
            return [];
        }

        if (! is_array($result)) {
            throw new LogicException(
                [ 'The `result` should be array', $result ]
            );
        }

        if (! array_key_exists(0, $result)) {
            return [];
        }

        return [ 0 => $result[ 0 ] ];
    }
}
