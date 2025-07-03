<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class LogicException extends \LogicException implements
    AggregateExceptionInterface,
    //
    \IteratorAggregate
{
    use ExceptionTrait;


    public function __construct(...$throwableArgs)
    {
        $args = Lib::php()->throwable_args(...$throwableArgs);

        $message = $args[ 'message' ];
        $messageList = array_values($args[ 'messageList' ]);
        $messageObjectList = array_values($args[ 'messageObjectList' ]);

        $previous = $args[ 'previous' ];
        $hasPrevious = (null !== $previous);

        $this->messageList = $messageList;
        $this->messageObjectList = $messageObjectList;

        $errorsCount = count($messageList);
        if ($hasPrevious) {
            $previousList = array_values($args[ 'previousList' ]);

            $errorsCount += count($previousList);
            $errorsCount -= 1;

            $this->previousList = $previousList;
        }

        if ($errorsCount > 1) {
            $message = "[ TOTAL: {$errorsCount} ] {$message}";
        }

        parent::__construct(
            $message,
            $args[ 'code' ],
            $args[ 'previous' ]
        );

        if ($hasPrevious) {
            $theDebugThrowabler = Lib::debugThrowabler();

            $this->previousMessageList = $theDebugThrowabler->getPreviousMessagesAllList($this);
        }
    }


    /**
     * @return \Traversable<string, \Throwable[]>
     */
    public function getIterator() : \Traversable
    {
        return Lib::debugThrowabler()->getPreviousTrackIterator($this);
    }
}
