<?php

namespace Gzhegow\Lib;

use Gzhegow\Lib\Modules\FsModule;
use Gzhegow\Lib\Modules\MbModule;
use Gzhegow\Lib\Modules\CliModule;
use Gzhegow\Lib\Modules\NetModule;
use Gzhegow\Lib\Modules\PhpModule;
use Gzhegow\Lib\Modules\StrModule;
use Gzhegow\Lib\Modules\UrlModule;
use Gzhegow\Lib\Modules\ArrModule;
use Gzhegow\Lib\Modules\JsonModule;
use Gzhegow\Lib\Modules\HttpModule;
use Gzhegow\Lib\Modules\TestModule;
use Gzhegow\Lib\Modules\TypeModule;
use Gzhegow\Lib\Modules\PregModule;
use Gzhegow\Lib\Modules\DebugModule;
use Gzhegow\Lib\Modules\ParseModule;
use Gzhegow\Lib\Modules\CryptModule;
use Gzhegow\Lib\Modules\BcmathModule;
use Gzhegow\Lib\Modules\AssertModule;
use Gzhegow\Lib\Modules\EscapeModule;
use Gzhegow\Lib\Modules\FormatModule;
use Gzhegow\Lib\Modules\RandomModule;
use Gzhegow\Lib\Modules\ItertoolsModule;


class Lib
{
    /**
     * @return ArrModule
     */
    public static function arr(ArrModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new ArrModule();
    }

    /**
     * @return AssertModule
     */
    public static function assert(AssertModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new AssertModule();
    }

    /**
     * @return BcmathModule
     */
    public static function bcmath(BcmathModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new BcmathModule();
    }

    /**
     * @return CliModule
     */
    public static function cli(CliModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new CliModule();
    }

    /**
     * @return CryptModule
     */
    public static function crypt(CryptModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new CryptModule();
    }

    /**
     * @return DebugModule
     */
    public static function debug(DebugModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new DebugModule();
    }

    /**
     * @return EscapeModule
     */
    public static function escape(EscapeModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new EscapeModule();
    }

    /**
     * @return FormatModule
     */
    public static function format(FormatModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new FormatModule();
    }

    /**
     * @return FsModule
     */
    public static function fs(FsModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new FsModule();
    }

    /**
     * @return HttpModule
     */
    public static function http(HttpModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new HttpModule();
    }

    /**
     * @return ItertoolsModule
     */
    public static function itertools(ItertoolsModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new ItertoolsModule();
    }

    /**
     * @return ItertoolsModule
     */
    public static function json(JsonModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new JsonModule();
    }

    /**
     * @return MbModule
     */
    public static function mb(MbModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new MbModule();
    }

    /**
     * @return NetModule
     */
    public static function net(NetModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new NetModule();
    }

    /**
     * @return ParseModule
     */
    public static function parse(ParseModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new ParseModule();
    }

    /**
     * @return PhpModule
     */
    public static function php(PhpModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new PhpModule();
    }

    /**
     * @return PregModule
     */
    public static function preg(PregModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new PregModule();
    }

    /**
     * @return RandomModule
     */
    public static function random(RandomModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new RandomModule();
    }

    /**
     * @return StrModule
     */
    public static function str(StrModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new StrModule();
    }

    /**
     * @return TestModule
     */
    public static function test(TestModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new TestModule();
    }

    /**
     * @return TypeModule
     */
    public static function type(TypeModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new TypeModule();
    }

    /**
     * @return UrlModule
     */
    public static function url(UrlModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new UrlModule();
    }


    /**
     * @throws \LogicException|\RuntimeException
     */
    public static function throw($throwableOrArg, ...$throwableArgs)
    {
        if (
            ($throwableOrArg instanceof \LogicException)
            || ($throwableOrArg instanceof \RuntimeException)
        ) {
            throw $throwableOrArg;
        }

        array_unshift($throwableArgs, $throwableOrArg);

        $thePhp = Lib::php();

        $throwableClass = $thePhp->static_throwable_class();

        $trace = property_exists($throwableClass, 'trace')
            ? debug_backtrace()
            : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $_throwableArgs = $thePhp->throwable_args(...$throwableArgs);
        $_throwableArgs[ 'file' ] = $trace[ 0 ][ 'file' ] ?? '{file}';
        $_throwableArgs[ 'line' ] = $trace[ 0 ][ 'line' ] ?? 0;
        $_throwableArgs[ 'trace' ] = $trace;

        $exceptionArgs = [];
        $exceptionArgs[] = $_throwableArgs[ 'message' ] ?? null;
        $exceptionArgs[] = $_throwableArgs[ 'code' ] ?? null;
        $exceptionArgs[] = $_throwableArgs[ 'previous' ] ?? null;

        $e = new $throwableClass(...$exceptionArgs);

        foreach ( $_throwableArgs as $key => $value ) {
            if (! property_exists($e, $key)) {
                unset($_throwableArgs[ $key ]);
            }
        }

        $fn = (function () use (&$_throwableArgs) {
            foreach ( $_throwableArgs as $key => $value ) {
                $this->{$key} = $value;
            }
        })->bindTo($e, $e);

        $fn();

        throw $e;
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public static function throw_new(...$throwableArgs)
    {
        $thePhp = Lib::php();

        $throwableClass = $thePhp->static_throwable_class();

        $trace = property_exists($throwableClass, 'trace')
            ? debug_backtrace()
            : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $_throwableArgs = $thePhp->throwable_args(...$throwableArgs);
        $_throwableArgs[ 'file' ] = $trace[ 0 ][ 'file' ] ?? '{file}';
        $_throwableArgs[ 'line' ] = $trace[ 0 ][ 'line' ] ?? 0;
        $_throwableArgs[ 'trace' ] = $trace;

        $exceptionArgs = [];
        $exceptionArgs[] = $_throwableArgs[ 'message' ] ?? null;
        $exceptionArgs[] = $_throwableArgs[ 'code' ] ?? null;
        $exceptionArgs[] = $_throwableArgs[ 'previous' ] ?? null;

        $e = new $throwableClass(...$exceptionArgs);

        foreach ( $_throwableArgs as $key => $value ) {
            if (! property_exists($e, $key)) {
                unset($_throwableArgs[ $key ]);
            }
        }

        $fn = (function () use (&$_throwableArgs) {
            foreach ( $_throwableArgs as $key => $value ) {
                $this->{$key} = $value;
            }
        })->bindTo($e, $e);

        $fn();

        throw $e;
    }


    /**
     * > gzhegow, thanks to PHP COMMUNITY!!111 we have to ensure internal types on all old classes
     *
     * @template-covariant T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function new7(string $class, ...$args)
    {
        if (PHP_VERSION_ID < 80000) {
            return new $class(...$args);
        }

        $pi = Lib::php()->pathinfo($class, null, '\\', 1);
        $namespace = $pi[ 'dirname' ];
        $namespace .= '\\PHP8';
        $classname = $pi[ 'filename' ];

        $fqcn = "\\{$namespace}\\{$classname}";

        $result = new $fqcn(...$args);

        return $result;
    }

    /**
     * > gzhegow, thanks to PHP COMMUNITY!!111 we have to ensure internal types on all old classes
     *
     * @template-covariant T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function new8(string $class, ...$args)
    {
        if (PHP_VERSION_ID >= 80000) {
            return new $class(...$args);
        }

        $pi = Lib::php()->pathinfo($class, null, '\\', 1);
        $namespace = $pi[ 'dirname' ];
        $namespace .= '\\PHP7';
        $classname = $pi[ 'filename' ];

        $fqcn = "\\{$namespace}\\{$classname}";

        $result = new $fqcn(...$args);

        return $result;
    }


    /**
     * @var array<string, mixed>
     */
    protected static $modules = [];
}
