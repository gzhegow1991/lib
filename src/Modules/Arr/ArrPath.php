<?php

namespace Gzhegow\Lib\Modules\Arr;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
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
     * @return static|bool|null
     */
    public static function fromValid($from, array $refs = [])
    {
        $withErrors = array_key_exists(0, $refs);

        $refs[ 0 ] = $refs[ 0 ] ?? null;

        $instance = null
            ?? static::fromStatic($from, $refs)
            ?? static::fromValidArray($from, $refs);

        if (! $withErrors) {
            if (null === $instance) {
                throw $refs[ 0 ];
            }
        }

        return $instance;
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, array $refs = [])
    {
        if ($from instanceof static) {
            return Lib::refsResult($refs, $from);
        }

        return Lib::refsError(
            $refs,
            new LogicException(
                [ 'The `from` must be instance of: ' . static::class, $from ]
            )
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromValidArray($from, array $refs = [])
    {
        if (is_array($from)) {
            $instance = new static();
            $instance->path = $from;

            return Lib::refsResult($refs, $instance);
        }

        return Lib::refsError(
            $refs,
            new LogicException(
                [ 'The `from` must be array', $from ]
            )
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
