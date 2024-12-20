<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


trait AssertTrait
{
    /**
     * @param resource|null $resource
     *
     * @return resource|null
     */
    public static function assert_resource_static($resource = null) // : ?resource
    {
        static $current;

        if (null !== $resource) {
            if (! is_resource($resource)) {
                throw new LogicException(
                    [
                        'The `resource` must be opened resource',
                        $resource,
                    ]
                );
            }

            $last = $current;

            $current = $resource;

            return $last;
        }

        return $current;
    }


    public static function assert_equals(
        array $trace,
        $value, $expect = null
    ) : bool
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? '{line}';

        $_value = is_a($value, \Closure::class)
            ? $value()
            : $value;

        $stdout = static::assert_resource_static();

        if ($_value !== $expect) {
            $message = '[ ERROR ] Test ' . __FUNCTION__ . '() failed.';

            static::debug_diff_vars($_value, $expect, $diff);

            if (null !== $stdout) {
                fwrite($stdout, '------' . PHP_EOL);
                fwrite($stdout, $message . PHP_EOL);
                fwrite($stdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                fwrite($stdout, $diff . PHP_EOL);
                fwrite($stdout, '------' . PHP_EOL);

                return false;
            }

            $e = new RuntimeException([ $message, $diff ]);
            $e->trace = $trace;
            $e->file = $traceFile;
            $e->line = $traceLine;

            throw $e;
        }

        if (null !== $stdout) {
            fwrite($stdout, '[ OK ] Test ' . __FUNCTION__ . '() passed.' . PHP_EOL);
        }

        return true;
    }

    public static function assert_not_equals(
        array $trace,
        $value, $expect = null
    ) : bool
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? '{line}';

        $_value = is_a($value, \Closure::class)
            ? $value()
            : $value;

        $stdout = static::assert_resource_static();

        if ($_value === $expect) {
            $message = '[ ERROR ] Test ' . __FUNCTION__ . '() failed.';

            if (null !== $stdout) {
                fwrite($stdout, '------' . PHP_EOL);
                fwrite($stdout, $message . PHP_EOL);
                fwrite($stdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                fwrite($stdout, '------' . PHP_EOL);

                return false;
            }

            $e = new RuntimeException([ $message ]);
            $e->trace = $trace;
            $e->file = $traceFile;
            $e->line = $traceLine;

            throw $e;
        }

        if (null !== $stdout) {
            fwrite($stdout, '[ OK ] Test ' . __FUNCTION__ . '() passed.' . PHP_EOL);
        }

        return true;
    }


    public static function assert_result(
        array $trace,
        \Closure $fn, $expect = null,
        string &$result = null
    ) : bool
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? '{line}';

        $var = $fn();

        $result = $var;

        $stdout = static::assert_resource_static();

        if ($result !== $expect) {
            $message = '[ ERROR ] Test ' . __FUNCTION__ . '() failed.';

            static::debug_diff_vars($result, $expect, $diff);

            if (null !== $stdout) {
                fwrite($stdout, '------' . PHP_EOL);
                fwrite($stdout, $message . PHP_EOL);
                fwrite($stdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                fwrite($stdout, $diff . PHP_EOL);
                fwrite($stdout, '------' . PHP_EOL);

                return false;
            }

            $e = new RuntimeException([ $message, $diff ]);
            $e->trace = $trace;
            $e->file = $traceFile;
            $e->line = $traceLine;

            throw $e;
        }

        if (null !== $stdout) {
            fwrite($stdout, '[ OK ] Test ' . __FUNCTION__ . '() passed.' . PHP_EOL);
        }

        return true;
    }

    public static function assert_output(
        array $trace,
        \Closure $fn, string $expect = null,
        string &$output = null
    ) : bool
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? '{line}';

        ob_start();
        $fn();
        $var = ob_get_clean();

        $output = $var;

        $stdout = static::assert_resource_static();

        $isDiff = static::debug_diff($output, $expect, $diff);

        if ($isDiff) {
            $message = '[ ERROR ] Test ' . __FUNCTION__ . '() failed.';

            if (null !== $stdout) {
                fwrite($stdout, '------' . PHP_EOL);
                fwrite($stdout, $message . PHP_EOL);
                fwrite($stdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                fwrite($stdout, $diff . PHP_EOL);
                fwrite($stdout, '------' . PHP_EOL);

                return false;
            }

            $e = new RuntimeException([ $message, $diff ]);
            $e->trace = $trace;
            $e->file = $traceFile;
            $e->line = $traceLine;

            throw $e;
        }

        if (null !== $stdout) {
            fwrite($stdout, '[ OK ] Test ' . __FUNCTION__ . '() passed.' . PHP_EOL);
        }

        return true;
    }

    public static function assert_microtime(
        array $trace,
        \Closure $fn, float $expectMax = null, float $expectMin = null,
        float &$microtime = null
    ) : bool
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? '{line}';

        $mt = microtime(true);

        $fn();

        $var = round(microtime(true) - $mt, 6);

        $microtime = $var;

        $messageMax = null;
        $messageMin = null;

        $diffMax = null;
        $diffMin = null;

        $isError = false;

        if (null !== $expectMax) {
            if ($microtime > $expectMax) {
                $messageMax = '[ ERROR ] Test ' . __FUNCTION__ . '() `$expectMax` failed.';
                $diffMax = $microtime - $expectMax;

                $isError = true;
            }
        }

        if (null !== $expectMin) {
            if ($microtime < $expectMin) {
                $messageMin = '[ ERROR ] Test ' . __FUNCTION__ . '() `$expectMin` failed.';
                $diffMin = $expectMin - $microtime;

                $isError = true;
            }
        }

        $stdout = static::assert_resource_static();

        if ($isError) {
            if (null !== $stdout) {
                fwrite($stdout, '------' . PHP_EOL);
                if (null !== $messageMax) {
                    fwrite($stdout, $messageMax . PHP_EOL);
                }
                if (null !== $messageMin) {
                    fwrite($stdout, $messageMin . PHP_EOL);
                }
                fwrite($stdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                if (null !== $diffMax) {
                    fwrite($stdout, $diffMax . PHP_EOL);
                }
                if (null !== $diffMin) {
                    fwrite($stdout, $diffMin . PHP_EOL);
                }
                fwrite($stdout, '------' . PHP_EOL);

                return false;
            }

            $e = new RuntimeException([ $messageMax ?? $messageMin, $diffMax ?? $diffMin ]);
            $e->trace = $trace;
            $e->file = $traceFile;
            $e->line = $traceLine;

            throw $e;
        }

        if (null !== $stdout) {
            fwrite($stdout, '[ OK ] Test ' . __FUNCTION__ . '() passed.' . PHP_EOL);
        }

        return true;
    }
}
