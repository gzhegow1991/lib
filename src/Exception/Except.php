<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\DebugModule;


class Except implements
    ExceptInterface,
    //
    \IteratorAggregate
{
    use ExceptTrait;


    public function __construct(...$throwableArgs)
    {
        $thePhp = Lib::php();

        $eArgs = $thePhp->throwable_args(...$throwableArgs);

        $eArgsMessage = $eArgs['message'];
        $eArgsCode = $eArgs['code'];

        $eArgsFile = $eArgs['file'];
        $eArgsLine = $eArgs['line'];

        $eArgsPrevious = $eArgs['previous'];
        $eArgsPreviousList = array_values($eArgs['previousList']);
        if ( ! $eArgsPrevious && $eArgsPreviousList ) {
            $eArgsPrevious = new AggregateException($eArgsPreviousList);

        } elseif ( $eArgsPrevious instanceof ExceptInterface ) {
            $eArgsPrevious = Exception::fromExcept($eArgsPrevious);
        }

        $eArgsMessageList = array_values($eArgs['messageList']) ?: [ $eArgsMessage ];
        $eArgsMessageObjectList = array_values($eArgs['messageObjectList']) ?: [ (object) [ $eArgsMessage ] ];

        $eArgsTrace = null;
        if ( DebugModule::staticShouldTrace() ) {
            $ex = new \Exception();
            $exTrace = $ex->getTrace();
            $exTraceShift = array_shift($exTrace);

            $eArgsTrace = $exTrace;
        }

        $eArgsFile = $eArgsFile ?? $exTrace[0]['file'] ?? $exTraceShift['file'] ?? '{{file}}';
        $eArgsLine = $eArgsLine ?? $exTrace[0]['line'] ?? $exTraceShift['line'] ?? -1;

        $this->message = $eArgsMessage;
        $this->code = $eArgsCode;
        $this->previous = $eArgsPrevious;

        $this->file = $eArgsFile;
        $this->line = $eArgsLine;

        $this->trace = $eArgsTrace;

        $this->messageList = $eArgsMessageList;
        $this->messageObjectList = $eArgsMessageObjectList;
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
