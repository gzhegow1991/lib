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
use Gzhegow\Lib\Modules\CmpModule;
use Gzhegow\Lib\Modules\NumModule;
use Gzhegow\Lib\Modules\FuncModule;
use Gzhegow\Lib\Modules\HttpModule;
use Gzhegow\Lib\Modules\TestModule;
use Gzhegow\Lib\Modules\PregModule;
use Gzhegow\Lib\Modules\DateModule;
use Gzhegow\Lib\Modules\DebugModule;
use Gzhegow\Lib\Modules\CryptModule;
use Gzhegow\Lib\Modules\AsyncModule;
use Gzhegow\Lib\Modules\BcmathModule;
use Gzhegow\Lib\Modules\EscapeModule;
use Gzhegow\Lib\Modules\FormatModule;
use Gzhegow\Lib\Modules\RandomModule;
use Gzhegow\Lib\Modules\SocialModule;
use Gzhegow\Lib\Modules\TypeBoolModule;
use Gzhegow\Lib\Modules\ItertoolsModule;
use Gzhegow\Lib\Modules\TypeThrowModule;
use Gzhegow\Lib\Modules\ParseNullModule;
use Gzhegow\Lib\Modules\EntrypointModule;
use Gzhegow\Lib\Modules\ParseThrowModule;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Php\ErrorBag\ErrorBag;


class Lib
{
    /**
     * @var ParseNullModule
     */
    public static $parseNull;
    /**
     * @var ParseThrowModule
     */
    public static $parseThrow;

    /**
     * > Lib::parse([ &$theParseThrow, &$theParseNull ])
     * > $theParseNull = Lib::parse()
     *
     * @param ParseThrowModule|null $pt
     * @param ParseNullModule|null  $pn
     *
     * @return ParseNullModule
     */
    public static function parse(&$pt = null, &$pn = null)
    {
        static::$parseNull = static::$parseNull ?? new ParseNullModule();

        $n = func_num_args();

        if ($n === 2) {
            $pn = static::$parseNull;

        } elseif ($n === 1) {
            static::$parseThrow = static::$parseThrow ?? new ParseThrowModule();

            $pt = static::$parseThrow;
        }

        return static::$parseNull;
    }

    /**
     * @return ParseNullModule
     */
    public static function parseNull()
    {
        return static::$parseNull = static::$parseNull ?? new ParseNullModule();
    }

    /**
     * @return ParseThrowModule
     */
    public static function parseThrow()
    {
        return static::$parseThrow = static::$parseThrow ?? new ParseThrowModule();
    }


    /**
     * @var TypeBoolModule
     */
    public static $typeBool;
    /**
     * @var TypeThrowModule
     */
    public static $typeThrow;

    /**
     * > Lib::type([ &$theTypeThrow, &$theTypeBool ])
     * > $theTypeBool = Lib::type()
     *
     * @param TypeThrowModule|null $tt
     * @param TypeBoolModule|null  $tb
     *
     * @return TypeBoolModule
     */
    public static function type(&$tt = null, &$tb = null)
    {
        static::$typeBool = static::$typeBool ?? new TypeBoolModule();

        $n = func_num_args();

        if ($n === 2) {
            $tb = static::$typeBool;

        } elseif ($n === 1) {
            static::$typeThrow = static::$typeThrow ?? new TypeThrowModule();

            $tt = static::$typeThrow;
        }

        return static::$typeBool;
    }

    /**
     * @return TypeBoolModule
     */
    public static function typeBool()
    {
        return static::$typeBool = static::$typeBool ?? new TypeBoolModule();
    }

    /**
     * @return TypeThrowModule
     */
    public static function typeThrow()
    {
        return static::$typeThrow = static::$typeThrow ?? new TypeThrowModule();
    }


    /**
     * @var ArrModule
     */
    public static $arr;

    public static function arr()
    {
        return static::$arr = static::$arr ?? new ArrModule();
    }

    /**
     * @var AsyncModule
     */
    public static $async;

    public static function async()
    {
        return static::$async = static::$async ?? new AsyncModule();
    }

    /**
     * @var BcmathModule
     */
    public static $bcmath;

    public static function bcmath()
    {
        return static::$bcmath = static::$bcmath ?? new BcmathModule();
    }

    /**
     * @var CliModule
     */
    public static $cli;

    public static function cli()
    {
        return static::$cli = static::$cli ?? new CliModule();
    }

    /**
     * @var CmpModule
     */
    public static $cmp;

    public static function cmp()
    {
        return static::$cmp = static::$cmp ?? new CmpModule();
    }

    /**
     * @var CryptModule
     */
    public static $crypt;

    public static function crypt()
    {
        return static::$crypt = static::$crypt ?? new CryptModule();
    }

    /**
     * @var DateModule
     */
    public static $date;

    public static function date()
    {
        return static::$date = static::$date ?? new DateModule();
    }

    /**
     * @var DebugModule
     */
    public static $debug;

    public static function debug()
    {
        return static::$debug = static::$debug ?? new DebugModule();
    }

    /**
     * @var EntrypointModule
     */
    public static $entrypoint;

    public static function entrypoint() : EntrypointModule
    {
        return static::$entrypoint = static::$entrypoint ?? new EntrypointModule();
    }

    /**
     * @var EscapeModule
     */
    public static $escape;

    public static function escape()
    {
        return static::$escape = static::$escape ?? new EscapeModule();
    }

    /**
     * @var FormatModule
     */
    public static $format;

    public static function format()
    {
        return static::$format = static::$format ?? new FormatModule();
    }

    /**
     * @var FsModule
     */
    public static $fs;

    public static function fs()
    {
        return static::$fs = static::$fs ?? new FsModule();
    }

    /**
     * @var FuncModule
     */
    public static $func;

    public static function func()
    {
        return static::$func = static::$func ?? new FuncModule();
    }

    /**
     * @var HttpModule
     */
    public static $http;

    public static function http()
    {
        return static::$http = static::$http ?? new HttpModule();
    }

    /**
     * @var ItertoolsModule
     */
    public static $itertools;

    public static function itertools()
    {
        return static::$itertools = static::$itertools ?? new ItertoolsModule();
    }

    /**
     * @var MbModule
     */
    public static $mb;

    public static function mb()
    {
        return static::$mb = static::$mb ?? new MbModule();
    }

    /**
     * @var NetModule
     */
    public static $net;

    public static function net()
    {
        return static::$net = static::$net ?? new NetModule();
    }

    /**
     * @var NumModule
     */
    public static $num;

    public static function num()
    {
        return static::$num = static::$num ?? new NumModule();
    }

    /**
     * @var PhpModule
     */
    public static $php;

    public static function php()
    {
        return static::$php = static::$php ?? new PhpModule();
    }

    /**
     * @var PregModule
     */
    public static $preg;

    public static function preg()
    {
        return static::$preg = static::$preg ?? new PregModule();
    }

    /**
     * @var RandomModule
     */
    public static $random;

    public static function random()
    {
        return static::$random = static::$random ?? new RandomModule();
    }

    /**
     * @var StrModule
     */
    public static $social;

    public static function social()
    {
        return static::$social = static::$social ?? new SocialModule();
    }

    /**
     * @var StrModule
     */
    public static $str;

    public static function str()
    {
        return static::$str = static::$str ?? new StrModule();
    }

    /**
     * @var TestModule
     */
    public static $test;

    public static function test()
    {
        return static::$test = static::$test ?? new TestModule();
    }

    /**
     * @var UrlModule
     */
    public static $url;

    public static function url()
    {
        return static::$url = static::$url ?? new UrlModule();
    }


    /**
     * > фабрика для ErrorBag - добавить теги ошибкам, чтобы потом сохранить в несколько отчетов
     */
    public static function errorBag(?ErrorBag &$b = null) : ErrorBag
    {
        return Lib::php()->newErrorBag($b);
    }

    /**
     * > в старых PHP нельзя выбросить исключения в рамках цепочки тернарных операторов
     *
     * @return null
     *
     * @noinspection PhpUnnecessaryStopStatementInspection
     *
     * @throws \LogicException|\RuntimeException
     */
    public static function throw($throwableOrArg, ...$throwableArgs)
    {
        if (false
            || ($throwableOrArg instanceof \LogicException)
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

        $thePhp->throw_new_trace($trace, ...$throwableArgs);

        return;
    }


    /**
     * > время в секундах
     */
    public static function time(?\DateTimeInterface $date = null) : string
    {
        if (null === $date) {
            $sec = time();

        } else {
            $sec = $date->format('U');
        }

        return $sec;
    }

    /**
     * > время в миллисекундах
     */
    public static function mtime(?\DateTimeInterface $date = null) : string
    {
        if (null === $date) {
            $now = microtime();

            [ $msec, $sec ] = explode(' ', $now, 2);

            $msec = substr($msec, 2, 3);

        } else {
            $sec = $date->format('U');
            $msec = $date->format('v');

            $msec = substr($msec, 0, 3);
        }

        $msec = str_pad($msec, 3, '0');

        $result = "{$sec}.{$msec}";

        return $result;
    }

    /**
     * > время в микросекундах
     */
    public static function utime(?\DateTimeInterface $date = null) : string
    {
        if (null === $date) {
            $now = microtime();

            [ $usec, $sec ] = explode(' ', $now, 2);

            $usec = substr($usec, 2, 6);

        } else {
            $sec = $date->format('U');
            $usec = $date->format('u');
        }

        $usec = str_pad($usec, 6, '0');

        $result = "{$sec}.{$usec}";

        return $result;
    }

    /**
     * > время в наносекундах
     */
    public static function ntime(?\DateTimeInterface $date = null) : string
    {
        if (null === $date) {
            $now = microtime();

            [ $usec, $sec ] = explode(' ', $now, 2);

            $usec = substr($usec, 2, 6);

        } else {
            $sec = $date->format('U');
            $usec = $date->format('u');
        }

        $hr = hrtime();
        $nsec = substr($hr[ 1 ], 0, 9);

        $nanosec = '';
        for ( $i = 0; $i < 9; $i++ ) {
            $nanosec[ $i ] = $usec[ $i ] ?? $nsec[ $i ] ?? '0';
        }

        $result = "{$sec}.{$nanosec}";

        return $result;
    }


    /**
     * > простой замерщик времени между вызовами - сразу несколько таймеров для замера
     *
     * @return array|float
     */
    public static function benchmark($clear = null, ?string $tag = null)
    {
        /** @var float $microtime */

        $microtime = microtime(true);

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
            $current->report[ $tag ][] = $microtime - $current->microtimes[ $tag ];
        }

        if ($clear) {
            unset($current->microtimes[ $tag ]);

        } else {
            $current->microtimes[ $tag ] = $microtime;
        }

        return $microtime;
    }


    /**
     * > подключить композер, установленный глобально - чтобы дебаг пакеты не добавлять в библиотеки, но пользоваться ими (временно)
     *
     * @return \Composer\Autoload\ClassLoader
     */
    public static function require_composer_global()
    {
        return require_once getenv('COMPOSER_HOME') . '/vendor/autoload.php';
    }

    /**
     * @param \Closure|null $refFn
     */
    public static function d(&$refFn = null) : \Closure
    {
        return $refFn = function ($var, ...$vars) {
            $t = \Gzhegow\Lib\Lib::debug()->file_line();

            \Gzhegow\Lib\Lib::debug()->d([ $t ], $var, ...$vars);
        };
    }

    /**
     * @param \Closure|null $refFn
     */
    public static function dd(&$refFn = null) : \Closure
    {
        return $refFn = function (...$vars) {
            $t = \Gzhegow\Lib\Lib::debug()->file_line();

            \Gzhegow\Lib\Lib::debug()->dd([ $t ], ...$vars);
        };
    }

    /**
     * @param \Closure|null $refFn
     */
    public static function ddd(&$refFn = null) : \Closure
    {
        return $refFn = function (?int $limit, $var, ...$vars) {
            $t = \Gzhegow\Lib\Lib::debug()->file_line();

            \Gzhegow\Lib\Lib::debug()->ddd([ $t ], $limit, $var, ...$vars);
        };
    }

    /**
     * @param \Closure|null $refFn
     */
    public static function td(int $throttleMs, &$refFn = null) : \Closure
    {
        if ($throttleMs < 0) {
            throw new LogicException(
                [ 'The `throttleMs` should be non-negative integer', $throttleMs ]
            );
        }

        return $refFn = function ($var, ...$vars) use ($throttleMs) {
            static $last;

            $last = $last ?? [];

            $t = \Gzhegow\Lib\Lib::debug()->file_line();

            $key = implode(':', $t);
            $last[ $key ] = $last[ $key ] ?? 0;

            $now = microtime(true);

            if (($now - $last[ $key ]) > ($throttleMs / 1000)) {
                $last[ $key ] = $now;

                \Gzhegow\Lib\Lib::debug()->d([ $t ], $var, ...$vars);
            }

            return $var;
        };
    }
}
