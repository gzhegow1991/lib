<?php

require_once getenv('COMPOSER_HOME') . '/vendor/autoload.php';
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

        $file = $current->getFile() ?? '{file}';
        $line = $current->getLine() ?? '{line}';
        echo "{$file} : {$line}" . PHP_EOL;

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
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug_value($value);
    }

    echo implode(' | ', $lines) . PHP_EOL;
}

function _debug(...$values) : void
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug_type_id($value);
    }

    echo implode(' | ', $lines) . PHP_EOL;
}

function _assert_output(
    \Closure $fn, string $expect = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::assert_resource(STDOUT);
    \Gzhegow\Lib\Lib::assert_output($trace, $fn, $expect);
}


// >>> ЗАПУСКАЕМ!

// >>> TEST
// > тесты DebugTrait
$fn = function () {
    _dump('[ TEST 1 ]');

    echo \Gzhegow\Lib\Lib::debug_value(null) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value(false) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value(1) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value(1.1) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value('string') . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value([]) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value((object) []) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_value(STDOUT) . PHP_EOL;

    echo PHP_EOL;

    $stdClass = (object) [];
    echo \Gzhegow\Lib\Lib::debug_value(
            [
                [ 1, 'apple', $stdClass ],
                [ 2, 'apples', $stdClass ],
                [ 1.5, 'apples', $stdClass ],
            ]
        ) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug_array(
            [
                [ 1, 'apple', $stdClass ],
                [ 2, 'apples', $stdClass ],
                [ 1.5, 'apples', $stdClass ],
            ]
        ) . PHP_EOL;

    echo PHP_EOL;

    echo \Gzhegow\Lib\Lib::debug_value_multiline(
            [
                [ 1, 'apple', $stdClass ],
                [ 2, 'apples', $stdClass ],
                [ 1.5, 'apples', $stdClass ],
            ]
        ) . PHP_EOL;

    echo \Gzhegow\Lib\Lib::debug_array_multiline(
            [
                [ 1, 'apple', $stdClass ],
                [ 2, 'apples', $stdClass ],
                [ 1.5, 'apples', $stdClass ],
            ]
        ) . PHP_EOL;

    echo '';
};
_assert_output($fn, <<<HEREDOC
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
[ "{ array(3) }", "{ array(3) }", "{ array(3) }" ]
[ [ 1, "apple", "{ object # stdClass }" ], [ 2, "apples", "{ object # stdClass }" ], [ 1.5, "apples", "{ object # stdClass }" ] ]
""
[
  "{ array(3) }",
  "{ array(3) }",
  "{ array(3) }"
]
[
  [
    1,
    "apple",
    "{ object # stdClass }"
  ],
  [
    2,
    "apples",
    "{ object # stdClass }"
  ],
  [
    1.5,
    "apples",
    "{ object # stdClass }"
  ]
]
""
HEREDOC
);

// >>> TEST
// > тесты StrTrait
$fn = function () {
    _dump('[ TEST 2 ]');

    _dump(\Gzhegow\Lib\Lib::str_lines("hello\nworld"));

    _dump(\Gzhegow\Lib\Lib::str_eol('hello' . PHP_EOL . 'world'));

    _dump(\Gzhegow\Lib\Lib::str_len('Привет'));
    _dump(\Gzhegow\Lib\Lib::str_len('Hello'));

    _dump(\Gzhegow\Lib\Lib::str_size('Привет'));
    _dump(\Gzhegow\Lib\Lib::str_size('Hello'));

    _dump(\Gzhegow\Lib\Lib::str_lower('ПРИВЕТ'));
    _dump(\Gzhegow\Lib\Lib::str_upper('привет'));

    _dump(\Gzhegow\Lib\Lib::str_lcfirst('ПРИВЕТ'));
    _dump(\Gzhegow\Lib\Lib::str_ucfirst('привет'));

    _dump(\Gzhegow\Lib\Lib::str_lcwords('ПРИВЕТ МИР'));
    _dump(\Gzhegow\Lib\Lib::str_ucwords('привет мир'));

    _dump(\Gzhegow\Lib\Lib::str_starts('привет', 'при'));
    _dump(\Gzhegow\Lib\Lib::str_ends('привет', 'вет'));
    _dump(\Gzhegow\Lib\Lib::str_contains('привет', 'ив'));

    _dump(\Gzhegow\Lib\Lib::str_lcrop('азаза_привет_азаза', 'аза'));
    _dump(\Gzhegow\Lib\Lib::str_rcrop('азаза_привет_азаза', 'аза'));
    _dump(\Gzhegow\Lib\Lib::str_crop('азаза_привет_азаза', 'аза'));

    _dump(\Gzhegow\Lib\Lib::str_unlcrop('"привет"', '"'));
    _dump(\Gzhegow\Lib\Lib::str_unrcrop('"привет"', '"'));
    _dump(\Gzhegow\Lib\Lib::str_uncrop('"привет"', '"'));

    _dump(\Gzhegow\Lib\Lib::str_replace_limit('за', '_', 'азазазазазаза', 3));

    _dump(\Gzhegow\Lib\Lib::str_camel('-hello-world-foo-bar'));
    _dump(\Gzhegow\Lib\Lib::str_camel('-helloWorldFooBar'));
    _dump(\Gzhegow\Lib\Lib::str_camel('-HelloWorldFooBar'));

    _dump(\Gzhegow\Lib\Lib::str_pascal('-hello-world-foo-bar'));
    _dump(\Gzhegow\Lib\Lib::str_pascal('-helloWorldFooBar'));
    _dump(\Gzhegow\Lib\Lib::str_pascal('-HelloWorldFooBar'));

    _dump(\Gzhegow\Lib\Lib::str_space('_Hello_WORLD_Foo_BAR'));
    _dump(\Gzhegow\Lib\Lib::str_snake('-Hello-WORLD-Foo-BAR'));
    _dump(\Gzhegow\Lib\Lib::str_kebab(' Hello WORLD Foo BAR'));

    _dump(\Gzhegow\Lib\Lib::str_space_lower('_Hello_WORLD_Foo_BAR'));
    _dump(\Gzhegow\Lib\Lib::str_snake_lower('-Hello-WORLD-Foo-BAR'));
    _dump(\Gzhegow\Lib\Lib::str_kebab_lower(' Hello WORLD Foo BAR'));

    _dump(\Gzhegow\Lib\Lib::str_space_upper('_Hello_WORLD_Foo_BAR'));
    _dump(\Gzhegow\Lib\Lib::str_snake_upper('-Hello-WORLD-Foo-BAR'));
    _dump(\Gzhegow\Lib\Lib::str_kebab_upper(' Hello WORLD Foo BAR'));

    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 2 ]"
[ "hello", "world" ]
"hello world"
6
5
12
5
"привет"
"ПРИВЕТ"
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
"helloWorldFooBar"
"helloWorldFooBar"
"helloWorldFooBar"
"HelloWorldFooBar"
"HelloWorldFooBar"
"HelloWorldFooBar"
" Hello WORLD Foo BAR"
"_Hello_WORLD_Foo_BAR"
"-Hello-WORLD-Foo-BAR"
" hello world foo bar"
"_hello_world_foo_bar"
"-hello-world-foo-bar"
" HELLO WORLD FOO BAR"
"_HELLO_WORLD_FOO_BAR"
"-HELLO-WORLD-FOO-BAR"
""
HEREDOC
);

// >>> TEST
// > тесты FormatTrait
$fn = function () {
    _dump('[ TEST 3 ]');


    $result = \Gzhegow\Lib\Lib::format_json_encode(
        $value = [ 'hello' ]
    );
    _dump($result);

    $result = \Gzhegow\Lib\Lib::format_json_encode(
        $value = NAN,
        $fallback = [ "NAN" ]
    );
    _dump($result);

    try {
        \Gzhegow\Lib\Lib::format_json_encode(
            $value = NAN
        );
    }
    catch ( \Throwable $e ) {
        _dump('[ CATCH ]');
    }


    $jsonc = "[1,/* 2 */3]";
    $result = \Gzhegow\Lib\Lib::format_jsonc_decode(
        $json = $jsonc,
        $associative = true
    );
    _dump($result);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 3 ]"
"[\"hello\"]"
"NAN"
"[ CATCH ]"
[ 1, 3 ]
""
HEREDOC
);

// >>> TEST
// > тесты FsTrait
$fn = function () {
    _dump('[ TEST 4 ]');


    $result = \Gzhegow\Lib\Lib::fs_file_put_contents(__DIR__ . '/var/1/1/1/1.txt', '123', [ 0775, true ]);
    _dump($result);

    $result = \Gzhegow\Lib\Lib::fs_file_put_contents(__DIR__ . '/var/1/1/1.txt', '123');
    _dump($result);

    $result = \Gzhegow\Lib\Lib::fs_file_put_contents(__DIR__ . '/var/1/1.txt', '123');
    _dump($result);


    $result = \Gzhegow\Lib\Lib::fs_file_get_contents(__DIR__ . '/var/1/1/1/1.txt');
    _dump($result);


    foreach (
        \Gzhegow\Lib\Lib::fs_dir_walk(__DIR__ . '/var/1')
        as $spl
    ) {
        $spl->isDir()
            ? \Gzhegow\Lib\Lib::fs_rmdir($spl->getRealPath())
            : \Gzhegow\Lib\Lib::fs_rm($spl->getRealPath());
    }
    \Gzhegow\Lib\Lib::fs_rmdir(__DIR__ . '/var/1');

    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 4 ]"
3
3
3
123
""
HEREDOC
);
