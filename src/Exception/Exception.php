<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class Exception extends \Exception implements
    ExceptionInterface,
    //
    \IteratorAggregate
{
    use ExceptionTrait;


    public static function fromExcept(ExceptInterface $e)
    {
        $ex = new static(
            $e->getMessage(),
            $e->getCode(),
            $e->getPrevious(),
        );

        $ex->fileOverride = $e->getFile();
        $ex->lineOverride = $e->getLine();

        $ex->traceOverride = ($e->hasTrace() ? $e->getTrace() : null);

        $ex->messageList = $e->getMessageList();
        $ex->messageObjectList = $e->getMessageObjectList();

        $ex->previousList = $e->getPreviousList();

        return $ex;
    }


    public function __construct(...$throwableArgs)
    {
        $thePhp = Lib::php();

        $eArgs = $thePhp->throwable_args(...$throwableArgs);

        $eArgsMessage = $eArgs['message'] ?? '[ NO MESSAGE ]';
        $eArgsCode = $eArgs['code'] ?? -1;
        $eArgsPrevious = $eArgs['previous'];

        $eArgsPreviousList = array_values($eArgs['previousList']);

        $eArgsFile = $eArgs['file'];
        $eArgsLine = $eArgs['line'];

        $eArgsMessageList = array_values($eArgs['messageList']) ?: [ $eArgsMessage ];
        $eArgsMessageObjectList = array_values($eArgs['messageObjectList']) ?: [ (object) [ $eArgsMessage ] ];

        $cnt = count($eArgsMessageList);
        if ( $cnt > 1 ) {
            $eArgsMessage = "[ MULTIPLE ERRORS: {$cnt} ]";
        }

        if ( $eArgsPrevious instanceof ExceptInterface ) {
            $eArgsPrevious = Exception::fromExcept($eArgsPrevious);
        }

        parent::__construct(
            $eArgsMessage,
            $eArgsCode,
            $eArgsPrevious
        );

        $this->fileOverride = $eArgsFile;
        $this->lineOverride = $eArgsLine;

        $this->messageList = $eArgsMessageList;
        $this->messageObjectList = $eArgsMessageObjectList;

        $this->previousList = $eArgsPreviousList;
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
