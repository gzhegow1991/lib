<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class Exception extends \Exception implements
    ExceptionInterface,
    //
    \IteratorAggregate
{
    use ExceptionTrait;


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

        parent::__construct(
            $eArgsMessage,
            $eArgsCode,
            $eArgsPrevious
        );

        $this->fileOverride = $eArgsFile;
        $this->lineOverride = $eArgsLine;

        $this->messageList = $eArgsMessageList;
        $this->messageObjectList = $eArgsMessageObjectList;
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
