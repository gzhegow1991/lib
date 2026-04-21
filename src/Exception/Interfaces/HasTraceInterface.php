<?php

namespace Gzhegow\Lib\Exception\Interfaces;


interface HasTraceInterface
{
    public function getFile() : string;


    public function getLine() : int;


    public function getTrace() : array;

    public function getTraceAsString() : string;
}
