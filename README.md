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
function _debug(...$values) : string
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug()->type($value);
    }

    $ret = implode(' | ', $lines) . PHP_EOL;

    echo $ret;

    return $ret;
}

function _dump(...$values) : string
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug()->value($value);
    }

    $ret = implode(' | ', $lines) . PHP_EOL;

    echo $ret;

    return $ret;
}

function _dump_array($value, int $maxLevel = null, bool $multiline = false) : string
{
    $content = $multiline
        ? \Gzhegow\Lib\Lib::debug()->array_multiline($value, $maxLevel)
        : \Gzhegow\Lib\Lib::debug()->array($value, $maxLevel);

    $ret = $content . PHP_EOL;

    echo $ret;

    return $ret;
}

function _assert_output(
    \Closure $fn, string $expect = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::assert()->output($trace, $fn, $expect);
}

function _assert_microtime(
    \Closure $fn, float $expectMax = null, float $expectMin = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::assert()->microtime($trace, $fn, $expectMax, $expectMin);
}



// >>> TEST
// > тесты StrModule
$fn = function () {
    _dump('[ StrModule ]');
    echo PHP_EOL;

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
};
_assert_output($fn, '
"[ StrModule ]"

[ "hello", "world" ]
"hello\n world"
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

');


// >>> ЗАПУСКАЕМ!

// >>> TEST
// > тесты ArrayModule
$fn = function () {
    _dump('[ ArrModule ]');
    echo PHP_EOL;

    $array = new \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf('object');
    $array[] = 1;
    $array[] = 2;
    _dump($array, $array->getItems());

    // > be aware, `ArrayOf` WILL NOT check type when adding elements, so this check returns true
    // > if you use this feature carefully - you can avoid that check, and code becomes faster
    // > it will work like TypeScript idea - check should exists directly for your colleagues who will read the sources
    _dump($array->isOfType('object'));

    $array = new \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfType('object');
    $array[] = new \stdClass();
    $array[] = new \stdClass();
    $array[] = new ArrayObject();
    try {
        $array[] = 1;
    }
    catch ( \Throwable $e ) {
    }
    _dump('[ CATCH ] ' . $e->getMessage());
    _dump($array, $array->getItems(), $array->isOfType('object'));

    $array = new \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfClass('string', \stdClass::class);
    $array[] = new \stdClass();
    $array[] = new \stdClass();
    try {
        $array[] = new ArrayObject();
    }
    catch ( \Throwable $e ) {
    }
    _dump('[ CATCH ] ' . $e->getMessage());
    try {
        $array[] = new class extends \stdClass {
        };
    }
    catch ( \Throwable $e ) {
    }
    _dump('[ CATCH ] ' . $e->getMessage());
    try {
        $array[] = 1;
    }
    catch ( \Throwable $e ) {
    }
    _dump('[ CATCH ] ' . $e->getMessage());
    _dump($array, $array->getItems(), $array->isOfType('object'));
};
_assert_output($fn, '
"[ ArrModule ]"

{ object(countable(2) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf } | [ 1, 2 ]
TRUE
"[ CATCH ] The `value` should be of type: object / 1"
{ object(countable(3) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfType } | [ "{ object # stdClass }", "{ object # stdClass }", "{ object(countable(0) iterable) # ArrayObject }" ] | TRUE
"[ CATCH ] The `value` should be of class: stdClass / { object(countable(0) iterable) # ArrayObject }"
"[ CATCH ] The `value` should be of class: stdClass / { object # class@anonymous }"
"[ CATCH ] The `value` should be of class: stdClass / 1"
{ object(countable(2) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfClass } | [ "{ object # stdClass }", "{ object # stdClass }" ] | TRUE
');


// >>> TEST
// > тесты BcmathModule
$fn = function () {
    _dump('[ BcmathModule ]');
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 0);
    _dump($result); // "2"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 2);
    _dump($result); // "1.01"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 0);
    _dump($result); // "-1"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 2);
    _dump($result); // "-1"
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('1.005', 0);
    _dump($result); // "2"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('1.005', 2);
    _dump($result); // "1.01"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('-1.005', 0);
    _dump($result); // "-2"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('-1.005', 2);
    _dump($result); // "-1.01"
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 0);
    _dump($result); // "1"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 2);
    _dump($result); // "1"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 0);
    _dump($result); // "-2"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 2);
    _dump($result); // "-1.01"
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('1.005', 0);
    _dump($result); // "1"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('1.005', 2);
    _dump($result); // "1"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('-1.005', 0);
    _dump($result); // "-1"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('-1.005', 2);
    _dump($result); // "-1"
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.5', 0);
    _dump($result); // "2"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.05', 0);
    _dump($result); // "1"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.005', 0);
    _dump($result); // "1"
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.5', 2);
    _dump($result); // "1.5"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.05', 2);
    _dump($result); // "1.05"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.005', 2);
    _dump($result); // "1.01"
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.5', 0);
    _dump($result); // "-2"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.05', 0);
    _dump($result); // "-1"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.005', 0);
    _dump($result); // "-1"
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.5', 2);
    _dump($result); // "-1.5"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.05', 2);
    _dump($result); // "-1.05"
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.005', 2);
    _dump($result); // "-1.01"
    echo PHP_EOL;

    $gcd = \Gzhegow\Lib\Lib::bcmath()->bcgcd(8, 12);
    _dump($gcd); // "4"
    $gcd = \Gzhegow\Lib\Lib::bcmath()->bcgcd(7, 13);
    _dump($gcd); // "1"
    echo PHP_EOL;

    $lcm = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 6);
    _dump($lcm); // "24"
    $lcm = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 5);
    _dump($lcm); // "40"
    $lcm = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 10);
    _dump($lcm); // "40"
    echo PHP_EOL;
};
_assert_output($fn, '
"[ BcmathModule ]"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "2" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1.01" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1" }

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "2" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1.01" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-2" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1.01" }

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-2" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1.01" }

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1" }

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "2" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1" }

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1.5" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1.05" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1.01" }

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-2" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1" }

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1.5" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1.05" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "-1.01" }

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "4" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "1" }

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "24" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "40" }
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber # "40" }
');


// // >>> TEST
// // > тесты CryptModule
$fn = function () {
    _dump('[ CryptModule ]');
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, '01');
    _dump($enc); // "0"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01');
    _dump($dec); // "0"
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(3, '01');
    _dump($enc); // "11"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01');
    _dump($dec); // "3"
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, '01234567');
    _dump($enc); // "0"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01234567');
    _dump($dec); // "0"
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(15, '01234567');
    _dump($enc); // "17"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01234567');
    _dump($dec); // "15"
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, '0123456789ABCDEF');
    _dump($enc); // "0"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '0123456789ABCDEF');
    _dump($dec); // "0"
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(31, '0123456789ABCDEF');
    _dump($enc); // "1F"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '0123456789ABCDEF');
    _dump($dec); // "31"
    echo PHP_EOL;


    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($enc); // "A"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($dec); // "0"
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($enc); // "K"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($dec); // "10"
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(25, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($enc); // "Z"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($dec); // "25"
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(26, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($enc); // "BA"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($dec); // "26"
    echo PHP_EOL;

    try {
        $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    }
    catch ( \Throwable $e ) {
    }
    _dump('[ CATCH ] ' . $e->getMessage());
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($enc); // "J"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($dec); // "10"
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(26, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($enc); // "Z"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($dec); // "26"
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(27, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($enc); // "AA"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($dec); // "27"
    echo PHP_EOL;


    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase('2147483647', '0123456789', '0123456789');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase('9223372036854775807', '0123456789', '0123456789');
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase('2147483647', '0123456789abcdefghijklmnopqrstuvwxyz', '0123456789');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($enc, '0123456789', '0123456789abcdefghijklmnopqrstuvwxyz');
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase('9223372036854775807', '0123456789abcdefghijklmnopqrstuvwxyz', '0123456789');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($enc, '0123456789', '0123456789abcdefghijklmnopqrstuvwxyz');
    _dump($dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('1', '01');
    _dump($enc); // "1"
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('11', '0123');
    _dump($enc); // "3"
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('111', '01234567');
    _dump($enc); // "7"
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('1111', '0123456789ABCDEF');
    _dump($enc); // "F"
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('11111', '0123456789ABCDEFGHIJKLMNOPQRSTUV');
    _dump($enc); // "V"
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('111111', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/');
    _dump($enc); // "/"
    echo PHP_EOL;


    echo PHP_EOL;


    $binary = decbin(5678);
    _dump($binary); // "1011000101110"
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase($binary, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _dump($enc); // "uYB"
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2bin($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _dump($dec); // "001011000101110"
    $decimal = bindec($dec);
    _dump($decimal); // "5678"
    echo PHP_EOL;

    $binaries = \Gzhegow\Lib\Lib::crypt()->letters_to_binaries([ 'hello' ]);
    _dump_array($binaries); // [ "01101000", "01100101", "01101100", "01101100", "01101111" ]
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2base($binaries, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _dump($enc); // "aGVsbG8"
    $list = \Gzhegow\Lib\Lib::crypt()->base2bin($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _dump($list); // [ "01101000", "01100101", "01101100", "01101100", "01101111" ]
    echo PHP_EOL;

    $gen = (function () { yield 'hello'; })();
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_it($gen);
    $enc = '';
    foreach ( $gen as $letter ) {
        $enc .= $letter;
    }
    _dump($enc);
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_it($enc);
    $dec = iterator_to_array($gen);
    _dump($dec);
    echo PHP_EOL;

    $gen = (function () { yield 'hello'; })();
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_it($gen);
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_it($gen);
    $dec = iterator_to_array($gen);
    _dump($dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode("hello");
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    _dump($dec);
    echo PHP_EOL;

    $src = "\x00\x00\x01\x00\xFF";
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($src);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    _dump($dec === $src);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode("你好");
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode("hello");
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    _dump($dec);
    echo PHP_EOL;

    $src = "\x00\x00\x01\x00\xFF";
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($src);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    _dump($dec === $src);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode("你好");
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    _dump($dec);
    echo PHP_EOL;
};
_assert_output($fn, '
"[ CryptModule ]"

"0"
"0"

"11"
"3"

"0"
"0"

"17"
"15"

"0"
"0"

"1F"
"31"


"A"
"0"

"K"
"10"

"Z"
"25"

"BA"
"26"

"[ CATCH ] The `decInteger` should be greater than zero due to `oneBasedTo` is set to TRUE"

"J"
"10"

"Z"
"26"

"AA"
"27"


"2147483647"
"9223372036854775807"

"zik0zj"
"2147483647"

"1y2p0ij32e8e7"
"9223372036854775807"


"1"
"3"
"7"
"F"
"V"
"/"


"1011000101110"
"uYB"
"0001011000101110"
5678

[ "01101000", "01100101", "01101100", "01101100", "01101111" ]
"aGVsbG8"
[ "01101000", "01100101", "01101100", "01101100", "01101111" ]

"aGVsbG8="
[ "h", "e", "l", "l", "o" ]

[ "h", "e", "l", "l", "o" ]


"Cn8eVZg"
"hello"

"11LZL"
TRUE

"2xuZUfBKa"
"你好"

"7tQLFHz"
"hello"

"00H79"
TRUE

"19PqtKE1t"
"你好"
');


// >>> TEST
// > тесты DebugModule
$fn = function () {
    _dump('[ DebugModule ]');
    echo PHP_EOL;

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
    );
    echo PHP_EOL;
    echo \Gzhegow\Lib\Lib::debug()->array(
        [
            [ 1, 'apple', $stdClass ],
            [ 2, 'apples', $stdClass ],
            [ 1.5, 'apples', $stdClass ],
        ], 2
    );
    echo PHP_EOL;

    echo PHP_EOL;

    echo \Gzhegow\Lib\Lib::debug()->value_multiline(
        [
            [ 1, 'apple', $stdClass ],
            [ 2, 'apples', $stdClass ],
            [ 1.5, 'apples', $stdClass ],
        ]
    );
    echo PHP_EOL;

    echo \Gzhegow\Lib\Lib::debug()->array_multiline(
        [
            [ 1, 'apple', $stdClass ],
            [ 2, 'apples', $stdClass ],
            [ 1.5, 'apples', $stdClass ],
        ], 2
    );
    echo PHP_EOL;
};
_assert_output($fn, '
"[ DebugModule ]"

NULL
FALSE
1
1.1
"string"
[  ]
{ object # stdClass }
{ resource(stream) }

[ "{ array(3) }", "{ array(3) }", "{ array(3) }" ]
[ [ 1, "apple", "{ object # stdClass }" ], [ 2, "apples", "{ object # stdClass }" ], [ 1.5, "apples", "{ object # stdClass }" ] ]

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
');


// >>> TEST
// > тесты FormatModule
$fn = function () {
    _dump('[ FormatModule ]');
    echo PHP_EOL;


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
    }
    _dump('[ CATCH ] ' . $e->getMessage());


    $jsonc = "[1,/* 2 */3]";
    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode(
        $json = $jsonc,
        $associative = true
    );
    _dump($result);
};
_assert_output($fn, '
"[ FormatModule ]"

"[\"hello\"]"
"NAN"
"[ CATCH ] Unable to `json_encode`"
[ 1, 3 ]
');


// >>> TEST
// > тесты FsModule
$fn = function () {
    _dump('[ FsModule ]');
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(__DIR__ . '/var/1/1/1/1.txt', '123', [ 0775, true ]);
    _dump($result);

    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(__DIR__ . '/var/1/1/1.txt', '123');
    _dump($result);

    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(__DIR__ . '/var/1/1.txt', '123');
    _dump($result);


    $result = \Gzhegow\Lib\Lib::fs()->file_get_contents(__DIR__ . '/var/1/1/1/1.txt');
    _dump($result);


    foreach (
        \Gzhegow\Lib\Lib::fs()->dir_walk_it(__DIR__ . '/var/1')
        as $spl
    ) {
        $spl->isDir()
            ? \Gzhegow\Lib\Lib::fs()->rmdir($spl->getRealPath())
            : \Gzhegow\Lib\Lib::fs()->rm($spl->getRealPath());
    }
    \Gzhegow\Lib\Lib::fs()->rmdir(__DIR__ . '/var/1');
};
_assert_output($fn, '
"[ FsModule ]"

3
3
3
"123"
');


// >>> TEST
// > тесты PhpModule
$fn = function () {
    _dump('[ PhpModule ]');
    echo PHP_EOL;


    \Gzhegow\Lib\Lib::php()->errors_start($b);

    for ( $i = 0; $i < 3; $i++ ) {
        \Gzhegow\Lib\Lib::php()->error([ 'This is the error message' ]);
    }

    $errors = \Gzhegow\Lib\Lib::php()->errors_end($b);

    _dump_array($errors, 2, true);
};
_assert_output($fn, '
"[ PhpModule ]"

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
');


// >>> TEST
// > тесты RandomModule
$fn = function () {
    _dump('[ RandomModule ]');
    echo PHP_EOL;

    $rand = \Gzhegow\Lib\Lib::random()->random_bytes(16);
    _dump($len = strlen($rand), $len === 16);

    $rand = \Gzhegow\Lib\Lib::random()->random_hex(16);
    _dump($len = strlen($rand), $len === 32);

    $rand = \Gzhegow\Lib\Lib::random()->random_int(1, 100);
    _dump(1 <= $rand, $rand <= 100);

    $rand = \Gzhegow\Lib\Lib::random()->random_string(16);
    _dump(mb_strlen($rand) === 16);

    $rand = \Gzhegow\Lib\Lib::random()->random_base64_urlsafe(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            rtrim($rand, '='),
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_64_RFC4648_URLSAFE
        )
    ;
    _dump(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base64(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            rtrim($rand, '='),
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_64_RFC4648
        )
    ;
    _dump(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base62(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            $rand,
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_62
        )
    ;
    _dump(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base58(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            $rand,
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_58
        )
    ;
    _dump(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base36(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            $rand,
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_36
        )
    ;
    _dump(null !== $test);
};
_assert_output($fn, '
"[ RandomModule ]"

16 | TRUE
32 | TRUE
TRUE | TRUE
TRUE
TRUE
TRUE
TRUE
TRUE
TRUE
');


// >>> TEST
// > тесты StrModule
$fn = function () {
    _dump('[ StrModule ]');
    echo PHP_EOL;

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
};
_assert_output($fn, '
"[ StrModule ]"

[ "hello", "world" ]
"hello\n world"
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
');


// >>> TEST
// > тесты AbstractContext
$fn = function () {
    _dump('[ AbstractContext ]');
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
};
_assert_output($fn, '
"[ AbstractContext ]"

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
');
```