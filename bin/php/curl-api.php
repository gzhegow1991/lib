<?php

if (PHP_SAPI !== 'cli') {
    echo "This script should start in CLI mode\n";

    exit(1);
}


require_once __DIR__ . '/../../vendor/autoload.php';

\Gzhegow\Lib\Lib::entrypoint()
    ->setErrorReporting(E_ALL | E_DEPRECATED | E_USER_DEPRECATED)
    ->setMemoryLimit('32M')
    ->setTimeLimit(0)
    ->setPostMaxSize(0)
    ->setUploadMaxFilesize(0)
    ->setUmask(0002)
    //
    ->useErrorReporting()
    ->useMemoryLimit()
    ->useTimeLimit()
    ->usePostMaxSize()
    ->useUploadMaxFilesize()
    ->useUmask()
    ->useErrorHandler()
    ->useExceptionHandler()
;


$timeoutMs = $argv[ 1 ] ?? 10000;        // 10 sec
$lockWaitTimeoutMs = $argv[ 2 ] ?? 1000; // 1 sec

\Gzhegow\Lib\Lib::type($tt);
$tt->int_non_negative_or_minus_one($timeoutMsInt, $timeoutMs);
$tt->int_non_negative_or_minus_one($lockWaitTimeoutMsInt, $lockWaitTimeoutMs);

$fetchApi = \Gzhegow\Lib\Lib::async()->fetchApi();

$fetchApi->daemonMain(
    $timeoutMsInt,
    $lockWaitTimeoutMsInt
);
