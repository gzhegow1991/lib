<?php

namespace Gzhegow\Lib\Modules\Async\Loop;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Async\Clock\Timeout;
use Gzhegow\Lib\Modules\Async\Clock\Interval;


class LoopManager implements LoopManagerInterface
{
    /**
     * @var bool
     */
    protected $isLoopRegistered = false;

    /**
     * @var \SplObjectStorage<Interval>
     */
    protected $intervals;
    /**
     * @var \SplObjectStorage<Timeout>
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


    public function __construct()
    {
        $this->intervals = new \SplObjectStorage();
        $this->timers = new \SplObjectStorage();

        $this->resetQueueMicrotask();
        $this->resetQueueMacrotask();
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
     * @return static
     */
    public function addInterval(Interval $interval)
    {
        $this->intervals->attach($interval);

        return $this;
    }

    /**
     * @return static
     */
    public function clearInterval(Interval $interval)
    {
        $this->intervals->detach($interval);

        return $this;
    }


    /**
     * @return static
     */
    public function addTimeout(Timeout $timer)
    {
        $this->timers->attach($timer);

        return $this;
    }

    /**
     * @return static
     */
    public function clearTimeout(Timeout $timer)
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

        return $this;
    }


    /**
     * @param callable $fn
     *
     * @return static
     */
    public function requestNextFrame($fn)
    {
        $this->queueMicrotask->enqueue($fn);

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
                if ($now >= $interval->timeoutMicrotime) {
                    $this->addMacrotask($interval->fnHandler);
                    $this->registerLoop();

                    $interval->timeoutMicrotime = $now + ($interval->waitMs / 1000);
                }

                unset($interval);
            }

            foreach ( $timers as $timer ) {
                if ($now >= $timer->timeoutMicrotime) {
                    $this->addMacrotask($timer->fnHandler);
                    $this->registerLoop();

                    $timers->detach($timer);
                }

                unset($timer);
            }

            $queue = $this->queueMicrotask;
            $this->resetQueueMicrotask($bufferMicrotask);

            while ( ! $queue->isEmpty() ) {
                $fn = $queue->dequeue();

                call_user_func($fn, $now);

                unset($fn);
            }

            $queue = $this->queueMacrotask;
            $this->resetQueueMacrotask($bufferMacrotask);

            while ( ! $queue->isEmpty() ) {
                $fn = $queue->dequeue();

                call_user_func($fn, $now);

                unset($fn);
            }

            unset($queue);

            usleep(1000);

        } while ( ! (true
            && (0 === count($intervals))
            && (0 === count($timers))
            && $bufferMicrotask->isEmpty()
            && $bufferMacrotask->isEmpty()
        ) );

        return $this;
    }

    /**
     * @return static
     */
    public function registerLoop()
    {
        if (! $this->isLoopRegistered) {
            Lib::entrypoint()->registerShutdownFunction([ $this, 'runLoop' ]);

            $this->isLoopRegistered = true;
        }

        return $this;
    }
}
