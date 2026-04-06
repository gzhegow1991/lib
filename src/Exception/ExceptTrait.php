<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Exception\Traits\HasTraceTrait;
use Gzhegow\Lib\Exception\Traits\HasPreviousTrait;
use Gzhegow\Lib\Exception\Traits\HasMessageListTrait;
use Gzhegow\Lib\Exception\Traits\HasPreviousListTrait;
use Gzhegow\Lib\Exception\Traits\HasTraceOverrideTrait;
use Gzhegow\Lib\Exception\Traits\HasPreviousOverrideTrait;


trait ExceptTrait
{
    use HasPreviousTrait;
    use HasTraceTrait;

    use HasPreviousOverrideTrait;
    use HasTraceOverrideTrait;

    use HasMessageListTrait;
    use HasPreviousListTrait;


    /**
     * @var string
     */
    protected $message;
    /**
     * @var int
     */
    protected $code;


    public function getMessage() : ?string
    {
        return $this->message;
    }

    public function getCode() : ?int
    {
        return $this->code;
    }
}
