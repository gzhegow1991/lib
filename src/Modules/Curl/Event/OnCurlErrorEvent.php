<?php

namespace Gzhegow\Lib\Modules\Curl\Event;

use Gzhegow\Lib\Modules\Curl\CurlItem;


class OnCurlErrorEvent extends AbstractOnCurlEvent
{
    /**
     * @var CurlItem
     */
    protected $curlItem;

    /**
     * @var int
     */
    protected $curlErrno;
    /**
     * @var string
     */
    protected $curlError;


    public function __construct(
        CurlItem $curlItem,
        int $errno, string $error
    )
    {
        $this->curlItem = $curlItem;

        $this->curlErrno = $errno;
        $this->curlError = $error;
    }


    public function getCurlItem() : CurlItem
    {
        return $this->curlItem;
    }


    /**
     * @return int
     */
    public function getCurlErrno() : int
    {
        return $this->curlErrno;
    }

    /**
     * @return string
     */
    public function getCurlError() : string
    {
        return $this->curlError;
    }
}
