<?php

namespace Gzhegow\Lib\Exception\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\ExceptionInterface;
use Gzhegow\Lib\Exception\Interfaces\HasPreviousListInterface;


/**
 * @see HasPreviousListInterface
 */
trait HasPreviousListTrait
{
    /**
     * @var (\Throwable|ExceptionInterface)[]
     */
    protected $previousList = [];
    /**
     * @var array
     */
    protected $previousMessageList;


    /**
     * @return (\Throwable|ExceptionInterface)[]
     */
    public function getPreviousList() : array
    {
        return $this->previousList;
    }

    /**
     * @return string[]
     */
    public function getPreviousMessageList() : array
    {
        $this->preparePreviousMessageList();

        return $this->previousMessageList;
    }

    protected function preparePreviousMessageList() : void
    {
        if ( null === $this->previousMessageList ) {
            $theDebugThrowabler = Lib::debugThrowabler();

            $previousMessageList = $theDebugThrowabler->getMessagesArray($this);

            $this->previousMessageList = $previousMessageList;
        }
    }
}
