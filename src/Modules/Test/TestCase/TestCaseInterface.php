<?php

namespace Gzhegow\Lib\Modules\Test\TestCase;

interface TestCaseInterface
{
    /**
     * @return static
     */
    public function fn(\Closure $fn, array $fnArgs = []);


    /**
     * @param resource|null $resource
     *
     * @return static
     */
    public function setResource($resource = null);

    /**
     * @return static
     */
    public function setTrace(?array $trace);


    /**
     * @return static
     */
    public function expectStdout(?string $stdout = '', &$refStdout = null);

    /**
     * @return static
     */
    public function expectStdoutIf(bool $if, ?string $stdout = '', &$refStdout = null);


    /**
     * @return static
     */
    public function expectSeconds(?float $secondsMin = 0.0, ?float $secondsMax = INF, &$refSeconds = null);

    /**
     * @return static
     */
    public function expectSecondsMin(?float $secondsMin = 0.0, &$refSeconds = null);

    /**
     * @return static
     */
    public function expectSecondsMax(?float $secondsMax = INF, &$refSeconds = null);

    /**
     * @return static
     */
    public function expectReturn(?array $return = [], &$refReturn = null);

    /**
     * @return static
     */
    public function expectMemoryMax(?string $memoryMax = '32M', &$refMemoryBytes = null);


    public function getFn() : array;


    /**
     * @return resource|null
     */
    public function getResource();

    public function getTrace() : ?array;


    /**
     * @param string $refStdout
     */
    public function hasStdout(&$refStdout = null) : bool;


    /**
     * @param float $refSecondsMin
     */
    public function hasSecondsMin(&$refSecondsMin = null) : bool;

    /**
     * @param float $refSecondsMax
     */
    public function hasSecondsMax(&$refSecondsMax = null) : bool;

    /**
     * @param array $refReturn
     */
    public function hasReturn(&$refReturn = null) : bool;

    /**
     * @param int $refMemoryMaxBytes
     */
    public function hasMemoryMax(&$refMemoryMaxBytes = null) : bool;


    public function run() : bool;
}
