<?php

namespace Gzhegow\Lib\Exception\Interfaces;

interface HasTraceOverrideInterface
{
    public function getFileOverride(?string $fileRoot = null) : string;

    public function getLineOverride();


    public function getTraceOverride(?string $fileRoot = null) : array;

    public function getTraceAsStringOverride(?string $fileRoot = null) : string;
}
