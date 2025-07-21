<?php

namespace Gzhegow\Lib\Modules\Test;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class TestCase
{
    /**
     * @var \Closure
     */
    protected $fn;
    /**
     * @var array
     */
    protected $fnArgs = [];

    /**
     * @var resource|null
     */
    protected $resource;

    /**
     * @var array
     */
    protected $trace;
    /**
     * @var array
     */
    protected $refTrace;

    /**
     * @var string
     */
    protected $stdout;
    /**
     * @var string
     */
    protected $refStdout;

    /**
     * @var float
     */
    protected $secondsMin;
    /**
     * @var float
     */
    protected $secondsMax;
    /**
     * @var float
     */
    protected $refSeconds;

    /**
     * @var array
     */
    protected $return;
    /**
     * @var mixed
     */
    protected $refReturn;

    /**
     * @var string
     */
    protected $memoryMax;
    /**
     * @var int
     */
    protected $memoryMaxBytes;
    /**
     * @var int
     */
    protected $refMemoryBytes;


    public function __construct()
    {
        $thePhp = Lib::php();

        $this->resource = $thePhp->output();
    }


    /**
     * @return static
     */
    public function fn(\Closure $fn, array $args = [])
    {
        $this->fn = $fn;
        $this->fnArgs = $args;

        return $this;
    }


    /**
     * @param resource|null $resource
     *
     * @return static
     */
    public function resource($resource = null)
    {
        if (null !== $resource) {
            if (! is_resource($resource)) {
                throw new LogicException(
                    [ 'The `resource` should be an opened resource', $resource ]
                );
            }
        }

        $this->resource = $resource;

        return $this;
    }


    /**
     * @return static
     */
    public function trace(?array $trace, &$refTrace = null)
    {
        $refTrace = null;

        $this->trace = $trace;
        $this->refTrace =& $refTrace;

        return $this;
    }


    /**
     * @return static
     */
    public function expectStdout(?string $stdout = '', &$refStdout = null)
    {
        $refStdout = null;

        $this->stdout = $stdout ?? '';

        $this->refStdout =& $refStdout;

        return $this;
    }

    /**
     * @return static
     */
    public function expectStdoutIf(bool $if, ?string $stdout = '', &$refStdout = null)
    {
        $refStdout = null;

        if (! $if) {
            return $this;
        }

        $this->stdout = $stdout ?? '';

        $this->refStdout =& $refStdout;

        return $this;
    }


    /**
     * @return static
     */
    public function expectSeconds(?float $secondsMin = 0.0, ?float $secondsMax = INF, &$refSeconds = null)
    {
        $refSeconds = null;

        $this->secondsMin = $secondsMin ?? 0.0;
        $this->secondsMax = $secondsMax ?? INF;

        $this->refSeconds =& $refSeconds;

        return $this;
    }

    /**
     * @return static
     */
    public function expectSecondsMin(?float $secondsMin = 0.0, &$refSeconds = null)
    {
        $refSeconds = null;

        $this->secondsMin = $secondsMin ?? 0.0;

        $this->refSeconds =& $refSeconds;

        return $this;
    }

    /**
     * @return static
     */
    public function expectSecondsMax(?float $secondsMax = INF, &$refSeconds = null)
    {
        $refSeconds = null;

        $this->secondsMax = $secondsMax ?? INF;

        $this->refSeconds =& $refSeconds;

        return $this;
    }

    /**
     * @return static
     */
    public function expectReturn(?array $return = [], &$refReturn = null)
    {
        $refReturn = null;

        $returnArray = $return ?? [];

        if ([] !== $returnArray) {
            if (! array_key_exists(0, $returnArray)) {
                throw new LogicException(
                    [ 'The `return[0]` should be an existing key', $return ]
                );
            }
        }

        $this->return = $returnArray;

        $this->refReturn =& $refReturn;

        return $this;
    }

    /**
     * @return static
     */
    public function expectMemoryMax(?string $memoryMax = '32M', &$refMemoryBytes = null)
    {
        $refMemoryBytes = null;

        $theFormat = Lib::format();

        $memoryMaxValid = $theFormat->bytes_decode([ NAN ], $memoryMax ?? '32M');

        $this->memoryMax = $memoryMaxValid;

        $this->refMemoryBytes =& $refMemoryBytes;

        return $this;
    }


    public function getFn() : array
    {
        if (null === $this->fn) {
            throw new RuntimeException(
                [ 'The `fn` should be not null', $this ]
            );
        }

        return [ $this->fn, $this->fnArgs ];
    }


    /**
     * @return resource|null
     */
    public function getResource()
    {
        return $this->resource;
    }


    /**
     * @param array $refTrace
     */
    public function hasTrace(&$refTrace = null) : bool
    {
        $refTrace = null;

        if (null !== $this->trace) {
            $refTrace = $this->trace;

            return true;
        }

        return false;
    }


    /**
     * @param string $refStdout
     */
    public function hasStdout(&$refStdout = null) : bool
    {
        $refStdout = null;

        if (null !== $this->stdout) {
            $refStdout = $this->stdout;

            return true;
        }

        return false;
    }

    /**
     * @param float $refSecondsMin
     */
    public function hasSecondsMin(&$refSecondsMin = null) : bool
    {
        $refSecondsMin = null;

        if (null !== $this->secondsMin) {
            $refSecondsMin = $this->secondsMin;

            return true;
        }

        return false;
    }

    /**
     * @param float $refSecondsMax
     */
    public function hasSecondsMax(&$refSecondsMax = null) : bool
    {
        $refSecondsMax = null;

        if (null !== $this->secondsMax) {
            $refSecondsMax = $this->secondsMax;

            return true;
        }

        return false;
    }

    /**
     * @param array $refReturn
     */
    public function hasReturn(&$refReturn = null) : bool
    {
        $refReturn = null;

        if (null !== $this->return) {
            $refReturn = $this->return[ 0 ];

            return true;
        }

        return false;
    }

    /**
     * @param int $refMemoryMaxBytes
     */
    public function hasMemoryMax(&$refMemoryMaxBytes = null) : bool
    {
        $refMemoryMaxBytes = null;

        if (null !== $this->memoryMaxBytes) {
            $refMemoryMaxBytes = $this->memoryMaxBytes;

            return true;
        }

        return false;
    }


    public function run() : bool
    {
        $theDebug = Lib::debug();

        [ $fn, $fnArgs ] = $this->getFn();

        $h = $this->getResource();
        $hasResource = (null !== $h);

        false
        || $this->hasTrace($trace)
        || ($trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

        $traceFile = $trace[ 0 ][ 'file' ] ?? '{file}';
        $traceLine = $trace[ 0 ][ 'line' ] ?? -1;

        $memoryBytesBefore = memory_get_usage();
        $secondsBefore = microtime(true);

        ob_start();
        $this->refReturn = call_user_func_array($fn, $fnArgs);
        $this->refStdout = ob_get_clean();

        $this->refSeconds = round(microtime(true) - $secondsBefore, 6);

        $this->refMemoryBytes = memory_get_usage() - $memoryBytesBefore;

        $eArray = [];

        if ($this->hasStdout($expectedStdout)) {
            $isStdoutDiff = $theDebug->diff(
                trim($this->refStdout),
                trim($expectedStdout),
                [ &$diffLines ]
            );

            if ($isStdoutDiff) {
                $message = '[ ERROR ] Test ' . __METHOD__ . '() `expectedStdout` failed.';

                $diffString = implode("\n", $diffLines);

                if ($hasResource) {
                    fwrite($h, '------' . "\n");
                    fwrite($h, $message . "\n");
                    fwrite($h, "{$traceFile} : {$traceLine}" . "\n");
                    fwrite($h, $diffString . "\n");
                    fwrite($h, '------' . "\n");
                }

                $e = new RuntimeException([ $message, $diffString ]);
                $e->setTrace($trace);
                $e->setFile($traceFile);
                $e->setLine($traceLine);

                $eArray[] = $e;
            }
        }

        $expectedSecondsMin = null;
        $expectedSecondsMax = null;
        if (false
            || $this->hasSecondsMin($expectedSecondsMin)
            || $this->hasSecondsMax($expectedSecondsMax)
        ) {
            $isError = false;
            $messageMax = null;
            $messageMin = null;
            $messageCaseMax = null;
            $messageCaseMin = null;
            if (null !== $expectedSecondsMax) {
                if ($this->refSeconds > $expectedSecondsMax) {
                    $messageMax = '[ ERROR ] Test ' . __METHOD__ . '() `expectedSecondsMax` failed.';
                    $messageCaseMax = ''
                        . 'Case: '
                        . sprintf('%.6f', $this->refSeconds)
                        . ' > '
                        . sprintf('%.6f', $expectedSecondsMax);

                    $isError = true;
                }
            }
            if (null !== $expectedSecondsMin) {
                if ($this->refSeconds < $expectedSecondsMin) {
                    $messageMin = '[ ERROR ] Test ' . __METHOD__ . '() `expectedSecondsMin` failed.';
                    $messageCaseMin = ''
                        . 'Case: '
                        . sprintf('%.6f', $this->refSeconds)
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

                if ($hasResource) {
                    fwrite($h, '------' . "\n");
                    if (null !== $messageMax) {
                        fwrite($h, $messageMax . "\n");
                    }
                    if (null !== $messageMin) {
                        fwrite($h, $messageMin . "\n");
                    }
                    fwrite($h, "{$traceFile} : {$traceLine}" . "\n");
                    if (null !== $messageCaseMax) {
                        fwrite($h, $messageCaseMax . "\n");
                    }
                    if (null !== $messageCaseMin) {
                        fwrite($h, $messageCaseMin . "\n");
                    }
                    fwrite($h, '------' . "\n");
                }

                $e = new RuntimeException(implode(' | ', array_filter($message)));
                $e->setTrace($trace);
                $e->setFile($traceFile);
                $e->setLine($traceLine);

                $eArray[] = $e;
            }
        }

        if ($this->hasReturn($return)) {
            $expectedReturn = $expectedReturn[ 0 ] ?? null;

            $isReturnDiff = ($this->refReturn !== $expectedReturn);

            if ($isReturnDiff) {
                $message = '[ ERROR ] Test ' . __METHOD__ . '() `expectedReturn` failed.';

                $theDebug->diff_vars($this->refReturn, $expectedReturn, [ &$diffLines ]);

                $diffString = implode("\n", $diffLines);

                if ($hasResource) {
                    fwrite($h, '------' . "\n");
                    fwrite($h, $message . "\n");
                    fwrite($h, "{$traceFile} : {$traceLine}" . "\n");
                    fwrite($h, $diffString . "\n");
                    fwrite($h, '------' . "\n");
                }

                $e = new RuntimeException([ $message, $diffString ]);
                $e->setTrace($trace);
                $e->setFile($traceFile);
                $e->setLine($traceLine);

                $eArray[] = $e;
            }
        }

        if ($this->hasMemoryMax($expectedMemoryMaxBytes)) {
            if ($this->refMemoryBytes > $expectedMemoryMaxBytes) {
                $message = '[ ERROR ] Test ' . __METHOD__ . '() `expectedBytesMax` failed.';
                $messageCase = ''
                    . 'Case: '
                    . sprintf('%f', $this->refMemoryBytes)
                    . ' > '
                    . sprintf('%f', $this->refMemoryBytes);

                if ($hasResource) {
                    fwrite($h, '------' . "\n");
                    fwrite($h, $message . "\n");
                    fwrite($h, "{$traceFile} : {$traceLine}" . "\n");
                    fwrite($h, $messageCase . "\n");
                    fwrite($h, '------' . "\n");
                }

                $e = new RuntimeException([ $message, $messageCase ]);
                $e->setTrace($trace);
                $e->setFile($traceFile);
                $e->setLine($traceLine);

                $eArray[] = $e;
            }
        }

        if ([] !== $eArray) {
            $message = '[ ERROR ] Test ' . __METHOD__ . '() failed.';

            if ($hasResource) {
                return false;
            }

            throw new RuntimeException($message, ...$eArray);
        }

        if ($hasResource) {
            fwrite($h, '[ OK ] Test ' . __METHOD__ . '() passed.' . "\n");
        }

        unset($refStdout);
        unset($refSeconds);
        unset($refReturn);
        unset($refBytes);

        return true;
    }
}
