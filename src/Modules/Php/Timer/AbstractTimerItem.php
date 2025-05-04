<?php

namespace Gzhegow\Lib\Modules\Php\Timer;

use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Loop\LoopManagerInterface;


abstract class AbstractTimerItem
{
    const STATE_PENDING   = 'pending';
    const STATE_POOLING   = 'pooling';
    const STATE_TIMEOUT   = 'timeout';
    const STATE_CANCELLED = 'cancelled';

    const LIST_STATE = [
        self::STATE_PENDING   => true,
        self::STATE_POOLING   => true,
        self::STATE_TIMEOUT   => true,
        self::STATE_CANCELLED => true,
    ];


    /**
     * @var callable
     */
    protected $fnHandler;
    /**
     * @var float
     */
    protected $waitMs;

    /**
     * @var string
     */
    protected $state = self::STATE_PENDING;
    /**
     * @var float
     */
    protected $timeoutMicrotime;


    private function __construct()
    {
    }


    /**
     * @return static|bool|null
     */
    public static function from($from, float $waitMs, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? static::fromObjectStatic($from, $cur)
            ?? static::fromCallableHandler($from, $waitMs, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }


    /**
     * @param callable $callable
     * @param float    $waitMs
     *
     * @return static
     */
    public static function new($callable, float $waitMs)
    {
        return static::fromCallableHandler($callable, $waitMs);
    }


    /**
     * @return static|bool|null
     */
    protected static function fromObjectStatic($from, $ctx = null)
    {
        if ($from instanceof static) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    protected static function fromCallableHandler($from, float $waitMs, $ctx = null)
    {
        if (! is_callable($from)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be callable', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ($waitMs < 0) {
            return Result::err(
                $ctx,
                [ 'The `waitMs` should be float greater than or equal to zero', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();
        $instance->fnHandler = $from;
        $instance->waitMs = $waitMs;

        $instance->timeoutMicrotime = microtime(true) + ($waitMs / 1000);

        return Result::ok($ctx, $instance);
    }


    public function getState() : string
    {
        return $this->state;
    }


    public function isAwaiting() : bool
    {
        return
            (static::STATE_POOLING === $this->state)
            || (static::STATE_PENDING === $this->state);
    }

    public function isSettled() : bool
    {
        return
            (static::STATE_TIMEOUT === $this->state)
            || (static::STATE_CANCELLED === $this->state);
    }


    public function isPending() : bool
    {
        return static::STATE_PENDING === $this->state;
    }

    public function isPooling() : bool
    {
        return static::STATE_POOLING === $this->state;
    }


    public function isTimeout() : bool
    {
        return static::STATE_TIMEOUT === $this->state;
    }

    public function isCancelled() : bool
    {
        return static::STATE_CANCELLED === $this->state;
    }


    public function tick() : void
    {
        if (static::STATE_POOLING !== $this->state) {
            throw new RuntimeException(
                [ 'The timer must be `pooling` to call `tick`', $this ]
            );
        }

        if (microtime(true) >= $this->timeoutMicrotime) {
            $this->state = static::STATE_TIMEOUT;
        }
    }

    public function onTimeout(LoopManagerInterface $loop) : void
    {
        if (static::STATE_TIMEOUT !== $this->state) {
            throw new RuntimeException(
                [ 'The timer must be `timeout` to call `onTimeout`', $this ]
            );
        }

        call_user_func($this->fnHandler);
    }
}
