<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class Exception extends \Exception
    implements ExceptionInterface
{
    use ExceptionTrait;


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
    protected $message;
    /**
     * @var int
     */
    protected $code;


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
