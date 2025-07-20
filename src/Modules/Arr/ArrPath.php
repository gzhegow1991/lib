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
     * @return static|Ret<static>
     */
    public static function fromValid($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromValidArray($from)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::val($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
            return Ret::val($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromValidArray($from, ?array $fallback = null)
    {
        if (is_array($from)) {
            $instance = new static();
            $instance->path = $from;

            return Ret::val($fallback, $instance);
        }

        return Ret::throw(
            $fallback,
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
