<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */

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
use Gzhegow\Lib\Modules\TypeModule;
use Gzhegow\Lib\Modules\DebugModule;
use Gzhegow\Lib\Modules\CryptModule;
use Gzhegow\Lib\Modules\AsyncModule;
use Gzhegow\Lib\Modules\BcmathModule;
use Gzhegow\Lib\Modules\EscapeModule;
use Gzhegow\Lib\Modules\FormatModule;
use Gzhegow\Lib\Modules\RandomModule;
use Gzhegow\Lib\Modules\SocialModule;
use Gzhegow\Lib\Modules\Func\Pipe\Pipe;
use Gzhegow\Lib\Modules\ItertoolsModule;
use Gzhegow\Lib\Modules\EntrypointModule;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\ErrorBag\ErrorBag;


class Lib
{
    /**
     * @var AsyncModule
     */
    public static $async;

    public static function async()
    {
        return static::$async = static::$async ?? new AsyncModule();
    }

    public static function asyncFetchApi()
    {
        return Lib::async()->fetchApi();
    }


    /**
     * @var CliModule
     */
    public static $cli;

    public static function cli()
    {
        return static::$cli = static::$cli ?? new CliModule();
    }

    public static function cliProcessManager()
    {
        return Lib::cli()->processManager();
    }


    /**
     * @var DebugModule
     */
    public static $debug;

    public static function debug()
    {
        return static::$debug = static::$debug ?? new DebugModule();
    }

    public static function debugBacktracer()
    {
        return Lib::debug()->backtracer();
    }

    public static function debugDumper()
    {
        return Lib::debug()->dumper();
    }

    public static function debugThrowabler()
    {
        return Lib::debug()->throwabler();
    }


    /**
     * @var FormatModule
     */
    public static $format;

    public static function format()
    {
        return static::$format = static::$format ?? new FormatModule();
    }

    public static function formatCsv()
    {
        return Lib::format()->csv();
    }

    public static function formatJson()
    {
        return Lib::format()->json();
    }

    public static function formatXml()
    {
        return Lib::format()->xml();
    }


    /**
     * @var FsModule
     */
    public static $fs;

    public static function fs()
    {
        return static::$fs = static::$fs ?? new FsModule();
    }

    public static function fsFile()
    {
        return Lib::fs()->fileSafe();
    }

    public static function fsSocket()
    {
        return Lib::fs()->socketSafe();
    }

    public static function fsStream()
    {
        return Lib::fs()->streamSafe();
    }


    /**
     * @var FuncModule
     */
    public static $func;

    public static function func()
    {
        return static::$func = static::$func ?? new FuncModule();
    }

    public static function funcInvoker()
    {
        return Lib::func()->invoker();
    }


    /**
     * @var HttpModule
     */
    public static $http;

    public static function http()
    {
        return static::$http = static::$http ?? new HttpModule();
    }

    public static function httpCookies()
    {
        return Lib::http()->cookies();
    }

    public static function httpSession()
    {
        return Lib::http()->session();
    }


    /**
     * @var SocialModule
     */
    public static $social;

    public static function social()
    {
        return static::$social = static::$social ?? new SocialModule();
    }

    public static function socialEmailParser()
    {
        return Lib::social()->emailParser();
    }

    public static function socialPhoneManager()
    {
        return Lib::social()->phoneManager();
    }


    /**
     * @var StrModule
     */
    public static $str;

    public static function str()
    {
        return static::$str = static::$str ?? new StrModule();
    }

    public static function strInflector()
    {
        return Lib::str()->inflector();
    }

    public static function strInterpolator()
    {
        return Lib::str()->interpolator();
    }

    public static function strSlugger()
    {
        return Lib::str()->slugger();
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
     * @var BcmathModule
     */
    public static $bcmath;

    public static function bcmath()
    {
        return static::$bcmath = static::$bcmath ?? new BcmathModule();
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
     * @var EntrypointModule
     */
    public static $entrypoint;

    public static function entrypoint()
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
     * @var TestModule
     */
    public static $test;

    public static function test()
    {
        return static::$test = static::$test ?? new TestModule();
    }

    /**
     * @var TypeModule
     */
    public static $type;

    public static function type()
    {
        return static::$type = static::$type ?? new TypeModule();
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
    public static function errorBag(?ErrorBag &$refB = null) : ErrorBag
    {
        return $refB = Lib::php()->newErrorBag();
    }


    /**
     * > фабрика для Pipe - задать этапы задачи в наглядом виде без деталей
     */
    public static function pipe(?Pipe &$refP = null) : Pipe
    {
        return $refP = Lib::func()->newPipe();
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

        } else {
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


    public static function dp($var, ...$vars) : string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return Lib::debug()->dumper()->dp($trace, $var, ...$vars);
    }

    /**
     * @return mixed
     */
    public static function d($var, ...$vars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return Lib::debug()->dumper()->d($trace, $var, ...$vars);
    }

    /**
     * @return mixed|void
     */
    public static function dd(...$vars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return Lib::debug()->dumper()->dd($trace, ...$vars);
    }

    /**
     * @return mixed|void
     */
    public static function ddd(?int $limit, $var, ...$vars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return Lib::debug()->dumper()->ddd($trace, $limit, $var, ...$vars);
    }


    public static function fnDP() : \Closure
    {
        return Lib::debug()->fnDP();
    }

    public function fnD() : \Closure
    {
        return Lib::debug()->fnD();
    }

    public static function fnDD() : \Closure
    {
        return Lib::debug()->fnDD();
    }

    public static function fnDDD() : \Closure
    {
        return Lib::debug()->fnDDD();
    }

    public static function fnTD(int $throttleMs) : \Closure
    {
        return Lib::debug()->fnTD($throttleMs);
    }


    /**
     * > примитивное глобальное хранилище для импортов-экспортов ("сервис-локатор", ОГА)
     */
    public static function &di() : array
    {
        static $imports;

        $imports = $imports ?? [];

        return $imports;
    }

    public static function export(string $file, $item = null, ?string $name = null) : array
    {
        static $export;

        $export = $export ?? [];

        if (! is_file($file)) {
            throw new LogicException(
                [ 'Missing `filepath` file: ' . $file ]
            );
        }

        $realpath = realpath($file);

        if (null !== $item) {
            if (null === $name) {
                if (is_array($item)) {
                    $export[ $realpath ] = array_replace(
                        $export[ $file ] ?? [],
                        $item
                    );

                } else {
                    $export[ $realpath ][] = $item;
                }

            } else {
                $export[ $realpath ][ $name ] = $item;
            }
        }

        return $export[ $realpath ];
    }

    public static function import(string $file) : array
    {
        if (! is_file($file)) {
            throw new RuntimeException(
                [ 'Missing `filepath` file: ' . $file ]
            );
        }

        $realpath = realpath($file);

        $imports =& static::di();

        if (! isset($imports[ $realpath ])) {
            $imports[ $realpath ] = include $realpath;
        }

        return $imports[ $realpath ];
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|null $classT
     *
     * @return T|mixed
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public static function importKey(string $file, string $key, ?string $classT = null)
    {
        if (! is_file($file)) {
            throw new RuntimeException(
                [ 'Missing `filepath` file: ' . $file ]
            );
        }

        $realpath = realpath($file);

        $imports =& static::di();

        if (! isset($imports[ $realpath ])) {
            $imports[ $realpath ] = include $realpath;
        }

        return $imports[ $realpath ][ $key ];
    }


    /**
     * > в старых версиях PHP нельзя выбросить исключения в рамках цепочки тернарных операторов
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

        $thePhp = Lib::php();

        array_unshift($throwableArgs, $throwableOrArg);

        $throwableClass = $thePhp->static_throwable_class();

        $trace = property_exists($throwableClass, 'trace')
            ? debug_backtrace()
            : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $thePhp->throw_new_trace($trace, ...$throwableArgs);

        return;
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
}
