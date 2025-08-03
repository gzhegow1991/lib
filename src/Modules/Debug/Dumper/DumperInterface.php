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


    public function dp(?array $debugBacktraceOverride, $var, ...$vars) : string;

    public function fnDP(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure;


    /**
     * @return mixed
     */
    public function d(?array $debugBacktraceOverride, $var, ...$vars);

    /**
     * @return mixed|void
     */
    public function dd(?array $debugBacktraceOverride, ...$vars);

    /**
     * @return mixed|void
     */
    public function ddd(?array $debugBacktraceOverride, int $times, $var, ...$vars);


    public function fnD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure;

    public function fnDD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure;

    public function fnDDD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure;


    /**
     * @return mixed|void
     */
    public function td(?array $debugBacktraceOverride, int $throttleMs, $var, ...$vars);

    public function fnTD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure;
}
