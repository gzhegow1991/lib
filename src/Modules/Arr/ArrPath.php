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
     * @return static
     */
    public static function from($from) // : static
    {
        $instance = static::tryFrom($from, $error);

        if (null === $instance) {
            throw $error;
        }

        return $instance;
    }

    /**
     * @return static
     */
    public static function fromValid(array $path)
    {
        $instance = new static();
        $instance->path = $path;

        return $instance;
    }


    /**
     * @return static|null
     */
    public static function tryFrom($from, \Throwable &$last = null) // : ?static
    {
        $last = null;

        Lib::php()->errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromArray($from);

        $errors = Lib::php()->errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, $last);
            }
        }

        return $instance;
    }


    /**
     * @return static|null
     */
    public static function tryFromInstance($instance) // : ?static
    {
        if (! ($instance instanceof static)) {
            return Lib::php()->error(
                [ 'The `from` should be instance of: ' . static::class, $instance ]
            );
        }

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFromArray($array) // : ?static
    {
        if (! is_array($array)) {
            return Lib::php()->error(
                [ 'The `from` should be array', $array ]
            );
        }

        $path = Lib::arr()->arrpath($array);

        $instance = new static();
        $instance->path = $path;

        return $instance;
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
