<?php

/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules\Curl\Event;

use Gzhegow\Lib\Modules\Curl\CurlItem;
use Gzhegow\Lib\Exception\RuntimeException;


class OnCurlDoneEvent extends AbstractOnCurlEvent
{
    /**
     * @var CurlItem
     */
    protected $curlItem;

    /**
     * @var int
     */
    protected $httpCode;

    /**
     * @var string
     */
    protected $httpEffectiveUrl;
    /**
     * @var string
     */
    protected $httpEffectiveMethod;

    /**
     * @var string
     */
    protected $httpContent;
    /**
     * @var array
     */
    protected $httpHeaders;


    public function __construct(
        CurlItem $curlItem,
        int $httpCode, string $httpEffectiveUrl, string $httpEffectiveMethod
    )
    {
        $this->curlItem = $curlItem;

        $this->httpCode = $httpCode;
        $this->httpEffectiveUrl = $httpEffectiveUrl;
        $this->httpEffectiveMethod = $httpEffectiveMethod;
    }


    public function getCurlItem() : CurlItem
    {
        return $this->curlItem;
    }


    public function getHttpCode() : int
    {
        return $this->httpCode;
    }


    public function getHttpEffectiveUrl() : string
    {
        return $this->httpEffectiveUrl;
    }

    /**
     * @return string
     */
    public function getHttpEffectiveMethod() : string
    {
        if ( PHP_VERSION_ID < 80200 ) {
            throw new RuntimeException('This function is avalable since PHP 8.2');
        }

        return $this->httpEffectiveMethod;
    }


    public function getHttpContent() : string
    {
        $ch = $this->curlItem->getCurlHandle();

        if ( null === $this->httpContent ) {
            $this->httpContent = curl_multi_getcontent($ch);
        }

        return $this->httpContent;
    }

    public function getHttpHeaders() : array
    {
        if ( null === $this->httpHeaders ) {
            $this->httpHeaders = $this->curlItem->getCurlHandleData()['httpHeaders'];
        }

        return $this->httpHeaders;
    }
}
