<?php

namespace Gzhegow\Lib\Modules\Async\FetchApi;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


class FilesystemFetchApi implements FetchApiInterface
{
    /**
     * @var bool
     */
    protected $isShutdownFunctionRegistered = false;

    /**
     * @var string
     */
    protected $binDirRealpath;
    /**
     * @var string
     */
    protected $binFilename;
    /**
     * @var string
     */
    protected $binFileRealpath;

    /**
     * @var string
     */
    protected $poolDirRealpath;
    /**
     * @var string
     */
    protected $poolFilename;
    /**
     * @var string
     */
    protected $poolFile;

    /**
     * @var string
     */
    protected $queueDirRealpath;
    /**
     * @var string
     */
    protected $queueFilename;
    /**
     * @var string
     */
    protected $queueFile;

    /**
     * @var string
     */
    protected $taskResultDirRealpath;


    public function __construct(array $config = [])
    {
        $theType = Lib::type($tt);

        $theType->realpath($binDirDefault, __DIR__ . '/../../../../bin/php/');
        $binDirRealpath = '';
        $binFilename = '';
        $binFileRealpath = '';

        $theType->realpath($poolDirDefault, __DIR__ . '/../../../../var/run/bin/php/curl-api/');
        $poolDirRealpath = '';
        $poolFilename = '';

        $theType->realpath($queueDirDefault, __DIR__ . '/../../../../var/queue/bin/php/curl-api/task/');
        $queueDirRealpath = '';
        $queueFilename = '';

        $theType->realpath($taskResultDirDefault, __DIR__ . '/../../../../var/tmp/bin/php/curl-api/task-result/');
        $taskResultDirRealpath = '';

        if (! $theType->dirpath_realpath($binDirRealpath, $binDir = $config[ 'bin_dir' ] ?? $binDirDefault)) {
            throw new LogicException(
                [ 'The `config[bin_dir]` should be existing directory', $binDir ]
            );
        }
        if (! $theType->dirpath_realpath($poolDirRealpath, $poolDir = $config[ 'pool_dir' ] ?? $poolDirDefault)) {
            throw new LogicException(
                [ 'The `config[pool_dir]` should be existing directory', $poolDir ]
            );
        }
        if (! $theType->dirpath_realpath($queueDirRealpath, $queueDir = $config[ 'queue_dir' ] ?? $queueDirDefault)) {
            throw new LogicException(
                [ 'The `config[queue_dir]` should be existing directory', $queueDir ]
            );
        }
        if (! $theType->dirpath_realpath($taskResultDirRealpath, $taskResultDir = $config[ 'task_result_dir' ] ?? $taskResultDirDefault)) {
            throw new LogicException(
                [ 'The `config[task_result_dir]` should be existing directory', $taskResultDir ]
            );
        }
        if (! $theType->filename($binFilename, $binFilenameSrc = $config[ 'bin_filename' ] ?? 'curl-api.php')) {
            throw new LogicException(
                [ 'The `config[bin_filename]` should be valid filename', $binFilenameSrc ]
            );
        }
        if (! $theType->filename($poolFilename, $poolFilenameSrc = $config[ 'pool_filename' ] ?? 'curl-api.pool')) {
            throw new LogicException(
                [ 'The `config[pool_filename]` should be valid filename', $poolFilenameSrc ]
            );
        }
        if (! $theType->filename($queueFilename, $queueFilenameSrc = $config[ 'queue_filename' ] ?? 'curl-api.queue')) {
            throw new LogicException(
                [ 'The `config[queue_filename]` should be valid filename', $queueFilenameSrc ]
            );
        }

        $binFilename = basename($binFilename, '.php') . '.php';
        $binFile = "{$binDirRealpath}/{$binFilename}";

        $queueFilename = basename($queueFilename, '.queue') . '.queue';
        $poolFile = "{$poolDirRealpath}/{$poolFilename}";

        $poolFilename = basename($poolFilename, '.pool') . '.pool';
        $queueFile = "{$queueDirRealpath}/{$queueFilename}";

        if (! $theType->filepath_realpath($binFileRealpath, $binFile)) {
            throw new LogicException(
                [ 'The `binFile` should be existing file', $binFile ]
            );
        }
        if (! $theType->filepath($poolFile, $poolFile, true)) {
            throw new LogicException(
                [ 'The `poolFile` should be valid filepath', $poolFile ]
            );
        }
        if (! $theType->freepath($queueFile, $queueFile)) {
            throw new LogicException(
                [ 'The `queueFile` should be valid filepath', $queueFile ]
            );
        }

        $this->binDirRealpath = $binDirRealpath;
        $this->binFilename = $binFilename;
        $this->binFileRealpath = $binFileRealpath;

        $this->poolDirRealpath = $poolDirRealpath;
        $this->poolFilename = $poolFilename;
        $this->poolFile = $poolFile;

        $this->queueDirRealpath = $queueDirRealpath;
        $this->queueFilename = $queueFilename;
        $this->queueFile = $queueFile;

        $this->taskResultDirRealpath = $taskResultDirRealpath;
    }


    public function pushTask(
        &$refTaskId,
        string $url, array $curlOptions = [],
        ?int $lockWaitTimeoutMs = 0
    ) : bool
    {
        $theType = Lib::type();

        if (! $theType->url($urlString, $url)) {
            throw new LogicException(
                [ 'The `url` should be valid url', $url ]
            );
        }

        if (! $theType->list($curlOptionsList, $curlOptions)) {
            throw new LogicException(
                [ 'The `curlOptions` should be list of CURL options', $curlOptions ]
            );
        }

        $queueFile = $this->queueFile;

        $refTaskId = Lib::random()->uuid();

        $task = [
            'id'           => $refTaskId,
            'url'          => $url,
            'curl_options' => $curlOptions,
        ];

        $serialized = serialize($task);

        $statusPush = Lib::fs()->brpush(
            100000, $lockWaitTimeoutMs,
            $queueFile, $serialized
        );

        return $statusPush;
    }

    public function popTask(
        &$refTask,
        ?int $blockTimeoutMs = 0
    ) : bool
    {
        $queueFile = $this->queueFile;

        $serialized = Lib::fs()->blpop(
            100000, $blockTimeoutMs,
            $queueFile, true
        );

        if (null !== $serialized) {
            $refTask = unserialize($serialized);

            return true;
        }

        return false;
    }


    public function clearTaskResults() : void
    {
        $gen = Lib::fs()->dir_walk_it($this->taskResultDirRealpath);

        foreach ( $gen as $spl ) {
            if ($spl->isDir()) {
                continue;
            }

            if ($spl->getBasename() === '.gitignore') {
                continue;
            }

            unlink($spl->getRealPath());
        }
    }

    public function taskGetResult(&$refTaskResult, string $taskId) : bool
    {
        if (! Lib::type()->string_not_empty($taskIdString, $taskId)) {
            throw new LogicException(
                [ 'The `taskId` should be non-empty string', $taskId ]
            );
        }

        return $this->taskFetchResult(
            $refTaskResult,
            $taskIdString, false
        );
    }

    public function taskFlushResult(&$refTaskResult, string $taskId) : bool
    {
        if (! Lib::type()->string_not_empty($taskIdString, $taskId)) {
            throw new LogicException(
                [ 'The `taskId` should be non-empty string', $taskId ]
            );
        }

        return $this->taskFetchResult(
            $refTaskResult,
            $taskIdString, true
        );
    }

    protected function taskFetchResult(
        &$refTaskResult,
        string $taskId, bool $delete
    ) : bool
    {
        $refTaskResult = null;

        $taskResultFile = "{$this->taskResultDirRealpath}/{$taskId}.result";

        $statusGet = false;
        if (is_file($taskResultFile) && (filesize($taskResultFile) > 0)) {
            $serialized = file_get_contents($taskResultFile);

            if (false !== $serialized) {
                $statusGet = true;

                if ($delete) {
                    unlink($taskResultFile);
                }

                $refTaskResult = unserialize($serialized);
            }
        }

        return $statusGet;
    }

    protected function taskSaveResult(array $task, array $taskResult) : bool
    {
        $taskId = $task[ 'id' ];

        $taskResultFile = "{$this->taskResultDirRealpath}/{$taskId}.result";

        $serialized = serialize($taskResult);

        $len = file_put_contents($taskResultFile, $serialized);

        $statusGet = $len !== false;

        return $statusGet;
    }


    protected function processTask(&$refTaskResult, array $task) : bool
    {
        return $this->processTaskUsingCurl($refTaskResult, $task);
    }

    protected function processTaskUsingCurl(&$refTaskResult, array $task) : bool
    {
        $refTaskResult = null;

        $taskUrl = $task[ 'url' ];
        $taskCurlOptions = $task[ 'curl_options' ];

        $ch = curl_init($taskUrl);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        if ([] !== $taskCurlOptions) {
            curl_setopt_array($ch, $taskCurlOptions);
        }

        $response = curl_exec($ch);
        if (false === $response) {
            return false;
        }

        $headersSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $headersSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headersSize);
        $content = substr($response, $headersSize);

        $refTaskResult = [
            'headers_sent' => $headersSent,
            //
            'http_code'    => $httpCode,
            'headers'      => $headers,
            'content'      => $content,
        ];

        return true;
    }


    public function daemonAddToPool(int $daemonTimeoutMs, ?float $nowMicrotime = null) : bool
    {
        $theType = Lib::type();

        if (! $theType->int_positive($daemonTimeoutMsInt, $daemonTimeoutMs)) {
            throw new LogicException(
                [ 'The `timeoutMs` should be positive integer', $daemonTimeoutMs ]
            );
        }

        if ((null !== ($nowMicrotimeFloat = $nowMicrotime))) {
            if (! $theType->float_non_negative($nowMicrotimeFloat, $nowMicrotime)) {
                throw new LogicException(
                    [ 'The `nowMicrotime` should be non-negative float', $nowMicrotime ]
                );
            }
        }

        $daemonPid = getmypid();

        $statusAdd = $this->workerAddToPool(
            $daemonPid, $daemonTimeoutMsInt,
            $nowMicrotimeFloat
        );

        return $statusAdd;
    }

    public function daemonRemoveFromPool(?float $nowMicrotime = null) : bool
    {
        $theType = Lib::type();

        if ((null !== ($nowMicrotimeFloat = $nowMicrotime))) {
            if (! $theType->float_non_negative($nowMicrotimeFloat, $nowMicrotime)) {
                throw new LogicException(
                    [ 'The `nowMicrotime` should be non-negative float', $nowMicrotime ]
                );
            }
        }

        $daemonPid = getmypid();

        $statusRemove = $this->workerRemoveFromPool(
            $daemonPid,
            $nowMicrotimeFloat
        );

        return $statusRemove;
    }


    /**
     * @param int        $workerPid
     * @param int        $workerTimeoutMs
     * @param float|null $nowMicrotime
     */
    protected function workerAddToPool(
        $workerPid, $workerTimeoutMs,
        $nowMicrotime = null
    ) : bool
    {
        $poolFile = $this->poolFile;

        $f = Lib::fs()->fileSafe();

        $status = $f->callSafe(
            static function () use (
                $f,
                $workerPid, $poolFile,
                $workerTimeoutMs, $nowMicrotime
            ) {
                $status = false;

                if ($fhPool = $f->fopen_flock_pooling(
                    100000, $workerTimeoutMs,
                    $poolFile, 'c+', LOCK_EX | LOCK_NB
                )) {
                    $nowMicrotimeFloat = $nowMicrotime ?? microtime(true);

                    $workerPidString = ltrim($workerPid, '0');

                    $lines = [];
                    while ( ! feof($fhPool) ) {
                        $line = fgets($fhPool);

                        $lineTrim = rtrim($line);
                        if ('' === $lineTrim) {
                            continue;
                        }

                        [ $pidLineString, $workerTimeoutMicrotimeLineString ] = explode('|', $lineTrim);

                        $workerTimeoutMicrotimeLineFloat = (float) $workerTimeoutMicrotimeLineString;
                        if ($nowMicrotimeFloat > $workerTimeoutMicrotimeLineFloat) {
                            continue;
                        }

                        $pidLineString = ltrim($pidLineString, '0');
                        if ($pidLineString === $workerPidString) {
                            continue;
                        }

                        $lines[] = $lineTrim;
                    }

                    $workerTimeoutMicrotimeFloat = $nowMicrotimeFloat + ($workerTimeoutMs / 1000);

                    $workerPidNewString = str_pad($workerPid, 10, '0', STR_PAD_LEFT);
                    $workerTimeoutMicrotimeNewString = sprintf('%.6f', $workerTimeoutMicrotimeFloat);

                    $lines[] = "{$workerPidNewString}|{$workerTimeoutMicrotimeNewString}";

                    $content = implode("\n", $lines);

                    rewind($fhPool);
                    ftruncate($fhPool, 0);

                    fwrite($fhPool, $content);

                    $status = true;
                }

                return $status;
            }
        );

        return $status;
    }

    /**
     * @param int        $workerPid
     * @param float|null $nowMicrotime
     */
    protected function workerRemoveFromPool(
        $workerPid,
        $nowMicrotime = null
    ) : bool
    {
        $poolFile = $this->poolFile;

        if (! is_file($poolFile)) {
            return true;
        }

        $f = Lib::fs()->fileSafe();

        $status = $f->callSafe(
            static function () use (
                $f,
                $workerPid, $poolFile,
                $nowMicrotime
            ) {
                $status = false;

                if ($fhPool = $f->fopen_flock_pooling(
                    100000, 1000,
                    $poolFile, 'r+', LOCK_EX | LOCK_NB
                )) {
                    $pidString = ltrim($workerPid, '0');
                    $nowMicrotimeFloat = $nowMicrotime ?? microtime(true);

                    $lines = [];
                    while ( ! feof($fhPool) ) {
                        $line = fgets($fhPool);

                        $lineTrim = rtrim($line);
                        if ('' === $lineTrim) {
                            continue;
                        }

                        [ $pidLineString, $timeoutMicrotimeLineString ] = explode('|', $lineTrim);

                        $timeoutMicrotimeLineFloat = (float) $timeoutMicrotimeLineString;
                        if ($nowMicrotimeFloat > $timeoutMicrotimeLineFloat) {
                            continue;
                        }

                        $pidLineString = ltrim($pidLineString, '0');
                        if ($pidLineString === $pidString) {
                            continue;
                        }

                        $lines[] = $lineTrim;
                    }

                    rewind($fhPool);
                    ftruncate($fhPool, 0);

                    if ([] !== $lines) {
                        $content = implode("\n", $lines);

                        fwrite($fhPool, $content);
                    }

                    $status = true;
                }

                return $status;
            }
        );

        if (is_file($poolFile) && ! filesize($poolFile)) {
            unlink($poolFile);
        }

        return $status;
    }


    public function daemonIsAwake(
        ?int &$refPidFirst = null,
        ?float $nowMicrotime = null
    ) : bool
    {
        $refPidFirst = null;

        $theType = Lib::type();

        if ((null !== ($nowMicrotimeFloat = $nowMicrotime))) {
            if (! $theType->float_non_negative($nowMicrotimeFloat, $nowMicrotime)) {
                throw new LogicException(
                    [ 'The `nowMicrotime` should be non-negative float', $nowMicrotime ]
                );
            }
        }

        $poolFile = $this->poolFile;

        if (! is_file($poolFile)) {
            return false;
        }

        $f = Lib::fs()->fileSafe();

        $status = $f->callSafe(
            static function () use (
                &$refPidFirst,
                //
                $f,
                $poolFile,
                $nowMicrotimeFloat
            ) {
                $status = false;

                if ($fhPool = $f->fopen_flock_pooling(
                    100000, 1000,
                    $poolFile, 'r', LOCK_SH | LOCK_NB
                )) {
                    $nowMicrotimeFloat = microtime(true);

                    $pidFirstLine = null;

                    while ( ! feof($fhPool) ) {
                        $line = fgets($fhPool);

                        $lineTrim = rtrim($line);
                        if ('' === $lineTrim) {
                            continue;
                        }

                        [ $pidLineString, $timeoutMicrotimeLineString ] = explode('|', $lineTrim);

                        $timeoutMicrotimeFloat = (float) $timeoutMicrotimeLineString;
                        if ($nowMicrotimeFloat > $timeoutMicrotimeFloat) {
                            continue;
                        }

                        $pidFirstLine = $pidLineString;

                        break;
                    }

                    if (null !== $pidFirstLine) {
                        $refPidFirst = (int) ltrim($pidFirstLine, '0');

                        $status = true;
                    }
                }

                return $status;
            }
        );

        if (is_file($poolFile) && ! filesize($poolFile)) {
            unlink($poolFile);
        }

        return $status;
    }

    public function daemonWakeup(
        ?int $timeoutMs = null,
        ?int $lockWaitTimeoutMs = null
    ) : void
    {
        // > метод не должен вызываться, чтобы поднять несколько демонов, для этого есть daemonSpawn
        $this->clearTaskResults();

        $this->daemonSpawn($timeoutMs, $lockWaitTimeoutMs);
    }

    public function daemonSpawn(
        ?int $timeoutMs = null,
        ?int $lockWaitTimeoutMs = null
    ) : void
    {
        $theType = Lib::type();

        $timeoutMs = $timeoutMs ?? 10000;
        $lockWaitTimeoutMs = $lockWaitTimeoutMs ?? 1000;

        if (! $theType->int_non_negative_or_minus_one($timeoutMsInt, $timeoutMs)) {
            throw new LogicException(
                [ 'The `timeoutMs` should be non-negative integer or be -1', $timeoutMs ]
            );
        }

        if (! $theType->int_non_negative_or_minus_one($lockWaitTimeoutMsInt, $lockWaitTimeoutMs)) {
            throw new LogicException(
                [ 'The `lockWaitTimeoutMs` should be non-negative integer or be -1', $lockWaitTimeoutMs ]
            );
        }

        $pm = Lib::cli()->processManager();

        $cmd = [];
        $cmd[] = realpath(PHP_BINARY);
        $cmd[] = $this->binFilename;
        $cmd[] = $timeoutMs;
        $cmd[] = $lockWaitTimeoutMs;

        $proc = $pm->newProc()
            ->setIsBackground(true)
            ->setCmd($cmd)
            ->setCwd($this->binDirRealpath)
        ;

        $pm->spawn($proc);
    }


    public function daemonMain(
        int $timeoutMs,
        int $lockWaitTimeoutMs
    ) : void
    {
        $theType = Lib::type();

        $pid = getmypid();

        if (! $theType->int_non_negative_or_minus_one($timeoutMsInt, $timeoutMs)) {
            throw new LogicException(
                [ 'The `timeoutMs` should be non-negative integer or -1', $timeoutMs ]
            );
        }

        if (! $theType->int_non_negative_or_minus_one($lockWaitTimeoutMsInt, $lockWaitTimeoutMs)) {
            throw new LogicException(
                [ 'The `lockWaitTimeoutMs` should be non-negative integer or -1', $lockWaitTimeoutMs ]
            );
        }

        if (-1 === $timeoutMsInt) $timeoutMsInt = null;
        if (-1 === $lockWaitTimeoutMsInt) $lockWaitTimeoutMsInt = null;

        $this->registerShutdownFunctionDaemon();

        echo "[ CURL-API ] Listening for tasks...\n";

        $this->workerRunLoop(
            $pid,
            $timeoutMsInt,
            $lockWaitTimeoutMsInt
        );
    }

    public function shutdownFunctionDaemon() : void
    {
        $this->daemonRemoveFromPool();
    }

    protected function registerShutdownFunctionDaemon() : void
    {
        if (! $this->isShutdownFunctionRegistered) {
            register_shutdown_function([ $this, 'shutdownFunctionDaemon' ]);

            $this->isShutdownFunctionRegistered = true;
        }
    }


    protected function workerRunLoop(
        int $pid,
        ?int $timeoutMs = null,
        ?int $lockWaitTimeoutMs = null
    ) : void
    {
        $isNullTimeout = (null === $timeoutMs);

        $nowMicrotime = microtime(true);

        $timeoutReportMs = $timeoutMs ?? 10000;

        $timeoutMicrotime = null;
        if (! $isNullTimeout) {
            $timeoutMicrotime = $nowMicrotime + ($timeoutMs / 1000);
        }

        $timeoutReportMicrotime = 0.0;

        do {
            $nowMicrotime = microtime(true);

            if ($nowMicrotime > $timeoutReportMicrotime) {
                $this->workerAddToPool($pid, $timeoutReportMs, $nowMicrotime);

                $timeoutReportMicrotime = $nowMicrotime + ($timeoutReportMs / 1000);
            }

            $task = [];
            $taskResult = [];

            $status = true
                && $this->popTask($task, $lockWaitTimeoutMs)
                && print_r('[ NEW ] Task: ' . $task[ 'id' ] . "\n")
                && $this->processTask($taskResult, $task)
                && print_r('[ END ] Task: ' . $task[ 'id' ] . "\n")
                && $this->taskSaveResult($task, $taskResult);

            if (! $isNullTimeout) {
                if ($status) {
                    $timeoutMicrotime = $nowMicrotime + ($timeoutMs / 1000);

                } elseif ($nowMicrotime > $timeoutMicrotime) {
                    break;
                }
            }

            usleep(1000);
        } while ( true );
    }
}
