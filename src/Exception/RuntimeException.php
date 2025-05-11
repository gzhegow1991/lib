<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class RuntimeException extends \RuntimeException implements
    AggregateExceptionInterface,
    //
    \IteratorAggregate
{
    use ExceptionTrait;


    public function __construct(...$throwableArgs)
    {
        $args = Lib::php()->throwable_args(...$throwableArgs);

        $this->messageList = array_values($args[ 'messageList' ]);
        $this->messageObjectList = array_values($args[ 'messageObjectList' ]);
        $this->previousList = array_values($args[ 'previousList' ]);

        $cnt = count($args[ 'messageList' ]);

        if ($cnt > 1) {
            $args[ 'message' ] = 'Multiple errors occured: ' . $cnt;
        }

        parent::__construct(
            $args[ 'message' ],
            $args[ 'code' ],
            $args[ 'previous' ]
        );
    }


    /**
     * @return \Traversable<string, \Throwable[]>
     */
    public function getIterator() : \Traversable
    {
        return Lib::debug()
            ->throwableManager()
            ->getPreviousTrackIterator($this)
        ;
    }
}
