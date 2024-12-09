<?php

require_once __DIR__ . '/vendor/autoload.php';


// > настраиваем PHP
ini_set('memory_limit', '32M');


// > настраиваем обработку ошибок
error_reporting(E_ALL);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (error_reporting() & $errno) {
        throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
    }
});
set_exception_handler(function (\Throwable $e) {
    // require_once getenv('COMPOSER_HOME') . '/vendor/autoload.php';
    // dd($e);

    $current = $e;
    do {
        echo "\n";

        echo \Gzhegow\Lib\Lib::debug_var_dump($current) . PHP_EOL;
        echo $current->getMessage() . PHP_EOL;

        foreach ( $e->getTrace() as $traceItem ) {
            echo "{$traceItem['file']} : {$traceItem['line']}" . PHP_EOL;
        }

        echo PHP_EOL;
    } while ( $current = $current->getPrevious() );

    die();
});


// > добавляем несколько функция для тестирования
function _dump(...$values) : void
{
    echo implode(' | ', array_map([ \Gzhegow\Lib\Lib::class, 'debug_value' ], $values));
}

function _dump_ln(...$values) : void
{
    echo implode(' | ', array_map([ \Gzhegow\Lib\Lib::class, 'debug_value' ], $values)) . PHP_EOL;
}

function _assert_call(\Closure $fn, array $expectResult = [], string $expectOutput = null) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    $expect = (object) [];

    if (count($expectResult)) {
        $expect->result = $expectResult[ 0 ];
    }

    if (null !== $expectOutput) {
        $expect->output = $expectOutput;
    }

    $status = \Gzhegow\Lib\Lib::assert_call($trace, $fn, $expect, $error, STDOUT);

    if (! $status) {
        throw new \Gzhegow\Lib\Exception\LogicException();
    }
}


// >>> ЗАПУСКАЕМ!

// >>> TEST
// > это пример теста
$fn = function () {
    _dump_ln('[ TEST 1 ]');
    _dump('');
};
_assert_call($fn, [], <<<HEREDOC
"[ TEST 1 ]"
""
HEREDOC
);
