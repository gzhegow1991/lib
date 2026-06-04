<?php

namespace Gzhegow\Lib\Modules\Php\Microtimer;


class Microtimer
{
    /**
     * @var float[]
     */
    public $microtimes = [];
    /**
     * @var float[][]
     */
    public $report = [];


    public static function new()
    {
        return new static();
    }


    public function __invoke($stringTag = null, $isDiff = null)
    {
        if ( null === $stringTag ) {
            $last = $this->report;

            $this->microtimes = [];
            $this->report = [];

            return $last;
        }

        return $isDiff
            ? $this->mtdiff($stringTag)
            : $this->mt($stringTag);
    }


    public function mt($stringTag = null)
    {
        /** @var float $microtime */

        $microtime = microtime(true);

        $stringTag = $stringTag ?? '';

        $this->report[$stringTag] = [];

        $this->microtimes[$stringTag] = $microtime;

        return $microtime;
    }

    public function mtdiff($stringTag = null)
    {
        /** @var float $microtime */

        $microtime = microtime(true);

        $stringTag = $stringTag ?? '';

        $diff = 0.0;

        if ( isset($this->microtimes[$stringTag]) ) {
            $diff = $microtime - $this->microtimes[$stringTag];

            $this->report[$stringTag][] = $diff;
        }

        $this->microtimes[$stringTag] = $microtime;

        return $diff;
    }
}
