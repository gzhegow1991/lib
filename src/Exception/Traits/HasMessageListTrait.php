<?php

namespace Gzhegow\Lib\Exception\Traits;

use Gzhegow\Lib\Exception\Interfaces\HasMessageListInterface;


/**
 * @mixin \Throwable
 *
 * @see HasMessageListInterface
 */
trait HasMessageListTrait
{
    /**
     * @var string[]
     */
    protected $messageList = [];
    /**
     * @var object[]
     */
    protected $messageObjectList = [];


    public function getMessageList() : array
    {
        return $this->messageList;
    }

    public function getMessageObjectList() : array
    {
        return $this->messageObjectList;
    }
}
