<?php

namespace Gzhegow\Lib\Modules\Fs;

use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @ method stream_get_contents()
 */
class StreamSafe
{
    /**
     * @return mixed
     */
    public function __call($name, $args)
    {
        $beforeErrorReporting = error_reporting(E_ALL | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED);
        $beforeErrorHandler = set_error_handler($this->fnErrorHandler());

        $fn = $this->__callGetCallable($name);

        $result = call_user_func_array($fn, $args);

        set_error_handler($beforeErrorHandler);
        error_reporting($beforeErrorReporting);

        return $result;
    }

    /**
     * @param string $name
     *
     * @return callable
     */
    protected function __callGetCallable(string $name)
    {
        /** @var array<string, callable> $map */
        static $map;

        $map = $map ?? [
            'stream_get_contents' => [ $this, 'stream_get_contents' ],
        ];

        $fn = $map[ $name ] ?: null;

        if (null === $fn) {
            throw new RuntimeException('Method is not exists: ' . $name);
        }

        return $fn;
    }


    /**
     * @return mixed
     */
    public function call(\Closure $closure)
    {
        $beforeErrorReporting = error_reporting(E_ALL | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED);
        $beforeErrorHandler = set_error_handler($this->fnErrorHandler());

        $result = call_user_func_array($closure, [ $this ]);

        set_error_handler($beforeErrorHandler);
        error_reporting($beforeErrorReporting);

        return $result;
    }


    /**
     * @param resource $resource
     *
     * @return string|false
     */
    protected function stream_get_contents($resource, $length = null, $offset = null)
    {
        $length = $length ?? -1;
        $offset = $offset ?? -1;

        $content = stream_get_contents($resource, $length, $offset);

        if (false === $content) {
            return false;
        }

        return $content;
    }


    protected function fnErrorHandler() : \Closure
    {
        /** @var \Closure $fn */
        static $fn;

        return $fn = $fn ?? function ($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
        };
    }
}
