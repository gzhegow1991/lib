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

        $this->previousList = array_values($args[ 'previousList' ]);

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
        return ErrorHandler::getThrowableIterator($this);
    }
}
