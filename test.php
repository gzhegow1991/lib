<?php

require_once __DIR__ . '/vendor/autoload.php';


// > настраиваем PHP
ini_set('memory_limit', '32M');


// > настраиваем обработку ошибок
// > некоторые CMS сами по себе применяют error_reporting/set_error_handler/set_exception_handler глубоко в ядре
// > с помощью этого класса можно указать при загрузке свои собственные и вызвав методы ->use{smtg}() вернуть указанные
(new \Gzhegow\Lib\Exception\ErrorHandler())
    // > index.php
    // ->setErrorReporting(E_ALL)
    // ->setErrorHandler([ \Gzhegow\Lib\Exception\ErrorHandler::class, 'error_handler' ])
    // ->setExceptionHandler([ \Gzhegow\Lib\Exception\ErrorHandler::class, 'exception_handler' ])
    //
    // > ... какой-то код самой CMS
    //
    // > yourscript.php
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

function _array($value, ?int $maxLevel = null, array $options = []) : string
{
    return \Gzhegow\Lib\Lib::debug()->value_array($value, $maxLevel, $options);
}

function _array_multiline($value, ?int $maxLevel = null, array $options = []) : string
{
    return \Gzhegow\Lib\Lib::debug()->value_array_multiline($value, $maxLevel, $options);
}

function _print(...$values) : void
{
    echo _values(' | ', ...$values) . PHP_EOL;
}

function _print_array($value, ?int $maxLevel = null, array $options = [])
{
    echo _array($value, $maxLevel, $options) . PHP_EOL;
}

function _print_array_multiline($value, ?int $maxLevel = null, array $options = [])
{
    echo _array_multiline($value, $maxLevel, $options) . PHP_EOL;
}

function _assert_stdout(
    \Closure $fn, array $fnArgs = [],
    ?string $expectedStdout = null
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
// > тесты Pipe
$fn = function () {
    _print('[ Pipe ]');
    echo PHP_EOL;

    $fn = \Gzhegow\Lib\Lib::pipe();

    $fn
        // > этот шаг может заменить значение, в данном случае приведя его к строке
        ->map('strval')
        //
        // > этот шаг может очистить значение (в последующих шаг будет использоваться NULL)
        ->filter('strlen')
        //
        // > этот шаг может выполнить сторонние действия, а возврат метода игнорируется
        ->tap(function ($value) {
            echo 'Hello World! Your value is: [ ' . _value($value) . ' ]' . PHP_EOL;

            throw new \Gzhegow\Lib\Exception\RuntimeException('This is the exception');
        })
        //
        // > этот шаг никогда не начнется, поскольку в прошлом шаге было выброшено исключение
        ->map('intval')
        //
        // > этот шаг может поймать исключение
        // ->catchTo($e) // > исключение будет сохранено в $e по ссылке, а значение удалено
        // ->catchTo($e, [ 'catchTo' ]) // > тоже, но значение будет заменено на 'catchTo'
        // ->catchTo($e, [ 'catchTo' ], LogicException::class) > тоже, но только если \Throwable это субкласс
        ->catchTo($e, [ 'catchTo' ], LogicException::class)
        //
        // > или можно обрабатывать исключения обычным способом через callable
        ->catch(function (\Throwable $e, $null, $result) {
            if ($e instanceof \RuntimeException) {
                return $result;
            }

            return $e;
        }, $arguments = [ 2 => 'catch' ])
    ;

    $result = $fn('');
    _print($result);

    $result = $fn(1);
    _print($result);

    $result = $fn('0');
    _print($result);
};
_assert_stdout($fn, [], '
"[ Pipe ]"

Hello World! Your value is: [ NULL ]
"catch"
Hello World! Your value is: [ "1" ]
"catch"
Hello World! Your value is: [ "0" ]
"catch"
');


// >>> TEST
// > тесты Benchmark
$fn = function () {
    _print('[ Benchmark ]');
    echo PHP_EOL;

    $tag = 'tag';

    \Gzhegow\Lib\Lib::benchmark(0, $tag);

    for ( $i = 0; $i < 2; $i++ ) {
        $ttag = 'tag' . $i;

        \Gzhegow\Lib\Lib::benchmark(0, $ttag);

        for ( $ii = 0; $ii < 2; $ii++ ) {
            $tttag = 'tag' . $i . $ii;

            \Gzhegow\Lib\Lib::benchmark(0, $tttag);

            usleep(1e4);

            \Gzhegow\Lib\Lib::benchmark(1, $tttag);
        }

        \Gzhegow\Lib\Lib::benchmark(1, $ttag);
    }

    \Gzhegow\Lib\Lib::benchmark(1, $tag);

    $report = \Gzhegow\Lib\Lib::benchmark();

    $expect = [
        'tag'   => (4 * 1e-4),
        //
        'tag0'  => (2 * 1e-4),
        'tag1'  => (2 * 1e-4),
        //
        'tag00' => (1e-4),
        'tag01' => (1e-4),
        'tag10' => (1e-4),
        'tag11' => (1e-4),
    ];

    $reportIndex = array_keys($report);

    foreach ( $report as $tag => $floats ) {
        $report[ $tag ] = $expect[ $tag ] < array_sum($floats);
    }

    _print_array_multiline($report, 2);
};
_assert_stdout($fn, [], '
"[ Benchmark ]"

###
[
  "tag" => TRUE,
  "tag0" => TRUE,
  "tag00" => TRUE,
  "tag01" => TRUE,
  "tag1" => TRUE,
  "tag10" => TRUE,
  "tag11" => TRUE
]
###
');


// >>> TEST
// > тесты Config
$fn = function () {
    _print('[ Config ]');
    echo PHP_EOL;

    class ConfigDummy extends \Gzhegow\Lib\Config\AbstractConfig
    {
        protected $child;

        public function __construct()
        {
            $this->child = new \ConfigChildDummy();

            parent::__construct();
        }
    }

    class ConfigChildDummy extends \Gzhegow\Lib\Config\AbstractConfig
    {
        protected $foo = 'bar';
    }

    class ConfigValidateDummy extends \Gzhegow\Lib\Config\AbstractConfig
    {
        protected $child;

        public function __construct()
        {
            $this->child = new \ConfigChildValidateDummy();

            parent::__construct();
        }
    }

    class ConfigChildValidateDummy extends \Gzhegow\Lib\Config\AbstractConfig
    {
        protected $foo = 1;
        protected $foo2;

        protected function validation(array &$context = []) : bool
        {
            if ($this->foo2 !== $this->foo) {
                return false;
            }

            return true;
        }
    }


    $config = new \ConfigDummy();

    $configChildDefault = $config->child;

    $configChildNewFooValue = 'baz';
    $configChildNew = new \ConfigChildDummy();
    $configChildNew->foo = $configChildNewFooValue;

    $config->child = $configChildNew;

    _print($config);
    _print($config->child, $config->child === $configChildDefault);
    _print($config->child->foo, $config->child->foo === $configChildNew->foo);

    echo PHP_EOL;


    $config = new \ConfigDummy();
    $configChildDefault = $config->child;

    $configChildNewFooValue = 'baz';
    $config->load([
        'hello' => 'world',
        'foo'   => 'bar',
        'child' => [
            'foo' => $configChildNewFooValue,
        ],
    ]);

    _print($config);
    _print($config->child, $config->child === $configChildDefault);
    _print($config->child->foo, $config->child->foo === $configChildNewFooValue);

    echo PHP_EOL;


    $configArray = $config->toArray();
    $config->load($configArray);

    _print($config);
    _print($config->child, $config->child === $configChildDefault);
    _print($config->child->foo, $config->child->foo === $configChildNewFooValue);

    echo PHP_EOL;


    $config = new \ConfigValidateDummy();
    try {
        $config->validate();
    }
    catch ( \Throwable $e ) {
        _print('[ CATCH ] ' . $e->getMessage());
    }
};
_assert_stdout($fn, [], '
"[ Config ]"

{ object # ConfigDummy }
{ object # ConfigChildDummy } | TRUE
"baz" | TRUE

{ object # ConfigDummy }
{ object # ConfigChildDummy } | TRUE
"baz" | TRUE

{ object # ConfigDummy }
{ object # ConfigChildDummy } | TRUE
"baz" | TRUE

"[ CATCH ] Configuration is invalid"
');


// >>> TEST
// > тесты Errors
$fn = function () {
    _print('[ Errors ]');
    echo PHP_EOL;


    \Gzhegow\Lib\Lib::php()->errors_start($b);

    for ( $i = 0; $i < 3; $i++ ) {
        \Gzhegow\Lib\Lib::php()->error([ 'This is the error message' ]);
    }

    $errors = \Gzhegow\Lib\Lib::php()->errors_end($b);

    _print_array_multiline($errors, 2);
};
_assert_stdout($fn, [], '
"[ Errors ]"

###
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
###
');


// >>> TEST
// > тесты Exception
$fn = function () {
    _print('[ Exception ]');
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
        unset($eTrace[ $i ][ 'args' ]);
    }

    _print($e->getFileOverride(__DIR__));

    echo PHP_EOL;

    _print_array_multiline($eTrace, 2);
};
_assert_stdout($fn, [], '
"[ Exception ]"

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

###
[
  [
    "function" => "{closure}"
  ],
  [
    "file" => "src/Modules/TestModule.php",
    "function" => "call_user_func_array"
  ],
  [
    "file" => "test.php",
    "function" => "assertStdout",
    "class" => "Gzhegow\Lib\Modules\TestModule",
    "type" => "->"
  ],
  [
    "file" => "test.php",
    "function" => "_assert_stdout"
  ]
]
###
');



// >>> TEST
// > тесты ArrayOf
$fn = function () {
    _print('[ ArrayOf ]');
    echo PHP_EOL;


    $notAnObject = 1;
    $objectStdClass = new stdClass();
    $objectArrayObject = new ArrayObject();
    $objectAnonymousStdClass = new class extends \stdClass {
    };


    /**
     * @var \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf $arrayOf
     * > это синтаксис создания объекта в 8 версии PHP отдельно от 7, на практике не потребуется, только для тестов
     */
    $class = \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf::class;
    $args = [ 'object' ];
    $arrayOf = \Gzhegow\Lib\Lib::new8($class, ...$args);

    // > осторожно, `ArrayOf` не проверяет типы при добавлении, для этого есть `ArrayOfType`
    // > этот объект сделан для того, чтобы убедится, что другой разработчик создал его с правильным типом
    // > при этом он может положить туда что захочет, это похоже на указание PHPDoc
    $arrayOf[] = $notAnObject;
    _print($arrayOf);
    _print($arrayOf->isOfType('object'), $arrayOf->getValues());

    echo PHP_EOL;


    /**
     * @var \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfType $arrayOf
     */
    $class = \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfType::class;
    $args = [ 'object' ];
    $arrayOf = \Gzhegow\Lib\Lib::new8($class, ...$args);
    $arrayOf[] = $objectStdClass;
    $arrayOf[] = $objectArrayObject;

    $e = null;
    try {
        $arrayOf[] = $notAnObject;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());
    _print($arrayOf);
    _print($arrayOf->isOfType('object'), $arrayOf->getValues());

    echo PHP_EOL;


    /**
     * @var \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfClass $arrayOf
     */
    $class = \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfClass::class;
    $args = [ \stdClass::class ];
    $arrayOf = \Gzhegow\Lib\Lib::new8($class, ...$args);
    $arrayOf[] = $objectStdClass;;

    $e = null;
    try {
        $arrayOf[] = $objectArrayObject;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());

    $e = null;
    try {
        $arrayOf[] = $objectAnonymousStdClass;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());

    $e = null;
    try {
        $arrayOf[] = $notAnObject;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());
    _print($arrayOf);
    _print($arrayOf->isOfType('object'), $arrayOf->getValues());

    echo PHP_EOL;


    // > для полного посвящения разработана структура `Map` по аналогии с JavaScript
    // > в качестве ключей этого объекта можно использовать вообще любые значения
    /**
     * @var \Gzhegow\Lib\Modules\Arr\Map\Map $map
     */
    $class = \Gzhegow\Lib\Modules\Arr\Map\Map::class;
    $map = \Gzhegow\Lib\Lib::new8($class);
    $map[ $stdClass = new \stdClass() ] = 1;
    $map[ $array = [ 1, 2, 3 ] ] = 1;
    _print($map);
    _print(isset($map[ $stdClass ]), isset($map[ $array ]));
    _print_array($map->keys(), 1);
    _print_array($map->values(), 2);
};
_assert_stdout($fn, [], PHP_VERSION_ID >= 80000
    ? '
"[ ArrayOf ]"

{ object(countable(1) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf }
TRUE | [ 1 ]

"[ CATCH ] The `value` should be of type: object"
{ object(countable(2) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfType }
TRUE | [ "{ object # stdClass }", "{ object(countable(0) iterable) # ArrayObject }" ]

"[ CATCH ] The `value` should be of class: stdClass"
"[ CATCH ] The `value` should be of class: stdClass"
"[ CATCH ] The `value` should be object"
{ object(countable(1) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfClass }
TRUE | [ "{ object # stdClass }" ]

{ object(countable(2) iterable) # Gzhegow\Lib\Modules\Arr\Map\Map }
TRUE | TRUE
[ "{ object # stdClass }", "{ array(3) }" ]
[ 1, 1 ]
'
    : '
"[ ArrayOf ]"

{ object(countable(1) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOf }
TRUE | [ 1 ]

"[ CATCH ] The `value` should be of type: object"
{ object(countable(2) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfType }
TRUE | [ "{ object # stdClass }", "{ object(countable(0) iterable) # ArrayObject }" ]

"[ CATCH ] The `value` should be of class: stdClass"
"[ CATCH ] The `value` should be of class: stdClass"
"[ CATCH ] The `value` should be object"
{ object(countable(1) iterable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfClass }
TRUE | [ "{ object # stdClass }" ]

{ object(countable(2) iterable) # Gzhegow\Lib\Modules\Arr\Map\PHP7\Map }
TRUE | TRUE
[ "{ object # stdClass }", "{ array(3) }" ]
[ 1, 1 ]
');



// >>> TEST
// > тесты ArrModule
$fn = function () {
    _print('[ ArrModule ]');
    echo PHP_EOL;

    $cases = [
        [ [ 1, 2, 3 ], [ 2, 3, 4 ] ],
        [ [ 1, '2', 3 ], [ 2, 3 ] ],
        [ [ '1', '2', '3' ], [ 1, 2 ] ],
        [ [ 1, 2, 2, 3 ], [ 2 ] ],
        [ [ 'x' => 100, 'y' => 200, 'z' => 300 ], [ 200, 300, 400 ] ],
    ];

    foreach ( $cases as [ $a, $b ] ) {
        $resStrict = \Gzhegow\Lib\Lib::arr()->intersect($a, $b);
        $resNonStrict = \Gzhegow\Lib\Lib::arr()->intersect_non_strict($a, $b);
        _print($resStrict, $resNonStrict, $resNonStrict === array_intersect($a, $b));

        $resStrict = \Gzhegow\Lib\Lib::arr()->diff($a, $b);
        $resNonStrict = \Gzhegow\Lib\Lib::arr()->diff_non_strict($a, $b);
        _print($resStrict, $resNonStrict, $resNonStrict === array_diff($a, $b));
    }
};
_assert_stdout($fn, [], '
"[ ArrModule ]"

[ 1 => 2, 2 => 3 ] | [ 1 => 2, 2 => 3 ] | TRUE
[ 1 ] | [ 1 ] | TRUE
[ 2 => 3 ] | [ 1 => "2", 2 => 3 ] | TRUE
[ 1, "2" ] | [ 1 ] | TRUE
[  ] | [ "1", "2" ] | TRUE
[ "1", "2", "3" ] | [ 2 => "3" ] | TRUE
[ 1 => 2, 2 => 2 ] | [ 1 => 2, 2 => 2 ] | TRUE
[ 0 => 1, 3 => 3 ] | [ 0 => 1, 3 => 3 ] | TRUE
[ "y" => 200, "z" => 300 ] | [ "y" => 200, "z" => 300 ] | TRUE
[ "x" => 100 ] | [ "x" => 100 ] | TRUE
');


// >>> TEST
// > тесты AssertModule
$fn = function () {
    _print('[ AssertModule ]');
    echo PHP_EOL;

    $var = \Gzhegow\Lib\Lib::assert(-1)
        ->int_positive()
        ->orNull()
    ;
    _print($var); // NULL

    $var = \Gzhegow\Lib\Lib::assert('-1')
        ->string_not_empty()
        ->numeric_positive()
        ->orFallback([ NAN ])
    ;
    _print($var); // NAN

    $e = null;
    try {
        $var = \Gzhegow\Lib\Lib::assert('-1')
            ->numeric_positive()
            ->triggerError('The value should be positive numeric', E_USER_ERROR)
            ->orNull()
        ;
    }
    catch ( \Throwable $e ) {
        // > since we are using (new \Gzhegow\Lib\Exception\ErrorHandler())->useErrorHandler() it throws
    }
    _print('[ CATCH ] ' . $e->getMessage());

    $e = null;
    try {
        $var = \Gzhegow\Lib\Lib::assert('-1')
            ->numeric_positive()
            ->orThrow('The value should be positive numeric')
        ;
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());
};
_assert_stdout($fn, [], '
"[ AssertModule ]"

NULL
NAN
"[ CATCH ] The value should be positive numeric"
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
// > тесты CmpModule
$fn = function () {
    _print('[ CmpModule ]');
    echo PHP_EOL;

    $object = new \StdClass();

    $resourceOpenedStdout = STDOUT;
    $resourceOpenedStderr = STDERR;
    $resourceClosed = fopen('php://memory', 'w');
    fclose($resourceClosed);

    $valuesXX = [
        0  => [
            NAN,
            null,
            new \Gzhegow\Lib\Modules\Type\Nil(),
        ],
        1  => [
            strval(NAN),
            'NULL',
            strval(new \Gzhegow\Lib\Modules\Type\Nil()),
        ],
        2  => [
            false,
            true,
        ],
        3  => [
            0,
            1,
        ],
        4  => [
            0.0,
            1.0,
            1.1,
            1e1,
            1.1e1, // 11
            1.1e-1, // 0.11
        ],
        5  => [
            PHP_INT_MAX, // > int
            PHP_INT_MIN, // > int
            (PHP_INT_MAX + 1), // > float
            (PHP_INT_MIN - 1), // > float
        ],
        6  => [
            PHP_FLOAT_MAX,
            PHP_FLOAT_MIN,
            -PHP_FLOAT_MAX,
            -PHP_FLOAT_MIN,
        ],
        7  => [
            INF,
            -INF,
        ],
        8  => [
            '',
            'a',
            'b',
            'ab',
            'ba',
        ],
        9  => [
            '0',
            '1',
        ],
        10 => [
            '0.0',
            '1.0',
            '1.1',
            '1e1',
            '1.1e1', // 11
            '1.1e-1', // 0.11
        ],
        11 => [
            strval(PHP_INT_MAX), // > int
            strval(PHP_INT_MIN), // > int
            strval(PHP_INT_MAX + 1), // > float
            strval(PHP_INT_MIN - 1), // > float
        ],
        12 => [
            strval(PHP_FLOAT_MAX),
            strval(PHP_FLOAT_MIN),
            strval(-PHP_FLOAT_MAX),
            strval(-PHP_FLOAT_MIN),
        ],
        13 => [
            strval(INF),
            strval(-INF),
        ],
        14 => [
            $resourceOpenedStdout,
            $resourceOpenedStderr,
            $resourceClosed,
        ],
        15 => [
            [],
            [ '' ],
            [ 'a' ],
            [ 'a', 'b' ],
        ],
        16 => [
            (new \DateTime('1970-01-01 00:00:00')),
            (new \DateTime('1970-01-01 00:00:01')),
            (new \DateTime('1970-01-01 00:00:01'))->modify('+500ms'),
        ],
        17 => [
            $object,
            new \StdClass(),
        ],
        18 => [
            (object) [],
            (object) [ '' ],
            (object) [ 'a' ],
            (object) [ 'a', 'b' ],
        ],
        19 => [
            new ArrayObject([]),
            new ArrayObject([ '' ]),
            new ArrayObject([ 'a' ]),
            new ArrayObject([ 'a', 'b' ]),
        ],
    ];


    $valuesY = $valuesXX;

    // > commented, should be same object
    // $valuesY[ 17 ][ 0 ] = clone $valuesY[ 17 ][ 0 ];
    $valuesY[ 17 ][ 1 ] = clone $valuesY[ 17 ][ 1 ];
    $valuesY[ 18 ][ 0 ] = clone $valuesY[ 18 ][ 0 ];
    $valuesY[ 18 ][ 1 ] = clone $valuesY[ 18 ][ 1 ];
    $valuesY[ 18 ][ 2 ] = clone $valuesY[ 18 ][ 2 ];
    $valuesY[ 18 ][ 3 ] = clone $valuesY[ 18 ][ 3 ];
    $valuesY[ 19 ][ 0 ] = clone $valuesY[ 19 ][ 0 ];
    $valuesY[ 19 ][ 1 ] = clone $valuesY[ 19 ][ 1 ];
    $valuesY[ 19 ][ 2 ] = clone $valuesY[ 19 ][ 2 ];
    $valuesY[ 19 ][ 3 ] = clone $valuesY[ 19 ][ 3 ];

    $valuesY = array_merge(...$valuesY);

    $theCmp = \Gzhegow\Lib\Lib::cmp();
    $theDebug = \Gzhegow\Lib\Lib::debug();

    $fnCmpName = null;
    $fnCmpSizeName = null;

    $fnCmp = $theCmp->fnCompareValues(
        _CMP_MODE_TYPE_CAST_OR_CONTINUE | _CMP_MODE_DATE_VS_SEC,
        _CMP_RESULT_NAN_RETURN,
        [ &$fnCmpName ]
    );
    $fnCmpSize = $theCmp->fnCompareSizes(
        _CMP_MODE_TYPE_CAST_OR_CONTINUE | _CMP_MODE_DATE_VS_SEC,
        _CMP_RESULT_NAN_RETURN,
        [ &$fnCmpSizeName ]
    );

    // $dumpPath = __DIR__ . '/var/dump/fn_compare_tables.txt';
    // if (is_file($dumpPath)) unlink($dumpPath);

    $xi = 0;
    foreach ( $valuesXX as $i => $valuesX ) {
        $table = [];
        $tableSize = [];
        foreach ( $valuesX as $x ) {
            $xKey = "A@{$xi} | " . $theDebug->value($x);

            $yi = 0;
            foreach ( $valuesY as $y ) {
                $yKey = "B@{$yi} | " . $theDebug->value($y);

                $result = $fnCmp($x, $y);
                $resultSize = $fnCmpSize($x, $y);

                $row = $yKey;
                $col = $xKey;

                $table[ $row ][ $col ] = "{$result} ? {$fnCmpName}";
                $tableSize[ $row ][ $col ] = "{$resultSize} ? {$fnCmpSizeName}";

                $yi++;
            }

            $xi++;
        }

        // $content = \Gzhegow\Lib\Lib::debug()->print_table($table, 1);
        // file_put_contents($dumpPath, $content . PHP_EOL . PHP_EOL, FILE_APPEND);

        // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
        // dd(\Gzhegow\Lib\Lib::debug()->print_table($tableSize, 1));
        echo md5(serialize($table)) . PHP_EOL;
        echo md5(serialize($tableSize)) . PHP_EOL;
        echo PHP_EOL;
    }
    unset($table);
};
_assert_stdout($fn, [], '
"[ CmpModule ]"

a75fe2dc510d4d1559dc93fb294f34aa
a75fe2dc510d4d1559dc93fb294f34aa

5369c1754322243e0d39af9ca563a057
1a2482d0414f29d1683971e5c8dab6e4

8d9bae46cb2aace21295674221edfcb7
a8d7f3fceddedc5b6c99e9cc87af036f

d2de73e611f4f46fb1d715065aaf8d66
77b541afdeeaff652b8999436cf75759

2a606361aa2625a0fd3354cd358bb1b0
fdeb5168c373f3b5a1be824905f96160

28e16009f9953574492e4866b41b7449
d8eabf258b3f01fc8544bd704edb0bb9

31c1802f97ccaadea39acea0382ef677
e9a376f16d62566fd93fc1b26ae1bd81

2232c4d1fae52a4dc66c63fb8dfddb6c
02a63e2497606a2dc132b37101d94b3b

09dedf3c01fe5ebfcb96671acb31ca43
767bb988c97cb93babf9e80f699a9bd6

7015ffc6caa649fe14189019ac29580a
d5127e6a0847dba84ca613b24882f603

56c446ad377af19cb14a25902bfdca77
8ac08583f3dbac0847dd7b25c0bd8dff

1dbf9ffe899df6e9ddc5655f71940210
3d10de1dd12fd826707288485fe9f7c6

4454bfc6a3902546c5fb4cdced945889
9064209c0fc1996a6c87ffe7cd2d1c2b

91f4324dd6d0a448482eae00926dd357
39358eed96f6a4030cbc478618e62144

8caf8b71107f20440ebc4a67965537e3
2cfbe39b15c3e9f9206028460234061f

1943a9c0529911fdf4f309a1ad39b7d2
961de1b52b86369f95416f4ecdb5f64a

7706e5fa73d93301a34aa27afb208d74
8d9ab134e85ebf5eb30427b479ea6e04

7995349e9b20ca614c6fbb1492eeca91
2617154af36cef999b61bfc5710ff869

e6eced782e89a529ae365f57b4de2a00
ffcfeda232c9c48e5dad8570571d482f

76b1ee3007109fec8adbca625357ed7a
ee3566d740d04509720c1c5e32c48188
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
    $src = 'hello world!';
    foreach ( $algos as $algo ) {
        $binary = false;
        $enc = \Gzhegow\Lib\Lib::crypt()->hash($algo, $src, $binary);
        $status = \Gzhegow\Lib\Lib::crypt()->hash_equals($enc, $algo, $src, $binary);
        _print($src, $enc, $status);

        $binary = true;
        $enc = \Gzhegow\Lib\Lib::crypt()->hash($algo, $src, $binary);
        $status = \Gzhegow\Lib\Lib::crypt()->hash_equals($enc, $algo, $src, $binary);
        _print($src, $enc, $status);

        echo PHP_EOL;
    }


    $src = 0;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '01');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01');
    _print($src, $enc, $dec);

    $src = 3;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '01');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01');
    _print($src, $enc, $dec);

    $src = 0;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '01234567');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01234567');
    _print($src, $enc, $dec);

    $src = 15;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '01234567');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01234567');
    _print($src, $enc, $dec);

    $src = 0;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '0123456789ABCDEF');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '0123456789ABCDEF');
    _print($src, $enc, $dec);

    $src = 31;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '0123456789ABCDEF');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '0123456789ABCDEF');
    _print($src, $enc, $dec);

    echo PHP_EOL;


    $oneBased = false;
    $src = 0;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    _print($src, $enc, $dec);

    $oneBased = false;
    $src = 10;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    _print($src, $enc, $dec);

    $oneBased = false;
    $src = 25;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    _print($src, $enc, $dec);

    $oneBased = false;
    $src = 26;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    _print($src, $enc, $dec);

    echo PHP_EOL;


    $oneBased = true;
    $src = 0;
    $e = null;
    try {
        $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    }
    catch ( \Throwable $e ) {
    }
    _print($src, '[ CATCH ] ' . $e->getMessage());

    $oneBased = true;
    $src = 10;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    _print($src, $enc, $dec);

    $oneBased = true;
    $src = 26;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    _print($src, $enc, $dec);

    $oneBased = true;
    $src = 27;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $oneBased);
    _print($src, $enc, $dec);

    echo PHP_EOL;


    $src = '2147483647';
    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($src, '0123456789', '0123456789');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase('9223372036854775807', '0123456789', '0123456789');
    _print($src, $enc, $dec);

    $src = '2147483647';
    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($src, '0123456789abcdefghijklmnopqrstuvwxyz', '0123456789');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($enc, '0123456789', '0123456789abcdefghijklmnopqrstuvwxyz');
    _print($src, $enc, $dec);

    $src = '9223372036854775807';
    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($src, '0123456789abcdefghijklmnopqrstuvwxyz', '0123456789');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($enc, '0123456789', '0123456789abcdefghijklmnopqrstuvwxyz');
    _print($src, $enc, $dec);

    echo PHP_EOL;


    $enc = [];
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('1', '01');
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('11', '0123');
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('111', '01234567');
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('1111', '0123456789ABCDEF');
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('11111', '0123456789ABCDEFGHIJKLMNOPQRSTUV');
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('111111', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/');
    _print(...$enc);
    echo PHP_EOL;


    $src = [ '你' ];
    $enc = \Gzhegow\Lib\Lib::crypt()->text2bin($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->bin2text($enc);
    _print_array($src);
    _print_array($enc);
    _print_array($dec);
    echo PHP_EOL;

    $src = [ '你好' ];
    $enc = \Gzhegow\Lib\Lib::crypt()->text2bin($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->bin2text($enc);
    _print_array($src);
    _print_array($enc);
    _print_array($dec);
    echo PHP_EOL;


    echo PHP_EOL;


    $src = 5678;
    $bin = decbin($src);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2binbase($bin, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    $dec = \Gzhegow\Lib\Lib::crypt()->binbase2bin($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    $res = bindec($dec);
    _print($src, $bin, $enc, $dec, $res);
    echo PHP_EOL;

    $src = [ 'hello' ];
    $bin = \Gzhegow\Lib\Lib::crypt()->text2bin($src);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2base($bin, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    $dec = \Gzhegow\Lib\Lib::crypt()->base2bin($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    $res = implode('', array_map('chr', array_map('bindec', $dec)));
    _print_array($src);
    _print_array($bin);
    _print($enc);
    _print_array($dec);
    _print($res);
    echo PHP_EOL;


    echo PHP_EOL;


    $src = 'HELLO';
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_it($src);
    $enc = implode('', iterator_to_array($gen));
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_it($enc);
    $dec = implode('', iterator_to_array($gen));
    _print($src, $enc, $dec);
    echo PHP_EOL;


    $src = "hello";
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    _print($src, $enc, $dec);

    $src = "\x00\x00\x01\x00\xFF";
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    _print($src, $enc, $dec);

    $src = "你好";
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    _print($src, $enc, $dec);

    echo PHP_EOL;


    $src = "hello";
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    _print($src, $enc, $dec);

    $src = "\x00\x00\x01\x00\xFF";
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    _print($src, $enc, $dec);

    $src = '你好';
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    _print($src, $enc, $dec);
};
_assert_stdout($fn, [], '
"[ CryptModule ]"

"hello world!" | "b034fff2" | TRUE
"hello world!" | "b`°4ÿò`" | TRUE

"hello world!" | "b79584fd" | TRUE
"hello world!" | "b`·•„ý`" | TRUE

"hello world!" | "fc3ff98e8c6a0d3087d515c0473f8677" | TRUE
"hello world!" | "b`ü?ùŽŒj\r0‡Õ\x15ÀG?†w`" | TRUE

"hello world!" | "430ce34d020724ed75a196dfc2ad67c77772d169" | TRUE
"hello world!" | "b`C\x0CãM\x02\x07$íu¡–ßÂ­gÇwrÑi`" | TRUE

"hello world!" | "7509e5bda0c762d2bac7f90d758b5b2263fa01ccbc542ab5e3df163be08e6ca9" | TRUE
"hello world!" | "b`u\tå½ ÇbÒºÇù\ru‹[\"cú\x01Ì¼T*µãß\x16;àŽl©`" | TRUE

0 | "0" | "0"
3 | "11" | "3"
0 | "0" | "0"
15 | "17" | "15"
0 | "0" | "0"
31 | "1F" | "31"

0 | "A" | "0"
10 | "K" | "10"
25 | "Z" | "25"
26 | "BA" | "26"

0 | "[ CATCH ] The `decInteger` should be greater than zero due to `oneBasedTo` is set to TRUE"
10 | "J" | "10"
26 | "Z" | "26"
27 | "AA" | "27"

"2147483647" | "2147483647" | "9223372036854775807"
"2147483647" | "zik0zj" | "2147483647"
"9223372036854775807" | "1y2p0ij32e8e7" | "9223372036854775807"

"1" | "3" | "7" | "F" | "V" | "/"

[ "你" ]
[ "111001001011110110100000" ]
[ "你" ]

[ "你好" ]
[ "111001001011110110100000", "111001011010010110111101" ]
[ "你", "好" ]


5678 | "1011000101110" | "uYB" | "0001011000101110" | 5678

[ "hello" ]
[ "01101000", "01100101", "01101100", "01101100", "01101111" ]
"aGVsbG8"
[ "01101000", "01100101", "01101100", "01101100", "01101111" ]
"hello"


"HELLO" | "SEVMTE8=" | "HELLO"

"hello" | "Cn8eVZg" | "hello"
"b`\x00\x00\x01\x00ÿ`" | "11LZL" | "b`\x00\x00\x01\x00ÿ`"
"你好" | "2xuZUfBKa" | "你好"

"hello" | "7tQLFHz" | "hello"
"b`\x00\x00\x01\x00ÿ`" | "00H79" | "b`\x00\x00\x01\x00ÿ`"
"你好" | "19PqtKE1t" | "你好"
');


// >>> TEST
// > тесты DateModule
$fn = function () {
    _print('[ DateModule ]');
    echo PHP_EOL;


    $before = date_default_timezone_get();
    date_default_timezone_set('UTC');


    $status = \Gzhegow\Lib\Lib::date()->type_timezone($dateTimezone, '+0100');
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone($dateTimezone, 'EET');
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone($dateTimezone, 'Europe/Minsk');
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone($dateTimezone, new \DateTimeZone('UTC'));
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone($dateTimezone, new \DateTime('now', new \DateTimeZone('UTC')));
    _print($status, $dateTimezone);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_timezone_offset($dateTimezone, '+0100');
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_offset($dateTimezone, new \DateTimeZone('+0100'));
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_offset($dateTimezone, new \DateTime('now', new \DateTimeZone('+0100')));
    _print($status, $dateTimezone);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_timezone_abbr($dateTimezone, 'EET');
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_abbr($dateTimezone, new \DateTimeZone('EET'));
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_abbr($dateTimezone, new \DateTime('now', new \DateTimeZone('EET')));
    _print($status, $dateTimezone);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_timezone_name($dateTimezone, 'Europe/Minsk');
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_name($dateTimezone, new \DateTimeZone('Europe/Minsk'));
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_name($dateTimezone, new \DateTime('now', new \DateTimeZone('Europe/Minsk')));
    _print($status, $dateTimezone);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_timezone_nameabbr($dateTimezone, 'EET');
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_nameabbr($dateTimezone, 'Europe/Minsk');
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_nameabbr($dateTimezone, new \DateTimeZone('EET'));
    _print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_nameabbr($dateTimezone, new \DateTime('now', new \DateTimeZone('Europe/Minsk')));
    _print($status, $dateTimezone);
    echo PHP_EOL;

    echo PHP_EOL;


    $status = \Gzhegow\Lib\Lib::date()->type_interval($dateInterval, 'P1D');
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval($dateInterval, 'P1.5D');
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval($dateInterval, '+100 seconds');
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval($dateInterval, new \DateInterval('P1D'));
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval($dateInterval, \DateInterval::createFromDateString('+100 seconds'));
    _print($status, $dateInterval);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_interval_duration($dateInterval, 'P1D');
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_duration($dateInterval, 'P1.5D');
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_duration($dateInterval, new \DateInterval('P1D'));
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_duration($dateInterval, \DateInterval::createFromDateString('+100 seconds'));
    _print($status, $dateInterval);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_interval_datestring($dateInterval, '+100 seconds');
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_datestring($dateInterval, new \DateInterval('P1D'));
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_datestring($dateInterval, \DateInterval::createFromDateString('+100 seconds'));
    _print($status, $dateInterval);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_interval_microtime($dateInterval, '123.456');
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_microtime($dateInterval, new \DateInterval('P1D'));
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_microtime($dateInterval, \DateInterval::createFromDateString('+100 seconds'));
    _print($status, $dateInterval);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_interval_ago($dateInterval, new \DateTime('tomorrow midnight'), new \DateTime('now midnight'));
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_ago($dateInterval, new \DateInterval('P1D'));
    _print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_ago($dateInterval, \DateInterval::createFromDateString('+100 seconds'));
    _print($status, $dateInterval);
    echo PHP_EOL;

    echo PHP_EOL;


    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject, '1970-01-01 midnight');
    $dateAtomString1 = $dateObject->format(DATE_ATOM);
    _print($status, $dateObject);

    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject2, $dateObject);
    $dateAtomString2 = $dateObject2->format(DATE_ATOM);
    _print($status, $dateObject2, $dateAtomString1 === $dateAtomString2);

    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject3, $dateAtomString1);
    $dateAtomString3 = $dateObject3->format(DATE_ATOM);
    _print($status, $dateObject3, $dateAtomString1 === $dateAtomString3);
    echo PHP_EOL;


    $status = \Gzhegow\Lib\Lib::date()->type_idate($dateImmutableObject, '1970-01-01 midnight');
    $dateAtomString1 = $dateImmutableObject->format(DATE_ATOM);
    _print($status, $dateImmutableObject);

    $status = \Gzhegow\Lib\Lib::date()->type_idate($dateImmutableObject2, $dateObject);
    $dateAtomString2 = $dateImmutableObject2->format(DATE_ATOM);
    _print($status, $dateImmutableObject2, $dateAtomString1 === $dateAtomString2);

    $status = \Gzhegow\Lib\Lib::date()->type_idate($dateImmutableObject3, $dateAtomString1);
    $dateAtomString3 = $dateImmutableObject3->format(DATE_ATOM);
    _print($status, $dateImmutableObject3, $dateAtomString1 === $dateAtomString3);
    echo PHP_EOL;


    $status = \Gzhegow\Lib\Lib::date()->type_date($dateObject, '1970-01-01 midnight');
    $dateAtomString1 = $dateObject->format(DATE_ATOM);
    _print($status, $dateObject);

    $status = \Gzhegow\Lib\Lib::date()->type_date($dateObject2, $dateObject);
    $dateAtomString2 = $dateObject2->format(DATE_ATOM);
    _print($status, $dateObject2, $dateAtomString1 === $dateAtomString2);

    $status = \Gzhegow\Lib\Lib::date()->type_idate($dateImmutableObject, $from = $dateObject);
    $dateAtomString3 = $dateImmutableObject->format(DATE_ATOM);
    _print($status, $dateImmutableObject, $dateAtomString1 === $dateAtomString3);

    $status = \Gzhegow\Lib\Lib::date()->type_date($dateImmutableObject2, $dateImmutableObject);
    $dateAtomString4 = $dateImmutableObject2->format(DATE_ATOM);
    _print($status, $dateImmutableObject2, $dateAtomString1 === $dateAtomString4);
    echo PHP_EOL;


    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject1, '1970-01-01 midnight');
    $dateAtomString = $dateObject1->format(DATE_ATOM);
    _print($status, $dateObject1);

    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject2, '1970-01-01 midnight', 'EET');
    $dateAtomString2 = $dateObject2->format(DATE_ATOM);
    _print($status, $dateObject2, $dateAtomString !== $dateAtomString2);
    echo PHP_EOL;

    echo PHP_EOL;


    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject, '1970-01-01 12:34:56');
    _print($status, $dateObject);
    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject, '1970-01-01 12:34:56.456');
    _print($status, $dateObject);
    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject, '1970-01-01 12:34:56.456789');
    _print($status, $dateObject);
    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject, '1970-01-01 12:34:56.456789', 'EET');
    _print($status, $dateObject);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz($result, '1970-01-01 12:34:56 +0100');
    _print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz($result, '1970-01-01 12:34:56.456 EET');
    _print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz($result, '1970-01-01 12:34:56.456789 Europe/Minsk');
    _print($status, $result);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_adate_formatted($result, 'Y-m-d H:i:s O', '1970-01-01 00:00:00 +0100');
    _print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_formatted($result, 'Y-m-d H:i:s T', '1970-01-01 00:00:00 EET');
    _print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_formatted($result, 'Y-m-d H:i:s e', '1970-01-01 00:00:00 Europe/Minsk');
    _print($status, $result);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz_formatted($result, 'Y-m-d H:i:s O', '1970-01-01 00:00:00 +0100');
    _print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz_formatted($result, 'Y-m-d H:i:s T', '1970-01-01 00:00:00 EET');
    _print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz_formatted($result, 'Y-m-d H:i:s e', '1970-01-01 00:00:00 Europe/Minsk');
    _print($status, $result);
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::date()->type_adate_microtime($result, '0');
    _print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_microtime($result, '123');
    _print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_microtime($result, '123.456');
    _print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_microtime($result, '123.456', 'EET');
    _print($status, $result);
    echo PHP_EOL;


    date_default_timezone_set($before);
};
_assert_stdout($fn, [], '
"[ DateModule ]"

TRUE | { object # DateTimeZone # "+01:00" }
TRUE | { object # DateTimeZone # "EET" }
TRUE | { object # DateTimeZone # "Europe/Minsk" }
TRUE | { object # DateTimeZone # "UTC" }
TRUE | { object # DateTimeZone # "UTC" }

TRUE | { object # DateTimeZone # "+01:00" }
TRUE | { object # DateTimeZone # "+01:00" }
TRUE | { object # DateTimeZone # "+01:00" }

TRUE | { object # DateTimeZone # "EET" }
TRUE | { object # DateTimeZone # "EET" }
TRUE | { object # DateTimeZone # "EET" }

TRUE | { object # DateTimeZone # "Europe/Minsk" }
TRUE | { object # DateTimeZone # "Europe/Minsk" }
TRUE | { object # DateTimeZone # "Europe/Minsk" }

TRUE | { object # DateTimeZone # "EET" }
TRUE | { object # DateTimeZone # "Europe/Minsk" }
TRUE | { object # DateTimeZone # "EET" }
TRUE | { object # DateTimeZone # "Europe/Minsk" }


TRUE | { object # DateInterval # "P1D" }
TRUE | { object # DateInterval # "P1DT12H" }
TRUE | { object # DateInterval # "PT100S" }
TRUE | { object # DateInterval # "P1D" }
TRUE | { object # DateInterval # "PT100S" }

TRUE | { object # DateInterval # "P1D" }
TRUE | { object # DateInterval # "P1DT12H" }
TRUE | { object # DateInterval # "P1D" }
TRUE | { object # DateInterval # "PT100S" }

TRUE | { object # DateInterval # "PT100S" }
TRUE | { object # DateInterval # "P1D" }
TRUE | { object # DateInterval # "PT100S" }

TRUE | { object # DateInterval # "PT2M3.456S" }
TRUE | { object # DateInterval # "P1D" }
TRUE | { object # DateInterval # "PT100S" }

TRUE | { object # DateInterval # "P1D" }
TRUE | { object # DateInterval # "P1D" }
TRUE | { object # DateInterval # "PT100S" }


TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+00:00" }
TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+00:00" } | TRUE
TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+00:00" } | TRUE

TRUE | { object # DateTimeImmutable # "1970-01-01T00:00:00.000000+00:00" }
TRUE | { object # DateTimeImmutable # "1970-01-01T00:00:00.000000+00:00" } | TRUE
TRUE | { object # DateTimeImmutable # "1970-01-01T00:00:00.000000+00:00" } | TRUE

TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+00:00" }
TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+00:00" } | TRUE
TRUE | { object # DateTimeImmutable # "1970-01-01T00:00:00.000000+00:00" } | TRUE
TRUE | { object # DateTimeImmutable # "1970-01-01T00:00:00.000000+00:00" } | TRUE

TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+00:00" }
TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+02:00" } | TRUE


TRUE | { object # DateTime # "1970-01-01T12:34:56.000000+00:00" }
TRUE | { object # DateTime # "1970-01-01T12:34:56.456000+00:00" }
TRUE | { object # DateTime # "1970-01-01T12:34:56.456789+00:00" }
TRUE | { object # DateTime # "1970-01-01T12:34:56.456789+02:00" }

TRUE | { object # DateTime # "1970-01-01T12:34:56.000000+01:00" }
TRUE | { object # DateTime # "1970-01-01T12:34:56.456000+02:00" }
TRUE | { object # DateTime # "1970-01-01T12:34:56.456789+03:00" }

TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+01:00" }
TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+02:00" }
TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+03:00" }

TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+01:00" }
TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+02:00" }
TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+03:00" }

TRUE | { object # DateTime # "1970-01-01T00:00:00.000000+00:00" }
TRUE | { object # DateTime # "1970-01-01T00:02:03.000000+00:00" }
TRUE | { object # DateTime # "1970-01-01T00:02:03.000456+00:00" }
TRUE | { object # DateTime # "1970-01-01T02:02:03.000456+02:00" }
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
    _print_array_multiline($diffLines);
    echo PHP_EOL;


    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple2\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry2\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry2";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines);
    echo PHP_EOL;


    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "fig\napple\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\nfig\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry\nfig";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines);
    echo PHP_EOL;


    echo PHP_EOL;


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "banana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines);
    echo PHP_EOL;

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    _print($isDiff);
    _print_array_multiline($diffLines);
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
###
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]
###


TRUE
###
[
  "[ 1 ] --- > apple2",
  "[ 1 ] +++ > apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]
###

TRUE
###
[
  "apple",
  "banana",
  "[ 3 ] --- > cherry2",
  "[ 3 ] +++ > cherry",
  "damson",
  "elderberry"
]
###

TRUE
###
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "[ 5 ] --- > elderberry2",
  "[ 5 ] +++ > elderberry"
]
###


TRUE
###
[
  "[ 1 ] --- > fig",
  "apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]
###

TRUE
###
[
  "apple",
  "banana",
  "cherry",
  "[ 4 ] --- > fig",
  "damson",
  "elderberry"
]
###

TRUE
###
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "elderberry",
  "[ 6 ] --- > fig"
]
###


TRUE
###
[
  "[ 1 ] +++ > apple",
  "banana",
  "cherry",
  "damson",
  "elderberry"
]
###

TRUE
###
[
  "apple",
  "banana",
  "[ 3 ] +++ > cherry",
  "damson",
  "elderberry"
]
###

TRUE
###
[
  "apple",
  "banana",
  "cherry",
  "damson",
  "[ 5 ] +++ > elderberry"
]
###


NULL
FALSE
1
1.1
"string"
[  ]
{ object # stdClass }
{ resource(opened) # stream }

[ "{ array(3) }", "{ array(3) }", "{ array(3) }" ]
[ [ 1, "apple", "{ object # stdClass }" ], [ 2, "apples", "{ object # stdClass }" ], [ 1.5, "apples", "{ object # stdClass }" ] ]

###
[
  "{ array(3) }",
  "{ array(3) }",
  "{ array(3) }"
]
###
###
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
###
');


// >>> TEST
// > тесты EscapeModule
$fn = function () {
    _print('[ EscapeModule ]');
    echo PHP_EOL;


    $params = [];
    $sqlIn = \Gzhegow\Lib\Lib::escape()->sql_in($params, 'AND `user_id`', [ 1, 2, 3 ]);
    _print($sqlIn);
    _print_array($params);

    echo PHP_EOL;


    $params = [];
    $sqlIn = \Gzhegow\Lib\Lib::escape()->sql_in($params, 'AND `user_id`', [ 1, 2, 3 ], 'user_id');
    _print($sqlIn);
    _print_array($params);

    echo PHP_EOL;


    $sqlLike = \Gzhegow\Lib\Lib::escape()->sql_like_quote('Hello, _user_! How are you today, in percents (%)?', '\\');
    _print($sqlLike);

    $sqlLike = \Gzhegow\Lib\Lib::escape()->sql_like_escape(
        'AND `search`', 'ILIKE',
        'Hello, _user_! How are you today, in percents (%)?'
    );
    _print($sqlLike);

    $sqlLike = \Gzhegow\Lib\Lib::escape()->sql_like_escape(
        'AND `name`', 'LIKE',
        [ '__' ], 'user%%__', [ '%' ]
    );
    _print($sqlLike);

    echo PHP_EOL;
};
_assert_stdout($fn, [], '
"[ EscapeModule ]"

"AND `user_id` IN (?, ?, ?)"
[ 1, 2, 3 ]

"AND `user_id` IN (:user_id0, :user_id1, :user_id2)"
[ ":user_id0" => 1, ":user_id1" => 2, ":user_id2" => 3 ]

"Hello, \_user\_! How are you today, in percents (\%)?"
"AND `search` ILIKE \"Hello, \_user\_! How are you today, in percents (\%)?\""
"AND `name` LIKE \"__user\%\%\_\_%\""
');


// >>> TEST
// > тесты FormatModule
$fn = function () {
    _print('[ FormatModule ]');
    echo PHP_EOL;


    $enc = \Gzhegow\Lib\Lib::format()->bytes_encode($src = 1024 * 1024);
    _print($enc);

    $dec = \Gzhegow\Lib\Lib::format()->bytes_decode($enc);
    _print($dec, $src === $dec);

    echo PHP_EOL;


    [ $csv, $bytes ] = \Gzhegow\Lib\Lib::format()->csv_encode_rows([ [ 'col1', 'col2' ], [ 'val1', 'val2' ] ]);
    _print($csv);
    _print($bytes);

    echo PHP_EOL;


    [ $csv, $bytes ] = \Gzhegow\Lib\Lib::format()->csv_encode_row([ 'col1', 'col2' ]);
    _print($csv);
    _print($bytes);
};
_assert_stdout($fn, [], '
"[ FormatModule ]"

"1MB"
1048576 | TRUE

"col1;col2\n
val1;val2\n
"
20

"col1;col2\n
"
10
');


// >>> TEST
// > тесты FsModule
$fn = function () {
    _print('[ FsModule ]');
    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(
        __DIR__ . '/var/1/1/1/1.txt', '123',
        [ FILE_APPEND ], [ 0775, true ]
    );
    _print($result);

    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(
        __DIR__ . '/var/1/1/1.txt', '123',
        [ FILE_APPEND ], [ 0775, true ]
    );
    _print($result);

    $result = \Gzhegow\Lib\Lib::fs()->file_put_contents(
        __DIR__ . '/var/1/1.txt', '123',
        [ FILE_APPEND ], [ 0775, true ]
    );
    _print($result);

    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::fs()->file_get_contents(
        __DIR__ . '/var/1/1/1/1.txt',
        []
    );
    _print($result);


    foreach (
        \Gzhegow\Lib\Lib::fs()->dir_walk_it(
            __DIR__ . '/var/1',
            [], []
        )
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
// > тесты JsonModule
$fn = function () {
    _print('[ JsonModule ]');
    echo PHP_EOL;


    $json1 = '{"hello": "world"}';
    $json2 = '
        {
            "hello": "world"
        }
    ';

    $jsonWithComment1 = "[1,/* 2 */,3]";
    $jsonWithComment2 = '
        {
            "hello": "world",
            # "foo1": "bar1",
            // "foo2": "bar2",
            /* "foo3": "bar3" */
        }
    ';


    $e = null;
    try {
        $result = \Gzhegow\Lib\Lib::json()->json_decode(null, true, []);
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());

    $e = null;
    try {
        $result = \Gzhegow\Lib\Lib::json()->jsonc_decode(null, true, []);
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());

    $result = \Gzhegow\Lib\Lib::json()->json_decode(null, true, [ null ]);
    _print($result);

    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode(null, true, [ null ]);
    _print($result);

    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::json()->json_decode($json1, true, []);
    _print($result);

    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode($json1, true, []);
    _print($result);

    $result = \Gzhegow\Lib\Lib::json()->json_decode($json2, true, []);
    _print($result);

    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode($json2, true, []);
    _print($result);

    echo PHP_EOL;


    $e = null;
    try {
        $result = \Gzhegow\Lib\Lib::json()->json_decode($jsonWithComment1, true, []);
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());

    $e = null;
    try {
        $result = \Gzhegow\Lib\Lib::json()->json_decode($jsonWithComment2, true, []);
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());

    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode($jsonWithComment1, true, []);
    _print($result);

    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode($jsonWithComment2, true, []);
    _print($result);

    echo PHP_EOL;


    $e = null;
    try {
        $result = \Gzhegow\Lib\Lib::json()->json_encode(null, [], false);
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());

    $result = \Gzhegow\Lib\Lib::json()->json_encode(null, [ null ], false);
    _print($result);

    $result = \Gzhegow\Lib\Lib::json()->json_encode(null, [], true);
    _print($result);

    echo PHP_EOL;


    $e = null;
    try {
        \Gzhegow\Lib\Lib::json()->json_encode($value = NAN);
    }
    catch ( \Throwable $e ) {
    }
    _print('[ CATCH ] ' . $e->getMessage());

    $result = \Gzhegow\Lib\Lib::json()->json_encode(
        $value = NAN,
        $fallback = [ "NAN" ]
    );
    _print($result);

    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::json()->json_encode("привет");
    _print($result);

    $result = \Gzhegow\Lib\Lib::json()->json_print("привет");
    _print($result);
};
_assert_stdout($fn, [], '
"[ JsonModule ]"

"[ CATCH ] Unable to `json_decode`"
"[ CATCH ] Unable to `jsonc_decode`"
NULL
NULL

[ "hello" => "world" ]
[ "hello" => "world" ]
[ "hello" => "world" ]
[ "hello" => "world" ]

"[ CATCH ] Unable to `json_decode`"
"[ CATCH ] Unable to `json_decode`"
[ 1, 2, 3 ]
[ "hello" => "world", "foo1" => "bar1", "foo2" => "bar2", "foo3" => "bar3" ]

"[ CATCH ] Unable to `json_encode`"
NULL
"null"

"[ CATCH ] Unable to `json_encode`"
"NAN"

"\"\u043f\u0440\u0438\u0432\u0435\u0442\""
"\"привет\""
');


// >>> TEST
// > тесты NetModule
$fn = function () {
    _print('[ NetModule ]');
    echo PHP_EOL;

    $ipV4List = [
        '192.168.1.10',
        '192.168.2.10',
        '10.0.0.1',
        '10.0.1.1',
    ];
    $subnetV4List = [
        '192.168.1.10/32',
        '192.168.1.10/24',
        '192.168.1.10/16',
        '10.0.0.1/32',
        '10.0.0.1/24',
        '10.0.0.1/16',
    ];

    foreach ( $ipV4List as $i => $ip ) {
        \Gzhegow\Lib\Lib::net()->type_address_ip_v4($addressIpV4, $ip);

        foreach ( $subnetV4List as $ii => $subnet ) {
            \Gzhegow\Lib\Lib::net()->type_subnet_v4($subnetV4, $subnet);

            $status1 = \Gzhegow\Lib\Lib::net()->is_ip_in_subnet($addressIpV4, $subnetV4);
            $status2 = \Gzhegow\Lib\Lib::net()->is_ip_in_subnet_v4($addressIpV4, $subnetV4);

            _print($ip, $subnet, $status1, $status1 === $status2);
        }

        echo PHP_EOL;
    }

    $ipV6List = [
        '2001:db8::',
        '2001:db8::1',
        '2001:db8:0:1::1',
        'fe80::1',
        '::1',
    ];
    $subnetV6List = [
        '2001:db8::/128',
        '2001:db8::/64',
        'fe80::/10',
        '::1/128',
        '::/0',
    ];

    foreach ( $ipV6List as $i => $ip ) {
        \Gzhegow\Lib\Lib::net()->type_address_ip_v6($addressIpV6, $ip);

        foreach ( $subnetV6List as $ii => $subnet ) {
            \Gzhegow\Lib\Lib::net()->type_subnet_v6($subnetV6, $subnet);

            $status1 = \Gzhegow\Lib\Lib::net()->is_ip_in_subnet($addressIpV6, $subnetV6);
            $status2 = \Gzhegow\Lib\Lib::net()->is_ip_in_subnet_v6($addressIpV6, $subnetV6);

            _print($ip, $subnet, $status1, $status1 === $status2);
        }

        echo PHP_EOL;
    }
};
_assert_stdout($fn, [], '
"[ NetModule ]"

"192.168.1.10" | "192.168.1.10/32" | TRUE | TRUE
"192.168.1.10" | "192.168.1.10/24" | TRUE | TRUE
"192.168.1.10" | "192.168.1.10/16" | TRUE | TRUE
"192.168.1.10" | "10.0.0.1/32" | FALSE | TRUE
"192.168.1.10" | "10.0.0.1/24" | FALSE | TRUE
"192.168.1.10" | "10.0.0.1/16" | FALSE | TRUE

"192.168.2.10" | "192.168.1.10/32" | FALSE | TRUE
"192.168.2.10" | "192.168.1.10/24" | FALSE | TRUE
"192.168.2.10" | "192.168.1.10/16" | TRUE | TRUE
"192.168.2.10" | "10.0.0.1/32" | FALSE | TRUE
"192.168.2.10" | "10.0.0.1/24" | FALSE | TRUE
"192.168.2.10" | "10.0.0.1/16" | FALSE | TRUE

"10.0.0.1" | "192.168.1.10/32" | FALSE | TRUE
"10.0.0.1" | "192.168.1.10/24" | FALSE | TRUE
"10.0.0.1" | "192.168.1.10/16" | FALSE | TRUE
"10.0.0.1" | "10.0.0.1/32" | TRUE | TRUE
"10.0.0.1" | "10.0.0.1/24" | TRUE | TRUE
"10.0.0.1" | "10.0.0.1/16" | TRUE | TRUE

"10.0.1.1" | "192.168.1.10/32" | FALSE | TRUE
"10.0.1.1" | "192.168.1.10/24" | FALSE | TRUE
"10.0.1.1" | "192.168.1.10/16" | FALSE | TRUE
"10.0.1.1" | "10.0.0.1/32" | FALSE | TRUE
"10.0.1.1" | "10.0.0.1/24" | FALSE | TRUE
"10.0.1.1" | "10.0.0.1/16" | TRUE | TRUE

"2001:db8::" | "2001:db8::/128" | TRUE | TRUE
"2001:db8::" | "2001:db8::/64" | TRUE | TRUE
"2001:db8::" | "fe80::/10" | FALSE | TRUE
"2001:db8::" | "::1/128" | FALSE | TRUE
"2001:db8::" | "::/0" | TRUE | TRUE

"2001:db8::1" | "2001:db8::/128" | FALSE | TRUE
"2001:db8::1" | "2001:db8::/64" | TRUE | TRUE
"2001:db8::1" | "fe80::/10" | FALSE | TRUE
"2001:db8::1" | "::1/128" | FALSE | TRUE
"2001:db8::1" | "::/0" | TRUE | TRUE

"2001:db8:0:1::1" | "2001:db8::/128" | FALSE | TRUE
"2001:db8:0:1::1" | "2001:db8::/64" | FALSE | TRUE
"2001:db8:0:1::1" | "fe80::/10" | FALSE | TRUE
"2001:db8:0:1::1" | "::1/128" | FALSE | TRUE
"2001:db8:0:1::1" | "::/0" | TRUE | TRUE

"fe80::1" | "2001:db8::/128" | FALSE | TRUE
"fe80::1" | "2001:db8::/64" | FALSE | TRUE
"fe80::1" | "fe80::/10" | TRUE | TRUE
"fe80::1" | "::1/128" | FALSE | TRUE
"fe80::1" | "::/0" | TRUE | TRUE

"::1" | "2001:db8::/128" | FALSE | TRUE
"::1" | "2001:db8::/64" | FALSE | TRUE
"::1" | "fe80::/10" | FALSE | TRUE
"::1" | "::1/128" | TRUE | TRUE
"::1" | "::/0" | TRUE | TRUE
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

    $before = error_reporting(0);
    try {
        $objectDummy->publicDynamicProperty = null;
    }
    catch ( \Throwable $e ) {
    }
    error_reporting($before);

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
    // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
    echo md5(serialize($table)) . PHP_EOL;
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
    // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
    echo md5(serialize($table)) . PHP_EOL;
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

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function_internal($result, $src, null);
        $table4[ $tableRow ][ 'callable_string_function_internal' ] = _value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function_non_internal($result, $src, null);
        $table4[ $tableRow ][ 'callable_string_function_non_internal' ] = _value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_method_static($result, $src, null);
        $table4[ $tableRow ][ 'callable_string_method_static' ] = _value($result);
    }
    // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
    echo md5(serialize($table1)) . PHP_EOL;
    echo md5(serialize($table2)) . PHP_EOL;
    echo md5(serialize($table3)) . PHP_EOL;
    echo md5(serialize($table4)) . PHP_EOL;
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
    // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
    echo md5(serialize($table)) . PHP_EOL;
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

                    $tableCol = _values(' / ', 'callable_string_function', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function($result, $src);
                    $table3[ $tableRow ][ $tableCol ] = _value($result);

                    $tableCol = _values(' / ', 'callable_string_function_internal', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function_internal($result, $src);
                    $table3[ $tableRow ][ $tableCol ] = _value($result);

                    $tableCol = _values(' / ', 'callable_string_function_non_internal', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function_non_internal($result, $src);
                    $table3[ $tableRow ][ $tableCol ] = _value($result);

                    $tableCol = _values(' / ', 'callable_string_method_static', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string_method_static($result, $src, $scope);
                    $table3[ $tableRow ][ $tableCol ] = _value($result);
                }
            }
        }
    }
    // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
    echo md5(serialize($table1)) . PHP_EOL;
    echo md5(serialize($table2)) . PHP_EOL;
    echo md5(serialize($table3)) . PHP_EOL;
    unset($table1);
    unset($table2);
    unset($table3);
    echo PHP_EOL;
};
_assert_stdout($fn, [], '
"[ PhpModule ]"

2f37ec97bf4a8f3de2842a958e72f8ac

3c5b07dea71adcdf1bba77a417215792

bcdbd4da5c1d80d67e8800a4bab352e4
f492869c99c9d5b081a3e6b62c2b0aab
ea13e84bea1e4178af4dfc6b33594718
ee32e6bfadc76c6ffc6ca7383f2ef63e

06ef24bf80b3dbe188d41dabcacb2027

d91da25286bd7e000367a9758699e0ca
cb145079faba3ab6cf451fe9116389ba
3c3e6efcd8ead6feb5fde9b2d9edc105
');


// >>> TEST
// > тесты PregModule
$fn = function () {
    _print('[ PregModule ]');
    echo PHP_EOL;


    $regex = \Gzhegow\Lib\Lib::preg()->preg_quote_ord("Hello, \x00!");
    _print($regex);
    echo PHP_EOL;


    $regex = \Gzhegow\Lib\Lib::preg()->preg_escape('/', '<html>', [ '.*' ], '</html>');
    _print($regex);

    $regex = \Gzhegow\Lib\Lib::preg()->preg_escape_ord(null, '/', '<html>', [ '.*' ], '</html>');
    _print($regex);
};
_assert_stdout($fn, [], '
"[ PregModule ]"

"\x{48}\x{65}\x{6C}\x{6C}\x{6F}\x{2C}\x{20}\x{0}\x{21}"

"/\<html\>.*\<\/html\>/"
"/\x{3C}\x{68}\x{74}\x{6D}\x{6C}\x{3E}.*\x{3C}\x{2F}\x{68}\x{74}\x{6D}\x{6C}\x{3E}/"
');


// >>> TEST
// > тесты RandomModule
$fn = function () {
    _print('[ RandomModule ]');
    echo PHP_EOL;

    $uuid = \Gzhegow\Lib\Lib::random()->uuid();
    $status = \Gzhegow\Lib\Lib::random()->type_uuid($result, $uuid);
    _print(strlen($uuid), $status);

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

36 | TRUE

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
// > тесты SocialModule
$fn = function () {
    _print('[ SocialModule ]');
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::social()->type_email($email, 'example@gmail.com');
    _print($status, $email);
    $status = \Gzhegow\Lib\Lib::social()->type_email($email, 'example@привет.рф');
    _print($status, $email);
    $status = \Gzhegow\Lib\Lib::social()->type_email($email, 'example@привет.рф', $filters = [ 'filter_unicode' ]);
    _print($status, $email);
    try {
        $status = \Gzhegow\Lib\Lib::social()->type_email($email, 'example@привет.рф', $filters = [ 'rfc' ]);
    }
    catch ( \Gzhegow\Lib\Exception\Runtime\ComposerException $e ) {
        _print('[ CATCH ] ' . $e->getMessage());
    }
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::social()->type_email_non_fake($email, 'example@gmail.com');
    _print($status, $email);
    $status = \Gzhegow\Lib\Lib::social()->type_email_non_fake($email, 'example@привет.рф');
    _print($status, $email);
    $status = \Gzhegow\Lib\Lib::social()->type_email_non_fake($email, 'example@привет.рф', $filters = [ 'filter_unicode' ]);
    _print($status, $email);
    try {
        $status = \Gzhegow\Lib\Lib::social()->type_email_non_fake($email, 'example@привет.рф', $filters = [ 'rfc' ]);
    }
    catch ( \Gzhegow\Lib\Exception\Runtime\ComposerException $e ) {
        _print('[ CATCH ] ' . $e->getMessage());
    }
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::social()->type_email_fake($email, 'no-reply@gmail.com');
    _print($status, $email);
    $status = \Gzhegow\Lib\Lib::social()->type_email_fake($email, 'email@example.com');
    _print($status, $email);
    echo PHP_EOL;


    $phones = [
        // > номера BY
        '+375 (29) 676-48-68',
        '+375296764868',
        '375296764868',

        // > номера RU
        '+7 (862) 220-40-16',
        '+78622204016',
        '78622204016',

        // > номера, начинающиеся на 89
        '89123456789',
        '89234567890',
        '89345678901',
        '89456789012',
        '89567890123',

        // > номера, начинающиеся на 9 и имеющие 10 цифр:
        '9123456789',
        '9234567890',
        '9345678901',
        '9456789012',
        '9567890123',

        // > номера мобильных операторов:
        '9012345678', // (МегаФон)
        '9023456789', // (Билайн)
        '9034567890', // (МТС)
        '9045678901', // (Теле2)
        '9056789012', // (МегаФон)
    ];

    $fakePhones = [
        // Фейковые номера для тестирования при разработке
        '+375990000000',
        '+79990000000',
        '+19700101000000',
    ];

    foreach ( $phones as $phone ) {
        $status = \Gzhegow\Lib\Lib::social()->type_phone($result, $phone);
        _print($phone, $status, $result);

        $status = \Gzhegow\Lib\Lib::social()->type_phone_non_fake($result, $phone);
        _print($phone, $status, $result);

        try {
            $status = \Gzhegow\Lib\Lib::social()->type_phone_real($result, $phone, '');
        }
        catch ( \Gzhegow\Lib\Exception\Runtime\ComposerException $e ) {
            _print('[ CATCH ] ' . $e->getMessage());
        }

        $status = \Gzhegow\Lib\Lib::social()->type_tel($result, $phone);
        _print($phone, $status, $result);

        $status = \Gzhegow\Lib\Lib::social()->type_tel_non_fake($result, $phone);
        _print($phone, $status, $result);

        try {
            $status = \Gzhegow\Lib\Lib::social()->type_tel_real($result, $phone, '');
        }
        catch ( \Gzhegow\Lib\Exception\Runtime\ComposerException $e ) {
            _print('[ CATCH ] ' . $e->getMessage());
        }

        echo PHP_EOL;
    }

    $phoneManager = \Gzhegow\Lib\Lib::social()->clonePhoneManager();
    $phoneManager->usePhoneFakeDatelike(true);

    foreach ( $fakePhones as $phone ) {
        $result = $phoneManager->parsePhone($phone);
        _print($phone, $result);

        $result = $phoneManager->parsePhoneFake($phone);
        _print($phone, $result);

        $result = $phoneManager->parseTel($phone);
        _print($phone, $result);

        $result = $phoneManager->parseTelFake($phone);
        _print($phone, $result);

        echo PHP_EOL;
    }

    // $phone = $phones[0];
    //
    // $phoneManager = \Gzhegow\Lib\Lib::social()->phoneManager();
    //
    // $phoneNumberWithoutDetection = $phoneManager->parsePhoneNumber($phone);
    // $phoneNumberWithDetection = $phoneManager->parsePhoneNumber($phone, '');
    //
    // $formatE164 = $phoneManager->formatE164($phone);
    // $formatNational = $phoneManager->formatNational($phone);
    // $formatInternational = $phoneManager->formatInternational($phone);
    // $formatRFC3966 = $phoneManager->formatRFC3966($phone);
    // $formatShort = $phoneManager->formatShort($phone);
    // $formatLong = $phoneManager->formatLong($phone);
    // $getLocationNameForPhone = $phoneManager->getLocationNameForPhone($phone);
    // $getOperatorNameForPhone = $phoneManager->getOperatorNameForPhone($phone);
    // $getTimezonesForPhone = $phoneManager->getTimezonesForPhone($phone);
    //
    // var_dump([
    //     $formatE164,
    //     $formatNational,
    //     $formatInternational,
    //     $formatRFC3966,
    //     $formatShort,
    //     $formatLong,
    //     $getLocationNameForPhone,
    //     $getOperatorNameForPhone,
    //     $getTimezonesForPhone,
    // ]);
};
_assert_stdout($fn, [], '
"[ SocialModule ]"

TRUE | "example@gmail.com"
FALSE | NULL
TRUE | "example@привет.рф"
"[ CATCH ] Please, run following commands: [ composer require egulias/email-validator ]"

TRUE | "example@gmail.com"
FALSE | NULL
TRUE | "example@привет.рф"
"[ CATCH ] Please, run following commands: [ composer require egulias/email-validator ]"

TRUE | "no-reply@gmail.com"
TRUE | "email@example.com"

"+375 (29) 676-48-68" | TRUE | "+375 (29) 676-48-68"
"+375 (29) 676-48-68" | TRUE | "+375 (29) 676-48-68"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"+375 (29) 676-48-68" | TRUE | "+375296764868"
"+375 (29) 676-48-68" | TRUE | "+375296764868"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"+375296764868" | TRUE | "+375296764868"
"+375296764868" | TRUE | "+375296764868"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"+375296764868" | TRUE | "+375296764868"
"+375296764868" | TRUE | "+375296764868"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"375296764868" | TRUE | "375296764868"
"375296764868" | TRUE | "375296764868"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"375296764868" | TRUE | "375296764868"
"375296764868" | TRUE | "375296764868"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"+7 (862) 220-40-16" | TRUE | "+7 (862) 220-40-16"
"+7 (862) 220-40-16" | TRUE | "+7 (862) 220-40-16"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"+7 (862) 220-40-16" | TRUE | "+78622204016"
"+7 (862) 220-40-16" | TRUE | "+78622204016"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"+78622204016" | TRUE | "+78622204016"
"+78622204016" | TRUE | "+78622204016"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"+78622204016" | TRUE | "+78622204016"
"+78622204016" | TRUE | "+78622204016"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"78622204016" | TRUE | "78622204016"
"78622204016" | TRUE | "78622204016"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"78622204016" | TRUE | "78622204016"
"78622204016" | TRUE | "78622204016"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"89123456789" | TRUE | "89123456789"
"89123456789" | TRUE | "89123456789"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"89123456789" | TRUE | "89123456789"
"89123456789" | TRUE | "89123456789"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"89234567890" | TRUE | "89234567890"
"89234567890" | TRUE | "89234567890"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"89234567890" | TRUE | "89234567890"
"89234567890" | TRUE | "89234567890"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"89345678901" | TRUE | "89345678901"
"89345678901" | TRUE | "89345678901"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"89345678901" | TRUE | "89345678901"
"89345678901" | TRUE | "89345678901"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"89456789012" | TRUE | "89456789012"
"89456789012" | TRUE | "89456789012"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"89456789012" | TRUE | "89456789012"
"89456789012" | TRUE | "89456789012"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"89567890123" | TRUE | "89567890123"
"89567890123" | TRUE | "89567890123"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"89567890123" | TRUE | "89567890123"
"89567890123" | TRUE | "89567890123"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"9123456789" | TRUE | "9123456789"
"9123456789" | TRUE | "9123456789"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"9123456789" | TRUE | "9123456789"
"9123456789" | TRUE | "9123456789"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"9234567890" | TRUE | "9234567890"
"9234567890" | TRUE | "9234567890"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"9234567890" | TRUE | "9234567890"
"9234567890" | TRUE | "9234567890"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"9345678901" | TRUE | "9345678901"
"9345678901" | TRUE | "9345678901"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"9345678901" | TRUE | "9345678901"
"9345678901" | TRUE | "9345678901"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"9456789012" | TRUE | "9456789012"
"9456789012" | TRUE | "9456789012"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"9456789012" | TRUE | "9456789012"
"9456789012" | TRUE | "9456789012"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"9567890123" | TRUE | "9567890123"
"9567890123" | TRUE | "9567890123"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"9567890123" | TRUE | "9567890123"
"9567890123" | TRUE | "9567890123"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"9012345678" | TRUE | "9012345678"
"9012345678" | TRUE | "9012345678"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"9012345678" | TRUE | "9012345678"
"9012345678" | TRUE | "9012345678"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"9023456789" | TRUE | "9023456789"
"9023456789" | TRUE | "9023456789"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"9023456789" | TRUE | "9023456789"
"9023456789" | TRUE | "9023456789"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"9034567890" | TRUE | "9034567890"
"9034567890" | TRUE | "9034567890"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"9034567890" | TRUE | "9034567890"
"9034567890" | TRUE | "9034567890"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"9045678901" | TRUE | "9045678901"
"9045678901" | TRUE | "9045678901"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"9045678901" | TRUE | "9045678901"
"9045678901" | TRUE | "9045678901"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"9056789012" | TRUE | "9056789012"
"9056789012" | TRUE | "9056789012"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"
"9056789012" | TRUE | "9056789012"
"9056789012" | TRUE | "9056789012"
"[ CATCH ] Please, run following commands: [ composer require giggsey/libphonenumber-for-php ]"

"+375990000000" | "+375990000000"
"+375990000000" | "+375990000000"
"+375990000000" | "+375990000000"
"+375990000000" | "+375990000000"

"+79990000000" | "+79990000000"
"+79990000000" | "+79990000000"
"+79990000000" | "+79990000000"
"+79990000000" | "+79990000000"

"+19700101000000" | "+19700101000000"
"+19700101000000" | "+19700101000000"
"+19700101000000" | "+19700101000000"
"+19700101000000" | "+19700101000000"
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
    echo PHP_EOL;

    $status = \Gzhegow\Lib\Lib::str()->str_starts('привет', 'ПРИ', true, [ &$substr ]);
    _print($status, $substr);
    $status = \Gzhegow\Lib\Lib::str()->str_ends('приВЕТ', 'вет', true, [ &$substr ]);
    _print($status, $substr);
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->lcrop('азаза_привет_азаза', 'аза'));
    _print(\Gzhegow\Lib\Lib::str()->rcrop('азаза_привет_азаза', 'аза'));
    _print(\Gzhegow\Lib\Lib::str()->crop('азаза_привет_азаза', 'аза'));
    _print(\Gzhegow\Lib\Lib::str()->unlcrop('"привет"', '"'));
    _print(\Gzhegow\Lib\Lib::str()->unrcrop('"привет"', '"'));
    _print(\Gzhegow\Lib\Lib::str()->uncrop('"привет"', '"'));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->str_replace_limit('за', '_', 'а-зазаза-зазаза', 3));
    _print(\Gzhegow\Lib\Lib::str()->str_ireplace_limit('зА', '_', 'а-заЗАза-заЗАза', 3));
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
    _print(\Gzhegow\Lib\Lib::str()->slugger()->translit(' привет мир ', null, [ 'и' ]));
    _print(\Gzhegow\Lib\Lib::str()->slugger()->slug('привет мир'));
    _print(\Gzhegow\Lib\Lib::str()->slugger()->slug('привет мир', ':', [ 'и' ]));
    echo PHP_EOL;

    _print(\Gzhegow\Lib\Lib::str()->inflector()->singularize('users'));
    _print(\Gzhegow\Lib\Lib::str()->inflector()->pluralize('user'));
    echo PHP_EOL;


    $array = [
        'users.name'   => false,
        'users..name'  => false,
        'users...name' => false,
        //
        'users.1.id'   => 1,
        'users.1.name' => 'name1',
        'users.2.id'   => 2,
        'users.2.name' => 'name2',
        'users.3.id'   => 3,
        'users.3.name' => 'name3',
    ];
    $keys = array_keys($array);
    _print_array_multiline(\Gzhegow\Lib\Lib::str()->str_match('users.*.name', $keys));
    _print_array_multiline(\Gzhegow\Lib\Lib::str()->str_match('users.*.name', $keys, '*'));
    _print_array_multiline(\Gzhegow\Lib\Lib::str()->str_match('users.*.name', $keys, '*', '.'));
    echo PHP_EOL;

    $array = [
        "users\x00name"         => false,
        "users\x00\x00name"     => false,
        "users\x00\x00\x00name" => false,
        //
        "users\x001\x00id"      => 1,
        "users\x001\x00name"    => 'name1',
        "users\x002\x00id"      => 2,
        "users\x002\x00name"    => 'name2',
        "users\x003\x00id"      => 3,
        "users\x003\x00name"    => 'name3',
    ];
    $keys = array_keys($array);
    _print_array_multiline(\Gzhegow\Lib\Lib::str()->str_match("users\x00*\x00name", $keys));
    _print_array_multiline($a = \Gzhegow\Lib\Lib::str()->str_match("users\x00*\x00name", $keys, '*'));
    _print_array_multiline(\Gzhegow\Lib\Lib::str()->str_match("users\x00*\x00name", $keys, '*', "\x00"));
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

TRUE | "вет"
TRUE | "при"

"за_привет_азаза"
"азаза_привет_аз"
"за_привет_аз"
"\"привет\""
"\"привет\""
"\"привет\""

"а-___-зазаза"
"а-___-заЗАза"

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
" prиvet mиr "
"privet-mir"
"prиvet:mиr"

[ "user" ]
[ "users" ]

###
[

]
###
###
[
  "users...name",
  "users.1.name",
  "users.2.name",
  "users.3.name"
]
###
###
[
  "users.1.name",
  "users.2.name",
  "users.3.name"
]
###

###
[

]
###
###
[
  "b`users\x00\x00\x00name`",
  "b`users\x001\x00name`",
  "b`users\x002\x00name`",
  "b`users\x003\x00name`"
]
###
###
[
  "b`users\x001\x00name`",
  "b`users\x002\x00name`",
  "b`users\x003\x00name`"
]
###
');


// >>> TEST
// > тесты UrlModule
$fn = function () {
    _print('[ UrlModule ]');
    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::url()->url($src = 'https://google.com/hello/world');
    _print($src, (bool) $result);

    $result = \Gzhegow\Lib\Lib::url()->url($src = ':hello/world');
    _print($src, (bool) $result);

    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::url()->host($src = 'https://google.com/hello/world');
    _print($src, (bool) $result);

    $result = \Gzhegow\Lib\Lib::url()->host($src = ':hello/world');
    _print($src, (bool) $result);

    echo PHP_EOL;


    $result = \Gzhegow\Lib\Lib::url()->link($src = 'https://google.com/hello/world');
    _print($src, (bool) $result);

    $result = \Gzhegow\Lib\Lib::url()->link($src = ':hello/world');
    _print($src, (bool) $result);
};
_assert_stdout($fn, [], '
"[ UrlModule ]"

"https://google.com/hello/world" | TRUE
":hello/world" | FALSE

"https://google.com/hello/world" | TRUE
":hello/world" | FALSE

"https://google.com/hello/world" | TRUE
":hello/world" | FALSE
');
