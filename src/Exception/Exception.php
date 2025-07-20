<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class Exception extends \Exception implements
    AggregateExceptionInterface,
    //
    \IteratorAggregate
{
    use ExceptionTrait;


    public function __construct(...$throwableArgs)
    {
        $theDebug = Lib::$debug;
        $thePhp = Lib::$php;

        $args = $thePhp->throwable_args(...$throwableArgs);

        $message = $args[ 'message' ] ?? static::class;
        $messageList = array_values($args[ 'messageList' ]);
        $messageObjectList = array_values($args[ 'messageObjectList' ]);

        $this->messageList = $messageList;
        $this->messageObjectList = $messageObjectList;

        $file = $args[ 'file' ];
        $line = $args[ 'line' ];
        $hasFileLine = (null !== $file);

        $previous = $args[ 'previous' ];
        $hasPrevious = (null !== $previous);

        $errorsCount = count($messageList);
        if ($hasPrevious) {
            $previousList = array_values($args[ 'previousList' ]);

            $errorsCount += count($previousList);
            $errorsCount -= 1;

            $this->previousList = $previousList;
        }

        if ($errorsCount > 1) {
            $message = "Multiple errors occured: {$errorsCount} total";
        }

        parent::__construct(
            $message,
            $args[ 'code' ],
            $args[ 'previous' ]
        );

        if ($hasPrevious) {
            $theDebugThrowabler = $theDebug->throwabler();

            $this->previousMessageList = $theDebugThrowabler->getPreviousMessagesAllList($this);
        }

        if ($hasFileLine) {
            $this->file = $file;
            $this->line = $line;
        }
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
