<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\Traits\AggregateExceptionTrait;
use Gzhegow\Lib\Exception\Interfaces\AggregateExceptionInterface;


class RuntimeException extends \RuntimeException implements
    AggregateExceptionInterface,
    //
    \IteratorAggregate
{
    use ExceptionTrait;

    use AggregateExceptionTrait;


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
     * @return iterable<string, \Throwable[]>
     */
    public function getIterator() : \Traversable
    {
        /** @var iterable<string, \Throwable[]> $iit */

        $it = Lib::new8(ExceptionIterator::class, [ $this ]);
        $iit = new \RecursiveIteratorIterator($it);

        return $iit;
    }
}
