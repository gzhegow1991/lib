<?php

namespace Gzhegow\Lib\Modules\Async\Loop;

use Gzhegow\Lib\Modules\Async\Clock\AsyncTimeout;
use Gzhegow\Lib\Modules\Async\Clock\AsyncInterval;


interface AsyncLoopManagerInterface
{
    /**
     * @return static
     */
    public function addInterval(AsyncInterval $interval);

    /**
     * @return static
     */
    public function clearInterval(AsyncInterval $interval);


    /**
     * @return static
     */
    public function addTimeout(AsyncTimeout $timer);

    /**
     * @return static
     */
    public function clearTimeout(AsyncTimeout $timer);


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
