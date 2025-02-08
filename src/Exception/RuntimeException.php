<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class RuntimeException extends \RuntimeException
    implements ExceptionInterface
{
    use ThrowableTrait;


    /**
     * @var string
     */
    public $file;
    /**
     * @var int
     */
    public $line;

    /**
     * @var string
     */
    public $message;
    /**
     * @var int
     */
    public $code;

    /**
     * @var array
     */
    public $trace;

    /**
     * @var \Throwable
     */
    public $previous;
    /**
     * @var \Throwable[]
     */
    public $previousList = [];


    public function __construct(...$throwableArgs)
    {
        foreach ( Lib::php()->throwable_args(...$throwableArgs) as $k => $v ) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }

        parent::__construct($this->message, $this->code, $this->previous);
    }
}
