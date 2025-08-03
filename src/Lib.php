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
use Gzhegow\Lib\Modules\Test\TestCase;
use Gzhegow\Lib\Modules\Func\Pipe\Pipe;
use Gzhegow\Lib\Modules\ItertoolsModule;
use Gzhegow\Lib\Modules\EntrypointModule;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Format\FormatCsv;
use Gzhegow\Lib\Modules\Format\FormatXml;
use Gzhegow\Lib\Modules\Format\FormatJson;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\ErrorBag\ErrorBag;
use Gzhegow\Lib\Modules\Fs\FileSafe\FileSafeProxy;
use Gzhegow\Lib\Modules\Debug\Dumper\DumperInterface;
use Gzhegow\Lib\Modules\Str\Slugger\SluggerInterface;
use Gzhegow\Lib\Modules\Fs\SocketSafe\SocketSafeProxy;
use Gzhegow\Lib\Modules\Fs\StreamSafe\StreamSafeProxy;
use Gzhegow\Lib\Modules\Func\Invoker\InvokerInterface;
use Gzhegow\Lib\Modules\Http\Cookies\CookiesInterface;
use Gzhegow\Lib\Modules\Http\Session\SessionInterface;
use Gzhegow\Lib\Modules\Async\Loop\LoopManagerInterface;
use Gzhegow\Lib\Modules\Async\FetchApi\FetchApiInterface;
use Gzhegow\Lib\Modules\Str\Inflector\InflectorInterface;
use Gzhegow\Lib\Modules\Async\Clock\ClockManagerInterface;
use Gzhegow\Lib\Modules\Cli\Process\ProcessManagerInterface;
use Gzhegow\Lib\Modules\Debug\Backtracer\BacktracerInterface;
use Gzhegow\Lib\Modules\Debug\Throwabler\ThrowablerInterface;
use Gzhegow\Lib\Modules\Async\Promise\PromiseManagerInterface;
use Gzhegow\Lib\Modules\Str\Interpolator\InterpolatorInterface;
use Gzhegow\Lib\Modules\Social\EmailParser\EmailParserInterface;
use Gzhegow\Lib\Modules\Social\PhoneManager\PhoneManagerInterface;
use Gzhegow\Lib\Modules\Php\CallableParser\CallableParserInterface;


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

    public static function asyncClock(?bool $clone = null) : ClockManagerInterface
    {
        return $clone
            ? Lib::async()->cloneClockManager()
            : Lib::async()->clockManager();
    }

    public static function asyncLoop(?bool $clone = null) : LoopManagerInterface
    {
        return $clone
            ? Lib::async()->cloneLoopManager()
            : Lib::async()->loopManager();
    }

    public static function asyncPromise(?bool $clone = null) : PromiseManagerInterface
    {
        return $clone
            ? Lib::async()->clonePromiseManager()
            : Lib::async()->promiseManager();
    }

    public static function asyncFetchApi(?bool $clone = null) : FetchApiInterface
    {
        return $clone
            ? Lib::async()->cloneFetchApi()
            : Lib::async()->fetchApi();
    }


    /**
     * @var CliModule
     */
    public static $cli;

    public static function cli()
    {
        return static::$cli = static::$cli ?? new CliModule();
    }

    public static function cliProcessManager(?bool $clone = null) : ProcessManagerInterface
    {
        return $clone
            ? Lib::cli()->cloneProcessManager()
            : Lib::cli()->processManager();
    }


    /**
     * @var DebugModule
     */
    public static $debug;

    public static function debug()
    {
        return static::$debug = static::$debug ?? new DebugModule();
    }

    public static function debugBacktracer(?bool $clone = null) : BacktracerInterface
    {
        return $clone
            ? Lib::debug()->cloneBacktracer()
            : Lib::debug()->backtracer();
    }

    public static function debugDumper(?bool $clone = null) : DumperInterface
    {
        return $clone
            ? Lib::debug()->cloneDumper()
            : Lib::debug()->dumper();
    }

    public static function debugThrowabler(?bool $clone = null) : ThrowablerInterface
    {
        return $clone
            ? Lib::debug()->cloneThrowabler()
            : Lib::debug()->throwabler();
    }


    /**
     * @var FormatModule
     */
    public static $format;

    public static function format()
    {
        return static::$format = static::$format ?? new FormatModule();
    }

    public static function formatCsv(?bool $clone = null) : FormatCsv
    {
        return $clone
            ? Lib::format()->cloneCsv()
            : Lib::format()->csv();
    }

    public static function formatJson(?bool $clone = null) : FormatJson
    {
        return $clone
            ? Lib::format()->cloneJson()
            : Lib::format()->json();
    }

    public static function formatXml(?bool $clone = null) : FormatXml
    {
        return $clone
            ? Lib::format()->cloneXml()
            : Lib::format()->xml();
    }


    /**
     * @var FsModule
     */
    public static $fs;

    public static function fs()
    {
        return static::$fs = static::$fs ?? new FsModule();
    }

    public static function fsFile() : FileSafeProxy
    {
        return Lib::fs()->fileSafe();
    }

    public static function fsSocket() : SocketSafeProxy
    {
        return Lib::fs()->socketSafe();
    }

    public static function fsStream() : StreamSafeProxy
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

    public static function funcInvoker(?bool $clone = null) : InvokerInterface
    {
        return $clone
            ? Lib::func()->cloneInvoker()
            : Lib::func()->invoker();
    }


    /**
     * @var HttpModule
     */
    public static $http;

    public static function http()
    {
        return static::$http = static::$http ?? new HttpModule();
    }

    public static function httpCookies() : CookiesInterface
    {
        return Lib::http()->cookies();
    }

    public static function httpSession() : SessionInterface
    {
        return Lib::http()->session();
    }


    /**
     * @var PhpModule
     */
    public static $php;

    public static function php()
    {
        return static::$php = static::$php ?? new PhpModule();
    }

    public static function phpCallableParser(?bool $clone = null) : CallableParserInterface
    {
        return $clone
            ? Lib::php()->cloneCallableParser()
            : Lib::php()->callableParser();
    }


    /**
     * @var SocialModule
     */
    public static $social;

    public static function social()
    {
        return static::$social = static::$social ?? new SocialModule();
    }

    public static function socialEmail(?bool $clone = null) : EmailParserInterface
    {
        return $clone
            ? Lib::social()->cloneEmailParser()
            : Lib::social()->emailParser();
    }

    public static function socialPhone(?bool $clone = null) : PhoneManagerInterface
    {
        return $clone
            ? Lib::social()->clonePhoneManager()
            : Lib::social()->phoneManager();
    }


    /**
     * @var StrModule
     */
    public static $str;

    public static function str()
    {
        return static::$str = static::$str ?? new StrModule();
    }

    public static function strInflector(?bool $clone = null) : InflectorInterface
    {
        return $clone
            ? Lib::str()->cloneInflector()
            : Lib::str()->inflector();
    }

    public static function strInterpolator(?bool $clone = null) : InterpolatorInterface
    {
        return $clone
            ? Lib::str()->cloneInterpolator()
            : Lib::str()->interpolator();
    }

    public static function strSlugger(?bool $clone = null) : SluggerInterface
    {
        return $clone
            ? Lib::str()->cloneSlugger()
            : Lib::str()->slugger();
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
     * > фабрика для Pipe - задать этапы задачи в наглядом виде без деталей
     */
    public static function pipe(?Pipe &$refP = null) : Pipe
    {
        return $refP = Lib::func()->newPipe();
    }

    /**
     * > фабрика для ErrorBag - добавить теги ошибкам, чтобы потом сохранить в несколько отчетов
     */
    public static function errorBag(?ErrorBag &$refB = null) : ErrorBag
    {
        return $refB = Lib::php()->newErrorBag();
    }

    /**
     * > фабрика для TestCase - быстро создать тест, проверяющий вывод, возврат, затраты времени и памяти
     */
    public static function testCase(?TestCase &$refT = null) : TestCase
    {
        return $refT = Lib::test()->newTestCase();
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


    public static function dumper($dumper = null, $printer = null) : DumperInterface
    {
        $theDebugDumper = Lib::debugDumper();

        if (null !== $dumper) {
            $dumperArray = (array) $dumper;

            $theDebugDumper->selectDumper(...$dumperArray);
        }

        if (null !== $printer) {
            $printerArray = (array) $printer;

            $theDebugDumper->selectPrinter(...$printerArray);
        }

        return $theDebugDumper;
    }


    public static function dp($var, ...$vars) : string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return Lib::debug()->dumper()->dp($trace, $var, ...$vars);
    }

    public static function fnDP(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure
    {
        return Lib::debug()->fnDP($limit, $debugBacktraceOverride);
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


    public static function fnD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure
    {
        return Lib::debug()->fnD($limit, $debugBacktraceOverride);
    }

    public static function fnDD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure
    {
        return Lib::debug()->fnDD($limit, $debugBacktraceOverride);
    }

    public static function fnDDD(?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure
    {
        return Lib::debug()->fnDDD($limit, $debugBacktraceOverride);
    }


    /**
     * @return mixed|void
     */
    public function td(int $throttleMs, $var, ...$vars)
    {
        return Lib::debug()->td($throttleMs, $var, ...$vars);
    }

    public static function fnTD(int $throttleMs, ?int $limit = null, ?array $debugBacktraceOverride = null) : \Closure
    {
        return Lib::debug()->fnTD($throttleMs, $limit, $debugBacktraceOverride);
    }


    /**
     * > конструкция require в PHP бросает FATAL, который не отлавливается с помощью set_error_handler()/set_exception_handler()
     *
     * @return mixed
     */
    public static function require(string $file)
    {
        if (! is_file($file)) {
            throw new LogicException(
                [ 'Missing `filepath` file: ' . $file ]
            );
        }

        if (! is_file($file)) {
            throw new LogicException(
                [ 'Missing `filepath` file: ' . $file ]
            );
        }

        $realpath = realpath($file);

        return include $realpath;
    }

    /**
     * > конструкция require в PHP бросает FATAL, который не отлавливается с помощью set_error_handler()/set_exception_handler()
     *
     * @return mixed
     */
    public static function require_once(string $file)
    {
        static $requireOnce;

        $requireOnce = $requireOnce ?? [];

        if (! is_file($file)) {
            throw new LogicException(
                [ 'Missing `filepath` file: ' . $file ]
            );
        }

        $realpath = realpath($file);

        if (! isset($requireOnce[ $realpath ])) {
            $requireOnce[ $realpath ] = include $realpath;
        }

        return $requireOnce[ $realpath ];
    }


    /**
     * > в версиях PHP ниже 8.0 нельзя выбросить исключения в рамках цепочки тернарных операторов
     *
     * @noinspection PhpUnnecessaryStopStatementInspection
     *
     * @return null
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

        $throwableClass = $thePhp->staticThrowableClass();

        $trace = property_exists($throwableClass, 'trace')
            ? debug_backtrace()
            : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $thePhp->throw_new_trace($trace, ...$throwableArgs);

        return;
    }


    /**
     * > примитивное глобальное хранилище для импортов-экспортов ("сервис-локатор", ОГА)
     */
    public static function &di() : array
    {
        static $services;

        $services = $services ?? [];

        return $services;
    }

    public static function export(string $file, $item = null, ?string $key = null) : array
    {
        if (! is_file($file)) {
            throw new LogicException(
                [ 'Missing `filepath` file: ' . $file ]
            );
        }

        $services =& static::di();

        $realpath = realpath($file);

        if (null !== $item) {
            if (null !== $key) {
                $services[ $realpath ][ $key ] = $item;

            } elseif (is_array($item)) {
                $services[ $realpath ] = array_replace(
                    $services[ $file ] ?? [],
                    $item
                );

            } else {
                $services[ $realpath ][] = $item;
            }
        }

        return $services[ $realpath ];
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
    public static function import(string $file, ?string $key = null, ?string $classT = null)
    {
        if (! is_file($file)) {
            throw new RuntimeException(
                [ 'Missing `filepath` file: ' . $file ]
            );
        }

        $services =& static::di();

        $realpath = realpath($file);

        if (! isset($services[ $realpath ])) {
            $services[ $realpath ] = include $realpath;
        }

        if (null !== $key) {
            return $services[ $realpath ][ $key ];
        }

        return $services[ $realpath ];
    }



    /**
     * > подключить Composer, установленный глобально - чтобы дебаг пакеты не добавлять в библиотеки, но пользоваться ими (временно)
     *
     * @return \Composer\Autoload\ClassLoader
     */
    public static function require_composer_global()
    {
        static $loader;

        $loader = $loader ?? require_once getenv('COMPOSER_HOME') . '/vendor/autoload.php';

        return $loader;
    }
}
