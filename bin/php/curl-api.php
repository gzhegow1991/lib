<?php

require_once __DIR__ . '/../../vendor/autoload.php';

define('__DIR_ROOT__', __DIR__ . '/../..');

\Gzhegow\Lib\Lib::entrypoint()
    ->setAllRecommended()
    //
    // // > debug
    // ->setPhpDisplayStartupErrors(0)
    // ->setPhpDisplayErrors(0)
    // ->setPhpLogErrors(1)
    // ->setPhpErrorLog(__DIR_ROOT__ . '/var/log/php_error.log')
    // // < debug
    //
    ->setCustomDirRoot(__DIR_ROOT__)
    //
    ->setPhpMaxExecutionTime(0)
    //
    ->useAll()
;

$theCli = \Gzhegow\Lib\Lib::cli();

$theAsyncFetchApi = \Gzhegow\Lib\Lib::asyncFetchApi();

$timeoutMs = $argv[1] ?? 10000;        // > 10 sec
$lockWaitTimeoutMs = $argv[2] ?? 1000; // > 1 sec

echo "[ CURL-API ] Listening for tasks...\n";

$theAsyncFetchApi->daemonMain($timeoutMs, $lockWaitTimeoutMs);
