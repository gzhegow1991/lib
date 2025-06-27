<?php

namespace Gzhegow\Lib\Modules\Async\FetchApi;

interface FetchApiInterface
{
    public function pushTask(&$refTaskId, string $url, array $curlOptions = [], ?int $lockWaitTimeoutMs = null) : bool;

    public function popTask(&$refTask, ?int $blockTimeoutMs = null) : bool;


    public function taskClearResults() : void;


    public function taskGetResult(&$refTaskResult, string $taskId) : bool;

    public function taskFlushResult(&$refTaskResult, string $taskId) : bool;


    public function daemonAddToPool(int $daemonTimeoutMs, ?float $nowMicrotime = null) : bool;

    public function daemonRemoveFromPool(?float $nowMicrotime = null) : bool;


    public function daemonIsAwake(?int &$refPidFirst = null) : bool;

    public function daemonWakeup(?int $timeoutMs = null, ?int $lockWaitTimeoutMs = null) : void;

    public function daemonSpawn(?int $timeoutMs = null, ?int $lockWaitTimeoutMs = null) : void;


    public function daemonMain(int $timeoutMs, int $lockWaitTimeoutMs) : void;
}
