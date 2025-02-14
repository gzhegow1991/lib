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
function _rdebug(...$values) : string
{
    return \Gzhegow\Lib\Lib::debug()->types('', [], ...$values);
}

function _rdump(...$values) : string
{
    return \Gzhegow\Lib\Lib::debug()->values('', [], ...$values);
}

function _debug(...$values) : void
{
    echo _rdebug(...$values) . PHP_EOL;
}

function _dump(...$values) : void
{
    echo _rdump(...$values) . PHP_EOL;
}

function _dump_array($value, int $maxLevel = null, array $options = []) : string
{
    $ret = \Gzhegow\Lib\Lib::debug()
        ->value_array($value, $maxLevel, $options)
    ;

    echo $ret . PHP_EOL;

    return $ret;
}

function _dump_array_multiline($value, int $maxLevel = null, array $options = []) : string
{
    $content = \Gzhegow\Lib\Lib::debug()
        ->value_array_multiline($value, $maxLevel, $options)
    ;

    $ret = $content . PHP_EOL;

    echo $ret;

    return $ret;
}

function _assert_return(
    \Closure $fn, array $fnArgs = [],
    $expectedReturn = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::test()->assertReturn(
        $trace,
        $fn, $fnArgs,
        $expectedReturn
    );
}

function _assert_stdout(
    \Closure $fn, array $fnArgs = [],
    string $expectedStdout = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::test()->assertStdout($trace,
        $fn, $fnArgs,
        $expectedStdout
    );
}

function _assert_microtime(
    \Closure $fn, array $fnArgs = [],
    float $expectedMicrotimeMax = null, float $expectedMicrotimeMin = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::test()->assertMicrotime(
        $trace,
        $fn, $fnArgs,
        $expectedMicrotimeMax, $expectedMicrotimeMin
    );
}


// >>> TEST
// > тесты ArrayModule
$fn = function () {
    _dump('[ ArrModule ]');
    echo PHP_EOL;

    $notAnObject = 1;
    $object = new stdClass();
    $anotherObject = new ArrayObject();
    $anonymousObject = new class extends \stdClass {
    };


    $array = new \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf('object');
    $array[] = $notAnObject;
    _dump($array);
    _dump($array->getItems());

    // > be aware, `ArrayOf` WILL NOT check type when adding elements, so this check returns true
    // > if you use this feature carefully - you can avoid that check, and code becomes faster
    // > it will work like PHPDoc idea - check should remember your colleagues who will read the sources without actually check
    _dump($array->isOfType('object'));
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
    _dump('[ CATCH ] ' . $e->getMessage());
    _dump($array);
    _dump($array->getItems());
    _dump($array->isOfType('object'));
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
    _dump('[ CATCH ] ' . $e->getMessage());
    try {
        $array[] = $anonymousObject;
    }
    catch ( \Throwable $e ) {
    }
    _dump('[ CATCH ] ' . $e->getMessage());
    try {
        $array[] = $notAnObject;
    }
    catch ( \Throwable $e ) {
    }
    _dump('[ CATCH ] ' . $e->getMessage());
    _dump($array);
    _dump($array->getItems());
    _dump($array->isOfType('object'));
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
    _dump('[ AssertModule ]');
    echo PHP_EOL;

    try {
        $var = \Gzhegow\Lib\Lib::assert()
            ->string_not_empty('')
            ->orThrow('The value should be non-empty string')
        ;
    }
    catch ( \Throwable $e ) {
    }
    _dump('[ CATCH ] ' . $e->getMessage());

    try {
        $var = \Gzhegow\Lib\Lib::assert()
            ->numeric_positive('-1')
            ->orThrow('The value should be positive numeric')
        ;
    }
    catch ( \Throwable $e ) {
    }
    _dump('[ CATCH ] ' . $e->getMessage());
};
_assert_stdout($fn, [], '
"[ AssertModule ]"

"[ CATCH ] The value should be non-empty string"
"[ CATCH ] The value should be positive numeric"
');


// >>> TEST
// > тесты BcmathModule
$fn = function () {
    _dump('[ BcmathModule ]');
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 2);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 2);
    _dump($result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('1.005', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('1.005', 2);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('-1.005', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('-1.005', 2);
    _dump($result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 2);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 2);
    _dump($result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('1.005', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('1.005', 2);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('-1.005', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('-1.005', 2);
    _dump($result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.5', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.05', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.005', 0);
    _dump($result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.5', 2);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.05', 2);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.005', 2);
    _dump($result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.5', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.05', 0);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.005', 0);
    _dump($result);
    echo PHP_EOL;

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.5', 2);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.05', 2);
    _dump($result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.005', 2);
    _dump($result);
    echo PHP_EOL;

    $gcd = \Gzhegow\Lib\Lib::bcmath()->bcgcd(8, 12);
    _dump($gcd);
    $gcd = \Gzhegow\Lib\Lib::bcmath()->bcgcd(7, 13);
    _dump($gcd);
    echo PHP_EOL;

    $lcm = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 6);
    _dump($lcm);
    $lcm = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 5);
    _dump($lcm);
    $lcm = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 10);
    _dump($lcm);
    echo PHP_EOL;
};
_assert_stdout($fn, [], '
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


    $algos = [
        'fnv1a32',
        'crc32',
        'md5',
        'sha1',
        'sha256',
    ];
    foreach ( $algos as $algo ) {
        $hash = \Gzhegow\Lib\Lib::crypt()->hash($algo, 'hello world!', $binary = false);
        _dump($hash);
        $result = \Gzhegow\Lib\Lib::crypt()->hash_equals($hash, $algo, 'hello world!', $binary = false);
        _dump($result);
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
        _dump($hash01);
        $result = \Gzhegow\Lib\Lib::crypt()->hash_equals($hash, $algo, 'hello world!', $binary = true);
        _dump($result);
        echo PHP_EOL;
    }


    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, '01');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01');
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(3, '01');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01');
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, '01234567');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01234567');
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(15, '01234567');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01234567');
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, '0123456789ABCDEF');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '0123456789ABCDEF');
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(31, '0123456789ABCDEF');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '0123456789ABCDEF');
    _dump($dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(25, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(26, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = false);
    _dump($dec);
    echo PHP_EOL;

    try {
        $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    }
    catch ( \Throwable $e ) {
    }
    _dump('[ CATCH ] ' . $e->getMessage());
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(26, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($dec);
    echo PHP_EOL;

    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase(27, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased = true);
    _dump($dec);
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
    _dump($enc);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('11', '0123');
    _dump($enc);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('111', '01234567');
    _dump($enc);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('1111', '0123456789ABCDEF');
    _dump($enc);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('11111', '0123456789ABCDEFGHIJKLMNOPQRSTUV');
    _dump($enc);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase('111111', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/');
    _dump($enc);
    echo PHP_EOL;


    echo PHP_EOL;


    $strings = [ '你' ];
    _dump_array($strings);
    $binaries = \Gzhegow\Lib\Lib::crypt()->text2bin($strings);
    _dump_array($binaries);
    $letters = \Gzhegow\Lib\Lib::crypt()->bin2text($binaries);
    _dump_array($letters);
    echo PHP_EOL;

    $strings = [ '你好' ];
    _dump_array($strings);
    $binaries = \Gzhegow\Lib\Lib::crypt()->text2bin($strings);
    _dump_array($binaries);
    $letters = \Gzhegow\Lib\Lib::crypt()->bin2text($binaries);
    _dump_array($letters);
    echo PHP_EOL;


    echo PHP_EOL;


    $number = 5678;
    _dump($number);
    $binary = decbin(5678);
    _dump($binary);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2numbase($binary, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2bin($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _dump($dec);
    $number = bindec($dec);
    _dump($number);
    echo PHP_EOL;

    $strings = [ 'hello' ];
    _dump_array($strings);
    $binaries = \Gzhegow\Lib\Lib::crypt()->text2bin($strings);
    _dump_array($binaries);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2base($binaries, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base2bin($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    _dump_array($dec);
    $text = implode('', array_map('chr', array_map('bindec', $dec)));
    _dump($text);
    echo PHP_EOL;


    echo PHP_EOL;


    $src = 'HELLO';
    _dump('input: ' . $src);
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
    _dump('result: ' . $dec);
    echo PHP_EOL;

    $src = 'HELLO';
    _dump('input: ' . $src);
    $gen = (function () use ($src) { yield $src; })();
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_it($gen);
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_it($gen);
    $dec = '';
    foreach ( $gen as $letter ) {
        $dec .= $letter;
    }
    _dump('result: ' . $dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $string = "hello";
    _dump($string);
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($string);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    _dump($dec);
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
    _dump('b`' . $srcDump . '`');
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($src);
    _dump($enc);
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
    _dump('b`' . $decDump . '`');
    echo PHP_EOL;

    $string = "你好";
    _dump($string);
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($string);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    _dump($dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $string = "hello";
    _dump($string);
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($string);
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    _dump($dec);
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
    _dump('b`' . $srcDump . '`');
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($src);
    _dump($enc);
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
    _dump('b`' . $decDump . '`');
    echo PHP_EOL;

    $string = '你好';
    _dump($string);
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode("你好");
    _dump($enc);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    _dump($dec);
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
    _dump('[ DebugModule ]');
    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _dump($isDiff);
    _dump_array_multiline($diffLines, 1);
    echo PHP_EOL;


    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple2\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _dump($isDiff);
    _dump_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry2\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _dump($isDiff);
    _dump_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry2";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _dump($isDiff);
    _dump_array_multiline($diffLines, 1);
    echo PHP_EOL;


    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "fig\napple\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _dump($isDiff);
    _dump_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\nfig\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _dump($isDiff);
    _dump_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry\nfig";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _dump($isDiff);
    _dump_array_multiline($diffLines, 1);
    echo PHP_EOL;


    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "banana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _dump($isDiff);
    _dump_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _dump($isDiff);
    _dump_array_multiline($diffLines, 1);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _dump($isDiff);
    _dump_array_multiline($diffLines, 1);
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
};
_assert_stdout($fn, [], '
"[ DebugModule ]"

FALSE
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]


TRUE
[
  "[ 1 ] +++ > apple @ --- apple2",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]

TRUE
[
  "apple",
  "banana",
  "[ 3 ] +++ > cherry @ --- cherry2",
  "damson",
  "elderberry"
]

TRUE
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "[ 5 ] +++ > elderberry @ --- elderberry2"
]


TRUE
[
  "[ 1 ] --- > fig",
  "apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]

TRUE
[
  "apple",
  "banana",
  "cherry",
  "[ 4 ] --- > fig",
  "damson",
  "elderberry"
]

TRUE
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "elderberry",
  "[ 6 ] --- > fig"
]


TRUE
[
  "[ 1 ] +++ > apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]

TRUE
[
  "apple",
  "banana",
  "[ 3 ] +++ > cherry",
  "damson",
  "elderberry"
]

TRUE
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "[ 5 ] +++ > elderberry"
]


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
    _dump('[ ParseModule ]');
    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::parse()->ctype_digit('123');
    _dump($result);
    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::parse()->ctype_alpha('abcABC');
    _dump($result);

    $result = \Gzhegow\Lib\Lib::parse()->ctype_alpha('abcABC', false);
    _dump($result);
    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::parse()->ctype_alnum('123abcABC');
    _dump($result);

    $result = \Gzhegow\Lib\Lib::parse()->ctype_alnum('123abcABC', false);
    _dump($result);
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
    _dump('[ PhpModule ]');
    echo PHP_EOL;


    \Gzhegow\Lib\Lib::php()->errors_start($b);

    for ( $i = 0; $i < 3; $i++ ) {
        \Gzhegow\Lib\Lib::php()->error([ 'This is the error message' ]);
    }

    $errors = \Gzhegow\Lib\Lib::php()->errors_end($b);

    _dump_array_multiline($errors, 2);


    echo PHP_EOL;


    class PhpModuleDummy1
    {
        public function publicMethod()
        {
        }

        protected function protectedMethod()
        {
        }

        private function privateMethod()
        {
        }


        public static function publicStaticMethod()
        {
        }

        protected static function protectedStaticMethod()
        {
        }

        private static function privateStaticMethod()
        {
        }
    }

    class PhpModuleDummy2
    {
        public function __call($name, $args)
        {
        }
    }

    class PhpModuleDummy3
    {
        public static function __callStatic($name, $args)
        {
        }
    }

    class PhpModuleDummy4
    {
        public function __invoke()
        {
        }
    }

    function PhpModule_dummy_function()
    {
    }


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

    $table = [];
    foreach ( $sources as $i => $src ) {
        $srcKey = _rdump($src);


        $status = \Gzhegow\Lib\Lib::php()->type_method_string($result, $src);
        $table[ $srcKey ][ 'method_string' ] = _rdump($status);
        // _dump('type_method_string', $src, $status, $result);

        $status = \Gzhegow\Lib\Lib::php()->type_method_array($result, $src);
        $table[ $srcKey ][ 'method_array' ] = _rdump($status);
        // _dump('type_method_array', $src, $status, $result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable($result, $src);
        $table[ $srcKey ][ 'callable' ] = _rdump($status);
        // _dump('type_callable', $src, $status, $result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable_object($result, $src);
        $table[ $srcKey ][ 'callable_object' ] = rtrim(_rdump($status));
        // _dump('type_callable_object', $src, $status, $result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_object_closure($result, $src);
        $table[ $srcKey ][ 'callable_object_closure' ] = _rdump($status);
        // _dump('type_callable_object_closure', $src, $status, $result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_object_invokable($result, $src);
        $table[ $srcKey ][ 'callable_object_invokable' ] = _rdump($status);
        // _dump('type_callable_object_invokable', $src, $status, $result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable_array($result, $src);
        $table[ $srcKey ][ 'callable_array' ] = _rdump($status);
        // _dump('type_callable_array', $src, $status, $result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method($result, $src);
        $table[ $srcKey ][ 'callable_array_method' ] = _rdump($status);
        // _dump('type_callable_array_method', $src, $status, $result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method_static($result, $src);
        $table[ $srcKey ][ 'callable_array_method_static' ] = _rdump($status);
        // _dump('type_callable_array_method_static', $src, $status, $result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method_non_static($result, $src);
        $table[ $srcKey ][ 'callable_array_method_non_static' ] = _rdump($status);
        // _dump('type_callable_array_method_non_static', $src, $status, $result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable_string($result, $src);
        $table[ $srcKey ][ 'callable_string' ] = _rdump($status);
        // _dump('type_callable_string', $src, $status, $result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function($result, $src);
        $table[ $srcKey ][ 'callable_string_function' ] = _rdump($status);
        // _dump('type_callable_string_function', $src, $status, $result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_method_static($result, $src);
        $table[ $srcKey ][ 'callable_string_method_static' ] = _rdump($status);
        // _dump('type_callable_string_method_static', $src, $status, $result);
    }
    \Gzhegow\Lib\Lib::debug()->print_table($table);


    echo PHP_EOL;


    $sourceClasses = [
        \PhpModuleDummy1::class,
        \PhpModuleDummy2::class,
        \PhpModuleDummy3::class,
        \PhpModuleDummy4::class,
    ];

    $table = [];
    foreach ( $sourceClasses as $sourceClass ) {
        $sourceObject = new $sourceClass();

        $sourceMethods = [
            'class::publicMethod'               => $sourceClass . '::publicMethod',
            'class::protectedMethod'            => $sourceClass . '::protectedMethod',
            'class::privateMethod'              => $sourceClass . '::privateMethod',
            //
            'class::publicStaticMethod'         => $sourceClass . '::publicStaticMethod',
            'class::protectedStaticMethod'      => $sourceClass . '::protectedStaticMethod',
            'class::privateStaticMethod'        => $sourceClass . '::privateStaticMethod',
            //
            '[ class, publicMethod ]'           => [ $sourceClass, 'publicMethod' ],
            '[ class, protectedMethod ]'        => [ $sourceClass, 'protectedMethod' ],
            '[ class, privateMethod ]'          => [ $sourceClass, 'privateMethod' ],
            //
            '[ class, publicStaticMethod ]'     => [ $sourceClass, 'publicStaticMethod' ],
            '[ class, protectedStaticMethod ]'  => [ $sourceClass, 'protectedStaticMethod' ],
            '[ class, privateStaticMethod ]'    => [ $sourceClass, 'privateStaticMethod' ],
            //
            '[ object, publicMethod ]'          => [ $sourceObject, 'publicMethod' ],
            '[ object, protectedMethod ]'       => [ $sourceObject, 'protectedMethod' ],
            '[ object, privateMethod ]'         => [ $sourceObject, 'privateMethod' ],
            //
            '[ object, publicStaticMethod ]'    => [ $sourceObject, 'publicStaticMethod' ],
            '[ object, protectedStaticMethod ]' => [ $sourceObject, 'protectedStaticMethod' ],
            '[ object, privateStaticMethod ]'   => [ $sourceObject, 'privateStaticMethod' ],
        ];
        $scopes = [
            'static' => 'static',
            'NULL'   => null,
            'class'  => $sourceClass,
        ];
        foreach ( $sourceMethods as $sourceMethodKey => $sourceMethod ) {
            $srcKey = _rdump($sourceMethod);

            $status = \Gzhegow\Lib\Lib::php()->type_method_string($result, $sourceMethod);
            $table[ $srcKey ][ _rdump('method_string') ] = _rdump($status);

            $status = \Gzhegow\Lib\Lib::php()->type_method_array($result, $sourceMethod);
            $table[ $srcKey ][ _rdump('method_array') ] = _rdump($status);

            foreach ( $scopes as $scopeKey => $scope ) {
                $status = \Gzhegow\Lib\Lib::php()->type_callable_array($result, $sourceMethod, $scope);
                $table[ $srcKey ][ _rdump('callable_array', 'scope: ' . $scopeKey) ] = _rdump($status);

                $status = \Gzhegow\Lib\Lib::php()->type_callable_string($result, $sourceMethod, $scope);
                $table[ $srcKey ][ _rdump('callable_string', 'scope: ' . $scopeKey) ] = _rdump($status);
            }
        }
    }
    \Gzhegow\Lib\Lib::debug()->print_table($table);
};
_assert_stdout($fn, [], '
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

+------------------------------+---------------+--------------+----------+-----------------+-------------------------+---------------------------+----------------+-----------------------+------------------------------+----------------------------------+-----------------+--------------------------+-------------------------------+
|                              | method_string | method_array | callable | callable_object | callable_object_closure | callable_object_invokable | callable_array | callable_array_method | callable_array_method_static | callable_array_method_non_static | callable_string | callable_string_function | callable_string_method_static |
+------------------------------+---------------+--------------+----------+-----------------+-------------------------+---------------------------+----------------+-----------------------+------------------------------+----------------------------------+-----------------+--------------------------+-------------------------------+
| "strlen"                     | FALSE         | FALSE        | TRUE     | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | TRUE            | TRUE                     | FALSE                         |
| "PhpModule_dummy_function"   | FALSE         | FALSE        | TRUE     | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | TRUE            | TRUE                     | FALSE                         |
| { object # Closure }         | FALSE         | FALSE        | TRUE     | TRUE            | TRUE                    | TRUE                      | FALSE          | FALSE                 | FALSE                        | FALSE                            | FALSE           | FALSE                    | FALSE                         |
| "stdClass"                   | FALSE         | FALSE        | FALSE    | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | FALSE           | FALSE                    | FALSE                         |
| { object # stdClass }        | FALSE         | FALSE        | FALSE    | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | FALSE           | FALSE                    | FALSE                         |
| "PhpModuleDummy1"            | FALSE         | FALSE        | FALSE    | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | FALSE           | FALSE                    | FALSE                         |
| "PhpModuleDummy2"            | FALSE         | FALSE        | FALSE    | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | FALSE           | FALSE                    | FALSE                         |
| "PhpModuleDummy3"            | FALSE         | FALSE        | FALSE    | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | FALSE           | FALSE                    | FALSE                         |
| "PhpModuleDummy4"            | FALSE         | FALSE        | FALSE    | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | FALSE           | FALSE                    | FALSE                         |
| { object # PhpModuleDummy1 } | FALSE         | FALSE        | FALSE    | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | FALSE           | FALSE                    | FALSE                         |
| { object # PhpModuleDummy2 } | FALSE         | FALSE        | FALSE    | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | FALSE           | FALSE                    | FALSE                         |
| { object # PhpModuleDummy3 } | FALSE         | FALSE        | FALSE    | FALSE           | FALSE                   | FALSE                     | FALSE          | FALSE                 | FALSE                        | FALSE                            | FALSE           | FALSE                    | FALSE                         |
| { object # PhpModuleDummy4 } | TRUE          | TRUE         | TRUE     | TRUE            | FALSE                   | TRUE                      | TRUE           | TRUE                  | FALSE                        | TRUE                             | FALSE           | FALSE                    | FALSE                         |
+------------------------------+---------------+--------------+----------+-----------------+-------------------------+---------------------------+----------------+-----------------------+------------------------------+----------------------------------+-----------------+--------------------------+-------------------------------+

+-------------------------------------------------------------+-----------------+----------------+------------------------------------+-------------------------------------+----------------------------------+-----------------------------------+-----------------------------------+------------------------------------+
|                                                             | "method_string" | "method_array" | "callable_array" | "scope: static" | "callable_string" | "scope: static" | "callable_array" | "scope: NULL" | "callable_string" | "scope: NULL" | "callable_array" | "scope: class" | "callable_string" | "scope: class" |
+-------------------------------------------------------------+-----------------+----------------+------------------------------------+-------------------------------------+----------------------------------+-----------------------------------+-----------------------------------+------------------------------------+
| "PhpModuleDummy1::publicMethod"                             | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy1::protectedMethod"                          | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy1::privateMethod"                            | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy1::publicStaticMethod"                       | TRUE            | TRUE           | TRUE                               | TRUE                                | TRUE                             | TRUE                              | TRUE                              | TRUE                               |
| "PhpModuleDummy1::protectedStaticMethod"                    | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | TRUE                              | TRUE                               |
| "PhpModuleDummy1::privateStaticMethod"                      | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | TRUE                              | TRUE                               |
| [ "PhpModuleDummy1", "publicMethod" ]                       | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy1", "protectedMethod" ]                    | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy1", "privateMethod" ]                      | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy1", "publicStaticMethod" ]                 | TRUE            | TRUE           | TRUE                               | TRUE                                | TRUE                             | TRUE                              | TRUE                              | TRUE                               |
| [ "PhpModuleDummy1", "protectedStaticMethod" ]              | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | TRUE                              | TRUE                               |
| [ "PhpModuleDummy1", "privateStaticMethod" ]                | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | TRUE                              | TRUE                               |
| [ "{ object # PhpModuleDummy1 }", "publicMethod" ]          | TRUE            | TRUE           | TRUE                               | FALSE                               | TRUE                             | FALSE                             | TRUE                              | FALSE                              |
| [ "{ object # PhpModuleDummy1 }", "protectedMethod" ]       | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | TRUE                              | FALSE                              |
| [ "{ object # PhpModuleDummy1 }", "privateMethod" ]         | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | TRUE                              | FALSE                              |
| [ "{ object # PhpModuleDummy1 }", "publicStaticMethod" ]    | TRUE            | TRUE           | TRUE                               | FALSE                               | TRUE                             | FALSE                             | TRUE                              | FALSE                              |
| [ "{ object # PhpModuleDummy1 }", "protectedStaticMethod" ] | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | TRUE                              | FALSE                              |
| [ "{ object # PhpModuleDummy1 }", "privateStaticMethod" ]   | TRUE            | TRUE           | FALSE                              | FALSE                               | FALSE                            | FALSE                             | TRUE                              | FALSE                              |
| "PhpModuleDummy2::publicMethod"                             | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy2::protectedMethod"                          | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy2::privateMethod"                            | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy2::publicStaticMethod"                       | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy2::protectedStaticMethod"                    | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy2::privateStaticMethod"                      | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy2", "publicMethod" ]                       | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy2", "protectedMethod" ]                    | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy2", "privateMethod" ]                      | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy2", "publicStaticMethod" ]                 | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy2", "protectedStaticMethod" ]              | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy2", "privateStaticMethod" ]                | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy2 }", "publicMethod" ]          | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy2 }", "protectedMethod" ]       | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy2 }", "privateMethod" ]         | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy2 }", "publicStaticMethod" ]    | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy2 }", "protectedStaticMethod" ] | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy2 }", "privateStaticMethod" ]   | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy3::publicMethod"                             | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy3::protectedMethod"                          | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy3::privateMethod"                            | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy3::publicStaticMethod"                       | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy3::protectedStaticMethod"                    | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy3::privateStaticMethod"                      | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy3", "publicMethod" ]                       | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy3", "protectedMethod" ]                    | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy3", "privateMethod" ]                      | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy3", "publicStaticMethod" ]                 | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy3", "protectedStaticMethod" ]              | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy3", "privateStaticMethod" ]                | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy3 }", "publicMethod" ]          | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy3 }", "protectedMethod" ]       | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy3 }", "privateMethod" ]         | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy3 }", "publicStaticMethod" ]    | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy3 }", "protectedStaticMethod" ] | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy3 }", "privateStaticMethod" ]   | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy4::publicMethod"                             | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy4::protectedMethod"                          | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy4::privateMethod"                            | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy4::publicStaticMethod"                       | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy4::protectedStaticMethod"                    | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| "PhpModuleDummy4::privateStaticMethod"                      | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy4", "publicMethod" ]                       | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy4", "protectedMethod" ]                    | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy4", "privateMethod" ]                      | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy4", "publicStaticMethod" ]                 | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy4", "protectedStaticMethod" ]              | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "PhpModuleDummy4", "privateStaticMethod" ]                | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy4 }", "publicMethod" ]          | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy4 }", "protectedMethod" ]       | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy4 }", "privateMethod" ]         | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy4 }", "publicStaticMethod" ]    | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy4 }", "protectedStaticMethod" ] | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
| [ "{ object # PhpModuleDummy4 }", "privateStaticMethod" ]   | FALSE           | FALSE          | FALSE                              | FALSE                               | FALSE                            | FALSE                             | FALSE                             | FALSE                              |
+-------------------------------------------------------------+-----------------+----------------+------------------------------------+-------------------------------------+----------------------------------+-----------------------------------+-----------------------------------+------------------------------------+
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
    _dump('[ StrModule ]');
    echo PHP_EOL;

    _dump(\Gzhegow\Lib\Lib::str()->lines("hello\nworld"));
    echo PHP_EOL;

    _dump(\Gzhegow\Lib\Lib::str()->eol('hello' . PHP_EOL . 'world'));
    echo PHP_EOL;

    _dump(\Gzhegow\Lib\Lib::str()->strlen('Привет'));
    _dump(\Gzhegow\Lib\Lib::str()->strlen('Hello'));
    _dump(\Gzhegow\Lib\Lib::str()->strsize('Привет'));
    _dump(\Gzhegow\Lib\Lib::str()->strsize('Hello'));
    echo PHP_EOL;

    _dump(\Gzhegow\Lib\Lib::str()->lower('ПРИВЕТ'));
    _dump(\Gzhegow\Lib\Lib::str()->upper('привет'));
    _dump(\Gzhegow\Lib\Lib::str()->lcfirst('ПРИВЕТ'));
    _dump(\Gzhegow\Lib\Lib::str()->ucfirst('привет'));
    _dump(\Gzhegow\Lib\Lib::str()->lcwords('ПРИВЕТ МИР'));
    _dump(\Gzhegow\Lib\Lib::str()->ucwords('привет мир'));

    _dump(\Gzhegow\Lib\Lib::str()->starts('привет', 'при'));
    _dump(\Gzhegow\Lib\Lib::str()->ends('привет', 'вет'));
    _dump(\Gzhegow\Lib\Lib::str()->contains('привет', 'ив'));
    echo PHP_EOL;

    _dump(\Gzhegow\Lib\Lib::str()->lcrop('азаза_привет_азаза', 'аза'));
    _dump(\Gzhegow\Lib\Lib::str()->rcrop('азаза_привет_азаза', 'аза'));
    _dump(\Gzhegow\Lib\Lib::str()->crop('азаза_привет_азаза', 'аза'));
    _dump(\Gzhegow\Lib\Lib::str()->unlcrop('"привет"', '"'));
    _dump(\Gzhegow\Lib\Lib::str()->unrcrop('"привет"', '"'));
    _dump(\Gzhegow\Lib\Lib::str()->uncrop('"привет"', '"'));
    echo PHP_EOL;

    _dump(\Gzhegow\Lib\Lib::str()->replace_limit('за', '_', 'азазазазазаза', 3));
    echo PHP_EOL;

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
    echo PHP_EOL;

    _dump(\Gzhegow\Lib\Lib::str()->prefix('primary'));
    _dump(\Gzhegow\Lib\Lib::str()->prefix('unique'));
    _dump(\Gzhegow\Lib\Lib::str()->prefix('index'));
    _dump(\Gzhegow\Lib\Lib::str()->prefix('fulltext'));
    _dump(\Gzhegow\Lib\Lib::str()->prefix('fullText'));
    _dump(\Gzhegow\Lib\Lib::str()->prefix('spatialIndex'));
    echo PHP_EOL;

    _dump(\Gzhegow\Lib\Lib::str()->inflector()->singularize('users'));
    _dump(\Gzhegow\Lib\Lib::str()->inflector()->pluralize('user'));
    echo PHP_EOL;

    _dump(\Gzhegow\Lib\Lib::str()->interpolator()->interpolate('привет {{username}}', [ 'username' => 'медвед' ]));
    echo PHP_EOL;

    _dump(\Gzhegow\Lib\Lib::str()->slugger()->slug('привет мир'));
};
_assert_stdout($fn, [], '
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

"pri"
"unq"
"ind"
"ful"
"ful"
"spa"

[ "user" ]
[ "users" ]

"привет медвед"

"privet-mir"
');
