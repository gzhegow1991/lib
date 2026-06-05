<?php

/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules\Curl;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Curl\Event\OnCurlDoneEvent;
use Gzhegow\Lib\Modules\Curl\Event\OnCurlErrorEvent;
use Gzhegow\Lib\Modules\Curl\Event\AbstractOnCurlEvent;
use Gzhegow\Lib\Modules\Curl\Event\OnCurlBatchEndEvent;
use Gzhegow\Lib\Modules\Curl\Event\OnCurlMultiInitEvent;
use Gzhegow\Lib\Modules\Curl\Event\OnCurlMultiExecEvent;
use Gzhegow\Lib\Modules\Curl\Event\OnCurlSingleExecEvent;
use Gzhegow\Lib\Modules\Curl\Event\OnCurlSingleInitEvent;
use Gzhegow\Lib\Modules\Curl\Event\OnCurlBatchStartEvent;


class CurlProcess
{
    /**
     * @var CurlItem
     */
    protected $curlItemBase;

    /**
     * @var CurlItem[]
     */
    protected $curlItems = [];

    /**
     * @var \SplQueue<CurlItem>
     */
    protected $curlQueueBatch;
    /**
     * @var \SplQueue<CurlItem>
     */
    protected $curlQueueChunk;


    public static function new()
    {
        return new static();
    }


    public function newCurlItem() : CurlItem
    {
        $instance = CurlItem::new();

        $instance->setCurlItemBase($this->curlItemBase);

        return $instance;
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


    public function add(CurlItem $item)
    {
        $this->curlItems[] = $item;

        return $this;
    }


    public function addGet(string $url)
    {
        $instance = $this->newCurlItem();
        $instance->setUrl($url);

        $this->curlItems[] = $instance;

        return $instance;
    }

    public function addPost(string $url)
    {
        $instance = $this->newCurlItem();
        $instance->setUrl($url);

        $this->curlItems[] = $instance;

        return $instance;
    }

    public function addPut(string $url)
    {
        $instance = $this->newCurlItem();
        $instance->setUrl($url);

        $this->curlItems[] = $instance;

        return $instance;
    }

    public function addPatch(string $url)
    {
        $instance = $this->newCurlItem();
        $instance->setUrl($url);

        $this->curlItems[] = $instance;

        return $instance;
    }

    public function addDelete(string $url)
    {
        $instance = $this->newCurlItem();
        $instance->setUrl($url);

        $this->curlItems[] = $instance;

        return $instance;
    }

    public function addOptions(string $url)
    {
        $instance = $this->newCurlItem();
        $instance->setUrl($url);

        $this->curlItems[] = $instance;

        return $instance;
    }

    public function addHead(string $url)
    {
        $instance = $this->newCurlItem();
        $instance->setUrl($url);

        $this->curlItems[] = $instance;

        return $instance;
    }

    public function addConnect(string $url)
    {
        $instance = $this->newCurlItem();
        $instance->setUrl($url);

        $this->curlItems[] = $instance;

        return $instance;
    }

    public function addTrace(string $url)
    {
        $instance = $this->newCurlItem();
        $instance->setUrl($url);

        $this->curlItems[] = $instance;

        return $instance;
    }


    /**
     * @return \Generator<string, AbstractOnCurlEvent>
     */
    public function execSingle() : \Generator
    {
        $this->curlQueueBatch = null;
        $this->curlQueueChunk = null;

        $this->curlQueueBatch = $curlQueue = new \SplQueue();

        foreach ( $this->curlItems as $curlItem ) {
            $curlQueue->enqueue($curlItem);
        }

        while ( ! $curlQueue->isEmpty() ) {
            /**
             * @var CurlItem[] $curlItemsBatch
             */
            $curlItemsBatch = [];

            while ( ! $curlQueue->isEmpty() ) {
                $curlItemsBatch[] = $curlQueue->dequeue();
            }

            $curlEvent = null;

            try {
                $curlEvent = new OnCurlBatchStartEvent($curlItemsBatch);
                yield 'onCurlBatchStart' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }

                yield from $this->processSingle($curlItemsBatch);

                $curlEvent = new OnCurlBatchEndEvent($curlItemsBatch);
                yield 'onCurlBatchEnd' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }
            }
            finally {
                if ( null !== $curlEvent ) {
                    if ( $curlEvent->isSkipped() ) {
                        $this->onCurlEventHandleSkip($curlEvent);
                    }
                }
            }
        }
    }

    /**
     * @return \Generator<string, AbstractOnCurlEvent>
     */
    public function execMulti() : \Generator
    {
        $this->curlQueueBatch = null;
        $this->curlQueueChunk = null;

        $this->curlQueueBatch = $curlQueue = new \SplQueue();

        foreach ( $this->curlItems as $curlItem ) {
            $curlQueue->enqueue($curlItem);
        }

        while ( ! $curlQueue->isEmpty() ) {
            /**
             * @var CurlItem[] $curlItemsBatch
             */
            $curlItemsBatch = [];

            while ( ! $curlQueue->isEmpty() ) {
                $curlItemsBatch[] = $curlQueue->dequeue();
            }

            $curlEvent = null;

            try {
                $curlEvent = new OnCurlBatchStartEvent($curlItemsBatch);
                yield 'onCurlBatchStart' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }

                yield from $this->processMulti($curlItemsBatch);

                $curlEvent = new OnCurlBatchEndEvent($curlItemsBatch);
                yield 'onCurlBatchEnd' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }
            }
            finally {
                if ( null !== $curlEvent ) {
                    if ( $curlEvent->isSkipped() ) {
                        $this->onCurlEventHandleSkip($curlEvent);
                    }
                }
            }
        }
    }

    /**
     * @return \Generator<string, AbstractOnCurlEvent>
     */
    public function execBatch(int $batchSize) : \Generator
    {
        $theType = Lib::type();

        $batchSizeInt = $theType->int_positive($batchSize)->orThrow();

        $this->curlQueueBatch = null;
        $this->curlQueueChunk = null;

        $this->curlQueueBatch = $curlQueue = new \SplQueue();

        foreach ( $this->curlItems as $curlItem ) {
            $curlQueue->enqueue($curlItem);
        }

        while ( ! $curlQueue->isEmpty() ) {
            /**
             * @var CurlItem[] $curlItemsBatch
             */
            $curlItemsBatch = [];

            $counter = 0;
            while ( ! $curlQueue->isEmpty() ) {
                $curlItemsBatch[] = $curlQueue->dequeue();

                $counter++;
                if ( $counter === $batchSizeInt ) {
                    break;
                }
            }

            $curlEvent = null;

            try {
                $curlEvent = new OnCurlBatchStartEvent($curlItemsBatch);
                yield 'onCurlBatchStart' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }

                yield from $this->processMulti($curlItemsBatch);

                $curlEvent = new OnCurlBatchEndEvent($curlItemsBatch);
                yield 'onCurlBatchEnd' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }
            }
            finally {
                if ( null !== $curlEvent ) {
                    if ( $curlEvent->isSkipped() ) {
                        $this->onCurlEventHandleSkip($curlEvent);
                    }
                }
            }
        }
    }


    /**
     * @return \Generator<string, AbstractOnCurlEvent>
     */
    protected function processSingle(array $curlItems) : \Generator
    {
        /**
         * @var \SplQueue<CurlItem> $curlQueue
         */
        $this->curlQueueChunk = $curlQueue = new \SplQueue();

        foreach ( $curlItems as $curlItem ) {
            $curlQueue->enqueue($curlItem);
        }

        while ( ! $curlQueue->isEmpty() ) {
            /**
             * @var CurlItem $curlItem
             */
            $curlItem = $curlQueue->dequeue();

            $ch = $curlItem->freshCurlHandle();

            $curlEvent = null;

            try {
                $curlEvent = new OnCurlSingleInitEvent(
                    $curlItem
                );
                yield 'onCurlInit' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }

                curl_exec($ch);

                $curlEvent = new OnCurlSingleExecEvent(
                    $curlItem
                );
                yield 'onCurlExec' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }

                $curlErrno = curl_errno($ch);

                if ( CURLE_OK !== $curlErrno ) {
                    $curlError = curl_error($ch);

                    $curlEvent = new OnCurlErrorEvent(
                        $curlItem,
                        $curlErrno, $curlError
                    );
                    yield 'onCurlError' => $curlEvent;

                    continue;
                }

                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $httpEffectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                $httpEffectiveMethod = null;

                if ( PHP_VERSION_ID >= 80200 ) {
                    $httpEffectiveMethod = curl_getinfo($ch, CURLINFO_EFFECTIVE_METHOD);
                }

                $curlItem->addCurlHandleData([
                    'httpCode'            => $httpCode,
                    'httpEffectiveUrl'    => $httpEffectiveUrl,
                    'httpEffectiveMethod' => $httpEffectiveMethod,
                ]);

                $curlEvent = new OnCurlDoneEvent(
                    $curlItem,
                    $httpCode, $httpEffectiveUrl, $httpEffectiveMethod
                );
                yield 'onCurlDone' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }
            }
            finally {
                if ( null !== $curlEvent ) {
                    if ( $curlEvent->isSkipped() ) {
                        $this->onCurlEventHandleSkip($curlEvent);
                    }
                }

                curl_close($ch);
            }
        }
    }

    /**
     * @noinspection PhpUnnecessaryStopStatementInspection
     */
    protected function processMulti(array $curlItems) : \Generator
    {
        /**
         * @var \SplQueue<CurlItem> $curlQueue
         */
        $this->curlQueueChunk = $curlQueue = new \SplQueue();

        foreach ( $curlItems as $curlItem ) {
            $curlQueue->enqueue($curlItem);
        }

        while ( ! $curlQueue->isEmpty() ) {
            /**
             * @var CurlItem[] $curlItemsChunk
             */
            $curlItemsChunk = [];

            $mh = curl_multi_init();
            $chh = [];

            while ( ! $curlQueue->isEmpty() ) {
                /**
                 * @var CurlItem $curlItem
                 */
                $curlItem = $curlQueue->dequeue();
                $curlItemsChunk[] = $curlItem;

                $ch = $curlItem->freshCurlHandle();
                $chh[] = $ch;

                curl_multi_add_handle($mh, $ch);
            }

            $curlEvent = null;

            try {
                $curlEvent = new OnCurlMultiInitEvent($curlItemsChunk);
                yield 'onCurlMultiInit' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }

                $running = null;
                do {
                    $status = curl_multi_exec($mh, $running);

                    if ( $running > 0 ) {
                        curl_multi_select($mh);
                    }
                } while ( ($running > 0) && ($status === CURLM_OK) );

                $curlEvent = new OnCurlMultiExecEvent($curlItemsChunk);
                yield 'onCurlMultiExec' => $curlEvent;
                if ( $curlEvent->isSkipped() ) {
                    continue;
                }

                foreach ( $curlItemsChunk as $curlItem ) {
                    $ch = $curlItem->getCurlHandle();

                    $curlErrno = curl_errno($ch);

                    if ( CURLE_OK !== $curlErrno ) {
                        $curlError = curl_error($ch);

                        $curlEvent = new OnCurlErrorEvent(
                            $curlItem,
                            $curlErrno, $curlError
                        );
                        yield 'onCurlError' => $curlEvent;

                        continue;
                    }

                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $httpEffectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                    $httpEffectiveMethod = null;

                    if ( PHP_VERSION_ID >= 80200 ) {
                        $httpEffectiveMethod = curl_getinfo($ch, CURLINFO_EFFECTIVE_METHOD);
                    }

                    $curlItem->addCurlHandleData([
                        'httpCode'            => $httpCode,
                        'httpEffectiveUrl'    => $httpEffectiveUrl,
                        'httpEffectiveMethod' => $httpEffectiveMethod,
                    ]);

                    $curlEvent = new OnCurlDoneEvent(
                        $curlItem,
                        $httpCode, $httpEffectiveUrl, $httpEffectiveMethod
                    );
                    yield 'onCurlDone' => $curlEvent;
                    if ( $curlEvent->isSkipped() ) {
                        continue;
                    }
                }
            }
            finally {
                if ( null !== $curlEvent ) {
                    if ( $curlEvent->isSkipped() ) {
                        $this->onCurlEventHandleSkip($curlEvent);
                    }
                }

                foreach ( $chh as $ch ) {
                    curl_multi_remove_handle($mh, $ch);
                    curl_close($ch);
                }

                curl_multi_close($mh);
            }
        }
    }


    protected function onCurlEventHandleSkip(AbstractOnCurlEvent $curlEvent) : void
    {
        if ( ! $curlEvent->isSkipped() ) {
            return;
        }

        $pushBeforeBatch = $curlEvent->getPushBeforeBatch();
        $pushBeforeChunk = $curlEvent->getPushBeforeChunk();
        $pushAfterChunk = $curlEvent->getPushAfterChunk();
        $pushAfterBatch = $curlEvent->getPushAfterBatch();

        while ( [] !== $pushBeforeBatch ) {
            $pb = array_pop($pushBeforeBatch);

            $this->curlQueueBatch->unshift($pb);
        }

        while ( [] !== $pushBeforeChunk ) {
            $pb = array_pop($pushBeforeChunk);

            $this->curlQueueChunk->unshift($pb);
        }

        foreach ( $pushAfterChunk as $pa ) {
            $this->curlQueueChunk->push($pa);
        }

        foreach ( $pushAfterBatch as $pa ) {
            $this->curlQueueBatch->push($pa);
        }
    }
}
