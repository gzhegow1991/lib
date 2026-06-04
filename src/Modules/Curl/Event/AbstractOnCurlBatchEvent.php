<?php

namespace Gzhegow\Lib\Modules\Curl\Event;

use Gzhegow\Lib\Modules\Curl\CurlItem;


abstract class AbstractOnCurlBatchEvent extends AbstractOnCurlEvent
{
    /**
     * @var CurlItem[]
     */
    protected $curlItemsBatch = [];


    public function __construct(array $curlItemsBatch)
    {
        $this->curlItemsBatch = $curlItemsBatch;
    }


    /**
     * @return CurlItem[]
     */
    public function getCurlItemsBatch() : array
    {
        return $this->curlItemsBatch;
    }
}
