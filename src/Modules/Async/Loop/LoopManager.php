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
     * @param \SplQueue $queue
     */
    protected function resetQueueMicrotask(&$queue = null) : void
    {
        $this->queueMicrotask = new \SplQueue();
        $this->queueMicrotask->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO);

        $queue = $this->queueMicrotask;
    }

    /**
     * @param \SplQueue $queue
     */
    protected function resetQueueMacrotask(&$queue = null) : void
    {
        $this->queueMacrotask = new \SplQueue();
        $this->queueMacrotask->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO);

        $queue = $this->queueMacrotask;
    }


    public function addInterval(Interval $interval) : void
    {
        $this->intervals->attach($interval);

        if (! $this->isLoopRegistered) {
            Lib::entrypoint()->registerShutdownFunction([ $this, 'runLoop' ]);

            $this->isLoopRegistered = true;
        }
    }

    public function clearInterval(Interval $interval) : void
    {
        $this->intervals->detach($interval);
    }


    public function addTimeout(Timeout $timer) : void
    {
        $this->timers->attach($timer);

        if (! $this->isLoopRegistered) {
            Lib::entrypoint()->registerShutdownFunction([ $this, 'runLoop' ]);

            $this->isLoopRegistered = true;
        }
    }

    public function clearTimeout(Timeout $timer) : void
    {
        $this->timers->detach($timer);
    }


    /**
     * @param callable $microtask
     *
     * @return void
     */
    public function addMicrotask($microtask) : void
    {
        $this->queueMicrotask->enqueue($microtask);

        if (! $this->isLoopRegistered) {
            Lib::entrypoint()->registerShutdownFunction([ $this, 'runLoop' ]);

            $this->isLoopRegistered = true;
        }
    }

    /**
     * @param callable $macrotask
     *
     * @return void
     */
    public function addMacrotask($macrotask) : void
    {
        $this->queueMacrotask->enqueue($macrotask);

        if (! $this->isLoopRegistered) {
            Lib::entrypoint()->registerShutdownFunction([ $this, 'runLoop' ]);

            $this->isLoopRegistered = true;
        }
    }


    public function runLoop() : void
    {
        $intervals = $this->intervals;
        $timers = $this->timers;

        do {

            $now = microtime(true);

            foreach ( $intervals as $interval ) {
                if ($now >= $interval->timeoutMicrotime) {
                    $this->addMacrotask($interval->fnHandler);

                    $interval->timeoutMicrotime = $now + ($interval->waitMs / 1000);
                }

                unset($interval);
            }

            foreach ( $timers as $timer ) {
                if ($now >= $timer->timeoutMicrotime) {
                    $this->addMacrotask($timer->fnHandler);

                    $timers->detach($timer);
                }

                unset($timer);
            }

            $queue = $this->queueMicrotask;
            $this->resetQueueMicrotask($bufferMicrotask);

            while ( ! $queue->isEmpty() ) {
                $fn = $queue->dequeue();

                call_user_func($fn);

                unset($fn);
            }

            $queue = $this->queueMacrotask;
            $this->resetQueueMacrotask($bufferMacrotask);

            while ( ! $queue->isEmpty() ) {
                $fn = $queue->dequeue();

                call_user_func($fn);

                unset($fn);
            }

            unset($queue);

            usleep(1000);

        } while ( ! (
            (0 === count($intervals))
            && (0 === count($timers))
            && $bufferMicrotask->isEmpty()
            && $bufferMacrotask->isEmpty()
        ) );
    }

    public function registerLoop() : void
    {
        if (! $this->isLoopRegistered) {
            Lib::entrypoint()->registerShutdownFunction([ $this, 'runLoop' ]);

            $this->isLoopRegistered = true;
        }
    }
}
