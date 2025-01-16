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
_assert_output($fn, '
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
