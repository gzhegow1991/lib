# Lib

Библиотека вспомогательных функций для использования в проектах и остальных пакетах

## Установить

```
composer require gzhegow/lib
```

## Запустить тесты

```
php test.php
```

## Примеры и тесты

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';


// > настраиваем PHP
// > некоторые CMS сами по себе применяют error_reporting/set_error_handler/set_exception_handler глубоко в ядре
// > с помощью этого класса можно указать при загрузке свои собственные и вызвав методы ->use{smtg}() вернуть указанные
($ex = \Gzhegow\Lib\Lib::entrypoint())
    // > частично удаляет путь файла из каждой строки `trace` (`trace[i][file]`) при обработке исключений
    ->setDirRoot(__DIR__ . '/..')
    //
    // > file: index.php
    // ->setErrorReporting(E_ALL)
    // ->setMemoryLimit('32M')
    // ->setTimeLimit(30)
    // ->setUmask(0002)
    // ->setErrorHandler([ $ex, 'fnErrorHandler' ])
    // ->setExceptionHandler([ $ex, 'fnExceptionHandler' ])
    //
    // > file: yourscript.php
    ->useErrorReporting()
    ->useMemoryLimit()
    ->useTimeLimit()
    ->useUmask()
    ->useErrorHandler()
    ->useExceptionHandler()
;


// > добавляем несколько функций для тестирования
$ffn = new class {
    function root() : string
    {
        return realpath(__DIR__ . '/..');
    }


    function value($value) : string
    {
        return \Gzhegow\Lib\Lib::debug()->value($value, []);
    }

    function value_array($value, ?int $maxLevel = null, array $options = []) : string
    {
        return \Gzhegow\Lib\Lib::debug()->value_array($value, $maxLevel, $options);
    }

    function value_array_multiline($value, ?int $maxLevel = null, array $options = []) : string
    {
        return \Gzhegow\Lib\Lib::debug()->value_array_multiline($value, $maxLevel, $options);
    }


    function values($separator = null, ...$values) : string
    {
        return \Gzhegow\Lib\Lib::debug()->values([], $separator, ...$values);
    }


    function print($value, ...$values)
    {
        echo $this->values(' | ', $value, ...$values) . "\n";

        return $value;
    }


    function print_array($value, ?int $maxLevel = null, array $options = [])
    {
        echo $this->value_array($value, $maxLevel, $options) . "\n";

        return $value;
    }

    function print_array_multiline($value, ?int $maxLevel = null, array $options = [])
    {
        echo $this->value_array_multiline($value, $maxLevel, $options) . "\n";

        return $value;
    }


    function test(\Closure $fn, array $args = []) : \Gzhegow\Lib\Modules\Test\TestRunner\TestRunner
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return \Gzhegow\Lib\Lib::test()->test()
            ->fn($fn, $args)
            ->trace($trace)
        ;
    }
};



\Gzhegow\Lib\Lib::require_composer_global();



// >>> TEST
// > тесты Promise
$fn = function () use ($ffn) {
    $ffn->print('[ Promise ]');
    echo "\n";


    \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(900)
        ->then(function ($value) use ($ffn) {
            echo "\n";
            $ffn->print("[ 900 | THEN 1.1 ]", $value);

            return 123;
        })
        ->then(function ($value) use ($ffn) {
            $ffn->print("[ 900 | THEN 1.2 ]", $value);

            throw new \Gzhegow\Lib\Exception\RuntimeException('123');
        })
        ->catch(function ($reason) use ($ffn) {
            $ffn->print("[ 900 | CATCH 1.3 ]", $reason);

            return \Gzhegow\Lib\Modules\Async\Promise\Promise::reject($reason);
        })
        ->catch(function ($reason) use ($ffn) {
            $ffn->print("[ 900 | CATCH 1.4 ]", $reason);

            return $reason;
        })
        ->then(function ($value) use ($ffn) {
            $ffn->print("[ 900 | THEN 1.5 ]", $value);
            echo "\n";

            return $value;
        })
    ;


    \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(800)
        ->then(function ($value) use ($ffn) {
            echo "\n";
            $ffn->print("[ 800 | THEN 2.1 ]", $value);

            return 123;
        })
        ->then(function ($value) use ($ffn) {
            $ffn->print("[ 800 | THEN 2.2 ]", $value);

            $var = yield \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(400)
                ->then(function () { return 456; })
            ;

            return $var;
        })
        ->then(function ($value) use ($ffn) {
            $ffn->print("[ 800+400 | THEN 2.3 ]", $value);
            echo "\n";

            return $value;
        })
    ;


    \Gzhegow\Lib\Modules\Async\Promise\Promise::timeout(
        \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(700),
        600
    )
        ->catch(function ($value) use ($ffn) {
            echo "\n";
            $ffn->print("[ 600 | TIMEOUT ]", $value);
            echo "\n";

            return true;
        })
    ;


    $ps = [
        '[ 100 | ALL ] 1',
        \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(100)->then(function () { return '[ 100 | ALL ] 2'; }),
        \Gzhegow\Lib\Modules\Async\Promise\Promise::resolve('[ 100 | ALL ] 3'),
    ];
    \Gzhegow\Lib\Modules\Async\Promise\Promise::allResolvedOf($ps)
        ->then(function ($res) use ($ffn) {
            echo "\n";
            $ffn->print_array_multiline($res);
            echo "\n";

            return $res;
        })
    ;


    $ps = [
        \Gzhegow\Lib\Modules\Async\Promise\Promise::resolve('[ 200 | ALL SETTLED ] 1'),
        \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(200)->then(function () { return '[ 200 | ALL SETTLED ] 2'; }),
        \Gzhegow\Lib\Modules\Async\Promise\Promise::reject('[ 200 | ALL SETTLED ] 3'),
        \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(100)->then(function () { return '[ 200 | ALL SETTLED ] 4'; }),
    ];
    \Gzhegow\Lib\Modules\Async\Promise\Promise::allOf($ps)
        ->then(function ($res) use ($ffn) {
            echo "\n";
            $ffn->print_array_multiline($res, 2);
            echo "\n";

            return $res;
        })
    ;


    $ps = [
        \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(400)->then(function () { return '[ 400 | RACE ] 1'; }),
        \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(300)->then(function () { return '[ 300 | RACE ] 2'; }),
    ];
    \Gzhegow\Lib\Modules\Async\Promise\Promise::firstOf($ps)
        ->then(function ($res) use ($ffn) {
            echo "\n";
            $ffn->print($res);
            echo "\n";

            return $res;
        })
    ;


    $ps = [
        \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(500)->then(function () { return '[ 500 | ANY ] 1'; }),
        \Gzhegow\Lib\Modules\Async\Promise\Promise::reject('[ 500 | ANY ] 2'),
    ];
    \Gzhegow\Lib\Modules\Async\Promise\Promise::firstResolvedOf($ps)
        ->then(function ($res) use ($ffn) {
            echo "\n";
            $ffn->print($res);
            echo "\n";

            return $res;
        })
    ;


    \Gzhegow\Lib\Modules\Async\Loop\Loop::runLoop();
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ Promise ]"


###
[
  "[ 100 | ALL ] 1",
  "[ 100 | ALL ] 2",
  "[ 100 | ALL ] 3"
]
###


###
[
  0 => [
    "status" => "resolved",
    "value" => "[ 200 | ALL SETTLED ] 1"
  ],
  2 => [
    "status" => "rejected",
    "reason" => "[ 200 | ALL SETTLED ] 3"
  ],
  3 => [
    "status" => "resolved",
    "value" => "[ 200 | ALL SETTLED ] 4"
  ],
  1 => [
    "status" => "resolved",
    "value" => "[ 200 | ALL SETTLED ] 2"
  ]
]
###


"[ 300 | RACE ] 2"


"[ 500 | ANY ] 1"


"[ 600 | TIMEOUT ]" | { object(iterable stringable) # Gzhegow\Lib\Exception\RuntimeException }


"[ 800 | THEN 2.1 ]" | NULL
"[ 800 | THEN 2.2 ]" | 123

"[ 900 | THEN 1.1 ]" | NULL
"[ 900 | THEN 1.2 ]" | 123
"[ 900 | CATCH 1.3 ]" | { object(iterable stringable) # Gzhegow\Lib\Exception\RuntimeException }
"[ 900 | CATCH 1.4 ]" | { object(iterable stringable) # Gzhegow\Lib\Exception\RuntimeException }
"[ 900 | THEN 1.5 ]" | { object(iterable stringable) # Gzhegow\Lib\Exception\RuntimeException }

"[ 800+400 | THEN 2.3 ]" | 456
');
$test->expectSecondsMin(1.2);
$test->expectSecondsMax(1.3);
$test->run();



// > Тест закомментирован, поскольку приводит к поднятию дополнительных процессов
// > Их потом не завершить, если неверные права пользователя, раскоментируйте, чтобы проверить
// // >>> TEST
// // > тесты Fetch
// $fn = function () use ($ffn) {
//     $ffn->print('[ Fetch ]');
//     echo "\n";
//
//
//     \Gzhegow\Lib\Lib::async()
//         ->promiseManager()
//         ->useFetchApiWakeup(true)
//     ;
//
//     \Gzhegow\Lib\Modules\Async\Promise\Promise::fetchCurl($url = 'https://google.com')
//         ->then(function ($result) use ($ffn, $url) {
//             $httpCode = $result[ 'http_code' ];
//
//             $ffn->print("{$url} - HTTP: {$httpCode}");
//         })
//     ;
//
//     \Gzhegow\Lib\Modules\Async\Promise\Promise::fetchCurl($url = 'https://yandex.ru')
//         ->then(function ($result) use ($ffn, $url) {
//             $httpCode = $result[ 'http_code' ];
//
//             $ffn->print("{$url} - HTTP: {$httpCode}");
//         })
//     ;
//
//
//     \Gzhegow\Lib\Modules\Async\Loop\Loop::runLoop();
// };
// $test = $ffn->test($fn);
// $test->expectStdout('
// "[ Fetch ]"
//
// "https://google.com - HTTP: 200"
// "https://yandex.ru - HTTP: 200"
// ');
// $test->run();



// >>> TEST
// > тесты Exception
$fn = function () use ($ffn) {
    $ffn->print('[ Exception ]');
    echo "\n";

    $eeee1 = new \Exception('eeee1', 0);
    $eeee2 = new \Exception('eeee2', 0);

    $eee0 = new \Gzhegow\Lib\Exception\LogicException('eee');
    $eee0->addPrevious($eeee1);
    $eee0->addPrevious($eeee2);

    $ee1 = new \Exception('ee1', 0, $previous = $eee0);
    $ee2 = new \Exception('ee2', 0, $previous = $eee0);

    $previousList = [ $ee1, $ee2 ];
    $e0 = new \Gzhegow\Lib\Exception\RuntimeException('e', 0, ...$previousList);

    $messages = \Gzhegow\Lib\Lib::debug()
        ->throwableManager()
        ->getPreviousMessagesLines($e0, _DEBUG_THROWABLE_WITHOUT_FILE)
    ;
    echo implode("\n", $messages);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ Exception ]"

[ 0 ] e
{ object # Gzhegow\Lib\Exception\RuntimeException }
--
-- [ 0.0 ] ee1
-- { object # Exception }
----
---- [ 0.0.0 ] eee
---- { object # Gzhegow\Lib\Exception\LogicException }
------
------ [ 0.0.0.0 ] eeee1
------ { object # Exception }
------
------ [ 0.0.0.1 ] eeee2
------ { object # Exception }
--
-- [ 0.1 ] ee2
-- { object # Exception }
----
---- [ 0.1.0 ] eee
---- { object # Gzhegow\Lib\Exception\LogicException }
------
------ [ 0.1.0.0 ] eeee1
------ { object # Exception }
------
------ [ 0.1.0.1 ] eeee2
------ { object # Exception }
');
$test->run();



// >>> TEST
// > тесты ErrorBag
$fn = function () use ($ffn) {
    $ffn->print('[ ErrorBag ]');
    echo "\n";

    \Gzhegow\Lib\Lib::errorBag($b);

    for ( $i = 0; $i < 2; $i++ ) {
        $tag = 'tag' . $i;

        for ( $ii = 0; $ii < 2; $ii++ ) {
            $ttag = 'ttag' . $ii;

            for ( $iii = 0; $iii < 2; $iii++ ) {
                $tttag = 'tttag' . $iii;

                $b->error("[ Error ] {$i}.{$ii}.{$iii}", [ $tag, $ttag, $tttag ], [ __FILE__, __LINE__ ]);
            }
        }
    }

    $list = $b->getErrorsByTags([ 'tttag0' ]); // 4

    $print = [];
    foreach ( $list as $l ) {
        $print[] = [ $l->error, $l ];
    }

    $ffn->print_array_multiline($print, 2);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ ErrorBag ]"

###
[
  [
    "[ Error ] 0.0.0",
    "{ object # Gzhegow\Lib\Modules\Php\ErrorBag\Error }"
  ],
  [
    "[ Error ] 0.1.0",
    "{ object # Gzhegow\Lib\Modules\Php\ErrorBag\Error }"
  ],
  [
    "[ Error ] 1.0.0",
    "{ object # Gzhegow\Lib\Modules\Php\ErrorBag\Error }"
  ],
  [
    "[ Error ] 1.1.0",
    "{ object # Gzhegow\Lib\Modules\Php\ErrorBag\Error }"
  ]
]
###
');
$test->run();



// >>> TEST
// > тесты Pipe
$fn = function () use ($ffn) {
    $ffn->print('[ Pipe ]');
    echo "\n";

    $fn = \Gzhegow\Lib\Lib::pipe();

    $fn
        // > этот шаг может заменить значение, в данном случае приведя его к строке
        ->map('strval')
        //
        // > этот шаг может очистить значение (в последующих шаг будет использоваться NULL)
        ->filter('strlen')
        //
        // > этот шаг может выполнить сторонние действия, а возврат метода игнорируется
        ->tap(function ($value) use ($ffn) {
            echo 'Hello World! Your value is: [ ' . $ffn->value($value) . ' ]' . "\n";

            throw new \Gzhegow\Lib\Exception\RuntimeException('This is the exception');
        })
        //
        // > этот шаг никогда не начнется, поскольку в прошлом шаге было выброшено исключение
        ->map('intval')
        //
        // > этот шаг может поймать исключение
        // ->catchTo($e) // > исключение будет сохранено в $e по ссылке, а значение удалено
        // ->catchTo($e, [ 'catchTo' ]) // > тоже, но значение будет заменено на 'catchTo'
        // ->catchTo($e, [ 'catchTo' ], LogicException::class) > тоже, но только если исключение нужного класса или его потомков
        ->catchTo($e, [ 'catchTo' ], \LogicException::class)
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
    $ffn->print($result);

    $result = $fn(1);
    $ffn->print($result);

    $result = $fn('0');
    $ffn->print($result);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ Pipe ]"

Hello World! Your value is: [ NULL ]
"catch"
Hello World! Your value is: [ "1" ]
"catch"
Hello World! Your value is: [ "0" ]
"catch"
');
$test->run();



// >>> TEST
// > тесты ArrayOf
$fn = function () use ($ffn) {
    $ffn->print('[ ArrayOf ]');
    echo "\n";


    $notAnObject = 1;
    $objectStdClass = new \stdClass();
    $objectArrayObject = new ArrayObject();
    $objectAnonymousStdClass = new class extends \stdClass {
    };


    // > осторожно, `ArrayOf` не проверяет типы при добавлении, для этого есть `ArrayOfType` или `ArrayOfClass`
    // > этот объект сделан для того, чтобы убедится, что другой разработчик создал его с правильным типом
    // > при этом он может положить туда что захочет, это похоже на указание PHPDoc
    $theArrayOf = \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOf::new('object');
    $theArrayOf[] = $notAnObject;
    $ffn->print($theArrayOf);
    $ffn->print($theArrayOf->isOfType('object'), $theArrayOf->getValues());

    echo "\n";


    $theArrayOf = \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfType::new('object');
    $theArrayOf[] = $objectStdClass;
    $theArrayOf[] = $objectArrayObject;

    try {
        $theArrayOf[] = $notAnObject;
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }
    $ffn->print($theArrayOf);
    $ffn->print($theArrayOf->isOfType('object'), $theArrayOf->getValues());

    echo "\n";


    $theArrayOf = \Gzhegow\Lib\Modules\Arr\ArrayOf\ArrayOfClass::new(\stdClass::class);
    $theArrayOf[] = $objectStdClass;

    try {
        $theArrayOf[] = $objectArrayObject;
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    try {
        $theArrayOf[] = $objectAnonymousStdClass;
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    try {
        $theArrayOf[] = $notAnObject;
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }
    $ffn->print($theArrayOf);
    $ffn->print($theArrayOf->isOfType('object'), $theArrayOf->getValues());

    echo "\n";


    /**
     * @var \Gzhegow\Lib\Modules\Arr\Map\PHP8\Map $theMap
     */
    $theMap = \Gzhegow\Lib\Modules\Arr\Map\Map::new();
    $theMap[ $stdClass = new \stdClass() ] = 1;
    $theMap[ $array = [ 1, 2, 3 ] ] = 1;
    $ffn->print($theMap);
    $ffn->print(isset($theMap[ $stdClass ]), isset($theMap[ $array ]));
    $ffn->print_array($theMap->keys(), 1);
    $ffn->print_array($theMap->values(), 2);
};
$test = $ffn->test($fn);

$test->expectStdoutIf(PHP_VERSION_ID >= 80000, '
"[ ArrayOf ]"

{ object(countable(1) iterable serializable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOf }
TRUE | [ 1 ]

"[ CATCH ] The `value` should be of type: object"
{ object(countable(2) iterable serializable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOfType }
TRUE | [ "{ object # stdClass }", "{ object(countable(0) iterable serializable) # ArrayObject }" ]

"[ CATCH ] The `value` should be of class: stdClass"
"[ CATCH ] The `value` should be of class: stdClass"
"[ CATCH ] The `value` should be object"
{ object(countable(1) iterable serializable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOfClass }
TRUE | [ "{ object # stdClass }" ]

{ object(countable(2) iterable serializable) # Gzhegow\Lib\Modules\Arr\Map\PHP8\Map }
TRUE | TRUE
[ "{ object # stdClass }", "{ array(3) }" ]
[ 1, 1 ]
');

$test->expectStdoutIf(PHP_VERSION_ID < 80000, '
"[ ArrayOf ]"

{ object(countable(1) iterable serializable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOf }
TRUE | [ 1 ]

"[ CATCH ] The `value` should be of type: object"
{ object(countable(2) iterable serializable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfType }
TRUE | [ "{ object # stdClass }", "{ object(countable(0) iterable serializable) # ArrayObject }" ]

"[ CATCH ] The `value` should be of class: stdClass"
"[ CATCH ] The `value` should be of class: stdClass"
"[ CATCH ] The `value` should be object"
{ object(countable(1) iterable serializable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfClass }
TRUE | [ "{ object # stdClass }" ]

{ object(countable(2) iterable serializable) # Gzhegow\Lib\Modules\Arr\Map\PHP7\Map }
TRUE | TRUE
[ "{ object # stdClass }", "{ array(3) }" ]
[ 1, 1 ]
');

$test->run();



// >>> TEST
// > тесты Benchmark
$fn = function () use ($ffn) {
    $ffn->print('[ Benchmark ]');
    echo "\n";

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

    $ffn->print_array_multiline($report, 2);
};
$test = $ffn->test($fn);
$test->expectStdout('
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
$test->run();



// >>> TEST
// > тесты Config
$fn = function () use ($ffn) {
    $ffn->print('[ Config ]');
    echo "\n";

    /**
     * @property $child
     */
    class ConfigDummy extends \Gzhegow\Lib\Config\AbstractConfig
    {
        protected $child;

        public function __construct()
        {
            $this->child = new \ConfigChildDummy();

            parent::__construct();
        }
    }

    /**
     * @property $foo
     */
    class ConfigChildDummy extends \Gzhegow\Lib\Config\AbstractConfig
    {
        protected $foo = 'bar';
    }

    /**
     * @property $child
     */
    class ConfigValidateDummy extends \Gzhegow\Lib\Config\AbstractConfig
    {
        protected $child;

        public function __construct()
        {
            $this->child = new \ConfigChildValidateDummy();

            parent::__construct();
        }
    }

    /**
     * @property $foo
     * @property $foo2
     */
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

    $ffn->print($config);
    $ffn->print($config->child, $config->child === $configChildDefault);
    $ffn->print($config->child->foo, $config->child->foo === $configChildNew->foo);

    echo "\n";


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

    $ffn->print($config);
    $ffn->print($config->child, $config->child === $configChildDefault);
    $ffn->print($config->child->foo, $config->child->foo === $configChildNewFooValue);

    echo "\n";


    $configArray = $config->toArray();
    $config->load($configArray);

    $ffn->print($config);
    $ffn->print($config->child, $config->child === $configChildDefault);
    $ffn->print($config->child->foo, $config->child->foo === $configChildNewFooValue);

    echo "\n";


    $config = new \ConfigValidateDummy();
    try {
        $config->validate();
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }
};
$test = $ffn->test($fn);
$test->expectStdout('
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
$test->run();



// >>> TEST
// > тесты Result
$fn = function () use ($ffn) {
    $ffn->print('[ Result ]');
    echo "\n";


    class ResultTest
    {
        public static function string($value, $res = null)
        {
            if (! is_string($value)) {
                return \Gzhegow\Lib\Modules\Php\Result\Result::err(
                    $res,
                    [ 'The `value` should be string', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return \Gzhegow\Lib\Modules\Php\Result\Result::ok($res, $value);
        }

        public static function string_not_empty($value, $res = null)
        {
            if (! (is_string($value) && ('' !== $value))) {
                return \Gzhegow\Lib\Modules\Php\Result\Result::err(
                    $res,
                    [ 'The `value` should be non-empty string', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return \Gzhegow\Lib\Modules\Php\Result\Result::ok($res, $value);
        }
    }


    $strInterpolator = \Gzhegow\Lib\Lib::str()->interpolator();


    $invalidValue = NAN;
    $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::type();
    $status =
        \Gzhegow\Lib\Modules\Bcmath\Number::fromStatic($invalidValue, $ctx)
        || \Gzhegow\Lib\Modules\Bcmath\Number::fromValidArray($invalidValue, $ctx);

    $errors = $ctx->errors();
    $errors = array_map([ $strInterpolator, 'interpolateMessage' ], $errors);
    $ffn->print_array_multiline($errors, 2);

    echo "\n";


    $invalidValue = NAN;
    $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::parse();
    $value = null
        ?? \Gzhegow\Lib\Modules\Bcmath\Number::fromStatic($invalidValue, $ctx)
        ?? \Gzhegow\Lib\Modules\Bcmath\Number::fromValidArray($invalidValue, $ctx);

    $errors = $ctx->errors();
    $errors = array_map([ $strInterpolator, 'interpolateMessage' ], $errors);
    $ffn->print_array_multiline($errors, 2);

    echo "\n";


    $invalidValue = NAN;
    $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::parseThrow();
    try {
        \Gzhegow\Lib\Modules\Bcmath\Number::fromStatic($invalidValue, $ctx);
    }
    catch ( \Throwable $e ) {
        $messages = \Gzhegow\Lib\Lib::debug()
            ->throwableManager()
            ->getPreviousMessagesLines($e, _DEBUG_THROWABLE_WITHOUT_FILE)
        ;

        echo implode("\n", $messages) . "\n";
        echo "\n";
    }


    try {
        $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::typeThrow();
        $status = true
            && ResultTest::string($invalidValue, $ctx)
            && ResultTest::string_not_empty($ctx->getResult(), $ctx);
    }
    catch ( \Throwable $e ) {
        $messages = \Gzhegow\Lib\Lib::debug()
            ->throwableManager()
            ->getPreviousMessagesLines($e, _DEBUG_THROWABLE_WITHOUT_FILE)
        ;

        echo implode("\n", $messages) . "\n";
        echo "\n";
    }


    try {
        $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::ignoreThrow();
        $devnull = null
            ?? ResultTest::string($invalidValue, $ctx)
            ?? ResultTest::string_not_empty($ctx->getResult(), $ctx);
    }
    catch ( \Throwable $e ) {
        $messages = \Gzhegow\Lib\Lib::debug()
            ->throwableManager()
            ->getPreviousMessagesLines($e, _DEBUG_THROWABLE_WITHOUT_FILE)
        ;

        echo implode("\n", $messages) . "\n";
        echo "\n";
    }


    $validValue = '123';

    $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::parseThrow();
    $value = ResultTest::string($validValue, $ctx);
    $ffn->print($value, $ctx->getResult());
    echo "\n";

    $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::typeThrow();
    $status = true
        && ResultTest::string($validValue, $ctx)
        && ResultTest::string_not_empty($ctx->getResult(), $ctx);
    $ffn->print($status, $ctx->getResult());
    echo "\n";

    $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::ignoreThrow();
    $devnull = null
        ?? ResultTest::string($validValue, $ctx)
        ?? ResultTest::string_not_empty($ctx->getResult(), $ctx);
    $ffn->print($devnull, $ctx->getResult());
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ Result ]"

###
[
  "The `from` must be instance of: Gzhegow\Lib\Modules\Bcmath\Number",
  "The `from` should be array"
]
###

###
[
  "The `from` must be instance of: Gzhegow\Lib\Modules\Bcmath\Number",
  "The `from` should be array"
]
###

[ 0 ] The `from` must be instance of: Gzhegow\Lib\Modules\Bcmath\Number
{ object # Gzhegow\Lib\Exception\LogicException }

[ 0 ] The `value` should be string
{ object # Gzhegow\Lib\Exception\LogicException }

[ 0 ] The `value` should be string
{ object # Gzhegow\Lib\Exception\LogicException }

"123" | "123"

TRUE | "123"

NULL | "123"
');
$test->run();



// >>> TEST
// > тесты ArrModule
$fn = function () use ($ffn) {
    $ffn->print('[ ArrModule ]');
    echo "\n";


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
        $ffn->print($resStrict, $resNonStrict, $resNonStrict === array_intersect($a, $b));

        $resStrict = \Gzhegow\Lib\Lib::arr()->diff($a, $b);
        $resNonStrict = \Gzhegow\Lib\Lib::arr()->diff_non_strict($a, $b);
        $ffn->print($resStrict, $resNonStrict, $resNonStrict === array_diff($a, $b));
    }

    echo "\n";


    $arr = [
        0 => '',
        1 => [],
        2 => [ 1, 2, 3 ],
        3 => [
            '',
            [],
            [ 1, 2, 3 ],
        ],
        4 => [
            'key0' => '',
            'key1' => [],
        ],
        5 => [
            'key0' => '',
            'key1' => [],
            'key2' => [ 1, 2, 3 ],
        ],

        'key0' => '',
        'key1' => [],
        'key2' => [ 1, 2, 3 ],
        'key3' => [
            '',
            [],
            [ 1, 2, 3 ],
        ],
        'key4' => [
            'key0' => '',
            'key1' => [],
        ],
        'key5' => [
            'key0' => '',
            'key1' => [],
            'key2' => [ 1, 2, 3 ],
        ],

        6      => [
            0 => '',
            1 => [],
            2 => [ 1, 2, 3 ],
            3 => [
                '',
                [],
                [ 1, 2, 3 ],
            ],
            4 => [
                'key0' => '',
                'key1' => [],
            ],
            5 => [
                'key0' => '',
                'key1' => [],
                'key2' => [ 1, 2, 3 ],
            ],

            'key0' => '',
            'key1' => [],
            'key2' => [ 1, 2, 3 ],
            'key3' => [
                '',
                [],
                [ 1, 2, 3 ],
            ],
            'key4' => [
                'key0' => '',
                'key1' => [],
            ],
            'key5' => [
                'key0' => '',
                'key1' => [],
                'key2' => [ 1, 2, 3 ],
            ],
        ],
        'key6' => [
            0 => '',
            1 => [],
            2 => [ 1, 2, 3 ],
            3 => [
                '',
                [],
                [ 1, 2, 3 ],
            ],
            4 => [
                'key0' => '',
                'key1' => [],
            ],
            5 => [
                'key0' => '',
                'key1' => [],
                'key2' => [ 1, 2, 3 ],
            ],

            'key0' => '',
            'key1' => [],
            'key2' => [ 1, 2, 3 ],
            'key3' => [
                '',
                [],
                [ 1, 2, 3 ],
            ],
            'key4' => [
                'key0' => '',
                'key1' => [],
            ],
            'key5' => [
                'key0' => '',
                'key1' => [],
                'key2' => [ 1, 2, 3 ],
            ],
        ],
    ];
    $res = \Gzhegow\Lib\Lib::arr()->dot(
        $arr, '.', [],
        _ARR_WALK_WITH_EMPTY_ARRAYS | _ARR_WALK_WITH_LISTS | _ARR_WALK_WITH_DICTS
    );
    $ffn->print_array_multiline($res, 2);
    echo "\n";


    $arr = [
        'prop' => 1,

        0 => [
            'prop' => 1,

            0 => [
                'prop' => 1,

                0 => [
                    'prop' => 1,
                ],
            ],
        ],
    ];

    $objStdClass = \Gzhegow\Lib\Lib::arr()->map_to_object($arr);
    $ffn->print($objStdClass, (array) $objStdClass);

    $target = new \stdClass();
    $objStdClass = \Gzhegow\Lib\Lib::arr()->map_to_object($arr, $target);
    $ffn->print($objStdClass, (array) $objStdClass);

    $target = new class {
        protected $prop;
    };
    $objAnonymous = \Gzhegow\Lib\Lib::arr()->map_to_object($arr, $target);
    $ffn->print($objAnonymous, (array) $objAnonymous);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ ArrModule ]"

[ 1 => 2, 2 => 3 ] | [ 1 => 2, 2 => 3 ] | TRUE
[ 1 ] | [ 1 ] | TRUE
[ 2 => 3 ] | [ 1 => "2", 2 => 3 ] | TRUE
[ 1, "2" ] | [ 1 ] | TRUE
[] | [ "1", "2" ] | TRUE
[ "1", "2", "3" ] | [ 2 => "3" ] | TRUE
[ 1 => 2, 2 => 2 ] | [ 1 => 2, 2 => 2 ] | TRUE
[ 0 => 1, 3 => 3 ] | [ 0 => 1, 3 => 3 ] | TRUE
[ "y" => 200, "z" => 300 ] | [ "y" => 200, "z" => 300 ] | TRUE
[ "x" => 100 ] | [ "x" => 100 ] | TRUE

###
[
  0 => "",
  1 => [],
  2 => [
    1,
    2,
    3
  ],
  "3.0" => "",
  "3.1" => [],
  "3.2" => [
    1,
    2,
    3
  ],
  4 => [
    "key0" => "",
    "key1" => []
  ],
  "5.key0" => "",
  "5.key1" => [],
  "5.key2" => [
    1,
    2,
    3
  ],
  "key0" => "",
  "key1" => [],
  "key2" => [
    1,
    2,
    3
  ],
  "key3.0" => "",
  "key3.1" => [],
  "key3.2" => [
    1,
    2,
    3
  ],
  "key4" => [
    "key0" => "",
    "key1" => []
  ],
  "key5.key0" => "",
  "key5.key1" => [],
  "key5.key2" => [
    1,
    2,
    3
  ],
  "6.0" => "",
  "6.1" => [],
  "6.2" => [
    1,
    2,
    3
  ],
  "6.3.0" => "",
  "6.3.1" => [],
  "6.3.2" => [
    1,
    2,
    3
  ],
  "6.4" => [
    "key0" => "",
    "key1" => []
  ],
  "6.5.key0" => "",
  "6.5.key1" => [],
  "6.5.key2" => [
    1,
    2,
    3
  ],
  "6.key0" => "",
  "6.key1" => [],
  "6.key2" => [
    1,
    2,
    3
  ],
  "6.key3.0" => "",
  "6.key3.1" => [],
  "6.key3.2" => [
    1,
    2,
    3
  ],
  "6.key4" => [
    "key0" => "",
    "key1" => []
  ],
  "6.key5.key0" => "",
  "6.key5.key1" => [],
  "6.key5.key2" => [
    1,
    2,
    3
  ],
  "key6.0" => "",
  "key6.1" => [],
  "key6.2" => [
    1,
    2,
    3
  ],
  "key6.3.0" => "",
  "key6.3.1" => [],
  "key6.3.2" => [
    1,
    2,
    3
  ],
  "key6.4" => [
    "key0" => "",
    "key1" => []
  ],
  "key6.5.key0" => "",
  "key6.5.key1" => [],
  "key6.5.key2" => [
    1,
    2,
    3
  ],
  "key6.key0" => "",
  "key6.key1" => [],
  "key6.key2" => [
    1,
    2,
    3
  ],
  "key6.key3.0" => "",
  "key6.key3.1" => [],
  "key6.key3.2" => [
    1,
    2,
    3
  ],
  "key6.key4" => [
    "key0" => "",
    "key1" => []
  ],
  "key6.key5.key0" => "",
  "key6.key5.key1" => [],
  "key6.key5.key2" => [
    1,
    2,
    3
  ]
]
###
' . "
{ object # stdClass } | [ \"prop\" => 1, 0 => \"{ object # stdClass }\" ]
{ object # stdClass } | [ \"prop\" => 1, 0 => \"{ object # stdClass }\" ]
{ object # class@anonymous } | [ \"\x00*\x00prop\" => 1, 0 => \"{ object # stdClass }\" ]
");
$test->run();



// >>> TEST
// > тесты BcmathModule
$fn = function () use ($ffn) {
    $ffn->print('[ BcmathModule ]');
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 2);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 2);
    $ffn->print($result, (string) $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('1.005', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('1.005', 2);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('-1.005', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyceil('-1.005', 2);
    $ffn->print($result, (string) $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 2);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 2);
    $ffn->print($result, (string) $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('1.005', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('1.005', 2);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('-1.005', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmoneyfloor('-1.005', 2);
    $ffn->print($result, (string) $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.5', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.05', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.005', 0);
    $ffn->print($result, (string) $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.5', 2);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.05', 2);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('1.005', 2);
    $ffn->print($result, (string) $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.5', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.05', 0);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.005', 0);
    $ffn->print($result, (string) $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.5', 2);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.05', 2);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcround('-1.005', 2);
    $ffn->print($result, (string) $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcgcd(8, 12);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcgcd(7, 13);
    $ffn->print($result, (string) $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 6);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 5);
    $ffn->print($result, (string) $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 10);
    $ffn->print($result, (string) $result);
    echo "\n";
};
$test = $ffn->test($fn);
$test->expectStdout('
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
$test->run();



// >>> TEST
// > тесты CmpModule
$fn = function () use ($ffn) {
    $ffn->print('[ CmpModule ]');
    echo "\n";

    $object = new \StdClass();

    $resourceOpenedStdout = STDOUT;
    $resourceOpenedStderr = STDERR;
    $resourceClosed = fopen('php://memory', 'w');
    fclose($resourceClosed);

    $valuesXX = [
        0  => [
            NAN,
            null,
            new \Gzhegow\Lib\Modules\Php\Nil(),
        ],
        1  => [
            strval(NAN),
            'NULL',
            strval(new \Gzhegow\Lib\Modules\Php\Nil()),
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

    // $dumpPath = $ffn->root() . '/var/dump/fn_compare_tables2.txt';
    // if (is_file($dumpPath)) unlink($dumpPath);

    $xi = 0;
    foreach ( $valuesXX as $valuesX ) {
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
        // file_put_contents($dumpPath, $content . "\n" . "\n", FILE_APPEND);

        // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
        // dd(\Gzhegow\Lib\Lib::debug()->print_table($tableSize, 1));
        echo md5(serialize($table)) . "\n";
        echo md5(serialize($tableSize)) . "\n";
        echo "\n";
    }
    unset($table);
};
$test = $ffn->test($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80200, '
"[ CmpModule ]"

cf4099ce4a62a1d06cf8b3aafc0da38d
cf4099ce4a62a1d06cf8b3aafc0da38d

490ec98f69fe96c14637416f066852c2
1fd5d4c6fc6cf6abde19e1c75ca55710

e606eca98951c845a7640edb02b3ab62
6fcc4ef34f6c7bcd1daa4855b8b196f2

48fe998035bdd10d06e73f0f8a08194a
da5a6500e2ad849c8772f1c713541973

6f0fd785225ac3eab7fa00b2c46cc298
a8fe44514f23a3182da5459418346eed

35101a67c8b16659d83418f721c93668
6dca25535d3e9ad90f35c0419acb37b5

87ff003090a1314209918f3ac7f8beba
ec8c98ae4dd41a94732bbdb499c3d845

55a35deb406692fd486098d19c923798
0b07fae03da41bff2d2c698d78bbd6c1

edf2f80951aa9ba3f6ae9289b3cea8b9
7d159d9422854dc5f443113d1710828e

6877e6ce3586a6d065eede6e05698977
dadf7999aaf18c657bdae55f4aefd521

e3ee774a87d3c5397efea14d9e1d1f6b
6c642f9464a155fc8b3478f7c73480cf

2558d66c489c580b403ae36be9a1f42f
0fb2ff53c46b572d09ed995fe5d320fa

ea7065cc26c32ddd0f949ea8dbef5f31
b159e16b3ebf3aa4c3743b5714656022

6e7182a443054f2608c1582473a69420
4fca1526ef9a3769fe3279b15543fc57

cd3ca8a442d8b5aef4173ae928978e96
6e3e6ed1c9a6e1ae2d634b9656c976f8

098c84f888f7c6049199cfa933e119d0
753b54ab1969bce113f3b5b413f66b08

c2991d79be720822b54584c3dc7d723c
e4f103e1717b536a5fec3fa45065c28c

8029eafbf5407e2a188bb7737154af51
6a2f24abb7ad00dce3e0caa451614c3f

94f9d7b5c4184b5a4c1bd9e9ae7fe219
4af4327c1bbf913323ee2f4b01ee77ea

1d836ee366bf12ffb91ba7cf0e569464
ed615e45c29e72aeb4c1404a47e59747
');
$test->expectStdoutIf(PHP_VERSION_ID < 80200, '
"[ CmpModule ]"

82f719e1e01d77d54712e33ee73c9eef
82f719e1e01d77d54712e33ee73c9eef

57712c0155ecc96bf7958eef2eb2c15c
4cdeb656c29a9d3c3bb3beeb4c935660

77aba0613789762ea2c3c479920904d5
05741b56dfff778ea656957062597693

a465d524f95253f9e78db5b8bd967b5c
3f3e59d3fb40b9d1254b2b15833caf6d

9840a56176ef81b518e0d819da2d9c3c
cfc83599a289f0f9f95ba4b7fe626c9e

3a8df645ac662bcb16cb4d1a5e9805be
e6029467cb9b33033e53ace6ca234328

b95287e75683be04860e5f2103b30c73
150271d2c69a7da3024abc760060a698

f0716acbde15cf9381d1add49c213e15
86888e38a0e818282dd161b537ffc679

1e65fa8f932a9535ff7c2aa1f60f82e5
7c37f83c6b382d48087f9ca329e79d18

a86e213893dae7509e4926b3be82f35e
86d9ace1ba936d47fd0a16971309c56a

665fb866f689f5440588a1dcb1593bf4
3ad7a0cb801fc6b8127d04182bb8e47f

d19a897e0b57bf63e8fbf9398358e6d8
b28f71f02f17cfe93cee65c64623400b

aa02005c30e79e8772d3df310716c8d7
fd1ac42f24cda6df6dbfefd12fc70317

6233d14a2cb943730a4513d69b5de514
898449a50d8741568159521163de7457

7f40a0427b5e1e2d646019d89a5a2f7f
68db9812f428826443e271cabf880fbb

10e9523c26b0f8c27d21b68a3acca316
aff0519a0870d57e0a0bf845d6209f63

9e31c04f69697190e88b16b1c576255b
2238672a27a185cd2e0c65e6ccb9ae16

9e793be458e49a3faf18be572d8edfec
1dd2cba5b0a201f8a24e72b6588ea2fb

b0ac0103412cfb09117c2a8ec71c0ad0
af0b49ea305eadac52ceccbeff73fa1d

a3fb74b5b7f1ce2b438df41a43581719
7f269763bf236100e2ed7c5a5f305dff
');
$test->run();



// >>> TEST
// > тесты CryptModule
$fn = function () use ($ffn) {
    $ffn->print('[ CryptModule ]');
    echo "\n";


    $algos = [
        'fnv1a32',
        'crc32',
        'md5',
        'sha1',
        'sha256',
    ];
    $src = 'hello world!';
    foreach ( $algos as $algo ) {
        $isModeBinary = false;
        $enc = \Gzhegow\Lib\Lib::crypt()->hash($algo, $src, $isModeBinary);
        $status = \Gzhegow\Lib\Lib::crypt()->hash_equals($enc, $algo, $src, $isModeBinary);
        $ffn->print($src, $enc, $status);

        $isModeBinary = true;
        $enc = \Gzhegow\Lib\Lib::crypt()->hash($algo, $src, $isModeBinary);
        $status = \Gzhegow\Lib\Lib::crypt()->hash_equals($enc, $algo, $src, $isModeBinary);
        $ffn->print($src, $enc, $status);

        echo "\n";
    }


    $src = 0;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '01');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01');
    $ffn->print($src, $enc, $dec);

    $src = 3;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '01');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01');
    $ffn->print($src, $enc, $dec);

    $src = 0;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '01234567');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01234567');
    $ffn->print($src, $enc, $dec);

    $src = 15;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '01234567');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '01234567');
    $ffn->print($src, $enc, $dec);

    $src = 0;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '0123456789ABCDEF');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '0123456789ABCDEF');
    $ffn->print($src, $enc, $dec);

    $src = 31;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, '0123456789ABCDEF');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, '0123456789ABCDEF');
    $ffn->print($src, $enc, $dec);

    echo "\n";


    $isOneBased = false;
    $src = 0;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $ffn->print($src, $enc, $dec);

    $isOneBased = false;
    $src = 10;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $ffn->print($src, $enc, $dec);

    $isOneBased = false;
    $src = 25;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $ffn->print($src, $enc, $dec);

    $isOneBased = false;
    $src = 26;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $ffn->print($src, $enc, $dec);

    echo "\n";


    $isOneBased = true;
    $src = 0;
    try {
        \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    }
    catch ( \Throwable $e ) {
        $ffn->print($src, '[ CATCH ] ' . $e->getMessage());
    }

    $isOneBased = true;
    $src = 10;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $ffn->print($src, $enc, $dec);

    $isOneBased = true;
    $src = 26;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $ffn->print($src, $enc, $dec);

    $isOneBased = true;
    $src = 27;
    $enc = \Gzhegow\Lib\Lib::crypt()->dec2numbase($src, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2dec($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $isOneBased);
    $ffn->print($src, $enc, $dec);

    echo "\n";


    $src = '2147483647';
    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($src, '0123456789', '0123456789');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase('9223372036854775807', '0123456789', '0123456789');
    $ffn->print($src, $enc, $dec);

    $src = '2147483647';
    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($src, '0123456789abcdefghijklmnopqrstuvwxyz', '0123456789');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($enc, '0123456789', '0123456789abcdefghijklmnopqrstuvwxyz');
    $ffn->print($src, $enc, $dec);

    $src = '9223372036854775807';
    $enc = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($src, '0123456789abcdefghijklmnopqrstuvwxyz', '0123456789');
    $dec = \Gzhegow\Lib\Lib::crypt()->numbase2numbase($enc, '0123456789', '0123456789abcdefghijklmnopqrstuvwxyz');
    $ffn->print($src, $enc, $dec);

    echo "\n";


    $enc = [];
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('1', '01');
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('11', '0123');
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('111', '01234567');
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('1111', '0123456789ABCDEF');
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('11111', '0123456789ABCDEFGHIJKLMNOPQRSTUV');
    $enc[] = \Gzhegow\Lib\Lib::crypt()->bin2binbase('111111', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/');
    $ffn->print(...$enc);
    echo "\n";


    $src = [ '你' ];
    $enc = \Gzhegow\Lib\Lib::crypt()->text2bin($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->bin2text($enc);
    $ffn->print_array($src);
    $ffn->print_array($enc);
    $ffn->print_array($dec);
    echo "\n";

    $src = [ '你好' ];
    $enc = \Gzhegow\Lib\Lib::crypt()->text2bin($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->bin2text($enc);
    $ffn->print_array($src);
    $ffn->print_array($enc);
    $ffn->print_array($dec);
    echo "\n";


    echo "\n";


    $src = 5678;
    $bin = decbin($src);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2binbase($bin, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    $dec = \Gzhegow\Lib\Lib::crypt()->binbase2bin($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    $res = bindec($dec);
    $ffn->print($src, $bin, $enc, $dec, $res);
    echo "\n";

    $src = [ 'hello' ];
    $bin = \Gzhegow\Lib\Lib::crypt()->text2bin($src);
    $enc = \Gzhegow\Lib\Lib::crypt()->bin2base($bin, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    $dec = \Gzhegow\Lib\Lib::crypt()->base2bin($enc, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
    $res = implode('', array_map('chr', array_map('bindec', $dec)));
    $ffn->print_array($src);
    $ffn->print_array($bin);
    $ffn->print($enc);
    $ffn->print_array($dec);
    $ffn->print($res);
    echo "\n";


    echo "\n";


    $src = 'HELLO';
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_it($src);
    $enc = implode('', iterator_to_array($gen));
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_it($enc);
    $dec = implode('', iterator_to_array($gen));
    $ffn->print($src, $enc, $dec);
    echo "\n";


    $src = "hello";
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    $ffn->print($src, $enc, $dec);

    $src = "\x00\x00\x01\x00\xFF";
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    $ffn->print($src, $enc, $dec);

    $src = "你好";
    $enc = \Gzhegow\Lib\Lib::crypt()->base58_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base58_decode($enc);
    $ffn->print($src, $enc, $dec);

    echo "\n";


    $src = "hello";
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    $ffn->print($src, $enc, $dec);

    $src = "\x00\x00\x01\x00\xFF";
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    $ffn->print($src, $enc, $dec);

    $src = '你好';
    $enc = \Gzhegow\Lib\Lib::crypt()->base62_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base62_decode($enc);
    $ffn->print($src, $enc, $dec);
};
$test = $ffn->test($fn);
$test->expectStdout('
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
$test->run();



// >>> TEST
// > тесты DateModule
$fn = function () use ($ffn) {
    $ffn->print('[ DateModule ]');
    echo "\n";


    $before = date_default_timezone_get();
    date_default_timezone_set('UTC');


    $status = \Gzhegow\Lib\Lib::date()->type_timezone($dateTimezone, '+0100');
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone($dateTimezone, 'EET');
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone($dateTimezone, 'Europe/Minsk');
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone($dateTimezone, new \DateTimeZone('UTC'));
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone($dateTimezone, new \DateTime('now', new \DateTimeZone('UTC')));
    $ffn->print($status, $dateTimezone);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_timezone_offset($dateTimezone, '+0100');
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_offset($dateTimezone, new \DateTimeZone('+0100'));
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_offset($dateTimezone, new \DateTime('now', new \DateTimeZone('+0100')));
    $ffn->print($status, $dateTimezone);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_timezone_abbr($dateTimezone, 'EET');
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_abbr($dateTimezone, new \DateTimeZone('EET'));
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_abbr($dateTimezone, new \DateTime('now', new \DateTimeZone('EET')));
    $ffn->print($status, $dateTimezone);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_timezone_name($dateTimezone, 'Europe/Minsk');
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_name($dateTimezone, new \DateTimeZone('Europe/Minsk'));
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_name($dateTimezone, new \DateTime('now', new \DateTimeZone('Europe/Minsk')));
    $ffn->print($status, $dateTimezone);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_timezone_nameabbr($dateTimezone, 'EET');
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_nameabbr($dateTimezone, 'Europe/Minsk');
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_nameabbr($dateTimezone, new \DateTimeZone('EET'));
    $ffn->print($status, $dateTimezone);
    $status = \Gzhegow\Lib\Lib::date()->type_timezone_nameabbr($dateTimezone, new \DateTime('now', new \DateTimeZone('Europe/Minsk')));
    $ffn->print($status, $dateTimezone);
    echo "\n";

    echo "\n";


    $status = \Gzhegow\Lib\Lib::date()->type_interval($dateInterval, 'P1D');
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval($dateInterval, 'P1.5D');
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval($dateInterval, '+100 seconds');
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval($dateInterval, new \DateInterval('P1D'));
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval($dateInterval, \DateInterval::createFromDateString('+100 seconds'));
    $ffn->print($status, $dateInterval);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_interval_duration($dateInterval, 'P1D');
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_duration($dateInterval, 'P1.5D');
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_duration($dateInterval, new \DateInterval('P1D'));
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_duration($dateInterval, \DateInterval::createFromDateString('+100 seconds'));
    $ffn->print($status, $dateInterval);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_interval_datestring($dateInterval, '+100 seconds');
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_datestring($dateInterval, new \DateInterval('P1D'));
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_datestring($dateInterval, \DateInterval::createFromDateString('+100 seconds'));
    $ffn->print($status, $dateInterval);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_interval_microtime($dateInterval, '123.456');
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_microtime($dateInterval, new \DateInterval('P1D'));
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_microtime($dateInterval, \DateInterval::createFromDateString('+100 seconds'));
    $ffn->print($status, $dateInterval);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_interval_ago($dateInterval, new \DateTime('tomorrow midnight'), new \DateTime('now midnight'));
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_ago($dateInterval, new \DateInterval('P1D'));
    $ffn->print($status, $dateInterval);
    $status = \Gzhegow\Lib\Lib::date()->type_interval_ago($dateInterval, \DateInterval::createFromDateString('+100 seconds'));
    $ffn->print($status, $dateInterval);
    echo "\n";

    echo "\n";


    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject, '1970-01-01 midnight');
    $dateAtomString1 = $dateObject->format(DATE_ATOM);
    $ffn->print($status, $dateObject);

    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject2, $dateObject);
    $dateAtomString2 = $dateObject2->format(DATE_ATOM);
    $ffn->print($status, $dateObject2, $dateAtomString1 === $dateAtomString2);

    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject3, $dateAtomString1);
    $dateAtomString3 = $dateObject3->format(DATE_ATOM);
    $ffn->print($status, $dateObject3, $dateAtomString1 === $dateAtomString3);
    echo "\n";


    $status = \Gzhegow\Lib\Lib::date()->type_idate($dateImmutableObject, '1970-01-01 midnight');
    $dateAtomString1 = $dateImmutableObject->format(DATE_ATOM);
    $ffn->print($status, $dateImmutableObject);

    $status = \Gzhegow\Lib\Lib::date()->type_idate($dateImmutableObject2, $dateObject);
    $dateAtomString2 = $dateImmutableObject2->format(DATE_ATOM);
    $ffn->print($status, $dateImmutableObject2, $dateAtomString1 === $dateAtomString2);

    $status = \Gzhegow\Lib\Lib::date()->type_idate($dateImmutableObject3, $dateAtomString1);
    $dateAtomString3 = $dateImmutableObject3->format(DATE_ATOM);
    $ffn->print($status, $dateImmutableObject3, $dateAtomString1 === $dateAtomString3);
    echo "\n";


    $status = \Gzhegow\Lib\Lib::date()->type_date($dateObject, '1970-01-01 midnight');
    $dateAtomString1 = $dateObject->format(DATE_ATOM);
    $ffn->print($status, $dateObject);

    $status = \Gzhegow\Lib\Lib::date()->type_date($dateObject2, $dateObject);
    $dateAtomString2 = $dateObject2->format(DATE_ATOM);
    $ffn->print($status, $dateObject2, $dateAtomString1 === $dateAtomString2);

    $status = \Gzhegow\Lib\Lib::date()->type_idate($dateImmutableObject, $from = $dateObject);
    $dateAtomString3 = $dateImmutableObject->format(DATE_ATOM);
    $ffn->print($status, $dateImmutableObject, $dateAtomString1 === $dateAtomString3);

    $status = \Gzhegow\Lib\Lib::date()->type_date($dateImmutableObject2, $dateImmutableObject);
    $dateAtomString4 = $dateImmutableObject2->format(DATE_ATOM);
    $ffn->print($status, $dateImmutableObject2, $dateAtomString1 === $dateAtomString4);
    echo "\n";


    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject1, '1970-01-01 midnight');
    $dateAtomString = $dateObject1->format(DATE_ATOM);
    $ffn->print($status, $dateObject1);

    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject2, '1970-01-01 midnight', 'EET');
    $dateAtomString2 = $dateObject2->format(DATE_ATOM);
    $ffn->print($status, $dateObject2, $dateAtomString !== $dateAtomString2);
    echo "\n";

    echo "\n";


    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject, '1970-01-01 12:34:56');
    $ffn->print($status, $dateObject);
    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject, '1970-01-01 12:34:56.456');
    $ffn->print($status, $dateObject);
    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject, '1970-01-01 12:34:56.456789');
    $ffn->print($status, $dateObject);
    $status = \Gzhegow\Lib\Lib::date()->type_adate($dateObject, '1970-01-01 12:34:56.456789', 'EET');
    $ffn->print($status, $dateObject);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz($result, '1970-01-01 12:34:56 +0100');
    $ffn->print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz($result, '1970-01-01 12:34:56.456 EET');
    $ffn->print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz($result, '1970-01-01 12:34:56.456789 Europe/Minsk');
    $ffn->print($status, $result);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_adate_formatted($result, '1970-01-01 00:00:00 +0100', 'Y-m-d H:i:s O',);
    $ffn->print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_formatted($result, '1970-01-01 00:00:00 EET', 'Y-m-d H:i:s T',);
    $ffn->print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_formatted($result, '1970-01-01 00:00:00 Europe/Minsk', 'Y-m-d H:i:s e',);
    $ffn->print($status, $result);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz_formatted($result, '1970-01-01 00:00:00 +0100', 'Y-m-d H:i:s O',);
    $ffn->print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz_formatted($result, '1970-01-01 00:00:00 EET', 'Y-m-d H:i:s T',);
    $ffn->print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_tz_formatted($result, '1970-01-01 00:00:00 Europe/Minsk', 'Y-m-d H:i:s e',);
    $ffn->print($status, $result);
    echo "\n";

    $status = \Gzhegow\Lib\Lib::date()->type_adate_microtime($result, '0');
    $ffn->print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_microtime($result, '123');
    $ffn->print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_microtime($result, '123.456');
    $ffn->print($status, $result);
    $status = \Gzhegow\Lib\Lib::date()->type_adate_microtime($result, '123.456', 'EET');
    $ffn->print($status, $result);
    echo "\n";


    date_default_timezone_set($before);
};
$test = $ffn->test($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80200, '
"[ DateModule ]"

TRUE | { object(serializable) # DateTimeZone # "+01:00" }
TRUE | { object(serializable) # DateTimeZone # "EET" }
TRUE | { object(serializable) # DateTimeZone # "Europe/Minsk" }
TRUE | { object(serializable) # DateTimeZone # "UTC" }
TRUE | { object(serializable) # DateTimeZone # "UTC" }

TRUE | { object(serializable) # DateTimeZone # "+01:00" }
TRUE | { object(serializable) # DateTimeZone # "+01:00" }
TRUE | { object(serializable) # DateTimeZone # "+01:00" }

TRUE | { object(serializable) # DateTimeZone # "EET" }
TRUE | { object(serializable) # DateTimeZone # "EET" }
TRUE | { object(serializable) # DateTimeZone # "EET" }

TRUE | { object(serializable) # DateTimeZone # "Europe/Minsk" }
TRUE | { object(serializable) # DateTimeZone # "Europe/Minsk" }
TRUE | { object(serializable) # DateTimeZone # "Europe/Minsk" }

TRUE | { object(serializable) # DateTimeZone # "EET" }
TRUE | { object(serializable) # DateTimeZone # "Europe/Minsk" }
TRUE | { object(serializable) # DateTimeZone # "EET" }
TRUE | { object(serializable) # DateTimeZone # "Europe/Minsk" }


TRUE | { object(serializable) # DateInterval # "P1D" }
TRUE | { object(serializable) # DateInterval # "P1DT12H" }
TRUE | { object(serializable) # DateInterval # "PT100S" }
TRUE | { object(serializable) # DateInterval # "P1D" }
TRUE | { object(serializable) # DateInterval # "PT100S" }

TRUE | { object(serializable) # DateInterval # "P1D" }
TRUE | { object(serializable) # DateInterval # "P1DT12H" }
TRUE | { object(serializable) # DateInterval # "P1D" }
TRUE | { object(serializable) # DateInterval # "PT100S" }

TRUE | { object(serializable) # DateInterval # "PT100S" }
TRUE | { object(serializable) # DateInterval # "P1D" }
TRUE | { object(serializable) # DateInterval # "PT100S" }

TRUE | { object(serializable) # DateInterval # "PT2M3.456S" }
TRUE | { object(serializable) # DateInterval # "P1D" }
TRUE | { object(serializable) # DateInterval # "PT100S" }

TRUE | { object(serializable) # DateInterval # "P1D" }
TRUE | { object(serializable) # DateInterval # "P1D" }
TRUE | { object(serializable) # DateInterval # "PT100S" }


TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+00:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+00:00" } | TRUE
TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+00:00" } | TRUE

TRUE | { object(serializable) # DateTimeImmutable # "1970-01-01T00:00:00.000000+00:00" }
TRUE | { object(serializable) # DateTimeImmutable # "1970-01-01T00:00:00.000000+00:00" } | TRUE
TRUE | { object(serializable) # DateTimeImmutable # "1970-01-01T00:00:00.000000+00:00" } | TRUE

TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+00:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+00:00" } | TRUE
TRUE | { object(serializable) # DateTimeImmutable # "1970-01-01T00:00:00.000000+00:00" } | TRUE
TRUE | { object(serializable) # DateTimeImmutable # "1970-01-01T00:00:00.000000+00:00" } | TRUE

TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+00:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+02:00" } | TRUE


TRUE | { object(serializable) # DateTime # "1970-01-01T12:34:56.000000+00:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T12:34:56.456000+00:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T12:34:56.456789+00:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T12:34:56.456789+02:00" }

TRUE | { object(serializable) # DateTime # "1970-01-01T12:34:56.000000+01:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T12:34:56.456000+02:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T12:34:56.456789+03:00" }

TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+01:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+02:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+03:00" }

TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+01:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+02:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+03:00" }

TRUE | { object(serializable) # DateTime # "1970-01-01T00:00:00.000000+00:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T00:02:03.000000+00:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T00:02:03.000456+00:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T02:02:03.000456+02:00" }
');
$test->expectStdoutIf(PHP_VERSION_ID < 80200, '
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
$test->run();



// >>> TEST
// > тесты DebugModule
$fn = function () use ($ffn) {
    $ffn->print('[ DebugModule ]');
    echo "\n";


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    $ffn->print($isDiff);
    $ffn->print_array_multiline($diffLines);
    echo "\n";


    echo "\n";


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple2\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    $ffn->print($isDiff);
    $ffn->print_array_multiline($diffLines);
    echo "\n";

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry2\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    $ffn->print($isDiff);
    $ffn->print_array_multiline($diffLines);
    echo "\n";

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry2";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    $ffn->print($isDiff);
    $ffn->print_array_multiline($diffLines);
    echo "\n";


    echo "\n";


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "fig\napple\nbanana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    $ffn->print($isDiff);
    $ffn->print_array_multiline($diffLines);
    echo "\n";

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\nfig\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    $ffn->print($isDiff);
    $ffn->print_array_multiline($diffLines);
    echo "\n";

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson\nelderberry\nfig";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    $ffn->print($isDiff);
    $ffn->print_array_multiline($diffLines);
    echo "\n";


    echo "\n";


    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "banana\ncherry\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    $ffn->print($isDiff);
    $ffn->print_array_multiline($diffLines);
    echo "\n";

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ndamson\nelderberry";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    $ffn->print($isDiff);
    $ffn->print_array_multiline($diffLines);
    echo "\n";

    $oldText = "apple\nbanana\ncherry\ndamson\nelderberry";
    $newText = "apple\nbanana\ncherry\ndamson";
    $isDiff = \Gzhegow\Lib\Lib::debug()->diff($oldText, $newText, [ &$diffLines ]);
    $ffn->print($isDiff);
    $ffn->print_array_multiline($diffLines);
    echo "\n";


    echo "\n";


    echo \Gzhegow\Lib\Lib::debug()->value(null) . "\n";
    echo \Gzhegow\Lib\Lib::debug()->value(false) . "\n";
    echo \Gzhegow\Lib\Lib::debug()->value(1) . "\n";
    echo \Gzhegow\Lib\Lib::debug()->value(1.1) . "\n";
    echo \Gzhegow\Lib\Lib::debug()->value('string') . "\n";
    echo \Gzhegow\Lib\Lib::debug()->value([]) . "\n";
    echo \Gzhegow\Lib\Lib::debug()->value((object) []) . "\n";
    echo \Gzhegow\Lib\Lib::debug()->value(STDOUT) . "\n";

    echo "\n";

    $stdClass = (object) [];
    echo \Gzhegow\Lib\Lib::debug()->value(
        [
            [ 1, 'apple', $stdClass ],
            [ 2, 'apples', $stdClass ],
            [ 1.5, 'apples', $stdClass ],
        ]
    );
    echo "\n";
    echo \Gzhegow\Lib\Lib::debug()->value_array(
        [
            [ 1, 'apple', $stdClass ],
            [ 2, 'apples', $stdClass ],
            [ 1.5, 'apples', $stdClass ],
        ], 2
    );
    echo "\n";


    echo "\n";


    echo \Gzhegow\Lib\Lib::debug()->value_multiline(
        [
            [ 1, 'apple', $stdClass ],
            [ 2, 'apples', $stdClass ],
            [ 1.5, 'apples', $stdClass ],
        ]
    );
    echo "\n";

    echo \Gzhegow\Lib\Lib::debug()->value_array_multiline(
        [
            [ 1, 'apple', $stdClass ],
            [ 2, 'apples', $stdClass ],
            [ 1.5, 'apples', $stdClass ],
        ], 2
    );

    echo "\n";

    echo "\n";


    $varToPrint = '<div class="block"></div>';

    $string = \Gzhegow\Lib\Lib::debug()
        ->cloneDumper()
        ->printer(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::PRINTER_VAR_DUMP)
        ->print($varToPrint)
    ;
    $ffn->print($string);

    $string = \Gzhegow\Lib\Lib::debug()
        ->cloneDumper()
        ->printer(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::PRINTER_PRINT_R)
        ->print($varToPrint)
    ;
    $ffn->print($string);

    $string = \Gzhegow\Lib\Lib::debug()
        ->cloneDumper()
        ->printer(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::PRINTER_JSON_ENCODE)
        ->print($varToPrint)
    ;
    $ffn->print($string);

    echo "\n";


    // $varToDump = '<div class="block"></div>';
    //
    // \Gzhegow\Lib\Lib::debug()
    //     ->cloneDumper()
    //     ->printer(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::PRINTER_VAR_DUMP)
    //     ->dumper(
    //         \Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::DUMPER_STDOUT,
    //         [ 'resource' => STDOUT ]
    //     )
    //     ->dump($varToDump)
    // ;
    //
    // \Gzhegow\Lib\Lib::debug()
    //     ->cloneDumper()
    //     ->printer(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::PRINTER_VAR_DUMP)
    //     ->dumper(
    //         \Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::DUMPER_STDOUT_HTML,
    //         [ 'resource' => STDOUT ]
    //     )
    //     ->dump($varToDump)
    // ;
    //
    // \Gzhegow\Lib\Lib::debug()
    //     ->cloneDumper()
    //     ->printer(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::PRINTER_VAR_DUMP)
    //     ->dumper(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::DUMPER_DEVTOOLS)
    //     ->dump($varToDump)
    // ;
    //
    // \Gzhegow\Lib\Lib::debug()
    //     ->cloneDumper()
    //     ->printer(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::PRINTER_VAR_DUMP)
    //     ->dumper(
    //         \Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::DUMPER_PDO,
    //         [
    //             'pdo'    => new \PDO('mysql:host=localhost;dbname=test', 'root', ''),
    //             'table'  => 'dump',
    //             'column' => 'var',
    //         ]
    //     )
    //     ->dump($varToDump)
    // ;
};
$test = $ffn->test($fn);
$test->expectStdout('
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
[]
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

"{ string(25) # \"<div class=\\\\"block\\\\"></div>\" }"
"<div class=\"block\"></div>"
"\"<div class=\\\\"block\\\\"></div>\""
');
$test->run();



// >>> TEST
// > тесты EscapeModule
$fn = function () use ($ffn) {
    $ffn->print('[ EscapeModule ]');
    echo "\n";


    $params = [];
    $sqlIn = \Gzhegow\Lib\Lib::escape()->sql_in($params, 'AND `user_id`', [ 1, 2, 3 ]);
    $ffn->print($sqlIn);
    $ffn->print_array($params);

    echo "\n";


    $params = [];
    $sqlIn = \Gzhegow\Lib\Lib::escape()->sql_in($params, 'AND `user_id`', [ 1, 2, 3 ], 'user_id');
    $ffn->print($sqlIn);
    $ffn->print_array($params);

    echo "\n";


    $sqlLike = \Gzhegow\Lib\Lib::escape()->sql_like_quote('Hello, _user_! How are you today, in percents (%)?', '\\');
    $ffn->print($sqlLike);

    $sqlLike = \Gzhegow\Lib\Lib::escape()->sql_like_escape(
        'AND `search`', 'ILIKE',
        'Hello, _user_! How are you today, in percents (%)?'
    );
    $ffn->print($sqlLike);

    $sqlLike = \Gzhegow\Lib\Lib::escape()->sql_like_escape(
        'AND `name`', 'LIKE',
        [ '__' ], 'user%%__', [ '%' ]
    );
    $ffn->print($sqlLike);

    echo "\n";
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ EscapeModule ]"

"AND `user_id` IN (?, ?, ?)"
[ 1, 2, 3 ]

"AND `user_id` IN (:user_id0, :user_id1, :user_id2)"
[ ":user_id0" => 1, ":user_id1" => 2, ":user_id2" => 3 ]

"Hello, \_user\_! How are you today, in percents (\%)?"
"AND `search` ILIKE \"Hello, \_user\_! How are you today, in percents (\%)?\""
"AND `name` LIKE \"__user\%\%\_\_%\""
');
$test->run();



// >>> TEST
// > тесты FormatModule
$fn = function () use ($ffn) {
    $ffn->print('[ FormatModule ]');
    echo "\n";


    $enc = \Gzhegow\Lib\Lib::format()->bytes_encode($src = 1024 * 1024);
    $ffn->print($enc);

    $dec = \Gzhegow\Lib\Lib::format()->bytes_decode($enc);
    $ffn->print($dec, $src === $dec);

    echo "\n";


    [ $csv, $bytes ] = \Gzhegow\Lib\Lib::format()->csv_encode_rows([ [ 'col1', 'col2' ], [ 'val1', 'val2' ] ]);
    $ffn->print($csv);
    $ffn->print($bytes);

    echo "\n";


    [ $csv, $bytes ] = \Gzhegow\Lib\Lib::format()->csv_encode_row([ 'col1', 'col2' ]);
    $ffn->print($csv);
    $ffn->print($bytes);
};
$test = $ffn->test($fn);
$test->expectStdout('
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
$test->run();



// >>> TEST
// > тесты FsModule
$fn = function () use ($ffn) {
    $ffn->print('[ FsModule ]');
    echo "\n";

    $theFs = \Gzhegow\Lib\Lib::fs();
    $f = $theFs->fileSafe();

    $file = $ffn->root() . '/var/1/1/1/1.txt';
    $realpath = $f->file_put_contents($file, '123', FILE_APPEND, [], [ 0775, true ]);
    $relpath = $theFs->path_relative($realpath, $ffn->root());
    $ffn->print($relpath);

    $file = $ffn->root() . '/var/1/1/1.txt';
    $realpath = $f->file_put_contents($file, '123', FILE_APPEND, [], [ 0775, true ]);
    $relpath = $theFs->path_relative($realpath, $ffn->root());
    $ffn->print($relpath);

    $file = $ffn->root() . '/var/1/1.txt';
    $realpath = $f->file_put_contents($file, '123', FILE_APPEND, [], [ 0775, true ]);
    $relpath = $theFs->path_relative($realpath, $ffn->root());
    $ffn->print($relpath);

    echo "\n";


    $file = $ffn->root() . '/var/1/1/1/1.txt';
    $content = $f->file_get_contents($file);
    $ffn->print($content);

    echo "\n";


    $dir = $ffn->root() . '/var/1';
    foreach ( \Gzhegow\Lib\Lib::fs()->dir_walk_it($dir) as $spl ) {
        $spl->isDir()
            ? $f->rmdir($spl->getRealPath())
            : $f->unlink($spl->getRealPath());
    }
    $f->rmdir($ffn->root() . '/var/1');
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ FsModule ]"

"var\1\1\1\1.txt"
"var\1\1\1.txt"
"var\1\1.txt"

"123"
');
$test->run();



// >>> TEST
// > тесты ItertoolsModule
$fn = function () use ($ffn) {
    $ffn->print('[ ItertoolsModule ]');
    echo "\n";

    $it = \Gzhegow\Lib\Lib::itertools()->range_it(0, 10);
    $result = iterator_to_array($it);
    $ffn->print($result);

    echo PHP_EOL;


    $it = \Gzhegow\Lib\Lib::itertools()->range_it(0, 10);
    $it = \Gzhegow\Lib\Lib::itertools()->reversed_it($it);
    $result = iterator_to_array($it);
    $ffn->print($result);

    echo PHP_EOL;


    $it = \Gzhegow\Lib\Lib::itertools()->product_it([ 'A', 'B', 'C', 'D' ], [ 'x', 'y' ]);
    $result = iterator_to_array($it);
    $result = array_map('implode', $result);
    $ffn->print_array($result);

    echo PHP_EOL;


    $it = \Gzhegow\Lib\Lib::itertools()->product_repeat_it(3, [ '0', '1' ]);
    $result = iterator_to_array($it);
    $result = array_map('implode', $result);
    $ffn->print_array($result);

    echo PHP_EOL;


    $it = \Gzhegow\Lib\Lib::itertools()->combinations_unique_it([ 'A', 'B', 'C', 'D' ], 2);
    $result = iterator_to_array($it);
    $result = array_map('implode', $result);
    $ffn->print_array($result);

    $it = \Gzhegow\Lib\Lib::itertools()->combinations_unique_it([ '0', '1', '2', '3' ], 3);
    $result = iterator_to_array($it);
    $result = array_map('implode', $result);
    $ffn->print_array($result);

    echo PHP_EOL;


    $it = \Gzhegow\Lib\Lib::itertools()->combinations_all_it([ 'A', 'B', 'C' ], 2);
    $result = iterator_to_array($it);
    $result = array_map('implode', $result);
    $ffn->print_array($result);

    echo PHP_EOL;


    $it = \Gzhegow\Lib\Lib::itertools()->permutations_it([ 'A', 'B', 'C', 'D' ], 2);
    $result = iterator_to_array($it);
    $result = array_map('implode', $result);
    $ffn->print_array($result);

    $it = \Gzhegow\Lib\Lib::itertools()->permutations_it([ '0', '1', '2' ]);
    $result = iterator_to_array($it);
    $result = array_map('implode', $result);
    $ffn->print_array($result);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ ItertoolsModule ]"

[ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ]

[ 10 => 10, 9 => 9, 8 => 8, 7 => 7, 6 => 6, 5 => 5, 4 => 4, 3 => 3, 2 => 2, 1 => 1, 0 => 0 ]

[ "Ax", "Ay", "Bx", "By", "Cx", "Cy", "Dx", "Dy" ]

[ "000", "001", "010", "011", "100", "101", "110", "111" ]

[ "AB", "AC", "AD", "BC", "BD", "CD" ]
[ "012", "013", "023", "123" ]

[ "AA", "AB", "AC", "BB", "BC", "CC" ]

[ "AB", "AC", "AD", "BA", "BC", "BD", "CA", "CB", "CD", "DA", "DB", "DC" ]
[ "012", "021", "102", "120", "201", "210" ]
');
$test->run();



// >>> TEST
// > тесты JsonModule
$fn = function () use ($ffn) {
    $ffn->print('[ JsonModule ]');
    echo "\n";


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


    try {
        \Gzhegow\Lib\Lib::json()->json_decode(null, true, []);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    try {
        \Gzhegow\Lib\Lib::json()->jsonc_decode(null, true, []);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    $result = \Gzhegow\Lib\Lib::json()->json_decode(null, true, [ null ]);
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode(null, true, [ null ]);
    $ffn->print($result);

    echo "\n";


    $result = \Gzhegow\Lib\Lib::json()->json_decode($json1, true, []);
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode($json1, true, []);
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::json()->json_decode($json2, true, []);
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode($json2, true, []);
    $ffn->print($result);

    echo "\n";


    try {
        \Gzhegow\Lib\Lib::json()->json_decode($jsonWithComment1, true, []);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    try {
        \Gzhegow\Lib\Lib::json()->json_decode($jsonWithComment2, true, []);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode($jsonWithComment1, true, []);
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::json()->jsonc_decode($jsonWithComment2, true, []);
    $ffn->print($result);

    echo "\n";


    try {
        \Gzhegow\Lib\Lib::json()->json_encode(null, [], false);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    $result = \Gzhegow\Lib\Lib::json()->json_encode(null, [ null ], false);
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::json()->json_encode(null, [], true);
    $ffn->print($result);

    echo "\n";


    try {
        \Gzhegow\Lib\Lib::json()->json_encode($value = NAN);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    $result = \Gzhegow\Lib\Lib::json()->json_encode(
        $value = NAN,
        $fallback = [ "NAN" ]
    );
    $ffn->print($result);

    echo "\n";


    $result = \Gzhegow\Lib\Lib::json()->json_encode("привет");
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::json()->json_print("привет");
    $ffn->print($result);
};
$test = $ffn->test($fn);
$test->expectStdout('
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
$test->run();



// >>> TEST
// > тесты NetModule
$fn = function () use ($ffn) {
    $ffn->print('[ NetModule ]');
    echo "\n";

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

            $ffn->print($ip, $subnet, $status1, $status1 === $status2);
        }

        echo "\n";
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

            $ffn->print($ip, $subnet, $status1, $status1 === $status2);
        }

        echo "\n";
    }
};
$test = $ffn->test($fn);
$test->expectStdout('
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
$test->run();



// >>> TEST
// > тесты ParseModule
$fn = function () use ($ffn) {
    $ffn->print('[ ParseModule ]');
    echo "\n";


    $result = \Gzhegow\Lib\Lib::parse()->ctype_digit('123');
    $ffn->print($result);
    echo "\n";


    $result = \Gzhegow\Lib\Lib::parse()->ctype_alpha('abcABC');
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::parse()->ctype_alpha('abcABC', false);
    $ffn->print($result);
    echo "\n";


    $result = \Gzhegow\Lib\Lib::parse()->ctype_alnum('123abcABC');
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::parse()->ctype_alnum('123abcABC', false);
    $ffn->print($result);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ ParseModule ]"

"123"

"abcABC"
NULL

"123abcABC"
NULL
');
$test->run();



// >>> TEST
// > тесты PhpModule
$fn = function () use ($ffn) {
    $ffn->print('[ PhpModule ]');
    echo "\n";


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
            echo __METHOD__ . "\n";
        }

        protected function protectedMethod()
        {
            echo __METHOD__ . "\n";
        }

        private function privateMethod()
        {
            echo __METHOD__ . "\n";
        }


        public static function publicStaticMethod()
        {
            echo __METHOD__ . "\n";
        }

        protected static function protectedStaticMethod()
        {
            echo __METHOD__ . "\n";
        }

        private static function privateStaticMethod()
        {
            echo __METHOD__ . "\n";
        }
    }

    class PhpModuleDummy2
    {
        public function __call($name, $args)
        {
            echo __METHOD__ . "\n";
        }
    }

    class PhpModuleDummy3
    {
        public static function __callStatic($name, $args)
        {
            echo __METHOD__ . "\n";
        }
    }

    class PhpModuleDummy4
    {
        public function __invoke()
        {
            echo __METHOD__ . "\n";
        }
    }

    function PhpModule_dummy_function()
    {
        echo __FUNCTION__ . "\n";
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

                $tableRow = $ffn->values(' / ', $src, $sourceProperty);
                $tableCol = $ffn->values(' / ', $tableColPublic, $tableColStatic);

                $table[ $tableRow ][ $tableCol ] = $ffn->value($status);
            }
        }
    }
    // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
    echo md5(serialize($table)) . "\n";
    unset($table);


    echo "\n";


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

                $tableRow = $ffn->values(' / ', $src, $sourceMethod);
                $tableCol = $ffn->values(' / ', $tableColPublic, $tableColStatic);

                $table[ $tableRow ][ $tableCol ] = $ffn->value($status);
            }
        }
    }
    // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
    echo md5(serialize($table)) . "\n";
    unset($table);


    echo "\n";


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
        $tableRow = $ffn->value($src);

        $status = \Gzhegow\Lib\Lib::php()->type_method_string($result, $src);
        $table1[ $tableRow ][ 'method_string' ] = $ffn->value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_method_array($result, $src);
        $table1[ $tableRow ][ 'method_array' ] = $ffn->value($result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable($result, $src, null);
        $table2[ $tableRow ][ 'callable' ] = $ffn->value($result);
        $table3[ $tableRow ][ 'callable' ] = $ffn->value($result);
        $table4[ $tableRow ][ 'callable' ] = $ffn->value($result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable_object($result, $src, null);
        $table2[ $tableRow ][ 'callable_object' ] = $ffn->value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_object_closure($result, $src, null);
        $table2[ $tableRow ][ 'callable_object_closure' ] = $ffn->value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_object_invokable($result, $src, null);
        $table2[ $tableRow ][ 'callable_object_invokable' ] = $ffn->value($result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable_array($result, $src, null);
        $table3[ $tableRow ][ 'callable_array' ] = $ffn->value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method($result, $src, null);
        $table3[ $tableRow ][ 'callable_array_method' ] = $ffn->value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method_static($result, $src, null);
        $table3[ $tableRow ][ 'callable_array_method_static' ] = $ffn->value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method_non_static($result, $src, null);
        $table3[ $tableRow ][ 'callable_array_method_non_static' ] = $ffn->value($result);


        $status = \Gzhegow\Lib\Lib::php()->type_callable_string($result, $src, null);
        $table4[ $tableRow ][ 'callable_string' ] = $ffn->value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function($result, $src, null);
        $table4[ $tableRow ][ 'callable_string_function' ] = $ffn->value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function_internal($result, $src, null);
        $table4[ $tableRow ][ 'callable_string_function_internal' ] = $ffn->value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function_non_internal($result, $src, null);
        $table4[ $tableRow ][ 'callable_string_function_non_internal' ] = $ffn->value($result);

        $status = \Gzhegow\Lib\Lib::php()->type_callable_string_method_static($result, $src, null);
        $table4[ $tableRow ][ 'callable_string_method_static' ] = $ffn->value($result);
    }
    // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
    echo md5(serialize($table1)) . "\n";
    echo md5(serialize($table2)) . "\n";
    echo md5(serialize($table3)) . "\n";
    echo md5(serialize($table4)) . "\n";
    unset($table1);
    unset($table2);
    unset($table3);
    unset($table4);


    echo "\n";


    $sources = [];
    $sourceClasses = [
        \PhpModuleDummy1::class,
        \PhpModuleDummy2::class,
        \PhpModuleDummy3::class,
        \PhpModuleDummy4::class,
    ];

    foreach ( $sourceClasses as $sourceClass ) {
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
    foreach ( $sources as $a ) {
        foreach ( $a as $aa ) {
            foreach ( $aa as $src ) {
                $tableRow = $ffn->value($src);

                $status = \Gzhegow\Lib\Lib::php()->type_method_array($result, $src);
                $table[ $tableRow ][ 'method_array' ] = $ffn->value($result);

                $status = \Gzhegow\Lib\Lib::php()->type_method_string($result, $src);
                $table[ $tableRow ][ 'method_string' ] = $ffn->value($result);
            }
        }
    }
    // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
    echo md5(serialize($table)) . "\n";
    unset($table);


    echo "\n";


    $table1 = [];
    $table2 = [];
    $table3 = [];
    foreach ( $sources as $a ) {
        foreach ( $a as $sourceClass => $aa ) {
            foreach ( $aa as $src ) {
                $tableRow = $ffn->value($src);

                $sourceScopes = [
                    'scope: global' => null,
                    'scope: local'  => $sourceClass,
                ];

                foreach ( $sourceScopes as $scopeKey => $scope ) {
                    $tableCol = $ffn->values(' / ', 'callable', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable($result, $src, $scope);
                    $table1[ $tableRow ][ $tableCol ] = $ffn->value($status);
                    $table2[ $tableRow ][ $tableCol ] = $ffn->value($status);
                    $table3[ $tableRow ][ $tableCol ] = $ffn->value($status);


                    $tableCol = $ffn->values(' / ', 'callable_object', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_object($result, $src, $scope);
                    $table1[ $tableRow ][ $tableCol ] = $ffn->value($result);

                    $tableCol = $ffn->values(' / ', 'callable_object_closure', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_object_closure($result, $src, $scope);
                    $table1[ $tableRow ][ $tableCol ] = $ffn->value($result);

                    $tableCol = $ffn->values(' / ', 'callable_object_invokable', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_object_invokable($result, $src, $scope);
                    $table1[ $tableRow ][ $tableCol ] = $ffn->value($result);


                    $tableCol = $ffn->values(' / ', 'callable_array', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_array($result, $src, $scope);
                    $table2[ $tableRow ][ $tableCol ] = $ffn->value($result);

                    $tableCol = $ffn->values(' / ', 'callable_array_method', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method($result, $src, $scope);
                    $table2[ $tableRow ][ $tableCol ] = $ffn->value($result);

                    $tableCol = $ffn->values(' / ', 'callable_array_method_static', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method_static($result, $src, $scope);
                    $table2[ $tableRow ][ $tableCol ] = $ffn->value($result);

                    $tableCol = $ffn->values(' / ', 'callable_array_method_non_static', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_array_method_non_static($result, $src, $scope);
                    $table2[ $tableRow ][ $tableCol ] = $ffn->value($result);


                    $tableCol = $ffn->values(' / ', 'callable_string', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string($result, $src, $scope);
                    $table3[ $tableRow ][ $tableCol ] = $ffn->value($result);

                    $tableCol = $ffn->values(' / ', 'callable_string_function', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function($result, $src);
                    $table3[ $tableRow ][ $tableCol ] = $ffn->value($result);

                    $tableCol = $ffn->values(' / ', 'callable_string_function_internal', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function_internal($result, $src);
                    $table3[ $tableRow ][ $tableCol ] = $ffn->value($result);

                    $tableCol = $ffn->values(' / ', 'callable_string_function_non_internal', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string_function_non_internal($result, $src);
                    $table3[ $tableRow ][ $tableCol ] = $ffn->value($result);

                    $tableCol = $ffn->values(' / ', 'callable_string_method_static', $scopeKey);
                    $status = \Gzhegow\Lib\Lib::php()->type_callable_string_method_static($result, $src, $scope);
                    $table3[ $tableRow ][ $tableCol ] = $ffn->value($result);
                }
            }
        }
    }
    // dd(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
    echo md5(serialize($table1)) . "\n";
    echo md5(serialize($table2)) . "\n";
    echo md5(serialize($table3)) . "\n";
    unset($table1);
    unset($table2);
    unset($table3);
    echo "\n";
};
$test = $ffn->test($fn);
$test->expectStdout('
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
$test->run();



// >>> TEST
// > тесты PregModule
$fn = function () use ($ffn) {
    $ffn->print('[ PregModule ]');
    echo "\n";


    $regex = \Gzhegow\Lib\Lib::preg()->preg_quote_ord("Hello, \x00!");
    $ffn->print($regex);
    echo "\n";


    $regex = \Gzhegow\Lib\Lib::preg()->preg_escape('/', '<html>', [ '.*' ], '</html>');
    $ffn->print($regex);

    $regex = \Gzhegow\Lib\Lib::preg()->preg_escape_ord(null, '/', '<html>', [ '.*' ], '</html>');
    $ffn->print($regex);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ PregModule ]"

"\x{48}\x{65}\x{6C}\x{6C}\x{6F}\x{2C}\x{20}\x{0}\x{21}"

"/\<html\>.*\<\/html\>/"
"/\x{3C}\x{68}\x{74}\x{6D}\x{6C}\x{3E}.*\x{3C}\x{2F}\x{68}\x{74}\x{6D}\x{6C}\x{3E}/"
');
$test->run();



// >>> TEST
// > тесты RandomModule
$fn = function () use ($ffn) {
    $ffn->print('[ RandomModule ]');
    echo "\n";

    $uuid = \Gzhegow\Lib\Lib::random()->uuid();
    $status = \Gzhegow\Lib\Lib::random()->type_uuid($result, $uuid);
    $ffn->print(strlen($uuid), $status);

    echo "\n";


    $rand = \Gzhegow\Lib\Lib::random()->random_bytes(16);
    $ffn->print($len = strlen($rand), $len === 16);

    $rand = \Gzhegow\Lib\Lib::random()->random_hex(16);
    $ffn->print($len = strlen($rand), $len === 32);

    $rand = \Gzhegow\Lib\Lib::random()->random_int(1, 100);
    $ffn->print(1 <= $rand, $rand <= 100);

    $rand = \Gzhegow\Lib\Lib::random()->random_string(16);
    $ffn->print(mb_strlen($rand) === 16);

    $rand = \Gzhegow\Lib\Lib::random()->random_base64_urlsafe(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            rtrim($rand, '='),
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_64_RFC4648_URLSAFE
        )
    ;
    $ffn->print(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base64(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            rtrim($rand, '='),
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_64_RFC4648
        )
    ;
    $ffn->print(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base62(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            $rand,
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_62
        )
    ;
    $ffn->print(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base58(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            $rand,
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_58
        )
    ;
    $ffn->print(null !== $test);

    $rand = \Gzhegow\Lib\Lib::random()->random_base36(16);
    $test = \Gzhegow\Lib\Lib::parse()
        ->base(
            $rand,
            \Gzhegow\Lib\Modules\CryptModule::ALPHABET_BASE_36
        )
    ;
    $ffn->print(null !== $test);
};
$test = $ffn->test($fn);
$test->expectStdout('
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
$test->run();



// >>> TEST
// > тесты SocialModule
$fn = function () use ($ffn) {
    $ffn->print('[ SocialModule ]');
    echo "\n";

    $status = \Gzhegow\Lib\Lib::social()->type_email($email, 'example@gmail.com');
    $ffn->print($status, $email);
    $status = \Gzhegow\Lib\Lib::social()->type_email($email, 'example@привет.рф');
    $ffn->print($status, $email);
    $status = \Gzhegow\Lib\Lib::social()->type_email($email, 'example@привет.рф', $filters = [ 'filter_unicode' ]);
    $ffn->print($status, $email);
    try {
        $status = \Gzhegow\Lib\Lib::social()->type_email($email, 'example@привет.рф', $filters = [ 'rfc' ]);
    }
    catch ( \Gzhegow\Lib\Exception\Runtime\ComposerException $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }
    echo "\n";

    $status = \Gzhegow\Lib\Lib::social()->type_email_non_fake($email, 'example@gmail.com');
    $ffn->print($status, $email);
    $status = \Gzhegow\Lib\Lib::social()->type_email_non_fake($email, 'example@привет.рф');
    $ffn->print($status, $email);
    $status = \Gzhegow\Lib\Lib::social()->type_email_non_fake($email, 'example@привет.рф', $filters = [ 'filter_unicode' ]);
    $ffn->print($status, $email);
    try {
        $status = \Gzhegow\Lib\Lib::social()->type_email_non_fake($email, 'example@привет.рф', $filters = [ 'rfc' ]);
    }
    catch ( \Gzhegow\Lib\Exception\Runtime\ComposerException $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }
    echo "\n";

    $status = \Gzhegow\Lib\Lib::social()->type_email_fake($email, 'no-reply@gmail.com');
    $ffn->print($status, $email);
    $status = \Gzhegow\Lib\Lib::social()->type_email_fake($email, 'email@example.com');
    $ffn->print($status, $email);
    echo "\n";


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
        $ffn->print($phone, $status, $result);

        $status = \Gzhegow\Lib\Lib::social()->type_phone_non_fake($result, $phone);
        $ffn->print($phone, $status, $result);

        try {
            $status = \Gzhegow\Lib\Lib::social()->type_phone_real($result, $phone, '');
        }
        catch ( \Gzhegow\Lib\Exception\Runtime\ComposerException $e ) {
            $ffn->print('[ CATCH ] ' . $e->getMessage());
        }

        $status = \Gzhegow\Lib\Lib::social()->type_tel($result, $phone);
        $ffn->print($phone, $status, $result);

        $status = \Gzhegow\Lib\Lib::social()->type_tel_non_fake($result, $phone);
        $ffn->print($phone, $status, $result);

        try {
            $status = \Gzhegow\Lib\Lib::social()->type_tel_real($result, $phone, '');
        }
        catch ( \Gzhegow\Lib\Exception\Runtime\ComposerException $e ) {
            $ffn->print('[ CATCH ] ' . $e->getMessage());
        }

        echo "\n";
    }

    $phoneManager = \Gzhegow\Lib\Lib::social()->clonePhoneManager();
    $phoneManager->usePhoneFakeDatelike(true);

    foreach ( $fakePhones as $phone ) {
        $result = $phoneManager->parsePhone($phone);
        $ffn->print($phone, $result);

        $result = $phoneManager->parsePhoneFake($phone);
        $ffn->print($phone, $result);

        $result = $phoneManager->parseTel($phone);
        $ffn->print($phone, $result);

        $result = $phoneManager->parseTelFake($phone);
        $ffn->print($phone, $result);

        echo "\n";
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
$test = $ffn->test($fn);
$test->expectStdout('
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
$test->run();



// >>> TEST
// > тесты StrModule
$fn = function () use ($ffn) {
    $ffn->print('[ StrModule ]');
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->lines("hello\nworld"));
    $ffn->print(\Gzhegow\Lib\Lib::str()->eol("hello\nworld"));
    $ffn->print(\Gzhegow\Lib\Lib::str()->lines('hello' . "\n" . 'world'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->eol('hello' . "\n" . 'world'));
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->strlen('Привет'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->strlen('Hello'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->strsize('Привет'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->strsize('Hello'));
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->lower('ПРИВЕТ'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->upper('привет'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->lcfirst('ПРИВЕТ'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->ucfirst('привет'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->lcwords('ПРИВЕТ МИР'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->ucwords('привет мир'));
    echo "\n";

    $status = \Gzhegow\Lib\Lib::str()->str_starts('привет', 'ПРИ', true, [ &$substr ]);
    $ffn->print($status, $substr);
    $status = \Gzhegow\Lib\Lib::str()->str_ends('приВЕТ', 'вет', true, [ &$substr ]);
    $ffn->print($status, $substr);
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->lcrop('азаза_привет_азаза', 'аза'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->rcrop('азаза_привет_азаза', 'аза'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->crop('азаза_привет_азаза', 'аза'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->unlcrop('"привет"', '"'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->unrcrop('"привет"', '"'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->uncrop('"привет"', '"'));
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->str_replace_limit('за', '_', 'а-зазаза-зазаза', 3));
    $ffn->print(\Gzhegow\Lib\Lib::str()->str_ireplace_limit('зА', '_', 'а-заЗАза-заЗАза', 3));
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->camel('-hello-world-foo-bar'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->camel('-helloWorldFooBar'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->camel('-HelloWorldFooBar'));

    $ffn->print(\Gzhegow\Lib\Lib::str()->pascal('-hello-world-foo-bar'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->pascal('-helloWorldFooBar'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->pascal('-HelloWorldFooBar'));

    $ffn->print(\Gzhegow\Lib\Lib::str()->space('_Hello_WORLD_Foo_BAR'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->snake('-Hello-WORLD-Foo-BAR'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->kebab(' Hello WORLD Foo BAR'));

    $ffn->print(\Gzhegow\Lib\Lib::str()->space_lower('_Hello_WORLD_Foo_BAR'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->snake_lower('-Hello-WORLD-Foo-BAR'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->kebab_lower(' Hello WORLD Foo BAR'));

    $ffn->print(\Gzhegow\Lib\Lib::str()->space_upper('_Hello_WORLD_Foo_BAR'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->snake_upper('-Hello-WORLD-Foo-BAR'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->kebab_upper(' Hello WORLD Foo BAR'));
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->prefix('primary'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->prefix('unique'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->prefix('index'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->prefix('fulltext'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->prefix('fullText'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->prefix('spatialIndex'));
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->translit_ru2ascii('привет мир'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->translit_ru2ascii('+привет +мир +100 abc', '-', '+'));
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->interpolator()->interpolate('привет {{username}}', [ 'username' => 'мир' ]));
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->slugger()->translit(' привет мир '));
    $ffn->print(\Gzhegow\Lib\Lib::str()->slugger()->translit(' привет мир ', null, [ 'и' ]));
    $ffn->print(\Gzhegow\Lib\Lib::str()->slugger()->slug('привет мир'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->slugger()->slug('привет мир', ':', [ 'и' ]));
    echo "\n";

    $ffn->print(\Gzhegow\Lib\Lib::str()->inflector()->singularize('users'));
    $ffn->print(\Gzhegow\Lib\Lib::str()->inflector()->pluralize('user'));
    echo "\n";


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
    $ffn->print_array_multiline(\Gzhegow\Lib\Lib::str()->str_match('users.*.name', $keys));
    $ffn->print_array_multiline(\Gzhegow\Lib\Lib::str()->str_match('users.*.name', $keys, '*'));
    $ffn->print_array_multiline(\Gzhegow\Lib\Lib::str()->str_match('users.*.name', $keys, '*', '.'));
    echo "\n";

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
    $ffn->print_array_multiline(\Gzhegow\Lib\Lib::str()->str_match("users\x00*\x00name", $keys));
    $ffn->print_array_multiline($a = \Gzhegow\Lib\Lib::str()->str_match("users\x00*\x00name", $keys, '*'));
    $ffn->print_array_multiline(\Gzhegow\Lib\Lib::str()->str_match("users\x00*\x00name", $keys, '*', "\x00"));
    echo "\n";
};
$test = $ffn->test($fn);
$test->expectStdout('
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
[]
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
[]
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
$test->run();



// >>> TEST
// > тесты UrlModule
$fn = function () use ($ffn) {
    $ffn->print('[ UrlModule ]');
    echo "\n";


    $result = \Gzhegow\Lib\Lib::url()->url($src = 'https://google.com/hello/world');
    $ffn->print($src, (bool) $result);

    $result = \Gzhegow\Lib\Lib::url()->url($src = ':hello/world');
    $ffn->print($src, (bool) $result);

    echo "\n";


    $result = \Gzhegow\Lib\Lib::url()->host($src = 'https://google.com/hello/world');
    $ffn->print($src, (bool) $result);

    $result = \Gzhegow\Lib\Lib::url()->host($src = ':hello/world');
    $ffn->print($src, (bool) $result);

    echo "\n";


    $result = \Gzhegow\Lib\Lib::url()->link($src = 'https://google.com/hello/world');
    $ffn->print($src, (bool) $result);

    $result = \Gzhegow\Lib\Lib::url()->link($src = ':hello/world');
    $ffn->print($src, (bool) $result);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ UrlModule ]"

"https://google.com/hello/world" | TRUE
":hello/world" | FALSE

"https://google.com/hello/world" | TRUE
":hello/world" | FALSE

"https://google.com/hello/world" | TRUE
":hello/world" | FALSE
');
$test->run();
```

