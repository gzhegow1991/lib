<?php

namespace Gzhegow\Lib\Exception\Iterator;

use Gzhegow\Lib\Exception\ExceptInterface;


class ExceptionIterator
{
    /**
     * @param (\Throwable|ExceptInterface)[] $items
     * @param (\Throwable|ExceptInterface)[] $track
     */
    public static function new(array $items, array $track = []) : object
    {
        return (PHP_VERSION_ID >= 80000)
            ? new \Gzhegow\Lib\Exception\Iterator\PHP8\ExceptionIterator($items, $track)
            : new \Gzhegow\Lib\Exception\Iterator\PHP7\ExceptionIterator($items, $track);
    }

    public static function is($instance) : bool
    {
        return false
            || ($instance instanceof \Gzhegow\Lib\Exception\Iterator\PHP8\ExceptionIterator)
            || ($instance instanceof \Gzhegow\Lib\Exception\Iterator\PHP7\ExceptionIterator);
    }
}
