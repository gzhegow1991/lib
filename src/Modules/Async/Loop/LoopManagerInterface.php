<?php

namespace Gzhegow\Lib\Modules\Async\Loop;

use Gzhegow\Lib\Modules\Async\Clock\Timeout;
use Gzhegow\Lib\Modules\Async\Clock\Interval;


interface LoopManagerInterface
{
    /**
     * @return static
     */
    public function addInterval(Interval $interval);

    /**
     * @return static
     */
    public function clearInterval(Interval $interval);


    /**
     * @return static
     */
    public function addTimeout(Timeout $timer);

    /**
     * @return static
     */
    public function clearTimeout(Timeout $timer);


    /**
     * @param callable $fnMicrotask
     *
     * @return static
     */
    public function addMicrotask($fnMicrotask);

    /**
     * @param callable $fnMacrotask
     *
     * @return static
     */
    public function addMacrotask($fnMacrotask);


    /**
     * @param callable $fn
     *
     * @return static
     */
    public function requestAnimationFrame($fn);


    /**
     * @return static
     */
    public function runLoop();
}
