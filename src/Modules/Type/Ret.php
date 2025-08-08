<?php

namespace Gzhegow\Lib\Modules\Type;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Type\Ret\Base\AbstractRet;


/**
 * @template T of mixed
 *
 * @mixin AbstractRet
 */
class Ret
{
    /**
     * @return static
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public static function new()
    {
        $className = (PHP_VERSION_ID >= 80000)
            ? '\Gzhegow\Lib\Modules\Type\Ret\PHP8\Ret'
            : '\Gzhegow\Lib\Modules\Type\Ret\PHP7\Ret';

        return new $className();
    }


    /**
     * @param T $value
     *
     * @return static<T>
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public static function val($value)
    {
        if ($value instanceof AbstractRet) {
            throw new LogicException(
                [ 'The `value` should not be instance of: ' . static::class, $value ]
            );
        }

        $className = (PHP_VERSION_ID >= 80000)
            ? '\Gzhegow\Lib\Modules\Type\Ret\PHP8\Ret'
            : '\Gzhegow\Lib\Modules\Type\Ret\PHP7\Ret';

        $instance = new $className();

        $instance->value = [ $value ];

        return $instance;
    }

    /**
     * @param static|mixed               $throwableArg
     *
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return static<T>
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public static function err($throwableArg, array $fileLine = [], ...$throwableArgs)
    {
        $className = (PHP_VERSION_ID >= 80000)
            ? '\Gzhegow\Lib\Modules\Type\Ret\PHP8\Ret'
            : '\Gzhegow\Lib\Modules\Type\Ret\PHP7\Ret';

        $instance = new $className();

        if ($throwableArg instanceof AbstractRet) {
            $instance->mergeFrom($throwableArg);

        } else {
            $fileLine = $fileLine ?: Lib::debug()->file_line();

            $instance->addError($throwableArg, $fileLine, ...$throwableArgs);
        }

        return $instance;
    }


    /**
     * @param T $value
     *
     * @return T|static<T>
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public static function ok(?array $fallback, $value)
    {
        if ($value instanceof AbstractRet) {
            throw new LogicException(
                [ 'The `value` should not be instance of: ' . static::class, $value ]
            );
        }

        if (null === $fallback) {
            $className = (PHP_VERSION_ID >= 80000)
                ? '\Gzhegow\Lib\Modules\Type\Ret\PHP8\Ret'
                : '\Gzhegow\Lib\Modules\Type\Ret\PHP7\Ret';

            $instance = new $className();

            $instance->value = [ $value ];

            return $instance;
        }

        return $value;
    }

    /**
     * @param static|mixed               $throwableArg
     *
     * @param array{ 0: string, 1: int } $fileLine
     *
     * @return T|static<T>
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public static function throw(?array $fallback, $throwableArg, array $fileLine = [], ...$throwableArgs)
    {
        $className = (PHP_VERSION_ID >= 80000)
            ? '\Gzhegow\Lib\Modules\Type\Ret\PHP8\Ret'
            : '\Gzhegow\Lib\Modules\Type\Ret\PHP7\Ret';

        $instance = new $className();

        if ($throwableArg instanceof AbstractRet) {
            $instance->mergeFrom($throwableArg);

            if ([] !== $throwableArgs) {
                $fileLine = $fileLine ?: Lib::debug()->file_line(2);

                $instance->addError(null, $fileLine, ...$throwableArgs);
            }

        } else {
            $fileLine = $fileLine ?: Lib::debug()->file_line(2);

            $instance->addError($throwableArg, $fileLine, ...$throwableArgs);
        }

        if (null === $fallback) {
            return $instance;
        }

        if ([] !== $fallback) {
            return $fallback[ 0 ];
        }

        return $instance->orThrow();
    }
}
