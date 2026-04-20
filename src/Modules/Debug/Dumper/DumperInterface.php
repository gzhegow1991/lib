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

    public function dumperDump(...$vars) : void;


    public function dp(?array $trace, $var, ...$vars) : string;

    public function fnDP(?int $shift = null, ?array $trace = null) : \Closure;


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
    public function ddd(?array $trace, int $times, $var, ...$vars);


    public function fnD(?int $shift = null, ?array $trace = null) : \Closure;

    public function fnDD(?int $shift = null, ?array $trace = null) : \Closure;

    public function fnDDD(?int $shift = null, ?array $trace = null) : \Closure;


    /**
     * @return mixed|void
     */
    public function td(?array $trace, int $throttleMs, $var, ...$vars);

    public function fnTD(?int $shift = null, ?array $trace = null) : \Closure;
}
