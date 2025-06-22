<?php

if (PHP_SAPI !== 'cli') {
    echo "This script should start in CLI mode\n";

    exit(1);
}


require_once __DIR__ . '/../../vendor/autoload.php';

\Gzhegow\Lib\Lib::entrypoint()
    ->setDirRoot(__DIR__ . '/../..')
    //
    ->setMaxExecutionTime(0)
    ->setPostMaxSize(0)
    ->setUploadMaxFilesize(0)
    //
    ->useAll()
;


$timeoutMs = $argv[ 1 ] ?? 10000;        // 10 sec
$lockWaitTimeoutMs = $argv[ 2 ] ?? 1000; // 1 sec

$theTypeThrow = \Gzhegow\Lib\Lib::typeThrow();
$theTypeThrow->int_non_negative_or_minus_one($timeoutMsInt, $timeoutMs);
$theTypeThrow->int_non_negative_or_minus_one($lockWaitTimeoutMsInt, $lockWaitTimeoutMs);

$fetchApi = \Gzhegow\Lib\Lib::async()->fetchApi();

$fetchApi->daemonMain(
    $timeoutMsInt,
    $lockWaitTimeoutMsInt
);
