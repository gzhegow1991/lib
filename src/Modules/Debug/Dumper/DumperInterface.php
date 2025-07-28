<?php

namespace Gzhegow\Lib\Modules\Debug\Dumper;

interface DumperInterface
{
    public function hasSymfonyVarDumper() : bool;


    /**
     * @return static
     */
    public function selectPrinter(?string $printer, ?array $printerOptions = null);

    public function printerPrint(...$vars) : string;


    /**
     * @return static
     */
    public function selectDumper(?string $dumper, ?array $dumperOptions = null);

    public function dumperEcho(...$vars) : void;


    public function dp(?array $trace, $var, ...$vars) : string;

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
