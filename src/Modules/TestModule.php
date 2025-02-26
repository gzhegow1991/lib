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
    public function static_stdout_resource(array $stdoutResource = []) // : ?resource
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
    public function assert(
        ?array $trace,
        $fn, array $fnArgs = [],
        string $expectedStdout = null,
        float $expectedSecondsMax = null, float $expectedSecondsMin = null,
        array $expectedReturn = [],
        array $refs = []
    ) : bool
    {
        $withStdout = array_key_exists(0, $refs);
        $withSeconds = array_key_exists(1, $refs);
        $withReturn = array_key_exists(2, $refs);

        $refStdout = null;
        $refSeconds = null;
        $refReturn = null;
        if ($withStdout) {
            $refStdout =& $refs[ 0 ];
            $refStdout = null;
        }
        if ($withSeconds) {
            $refSeconds =& $refs[ 1 ];
            $refSeconds = null;
        }
        if ($withReturn) {
            $refReturn =& $refs[ 2 ];
            $refReturn = null;
        }

        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? 0;

        $mt = microtime(true);

        ob_start();
        $currentReturn = call_user_func_array($fn, $fnArgs);
        $currentStdout = ob_get_clean();

        $currentSeconds = round(microtime(true) - $mt, 6);

        $refStdout = $currentStdout;
        $refSeconds = $currentSeconds;
        $refReturn = $currentReturn;

        $resourceStdout = $this->static_stdout_resource();

        $eArray = [];

        if (null !== $expectedStdout) {
            $isStdoutDiff = Lib::debug()->diff(
                trim($currentStdout),
                trim($expectedStdout),
                [ &$diffLines ]
            );

            if ($isStdoutDiff) {
                $message = '[ ERROR ] Test ' . __METHOD__ . '() `expectedStdout` failed.';

                $diffString = implode(PHP_EOL, $diffLines);

                if (null !== $resourceStdout) {
                    fwrite($resourceStdout, '------' . PHP_EOL);
                    fwrite($resourceStdout, $message . PHP_EOL);
                    fwrite($resourceStdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                    fwrite($resourceStdout, $diffString . PHP_EOL);
                    fwrite($resourceStdout, '------' . PHP_EOL);
                }

                $e = new RuntimeException([ $message, $diffString ]);
                $e->setTrace($trace);
                $e->setFile($traceFile);
                $e->setLine($traceLine);

                $eArray[] = $e;
            }
        }

        if (false
            || (null !== $expectedSecondsMax)
            || (null !== $expectedSecondsMin)
        ) {
            $isError = false;
            $messageMax = null;
            $messageMin = null;
            $messageCaseMax = null;
            $messageCaseMin = null;
            if (null !== $expectedSecondsMax) {
                if ($currentSeconds > $expectedSecondsMax) {
                    $messageMax = '[ ERROR ] Test ' . __METHOD__ . '() `expectedSecondsMax` failed.';
                    $messageCaseMax = ''
                        . 'Case: '
                        . sprintf('%.6f', $currentSeconds)
                        . ' > '
                        . sprintf('%.6f', $expectedSecondsMax);

                    $isError = true;
                }
            }
            if (null !== $expectedSecondsMin) {
                if ($currentSeconds < $expectedSecondsMin) {
                    $messageMin = '[ ERROR ] Test ' . __METHOD__ . '() `expectedSecondsMin` failed.';
                    $messageCaseMin = ''
                        . 'Case: '
                        . sprintf('%.6f', $currentSeconds)
                        . ' < '
                        . sprintf('%.6f', $expectedSecondsMin);

                    $isError = true;
                }
            }
            if ($isError) {
                $message = [
                    $messageMax,
                    $messageCaseMax,
                    $messageMin,
                    $messageCaseMin,
                ];

                if (null !== $resourceStdout) {
                    fwrite($resourceStdout, '------' . PHP_EOL);
                    if (null !== $messageMax) {
                        fwrite($resourceStdout, $messageMax . PHP_EOL);
                    }
                    if (null !== $messageMin) {
                        fwrite($resourceStdout, $messageMin . PHP_EOL);
                    }
                    fwrite($resourceStdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                    if (null !== $messageCaseMax) {
                        fwrite($resourceStdout, $messageCaseMax . PHP_EOL);
                    }
                    if (null !== $messageCaseMin) {
                        fwrite($resourceStdout, $messageCaseMin . PHP_EOL);
                    }
                    fwrite($resourceStdout, '------' . PHP_EOL);
                }

                $e = new RuntimeException(implode(' | ', array_filter($message)));
                $e->setTrace($trace);
                $e->setFile($traceFile);
                $e->setLine($traceLine);

                $eArray[] = $e;
            }
        }

        if (count($expectedReturn)) {
            $expectedReturn = $expectedReturn[ 0 ] ?? null;

            if ($currentReturn !== $expectedReturn) {
                $message = '[ ERROR ] Test ' . __METHOD__ . '() `expectedReturn` failed.';

                $isReturnDiff = Lib::debug()->diff_vars($currentReturn, $expectedReturn, [ &$diffLines ]);

                $diffString = implode(PHP_EOL, $diffLines);

                if (null !== $resourceStdout) {
                    fwrite($resourceStdout, '------' . PHP_EOL);
                    fwrite($resourceStdout, $message . PHP_EOL);
                    fwrite($resourceStdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                    fwrite($resourceStdout, $diffString . PHP_EOL);
                    fwrite($resourceStdout, '------' . PHP_EOL);
                }

                $e = new RuntimeException([ $message, $diffString ]);
                $e->setTrace($trace);
                $e->setFile($traceFile);
                $e->setLine($traceLine);

                $eArray[] = $e;
            }
        }

        if (count($eArray)) {
            $message = '[ ERROR ] Test ' . __METHOD__ . '() failed.';

            if (null !== $resourceStdout) {
                return false;
            }

            throw new RuntimeException($message, ...$eArray);
        }

        if (null !== $resourceStdout) {
            fwrite($resourceStdout, '[ OK ] Test ' . __METHOD__ . '() passed.' . PHP_EOL);
        }

        unset($refStdout);
        unset($refReturn);
        unset($refSeconds);

        return true;
    }

    /**
     * @param callable $fn
     */
    public function assertStdout(
        ?array $trace,
        $fn, array $fnArgs = [],
        string $expectedStdout = null,
        string &$stdout = null
    ) : bool
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? 0;

        ob_start();
        $var = call_user_func_array($fn, $fnArgs);
        $currentStdout = ob_get_clean();

        $stdout = $currentStdout;

        $resourceStdout = $this->static_stdout_resource();

        $isDiff = Lib::debug()->diff(
            trim($currentStdout),
            trim($expectedStdout),
            [ &$diffLines ]
        );

        if ($isDiff) {
            $message = '[ ERROR ] Test ' . __METHOD__ . '() failed.';

            $diffString = implode(PHP_EOL, $diffLines);

            if (null !== $resourceStdout) {
                fwrite($resourceStdout, '------' . PHP_EOL);
                fwrite($resourceStdout, $message . PHP_EOL);
                fwrite($resourceStdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                fwrite($resourceStdout, $diffString . PHP_EOL);
                fwrite($resourceStdout, '------' . PHP_EOL);
            }

            $e = new RuntimeException([ $message, $diffString ]);
            $e->setTrace($trace);
            $e->setFile($traceFile);
            $e->setLine($traceLine);

            if (null !== $resourceStdout) {
                return false;
            }

            throw $e;
        }

        if (null !== $resourceStdout) {
            fwrite($resourceStdout, '[ OK ] Test ' . __METHOD__ . '() passed.' . PHP_EOL);
        }

        return true;
    }

    /**
     * @param callable $fn
     */
    public function assertMicrotime(
        ?array $trace,
        $fn, array $fnArgs = [],
        float $expectedSecondsMax = null, float $expectedSecondsMin = null,
        float &$seconds = null
    ) : bool
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? 0;

        $mt = microtime(true);

        call_user_func_array($fn, $fnArgs);

        $currentSeconds = round(microtime(true) - $mt, 6);

        $seconds = $currentSeconds;

        $resourceStdout = $this->static_stdout_resource();

        $isError = false;
        $messageMax = null;
        $messageMin = null;
        $messageCaseMax = null;
        $messageCaseMin = null;
        if (null !== $expectedSecondsMax) {
            if ($currentSeconds > $expectedSecondsMax) {
                $messageMax = '[ ERROR ] Test ' . __METHOD__ . '() `expectedSecondsMax` failed.';
                $messageCaseMax = ''
                    . 'Case: '
                    . sprintf('%.6f', $currentSeconds)
                    . ' > '
                    . sprintf('%.6f', $expectedSecondsMax);

                $isError = true;
            }
        }
        if (null !== $expectedSecondsMin) {
            if ($currentSeconds < $expectedSecondsMin) {
                $messageMin = '[ ERROR ] Test ' . __METHOD__ . '() `expectedSecondsMin` failed.';
                $messageCaseMin = ''
                    . 'Case: '
                    . sprintf('%.6f', $currentSeconds)
                    . ' < '
                    . sprintf('%.6f', $expectedSecondsMin);

                $isError = true;
            }
        }
        if ($isError) {
            $message = [
                $messageMax,
                $messageCaseMax,
                $messageMin,
                $messageCaseMin,
            ];

            if (null !== $resourceStdout) {
                fwrite($resourceStdout, '------' . PHP_EOL);
                if (null !== $messageMax) {
                    fwrite($resourceStdout, $messageMax . PHP_EOL);
                }
                if (null !== $messageMin) {
                    fwrite($resourceStdout, $messageMin . PHP_EOL);
                }
                fwrite($resourceStdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                if (null !== $messageCaseMax) {
                    fwrite($resourceStdout, $messageCaseMax . PHP_EOL);
                }
                if (null !== $messageCaseMin) {
                    fwrite($resourceStdout, $messageCaseMin . PHP_EOL);
                }
                fwrite($resourceStdout, '------' . PHP_EOL);
            }

            $e = new RuntimeException(implode(' | ', array_filter($message)));
            $e->setTrace($trace);
            $e->setFile($traceFile);
            $e->setLine($traceLine);

            if (null !== $resourceStdout) {
                return false;
            }

            throw $e;
        }

        if (null !== $resourceStdout) {
            fwrite($resourceStdout, '[ OK ] Test ' . __METHOD__ . '() passed.' . PHP_EOL);
        }

        return true;
    }

    /**
     * @param callable $fn
     */
    public function assertReturn(
        ?array $trace,
        $fn, array $fnArgs = [],
        array $expectedReturn = [],
        string &$result = null
    ) : bool
    {
        $expectedReturn = $expectedReturn[ 0 ] ?? null;

        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? 0;

        $currentReturn = call_user_func_array($fn, $fnArgs);

        $result = $currentReturn;

        $resourceStdout = $this->static_stdout_resource();

        if ($currentReturn !== $expectedReturn) {
            $message = '[ ERROR ] Test ' . __METHOD__ . '() failed.';

            $isDiff = Lib::debug()->diff_vars($result, $expectedReturn, [ &$diffLines ]);

            $diffString = implode(PHP_EOL, $diffLines);

            if (null !== $resourceStdout) {
                fwrite($resourceStdout, '------' . PHP_EOL);
                fwrite($resourceStdout, $message . PHP_EOL);
                fwrite($resourceStdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                fwrite($resourceStdout, $diffString . PHP_EOL);
                fwrite($resourceStdout, '------' . PHP_EOL);
            }

            $e = new RuntimeException([ $message, $diffString ]);
            $e->setTrace($trace);
            $e->setFile($traceFile);
            $e->setLine($traceLine);

            if (null !== $resourceStdout) {
                return false;
            }

            throw $e;
        }

        if (null !== $resourceStdout) {
            fwrite($resourceStdout, '[ OK ] Test ' . __METHOD__ . '() passed.' . PHP_EOL);
        }

        return true;
    }

    /**
     * @param callable $fn
     */
    public function assertMemory(
        ?array $trace,
        $fn, array $fnArgs = [],
        float $expectedBytesMax = null,
        float &$bytes = null
    ) : bool
    {
        $trace = $trace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? 0;

        $mem = memory_get_usage();

        $return = call_user_func_array($fn, $fnArgs);

        $currentBytes = memory_get_usage() - $mem;

        $bytes = $currentBytes;

        $resourceStdout = $this->static_stdout_resource();

        if ($currentBytes > $expectedBytesMax) {
            $message = '[ ERROR ] Test ' . __METHOD__ . '() `expectedBytesMax` failed.';
            $messageCase = ''
                . 'Case: '
                . sprintf('%d', $currentBytes)
                . ' > '
                . sprintf('%d', $expectedBytesMax);

            if (null !== $resourceStdout) {
                fwrite($resourceStdout, '------' . PHP_EOL);
                fwrite($resourceStdout, $message . PHP_EOL);
                fwrite($resourceStdout, "{$traceFile} : {$traceLine}" . PHP_EOL);
                fwrite($resourceStdout, $messageCase . PHP_EOL);
                fwrite($resourceStdout, '------' . PHP_EOL);
            }

            $e = new RuntimeException($message);
            $e->setTrace($trace);
            $e->setFile($traceFile);
            $e->setLine($traceLine);

            if (null !== $resourceStdout) {
                return false;
            }

            throw $e;
        }

        if (null !== $resourceStdout) {
            fwrite($resourceStdout, '[ OK ] Test ' . __METHOD__ . '() passed.' . PHP_EOL);
        }

        return true;
    }
}
