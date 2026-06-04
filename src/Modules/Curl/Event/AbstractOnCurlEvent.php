<?php

namespace Gzhegow\Lib\Modules\Curl\Event;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Curl\CurlItem;


abstract class AbstractOnCurlEvent
{
    /**
     * @var array
     */
    protected $pushBeforeBatch = [];
    /**
     * @var array
     */
    protected $pushAfterBatch = [];
    /**
     * @var array
     */
    protected $pushBeforeChunk = [];
    /**
     * @var array
     */
    protected $pushAfterChunk = [];

    /**
     * @var bool
     */
    protected $isSkipped = false;


    /**
     * @return CurlItem[]
     */
    public function getPushBeforeBatch() : array
    {
        return $this->pushBeforeBatch;
    }

    /**
     * @return CurlItem[]
     */
    public function getPushAfterBatch() : array
    {
        return $this->pushAfterBatch;
    }


    /**
     * @return CurlItem[]
     */
    public function getPushBeforeChunk() : array
    {
        return $this->pushBeforeChunk;
    }

    /**
     * @return CurlItem[]
     */
    public function getPushAfterChunk() : array
    {
        return $this->pushAfterChunk;
    }


    /**
     * @param CurlItem[] $curlItems
     */
    public function pushBeforeBatch(array $curlItems)
    {
        $theType = Lib::type();

        foreach ( $curlItems as $curlItem ) {
            $theType->instance_of($curlItem, CurlItem::class)->orThrow();

            $this->pushBeforeBatch[] = $curlItem;
        }

        return $this;
    }

    /**
     * @param CurlItem[] $curlItems
     */
    public function pushAfterBatch(array $curlItems)
    {
        $theType = Lib::type();

        foreach ( $curlItems as $curlItem ) {
            $theType->instance_of($curlItem, CurlItem::class)->orThrow();

            $this->pushAfterBatch[] = $curlItem;
        }

        return $this;
    }


    /**
     * @param CurlItem[] $curlItems
     */
    public function pushBeforeChunk(array $curlItems)
    {
        $theType = Lib::type();

        foreach ( $curlItems as $curlItem ) {
            $theType->instance_of($curlItem, CurlItem::class)->orThrow();

            $this->pushBeforeChunk[] = $curlItem;
        }

        return $this;
    }

    /**
     * @param CurlItem[] $curlItems
     */
    public function pushAfterChunk(array $curlItems)
    {
        $theType = Lib::type();

        foreach ( $curlItems as $curlItem ) {
            $theType->instance_of($curlItem, CurlItem::class)->orThrow();

            $this->pushAfterChunk[] = $curlItem;
        }

        return $this;
    }


    public function isSkipped() : bool
    {
        return $this->isSkipped;
    }

    /**
     * @return static
     */
    public function skip()
    {
        $this->isSkipped = true;

        return $this;
    }
}
