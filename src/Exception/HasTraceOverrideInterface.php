<?php

namespace Gzhegow\Lib\Exception;

interface HasTraceOverrideInterface
{
    public function getFileOverride() : string;

    public function getLineOverride() : int;


    public function getTraceOverride() : array;

    public function getTraceOverrideAsString() : string;
}
