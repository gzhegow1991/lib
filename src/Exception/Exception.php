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


    public function __construct(...$errors)
    {
        foreach ( Lib::php_throwable_args(...$errors) as $k => $v ) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }

        parent::__construct($this->message, $this->code, $this->previous);
    }
}
