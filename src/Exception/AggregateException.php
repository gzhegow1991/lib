<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class AggregateException extends \RuntimeException implements
    ExceptionInterface,
    //
    \IteratorAggregate
{
    use ExceptionTrait;


    /**
     * @var (\Throwable|ExceptInterface)[]
     */
    protected $throwables = [];


    public function __construct(array $throwables)
    {
        if ( [] === $throwables ) {
            throw new RuntimeException(
                [ 'The `throwables` should be array, non empty', $throwables ]
            );
        }

        $throwables = array_values($throwables);

        $throwablesCnt = count($throwables);

        $message = "[ MULTIPLE ERRORS # {$throwablesCnt} ]";

        parent::__construct(
            $message,
            -1,
        );

        $this->throwables = $throwables;
    }


    /**
     * @return \Traversable<string, \Throwable[]>
     */
    public function getIterator() : \Traversable
    {
        $theDebugThrowabler = Lib::debugThrowabler();

        return $theDebugThrowabler->getPreviousIterator($this);
    }


    /**
     * @return (\Throwable|ExceptInterface)[]
     */
    public function getThrowables() : array
    {
        return $this->throwables;
    }
}
