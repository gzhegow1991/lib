<?php

namespace Gzhegow\Lib\Modules\Debug\Dumper;

interface DumperInterface
{
    /**
     * @return static
     */
    public function printer(?string $printer, ?array $printerOptions = null);

    /**
     * @return static
     */
    public function dumper(?string $dumper, ?array $dumperOptions = null);


    public function print(...$vars) : string;


    /**
     * @return mixed
     */
    public function d(?array $trace, $var, ...$vars);

    /**
     * @return mixed|void
     */
    public function dd(?array $trace, ...$vars);

    /**
     * @return mixed|void
     */
    public function ddd(?array $trace, ?int $limit, $var, ...$vars);
}
