<?php

namespace Gzhegow\Lib\Modules\Async\FetchApi;

interface FetchApiInterface
{
    public function pushTask(&$taskId, string $url, array $curlOptions = [], ?int $lockWaitTimeoutMs = null) : bool;

    public function popTask(&$task, ?int $blockTimeoutMs = null) : bool;


    public function taskGetResult(&$taskResult, string $taskId) : bool;

    public function taskFlushResult(&$taskResult, string $taskId) : bool;

    public function clearTaskResults() : void;


    public function daemonAddToPool(int $daemonTimeoutMs, ?float $nowMicrotime = null) : bool;

    public function daemonRemoveFromPool(?float $nowMicrotime = null) : bool;


    public function daemonIsAwake(?int &$pidFirst = null) : bool;

    public function daemonWakeup(?int $timeoutMs = null, ?int $lockWaitTimeoutMs = null) : void;

    public function daemonSpawn(?int $timeoutMs = null, ?int $lockWaitTimeoutMs = null) : void;


    public function daemonMain(int $timeoutMs, int $lockWaitTimeoutMs) : void;
}
