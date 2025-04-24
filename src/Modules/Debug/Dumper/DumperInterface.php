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
    public function dump($var, ...$vars);

    /**
     * @return mixed
     */
    public function d($var, ...$vars);

    public function dd(...$vars) : void;

    /**
     * @return mixed|void
     */
    public function ddd(?int $limit, $var, ...$vars);


    /**
     * @return mixed
     */
    public function dumpTrace(?array $trace, $var, ...$vars);

    /**
     * @return mixed
     */
    public function dTrace(?array $trace, $var, ...$vars);

    public function ddTrace(?array $trace, ...$vars) : void;

    /**
     * @return mixed|void
     */
    public function dddTrace(?array $trace, ?int $limit, $var, ...$vars);
}
