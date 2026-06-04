<?php

/**
 * @noinspection PhpComposerExtensionStubsInspection
 * @noinspection PhpReturnDocTypeMismatchInspection
 */

namespace Gzhegow\Lib\Modules\Curl;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Http\HttpHeader\HttpHeader;


class CurlItem
{
    const DATATYPE_FORM      = 'form';
    const DATATYPE_JSON      = 'json';
    const DATATYPE_MULTIPART = 'multipart';
    const DATATYPE_QUERY     = 'query';
    const DATATYPE_RAW       = 'raw';
    const DATATYPE_TEXT      = 'text';
    const DATATYPE_XML       = 'xml';

    const LIST_DATATYPE = [
        self::DATATYPE_FORM      => true,
        self::DATATYPE_JSON      => true,
        self::DATATYPE_MULTIPART => true,
        self::DATATYPE_QUERY     => true,
        self::DATATYPE_RAW       => true,
        self::DATATYPE_TEXT      => true,
        self::DATATYPE_XML       => true,
    ];


    /**
     * @var CurlItem
     */
    protected $curlItemBase;

    /**
     * @var string
     */
    protected $requestUrl;
    /**
     * @var string
     */
    protected $requestMethod;

    /**
     * @var string|array|object
     */
    protected $data;
    /**
     * @var string
     */
    protected $dataType;

    /**
     * @var array
     */
    protected $curlOptions = [];
    /**
     * @var array<HttpHeader>
     */
    protected $curlHeaders = [];

    /**
     * @var array{ 0?: mixed }
     */
    protected $userData = [];

    /**
     * @var resource|\CurlHandle
     */
    protected $ch;
    /**
     * @var array
     */
    protected $chData = [
        'httpHeaders' => [],
    ];


    public static function new()
    {
        return new static();
    }


    public function getCurlItemBase() : ?CurlItem
    {
        return $this->curlItemBase;
    }

    public function setCurlItemBase(?CurlItem $curlItemBase)
    {
        $this->curlItemBase = $curlItemBase;

        return $this;
    }


    public function getUrl() : string
    {
        return $this->requestUrl;
    }

    public function setUrl($url)
    {
        $theType = Lib::type();

        $urlValid = $theType->url($url)->orThrow();

        $this->requestUrl = $urlValid;

        return $this;
    }


    public function getMethod() : ?string
    {
        return $this->requestMethod;
    }

    public function setMethod($method)
    {
        $theType = Lib::type();

        $methodValid = $theType->http_method($method)->orThrow();

        $this->requestMethod = $methodValid;

        return $this;
    }


    public function getData()
    {
        return $this->data;
    }

    public function getDataType() : ?string
    {
        return $this->dataType;
    }

    public function setData($dataType, $data)
    {
        $theType = Lib::type();

        $theType->array_key_exists(static::LIST_DATATYPE, $dataType)->orThrow();

        switch ( $dataType ):
            case self::DATATYPE_FORM:
                $this->setDataForm($data);

                break;

            case self::DATATYPE_JSON:
                $this->setDataJson($data);

                break;

            case self::DATATYPE_MULTIPART:
                $this->setDataMultipart($data);

                break;

            case self::DATATYPE_QUERY:
                $this->setDataQuery($data);

                break;

            case self::DATATYPE_RAW:
                $this->setDataRaw($data);

                break;

            case self::DATATYPE_TEXT:
                $this->setDataText($data);

                break;

            case self::DATATYPE_XML:
                $this->setDataXml($data);

                break;
        endswitch;
    }

    public function setDataQuery($data)
    {
        $theType = Lib::type();

        $ret = Lib::newRet();

        $dataValid = null
            ?? $theType->array($data)->orNull($ret)
            ?? $theType->stdclass($data)->orNull($ret);

        $ret->orThrow(
            [ 'The `data` should be array or stdclass', $data ],
            [ __FILE__, __LINE__ ]
        );

        $queue = array_reverse((array) $dataValid);

        while ( [] !== $queue ) {
            $cur = array_pop($queue);

            if ( ! (false
                || is_array($cur)
                || ($cur instanceof \stdClass)
            ) ) {
                $theType->string($cur)->orThrow();

            } else {
                foreach ( array_reverse((array) $cur) as $key => $val ) {
                    if ( '' === $key ) {
                        throw new LogicException(
                            [ 'The `key` should be string, non-empty', $dataValid ]
                        );
                    }

                    $queue[] = $val;
                }
            }
        }

        $this->dataType = static::DATATYPE_QUERY;
        $this->data = $dataValid;

        return $this;
    }

    public function setDataForm($data)
    {
        $theType = Lib::type();

        $ret = Lib::newRet();

        $dataValid = null
            ?? $theType->array($data)->orNull($ret)
            ?? $theType->stdclass($data)->orNull($ret);

        $ret->orThrow(
            [ 'The `data` should be array or stdclass', $data ],
            [ __FILE__, __LINE__ ]
        );

        $this->dataType = static::DATATYPE_FORM;
        $this->data = $dataValid;

        return $this;
    }

    public function setDataMultipart($data)
    {
        $theType = Lib::type();

        $ret = Lib::newRet();

        $dataValid = null
            ?? $theType->array($data)->orNull($ret)
            ?? $theType->stdclass($data)->orNull($ret);

        $ret->orThrow(
            [ 'The `data` should be array or stdclass', $data ],
            [ __FILE__, __LINE__ ]
        );

        $this->dataType = static::DATATYPE_MULTIPART;
        $this->data = $dataValid;

        return $this;
    }

    public function setDataJson($data)
    {
        $theType = Lib::type();

        $ret = Lib::newRet();

        $dataValid = null
            ?? $theType->array($data)->orNull($ret)
            ?? $theType->stdclass($data)->orNull($ret);

        $ret->orThrow(
            [ 'The `data` should be array or stdclass', $data ],
            [ __FILE__, __LINE__ ]
        );

        $this->dataType = static::DATATYPE_JSON;
        $this->data = $dataValid;

        return $this;
    }

    public function setDataXml($data)
    {
        $theType = Lib::type();
        $theFormatXml = Lib::formatXml();

        $dataString = $theType->string_not_empty($data)->orThrow();

        $theFormatXml->parse_xml_sxe([], $dataString);

        $this->dataType = static::DATATYPE_XML;
        $this->data = $dataString;

        return $this;
    }

    public function setDataText($data)
    {
        $theType = Lib::type();

        $dataValid = $theType->string_not_empty($data)->orThrow();

        $this->dataType = static::DATATYPE_TEXT;
        $this->data = $dataValid;

        return $this;
    }

    public function setDataRaw($data)
    {
        $theType = Lib::type();

        $dataValid = $theType->string_not_empty($data)->orThrow();

        $this->dataType = static::DATATYPE_RAW;
        $this->data = $dataValid;

        return $this;
    }


    public function getCurlOptions(?bool $withBase = null) : array
    {
        $withBase = $withBase ?? false;

        if ( $withBase && (null !== $this->curlItemBase) ) {
            $curlOptions = array_replace(
                $this->curlItemBase->getCurlOptions(),
                $this->curlOptions
            );

        } else {
            $curlOptions = $this->curlOptions;
        }

        return $curlOptions;
    }

    public function setCurlOptions(array $curlOptions)
    {
        $theCurl = Lib::curl();

        $this->curlOptions = [];

        foreach ( $curlOptions as $opt => $value ) {
            $curlOpt = null;

            if ( is_string($opt) ) {
                $curlOpts = $theCurl->getCurlOptConstants();

                if ( ! array_key_exists($opt, $curlOpts) ) {
                    throw new LogicException(
                        [ 'The `opt` is unknown', $opt ]
                    );
                }

                $curlOpt = $curlOpts[$opt];

            } elseif ( is_int($opt) ) {
                if ( ! $theCurl->hasCurlOpt($opt) ) {
                    throw new LogicException(
                        [ 'The `opt` is unknown', $opt ]
                    );
                }

                $curlOpt = $opt;
            }

            $this->curlOptions[$curlOpt] = $value;
        }

        return $this;
    }

    public function setCurlOpt($opt, $value)
    {
        $theCurl = Lib::curl();

        $curlOpt = null;

        if ( is_string($opt) ) {
            $curlOpts = $theCurl->getCurlOptConstants();

            if ( ! array_key_exists($opt, $curlOpts) ) {
                throw new LogicException(
                    [ 'The `opt` is unknown', $opt ]
                );
            }

            $curlOpt = $curlOpts[$opt];

        } elseif ( is_int($opt) ) {
            if ( ! $theCurl->hasCurlOpt($opt) ) {
                throw new LogicException(
                    [ 'The `opt` is unknown', $opt ]
                );
            }

            $curlOpt = $opt;
        }

        if ( null === $curlOpt ) {
            throw new LogicException(
                [ 'The `opt` should be string or int', $opt ]
            );
        }

        $this->curlOptions[$curlOpt] = $value;

        return $this;
    }

    public function unsetCurlOpt(int $opt)
    {
        unset($this->curlOptions[$opt]);

        return $this;
    }


    public function getHeaders(?bool $withBase = null) : array
    {
        $withBase = $withBase ?? false;

        if ( $withBase && (null !== $this->curlItemBase) ) {
            $curlHeaders = array_replace(
                $this->curlItemBase->getHeaders(),
                $this->curlHeaders
            );

        } else {
            $curlHeaders = $this->curlHeaders;
        }

        return $curlHeaders;
    }

    public function setHeaders(array $headers)
    {
        $this->curlHeaders = [];

        foreach ( $headers as [$header, $value, $params] ) {
            $this->setHeader($header, $value, $params);
        }

        return $this;
    }

    public function setHeader(string $header, string $value, array $params = [])
    {
        $httpHeader = HttpHeader::fromArray([ $header, $value, $params ]);

        $this->curlHeaders[$httpHeader->getName()] = $httpHeader;

        return $this;
    }

    public function addHeaders(array $headers)
    {
        foreach ( $headers as [$header, $value, $params] ) {
            $this->addHeader($header, $value, $params);
        }

        return $this;
    }

    public function addHeader(string $header, string $value, array $params = [])
    {
        $httpHeader = HttpHeader::fromArray([ $header, $value, $params ]);

        $httpHeaderName = $httpHeader->getName();

        if ( isset($this->curlHeaders[$httpHeaderName]) ) {
            return $this;
        }

        $this->curlHeaders[$httpHeaderName] = $httpHeader;

        return $this;
    }

    public function unsetHeader(string $header)
    {
        $httpHeader = HttpHeader::fromArray([ $header, '', [] ]);

        unset($this->curlHeaders[$httpHeader->getName()]);

        return $this;
    }

    /**
     * @template T
     *
     * @param class-string<T>|null $classT
     *
     * @return T
     */
    public function &getUserData(?string $classT = null)
    {
        $refUserData =& $this->userData[0];

        return $refUserData;
    }

    public function setUserData($userData)
    {
        $this->userData = [ $userData ];

        return $this;
    }


    /**
     * @return resource|\CurlHandle
     */
    public function getCurlHandle()
    {
        if ( null === $this->ch ) {
            $this->ch = $this->makeCurlHandle();
        }

        return $this->ch;
    }

    /**
     * @return resource|\CurlHandle
     */
    public function resetCurlHandle()
    {
        $this->ch = $this->makeCurlHandle();

        return $this->ch;
    }

    /**
     * @return resource|\CurlHandle|null
     */
    public function flushCurlHandle()
    {
        $ch = $this->ch;

        $this->ch = null;

        return $ch;
    }

    /**
     * @return resource|\CurlHandle
     */
    public function makeCurlHandle()
    {
        $theType = Lib::type();

        $urlValid = $theType->url($this->requestUrl)->orThrow();

        $ch = curl_init($urlValid);

        $hasCurlProcessItemBase = (null !== $this->curlItemBase);

        $curlOptions = [];
        $curlHeaders = [];

        if ( $hasCurlProcessItemBase ) {
            $curlOptions = $this->curlItemBase->getCurlOptions();
            $curlHeaders = $this->curlItemBase->getHeaders();
        }

        $curlOptions = array_replace($curlOptions, $this->curlOptions);
        $curlHeaders = array_replace($curlHeaders, $this->curlHeaders);

        $hasMethod = (null !== $this->requestMethod);
        $hasData = (null !== $this->dataType);

        $hasOptions = ([] !== $curlOptions);
        $hasHeaders = ([] !== $curlHeaders);

        $hasNativeHeader = $hasOptions
            && (false
                || array_key_exists(CURLOPT_HEADER, $curlOptions)
            );

        $hasNativeHeaderFunction = $hasOptions
            && (false
                || array_key_exists(CURLOPT_HEADERFUNCTION, $curlOptions)
            );

        if ( $hasNativeHeader ) {
            if ( $hasNativeHeaderFunction ) {
                throw new RuntimeException(
                    [ 'Unable to set cURL CURLOPT_HEADER and CURLOPT_HEADERFUNCTION at same time', $curlOptions ]
                );
            }

            throw new RuntimeException(
                [ 'To receive headers you have to use CURLOPT_HEADERFUNCTION', $curlOptions ]
            );
        }

        if ( $hasOptions ) {
            $status = curl_setopt_array($ch, $curlOptions);

            if ( false === $status ) {
                throw new RuntimeException(
                    [ 'Unable to set cURL `curlOptions`', $curlOptions ]
                );
            }
        }

        if ( $hasMethod ) {
            $hasNativeMethod = $hasOptions
                && (false
                    || array_key_exists(CURLOPT_POST, $curlOptions)
                    || array_key_exists(CURLOPT_POSTFIELDS, $curlOptions)
                    || array_key_exists(CURLOPT_CUSTOMREQUEST, $curlOptions)
                );

            if ( $hasNativeMethod ) {
                throw new RuntimeException(
                    [
                        ''
                        . 'You can use to set HTTP method either of'
                        . ' using `->setMethod($method)` or using cURL options, '
                        . ' you can\'t use both at same time',
                    ]
                );
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, null);

            switch ( $this->requestMethod ):
                case 'GET':
                    curl_setopt($ch, CURLOPT_POST, false);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, null);

                    break;

                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, null);

                    break;

                default:
                    curl_setopt($ch, CURLOPT_POST, false);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->requestMethod);

                    break;

            endswitch;
        }

        if ( $hasData ) {
            $status = null;

            switch ( $this->dataType ):
                case static::DATATYPE_QUERY:
                    $urlQuery = $theType->url($this->requestUrl, $this->data)->orThrow();

                    $status = curl_setopt($ch, CURLOPT_URL, $urlQuery);

                    $this->unsetHeader('Content-Type');
                    $this->unsetHeader('Content-Length');

                    break;

                case static::DATATYPE_FORM:
                    $status = curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));

                    $this->unsetHeader('Content-Type');
                    $this->unsetHeader('Content-Length');

                    break;

                case static::DATATYPE_MULTIPART:
                    $status = curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);

                    $this->unsetHeader('Content-Type');
                    $this->unsetHeader('Content-Length');

                    break;

                case static::DATATYPE_TEXT:
                    $status = curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);

                    $this->setHeader('Content-Type', 'text/plain', [ 'charset' => 'utf-8' ]);
                    $this->setHeader('Content-Length', strlen($this->data));

                    break;

                case static::DATATYPE_JSON:
                    $theFormatJson = Lib::formatJson();

                    $dataJson = $theFormatJson->json_encode([], $this->data);

                    $status = curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);

                    $this->setHeader('Content-Type', 'application/json', [ 'charset' => 'utf-8' ]);
                    $this->setHeader('Content-Length', strlen($dataJson));

                    break;

                case static::DATATYPE_XML:
                    $status = curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);

                    $this->setHeader('Content-Type', 'application/xml', [ 'charset' => 'utf-8' ]);
                    $this->setHeader('Content-Length', strlen($this->data));

                    break;

                case static::DATATYPE_RAW:
                    $status = curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);

                    // $this->unsetHeader('Content-Type');
                    // $this->unsetHeader('Content-Length');

                    break;

            endswitch;

            if ( false === $status ) {
                throw new RuntimeException(
                    [ 'Unable to set cURL `data`', $this->dataType, $this->data ]
                );
            }
        }

        if ( $hasHeaders ) {
            $hasNativeHeaders = $hasOptions
                && (false
                    || array_key_exists(CURLOPT_HTTPHEADER, $curlOptions)
                );

            $curlHeadersRaw = [];

            if ( $hasNativeHeaders ) {
                $curlHeadersRaw = $curlOptions[CURLOPT_HTTPHEADER];
            }

            foreach ( $curlHeaders as $httpHeader ) {
                $curlHeadersRaw[] = $httpHeader->toString();
            }

            $status = curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeadersRaw);

            if ( false === $status ) {
                throw new RuntimeException(
                    [ 'Unable to set cURL `curlHeaders`', $curlHeaders ]
                );
            }
        }

        $fnUserHeaderFunction = null;
        if ( $hasNativeHeaderFunction ) {
            $fnUserHeaderFunction = $curlOptions[CURLOPT_HEADERFUNCTION];
        }

        $fnHeaderFunction = $this->makeCurlOptHeaderFunction($fnUserHeaderFunction);

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, $fnHeaderFunction);

        return $ch;
    }


    public function getCurlHandleData() : array
    {
        return $this->chData;
    }


    protected function makeCurlOptHeaderFunction($fnUserHeaderFunction = null) : \Closure
    {
        return function ($ch, string $headerLine) use ($fnUserHeaderFunction) {
            $this->chData['httpHeaders'][] = $headerLine;

            $strlen = null;
            if ( null != $fnUserHeaderFunction ) {
                $strlen = $fnUserHeaderFunction($ch, $headerLine);
            }

            return $strlen ?? strlen($headerLine);
        };
    }
}
