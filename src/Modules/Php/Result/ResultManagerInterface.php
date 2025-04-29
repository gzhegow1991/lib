<?php

namespace Gzhegow\Lib\Modules\Php\Result;


interface ResultManagerInterface
{
    public function type(?ResultContext &$ref = null) : ?ResultContext;

    public function parse(?ResultContext &$ref = null) : ?ResultContext;

    public function map(?ResultContext &$ref = null) : ?ResultContext;


    public function assert(?ResultContext &$ref = null) : ?ResultContext;

    public function assertType(?ResultContext &$ref = null) : ?ResultContext;

    public function assertParse(?ResultContext &$ref = null) : ?ResultContext;


    /**
     * @return ResultContext|mixed|true
     */
    public function ok(?ResultContext $ctx, $value);

    /**
     * @return ResultContext|null|false
     */
    public function err(?ResultContext $ctx, $error, array $trace = [], array $tags = []);
}
