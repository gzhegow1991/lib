# Lib

Библиотека вспомогательных функций для использования в проектах и остальных пакетах

## Установка

```
composer require gzhegow/lib;
```

## Тесты

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';


// > настраиваем PHP
ini_set('memory_limit', '32M');


// > настраиваем обработку ошибок
($ex = new \Gzhegow\Lib\Exception\ErrorHandler())
    ->useErrorReporting()
    ->useErrorHandler()
    ->useExceptionHandler()
;


// > добавляем несколько функция для тестирования
function _value($value) : string
{
    return \Gzhegow\Lib\Lib::debug()->value($value, []);
}

function _values($separator = null, ...$values) : string
{
    return \Gzhegow\Lib\Lib::debug()->values($separator, [], ...$values);
}

function _array($value, int $maxLevel = null, array $options = []) : string
{
    return \Gzhegow\Lib\Lib::debug()->value_array($value, $maxLevel, $options);
}

function _array_multiline($value, int $maxLevel = null, array $options = []) : string
{
    return \Gzhegow\Lib\Lib::debug()->value_array_multiline($value, $maxLevel, $options);
}

function _print(...$values) : void
{
    echo _values(' | ', ...$values) . PHP_EOL;
}

function _print_array($value, int $maxLevel = null, array $options = [])
{
    echo _array($value, $maxLevel, $options) . PHP_EOL;
}

function _print_array_multiline($value, int $maxLevel = null, array $options = [])
{
    echo _array_multiline($value, $maxLevel, $options) . PHP_EOL;
}

function _assert_stdout(
    \Closure $fn, array $fnArgs = [],
    string $expectedStdout = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::test()->assertStdout(
        $trace,
        $fn, $fnArgs,
        $expectedStdout
    );
}


// >>> TEST
// > тесты Exceptions
$fn = function () use ($ex) {
    _print('[ Exceptions ]');
    echo PHP_EOL;

    $eeee1 = new \Exception('eeee1', 0);
    $eeee2 = new \Exception('eeee2', 0);

    $previousList = [ $eeee1, $eeee2 ];
    $eee0 = new \Gzhegow\Lib\Exception\LogicException('eee', 0, ...$previousList);

    $ee1 = new \Exception('ee1', 0, $previous = $eee0);
    $ee2 = new \Exception('ee2', 0, $previous = $eee0);

    $previousList = [ $ee1, $ee2 ];
    $e0 = new \Gzhegow\Lib\Exception\RuntimeException('e', 0, ...$previousList);

    $iit = $e0->getIterator();

    // > or:
    // $it = new \Gzhegow\Lib\Exception\ExceptionIterator([ $e0 ]);
    // $iit = new \RecursiveIteratorIterator($it);

    foreach ( $iit as $i => $track ) {
        foreach ( $track as $ii => $e ) {
            $phpClass = get_class($e);

            echo "[ {$ii} ] {$e->getMessage()}" . PHP_EOL;
            echo "{ object # {$phpClass} }" . PHP_EOL;
            echo PHP_EOL;
        }

        echo PHP_EOL;
    }


    $e = new \Gzhegow\Lib\Exception\RuntimeException();
    $eTrace = $e->getTraceOverride(__DIR__);
    foreach ( $eTrace as $i => $frame ) {
        unset($eTrace[ $i ][ 'line' ]);
    }

    _print($e->getFileOverride(__DIR__));

    echo PHP_EOL;

    _print_array_multiline($eTrace, 2);
};
_assert_stdout($fn, [], '
"[ Exceptions ]"

[ 0 ] e
{ object # Gzhegow\Lib\Exception\RuntimeException }

[ 0.0 ] ee1
{ object # Exception }

[ 0.0.0 ] eee
{ object # Gzhegow\Lib\Exception\LogicException }

[ 0.0.0.0 ] eeee1
{ object # Exception }


[ 0 ] e
{ object # Gzhegow\Lib\Exception\RuntimeException }

[ 0.0 ] ee1
{ object # Exception }

[ 0.0.0 ] eee
{ object # Gzhegow\Lib\Exception\LogicException }

[ 0.0.0.1 ] eeee2
{ object # Exception }


[ 0 ] e
{ object # Gzhegow\Lib\Exception\RuntimeException }

[ 0.1 ] ee2
{ object # Exception }

[ 0.1.0 ] eee
{ object # Gzhegow\Lib\Exception\LogicException }

[ 0.1.0.0 ] eeee1
{ object # Exception }


[ 0 ] e
{ object # Gzhegow\Lib\Exception\RuntimeException }

[ 0.1 ] ee2
{ object # Exception }

[ 0.1.0 ] eee
{ object # Gzhegow\Lib\Exception\LogicException }

[ 0.1.0.1 ] eeee2
{ object # Exception }


"test.php"

```
[
  [
    "function" => "{closure}",
    "args" => "{ array(0) }"
  ],
  [
    "file" => "src/Modules/TestModule.php",
    "function" => "call_user_func_array",
    "args" => "{ array(2) }"
  ],
  [
    "file" => "test.php",
    "function" => "assertStdout",
    "class" => "Gzhegow\Lib\Modules\TestModule",
    "type" => "->",
    "args" => "{ array(4) }"
  ],
  [
    "file" => "test.php",
    "function" => "_assert_stdout",
    "args" => "{ array(3) }"
  ]
]
```

');


// >>> TEST
// > тесты ArrayModule
$fn = function () {
    _print('[ ArrModule ]');
    echo PHP_EOL;

    $notAnObject = 1;
    $object = new stdClass();
    $anotherObject = new ArrayObject();
    $anonymousObject = new class extends \stdClass {
    };


    $array = new \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf('object');
    $array[] = $notAnObject;
    _print($array);
    _print($array->getItems());

    // > be aware, `ArrayOf` WILL NOT check type when adding elements, so this check returns true
    // > if you use this feature carefully - you can avoid that check, and code becomes faster
    // > it will work like PHPDoc idea - check should remember your colleagues who will read the sources without actually check
    _print($array->isOfType('object'));
    echo PHP_EOL;


    $array = new \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfType(
        $types = [ 'mixed' => 'object' ]
    );
    $array[] = $object;
    $array[] = $anotherObject;
    try {
        $array[] = $notAnObject;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());
    _print($array);
    _print($array->getItems());
    _print($array->isOfType('object'));
    echo PHP_EOL;


    $array = new \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfClass(
        $keyType = 'string',
        $objectClass = \stdClass::class
    );
    $array[] = $object;
    try {
        $array[] = $anotherObject;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());
    try {
        $array[] = $anonymousObject;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());
    try {
        $array[] = $notAnObject;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());
    _print($array);
    _print($array->getItems());
    _print($array->isOfType('object'));
};
_assert_stdout($fn, [], '
"[ ArrModule ]"

{ object(countable(1) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf }
[ 1 ]
TRUE

"[ CATCH ] The `value` should be of type: object / 1"
{ object(countable(2) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfType }
[ "{ object # stdClass }", "{ object(countable(0) iterable) # ArrayObject }" ]
TRUE

"[ CATCH ] The `value` should be of class: stdClass / { object(countable(0) iterable) # ArrayObject }"
"[ CATCH ] The `value` should be of class: stdClass / { object # class@anonymous }"
"[ CATCH ] The `value` should be of class: stdClass / 1"
{ object(countable(1) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfClass }
[ "{ object # stdClass }" ]
TRUE
');


// >>> TEST
// > тесты AssertModule
$fn = function () {
    _print('[ AssertModule ]');
    echo PHP_EOL;

    try {
        $var = \Gzhegow\Lib\Lib::assert()
            ->string_not_empty('')
            ->orThrow('The value should be non-empty string')
        ;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());

    try {
        $var = \Gzhegow\Lib\Lib::assert()
            ->numeric_positive('-1')
            ->orThrow('The value should be positive numeric')
        ;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());
};
_assert_stdout($fn, [], '
"[ AssertModule ]"

"[ CATCH ] The value should be non-empty string"
"[ CATCH ] The value should be positive numeric"
');


// >>> TEST
// > тесты BcmathModule
$fn = function () {
    _print('[ BcmathModule ]');
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 2);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 2);
    _print($result, (string) $result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('1.005', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('1.005', 2);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('-1.005', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('-1.005', 2);
    _print($result, (string) $result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 2);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 2);
    _print($result, (string) $result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('1.005', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('1.005', 2);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('-1.005', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('-1.005', 2);
    _print($result, (string) $result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.5', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.05', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.005', 0);
    _print($result, (string) $result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.5', 2);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.05', 2);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.005', 2);
    _print($result, (string) $result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.5', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.05', 0);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.005', 0);
    _print($result, (string) $result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.5', 2);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.05', 2);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.005', 2);
    _print($result, (string) $result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcgcd(8, 12);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcgcd(7, 13);
    _print($result, (string) $result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 6);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 5);
    _print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 10);
    _print($result, (string) $result);
    echo PHP_EOL;
};
_assert_stdout($fn, [], '
"[ BcmathModule ]"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "2"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1.01"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "2"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1.01"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-2"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1.01"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-2"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1.01"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "2"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1.5"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1.05"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1.01"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-2"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1.5"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1.05"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "-1.01"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "4"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "1"

{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "24"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "40"
{ object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber } | "40"
');


// >>> TEST
// > тесты CryptModule
$fn = function () {
    _print('[ CryptModule ]');
    echo PHP_EOL;


    $algos = [
        'fnv1a32',
        'crc32',
        'md5',
        'sha1',
        'sha256',
    ];
    foreach ( $algos as $algo ) {
        $hash = \Gzhegow\Lib\Lib::crypt()->hash($algo, 'hello world!', $binary = false);
        _print($hash);
        $result = \Gzhegow\Lib\Lib::crypt()->hash_equals($hash, $algo, 'hello world!', $binary = false);
        _print($result);
        echo PHP_EOL;

        $hash = \Gzhegow\Lib\Lib::crypt()->hash($algo, 'hello world!', $binary = true);
        $hash01 = '';
        foreach ( str_split(bin2hex($hash), 2) as $hex ) {
            $hash01 .= str_pad(
                base_convert($hex, 16, 2),
                8,
                '0',
                STR_PAD_LEFT
            );
        }
        _print($hash01);
        $result = \Gzhegow\Lib\Lib::crypt()->hash_equals($hash, $algo, 'hello world!', $binary = true);
        _print($result);
        echo PHP_EOL;
    }


    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, '01');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01');
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(3, '01');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01');
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, '01234567');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01234567');
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(15, '01234567');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01234567');
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, '0123456789ABCDEF');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '0123456789ABCDEF');
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(31, '0123456789ABCDEF');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '0123456789ABCDEF');
    _print($dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(25, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(26, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _print($dec);
    echo PHP_EOL;

    try {
        $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(26, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(27, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _print($dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase('2147483647', '0123456789', '0123456789');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase('9223372036854775807', '0123456789', '0123456789');
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase('2147483647', '0123456789abcdefghijklmnopqrstuvwxyz', '0123456789');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($enc, '0123456789', '0123456789abcdefghijklmnopqrstuvwxyz');
    _print($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase('9223372036854775807', '0123456789abcdefghijklmnopqrstuvwxyz', '0123456789');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($enc, '0123456789', '0123456789abcdefghijklmnopqrstuvwxyz');
    _print($dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('1', '01');
    _print($enc);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('11', '0123');
    _print($enc);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('111', '01234567');
    _print($enc);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('1111', '0123456789ABCDEF');
    _print($enc);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('11111', '0123456789ABCDEFGHIJKLMNOPQRSTUV');
    _print($enc);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('111111', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/');
    _print($enc);
    echo PHP_EOL;


    echo PHP_EOL;


    $strings = [ '你' ];
    _print_array($strings);
    $binaries = \Gzhegow\Lib\Lib::crypt()->text2bin($strings);
    _print_array($binaries);
    $letters = \Gzhegow\Lib\Lib::crypt()->bin2text($binaries);
    _print_array($letters);
    echo PHP_EOL;

    $strings = [ '你好' ];
    _print_array($strings);
    $binaries = \Gzhegow\Lib\Lib::crypt()->text2bin($strings);
    _print_array($binaries);
    $letters = \Gzhegow\Lib\Lib::crypt()->bin2text($binaries);
    _print_array($letters);
    echo PHP_EOL;


    echo PHP_EOL;


    $number = 5678;
    _print($number);
    $binary = decbin(5678);
    _print($binary);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase($binary, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2bin($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _print($dec);
    $number = bindec($dec);
    _print($number);
    echo PHP_EOL;

    $strings = [ 'hello' ];
    _print_array($strings);
    $binaries = \Gzhegow\Lib\Lib::crypt()->text2bin($strings);
    _print_array($binaries);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2base($binaries, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base2bin($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _print_array($dec);
    $text = implode('', array_map('chr', array_map('bindec', $dec)));
    _print($text);
    echo PHP_EOL;


    echo PHP_EOL;


    $src = 'HELLO';
    _print('input: ' . $src);
    $gen = (function () use ($src) { yield $src; })();
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_it($gen);
    $enc = '';
    foreach ( $gen as $letter ) {
        $enc .= $letter;
    }
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_it($enc);
    $dec = '';
    foreach ( $gen as $letter ) {
        $dec .= $letter;
    }
    _print('result: ' . $dec);
    echo PHP_EOL;

    $src = 'HELLO';
    _print('input: ' . $src);
    $gen = (function () use ($src) { yield $src; })();
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_it($gen);
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_it($gen);
    $dec = '';
    foreach ( $gen as $letter ) {
        $dec .= $letter;
    }
    _print('result: ' . $dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $string = "hello";
    _print($string);
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($string);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    _print($dec);
    echo PHP_EOL;

    $src = "\x00\x00\x01\x00\xFF";
    $srcDump = '';
    $len = mb_strlen($src);
    for ( $i = 0; $i < $len; $i++ ) {
        $chr = mb_substr($src, $i, 1);
        $chr = ord($chr);
        $chr = dechex($chr);
        $chr = str_pad($chr, 2, '0', STR_PAD_LEFT);
        $chr = '\x' . $chr;

        $srcDump .= $chr;
    }
    _print('b`' . $srcDump . '`');
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($src);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    $decDump = '';
    $len = mb_strlen($src);
    for ( $i = 0; $i < $len; $i++ ) {
        $chr = mb_substr($src, $i, 1);
        $chr = ord($chr);
        $chr = dechex($chr);
        $chr = str_pad($chr, 2, '0', STR_PAD_LEFT);
        $chr = '\x' . $chr;

        $decDump .= $chr;
    }
    _print('b`' . $decDump . '`');
    echo PHP_EOL;

    $string = "你好";
    _print($string);
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($string);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    _print($dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $string = "hello";
    _print($string);
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($string);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    _print($dec);
    echo PHP_EOL;

    $src = "\x00\x00\x01\x00\xFF";
    $srcDump = '';
    $len = mb_strlen($src);
    for ( $i = 0; $i < $len; $i++ ) {
        $chr = mb_substr($src, $i, 1);
        $chr = ord($chr);
        $chr = dechex($chr);
        $chr = str_pad($chr, 2, '0', STR_PAD_LEFT);
        $chr = '\x' . $chr;

        $srcDump .= $chr;
    }
    _print('b`' . $srcDump . '`');
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($src);
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    $decDump = '';
    $len = mb_strlen($src);
    for ( $i = 0; $i < $len; $i++ ) {
        $chr = mb_substr($src, $i, 1);
        $chr = ord($chr);
        $chr = dechex($chr);
        $chr = str_pad($chr, 2, '0', STR_PAD_LEFT);
        $chr = '\x' . $chr;

        $decDump .= $chr;
    }
    _print('b`' . $decDump . '`');
    echo PHP_EOL;

    $string = '你好';
    _print($string);
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode("你好");
    _print($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    _print($dec);
    echo PHP_EOL;
};
_assert_stdout($fn, [], '
"[ CryptModule ]"

"b034fff2"
TRUE

"10110000001101001111111111110010"
TRUE

"b79584fd"
TRUE

"10110111100101011000010011111101"
TRUE

"fc3ff98e8c6a0d3087d515c0473f8677"
TRUE

"11111100001111111111100110001110100011000110101000001101001100001000011111010101000101011100000001000111001111111000011001110111"
TRUE

"430ce34d020724ed75a196dfc2ad67c77772d169"
TRUE

"0100001100001100111000110100110100000010000001110010010011101101011101011010000110010110110111111100001010101101011001111100011101110111011100101101000101101001"
TRUE

"7509e5bda0c762d2bac7f90d758b5b2263fa01ccbc542ab5e3df163be08e6ca9"
TRUE

"0111010100001001111001011011110110100000110001110110001011010010101110101100011111111001000011010111010110001011010110110010001001100011111110100000000111001100101111000101010000101010101101011110001111011111000101100011101111100000100011100110110010101001"
TRUE


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


[ "你" ]
[ "111001001011110110100000" ]
[ "你" ]

[ "你好" ]
[ "111001001011110110100000", "111001011010010110111101" ]
[ "你", "好" ]


5678
"1011000101110"
"uYB"
"0001011000101110"
5678

[ "hello" ]
[ "01101000", "01100101", "01101100", "01101100", "01101111" ]
"aGVsbG8"
[ "01101000", "01100101", "01101100", "01101100", "01101111" ]
"hello"


"input: HELLO"
"result: HELLO"

"input: HELLO"
"result: HELLO"


"hello"
"Cn8eVZg"
"hello"

"b`\x00\x00\x01\x00\xff`"
"11LZL"
"b`\x00\x00\x01\x00\xff`"

"你好"
"2xuZUfBKa"
"你好"


"hello"
"7tQLFHz"
"hello"

"b`\x00\x00\x01\x00\xff`"
"00H79"
"b`\x00\x00\x01\x00\xff`"

"你好"
"19PqtKE1t"
"你好"
');


// >>> TEST
// > тесты DebugModule
$fn = function () {
    _print('[ DebugModule ]');
    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines, 1);
    echo PHP_EOL;


    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple2\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry2\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry2";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines, 1);
    echo PHP_EOL;


    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "fig\napple\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\nfig\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry\nfig";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines, 1);
    echo PHP_EOL;


    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "banana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines, 1);
    echo PHP_EOL;


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
    echo \Gzhegow\Lib\Lib::debug()->value_array(
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

    echo \Gzhegow\Lib\Lib::debug()->value_array_multiline(
        [
            [ 1, 'apple', $stdClass ],
            [ 2, 'apples', $stdClass ],
            [ 1.5, 'apples', $stdClass ],
        ], 2
    );
    echo PHP_EOL;


    function DebugModule_dump(...$vars)
    {
        $theDebug = \Gzhegow\Lib\Lib::debug();

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $theDebug->static_dumper_fn([ $theDebug, 'dumper_var_dump' ]);
        // $theDebug->dumper_fn_static([ $theDebug, 'dumper_var_dump_native' ]);
        // $theDebug->dumper_fn_static([ $theDebug, 'dumper_print_r' ]);
        // $theDebug->dumper_fn_static([ $theDebug, 'dumper_var_export' ]);
        // $theDebug->dumper_fn_static([ $theDebug, 'dumper_var_export_native' ]);
        // $theDebug->dumper_fn_static([ $theDebug, 'dumper_symfony' ]);

        $theDebug->static_dump_fn([ $theDebug, 'dump_echo' ]);
        $options = [];

        // $theDebug->dump_fn_static([ $theDebug, 'dump_stdout' ]);
        // $options = [ STDOUT ];

        // $theDebug->dump_fn_static([ $theDebug, 'dump_stdout_html' ]);
        // $options = [ STDOUT ];

        // $theDebug->dump_fn_static([ $theDebug, 'dump_browser_console' ]);
        // $options = [];

        // $theDebug->dump_fn_static([ $theDebug, 'dump_pdo' ]);
        // $pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', '');
        // $table = 'dump';
        // $column = 'var';
        // $options = [ $pdo, $table, $column ];

        $theDebug->dump($trace, $options, ...$vars) . PHP_EOL;
    }

    // ob_start();
    // _dump('hello', 'world');
    // echo ob_get_clean();
    // // { string(53) # "D:\OpenServer\.org\@gzhegow\_1_\_1_lib\test.php: 1108" }
    // // { string(5) # "hello" }
    // // { string(5) # "world" }
};
_assert_stdout($fn, [], '
"[ DebugModule ]"

FALSE
```
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]
```


TRUE
```
[
  "[ 1 ] --- > apple2",
  "[ 1 ] +++ > apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]
```

TRUE
```
[
  "apple",
  "banana",
  "[ 3 ] --- > cherry2",
  "[ 3 ] +++ > cherry",
  "damson",
  "elderberry"
]
```

TRUE
```
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "[ 5 ] --- > elderberry2",
  "[ 5 ] +++ > elderberry"
]
```


TRUE
```
[
  "[ 1 ] --- > fig",
  "apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]
```

TRUE
```
[
  "apple",
  "banana",
  "cherry",
  "[ 4 ] --- > fig",
  "damson",
  "elderberry"
]
```

TRUE
```
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "elderberry",
  "[ 6 ] --- > fig"
]
```


TRUE
```
[
  "[ 1 ] +++ > apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]
```

TRUE
```
[
  "apple",
  "banana",
  "[ 3 ] +++ > cherry",
  "damson",
  "elderberry"
]
```

TRUE
```
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "[ 5 ] +++ > elderberry"
]
```


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

```
[
  "{ array(3) }",
  "{ array(3) }",
  "{ array(3) }"
]
```
```
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
```
');


// >>> TEST
// > тесты FormatModule
$fn = function () {
    _print('[ FormatModule ]');
    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::json()->json_encode(
        $value = [ 'hello' ]
    );
    _print($result);

    $result = \Gzhegow\Lib\Lib::json()->json_encode(
        $value = NAN,
        $fallback = [ "NAN" ]
    );
    _print($result);

    try {
        \Gzhegow\Lib\Lib::json()->json_encode(
            $value = NAN
        );
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());


    $jsonc = "[1,/* 2 */3]";
    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode(
        $json = $jsonc,
        $associative = true
    );
    _print($result);
};
_assert_stdout($fn, [], '
"[ FormatModule ]"

"[\"hello\"]"
"NAN"
"[ CATCH ] Unable to `json_encode`"
[ 1, 3 ]
');


// >>> TEST
// > тесты FsModule
$fn = function () {
    _print('[ FsModule ]');
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(__DIR__ . '/var/1/1/1/1.txt', '123', [ 0775, true ]);
    _print($result);

    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(__DIR__ . '/var/1/1/1.txt', '123');
    _print($result);

    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(__DIR__ . '/var/1/1.txt', '123');
    _print($result);


    $result = \Gzhegow\Lib\Lib::fs()->file_get_contents(__DIR__ . '/var/1/1/1/1.txt');
    _print($result);


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
_assert_stdout($fn, [], '
"[ FsModule ]"

3
3
3
"123"
');


// >>> TEST
// > тесты ParseModule
$fn = function () {
    _print('[ ParseModule ]');
    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::parse()->ctype_digit('123');
    _print($result);
    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::parse()->ctype_alpha('abcABC');
    _print($result);

    $result = \Gzhegow\Lib\Lib::parse()->ctype_alpha('abcABC', false);
    _print($result);
    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::parse()->ctype_alnum('123abcABC');
    _print($result);

    $result = \Gzhegow\Lib\Lib::parse()->ctype_alnum('123abcABC', false);
    _print($result);
};
_assert_stdout($fn, [], '
"[ ParseModule ]"

"123"

"abcABC"
NULL

"123abcABC"
NULL
');


// >>> TEST
// > тесты PhpModule
$fn = function () {
    _print('[ PhpModule ]');
    echo PHP_EOL;


    \Gzhegow\Lib\Lib::php()->errors_start($b);

    for ( $i = 0; $i < 3; $i++ ) {
        \Gzhegow\Lib\Lib::php()->error([ 'This is the error message' ]);
    }

    $errors = \Gzhegow\Lib\Lib::php()->errors_end($b);

    _print_array_multiline($errors, 2);


    echo PHP_EOL;


    class PhpModuleDummy
    {
        public           $publicProperty;
        protected        $protectedProperty;
        private          $privateProperty;
        public static    $publicStaticProperty;
        protected static $protectedStaticProperty;
        private static   $privateStaticProperty;
    }

    class PhpModuleDummy1
    {
        public function publicMethod()
        {
            echo __METHOD__ . PHP_EOL;
        }

        protected function protectedMethod()
        {
            echo __METHOD__ . PHP_EOL;
        }

        private function privateMethod()
        {
            echo __METHOD__ . PHP_EOL;
        }


        public static function publicStaticMethod()
        {
            echo __METHOD__ . PHP_EOL;
        }

        protected static function protectedStaticMethod()
        {
            echo __METHOD__ . PHP_EOL;
        }

        private static function privateStaticMethod()
        {
            echo __METHOD__ . PHP_EOL;
        }
    }

    class PhpModuleDummy2
    {
        public function __call($name, $args)
        {
            echo __METHOD__ . PHP_EOL;
        }
    }

    class PhpModuleDummy3
    {
        public static function __callStatic($name, $args)
        {
            echo __METHOD__ . PHP_EOL;
        }
    }

    class PhpModuleDummy4
    {
        public function __invoke()
        {
            echo __METHOD__ . PHP_EOL;
        }
    }

    function PhpModule_dummy_function()
    {
        echo __FUNCTION__ . PHP_EOL;
    }


    $sources = [
        $classDummy = \PhpModuleDummy::class,
        $objectDummy = new \PhpModuleDummy(),
    ];
    $sourceProperties = [
        'publicProperty',
        'protectedProperty',
        'privateProperty',
        'publicStaticProperty',
        'protectedStaticProperty',
        'privateStaticProperty',
        'publicDynamicProperty',
    ];
    $sourceFlags = [
        // public, static
        [ null, null ],
        [ null, false ],
        [ null, true ],
        [ false, null ],
        [ true, null ],
        [ false, false ],
        [ true, true ],
        [ false, true ],
        [ true, false ],
    ];
    $objectDummy->publicDynamicProperty = null;

    $table = [];
    foreach ( $sources as $src ) {
        foreach ( $sourceProperties as $sourceProperty ) {
            foreach ( $sourceFlags as [ $isPublic, $isStatic ] ) {
                $status = \Gzhegow\Lib\Lib::php()->property_exists(
                    $src,
                    $sourceProperty,
                    $isPublic,
                    $isStatic
                );

                $tableColPublic = null
                    ?? (($isPublic === true) ? 'PUBLIC' : null)
                    ?? (($isPublic === null) ? '?PUBLIC' : null)
                    ?? (($isPublic === false) ? '!PUBLIC' : null);

                $tableColStatic = null
                    ?? (($isStatic === true) ? 'STATIC' : null)
                    ?? (($isStatic === null) ? '?STATIC' : null)
                    ?? (($isStatic === false) ? '!STATIC' : null);

                $tableRow = _values(' / ', $src, $sourceProperty);
                $tableCol = _values(' / ', $tableColPublic, $tableColStatic);

                $table[ $tableRow ][ $tableCol ] = _value($status);
            }
        }
    }
    \Gzhegow\Lib\Lib::debug()->print_table($table);
    unset($table);


    echo PHP_EOL;


    $sources = [
        $classDummy = \PhpModuleDummy1::class,
        $objectDummy = new \PhpModuleDummy1(),
    ];
    $sourceMethods = [
        'publicMethod',
        'protectedMethod',
        'privateMethod',
        'publicStaticMethod',
        'protectedStaticMethod',
        'privateStaticMethod',
    ];
    $sourceFlags = [
        // public, static
        [ null, null ],
        [ true, null ],
        [ null, true ],
        [ true, true ],
        [ false, null ],
        [ null, false ],
        [ false, false ],
        [ true, false ],
        [ false, true ],
    ];

    $table = [];
    foreach ( $sources as $src ) {
        foreach ( $sourceMethods as $sourceMethod ) {
            foreach ( $sourceFlags as [ $isPublic, $isStatic ] ) {
                $status = \Gzhegow\Lib\Lib::php()->method_exists(
                    $src,
                    $sourceMethod,
                    $isPublic,
                    $isStatic
                );

                $tableColPublic = null
                    ?? (($isPublic === true) ? 'PUBLIC' : null)
                    ?? (($isPublic === null) ? '?PUBLIC' : null)
                    ?? (($isPublic === false) ? '!PUBLIC' : null);

                $tableColStatic = null
                    ?? (($isStatic === true) ? 'STATIC' : null)
                    ?? (($isStatic === null) ? '?STATIC' : null)
                    ?? (($isStatic === false) ? '!STATIC' : null);

                $tableRow = _values(' / ', $src, $sourceMethod);
                $tableCol = _values(' / ', $tableColPublic, $tableColStatic);

                $table[ $tableRow ][ $tableCol ] = _value($status);
            }
        }
    }
    \Gzhegow\Lib\Lib::debug()->print_table($table);
    unset($table);


    echo PHP_EOL;


    $sources = [
        $functionInternal = 'strlen',
        $functionUser = 'PhpModule_dummy_function',
        $closure = function () { },
        $classInternal = \stdClass::class,
        $objectInternal = new \stdClass(),
        $classDummy1 = \PhpModuleDummy1::class,
        $classDummy2 = \PhpModuleDummy2::class,
        $classDummy3 = \PhpModuleDummy3::class,
        $classDummy4 = \PhpModuleDummy4::class,
        $objectDummy1 = new PhpModuleDummy1(),
        $objectDummy2 = new PhpModuleDummy2(),
        $objectDummy3 = new PhpModuleDummy3(),
        $objectDummy4 = new PhpModuleDummy4(),
    ];

    $table1 = [];
    $table2 = [];
    $table3 = [];
    $table4 = [];
    foreach ( $sources as $i => $src ) {
        $tableRow = _value($src);

        $status = \Gzhegow\Lib\Lib::php()->type_method_string($result, $src);
        $table1[ $tableRow ][ 'method_string' ] = _value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_method_array($result, $src);
        $table1[ $tableRow ][ 'method_array' ] = _value($result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable($result, $src, null);
        $table2[ $tableRow ][ 'callable' ] = _value($result);
        $table3[ $tableRow ][ 'callable' ] = _value($result);
        $table4[ $tableRow ][ 'callable' ] = _value($result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable_object($result, $src, null);
        $table2[ $tableRow ][ 'callable_object' ] = _value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_object_closure($result, $src, null);
        $table2[ $tableRow ][ 'callable_object_closure' ] = _value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_object_invokable($result, $src, null);
        $table2[ $tableRow ][ 'callable_object_invokable' ] = _value($result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable_array($result, $src, null);
        $table3[ $tableRow ][ 'callable_array' ] = _value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method($result, $src, null);
        $table3[ $tableRow ][ 'callable_array_method' ] = _value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method_static($result, $src, null);
        $table3[ $tableRow ][ 'callable_array_method_static' ] = _value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method_non_static($result, $src, null);
        $table3[ $tableRow ][ 'callable_array_method_non_static' ] = _value($result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable_string($result, $src, null);
        $table4[ $tableRow ][ 'callable_string' ] = _value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function($result, $src, null);
        $table4[ $tableRow ][ 'callable_string_function' ] = _value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_method_static($result, $src, null);
        $table4[ $tableRow ][ 'callable_string_method_static' ] = _value($result);
    }
    \Gzhegow\Lib\Lib::debug()->print_table($table1);
    \Gzhegow\Lib\Lib::debug()->print_table($table2);
    \Gzhegow\Lib\Lib::debug()->print_table($table3);
    \Gzhegow\Lib\Lib::debug()->print_table($table4);
    unset($table1);
    unset($table2);
    unset($table3);
    unset($table4);


    echo PHP_EOL;


    $sources = [];
    $sourceClasses = [
        \PhpModuleDummy1::class,
        \PhpModuleDummy2::class,
        \PhpModuleDummy3::class,
        \PhpModuleDummy4::class,
    ];

    foreach ( $sourceClasses as $i => $sourceClass ) {
        $sourceObject = new $sourceClass();

        $sources[ 0 ][ $sourceClass ] = [
            $sourceClass, // class
            $sourceObject, // object
        ];
        $sources[ 1 ][ $sourceClass ] = [
            $sourceClass . '::publicMethod', // 'class::publicMethod'
            $sourceClass . '::protectedMethod', // 'class::protectedMethod'
            $sourceClass . '::privateMethod', // 'class::privateMethod'
            //
            $sourceClass . '::publicStaticMethod', // 'class::publicStaticMethod'
            $sourceClass . '::protectedStaticMethod', // 'class::protectedStaticMethod'
            $sourceClass . '::privateStaticMethod', // 'class::privateStaticMethod'
            //
            $sourceClass . '::__call', // 'class::__call'
            $sourceClass . '::__callStatic', // 'class::__callStatic'
            $sourceClass . '::__invoke', // 'class::__invoke'
        ];
        $sources[ 2 ][ $sourceClass ] = [
            [ $sourceClass, 'publicMethod' ], // '[ class, publicMethod ]'
            [ $sourceClass, 'protectedMethod' ], // '[ class, protectedMethod ]'
            [ $sourceClass, 'privateMethod' ], // '[ class, privateMethod ]'
            //
            [ $sourceClass, 'publicStaticMethod' ], // '[ class, publicStaticMethod ]'
            [ $sourceClass, 'protectedStaticMethod' ], // '[ class, protectedStaticMethod ]'
            [ $sourceClass, 'privateStaticMethod' ], // '[ class, privateStaticMethod ]'
            //
            [ $sourceClass, '__call' ], // '[ class, __call ]'
            [ $sourceClass, '__callStatic' ], // '[ class, __callStatic ]'
            [ $sourceClass, '__invoke' ], // '[ class, __invoke ]'
        ];
        $sources[ 3 ][ $sourceClass ] = [
            [ $sourceObject, 'publicMethod' ], // '[ object, publicMethod ]'
            [ $sourceObject, 'protectedMethod' ], // '[ object, protectedMethod ]'
            [ $sourceObject, 'privateMethod' ], // '[ object, privateMethod ]'
            //
            [ $sourceObject, 'publicStaticMethod' ], // '[ object, publicStaticMethod ]'
            [ $sourceObject, 'protectedStaticMethod' ], // '[ object, protectedStaticMethod ]'
            [ $sourceObject, 'privateStaticMethod' ], // '[ object, privateStaticMethod ]'
            //
            [ $sourceObject, '__call' ], // '[ object, __call ]'
            [ $sourceObject, '__callStatic' ], // '[ object, __callStatic ]'
            [ $sourceObject, '__invoke' ], // '[ object, __invoke ]'
        ];
    }


    $table = [];
    foreach ( $sources as $type => $a ) {
        foreach ( $a as $sourceClass => $aa ) {
            foreach ( $aa as $src ) {
                $tableRow = _value($src);

                $status = \Gzhegow\Lib\Lib::php()->type_method_array($result, $src);
                $table[ $tableRow ][ 'method_array' ] = _value($result);

                $status = \Gzhegow\Lib\Lib::php()->type_method_string($result, $src);
                $table[ $tableRow ][ 'method_string' ] = _value($result);
            }
        }
    }
    \Gzhegow\Lib\Lib::debug()->print_table($table);
    unset($table);


    echo PHP_EOL;


    $table1 = [];
    $table2 = [];
    $table3 = [];
    foreach ( $sources as $type => $a ) {
        foreach ( $a as $sourceClass => $aa ) {
            foreach ( $aa as $src ) {
                $tableRow = _value($src);

                $sourceScopes = [
                    'scope: global' => null,
                    'scope: local'  => $sourceClass,
                ];

                foreach ( $sourceScopes as $scopeKey => $scope ) {
                    $tableCol = _values(' / ', 'callable', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable($result, $src, $scope);
                    $table1[ $tableRow ][ $tableCol ] = _value($status);
                    $table2[ $tableRow ][ $tableCol ] = _value($status);
                    $table3[ $tableRow ][ $tableCol ] = _value($status);


                    $tableCol = _values(' / ', 'callable_object', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_object($result, $src, $scope);
                    $table1[ $tableRow ][ $tableCol ] = _value($result);

                    $tableCol = _values(' / ', 'callable_object_closure', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_object_closure($result, $src, $scope);
                    $table1[ $tableRow ][ $tableCol ] = _value($result);

                    $tableCol = _values(' / ', 'callable_object_invokable', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_object_invokable($result, $src, $scope);
                    $table1[ $tableRow ][ $tableCol ] = _value($result);


                    $tableCol = _values(' / ', 'callable_array', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_array($result, $src, $scope);
                    $table2[ $tableRow ][ $tableCol ] = _value($result);

                    $tableCol = _values(' / ', 'callable_array_method', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method($result, $src, $scope);
                    $table2[ $tableRow ][ $tableCol ] = _value($result);

                    $tableCol = _values(' / ', 'callable_array_method_static', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method_static($result, $src, $scope);
                    $table2[ $tableRow ][ $tableCol ] = _value($result);

                    $tableCol = _values(' / ', 'callable_array_method_non_static', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method_non_static($result, $src, $scope);
                    $table2[ $tableRow ][ $tableCol ] = _value($result);


                    $tableCol = _values(' / ', 'callable_string', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string($result, $src, $scope);
                    $table3[ $tableRow ][ $tableCol ] = _value($result);

                    $tableCol = _values(' / ', 'callable_string', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function($result, $src, $scope);
                    $table3[ $tableRow ][ $tableCol ] = _value($result);

                    $tableCol = _values(' / ', 'callable_string', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string_method_static($result, $src, $scope);
                    $table3[ $tableRow ][ $tableCol ] = _value($result);
                }
            }
        }
    }
    echo md5(\Gzhegow\Lib\Lib::debug()->print_table($table1, 1)) . PHP_EOL;
    echo md5(\Gzhegow\Lib\Lib::debug()->print_table($table2, 1)) . PHP_EOL;
    echo md5(\Gzhegow\Lib\Lib::debug()->print_table($table3, 1)) . PHP_EOL;
    unset($table1);
    unset($table2);
    unset($table3);
};
_assert_stdout($fn, [], '
"[ PhpModule ]"

```
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
```

+---------------------------------------------------------+-----------------------+-----------------------+----------------------+-----------------------+----------------------+-----------------------+---------------------+----------------------+----------------------+
|                                                         | "?PUBLIC" / "?STATIC" | "?PUBLIC" / "!STATIC" | "?PUBLIC" / "STATIC" | "!PUBLIC" / "?STATIC" | "PUBLIC" / "?STATIC" | "!PUBLIC" / "!STATIC" | "PUBLIC" / "STATIC" | "!PUBLIC" / "STATIC" | "PUBLIC" / "!STATIC" |
+---------------------------------------------------------+-----------------------+-----------------------+----------------------+-----------------------+----------------------+-----------------------+---------------------+----------------------+----------------------+
| "PhpModuleDummy" / "publicProperty"                     | TRUE                  | TRUE                  | FALSE                | FALSE                 | TRUE                 | FALSE                 | FALSE               | FALSE                | TRUE                 |
| "PhpModuleDummy" / "protectedProperty"                  | TRUE                  | TRUE                  | FALSE                | TRUE                  | FALSE                | TRUE                  | FALSE               | FALSE                | FALSE                |
| "PhpModuleDummy" / "privateProperty"                    | TRUE                  | TRUE                  | FALSE                | TRUE                  | FALSE                | TRUE                  | FALSE               | FALSE                | FALSE                |
| "PhpModuleDummy" / "publicStaticProperty"               | TRUE                  | FALSE                 | TRUE                 | FALSE                 | TRUE                 | FALSE                 | TRUE                | FALSE                | FALSE                |
| "PhpModuleDummy" / "protectedStaticProperty"            | TRUE                  | FALSE                 | TRUE                 | TRUE                  | FALSE                | FALSE                 | FALSE               | TRUE                 | FALSE                |
| "PhpModuleDummy" / "privateStaticProperty"              | TRUE                  | FALSE                 | TRUE                 | TRUE                  | FALSE                | FALSE                 | FALSE               | TRUE                 | FALSE                |
| "PhpModuleDummy" / "publicDynamicProperty"              | FALSE                 | FALSE                 | FALSE                | FALSE                 | FALSE                | FALSE                 | FALSE               | FALSE                | FALSE                |
| { object # PhpModuleDummy } / "publicProperty"          | TRUE                  | TRUE                  | FALSE                | FALSE                 | TRUE                 | FALSE                 | FALSE               | FALSE                | TRUE                 |
| { object # PhpModuleDummy } / "protectedProperty"       | TRUE                  | TRUE                  | FALSE                | TRUE                  | FALSE                | TRUE                  | FALSE               | FALSE                | FALSE                |
| { object # PhpModuleDummy } / "privateProperty"         | TRUE                  | TRUE                  | FALSE                | TRUE                  | FALSE                | TRUE                  | FALSE               | FALSE                | FALSE                |
| { object # PhpModuleDummy } / "publicStaticProperty"    | TRUE                  | FALSE                 | TRUE                 | FALSE                 | TRUE                 | FALSE                 | TRUE                | FALSE                | FALSE                |
| { object # PhpModuleDummy } / "protectedStaticProperty" | TRUE                  | FALSE                 | TRUE                 | TRUE                  | FALSE                | FALSE                 | FALSE               | TRUE                 | FALSE                |
| { object # PhpModuleDummy } / "privateStaticProperty"   | TRUE                  | FALSE                 | TRUE                 | TRUE                  | FALSE                | FALSE                 | FALSE               | TRUE                 | FALSE                |
| { object # PhpModuleDummy } / "publicDynamicProperty"   | TRUE                  | TRUE                  | FALSE                | FALSE                 | TRUE                 | FALSE                 | FALSE               | FALSE                | TRUE                 |
+---------------------------------------------------------+-----------------------+-----------------------+----------------------+-----------------------+----------------------+-----------------------+---------------------+----------------------+----------------------+

+--------------------------------------------------------+-----------------------+----------------------+----------------------+---------------------+-----------------------+-----------------------+-----------------------+----------------------+----------------------+
|                                                        | "?PUBLIC" / "?STATIC" | "PUBLIC" / "?STATIC" | "?PUBLIC" / "STATIC" | "PUBLIC" / "STATIC" | "!PUBLIC" / "?STATIC" | "?PUBLIC" / "!STATIC" | "!PUBLIC" / "!STATIC" | "PUBLIC" / "!STATIC" | "!PUBLIC" / "STATIC" |
+--------------------------------------------------------+-----------------------+----------------------+----------------------+---------------------+-----------------------+-----------------------+-----------------------+----------------------+----------------------+
| "PhpModuleDummy1" / "publicMethod"                     | TRUE                  | TRUE                 | FALSE                | FALSE               | FALSE                 | TRUE                  | FALSE                 | TRUE                 | FALSE                |
| "PhpModuleDummy1" / "protectedMethod"                  | TRUE                  | FALSE                | FALSE                | FALSE               | TRUE                  | TRUE                  | TRUE                  | FALSE                | FALSE                |
| "PhpModuleDummy1" / "privateMethod"                    | TRUE                  | FALSE                | FALSE                | FALSE               | TRUE                  | TRUE                  | TRUE                  | FALSE                | FALSE                |
| "PhpModuleDummy1" / "publicStaticMethod"               | TRUE                  | TRUE                 | TRUE                 | TRUE                | FALSE                 | FALSE                 | FALSE                 | FALSE                | FALSE                |
| "PhpModuleDummy1" / "protectedStaticMethod"            | TRUE                  | FALSE                | TRUE                 | FALSE               | TRUE                  | FALSE                 | FALSE                 | FALSE                | TRUE                 |
| "PhpModuleDummy1" / "privateStaticMethod"              | TRUE                  | FALSE                | TRUE                 | FALSE               | TRUE                  | FALSE                 | FALSE                 | FALSE                | TRUE                 |
| { object # PhpModuleDummy1 } / "publicMethod"          | TRUE                  | TRUE                 | FALSE                | FALSE               | FALSE                 | TRUE                  | FALSE                 | TRUE                 | FALSE                |
| { object # PhpModuleDummy1 } / "protectedMethod"       | TRUE                  | FALSE                | FALSE                | FALSE               | TRUE                  | TRUE                  | TRUE                  | FALSE                | FALSE                |
| { object # PhpModuleDummy1 } / "privateMethod"         | TRUE                  | FALSE                | FALSE                | FALSE               | TRUE                  | TRUE                  | TRUE                  | FALSE                | FALSE                |
| { object # PhpModuleDummy1 } / "publicStaticMethod"    | TRUE                  | TRUE                 | TRUE                 | TRUE                | FALSE                 | FALSE                 | FALSE                 | FALSE                | FALSE                |
| { object # PhpModuleDummy1 } / "protectedStaticMethod" | TRUE                  | FALSE                | TRUE                 | FALSE               | TRUE                  | FALSE                 | FALSE                 | FALSE                | TRUE                 |
| { object # PhpModuleDummy1 } / "privateStaticMethod"   | TRUE                  | FALSE                | TRUE                 | FALSE               | TRUE                  | FALSE                 | FALSE                 | FALSE                | TRUE                 |
+--------------------------------------------------------+-----------------------+----------------------+----------------------+---------------------+-----------------------+-----------------------+-----------------------+----------------------+----------------------+

+-----------------------------------------+-----------------------------+-----------------------------------+
|                                         | method_string               | method_array                      |
+-----------------------------------------+-----------------------------+-----------------------------------+
| "strlen"                                | NULL                        | NULL                              |
| "PhpModule_dummy_function"              | NULL                        | NULL                              |
| { object(invokable) # Closure }         | NULL                        | NULL                              |
| "stdClass"                              | NULL                        | NULL                              |
| { object # stdClass }                   | NULL                        | NULL                              |
| "PhpModuleDummy1"                       | NULL                        | NULL                              |
| "PhpModuleDummy2"                       | NULL                        | NULL                              |
| "PhpModuleDummy3"                       | NULL                        | NULL                              |
| "PhpModuleDummy4"                       | "PhpModuleDummy4->__invoke" | [ "PhpModuleDummy4", "__invoke" ] |
| { object # PhpModuleDummy1 }            | NULL                        | NULL                              |
| { object # PhpModuleDummy2 }            | NULL                        | NULL                              |
| { object # PhpModuleDummy3 }            | NULL                        | NULL                              |
| { object(invokable) # PhpModuleDummy4 } | "PhpModuleDummy4->__invoke" | [ "PhpModuleDummy4", "__invoke" ] |
+-----------------------------------------+-----------------------------+-----------------------------------+
+-----------------------------------------+-----------------------------------------+-----------------------------------------+---------------------------------+-----------------------------------------+
|                                         | callable                                | callable_object                         | callable_object_closure         | callable_object_invokable               |
+-----------------------------------------+-----------------------------------------+-----------------------------------------+---------------------------------+-----------------------------------------+
| "strlen"                                | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| "PhpModule_dummy_function"              | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| { object(invokable) # Closure }         | { object(invokable) # Closure }         | { object(invokable) # Closure }         | { object(invokable) # Closure } | NULL                                    |
| "stdClass"                              | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| { object # stdClass }                   | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| "PhpModuleDummy1"                       | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| "PhpModuleDummy2"                       | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| "PhpModuleDummy3"                       | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| "PhpModuleDummy4"                       | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| { object # PhpModuleDummy1 }            | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| { object # PhpModuleDummy2 }            | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| { object # PhpModuleDummy3 }            | NULL                                    | NULL                                    | NULL                            | NULL                                    |
| { object(invokable) # PhpModuleDummy4 } | { object(invokable) # PhpModuleDummy4 } | { object(invokable) # PhpModuleDummy4 } | NULL                            | { object(invokable) # PhpModuleDummy4 } |
+-----------------------------------------+-----------------------------------------+-----------------------------------------+---------------------------------+-----------------------------------------+
+-----------------------------------------+-----------------------------------------+----------------+-----------------------+------------------------------+----------------------------------+
|                                         | callable                                | callable_array | callable_array_method | callable_array_method_static | callable_array_method_non_static |
+-----------------------------------------+-----------------------------------------+----------------+-----------------------+------------------------------+----------------------------------+
| "strlen"                                | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| "PhpModule_dummy_function"              | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| { object(invokable) # Closure }         | { object(invokable) # Closure }         | NULL           | NULL                  | NULL                         | NULL                             |
| "stdClass"                              | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| { object # stdClass }                   | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| "PhpModuleDummy1"                       | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| "PhpModuleDummy2"                       | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| "PhpModuleDummy3"                       | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| "PhpModuleDummy4"                       | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| { object # PhpModuleDummy1 }            | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| { object # PhpModuleDummy2 }            | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| { object # PhpModuleDummy3 }            | NULL                                    | NULL           | NULL                  | NULL                         | NULL                             |
| { object(invokable) # PhpModuleDummy4 } | { object(invokable) # PhpModuleDummy4 } | NULL           | NULL                  | NULL                         | NULL                             |
+-----------------------------------------+-----------------------------------------+----------------+-----------------------+------------------------------+----------------------------------+
+-----------------------------------------+-----------------------------------------+----------------------------+----------------------------+-------------------------------+
|                                         | callable                                | callable_string            | callable_string_function   | callable_string_method_static |
+-----------------------------------------+-----------------------------------------+----------------------------+----------------------------+-------------------------------+
| "strlen"                                | NULL                                    | "strlen"                   | "strlen"                   | NULL                          |
| "PhpModule_dummy_function"              | NULL                                    | "PhpModule_dummy_function" | "PhpModule_dummy_function" | NULL                          |
| { object(invokable) # Closure }         | { object(invokable) # Closure }         | NULL                       | NULL                       | NULL                          |
| "stdClass"                              | NULL                                    | NULL                       | NULL                       | NULL                          |
| { object # stdClass }                   | NULL                                    | NULL                       | NULL                       | NULL                          |
| "PhpModuleDummy1"                       | NULL                                    | NULL                       | NULL                       | NULL                          |
| "PhpModuleDummy2"                       | NULL                                    | NULL                       | NULL                       | NULL                          |
| "PhpModuleDummy3"                       | NULL                                    | NULL                       | NULL                       | NULL                          |
| "PhpModuleDummy4"                       | NULL                                    | NULL                       | NULL                       | NULL                          |
| { object # PhpModuleDummy1 }            | NULL                                    | NULL                       | NULL                       | NULL                          |
| { object # PhpModuleDummy2 }            | NULL                                    | NULL                       | NULL                       | NULL                          |
| { object # PhpModuleDummy3 }            | NULL                                    | NULL                       | NULL                       | NULL                          |
| { object(invokable) # PhpModuleDummy4 } | { object(invokable) # PhpModuleDummy4 } | NULL                       | NULL                       | NULL                          |
+-----------------------------------------+-----------------------------------------+----------------------------+----------------------------+-------------------------------+

+------------------------------------------------------------------------+------------------------------------------------+------------------------------------------+
|                                                                        | method_array                                   | method_string                            |
+------------------------------------------------------------------------+------------------------------------------------+------------------------------------------+
| "PhpModuleDummy1"                                                      | NULL                                           | NULL                                     |
| { object # PhpModuleDummy1 }                                           | NULL                                           | NULL                                     |
| "PhpModuleDummy2"                                                      | NULL                                           | NULL                                     |
| { object # PhpModuleDummy2 }                                           | NULL                                           | NULL                                     |
| "PhpModuleDummy3"                                                      | NULL                                           | NULL                                     |
| { object # PhpModuleDummy3 }                                           | NULL                                           | NULL                                     |
| "PhpModuleDummy4"                                                      | [ "PhpModuleDummy4", "__invoke" ]              | "PhpModuleDummy4->__invoke"              |
| { object(invokable) # PhpModuleDummy4 }                                | [ "PhpModuleDummy4", "__invoke" ]              | "PhpModuleDummy4->__invoke"              |
| "PhpModuleDummy1::publicMethod"                                        | [ "PhpModuleDummy1", "publicMethod" ]          | "PhpModuleDummy1->publicMethod"          |
| "PhpModuleDummy1::protectedMethod"                                     | [ "PhpModuleDummy1", "protectedMethod" ]       | "PhpModuleDummy1->protectedMethod"       |
| "PhpModuleDummy1::privateMethod"                                       | [ "PhpModuleDummy1", "privateMethod" ]         | "PhpModuleDummy1->privateMethod"         |
| "PhpModuleDummy1::publicStaticMethod"                                  | [ "PhpModuleDummy1", "publicStaticMethod" ]    | "PhpModuleDummy1::publicStaticMethod"    |
| "PhpModuleDummy1::protectedStaticMethod"                               | [ "PhpModuleDummy1", "protectedStaticMethod" ] | "PhpModuleDummy1::protectedStaticMethod" |
| "PhpModuleDummy1::privateStaticMethod"                                 | [ "PhpModuleDummy1", "privateStaticMethod" ]   | "PhpModuleDummy1::privateStaticMethod"   |
| "PhpModuleDummy1::__call"                                              | NULL                                           | NULL                                     |
| "PhpModuleDummy1::__callStatic"                                        | NULL                                           | NULL                                     |
| "PhpModuleDummy1::__invoke"                                            | NULL                                           | NULL                                     |
| "PhpModuleDummy2::publicMethod"                                        | NULL                                           | NULL                                     |
| "PhpModuleDummy2::protectedMethod"                                     | NULL                                           | NULL                                     |
| "PhpModuleDummy2::privateMethod"                                       | NULL                                           | NULL                                     |
| "PhpModuleDummy2::publicStaticMethod"                                  | NULL                                           | NULL                                     |
| "PhpModuleDummy2::protectedStaticMethod"                               | NULL                                           | NULL                                     |
| "PhpModuleDummy2::privateStaticMethod"                                 | NULL                                           | NULL                                     |
| "PhpModuleDummy2::__call"                                              | [ "PhpModuleDummy2", "__call" ]                | "PhpModuleDummy2->__call"                |
| "PhpModuleDummy2::__callStatic"                                        | NULL                                           | NULL                                     |
| "PhpModuleDummy2::__invoke"                                            | NULL                                           | NULL                                     |
| "PhpModuleDummy3::publicMethod"                                        | NULL                                           | NULL                                     |
| "PhpModuleDummy3::protectedMethod"                                     | NULL                                           | NULL                                     |
| "PhpModuleDummy3::privateMethod"                                       | NULL                                           | NULL                                     |
| "PhpModuleDummy3::publicStaticMethod"                                  | NULL                                           | NULL                                     |
| "PhpModuleDummy3::protectedStaticMethod"                               | NULL                                           | NULL                                     |
| "PhpModuleDummy3::privateStaticMethod"                                 | NULL                                           | NULL                                     |
| "PhpModuleDummy3::__call"                                              | NULL                                           | NULL                                     |
| "PhpModuleDummy3::__callStatic"                                        | [ "PhpModuleDummy3", "__callStatic" ]          | "PhpModuleDummy3::__callStatic"          |
| "PhpModuleDummy3::__invoke"                                            | NULL                                           | NULL                                     |
| "PhpModuleDummy4::publicMethod"                                        | NULL                                           | NULL                                     |
| "PhpModuleDummy4::protectedMethod"                                     | NULL                                           | NULL                                     |
| "PhpModuleDummy4::privateMethod"                                       | NULL                                           | NULL                                     |
| "PhpModuleDummy4::publicStaticMethod"                                  | NULL                                           | NULL                                     |
| "PhpModuleDummy4::protectedStaticMethod"                               | NULL                                           | NULL                                     |
| "PhpModuleDummy4::privateStaticMethod"                                 | NULL                                           | NULL                                     |
| "PhpModuleDummy4::__call"                                              | NULL                                           | NULL                                     |
| "PhpModuleDummy4::__callStatic"                                        | NULL                                           | NULL                                     |
| "PhpModuleDummy4::__invoke"                                            | [ "PhpModuleDummy4", "__invoke" ]              | "PhpModuleDummy4->__invoke"              |
| [ "PhpModuleDummy1", "publicMethod" ]                                  | [ "PhpModuleDummy1", "publicMethod" ]          | "PhpModuleDummy1->publicMethod"          |
| [ "PhpModuleDummy1", "protectedMethod" ]                               | [ "PhpModuleDummy1", "protectedMethod" ]       | "PhpModuleDummy1->protectedMethod"       |
| [ "PhpModuleDummy1", "privateMethod" ]                                 | [ "PhpModuleDummy1", "privateMethod" ]         | "PhpModuleDummy1->privateMethod"         |
| [ "PhpModuleDummy1", "publicStaticMethod" ]                            | [ "PhpModuleDummy1", "publicStaticMethod" ]    | "PhpModuleDummy1::publicStaticMethod"    |
| [ "PhpModuleDummy1", "protectedStaticMethod" ]                         | [ "PhpModuleDummy1", "protectedStaticMethod" ] | "PhpModuleDummy1::protectedStaticMethod" |
| [ "PhpModuleDummy1", "privateStaticMethod" ]                           | [ "PhpModuleDummy1", "privateStaticMethod" ]   | "PhpModuleDummy1::privateStaticMethod"   |
| [ "PhpModuleDummy1", "__call" ]                                        | NULL                                           | NULL                                     |
| [ "PhpModuleDummy1", "__callStatic" ]                                  | NULL                                           | NULL                                     |
| [ "PhpModuleDummy1", "__invoke" ]                                      | NULL                                           | NULL                                     |
| [ "PhpModuleDummy2", "publicMethod" ]                                  | NULL                                           | NULL                                     |
| [ "PhpModuleDummy2", "protectedMethod" ]                               | NULL                                           | NULL                                     |
| [ "PhpModuleDummy2", "privateMethod" ]                                 | NULL                                           | NULL                                     |
| [ "PhpModuleDummy2", "publicStaticMethod" ]                            | NULL                                           | NULL                                     |
| [ "PhpModuleDummy2", "protectedStaticMethod" ]                         | NULL                                           | NULL                                     |
| [ "PhpModuleDummy2", "privateStaticMethod" ]                           | NULL                                           | NULL                                     |
| [ "PhpModuleDummy2", "__call" ]                                        | [ "PhpModuleDummy2", "__call" ]                | "PhpModuleDummy2->__call"                |
| [ "PhpModuleDummy2", "__callStatic" ]                                  | NULL                                           | NULL                                     |
| [ "PhpModuleDummy2", "__invoke" ]                                      | NULL                                           | NULL                                     |
| [ "PhpModuleDummy3", "publicMethod" ]                                  | NULL                                           | NULL                                     |
| [ "PhpModuleDummy3", "protectedMethod" ]                               | NULL                                           | NULL                                     |
| [ "PhpModuleDummy3", "privateMethod" ]                                 | NULL                                           | NULL                                     |
| [ "PhpModuleDummy3", "publicStaticMethod" ]                            | NULL                                           | NULL                                     |
| [ "PhpModuleDummy3", "protectedStaticMethod" ]                         | NULL                                           | NULL                                     |
| [ "PhpModuleDummy3", "privateStaticMethod" ]                           | NULL                                           | NULL                                     |
| [ "PhpModuleDummy3", "__call" ]                                        | NULL                                           | NULL                                     |
| [ "PhpModuleDummy3", "__callStatic" ]                                  | [ "PhpModuleDummy3", "__callStatic" ]          | "PhpModuleDummy3::__callStatic"          |
| [ "PhpModuleDummy3", "__invoke" ]                                      | NULL                                           | NULL                                     |
| [ "PhpModuleDummy4", "publicMethod" ]                                  | NULL                                           | NULL                                     |
| [ "PhpModuleDummy4", "protectedMethod" ]                               | NULL                                           | NULL                                     |
| [ "PhpModuleDummy4", "privateMethod" ]                                 | NULL                                           | NULL                                     |
| [ "PhpModuleDummy4", "publicStaticMethod" ]                            | NULL                                           | NULL                                     |
| [ "PhpModuleDummy4", "protectedStaticMethod" ]                         | NULL                                           | NULL                                     |
| [ "PhpModuleDummy4", "privateStaticMethod" ]                           | NULL                                           | NULL                                     |
| [ "PhpModuleDummy4", "__call" ]                                        | NULL                                           | NULL                                     |
| [ "PhpModuleDummy4", "__callStatic" ]                                  | NULL                                           | NULL                                     |
| [ "PhpModuleDummy4", "__invoke" ]                                      | [ "PhpModuleDummy4", "__invoke" ]              | "PhpModuleDummy4->__invoke"              |
| [ "{ object # PhpModuleDummy1 }", "publicMethod" ]                     | [ "PhpModuleDummy1", "publicMethod" ]          | "PhpModuleDummy1->publicMethod"          |
| [ "{ object # PhpModuleDummy1 }", "protectedMethod" ]                  | [ "PhpModuleDummy1", "protectedMethod" ]       | "PhpModuleDummy1->protectedMethod"       |
| [ "{ object # PhpModuleDummy1 }", "privateMethod" ]                    | [ "PhpModuleDummy1", "privateMethod" ]         | "PhpModuleDummy1->privateMethod"         |
| [ "{ object # PhpModuleDummy1 }", "publicStaticMethod" ]               | [ "PhpModuleDummy1", "publicStaticMethod" ]    | "PhpModuleDummy1::publicStaticMethod"    |
| [ "{ object # PhpModuleDummy1 }", "protectedStaticMethod" ]            | [ "PhpModuleDummy1", "protectedStaticMethod" ] | "PhpModuleDummy1::protectedStaticMethod" |
| [ "{ object # PhpModuleDummy1 }", "privateStaticMethod" ]              | [ "PhpModuleDummy1", "privateStaticMethod" ]   | "PhpModuleDummy1::privateStaticMethod"   |
| [ "{ object # PhpModuleDummy1 }", "__call" ]                           | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy1 }", "__callStatic" ]                     | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy1 }", "__invoke" ]                         | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy2 }", "publicMethod" ]                     | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy2 }", "protectedMethod" ]                  | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy2 }", "privateMethod" ]                    | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy2 }", "publicStaticMethod" ]               | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy2 }", "protectedStaticMethod" ]            | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy2 }", "privateStaticMethod" ]              | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy2 }", "__call" ]                           | [ "PhpModuleDummy2", "__call" ]                | "PhpModuleDummy2->__call"                |
| [ "{ object # PhpModuleDummy2 }", "__callStatic" ]                     | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy2 }", "__invoke" ]                         | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy3 }", "publicMethod" ]                     | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy3 }", "protectedMethod" ]                  | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy3 }", "privateMethod" ]                    | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy3 }", "publicStaticMethod" ]               | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy3 }", "protectedStaticMethod" ]            | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy3 }", "privateStaticMethod" ]              | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy3 }", "__call" ]                           | NULL                                           | NULL                                     |
| [ "{ object # PhpModuleDummy3 }", "__callStatic" ]                     | [ "PhpModuleDummy3", "__callStatic" ]          | "PhpModuleDummy3::__callStatic"          |
| [ "{ object # PhpModuleDummy3 }", "__invoke" ]                         | NULL                                           | NULL                                     |
| [ "{ object(invokable) # PhpModuleDummy4 }", "publicMethod" ]          | NULL                                           | NULL                                     |
| [ "{ object(invokable) # PhpModuleDummy4 }", "protectedMethod" ]       | NULL                                           | NULL                                     |
| [ "{ object(invokable) # PhpModuleDummy4 }", "privateMethod" ]         | NULL                                           | NULL                                     |
| [ "{ object(invokable) # PhpModuleDummy4 }", "publicStaticMethod" ]    | NULL                                           | NULL                                     |
| [ "{ object(invokable) # PhpModuleDummy4 }", "protectedStaticMethod" ] | NULL                                           | NULL                                     |
| [ "{ object(invokable) # PhpModuleDummy4 }", "privateStaticMethod" ]   | NULL                                           | NULL                                     |
| [ "{ object(invokable) # PhpModuleDummy4 }", "__call" ]                | NULL                                           | NULL                                     |
| [ "{ object(invokable) # PhpModuleDummy4 }", "__callStatic" ]          | NULL                                           | NULL                                     |
| [ "{ object(invokable) # PhpModuleDummy4 }", "__invoke" ]              | [ "PhpModuleDummy4", "__invoke" ]              | "PhpModuleDummy4->__invoke"              |
+------------------------------------------------------------------------+------------------------------------------------+------------------------------------------+

ade866c08e7a84839f9cc464b4d0461d
e3e8ff183726869306ec79e12c313e6f
f200e4a1931dce3cb01f1f50469a92d3
');


// >>> TEST
// > тесты RandomModule
$fn = function () {
    _print('[ RandomModule ]');
    echo PHP_EOL;

    $rand = \Gzhegow\Lib\Lib::random()->random_bytes(16);
    _print($len = strlen($rand), $len === 16);

    $rand = \Gzhegow\Lib\Lib::random()->random_hex(16);
    _print($len = strlen($rand), $len === 32);

    $rand = \Gzhegow\Lib\Lib::random()->random_int(1, 100);
    _print(1 <= $rand, $rand <= 100);

    $rand = \Gzhegow\Lib\Lib::random()->random_string(16);
    _print(mb_strlen($rand) === 16);

    $rand = \Gzhegow\Lib\Lib::random()->random_base64_urlsafe(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            rtrim($rand, '='),
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_64_RFC4648_URLSAFE
        )
    ;
    _print(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base64(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            rtrim($rand, '='),
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_64_RFC4648
        )
    ;
    _print(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base62(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            $rand,
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_62
        )
    ;
    _print(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base58(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            $rand,
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_58
        )
    ;
    _print(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base36(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            $rand,
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_36
        )
    ;
    _print(null !== $test);
};
_assert_stdout($fn, [], '
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
    _print('[ StrModule ]');
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->lines("hello\nworld"));
    _print(\Gzhegow\Lib\Lib::str()->eol("hello\nworld"));
    _print(\Gzhegow\Lib\Lib::str()->lines('hello' . PHP_EOL . 'world'));
    _print(\Gzhegow\Lib\Lib::str()->eol('hello' . PHP_EOL . 'world'));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->strlen('Привет'));
    _print(\Gzhegow\Lib\Lib::str()->strlen('Hello'));
    _print(\Gzhegow\Lib\Lib::str()->strsize('Привет'));
    _print(\Gzhegow\Lib\Lib::str()->strsize('Hello'));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->lower('ПРИВЕТ'));
    _print(\Gzhegow\Lib\Lib::str()->upper('привет'));
    _print(\Gzhegow\Lib\Lib::str()->lcfirst('ПРИВЕТ'));
    _print(\Gzhegow\Lib\Lib::str()->ucfirst('привет'));
    _print(\Gzhegow\Lib\Lib::str()->lcwords('ПРИВЕТ МИР'));
    _print(\Gzhegow\Lib\Lib::str()->ucwords('привет мир'));

    _print(\Gzhegow\Lib\Lib::str()->starts('привет', 'при'));
    _print(\Gzhegow\Lib\Lib::str()->ends('привет', 'вет'));
    _print(\Gzhegow\Lib\Lib::str()->contains('привет', 'ив'));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->lcrop('азаза_привет_азаза', 'аза'));
    _print(\Gzhegow\Lib\Lib::str()->rcrop('азаза_привет_азаза', 'аза'));
    _print(\Gzhegow\Lib\Lib::str()->crop('азаза_привет_азаза', 'аза'));
    _print(\Gzhegow\Lib\Lib::str()->unlcrop('"привет"', '"'));
    _print(\Gzhegow\Lib\Lib::str()->unrcrop('"привет"', '"'));
    _print(\Gzhegow\Lib\Lib::str()->uncrop('"привет"', '"'));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->replace_limit('за', '_', 'азазазазазаза', 3));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->camel('-hello-world-foo-bar'));
    _print(\Gzhegow\Lib\Lib::str()->camel('-helloWorldFooBar'));
    _print(\Gzhegow\Lib\Lib::str()->camel('-HelloWorldFooBar'));
    _print(\Gzhegow\Lib\Lib::str()->pascal('-hello-world-foo-bar'));
    _print(\Gzhegow\Lib\Lib::str()->pascal('-helloWorldFooBar'));
    _print(\Gzhegow\Lib\Lib::str()->pascal('-HelloWorldFooBar'));
    _print(\Gzhegow\Lib\Lib::str()->space('_Hello_WORLD_Foo_BAR'));
    _print(\Gzhegow\Lib\Lib::str()->snake('-Hello-WORLD-Foo-BAR'));
    _print(\Gzhegow\Lib\Lib::str()->kebab(' Hello WORLD Foo BAR'));
    _print(\Gzhegow\Lib\Lib::str()->space_lower('_Hello_WORLD_Foo_BAR'));
    _print(\Gzhegow\Lib\Lib::str()->snake_lower('-Hello-WORLD-Foo-BAR'));
    _print(\Gzhegow\Lib\Lib::str()->kebab_lower(' Hello WORLD Foo BAR'));
    _print(\Gzhegow\Lib\Lib::str()->space_upper('_Hello_WORLD_Foo_BAR'));
    _print(\Gzhegow\Lib\Lib::str()->snake_upper('-Hello-WORLD-Foo-BAR'));
    _print(\Gzhegow\Lib\Lib::str()->kebab_upper(' Hello WORLD Foo BAR'));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->prefix('primary'));
    _print(\Gzhegow\Lib\Lib::str()->prefix('unique'));
    _print(\Gzhegow\Lib\Lib::str()->prefix('index'));
    _print(\Gzhegow\Lib\Lib::str()->prefix('fulltext'));
    _print(\Gzhegow\Lib\Lib::str()->prefix('fullText'));
    _print(\Gzhegow\Lib\Lib::str()->prefix('spatialIndex'));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->translit_ru2ascii('привет мир'));
    _print(\Gzhegow\Lib\Lib::str()->translit_ru2ascii('+привет +мир +100 abc', '-', '+'));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->interpolator()->interpolate('привет {{username}}', [ 'username' => 'мир' ]));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->slugger()->translit(' привет мир '));
    _print(\Gzhegow\Lib\Lib::str()->slugger()->slug('привет мир'));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->inflector()->singularize('users'));
    _print(\Gzhegow\Lib\Lib::str()->inflector()->pluralize('user'));
    echo PHP_EOL;
};
_assert_stdout($fn, [], '
"[ StrModule ]"

[ "hello", "world" ]
"hello\n
world"
[ "hello", "world" ]
"hello\n
world"

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

"pri"
"unq"
"ind"
"ful"
"ful"
"spa"

"npuBeT Mup"
"+npuBeT +Mup +100 ---"

"привет мир"

" privet mir "
"privet-mir"

[ "user" ]
[ "users" ]
');
```