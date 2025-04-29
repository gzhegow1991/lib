<?php

namespace Gzhegow\Lib\Exception\Interfaces;


interface HasTraceOverrideInterface
{
    public function getFileOverride(?string $dirRoot = null) : string;

    public function getLineOverride() : int;


    public function getTraceOverride(?string $dirRoot = null) : array;

    public function getTraceAsStringOverride(?string $fileRoot = null) : string;
}
