<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class TestModule
{
    /**
     * @var resource
     */
    protected $stdoutResource = STDOUT;


    /**
     * @param array{ 0: resource|null }|null $stdoutResource
     *
     * @return resource|null
     */
    public function stdout_resource_static(array $stdoutResource = []) // : ?resource
    {
        if (count($stdoutResource)) {
            [ $h ] = $stdoutResource;

            if (null !== $h) {
                if (! is_resource($stdoutResource)) {
                    throw new LogicException(
                        [ 'The `resource` must be opened resource', $stdoutResource ]
                    );
                }
            }

            $last = $this->stdoutResource;

            $current = $stdoutResource;

            $result = $last;
        }

        $result = $result ?? $this->stdoutResource;

        return $result;
    }


    /**
     * @param callable $fn
     */
    public function assertReturn(
        array $trace,
        $fn, array $fnArgs = [],
        $expectedReturn = null,
        string &$result = null
    ) : bool
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? '{line}';

        $var = call_user_func_array($fn, $fnArgs);

        $result = $var;

        $stdout = $this->stdout_resource_static();

        if ($result !== $expectedReturn) {
            $message = '[ ERROR ] Test ' . __METHOD__ . '() failed.';

            Lib::debug()->diff_vars($result, $expectedReturn, [ 1 => &$diff ]);

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
            fwrite($stdout, '[ OK ] Test ' . __METHOD__ . '() passed.' . PHP_EOL);
        }

        return true;
    }

    /**
     * @param callable $fn
     */
    public function assertStdout(
        array $trace,
        $fn, array $fnArgs = [],
        string $expectedStdout = null,
        string &$output = null
    ) : bool
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? '{line}';

        ob_start();
        $var = call_user_func_array($fn, $fnArgs);
        $var = ob_get_clean();

        $output = $var;

        $stdout = $this->stdout_resource_static();

        $isDiff = Lib::debug()->diff(
            trim($output),
            trim($expectedStdout),
            [ 1 => &$diff ]
        );

        if ($isDiff) {
            $message = '[ ERROR ] Test ' . __METHOD__ . '() failed.';

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
            fwrite($stdout, '[ OK ] Test ' . __METHOD__ . '() passed.' . PHP_EOL);
        }

        return true;
    }

    /**
     * @param callable $fn
     */
    public function assertMicrotime(
        array $trace,
        $fn, array $fnArgs = [],
        float $expectedMicrotimeMax = null, float $expectedMicrotimeMin = null,
        float &$microtime = null
    ) : bool
    {
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? '{line}';

        $mt = microtime(true);

        call_user_func_array($fn, $fnArgs);

        $mtDiff = round(microtime(true) - $mt, 6);

        $microtime = $mtDiff;

        $messageMax = null;
        $messageMin = null;

        $diffMax = null;
        $diffMin = null;

        $isError = false;

        if (null !== $expectedMicrotimeMax) {
            if ($mtDiff > $expectedMicrotimeMax) {
                $messageMax = '[ ERROR ] Test ' . __METHOD__ . '() `$expectMax` failed.';
                $diffMax = $mtDiff - $expectedMicrotimeMax;

                $isError = true;
            }
        }

        if (null !== $expectedMicrotimeMin) {
            if ($mtDiff < $expectedMicrotimeMin) {
                $messageMin = '[ ERROR ] Test ' . __METHOD__ . '() `$expectMin` failed.';
                $diffMin = $expectedMicrotimeMin - $mtDiff;

                $isError = true;
            }
        }

        $stdout = $this->stdout_resource_static();

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
