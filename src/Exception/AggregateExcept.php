<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class AggregateExcept implements
    ExceptInterface,
    //
    \IteratorAggregate
{
    use ExceptTrait;


    /**
     * @var (\Throwable|ExceptInterface)[]
     */
    protected $throwables = [];


    public function __construct(array $throwables)
    {
        if ( [] === $throwables ) {
            throw new RuntimeException(
                [ 'The `throwables` should be array, non empty', $throwables ]
            );
        }

        $throwables = array_values($throwables);

        $theDebug = Lib::debug();

        $eFile = null;
        $eLine = null;
        $eTrace = null;
        if ( $theDebug->stateShouldTrace() ) {
            $ex = new \Exception();
            $exTrace = $ex->getTrace();
            $exTraceShift = array_shift($exTrace);

            $eTrace = $exTrace;
            $eFile = $theDebug->file_for_trace($exTrace[0]['file'] ?? $exTraceShift['file'] ?? null, '');
            $eLine = $theDebug->line_for_trace($exTrace[0]['line'] ?? $exTraceShift['line'] ?? null);
        }

        $throwablesCnt = count($throwables);

        $message = "[ MULTIPLE ERRORS # {$throwablesCnt} ]";

        $this->message = $message;
        $this->code = -1;

        $this->file = $eFile;
        $this->line = $eLine;
        $this->trace = $eTrace;

        $this->throwables = $throwables;
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

        return $theDebugThrowabler->getPreviousIterator($this);
    }
}
