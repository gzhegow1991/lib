<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class ErrorException extends \ErrorException implements
    ExceptionInterface,
    //
    \IteratorAggregate
{
    use ExceptionTrait;


    public function __construct(
        string $message = "", int $code = 0, int $severity = 1,
        ?string $filename = null, ?int $line = null,
        ?\Throwable $previous = null
    )
    {
        $theDebug = Lib::debug();
        $thePhp = Lib::php();

        $args = $thePhp->throwable_args($message, $code, [ $filename, $line ], $previous);

        $messageList = array_values($args['messageList']) ?: [ $message ];
        $messageObjectList = array_values($args['messageObjectList']) ?: [ (object) [ $message ] ];

        $this->messageList = $messageList;
        $this->messageObjectList = $messageObjectList;

        $hasFileLine = (null !== $filename);
        $hasPrevious = (null !== $previous);

        $errorsCount = count($messageList);

        if ( $hasPrevious ) {
            $previousList = array_values($args['previousList']);

            $this->previousList = $previousList;
        }

        if ( $errorsCount > 1 ) {
            $message = "Multiple errors occured: {$errorsCount} total";
        }

        parent::__construct(
            $message, $code,
            $severity,
            $filename, $line,
            $previous
        );

        if ( $hasPrevious ) {
            $theDebugThrowabler = $theDebug->throwabler();

            $this->previousMessageList = $theDebugThrowabler->getPreviousMessageFirstList($this);
        }

        if ( $hasFileLine ) {
            $this->file = $filename;
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
