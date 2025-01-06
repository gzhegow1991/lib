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
use Gzhegow\Lib\Modules\BoolModule;
use Gzhegow\Lib\Modules\HttpModule;
use Gzhegow\Lib\Modules\DebugModule;
use Gzhegow\Lib\Modules\ParseModule;
use Gzhegow\Lib\Modules\CryptModule;
use Gzhegow\Lib\Modules\BcmathModule2;
use Gzhegow\Lib\Modules\AssertModule;
use Gzhegow\Lib\Modules\EscapeModule;
use Gzhegow\Lib\Modules\FormatModule;
use Gzhegow\Lib\Modules\RandomModule;
use Gzhegow\Lib\Modules\ItertoolsModule;


if (! defined('_UNDEFINED')) define('_UNDEFINED', NAN);

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
     * @return BcmathModule2
     */
    public static function bcmath(BcmathModule2 $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new BcmathModule2();
    }

    /**
     * @return BoolModule
     */
    public static function bool(BoolModule $instance = null)
    {
        $key = __FUNCTION__;

        return static::$modules[ $key ] = $instance
            ?? static::$modules[ $key ]
            ?? new BoolModule();
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
     * @var array<string, mixed>
     */
    protected static $modules = [];
}
