<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class Except implements
    ExceptInterface,
    //
    \IteratorAggregate
{
    use ExceptTrait;


    /**
     * @var bool
     */
    protected static $shouldTrace = true;

    /**
     * @param int|false|null $shouldTrace
     */
    public static function staticShouldTrace(?bool $shouldTrace = null) : bool
    {
        $last = static::$shouldTrace;

        if ( null !== $shouldTrace ) {
            if ( false === $shouldTrace ) {
                static::$shouldTrace = false;

            } else {
                static::$shouldTrace = (bool) $shouldTrace;
            }
        }

        static::$shouldTrace = static::$shouldTrace ?? false;

        return $last;
    }


    public function __construct(...$throwableArgs)
    {
        $thePhp = Lib::php();

        $eArgs = $thePhp->throwable_args(...$throwableArgs);

        $message = $eArgs['message'] ?? '[ NO MESSAGE ]';
        $code = $eArgs['code'] ?? -1;
        $previous = $eArgs['previous'];

        $previousList = array_values($eArgs['previousList']);

        $file = $eArgs['file'];
        $line = $eArgs['line'];

        $messageList = array_values($eArgs['messageList']) ?: [ $message ];
        $messageObjectList = array_values($eArgs['messageObjectList']) ?: [ (object) [ $message ] ];

        $errorsCount = count($messageList);
        if ( $errorsCount > 1 ) {
            $message = "[ MULTIPLE ERRORS: {$errorsCount} ]";
        }

        $trace = null;
        if ( static::staticShouldTrace() ) {
            $ex = new \Exception();
            $exTrace = $ex->getTrace();

            $exTraceShift = array_shift($exTrace);

            $trace = $exTrace;
            $file = $file ?? $exTrace[0]['file'] ?? $exTraceShift['file'] ?? '{file}';
            $line = $line ?? $exTrace[0]['line'] ?? $exTraceShift['line'] ?? -1;
        }

        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;

        $this->file = $file;
        $this->line = $line;
        $this->trace = $trace;

        $this->messageList = $messageList;
        $this->messageObjectList = $messageObjectList;

        $this->previousList = $previousList;
    }


    public function __toString()
    {
        return sprintf(
            "%s: %s in %s:%d\nStack trace:\n%s",
            get_class($this),
            $this->getMessage(),
            $this->getFile(),
            $this->getLine(),
            $this->getTraceAsString()
        );
    }


    /**
     * @return \Traversable<string, \Throwable[]>
     */
    public function getIterator() : \Traversable
    {
        $theDebugThrowabler = Lib::debugThrowabler();

        return $theDebugThrowabler->getPreviousTrackIterator($this);
    }
}
