<?php

require_once __DIR__ . '/../../vendor/autoload.php';

\Gzhegow\Lib\Lib::entrypoint()
    ->setAllRecommended()
    //
    ->setCustomDirRoot(__DIR__ . '/../..')
    //
    ->setPhpMaxExecutionTime(0)
    //
    ->useAll()
;

$theAsyncFetchApi = \Gzhegow\Lib\Lib::asyncFetchApi();
$theCli = \Gzhegow\Lib\Lib::cli();
$theType = \Gzhegow\Lib\Lib::type();

$timeoutMs = $argv[1] ?? 10000;        // > 10 sec
$lockWaitTimeoutMs = $argv[2] ?? 1000; // > 1 sec

echo "[ CURL-API ] Listening for tasks...\n";

$theAsyncFetchApi->daemonMain($timeoutMs, $lockWaitTimeoutMs);
