<?php

if (PHP_SAPI !== 'cli') {
    echo "This script should run in CLI mode\n";

    exit(1);
}


require_once __DIR__ . '/../../vendor/autoload.php';

\Gzhegow\Lib\Lib::entrypoint()
    ->setDirRoot(__DIR__ . '/../..')
    //
    ->setMaxExecutionTime(0)
    //
    ->setPostMaxSize(0)
    ->setUploadMaxFilesize(0)
    //
    ->useAll()
;

\Gzhegow\Lib\Lib::require_composer_global();


$timeoutMs = $argv[ 1 ] ?? 10000;        // 10 sec
$lockWaitTimeoutMs = $argv[ 2 ] ?? 1000; // 1 sec

$theType = \Gzhegow\Lib\Lib::type();

$timeoutMsInt = $theType->int_non_negative_or_minus_one($timeoutMs)->orThrow();
$lockWaitTimeoutMsInt = $theType->int_non_negative_or_minus_one($lockWaitTimeoutMs)->orThrow();

\Gzhegow\Lib\Lib::asyncFetchApi()
    ->daemonMain(
        $timeoutMsInt,
        $lockWaitTimeoutMsInt
    )
;
