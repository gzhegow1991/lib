<?php

namespace Gzhegow\Lib\Modules\Curl\Event;

use Gzhegow\Lib\Modules\Curl\CurlItem;


abstract class AbstractOnCurlSingleEvent extends AbstractOnCurlEvent
{
    /**
     * @var CurlItem
     */
    protected $curlItem;


    public function __construct(CurlItem $curlItem)
    {
        $this->curlItem = $curlItem;
    }


    public function getCurlItem() : CurlItem
    {
        return $this->curlItem;
    }
}
