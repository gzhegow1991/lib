<?php

namespace Gzhegow\Lib\Modules\Arr;

use Gzhegow\Lib\Modules\Php\Result\Result;
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
    public static function fromValid($from, $ctx = null)
    {
        $ctxCur = Result::nullchain();

        $instance = null
            ?? static::fromStatic($from, $ctxCur)
            ?? static::fromValidArray($from, $ctxCur);

        if ($ctxCur->isErr()) {
            return Result::err($ctx, $ctxCur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, $ctx = null)
    {
        if ($from instanceof static) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` must be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromValidArray($from, $ctx = null)
    {
        if (is_array($from)) {
            $instance = new static();
            $instance->path = $from;

            return Result::ok($ctx, $instance);
        }

        return Result::err(
            $ctx,
            [ 'The `from` must be array', $from ],
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
