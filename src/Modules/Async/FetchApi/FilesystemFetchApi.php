<?php

namespace Gzhegow\Lib\Modules\Async\FetchApi;

use Gzhegow\Lib\Lib;
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
        Lib::type($tt);

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

        $tt->dirpath_realpath($binDirRealpath, $config[ 'bin_dir' ] ?? $binDirDefault);
        $tt->dirpath_realpath($poolDirRealpath, $config[ 'pool_dir' ] ?? $poolDirDefault);
        $tt->dirpath_realpath($queueDirRealpath, $config[ 'queue_dir' ] ?? $queueDirDefault);
        $tt->dirpath_realpath($taskResultDirRealpath, $config[ 'task_result_dir' ] ?? $taskResultDirDefault);
        $tt->filename($binFilename, $config[ 'bin_filename' ] ?? 'curl-api.php');
        $tt->filename($poolFilename, $config[ 'pool_filename' ] ?? 'curl-api.pool');
        $tt->filename($queueFilename, $config[ 'queue_filename' ] ?? 'curl-api.queue');

        $binFilename = basename($binFilename, '.php') . '.php';
        $binFile = "{$binDirRealpath}/{$binFilename}";

        $queueFilename = basename($queueFilename, '.queue') . '.queue';
        $poolFile = "{$poolDirRealpath}/{$poolFilename}";

        $poolFilename = basename($poolFilename, '.pool') . '.pool';
        $queueFile = "{$queueDirRealpath}/{$queueFilename}";

        $tt->filepath_realpath($binFileRealpath, $binFile);
        $tt->filepath($poolFile, $poolFile, true);
        $tt->freepath($queueFile, $queueFile);

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
        Lib::type($tt);

        $tt->url($urlString, $url);
        $tt->list($curlOptionsList, $curlOptions);

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


    public function taskGetResult(string $taskId, ?array &$taskResult = null) : bool
    {
        Lib::type($tt);

        $tt->string_not_empty($taskIdString, $taskId);

        return $this->taskFetchResult($taskIdString, false, $taskResult);
    }

    public function taskFlushResult(string $taskId, ?array &$taskResult = null) : bool
    {
        Lib::type($tt);

        $tt->string_not_empty($taskIdString, $taskId);

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


    public function daemonAddToPool(int $timeoutMs, ?float $nowUtime = null) : bool
    {
        Lib::type($tt);

        $tt->int_positive($timeoutMsInt, $timeoutMs);

        is_null($nowUtimeFloat = $nowUtime)
        || $tt->float_non_negative($nowUtimeFloat, $nowUtime);

        $pid = getmypid();

        $status = $this->workerAddToPool($pid, $timeoutMsInt, $nowUtimeFloat);

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
        ?float $nowUtime = null
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
        $nowUtimeFloat = $nowUtime ?? microtime(true);

        $lines = [];
        while ( ! feof($fhPool) ) {
            $line = fgets($fhPool);
            $lineTrim = rtrim($line);
            if ('' === $lineTrim) {
                continue;
            }

            [ $pidLineString, $timeoutMtLineString ] = explode('|', $lineTrim);

            $timeoutMtLineFloat = (float) $timeoutMtLineString;
            if ($nowUtimeFloat > $timeoutMtLineFloat) {
                continue;
            }

            $pidLineString = ltrim($pidLineString, '0');
            if ($pidLineString === $pidString) {
                continue;
            }

            $lines[] = $lineTrim;
        }

        $timeoutUtimeFloat = $nowUtimeFloat + ($timeoutMs / 1000);

        $pidNewString = str_pad($pid, 10, '0', STR_PAD_LEFT);
        $timeoutMtNewString = sprintf('%.6f', $timeoutUtimeFloat);

        $lines[] = "{$pidNewString}|{$timeoutMtNewString}";

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
        $nowUtimeFloat = microtime(true);

        $lines = [];
        while ( ! feof($fhPool) ) {
            $line = fgets($fhPool);
            $lineTrim = rtrim($line);
            if ('' === $lineTrim) {
                continue;
            }

            [ $pidLineString, $timeoutMtLineString ] = explode('|', $lineTrim);

            $timeoutMtLineFloat = (float) $timeoutMtLineString;
            if ($nowUtimeFloat > $timeoutMtLineFloat) {
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

        $nowUtimeFloat = microtime(true);

        $pidFirstLine = null;
        while ( ! feof($fhPool) ) {
            $line = fgets($fhPool);
            $lineTrim = rtrim($line);
            if ('' === $lineTrim) {
                continue;
            }

            [ $pidLineString, $timeoutMtLineString ] = explode('|', $lineTrim);

            $timeoutMtFloat = (float) $timeoutMtLineString;

            if ($nowUtimeFloat > $timeoutMtFloat) {
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
        Lib::type($tt);

        $timeoutMs = $timeoutMs ?? 10000;
        $lockWaitTimeoutMs = $lockWaitTimeoutMs ?? 1000;

        $tt->int_non_negative_fallback($timeoutMsInt, $timeoutMs);
        $tt->int_non_negative_fallback($lockWaitTimeoutMsInt, $lockWaitTimeoutMs);

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

        $this->daemonSpawn($timeoutMsInt, $lockWaitTimeoutMsInt);
    }

    protected function daemonSpawn(
        int $timeoutMs,
        int $lockWaitTimeoutMs
    ) : void
    {
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
        Lib::type($tt, $tb);

        $pid = getmypid();

        $tt->int_non_negative_fallback($timeoutMsInt, $timeoutMs);
        $tt->int_non_negative_fallback($lockWaitTimeoutMsInt, $lockWaitTimeoutMs);

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

        $nowUtime = microtime(true);

        $timeoutReportMs = $timeoutMs ?? 10000;

        $timeoutMt = $isNullTimeout ? null : ($nowUtime + ($timeoutMs / 1000));
        $timeoutReportMt = 0;

        do {
            $nowUtime = microtime(true);

            if ($nowUtime > $timeoutReportMt) {
                $this->workerAddToPool($pid, $timeoutReportMs, $nowUtime);

                $timeoutReportMt = $nowUtime + ($timeoutReportMs / 1000);
            }

            $task = [];
            $taskResult = [];

            $statusPop = $this->popTask($lockWaitTimeoutMs, $task);
            $statusProcess = $statusPop && $this->processTask($task, $taskResult);
            $statusSave = $statusProcess && $this->taskSaveResult($task, $taskResult);
            $status = $statusSave;

            if (! $isNullTimeout) {
                if ($status) {
                    $timeoutMt = $nowUtime + ($timeoutMs / 1000);

                } elseif ($nowUtime > $timeoutMt) {
                    break;
                }
            }

            usleep(1000);
        } while ( true );
    }
}
