<?php

namespace Gzhegow\Lib\Modules\Async\FetchApi;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class FilesystemFetchApi implements FetchApiInterface
{
    /**
     * @var bool
     */
    protected $isRegisterShutdownFunctionCalled = false;

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
        if (! extension_loaded('curl')) {
            throw new ExtensionException(
                [ 'Missing PHP extension: curl' ]
            );
        }

        $theType = Lib::type();

        $binDirDefault = $theType->realpath(__DIR__ . '/../../../../bin/php/')->orThrow();
        $binDirRealpath = '';
        $binFilename = '';

        $poolDirDefault = $theType->realpath(__DIR__ . '/../../../../var/run/bin/php/curl-api/')->orThrow();
        $poolDirRealpath = '';
        $poolFilename = '';

        $queueDirDefault = $theType->realpath(__DIR__ . '/../../../../var/queue/bin/php/curl-api/task/')->orThrow();
        $queueDirRealpath = '';
        $queueFilename = '';

        $taskResultDirDefault = $theType->realpath(__DIR__ . '/../../../../var/tmp/bin/php/curl-api/task-result/')->orThrow();
        $taskResultDirRealpath = '';

        if (! $theType->dirpath_realpath($binDir = $config[ 'bin_dir' ] ?? $binDirDefault)->isOk([ &$binDirRealpath ])) {
            throw new LogicException(
                [ 'The `config[bin_dir]` should be an existing directory', $binDir ]
            );
        }
        if (! $theType->dirpath_realpath($poolDir = $config[ 'pool_dir' ] ?? $poolDirDefault)->isOk([ &$poolDirRealpath ])) {
            throw new LogicException(
                [ 'The `config[pool_dir]` should be an existing directory', $poolDir ]
            );
        }
        if (! $theType->dirpath_realpath($queueDir = $config[ 'queue_dir' ] ?? $queueDirDefault)->isOk([ &$queueDirRealpath ])) {
            throw new LogicException(
                [ 'The `config[queue_dir]` should be an existing directory', $queueDir ]
            );
        }
        if (! $theType->dirpath_realpath($taskResultDir = $config[ 'task_result_dir' ] ?? $taskResultDirDefault)->isOk([ &$taskResultDirRealpath ])) {
            throw new LogicException(
                [ 'The `config[task_result_dir]` should be an existing directory', $taskResultDir ]
            );
        }
        if (! $theType->filename($binFilenameSrc = $config[ 'bin_filename' ] ?? 'curl-api.php')->isOk([ &$binFilename ])) {
            throw new LogicException(
                [ 'The `config[bin_filename]` should be a valid filename', $binFilenameSrc ]
            );
        }
        if (! $theType->filename($poolFilenameSrc = $config[ 'pool_filename' ] ?? 'curl-api.pool')->isOk([ &$poolFilename ])) {
            throw new LogicException(
                [ 'The `config[pool_filename]` should be a valid filename', $poolFilenameSrc ]
            );
        }
        if (! $theType->filename($queueFilenameSrc = $config[ 'queue_filename' ] ?? 'curl-api.queue')->isOk([ &$queueFilename ])) {
            throw new LogicException(
                [ 'The `config[queue_filename]` should be a valid filename', $queueFilenameSrc ]
            );
        }

        $binFilename = basename($binFilename, '.php') . '.php';
        $binFile = "{$binDirRealpath}/{$binFilename}";

        $queueFilename = basename($queueFilename, '.queue') . '.queue';
        $poolFile = "{$poolDirRealpath}/{$poolFilename}";

        $poolFilename = basename($poolFilename, '.pool') . '.pool';
        $queueFile = "{$queueDirRealpath}/{$queueFilename}";

        $binFileRealpath = $theType->filepath_realpath($binFile)->orThrow();
        $poolFileFilepath = $theType->filepath($poolFile, true)->orThrow();
        $queueFileFreepath = $theType->freepath($queueFile)->orThrow();

        $this->binDirRealpath = $binDirRealpath;
        $this->binFilename = $binFilename;
        $this->binFileRealpath = $binFileRealpath;

        $this->poolDirRealpath = $poolDirRealpath;
        $this->poolFilename = $poolFilename;
        $this->poolFile = $poolFileFilepath;

        $this->queueDirRealpath = $queueDirRealpath;
        $this->queueFilename = $queueFilename;
        $this->queueFile = $queueFileFreepath;

        $this->taskResultDirRealpath = $taskResultDirRealpath;
    }


    public function pushTask(
        &$refTaskId,
        string $url, array $curlOptions = [],
        ?int $lockWaitTimeoutMs = 0
    ) : bool
    {
        $refTaskId = null;

        $theFs = Lib::fs();
        $theRandom = Lib::random();
        $theType = Lib::type();

        $urlString = $theType->url($url)->orThrow();
        $curlOptionsList = $theType->list($curlOptions)->orThrow();

        $queueFile = $this->queueFile;

        $taskId = $theRandom->uuid();

        $task = [
            'id'           => $taskId,
            'url'          => $urlString,
            'curl_options' => $curlOptionsList,
        ];

        $serialized = serialize($task);

        $statusPush = $theFs->brpush(
            100000, $lockWaitTimeoutMs,
            $queueFile, $serialized
        );

        if ($statusPush) {
            $refTaskId = $taskId;
        }

        return $statusPush;
    }

    public function popTask(
        &$refTask,
        ?int $blockTimeoutMs = 0
    ) : bool
    {
        $refTask = null;

        $theFs = Lib::fs();

        $queueFile = $this->queueFile;

        $serialized = $theFs->blpop(
            100000, $blockTimeoutMs,
            $queueFile, true
        );

        if (null !== $serialized) {
            $refTask = unserialize($serialized);

            return true;
        }

        return false;
    }


    public function taskClearResults() : void
    {
        $theFs = Lib::fs();

        $gen = $theFs->dir_walk_it($this->taskResultDirRealpath);

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
        $theType = Lib::type();

        $taskIdStringNotEmpty = $theType->string_not_empty($taskId)->orThrow();

        $statusGet = $this->taskFetchResult(
            $refTaskResult,
            $taskIdStringNotEmpty, false
        );

        return $statusGet;
    }

    public function taskFlushResult(&$refTaskResult, string $taskId) : bool
    {
        $theType = Lib::type();

        $taskIdString = $theType->string_not_empty($taskId)->orThrow();

        $statusFetch = $this->taskFetchResult(
            $refTaskResult,
            $taskIdString, true
        );

        return $statusFetch;
    }

    protected function taskFetchResult(
        &$refTaskResult,
        string $taskId, bool $delete
    ) : bool
    {
        $refTaskResult = null;

        $taskResultFile = "{$this->taskResultDirRealpath}/{$taskId}.result";

        $statusFetch = false;
        if (is_file($taskResultFile) && (filesize($taskResultFile) > 0)) {
            $serialized = file_get_contents($taskResultFile);

            if (false !== $serialized) {
                $statusFetch = true;

                if ($delete) {
                    unlink($taskResultFile);
                }

                $refTaskResult = unserialize($serialized);
            }
        }

        return $statusFetch;
    }

    protected function taskSaveResult(array $task, array $taskResult) : bool
    {
        $taskId = $task[ 'id' ];

        $taskResultFile = "{$this->taskResultDirRealpath}/{$taskId}.result";

        $serialized = serialize($taskResult);

        $len = file_put_contents($taskResultFile, $serialized);

        $statusSave = false !== $len;

        return $statusSave;
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

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

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

        $urlOut = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $headersOut = curl_getinfo($ch, CURLINFO_HEADER_OUT);

        $headersSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headersSize);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content = substr($response, $headersSize);

        $refTaskResult = [
            'url'         => $taskUrl,
            //
            'url_out'     => $urlOut,
            'headers_out' => $headersOut,
            //
            'headers'     => $headers,
            'http_code'   => $httpCode,
            'content'     => $content,
        ];

        return true;
    }


    public function daemonAddToPool(int $daemonTimeoutMs, ?float $nowMicrotime = null) : bool
    {
        $theType = Lib::type();

        $daemonTimeoutMsInt = $theType->int_positive($daemonTimeoutMs)->orThrow();

        if ((null !== ($nowMicrotimeFloat = $nowMicrotime))) {
            $nowMicrotimeFloat = $theType->float_non_negative($nowMicrotime)->orThrow();
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
            $nowMicrotimeFloat = $theType->float_non_negative($nowMicrotime)->orThrow();
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
        $theFs = Lib::fs();
        $theFsFile = $theFs->fileSafe();

        $poolFile = $this->poolFile;

        $status = $theFsFile->call_safe(
            static function () use (
                $theFsFile,
                $workerPid, $poolFile,
                $workerTimeoutMs, $nowMicrotime
            ) {
                $status = false;

                if ($fhPool = $theFsFile->fopen_flock_pooling(
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
        $theFs = Lib::fs();
        $theFsFile = $theFs->fileSafe();

        $poolFile = $this->poolFile;

        if (! is_file($poolFile)) {
            return true;
        }

        $status = $theFsFile->call_safe(
            static function () use (
                $theFsFile,
                $workerPid, $poolFile,
                $nowMicrotime
            ) {
                $status = false;

                if ($fhPool = $theFsFile->fopen_flock_pooling(
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


    public function daemonIsAwake(?int &$refPidFirst = null) : bool
    {
        $refPidFirst = null;

        $theFs = Lib::fs();
        $theFsFile = $theFs->fileSafe();

        $poolFile = $this->poolFile;

        if (! is_file($poolFile)) {
            return false;
        }

        $status = $theFsFile->call_safe(
            static function () use (
                &$refPidFirst,
                //
                $theFsFile,
                $poolFile
            ) {
                $status = false;

                if ($fhPool = $theFsFile->fopen_flock_pooling(
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
        $this->taskClearResults();

        $this->daemonSpawn($timeoutMs, $lockWaitTimeoutMs);
    }

    public function daemonSpawn(
        ?int $timeoutMs = null,
        ?int $lockWaitTimeoutMs = null
    ) : void
    {
        $timeoutMs = $timeoutMs ?? 10000;
        $lockWaitTimeoutMs = $lockWaitTimeoutMs ?? 1000;

        $theCli = Lib::cli();
        $theCliProcessManager = $theCli->processManager();
        $theType = Lib::type();

        $timeoutMsInt = $theType->int_non_negative_or_minus_one($timeoutMs)->orThrow();
        $lockWaitTimeoutMsInt = $theType->int_non_negative_or_minus_one($lockWaitTimeoutMs)->orThrow();

        $cmd = [];
        $cmd[] = realpath(PHP_BINARY);
        $cmd[] = $this->binFilename;
        $cmd[] = $timeoutMsInt;
        $cmd[] = $lockWaitTimeoutMsInt;

        $proc = $theCliProcessManager->newProcNormal();

        $proc
            ->setCmd($cmd)
            ->setCwd($this->binDirRealpath)
        ;

        $theCliProcessManager->spawnNormal($proc);
    }


    public function daemonMain(
        int $timeoutMs,
        int $lockWaitTimeoutMs
    ) : void
    {
        $theType = Lib::type();

        $pid = getmypid();

        $timeoutMsInt = $theType->int_non_negative_or_minus_one($timeoutMs)->orThrow();
        $lockWaitTimeoutMsInt = $theType->int_non_negative_or_minus_one($lockWaitTimeoutMs)->orThrow();

        if (-1 === $timeoutMsInt) $timeoutMsInt = null;
        if (-1 === $lockWaitTimeoutMsInt) $lockWaitTimeoutMsInt = null;

        $this->registerShutdownFunction();

        echo "[ CURL-API ] Listening for tasks...\n";

        $this->workerRunLoop(
            $pid,
            $timeoutMsInt,
            $lockWaitTimeoutMsInt
        );
    }


    public function registerShutdownFunctionFn() : void
    {
        $this->daemonRemoveFromPool();
    }

    protected function registerShutdownFunction() : void
    {
        if (! $this->isRegisterShutdownFunctionCalled) {
            register_shutdown_function([ $this, 'registerShutdownFunctionFn' ]);

            $this->isRegisterShutdownFunctionCalled = true;
        }
    }


    protected function workerRunLoop(
        int $pid,
        ?int $timeoutMs = null,
        ?int $waitTimeoutMs = null
    ) : void
    {
        $isNullTimeout = (null === $timeoutMs);

        $nowMicrotime = microtime(true);

        $timeoutBreakMs = $timeoutMs ?? 10000;
        $timeoutReportMs = $timeoutMs ?? 10000;

        $timeoutBreakMicrotime = null;
        if (! $isNullTimeout) {
            $timeoutBreakMicrotime = $nowMicrotime + ($timeoutBreakMs / 1000);
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

            /**
             * @noinspection PhpVarExportUsedWithoutReturnArgumentInspection
             */
            $status = true
                && $this->popTask($task, $waitTimeoutMs)
                && print_r('[ NEW ] Task: ' . $task[ 'id' ] . "\n")
                && $this->processTask($taskResult, $task)
                && print_r('[ OK ] Task: ' . $task[ 'id' ] . "\n")
                && $this->taskSaveResult($task, $taskResult);

            if (! $isNullTimeout) {
                if ($status) {
                    $timeoutBreakMicrotime = $nowMicrotime + ($timeoutBreakMs / 1000);

                } elseif ($nowMicrotime > $timeoutBreakMicrotime) {
                    break;
                }
            }

            usleep(1000);
        } while ( true );
    }
}
