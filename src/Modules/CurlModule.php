<?php

/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Curl\CurlItem;
use Gzhegow\Lib\Modules\Curl\CurlProcess;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Curl\Event\AbstractOnCurlEvent;


class CurlModule
{
    /**
     * @var CurlItem
     */
    protected $stateCurlItemBase;

    /**
     * @param CurlItem|null $curlItemBase
     */
    public function stateCurlItemBase($curlItemBase = null) : ?CurlItem
    {
        $last = null;

        if ( $isChange = (null !== $curlItemBase) ) {
            $last = $this->stateCurlItemBase;

            if ( false === $curlItemBase ) {
                $this->stateCurlItemBase = null;

            } else {
                $theType = Lib::type();

                $curlProcessItemBaseValid = $theType->instance_of($curlItemBase, CurlItem::class)->orThrow();

                $this->stateCurlItemBase = $curlProcessItemBaseValid;
            }
        }

        if ( null === $this->stateCurlItemBase ) {
            $instance = new CurlItem();

            $instance->setCurlOptions([
                CURLOPT_RETURNTRANSFER => true,
                //
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT        => 10,
                //
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 10,
                //
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => 1,
                //
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
                //
                CURLOPT_USERAGENT      => 'API/1.0',
            ]);

            $this->stateCurlItemBase = $instance;
        }

        return $isChange ? $last : $this->stateCurlItemBase;
    }


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        if ( ! extension_loaded('curl') ) {
            throw new RuntimeException(
                [ 'The extension missing: curl' ]
            );
        }

        return $this;
    }


    /**
     * @return Ret<resource|\CurlHandle>|resource|\CurlHandle
     */
    public function type_curl($fb, $value)
    {
        if ( is_a($value, '\CurlHandle') ) {
            return Ret::ok($fb, $value);
        }

        $theType = Lib::type();

        $ret = $theType->resource_opened($value, 'curl');

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be curl, opened' ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $value);
    }

    /**
     * @return Ret<resource|\CurlMultiHandle>|resource|\CurlMultiHandle
     */
    public function type_curl_multi($fb, $value)
    {
        if ( is_a($value, '\CurlMultiHandle') ) {
            return Ret::ok($fb, $value);
        }

        $theType = Lib::type();

        $ret = $theType->resource_opened($value, 'curl_multi');

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be curl multi, opened' ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $value);
    }


    public function newCurlProcess() : CurlProcess
    {
        $instance = CurlProcess::new();

        $instanceBase = $this->stateCurlItemBase();
        $instance->setCurlItemBase($instanceBase);

        return $instance;
    }

    public function newCurlItem() : CurlItem
    {
        $instance = CurlItem::new();

        $instanceBase = $this->stateCurlItemBase();
        $instance->setCurlItemBase($instanceBase);

        return $instance;
    }


    /**
     * @param CurlItem[]|array[]|string[] $curlItems
     *
     * @return \Generator<string, AbstractOnCurlEvent>
     */
    public function execSingle(array $curlItems) : \Generator
    {
        $cp = $this->newCurlProcess();

        foreach ( $curlItems as $curlItem ) {
            if ( $curlItem instanceof CurlItem ) {
                //

            } elseif ( is_array($curlItem) ) {
                [ $url, $method, $data, $dataType, $headers, $curlOptions ] = $curlItem + [ null, null, null, null, null, null ];

                $dataType = $dataType ?? 'raw';

                $curlItem = $this->newCurlItem();
                $curlItem->setUrl($url);

                if ( null !== $method ) $curlItem->setMethod($method);
                if ( null !== $data ) $curlItem->setData($dataType, $data);
                if ( null !== $headers ) $curlItem->setHeaders($headers);
                if ( null !== $curlOptions ) $curlItem->setCurlOptions($curlOptions);

            } elseif ( is_string($curlItem) ) {
                $url = $curlItem;

                $curlItem = $this->newCurlItem();
                $curlItem->setUrl($url);
            }

            $cp->add($curlItem);
        }

        yield from $cp->execSingle();
    }

    /**
     * @param CurlItem[]|array[]|string[] $curlItems
     *
     * @return \Generator<string, AbstractOnCurlEvent>
     */
    public function execMulti(array $curlItems) : \Generator
    {
        $cp = $this->newCurlProcess();

        foreach ( $curlItems as $curlItem ) {
            if ( $curlItem instanceof CurlItem ) {
                //

            } elseif ( is_array($curlItem) ) {
                [ $url, $method, $data, $dataType, $headers, $curlOptions ] = $curlItem + [ null, null, null, null, null, null ];

                $dataType = $dataType ?? 'raw';

                $curlItem = $this->newCurlItem();
                $curlItem->setUrl($url);

                if ( null !== $method ) $curlItem->setMethod($method);
                if ( null !== $data ) $curlItem->setData($dataType, $data);
                if ( null !== $headers ) $curlItem->setHeaders($headers);
                if ( null !== $curlOptions ) $curlItem->setCurlOptions($curlOptions);

            } elseif ( is_string($curlItem) ) {
                $url = $curlItem;

                $curlItem = $this->newCurlItem();
                $curlItem->setUrl($url);
            }

            $cp->add($curlItem);
        }

        yield from $cp->execMulti();
    }

    /**
     * @param CurlItem[]|array[]|string[] $curlItems
     *
     * @return \Generator<string, AbstractOnCurlEvent>
     */
    public function execBatch(int $batchSize, array $curlItems) : \Generator
    {
        $cp = $this->newCurlProcess();

        foreach ( $curlItems as $curlItem ) {
            if ( $curlItem instanceof CurlItem ) {
                //

            } elseif ( is_array($curlItem) ) {
                [ $url, $method, $data, $dataType, $headers, $curlOptions ] = $curlItem + [ null, null, null, null, null, null ];

                $dataType = $dataType ?? 'raw';

                $curlItem = $this->newCurlItem();
                $curlItem->setUrl($url);

                if ( null !== $method ) $curlItem->setMethod($method);
                if ( null !== $data ) $curlItem->setData($dataType, $data);
                if ( null !== $headers ) $curlItem->setHeaders($headers);
                if ( null !== $curlOptions ) $curlItem->setCurlOptions($curlOptions);

            } elseif ( is_string($curlItem) ) {
                $url = $curlItem;

                $curlItem = $this->newCurlItem();
                $curlItem->setUrl($url);
            }

            $cp->add($curlItem);
        }

        yield from $cp->execBatch($batchSize);
    }


    public function getCurlConstants() : array
    {
        $this->loadCurlConstants();

        return [
            'CURLOPT' => static::$curlOptConstantsMap,
        ];
    }

    public function getCurlOptConstants() : array
    {
        $this->loadCurlConstants();

        return static::$curlOptConstantsMap;
    }

    public function hasCurlOpt(int $opt) : bool
    {
        $this->loadCurlConstants();

        return array_key_exists($opt, static::$curlOptConstantsIndex);
    }

    public function hasCurlOptName(string $optName) : bool
    {
        $this->loadCurlConstants();

        return array_key_exists($optName, static::$curlOptConstantsMap);
    }

    protected function loadCurlConstants() : void
    {
        if ( static::$curlConstantsLoaded ) {
            return;
        }

        $listConst = get_defined_constants(true);

        static::$curlConstantsLoaded = true;

        if ( ! isset($listConst['curl']) ) {
            return;
        }

        foreach ( $listConst['curl'] as $name => $value ) {
            if ( false ) {
                //

            } elseif ( 'CURLOPT_' === substr($name, 0, 8) ) {
                static::$curlOptConstantsMap[$name] = $value;
                static::$curlOptConstantsIndex[$value] = array_merge(
                    static::$curlOptConstantsIndex[$value] ?? [],
                    [ $name ]
                );
            }
        }
    }

    /**
     * @var bool
     */
    protected static $curlConstantsLoaded = false;
    /**
     * @var array
     */
    protected static $curlOptConstantsMap = [];
    /**
     * @var array
     */
    protected static $curlOptConstantsIndex = [];
}
