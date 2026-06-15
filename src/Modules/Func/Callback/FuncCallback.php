<?php

namespace Gzhegow\Lib\Modules\Func\Callback;

use Gzhegow\Lib\Exception\LogicException;


class FuncCallback
{
    /**
     * @var callable
     */
    protected $fn;

    /**
     * @var object|class-string|null
     */
    protected $newScope;
    /**
     * @var object|null
     */
    protected $newThis;

    /**
     * @var array
     */
    protected $bindArgs;
    /**
     * @var array
     */
    protected $useArgs;

    /**
     * @var bool
     */
    protected $isInternal;
    /**
     * @var bool
     */
    protected $isSafe;

    /**
     * @var bool
     */
    protected $toBoolean;
    /**
     * @var bool
     */
    protected $toBooleanNot;

    /**
     * @var int
     */
    protected $throttleMs;

    /**
     * @var \Closure
     */
    protected $callback;


    /**
     * @param callable $fn
     *
     * @return static
     */
    public static function new($fn)
    {
        $instance = new static();

        $instance->fn = $fn;

        return $instance;
    }


    public function __invoke(...$args)
    {
        $callback = $this->callback ?? $this->make();

        return $callback(...$args);
    }


    /**
     * @param object|class-string|null $newScope
     *
     * @return static
     */
    public function newScope($newScope = null)
    {
        $this->newScope = $newScope;

        return $this;
    }

    /**
     * @return static
     */
    public function newThis(?object $newThis = null)
    {
        $this->newThis = $newThis;

        return $this;
    }


    /**
     * @return static
     */
    public function bind(?array $bindArgs = null)
    {
        $bindArgsList = $bindArgs;

        if ( null !== $bindArgs ) {
            $bindArgsList = array_values($bindArgs);
        }

        $this->bindArgs = $bindArgsList;

        return $this;
    }

    public function use(?array $useArgs = null)
    {
        $useArgsFiltered = $useArgs;

        if ( null !== $useArgs ) {
            $useArgsFiltered = [];

            foreach ( array_values($useArgs) as $i => $v ) {
                if ( null !== $v ) {
                    $useArgsFiltered[$i] = $v;
                }
            }
        }

        $this->useArgs = $useArgsFiltered;

        return $this;
    }


    public function setInternal(?bool $enable = null)
    {
        $enable = $enable ?? true;

        $this->isInternal = $enable;

        return $this;
    }


    public function setSafe(?bool $enable = null)
    {
        $enable = $enable ?? true;

        $this->isSafe = $enable;

        return $this;
    }

    /**
     * @throws \ErrorException
     */
    public function safe_error_handler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
    }


    public function toBool(?bool $enable = null)
    {
        $enable = $enable ?? true;

        $this->toBoolean = $enable ? true : null;
        $this->toBooleanNot = $enable ? false : null;

        return $this;
    }

    public function toBoolNot(?bool $enable = null)
    {
        $enable = $enable ?? true;

        $this->toBoolean = $enable ? true : null;
        $this->toBooleanNot = $enable ? true : null;

        return $this;
    }


    public function setThrottle(int $throttleMs)
    {
        if ( $throttleMs < 1 ) {
            throw new LogicException(
                [ 'The `throttleMs` should be greater than 1', $throttleMs ]
            );
        }

        $this->throttleMs = $throttleMs;

        return $this;
    }


    public function make() : \Closure
    {
        if ( null !== $this->callback ) {
            return $this->callback;
        }

        $fn = $this->fn;

        $bindArgs = $this->bindArgs;
        $hasBindArgs = (null !== $this->bindArgs);

        $useArgs = $this->useArgs;
        $hasUseArgs = (null !== $this->useArgs);

        $isInternal = $this->isInternal;
        $isSafe = $this->isSafe;

        $toBoolean = $this->toBoolean;
        $toBooleanNot = $this->toBooleanNot;

        $throttleMs = $this->throttleMs;
        $hasThrottleMs = (null !== $this->throttleMs);

        $internalMsgKnownMap = null;
        if ( $isInternal ) {
            $internalMsgKnownMap = [
                '() expects exactly '  => 19,
                '() expects at most '  => 19,
                '() expects at least ' => 20,
            ];
        }

        $safeErrorHandler = null;
        if ( $isSafe ) {
            $safeErrorHandler = [ $this, 'safe_error_handler' ];
        }

        $refThrottleLastCallMicrotime = null;
        $throttleSec = null;
        if ( $hasThrottleMs ) {
            $throttleSec = $throttleMs / 1000;
        }

        if ( ! (true
            && (null === $this->newThis)
            && (null === $this->newScope)
        ) ) {
            $fn = \Closure::bind($fn, $this->newThis, $this->newScope);
        }

        $callback = function (...$args) use (
            $fn,
            $hasBindArgs, $bindArgs,
            $hasUseArgs, $useArgs,
            $isInternal, $internalMsgKnownMap,
            $isSafe, $safeErrorHandler,
            $toBoolean, $toBooleanNot,
            $hasThrottleMs, $throttleSec, &$refThrottleLastCallMicrotime
        ) {
            if ( $hasThrottleMs ) {
                $now = microtime(true);

                if ( ($now - $refThrottleLastCallMicrotime) < $throttleSec ) {
                    return [];

                } else {
                    $refThrottleLastCallMicrotime = $now;
                }
            }

            $n = 0;
            $fnArgs = [];
            if ( $hasBindArgs ) {
                foreach ( $bindArgs as $i => $v ) {
                    $fnArgs[$n++] =& $bindArgs[$i];
                }
            }
            foreach ( $args as $i => $v ) {
                if ( null === $v ) {
                    if ( $hasUseArgs && isset($useArgs[$i]) ) {
                        $fnArgs[$n++] =& $useArgs[$i];

                    } else {
                        $fnArgs[$n++] =& $args[$i];
                    }

                } else {
                    $fnArgs[$n++] =& $args[$i];
                }
            }

            if ( $isSafe ) {
                $beforeErrorReporting = error_reporting(E_ALL | E_DEPRECATED | E_USER_DEPRECATED);
                $beforeErrorHandler = set_error_handler($safeErrorHandler);
            }

            error_clear_last();

            $ex = null;
            $e = null;
            try {
                $result = call_user_func_array($fn, $fnArgs);
            }
            catch ( \Throwable $ex ) {
                if ( ! $isInternal ) {
                    throw $ex;
                }
            }
            finally {
                if ( $isSafe ) {
                    set_error_handler($beforeErrorHandler);
                    error_reporting($beforeErrorReporting);
                }
            }

            $eMsg = null
                ?? ($ex ? $ex->getMessage() : null)
                ?? (($e = error_get_last()) ? $e['message'] : null);

            if ( $isInternal ) {
                if ( $ex || $e ) {
                    $isKnown = false;
                    $eSubstrPos = null;
                    $eSubstrLen = null;
                    foreach ( $internalMsgKnownMap as $eSubstr => $eSubstrLen ) {
                        if ( false !== ($eSubstrPos = strpos($eMsg, $eSubstr)) ) {
                            $isKnown = true;

                            break;
                        }
                    }

                    if ( ! $isKnown ) {
                        throw $ex;
                    }

                    $max = (int) substr($eMsg, $eSubstrPos + $eSubstrLen);

                    array_splice($fnArgs, $max);

                    $result = call_user_func_array($fn, $fnArgs);
                }
            }

            if ( $toBoolean ) {
                $result = $toBooleanNot
                    ? (! $result)
                    : ((bool) $result);
            }

            return $hasThrottleMs ? [ $result ] : $result;
        };

        return $this->callback = $callback;
    }
}
