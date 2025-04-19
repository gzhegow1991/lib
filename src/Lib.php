<?php

namespace Gzhegow\Lib;

use Gzhegow\Lib\Modules\FsModule;
use Gzhegow\Lib\Modules\MbModule;
use Gzhegow\Lib\Modules\Php\Pipe;
use Gzhegow\Lib\Modules\CliModule;
use Gzhegow\Lib\Modules\NetModule;
use Gzhegow\Lib\Modules\PhpModule;
use Gzhegow\Lib\Modules\StrModule;
use Gzhegow\Lib\Modules\UrlModule;
use Gzhegow\Lib\Modules\ArrModule;
use Gzhegow\Lib\Modules\CmpModule;
use Gzhegow\Lib\Modules\JsonModule;
use Gzhegow\Lib\Modules\HttpModule;
use Gzhegow\Lib\Modules\TestModule;
use Gzhegow\Lib\Modules\TypeModule;
use Gzhegow\Lib\Modules\PregModule;
use Gzhegow\Lib\Modules\DateModule;
use Gzhegow\Lib\Modules\DebugModule;
use Gzhegow\Lib\Modules\ParseModule;
use Gzhegow\Lib\Modules\CryptModule;
use Gzhegow\Lib\Modules\BcmathModule;
use Gzhegow\Lib\Modules\AssertModule;
use Gzhegow\Lib\Modules\EscapeModule;
use Gzhegow\Lib\Modules\FormatModule;
use Gzhegow\Lib\Modules\RandomModule;
use Gzhegow\Lib\Modules\SocialModule;
use Gzhegow\Lib\Modules\ItertoolsModule;


class Lib
{
    /**
     * @return AssertModule
     */
    public static $assert;

    public static function assert($value, ?AssertModule $instance = null)
    {
        return static::$assert = $instance
            ?? static::$assert
            ?? new AssertModule($value);
    }

    /**
     * @return ParseModule
     */
    public static $parse;

    public static function parse(?ParseModule $instance = null)
    {
        return static::$parse = $instance
            ?? static::$parse
            ?? new ParseModule();
    }

    /**
     * @return TypeModule
     */
    public static $type;

    public static function type(?TypeModule $instance = null)
    {
        return static::$type = $instance
            ?? static::$type
            ?? new TypeModule();
    }


    /**
     * @return ArrModule
     */
    public static $arr;

    public static function arr(?ArrModule $instance = null)
    {
        return static::$arr = $instance
            ?? static::$arr
            ?? new ArrModule();
    }

    /**
     * @return BcmathModule
     */
    public static $bcmath;

    public static function bcmath(?BcmathModule $instance = null)
    {
        return static::$bcmath = $instance
            ?? static::$bcmath
            ?? new BcmathModule();
    }

    /**
     * @return CliModule
     */
    public static $cli;

    public static function cli(?CliModule $instance = null)
    {
        return static::$cli = $instance
            ?? static::$cli
            ?? new CliModule();
    }

    /**
     * @return CmpModule
     */
    public static $cmp;

    public static function cmp(?CmpModule $instance = null)
    {
        return static::$cmp = $instance
            ?? static::$cmp
            ?? new CmpModule();
    }

    /**
     * @return CryptModule
     */
    public static $crypt;

    public static function crypt(?CryptModule $instance = null)
    {
        return static::$crypt = $instance
            ?? static::$crypt
            ?? new CryptModule();
    }

    /**
     * @return DateModule
     */
    public static $date;

    public static function date(?DateModule $instance = null)
    {
        return static::$date = $instance
            ?? static::$date
            ?? new DateModule();
    }

    /**
     * @return DebugModule
     */
    public static $debug;

    public static function debug(?DebugModule $instance = null)
    {
        return static::$debug = $instance
            ?? static::$debug
            ?? new DebugModule();
    }

    /**
     * @return EscapeModule
     */
    public static $escape;

    public static function escape(?EscapeModule $instance = null)
    {
        return static::$escape = $instance
            ?? static::$escape
            ?? new EscapeModule();
    }

    /**
     * @return FormatModule
     */
    public static $format;

    public static function format(?FormatModule $instance = null)
    {
        return static::$format = $instance
            ?? static::$format
            ?? new FormatModule();
    }

    /**
     * @return FsModule
     */
    public static $fs;

    public static function fs(?FsModule $instance = null)
    {
        return static::$fs = $instance
            ?? static::$fs
            ?? new FsModule();
    }

    /**
     * @return HttpModule
     */
    public static $http;

    public static function http(?HttpModule $instance = null)
    {
        return static::$http = $instance
            ?? static::$http
            ?? new HttpModule();
    }

    /**
     * @return ItertoolsModule
     */
    public static $itertools;

    public static function itertools(?ItertoolsModule $instance = null)
    {
        return static::$itertools = $instance
            ?? static::$itertools
            ?? new ItertoolsModule();
    }

    /**
     * @return ItertoolsModule
     */
    public static $json;

    public static function json(?JsonModule $instance = null)
    {
        return static::$json = $instance
            ?? static::$json
            ?? new JsonModule();
    }

    /**
     * @return MbModule
     */
    public static $mb;

    public static function mb(?MbModule $instance = null)
    {
        return static::$mb = $instance
            ?? static::$mb
            ?? new MbModule();
    }

    /**
     * @return NetModule
     */
    public static $net;

    public static function net(?NetModule $instance = null)
    {
        return static::$net = $instance
            ?? static::$net
            ?? new NetModule();
    }

    /**
     * @return PhpModule
     */
    public static $php;

    public static function php(?PhpModule $instance = null)
    {
        return static::$php = $instance
            ?? static::$php
            ?? new PhpModule();
    }

    /**
     * @return PregModule
     */
    public static $preg;

    public static function preg(?PregModule $instance = null)
    {
        return static::$preg = $instance
            ?? static::$preg
            ?? new PregModule();
    }

    /**
     * @return RandomModule
     */
    public static $random;

    public static function random(?RandomModule $instance = null)
    {
        return static::$random = $instance
            ?? static::$random
            ?? new RandomModule();
    }

    /**
     * @return StrModule
     */
    public static $social;

    public static function social(?SocialModule $instance = null)
    {
        return static::$social = $instance
            ?? static::$social
            ?? new SocialModule();
    }

    /**
     * @return StrModule
     */
    public static $str;

    public static function str(?StrModule $instance = null)
    {
        return static::$str = $instance
            ?? static::$str
            ?? new StrModule();
    }

    /**
     * @return TestModule
     */
    public static $test;

    public static function test(?TestModule $instance = null)
    {
        return static::$test = $instance
            ?? static::$test
            ?? new TestModule();
    }

    /**
     * @return UrlModule
     */
    public static $url;

    public static function url(?UrlModule $instance = null)
    {
        return static::$url = $instance
            ?? static::$url
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


    public static function pipe() : Pipe
    {
        return new Pipe();
    }


    /**
     * @return array|float
     */
    public static function benchmark($clear = null, ?string $tag = null)
    {
        /** @var float $mt */

        $mt = microtime(true);

        static $current;

        $tag = $tag ?? '';

        if (null !== $clear) {
            $clear = (bool) $clear;
        }

        if (null === $clear) {
            $last = $current;

            $current = null;

            // ! return
            return $last->report ?? [];
        }

        if (null === $current) {
            $current = new class {
                /**
                 * @var float[][]
                 */
                public $report = [];
                /**
                 * @var float[]
                 */
                public $microtimes = [];
            };
        }

        if (! isset($current->report[ $tag ])) {
            $current->report[ $tag ] = [];
        }

        if (isset($current->microtimes[ $tag ])) {
            $current->report[ $tag ][] = $mt - $current->microtimes[ $tag ];
        }

        if ($clear) {
            unset($current->microtimes[ $tag ]);

        } else {
            $current->microtimes[ $tag ] = $mt;
        }

        return $mt;
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

        $pi = Lib::php()->pathinfo($class, '\\');
        $namespace = $pi[ 'dirname' ];
        $namespace .= '\\PHP8';
        $classname = $pi[ 'basename' ];

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

        $pi = Lib::php()->pathinfo($class, '\\');
        $namespace = $pi[ 'dirname' ];
        $namespace .= '\\PHP7';
        $classname = $pi[ 'basename' ];

        $fqcn = "\\{$namespace}\\{$classname}";

        $result = new $fqcn(...$args);

        return $result;
    }


    /**
     * @var array<string, mixed>
     */
    protected static $modules = [];
}
