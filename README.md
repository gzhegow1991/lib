# Lib

Библиотека вспомогательных функций для использования в проектах и остальных пакетах

## Установка

```
composer require gzhegow/lib;
```

## Пример

```php
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
            $file = $traceItem[ 'file' ] ?? '{file}';
            $line = $traceItem[ 'line' ] ?? '{line}';

            echo "{$file} : {$line}" . PHP_EOL;
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

    echo \Gzhegow\Lib\Lib::debug_value(null) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value(false) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value(1) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value(1.1) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value('string') . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value([]) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value((object) []) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value(STDOUT) . PHP_EOL;

    _dump_ln('');

    echo \Gzhegow\Lib\Lib::debug_value(
            [
                [ '1', 'apple' ],
                [ '2', 'apples' ],
                [ '1.5', 'apples' ],
            ]
        ) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_array(
            [
                [ '1', 'apple' ],
                [ '2', 'apples' ],
                [ '1.5', 'apples' ],
            ]
        ) . PHP_EOL;

    _dump_ln('');

    echo \Gzhegow\Lib\Lib::debug_value_multiline(
            [
                [ '1', 'apple' ],
                [ '2', 'apples' ],
                [ '1.5', 'apples' ],
            ]
        ) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_array_multiline(
            [
                [ '1', 'apple' ],
                [ '2', 'apples' ],
                [ '1.5', 'apples' ],
            ]) . PHP_EOL;

    _dump('');
};
_assert_call($fn, [], <<<HEREDOC
"[ TEST 1 ]"
NULL
FALSE
1
1.1
"string"
[  ]
{ object # stdClass }
{ resource(stream) }
""
[ "{ array(2) }", "{ array(2) }", "{ array(2) }" ]
[ [ "1", "apple" ], [ "2", "apples" ], [ "1.5", "apples" ] ]
""
[
  "{ array(2) }",
  "{ array(2) }",
  "{ array(2) }"
]
[
  [
    "1",
    "apple"
  ],
  [
    "2",
    "apples"
  ],
  [
    "1.5",
    "apples"
  ]
]
""
HEREDOC
);

// >>> TEST
// > это пример теста
$fn = function () {
    _dump_ln('[ TEST 2 ]');

    _dump_ln(\Gzhegow\Lib\Lib::str_lines("hello\nworld"));
    _dump_ln(\Gzhegow\Lib\Lib::str_eol('hello' . PHP_EOL . 'world'));
    _dump_ln(\Gzhegow\Lib\Lib::str_len('Привет'));
    _dump_ln(\Gzhegow\Lib\Lib::str_len('Hello'));
    _dump_ln(\Gzhegow\Lib\Lib::str_size('Привет'));
    _dump_ln(\Gzhegow\Lib\Lib::str_size('Hello'));
    _dump_ln(\Gzhegow\Lib\Lib::str_lcfirst('ПРИВЕТ'));
    _dump_ln(\Gzhegow\Lib\Lib::str_ucfirst('привет'));
    _dump_ln(\Gzhegow\Lib\Lib::str_lcwords('ПРИВЕТ МИР'));
    _dump_ln(\Gzhegow\Lib\Lib::str_ucwords('привет мир'));
    _dump_ln(\Gzhegow\Lib\Lib::str_starts('привет', 'при'));
    _dump_ln(\Gzhegow\Lib\Lib::str_ends('привет', 'вет'));
    _dump_ln(\Gzhegow\Lib\Lib::str_contains('привет', 'ив'));
    _dump_ln(\Gzhegow\Lib\Lib::str_lcrop('азаза_привет_азаза', 'аза'));
    _dump_ln(\Gzhegow\Lib\Lib::str_rcrop('азаза_привет_азаза', 'аза'));
    _dump_ln(\Gzhegow\Lib\Lib::str_crop('азаза_привет_азаза', 'аза'));
    _dump_ln(\Gzhegow\Lib\Lib::str_unlcrop('"привет"', '"'));
    _dump_ln(\Gzhegow\Lib\Lib::str_unrcrop('"привет"', '"'));
    _dump_ln(\Gzhegow\Lib\Lib::str_uncrop('"привет"', '"'));
    _dump_ln(\Gzhegow\Lib\Lib::str_replace_limit('за', '_', 'азазазазазаза', 3));
    _dump_ln(\Gzhegow\Lib\Lib::str_space('hello-world-foo-bar'));
    _dump_ln(\Gzhegow\Lib\Lib::str_snake('hello-world-foo-bar'));
    _dump_ln(\Gzhegow\Lib\Lib::str_camel('hello-world-foo-bar'));
    _dump_ln(\Gzhegow\Lib\Lib::str_pascal('hello-world-foo-bar'));

    _dump('');
};
_assert_call($fn, [], <<<HEREDOC
"[ TEST 2 ]"
[ "hello", "world" ]
"hello world"
6
5
12
5
"пРИВЕТ"
"Привет"
"пРИВЕТ мИР"
"Привет Мир"
"вет"
"при"
[ "пр", "ет" ]
"за_привет_азаза"
"азаза_привет_аз"
"за_привет_аз"
"\"привет\""
"\"привет\""
"\"привет\""
"а___зазаза"
"hello world foo bar"
"hello_world_foo_bar"
"helloWorldFooBar"
"HelloWorldFooBar"
""
HEREDOC
);
```