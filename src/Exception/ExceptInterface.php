<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Interfaces\HasTraceInterface;
use Gzhegow\Lib\Exception\Interfaces\HasPreviousInterface;
use Gzhegow\Lib\Exception\Interfaces\HasMessageListInterface;
use Gzhegow\Lib\Exception\Interfaces\HasPreviousListInterface;
use Gzhegow\Lib\Exception\Interfaces\HasTraceOverrideInterface;
use Gzhegow\Lib\Exception\Interfaces\HasPreviousOverrideInterface;


interface ExceptInterface extends
    HasPreviousInterface,
    HasTraceInterface,
    //
    HasPreviousOverrideInterface,
    HasTraceOverrideInterface,
    //
    HasMessageListInterface,
    HasPreviousListInterface
{
    public function getMessage() : ?string;

    public function getCode() : ?int;
}
