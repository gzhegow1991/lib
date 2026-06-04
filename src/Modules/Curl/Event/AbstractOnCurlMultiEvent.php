<?php

namespace Gzhegow\Lib\Modules\Curl\Event;

use Gzhegow\Lib\Modules\Curl\CurlItem;


abstract class AbstractOnCurlMultiEvent extends AbstractOnCurlEvent
{
    /**
     * @var CurlItem[]
     */
    protected $curlItems = [];


    public function __construct(array $curlItems)
    {
        $this->curlItems = $curlItems;
    }


    /**
     * @return CurlItem[]
     */
    public function getCurlItems() : array
    {
        return $this->curlItems;
    }
}
