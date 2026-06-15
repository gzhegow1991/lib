<?php

namespace Gzhegow\Lib\Modules\Async\Loop;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Async\Clock\AsyncTimeout;
use Gzhegow\Lib\Modules\Async\Clock\AsyncInterval;


class DefaultAsyncLoopManager implements AsyncLoopManagerInterface
{
    /**
     * @var \SplObjectStorage<AsyncInterval>
     */
    protected $intervals;
    /**
     * @var \SplObjectStorage<AsyncTimeout>
     */
    protected $timers;

    /**
     * @var \SplQueue<callable>
     */
    protected $queueMicrotask;
    /**
     * @var \SplQueue<callable>
     */
    protected $queueMacrotask;
    /**
     * @var \SplQueue<callable>
     */
    protected $queueAnimationFrame;


    public function __construct()
    {
        $this->intervals = new \SplObjectStorage();
        $this->timers = new \SplObjectStorage();

        $this->resetQueueMicrotask();
        $this->resetQueueMacrotask();
        $this->resetQueueAnimationFrame();
    }


    /**
     * @param \SplQueue $refQueue
     *
     * @return static
     */
    protected function resetQueueMicrotask(&$refQueue = null)
    {
        $this->queueMicrotask = new \SplQueue();
        $this->queueMicrotask->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO);

        $refQueue = $this->queueMicrotask;

        return $this;
    }

    /**
     * @param \SplQueue $refQueue
     *
     * @return static
     */
    protected function resetQueueMacrotask(&$refQueue = null)
    {
        $this->queueMacrotask = new \SplQueue();
        $this->queueMacrotask->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO);

        $refQueue = $this->queueMacrotask;

        return $this;
    }

    /**
     * @param \SplQueue $refQueue
     *
     * @return static
     */
    protected function resetQueueAnimationFrame(&$refQueue = null)
    {
        $this->queueAnimationFrame = new \SplQueue();
        $this->queueAnimationFrame->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO);

        $refQueue = $this->queueAnimationFrame;

        return $this;
    }


    /**
     * @return static
     */
    public function addInterval(AsyncInterval $interval)
    {
        $this->intervals->attach($interval);
        $this->registerShutdownFunction();

        return $this;
    }

    /**
     * @return static
     */
    public function clearInterval(AsyncInterval $interval)
    {
        $this->intervals->detach($interval);

        return $this;
    }


    /**
     * @return static
     */
    public function addTimeout(AsyncTimeout $timer)
    {
        $this->timers->attach($timer);
        $this->registerShutdownFunction();

        return $this;
    }

    /**
     * @return static
     */
    public function clearTimeout(AsyncTimeout $timer)
    {
        $this->timers->detach($timer);

        return $this;
    }


    /**
     * @param callable $fnMicrotask
     *
     * @return static
     */
    public function addMicrotask($fnMicrotask)
    {
        $this->queueMicrotask->enqueue($fnMicrotask);
        $this->registerShutdownFunction();

        return $this;
    }

    /**
     * @param callable $fnMacrotask
     *
     * @return static
     */
    public function addMacrotask($fnMacrotask)
    {
        $this->queueMacrotask->enqueue($fnMacrotask);
        $this->registerShutdownFunction();

        return $this;
    }


    /**
     * @param callable $fn
     *
     * @return static
     */
    public function requestAnimationFrame($fn)
    {
        $this->queueAnimationFrame->enqueue($fn);
        $this->registerShutdownFunction();

        return $this;
    }


    /**
     * @return static
     */
    public function runLoop()
    {
        $intervals = $this->intervals;
        $timers = $this->timers;

        do {
            $now = microtime(true);

            foreach ( $intervals as $interval ) {
                if ( $now >= $interval->timeoutMicrotime ) {
                    $this->addMacrotask($interval->fnHandler);

                    $interval->timeoutMicrotime = $now + ($interval->waitMs / 1000);
                }

                unset($interval);
            }

            foreach ( $timers as $timer ) {
                if ( $now >= $timer->timeoutMicrotime ) {
                    $this->addMacrotask($timer->fnHandler);

                    $timers->detach($timer);
                }

                unset($timer);
            }

            $queue = $this->queueMicrotask;
            $this->resetQueueMicrotask($bufferMicrotask);

            while ( ! $queue->isEmpty() ) {
                $fn = $queue->dequeue();

                // > call_user_func($fn)
                $fn();

                unset($fn);
            }

            $queue = $this->queueMacrotask;
            $this->resetQueueMacrotask($bufferMacrotask);

            while ( ! $queue->isEmpty() ) {
                $fn = $queue->dequeue();

                // > call_user_func($fn)
                $fn();

                unset($fn);
            }

            $queue = $this->queueAnimationFrame;
            $this->resetQueueAnimationFrame($bufferAnimationFrame);

            while ( ! $queue->isEmpty() ) {
                $fn = $queue->dequeue();

                // > call_user_func($fn, $now)
                $fn($now);

                unset($fn);
            }

            unset($queue);

            usleep(1000);

        } while ( ! (true
            && (0 === count($intervals))
            && (0 === count($timers))
            && $bufferMicrotask->isEmpty()
            && $bufferMacrotask->isEmpty()
            && $bufferAnimationFrame->isEmpty()
        ) );

        return $this;
    }


    public function registerShutdownFunction() : void
    {
        $theEntrypoint = Lib::entrypoint();

        $theEntrypoint->registerShutdownFunction([ $this, 'onShutdown_runLoop' ]);
    }

    public function onShutdown_runLoop() : void
    {
        $this->runLoop();
    }
}
