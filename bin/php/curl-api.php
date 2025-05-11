<?php

if (PHP_SAPI !== 'cli') {
    echo "This script should start in CLI mode\n";

    exit(1);
}


require_once __DIR__ . '/../../vendor/autoload.php';

\Gzhegow\Lib\Lib::entrypoint()
    ->setErrorReporting(E_ALL | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED)
    ->setTimeLimit(0)
    ->setMemoryLimit('32M')
    //
    ->useErrorReporting()
    ->useTimeLimit()
    ->useMemoryLimit()
    ->useErrorHandler()
    ->useExceptionHandler()
;


$timeoutMs = $argv[ 1 ] ?? 10000;
$lockWaitTimeoutMs = $argv[ 2 ] ?? 1000;


$fetchApi = \Gzhegow\Lib\Lib::async()->fetchApi();

$fetchApi->daemonMain(
    $timeoutMs,
    $lockWaitTimeoutMs
);
