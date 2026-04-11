<?php

namespace Gzhegow\Lib\Modules\Arr;

use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;


class ArrPath implements
    ToArrayInterface
{
    /**
     * @var array
     */
    protected $path;


    private function __construct()
    {
    }


    /**
     * @return Ret<static>|static
     */
    public static function fromValid($from, $fb = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromValidArray($from)->orNull($ret);

        if ( ! $ret->isOk() ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $instance);
    }

    /**
     * @return Ret<static>|static
     */
    public static function fromStatic($from, $fb = null)
    {
        if ( $from instanceof static ) {
            return Ret::ok($fb, $from);
        }

        return Ret::throw(
            $fb,
            [ 'The `from` should be an instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<static>|static
     */
    public static function fromValidArray($from, $fb = null)
    {
        if ( is_array($from) ) {
            $instance = new static();
            $instance->path = $from;

            return Ret::ok($fb, $instance);
        }

        return Ret::throw(
            $fb,
            [ 'The `from` should be an array', $from ],
            [ __FILE__, __LINE__ ]
        );
    }


    public function toArray(array $options = []) : array
    {
        return $this->path;
    }


    public function getPath() : array
    {
        return $this->path;
    }
}
