<?php

namespace Gzhegow\Lib\Modules\Async\FetchApi;

interface FetchApiInterface
{
    public function pushTask(string $url, array $curlOptions = [], ?int $lockWaitTimeoutMs = null, ?string &$taskId = null) : bool;

    public function popTask(?int $blockTimeoutMs = null, ?array &$task = null) : bool;


    public function taskGetResult(string $taskId, ?array &$taskResult = null) : bool;

    public function taskFlushResult(string $taskId, ?array &$taskResult = null) : bool;

    public function clearTaskResults() : void;


    public function daemonAddToPool(int $timeoutMs, ?float $nowMicrotime = null) : bool;

    public function daemonRemoveFromPool() : bool;


    public function daemonIsAwake(?int &$pidFirst = null) : bool;

    public function daemonWakeup(?int $timeoutMs = null, ?int $lockWaitTimeoutMs = null) : void;

    public function daemonSpawn(?int $timeoutMs = null, ?int $lockWaitTimeoutMs = null) : void;


    public function daemonMain(int $timeoutMs, int $lockWaitTimeoutMs) : void;
}
