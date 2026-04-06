<?php

namespace Gzhegow\Lib\Exception\Interfaces;


interface HasTraceInterface
{
    public function hasFile() : bool;

    public function getFile() : ?string;

    public function setFile(?string $file);


    public function hasLine() : bool;

    public function getLine() : ?int;

    public function setLine(?int $line);


    public function hasTrace() : bool;

    public function getTrace() : array;

    public function getTraceAsString() : string;

    public function setTrace(?array $trace);
}
