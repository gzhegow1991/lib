<?php

namespace Gzhegow\Lib\Modules\Async\FetchApi;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\Runtime\FilesystemException;


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
        $theType = Lib::type();

        $binDirDefault = realpath(__DIR__ . '/../../../../bin/php/');
        $binDirRealpath = '';
        $binFilename = '';
        $binFileRealpath = '';

        $poolDirDefault = realpath(__DIR__ . '/../../../../var/run/bin/php/curl-api/');
        $poolDirRealpath = '';
        $poolFilename = '';

        $queueDirDefault = realpath(__DIR__ . '/../../../../var/queue/bin/php/curl-api/task/');
        $queueDirRealpath = '';
        $queueFilename = '';

        $taskResultDirDefault = realpath(__DIR__ . '/../../../../var/tmp/bin/php/curl-api/task-result/');
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
        string $url, array $curlOptions = [],
        ?int $lockWaitTimeoutMs = 0,
        ?string &$taskId = null
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

        $taskId = Lib::random()->uuid();

        $task = [
            'id'           => $taskId,
            'url'          => $url,
            'curl_options' => $curlOptions,
        ];

        $serialized = serialize($task);

        $statusPush = Lib::fs()->rpush($this->queueFile, $serialized, $lockWaitTimeoutMs);

        return $statusPush;
    }

    public function popTask(
        ?int $blockTimeoutMs = 0,
        ?array &$task = null
    ) : bool
    {
        $serialized = Lib::fs()->lpop($this->queueFile, $blockTimeoutMs, false);

        if (null !== $serialized) {
            $task = unserialize($serialized);

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

            @unlink($spl->getRealPath());
        }
    }

    public function taskGetResult(string $taskId, ?array &$taskResult = null) : bool
    {
        if (! Lib::type()->string_not_empty($taskIdString, $taskId)) {
            throw new LogicException(
                [ 'The `taskId` should be non-empty string', $taskId ]
            );
        }

        return $this->taskFetchResult($taskIdString, false, $taskResult);
    }

    public function taskFlushResult(string $taskId, ?array &$taskResult = null) : bool
    {
        if (! Lib::type()->string_not_empty($taskIdString, $taskId)) {
            throw new LogicException(
                [ 'The `taskId` should be non-empty string', $taskId ]
            );
        }

        return $this->taskFetchResult($taskIdString, true, $taskResult);
    }

    protected function taskFetchResult(
        string $taskId, bool $delete,
        ?array &$taskResult = null
    ) : bool
    {
        $taskResult = null;

        $taskResultFile = "{$this->taskResultDirRealpath}/{$taskId}.result";

        $statusGet = false;
        if (is_file($taskResultFile) && (filesize($taskResultFile) > 0)) {
            $serialized = file_get_contents($taskResultFile);

            if (false !== $serialized) {
                $statusGet = true;

                if ($delete) {
                    unlink($taskResultFile);
                }

                $taskResult = unserialize($serialized);
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


    protected function processTask(array $task, ?array &$taskResult = null) : bool
    {
        return $this->processTaskUsingCurl($task, $taskResult);
    }

    protected function processTaskUsingCurl(array $task, ?array &$taskResult = null) : bool
    {
        $taskResult = null;

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

        $taskResult = [
            'headers_sent' => $headersSent,
            //
            'http_code'    => $httpCode,
            'headers'      => $headers,
            'content'      => $content,
        ];

        return true;
    }


    public function daemonAddToPool(int $timeoutMs, ?float $nowMicrotime = null) : bool
    {
        $theType = Lib::type();

        if (! $theType->int_positive($timeoutMsInt, $timeoutMs)) {
            throw new LogicException(
                [ 'The `timeoutMs` should be positive integer', $timeoutMs ]
            );
        }

        if (! is_null($nowMicrotimeFloat = $nowMicrotime)) {
            if (! $theType->float_non_negative($nowMicrotimeFloat, $nowMicrotime)) {
                throw new LogicException(
                    [ 'The `nowMicrotime` should be non-negative float', $nowMicrotime ]
                );
            }
        }

        $pid = getmypid();

        $status = $this->workerAddToPool($pid, $timeoutMsInt, $nowMicrotimeFloat);

        return $status;
    }

    public function daemonRemoveFromPool() : bool
    {
        $pid = getmypid();

        $statusRemove = $this->workerRemoveFromPool($pid);

        return $statusRemove;
    }


    protected function workerAddToPool(
        int $pid, int $timeoutMs,
        ?float $nowMicrotime = null
    ) : bool
    {
        $theFs = Lib::fs();

        $fhPool = $theFs->fopen_lock($this->poolFile, 'c+', LOCK_EX, 1000);
        if (false === $fhPool) {
            throw new FilesystemException(
                [ 'Unable to ' . __METHOD__ . ' due to locking file with LOCK_EX is failed', $this->poolFile ]
            );
        }

        $pidString = ltrim($pid, '0');
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

        $timeoutMicrotimeFloat = $nowMicrotimeFloat + ($timeoutMs / 1000);

        $pidNewString = str_pad($pid, 10, '0', STR_PAD_LEFT);
        $timeoutMicrotimeNewString = sprintf('%.6f', $timeoutMicrotimeFloat);

        $lines[] = "{$pidNewString}|{$timeoutMicrotimeNewString}";

        rewind($fhPool);
        ftruncate($fhPool, 0);

        fwrite($fhPool, implode("\n", $lines));

        $theFs->fclose_unlock($fhPool);

        return true;
    }

    protected function workerRemoveFromPool(int $pid) : bool
    {
        $theFs = Lib::fs();

        $fhPool = $theFs->fopen_lock_tmpfile($this->poolFile, 'c+', LOCK_EX, 1000);
        if (false === $fhPool) {
            throw new FilesystemException(
                [ 'Unable to ' . __METHOD__ . ' due to locking file with LOCK_EX is failed', $this->poolFile ]
            );
        }

        $pidString = ltrim($pid, '0');
        $nowMicrotimeFloat = microtime(true);

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

        fwrite($fhPool, implode("\n", $lines));

        $theFs->fclose_unlock($fhPool);

        return true;
    }


    public function daemonIsAwake(?int &$pidFirst = null) : bool
    {
        $pidFirst = null;

        $theFs = Lib::fs();

        $poolFile = $this->poolFile;

        if (! is_file($poolFile)) {
            return false;
        }

        $fhPool = $theFs->fopen_lock($poolFile, 'r', LOCK_SH, 100);
        if (false === $fhPool) {
            throw new FilesystemException(
                [ 'Unable to ' . __METHOD__ . ' due to locking file with LOCK_SH is failed', $poolFile ]
            );
        }

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

        $theFs->fclose_unlock($fhPool);

        if (null !== $pidFirstLine) {
            $pidFirst = (int) ltrim($pidFirstLine, '0');

            return true;
        }

        return false;
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

        $pm->spawn(
            $result,
            $cmd, $this->binDirRealpath
        );
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

        $timeoutMicrotime = $isNullTimeout ? null : ($nowMicrotime + ($timeoutMs / 1000));
        $timeoutReportMicrotime = 0.0;

        do {
            $nowMicrotime = microtime(true);

            if ($nowMicrotime > $timeoutReportMicrotime) {
                $this->workerAddToPool($pid, $timeoutReportMs, $nowMicrotime);

                $timeoutReportMicrotime = $nowMicrotime + ($timeoutReportMs / 1000);
            }

            $task = [];
            $taskResult = [];

            $statusPop = $this->popTask($lockWaitTimeoutMs, $task);
            $statusProcess = $statusPop && $this->processTask($task, $taskResult);
            $statusSave = $statusProcess && $this->taskSaveResult($task, $taskResult);
            $status = $statusSave;

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
