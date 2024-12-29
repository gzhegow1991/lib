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
(new \Gzhegow\Lib\Exception\ErrorHandler())
    ->useErrorReporting()
    ->useErrorHandler()
    ->useExceptionHandler()
;


// > добавляем несколько функция для тестирования
function _debug(...$values) : void
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug()->type_id($value);
    }

    echo implode(' | ', $lines) . PHP_EOL;
}

function _dump(...$values) : void
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug()->value($value);
    }

    echo implode(' | ', $lines) . PHP_EOL;
}

function _dump_array($value, int $maxLevel = null, bool $multiline = false) : void
{
    $content = $multiline
        ? \Gzhegow\Lib\Lib::debug()->array_multiline($value, $maxLevel)
        : \Gzhegow\Lib\Lib::debug()->array($value, $maxLevel);

    echo $content . PHP_EOL;
}

function _assert_output(
    \Closure $fn, string $expect = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::assert()->resource_static(STDOUT);
    \Gzhegow\Lib\Lib::assert()->output($trace, $fn, $expect);
}


// // >>> TEST
// // > тесты AbstractContext
// $fn = function () {
//     _dump('[ TEST 0 ]');
//
//     echo '';
// };
// _assert_output($fn, <<<HEREDOC
// "[ TEST 0 ]"
// ""
// HEREDOC
// );
// dd();


// >>> ЗАПУСКАЕМ!

// >>> TEST
// > тесты DebugModule
$fn = function () {
    _dump('[ TEST 1 ]');

    echo \Gzhegow\Lib\Lib::debug()->value(null) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug()->value(false) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug()->value(1) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug()->value(1.1) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug()->value('string') . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug()->value([]) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug()->value((object) []) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug()->value(STDOUT) . PHP_EOL;

    echo PHP_EOL;

    $stdClass = (object) [];
    echo \Gzhegow\Lib\Lib::debug()->value(
            [
                [ 1, 'apple', $stdClass ],
                [ 2, 'apples', $stdClass ],
                [ 1.5, 'apples', $stdClass ],
            ]
        ) . PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug()->array(
            [
                [ 1, 'apple', $stdClass ],
                [ 2, 'apples', $stdClass ],
                [ 1.5, 'apples', $stdClass ],
            ], 2
        ) . PHP_EOL;

    echo PHP_EOL;

    echo \Gzhegow\Lib\Lib::debug()->value_multiline(
            [
                [ 1, 'apple', $stdClass ],
                [ 2, 'apples', $stdClass ],
                [ 1.5, 'apples', $stdClass ],
            ]
        ) . PHP_EOL;

    echo \Gzhegow\Lib\Lib::debug()->array_multiline(
            [
                [ 1, 'apple', $stdClass ],
                [ 2, 'apples', $stdClass ],
                [ 1.5, 'apples', $stdClass ],
            ], 2
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
// > тесты StrModule
$fn = function () {
    _dump('[ TEST 2 ]');

    _dump(\Gzhegow\Lib\Lib::str()->lines("hello\nworld"));

    _dump(\Gzhegow\Lib\Lib::str()->eol('hello' . PHP_EOL . 'world'));

    _dump(\Gzhegow\Lib\Lib::str()->strlen('Привет'));
    _dump(\Gzhegow\Lib\Lib::str()->strlen('Hello'));

    _dump(\Gzhegow\Lib\Lib::str()->strsize('Привет'));
    _dump(\Gzhegow\Lib\Lib::str()->strsize('Hello'));

    _dump(\Gzhegow\Lib\Lib::str()->lower('ПРИВЕТ'));
    _dump(\Gzhegow\Lib\Lib::str()->upper('привет'));

    _dump(\Gzhegow\Lib\Lib::str()->lcfirst('ПРИВЕТ'));
    _dump(\Gzhegow\Lib\Lib::str()->ucfirst('привет'));

    _dump(\Gzhegow\Lib\Lib::str()->lcwords('ПРИВЕТ МИР'));
    _dump(\Gzhegow\Lib\Lib::str()->ucwords('привет мир'));

    _dump(\Gzhegow\Lib\Lib::str()->starts('привет', 'при'));
    _dump(\Gzhegow\Lib\Lib::str()->ends('привет', 'вет'));
    _dump(\Gzhegow\Lib\Lib::str()->contains('привет', 'ив'));

    _dump(\Gzhegow\Lib\Lib::str()->lcrop('азаза_привет_азаза', 'аза'));
    _dump(\Gzhegow\Lib\Lib::str()->rcrop('азаза_привет_азаза', 'аза'));
    _dump(\Gzhegow\Lib\Lib::str()->crop('азаза_привет_азаза', 'аза'));

    _dump(\Gzhegow\Lib\Lib::str()->unlcrop('"привет"', '"'));
    _dump(\Gzhegow\Lib\Lib::str()->unrcrop('"привет"', '"'));
    _dump(\Gzhegow\Lib\Lib::str()->uncrop('"привет"', '"'));

    _dump(\Gzhegow\Lib\Lib::str()->replace_limit('за', '_', 'азазазазазаза', 3));

    _dump(\Gzhegow\Lib\Lib::str()->camel('-hello-world-foo-bar'));
    _dump(\Gzhegow\Lib\Lib::str()->camel('-helloWorldFooBar'));
    _dump(\Gzhegow\Lib\Lib::str()->camel('-HelloWorldFooBar'));

    _dump(\Gzhegow\Lib\Lib::str()->pascal('-hello-world-foo-bar'));
    _dump(\Gzhegow\Lib\Lib::str()->pascal('-helloWorldFooBar'));
    _dump(\Gzhegow\Lib\Lib::str()->pascal('-HelloWorldFooBar'));

    _dump(\Gzhegow\Lib\Lib::str()->space('_Hello_WORLD_Foo_BAR'));
    _dump(\Gzhegow\Lib\Lib::str()->snake('-Hello-WORLD-Foo-BAR'));
    _dump(\Gzhegow\Lib\Lib::str()->kebab(' Hello WORLD Foo BAR'));

    _dump(\Gzhegow\Lib\Lib::str()->space_lower('_Hello_WORLD_Foo_BAR'));
    _dump(\Gzhegow\Lib\Lib::str()->snake_lower('-Hello-WORLD-Foo-BAR'));
    _dump(\Gzhegow\Lib\Lib::str()->kebab_lower(' Hello WORLD Foo BAR'));

    _dump(\Gzhegow\Lib\Lib::str()->space_upper('_Hello_WORLD_Foo_BAR'));
    _dump(\Gzhegow\Lib\Lib::str()->snake_upper('-Hello-WORLD-Foo-BAR'));
    _dump(\Gzhegow\Lib\Lib::str()->kebab_upper(' Hello WORLD Foo BAR'));

    _dump(\Gzhegow\Lib\Lib::str()->inflector()->singularize('users'));
    _dump(\Gzhegow\Lib\Lib::str()->inflector()->pluralize('user'));

    _dump(\Gzhegow\Lib\Lib::str()->slugger()->slug('привет мир'));

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
[ "user" ]
[ "users" ]
"privet-mir"
""
HEREDOC
);


// >>> TEST
// > тесты FormatModule
$fn = function () {
    _dump('[ TEST 3 ]');


    $result = \Gzhegow\Lib\Lib::json()->json_encode(
        $value = [ 'hello' ]
    );
    _dump($result);

    $result = \Gzhegow\Lib\Lib::json()->json_encode(
        $value = NAN,
        $fallback = [ "NAN" ]
    );
    _dump($result);

    try {
        \Gzhegow\Lib\Lib::json()->json_encode(
            $value = NAN
        );
    }
    catch ( \Throwable $e ) {
        _dump('[ CATCH ]');
    }


    $jsonc = "[1,/* 2 */3]";
    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode(
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
// > тесты FsModule
$fn = function () {
    _dump('[ TEST 4 ]');


    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(__DIR__ . '/var/1/1/1/1.txt', '123', [ 0775, true ]);
    _dump($result);

    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(__DIR__ . '/var/1/1/1.txt', '123');
    _dump($result);

    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(__DIR__ . '/var/1/1.txt', '123');
    _dump($result);


    $result = \Gzhegow\Lib\Lib::fs()->file_get_contents(__DIR__ . '/var/1/1/1/1.txt');
    _dump($result);


    foreach (
        \Gzhegow\Lib\Lib::fs()->dir_walk(__DIR__ . '/var/1')
        as $spl
    ) {
        $spl->isDir()
            ? \Gzhegow\Lib\Lib::fs()->rmdir($spl->getRealPath())
            : \Gzhegow\Lib\Lib::fs()->rm($spl->getRealPath());
    }
    \Gzhegow\Lib\Lib::fs()->rmdir(__DIR__ . '/var/1');

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


// >>> TEST
// > тесты PhpModule
$fn = function () {
    _dump('[ TEST 5 ]');


    \Gzhegow\Lib\Lib::php()->errors_start($b);

    for ( $i = 0; $i < 3; $i++ ) {
        \Gzhegow\Lib\Lib::php()->error([ 'This is the error message' ]);
    }

    $errors = \Gzhegow\Lib\Lib::php()->errors_end($b);

    _dump_array($errors, 2, true);


    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 5 ]"
[
  [
    "This is the error message"
  ],
  [
    "This is the error message"
  ],
  [
    "This is the error message"
  ]
]
""
HEREDOC
);


// >>> TEST
// > тесты ArrayModule
$fn = function () {
    _dump('[ TEST 6 ]');


    $array = new \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf('object');
    $array[] = 1;
    $array[] = 2;
    _dump($array, $array->getItems(), $array->isOfType('object'));

    $array = new \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfType('object');
    $array[] = new \stdClass();
    $array[] = new \stdClass();
    $array[] = new ArrayObject();
    try {
        $array[] = 1;
    }
    catch ( \Throwable $e ) {
        _dump('[ CATCH ]');
    }
    _dump($array, $array->getItems(), $array->isOfType('object'));

    $array = new \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfClass('string', \stdClass::class);
    $array[] = new \stdClass();
    $array[] = new \stdClass();
    try {
        $array[] = new ArrayObject();
    }
    catch ( \Throwable $e ) {
        _dump('[ CATCH ]');
    }
    try {
        $array[] = new class extends \stdClass {
        };
    }
    catch ( \Throwable $e ) {
        _dump('[ CATCH ]');
    }
    try {
        $array[] = 1;
    }
    catch ( \Throwable $e ) {
        _dump('[ CATCH ]');
    }
    _dump($array, $array->getItems(), $array->isOfType('object'));

    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 6 ]"
{ object(iterable countable(2)) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf } | [ 1, 2 ] | TRUE
"[ CATCH ]"
{ object(iterable countable(3)) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfType } | [ "{ object # stdClass }", "{ object # stdClass }", "{ object(iterable countable(0)) # ArrayObject }" ] | TRUE
"[ CATCH ]"
"[ CATCH ]"
"[ CATCH ]"
{ object(iterable countable(2)) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfClass } | [ "{ object # stdClass }", "{ object # stdClass }" ] | TRUE
""
HEREDOC
);


// >>> TEST
// > тесты BcMathModule
$fn = function () {
    _dump('[ TEST 7 ]');

    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 0); // 2
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 2); // 1.01
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 0); // -1
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 2); // -1
    _dump($result->getValue());
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('1.005', 0); // 2
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('1.005', 2); // 1.01
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('-1.005', 0); // -2
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('-1.005', 2); // -1.01
    _dump($result->getValue());
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 0); // 1
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 2); // 1
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 0); // -2
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 2); // -1.01
    _dump($result->getValue());
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('1.005', 0); // 1
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('1.005', 2); // 1
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('-1.005', 0); // -1
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('-1.005', 2); // -1
    _dump($result->getValue());
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.5', 0);
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.05', 0);
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.005', 0);
    _dump($result->getValue());
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.5', 2);
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.05', 2);
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.005', 2);
    _dump($result->getValue());
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.5', 0);
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.05', 0);
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.005', 0);
    _dump($result->getValue());
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.5', 2);
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.05', 2);
    _dump($result->getValue());
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.005', 2);
    _dump($result->getValue());
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->base_convert('2147483647', '0123456789', '0123456789');
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->base_convert('9223372036854775807', '0123456789', '0123456789');
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->base_convert('2147483647', '0123456789abcdefghijklmnopqrstuvwxyz', '0123456789');
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->base_convert('9223372036854775807', '0123456789abcdefghijklmnopqrstuvwxyz', '0123456789');
    _dump($result);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 7 ]"
2
1.01
-1
-1

2
1.01
-2
-1.01

1
1
-2
-1.01

1
1
-1
-1

2
1
1

1.5
1.05
1.01

-2
-1
-1

-1.5
-1.05
-1.01

2147483647
9223372036854775807
"zik0zj"
"1y2p0ij32e8e7"
""
HEREDOC
);


// >>> TEST
// > тесты AbstractContext
$fn = function () {
    _dump('[ TEST 8 ]');
    echo PHP_EOL;

    $instances = [];
    $instances[ \Gzhegow\Lib\Context\Traits\WritableTrait::class ] = new class extends \Gzhegow\Lib\Context\AbstractContext {
        use \Gzhegow\Lib\Context\Traits\WritableTrait;


        protected $foo = 1;
    };
    $instances[ \Gzhegow\Lib\Context\Traits\EditonlyTrait::class ] = new class extends \Gzhegow\Lib\Context\AbstractContext {
        use \Gzhegow\Lib\Context\Traits\EditonlyTrait;


        protected $foo = 1;
    };
    $instances[ \Gzhegow\Lib\Context\Traits\ReadonlyTrait::class ] = new class extends \Gzhegow\Lib\Context\AbstractContext {
        use \Gzhegow\Lib\Context\Traits\ReadonlyTrait;


        protected $foo = 1;
    };
    $instances[ \Gzhegow\Lib\Context\Traits\AnyPropertiesTrait::class ] = new class extends \Gzhegow\Lib\Context\AbstractContext {
        use \Gzhegow\Lib\Context\Traits\AnyPropertiesTrait;


        protected $foo = 1;
    };
    $instances[ \Gzhegow\Lib\Context\Traits\PublicPropertiesTrait::class ] = new class extends \Gzhegow\Lib\Context\AbstractContext {
        use \Gzhegow\Lib\Context\Traits\PublicPropertiesTrait;


        protected $foo = 1;
    };

    foreach ( $instances as $key => $instance ) {
        _dump($key);

        _dump('<-foo');
        try {
            _dump('foo', $instance->foo);
        }
        catch ( \Throwable $e ) {
            _dump("[ CATCH ] {$e->getMessage()}");
        }

        _dump('->foo');
        try {
            $instance->foo = 11;
        }
        catch ( \Throwable $e ) {
            _dump("[ CATCH ] {$e->getMessage()}");
        }

        _dump('<-foo');
        try {
            _dump('foo', $instance->foo);
        }
        catch ( \Throwable $e ) {
            _dump("[ CATCH ] {$e->getMessage()}");
        }


        _dump('<-bar');
        try {
            _dump('bar', $instance->bar);
        }
        catch ( \Throwable $e ) {
            _dump("[ CATCH ] {$e->getMessage()}");
        }

        _dump('->bar');
        try {
            $instance->bar = 22;
        }
        catch ( \Throwable $e ) {
            _dump("[ CATCH ] {$e->getMessage()}");
        }

        _dump('<-bar');
        try {
            _dump('bar', $instance->bar);
        }
        catch ( \Throwable $e ) {
            _dump("[ CATCH ] {$e->getMessage()}");
        }

        echo PHP_EOL;
    }

    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 8 ]"

"Gzhegow\Lib\Context\Traits\WritableTrait"
"<-foo"
"foo" | 1
"->foo"
"<-foo"
"foo" | 11
"<-bar"
"[ CATCH ] Missing property: bar"
"->bar"
"<-bar"
"bar" | 22

"Gzhegow\Lib\Context\Traits\EditonlyTrait"
"<-foo"
"foo" | 1
"->foo"
"<-foo"
"foo" | 11
"<-bar"
"[ CATCH ] Missing property: bar"
"->bar"
"[ CATCH ] Unable to ->set() due to failed filter: editonlyTrait_set"
"<-bar"
"[ CATCH ] Missing property: bar"

"Gzhegow\Lib\Context\Traits\ReadonlyTrait"
"<-foo"
"foo" | 1
"->foo"
"[ CATCH ] Unable to ->set() due to failed filter: readonlyTrait_set"
"<-foo"
"foo" | 1
"<-bar"
"[ CATCH ] Missing property: bar"
"->bar"
"<-bar"
"bar" | 22

"Gzhegow\Lib\Context\Traits\AnyPropertiesTrait"
"<-foo"
"foo" | 1
"->foo"
"<-foo"
"foo" | 11
"<-bar"
"bar" | NULL
"->bar"
"<-bar"
"bar" | 22

"Gzhegow\Lib\Context\Traits\PublicPropertiesTrait"
"<-foo"
"[ CATCH ] Unable to ->get() due to failed filter: publicPropertiesTrait_get"
"->foo"
"<-foo"
"[ CATCH ] Unable to ->get() due to failed filter: publicPropertiesTrait_get"
"<-bar"
"[ CATCH ] Unable to ->get() due to failed filter: publicPropertiesTrait_get"
"->bar"
"<-bar"
"bar" | 22

""
HEREDOC
);
```