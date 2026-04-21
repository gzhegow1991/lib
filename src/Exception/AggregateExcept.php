<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\DebugModule;


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
            throw new RuntimeException([ 'The `throwables` should be array, non empty', $throwables ]);
        }

        $throwables = array_values($throwables);

        $eFile = null;
        $eLine = null;
        $eTrace = null;
        if ( DebugModule::staticShouldTrace() ) {
            $ex = new \Exception();
            $exTrace = $ex->getTrace();
            $exTraceShift = array_shift($exTrace);

            $eTrace = $exTrace;
            $eFile = $exTrace[0]['file'] ?? $exTraceShift['file'] ?? '{{file}}';
            $eLine = $exTrace[0]['line'] ?? $exTraceShift['line'] ?? -1;
        }

        $throwablesCnt = count($throwables);

        // $message = ($throwablesCnt > 1)
        //     ? "[ MULTIPLE ERRORS # {$throwablesCnt} ]"
        //     : $throwables[0]->getMessage();
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
