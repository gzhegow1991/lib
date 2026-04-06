<?php

namespace Gzhegow\Lib\Exception\Interfaces;


interface HasTraceOverrideInterface
{
    public function hasFileOverride() : bool;

    public function getFileOverride(?string $dirRoot = null) : string;

    public function setFileOverride(?string $file);


    public function hasLineOverride() : bool;

    public function getLineOverride() : int;

    public function setLineOverride(?int $line);


    public function hasTraceOverride() : bool;

    public function getTraceOverride(?string $dirRoot = null) : array;

    public function getTraceAsStringOverride(?string $dirRoot = null) : string;

    public function setTraceOverride(?array $trace);
}
