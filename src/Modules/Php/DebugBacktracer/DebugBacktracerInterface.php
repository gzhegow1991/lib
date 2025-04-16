<?php

namespace Gzhegow\Lib\Modules\Php\DebugBacktracer;

interface DebugBacktracerInterface
{
    /**
     * @return static
     */
    public function trace(array $trace);


    /**
     * @return static
     */
    public function options(?int $options = null);

    /**
     * @return static
     */
    public function limit(?int $limit = null);

    /**
     * @return static
     */
    public function rootDirectory(string $rootDirectory);


    /**
     * @return static
     */
    public function of(?array $of);

    /**
     * @return static
     */
    public function ofStartsWith(?array $of);


    /**
     * @return static
     */
    public function filter(?array $filter);

    /**
     * @return static
     */
    public function filterStartsWith(?array $filter);


    /**
     * @return static
     */
    public function filterNot(?array $filter);

    /**
     * @return static
     */
    public function filterNotStartsWith(?array $filter);


    /**
     * @return array<int, array{
     *     file: string,
     *     line: string,
     *     class: string|null,
     *     function: string|null,
     *     type: string|null,
     *     object: object|null,
     *     args: array|null,
     * }>
     */
    public function get() : array;

    /**
     * @return array{ 0: string, 1: int }|null
     */
    public function getFileLine() : ?array;
}
