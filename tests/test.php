<?php

require_once __DIR__ . '/../vendor/autoload.php';


// > настраиваем PHP
// > некоторые CMS сами по себе применяют настройки глубоко в ядре
// > с помощью этого класса можно указать при загрузке свои собственные и вызвав методы ->use{smtg}() вернуть указанные
($ex = \Gzhegow\Lib\Lib::entrypoint())
    //
    // > частично удаляет путь файла из каждой строки `trace` (`trace[i][file]`) при обработке исключений
    ->setDirRoot(__DIR__ . '/..')
    //
    ->useErrorReporting()
    ->useErrorLog()
    ->useDisplayErrors()
    ->useErrorHandler()
    ->useExceptionHandler()
    //
    ->useMemoryLimit()
    //
    ->useMaxExecutionTime()
    ->useMaxInputTime()
    //
    ->useObImplicitFlush()
    //
    ->useTimezoneDefault()
    //
    ->usePostMaxSize()
    //
    ->useUploadMaxFilesize()
    ->useUploadTmpDir()
    //
    ->usePrecision()
    //
    ->useUmask()
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


    function test(\Closure $fn, array $args = []) : \Gzhegow\Lib\Modules\Test\Test
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return \Gzhegow\Lib\Lib::test()->newTest()
            ->fn($fn, $args)
            ->trace($trace)
        ;
    }
};



// >>> TEST
// > тесты Promise
$fn = function () use ($ffn) {
    $ffn->print('[ Promise ]');
    echo "\n";


    \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(900)
        ->then(function () use ($ffn) {
            echo "\n";
            $ffn->print("[ 900 | THEN 1.1 ]");

            return 123;
        })
        ->then(function ($value) use ($ffn) {
            $ffn->print("[ 900 | THEN 1.2 ]", $value);

            throw new \Gzhegow\Lib\Exception\RuntimeException('123');
        })
        ->catch(function ($reason) use ($ffn) {
            $ffn->print("[ 900 | CATCH 1.3 ]", $reason);

            return \Gzhegow\Lib\Modules\Async\Promise\Promise::rejected($reason);
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
        ->then(function () use ($ffn) {
            echo "\n";
            $ffn->print("[ 800 | THEN 2.1 ]");

            return 123;
        })
        ->then(function ($value) use ($ffn) {
            $ffn->print("[ 800 | THEN 2.2 ]", $value);

            $var = yield \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(400)
                ->then(function () {
                    return 456;
                })
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
        \Gzhegow\Lib\Modules\Async\Promise\Promise::resolved('[ 100 | ALL ] 3'),
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
        \Gzhegow\Lib\Modules\Async\Promise\Promise::resolved('[ 200 | ALL SETTLED ] 1'),
        \Gzhegow\Lib\Modules\Async\Promise\Promise::delay(200)->then(function () { return '[ 200 | ALL SETTLED ] 2'; }),
        \Gzhegow\Lib\Modules\Async\Promise\Promise::rejected('[ 200 | ALL SETTLED ] 3'),
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
        \Gzhegow\Lib\Modules\Async\Promise\Promise::rejected('[ 500 | ANY ] 2'),
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


"[ 600 | TIMEOUT ]" | { object(iterable stringable) # Gzhegow\Lib\Exception\RuntimeException # "Timeout: 600ms" }


"[ 800 | THEN 2.1 ]"
"[ 800 | THEN 2.2 ]" | 123

"[ 900 | THEN 1.1 ]"
"[ 900 | THEN 1.2 ]" | 123
"[ 900 | CATCH 1.3 ]" | { object(iterable stringable) # Gzhegow\Lib\Exception\RuntimeException # "123" }
"[ 900 | CATCH 1.4 ]" | { object(iterable stringable) # Gzhegow\Lib\Exception\RuntimeException # "123" }
"[ 900 | THEN 1.5 ]" | { object(iterable stringable) # Gzhegow\Lib\Exception\RuntimeException # "123" }

"[ 800+400 | THEN 2.3 ]" | 456
');
$test->expectSecondsMin(1.2);
$test->expectSecondsMax(1.3);
$test->run();



// // > Тест закомментирован, поскольку приводит к поднятию дополнительных процессов
// // > Если неверные права пользователя в Unix, с этим будут проблемы, раскоментируйте, чтобы протестировать
// // >>> TEST
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
// die();



// >>> TEST
// > тесты Exception
$fn = function () use ($ffn) {
    $ffn->print('[ Exception ]');
    echo "\n";

    $eeee1 = new \Exception('eeee1', 0);
    $eeee2 = new \Exception('eeee2', 0);

    $eee0 = new \Gzhegow\Lib\Exception\LogicException('eee', $eeee1, $eeee2);

    $ee1 = new \Exception('ee1', 0, $previous = $eee0);
    $ee2 = new \Exception('ee2', 0, $previous = $eee0);

    $previousList = [ $ee1, $ee2 ];
    $e0 = new \Gzhegow\Lib\Exception\RuntimeException('e', 0, ...$previousList);

    $messages = \Gzhegow\Lib\Lib::debugThrowabler()
        ->getPreviousMessagesAllLines($e0, _DEBUG_THROWABLE_WITHOUT_FILE)
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


    $fnStrval = function ($input) {
        echo '> fnStrval' . "\n";

        return strval($input);
    };
    $fnStrlen = function ($input) {
        echo '> fnStrlen' . "\n";

        return strlen($input);
    };
    $fnTapCustom = function ($input) use ($ffn) {
        echo '> fnTapCustom' . "\n";

        $ffn->print($input);

        throw new \Gzhegow\Lib\Exception\RuntimeException('This is the exception');
    };
    $fnIntval = function ($value) {
        echo '> fnIntval' . "\n";

        return intval($value);
    };

    $fnCatch = function (\Throwable $e, $input, $context, array $args = []) {
        echo '> fnCatch' . "\n";

        if ($e instanceof \RuntimeException) {
            return $args[ 2 ];
        }

        return $e;
    };
    $fnCatchArgs = [
        // 0 => null, // > ключ будет добавлен и заполнен NULL
        // 1 => null, // > ключ будет добавлен и заполнен NULL
        2 => 'new_result',
    ];

    $fnMiddleware = function ($fnNext, $input) {
        echo '> fnMiddleware::before' . "\n";

        $result = $fnNext($input);

        echo '> fnMiddleware::after' . "\n";

        return $result;
    };
    $fnMiddlewareStep1 = function ($input) {
        echo '> fnMiddlewareStep1' . "\n";

        return $input . '1';
    };
    $fnMiddlewareStep2 = function ($input) {
        echo '> fnMiddlewareStep2' . "\n";

        return $input . '2';
    };


    $pipe = \Gzhegow\Lib\Lib::func()->newPipe();
    $pipe
        // > этот шаг может заменить значение, в данном случае приведя его к строке
        ->map($fnStrval)
        //
        // > этот шаг может очистить значение (в последующих шаг будет использоваться NULL)
        ->filter($fnStrlen)
        //
        // > этот шаг может выполнить сторонние действия, а возврат метода игнорируется
        ->tap($fnTapCustom)
        //
        // > этот шаг никогда не начнется, поскольку в прошлом шаге было выброшено исключение
        ->map($fnIntval)
        //
        // > или можно обрабатывать исключения обычным способом через callable
        ->catch($fnCatch, $fnCatchArgs)
        //
        // > ещё можно добавить обёртки-middleware
        ->middleware($fnMiddleware)
        /**/ ->map($fnMiddlewareStep1)
        /**/ ->map($fnMiddlewareStep2)
        ->endMiddleware()
    ;


    $result = $pipe->run(0);
    $ffn->print($result);
    echo "\n";

    $result = $pipe->run(1);
    $ffn->print($result);
    echo "\n";


    $result = $pipe('');
    $ffn->print($result);
    echo "\n";

    $result = $pipe('0');
    $ffn->print($result);
    echo "\n";

    $result = $pipe('1');
    $ffn->print($result);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ Pipe ]"

> fnStrval
> fnStrlen
> fnTapCustom
"0"
> fnCatch
> fnMiddleware::before
> fnMiddlewareStep1
> fnMiddlewareStep2
> fnMiddleware::after
"new_result12"

> fnStrval
> fnStrlen
> fnTapCustom
"1"
> fnCatch
> fnMiddleware::before
> fnMiddlewareStep1
> fnMiddlewareStep2
> fnMiddleware::after
"new_result12"

> fnStrval
> fnStrlen
> fnTapCustom
NULL
> fnCatch
> fnMiddleware::before
> fnMiddlewareStep1
> fnMiddlewareStep2
> fnMiddleware::after
"new_result12"

> fnStrval
> fnStrlen
> fnTapCustom
"0"
> fnCatch
> fnMiddleware::before
> fnMiddlewareStep1
> fnMiddlewareStep2
> fnMiddleware::after
"new_result12"

> fnStrval
> fnStrlen
> fnTapCustom
"1"
> fnCatch
> fnMiddleware::before
> fnMiddlewareStep1
> fnMiddlewareStep2
> fnMiddleware::after
"new_result12"
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

"[ CATCH ] The `value` should be a value of type: object"
{ object(countable(2) iterable serializable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP8\ArrayOfType }
TRUE | [ "{ object # stdClass }", "{ object(countable(0) iterable serializable) # ArrayObject }" ]

"[ CATCH ] The `value` should be an instance of the class: stdClass"
"[ CATCH ] The `value` should be an instance of the class: stdClass"
"[ CATCH ] The `value` should be an object"
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

"[ CATCH ] The `value` should be a value of type: object"
{ object(countable(2) iterable serializable) # Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7\ArrayOfType }
TRUE | [ "{ object # stdClass }", "{ object(countable(0) iterable serializable) # ArrayObject }" ]

"[ CATCH ] The `value` should be an instance of the class: stdClass"
"[ CATCH ] The `value` should be an instance of the class: stdClass"
"[ CATCH ] The `value` should be an object"
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

        protected function validation(array &$refContext = []) : bool
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
                    [ 'The `value` should be a string', $value ],
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
                    [ 'The `value` should be a non-empty string', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return \Gzhegow\Lib\Modules\Php\Result\Result::ok($res, $value);
        }
    }


    $strInterpolator = \Gzhegow\Lib\Lib::str()->interpolator();


    $invalidValue = NAN;
    $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::type();
    $status = false
        || \Gzhegow\Lib\Modules\Bcmath\Number::fromStatic($invalidValue, $ctx)
        || \Gzhegow\Lib\Modules\Bcmath\Number::fromValidArray($invalidValue, $ctx);

    $errorList = $ctx->getErrorList();
    $errorList = array_map([ $strInterpolator, 'interpolateMessage' ], $errorList);
    $ffn->print_array_multiline($errorList, 2);

    echo "\n";


    $invalidValue = NAN;
    $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::parse();
    $value = null
        ?? \Gzhegow\Lib\Modules\Bcmath\Number::fromStatic($invalidValue, $ctx)
        ?? \Gzhegow\Lib\Modules\Bcmath\Number::fromValidArray($invalidValue, $ctx);

    $errorList = $ctx->getErrorList();
    $errorList = array_map([ $strInterpolator, 'interpolateMessage' ], $errorList);
    $ffn->print_array_multiline($errorList, 2);

    echo "\n";


    $invalidValue = NAN;
    $ctx = \Gzhegow\Lib\Modules\Php\Result\Result::parseThrow();
    try {
        \Gzhegow\Lib\Modules\Bcmath\Number::fromStatic($invalidValue, $ctx);
    }
    catch ( \Throwable $e ) {
        $messages = \Gzhegow\Lib\Lib::debugThrowabler()->getPreviousMessagesAllLines($e, _DEBUG_THROWABLE_WITHOUT_FILE);

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
        $messages = \Gzhegow\Lib\Lib::debugThrowabler()->getPreviousMessagesAllLines($e, _DEBUG_THROWABLE_WITHOUT_FILE);

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
        $messages = \Gzhegow\Lib\Lib::debugThrowabler()->getPreviousMessagesAllLines($e, _DEBUG_THROWABLE_WITHOUT_FILE);

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
  "The `from` should be an instance of: Gzhegow\Lib\Modules\Bcmath\Number",
  "The `from` should be array"
]
###

###
[
  "The `from` should be an instance of: Gzhegow\Lib\Modules\Bcmath\Number",
  "The `from` should be array"
]
###

[ 0 ] The `from` should be an instance of: Gzhegow\Lib\Modules\Bcmath\Number
{ object # Gzhegow\Lib\Exception\LogicException }

[ 0 ] The `value` should be a string
{ object # Gzhegow\Lib\Exception\LogicException }

[ 0 ] The `value` should be a string
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

    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('0', 0);
    $ffn->print('bcabs', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 0);
    $ffn->print('bcabs', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 0);
    $ffn->print('bcabs', (string) $result, $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('0', 0);
    $ffn->print('bcceil', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('1.005', 0);
    $ffn->print('bcceil', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcceil('-1.005', 0);
    $ffn->print('bcceil', (string) $result, $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('0', 0);
    $ffn->print('bcfloor', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('1.005', 0);
    $ffn->print('bcfloor', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfloor('-1.005', 0);
    $ffn->print('bcfloor', (string) $result, $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcmod(5.75, 2);
    $ffn->print('bcmod', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmod(-5.75, 2);
    $ffn->print('bcmod', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmod(5.75, 2.25);
    $ffn->print('bcmod', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcmod(-5.75, 2.25);
    $ffn->print('bcmod', (string) $result, $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcfmod(5.75, 2, 2);
    $ffn->print('bcfmod', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfmod(-5.75, 2, 2);
    $ffn->print('bcfmod', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfmod(5.75, 2.25, 2);
    $ffn->print('bcfmod', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcfmod(-5.75, 2.25, 2);
    $ffn->print('bcfmod', (string) $result, $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bcgcd(8, 12);
    $ffn->print('bcgcd', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bcgcd(7, 13);
    $ffn->print('bcgcd', (string) $result, $result);
    echo "\n";

    $result = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 6);
    $ffn->print('bclcm', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 5);
    $ffn->print('bclcm', (string) $result, $result);
    $result = \Gzhegow\Lib\Lib::bcmath()->bclcm(8, 10);
    $ffn->print('bclcm', (string) $result, $result);
    echo "\n";


    $values = [];
    $values[] = [
        -2.4,
        -2.04,
        -2.004,
        -1.4,
        -1.04,
        -1.004,
        -0.4,
        -0.04,
        -0.004,
        0,
        0.004,
        0.04,
        0.4,
        1.004,
        1.04,
        1.4,
        2.004,
        2.04,
        2.4,
    ];
    $values[] = [
        -2.5,
        -2.05,
        -2.005,
        -1.5,
        -1.05,
        -1.005,
        -0.5,
        -0.05,
        -0.005,
        0,
        0.005,
        0.05,
        0.5,
        1.005,
        1.05,
        1.5,
        2.005,
        2.05,
        2.5,
    ];
    $values[] = [
        -2.6,
        -2.06,
        -2.006,
        -1.6,
        -1.06,
        -1.006,
        -0.6,
        -0.06,
        -0.006,
        0,
        0.006,
        0.06,
        0.6,
        1.006,
        1.06,
        1.6,
        2.006,
        2.06,
        2.6,
    ];

    $precisions = [ 0, 2 ];

    $modes = [];
    $modes[ 'ROUNDING' ] = [
        'R_AWAY_FROM_ZERO'  => _NUM_ROUND_AWAY_FROM_ZERO,
        'R_TOWARD_ZERO'     => _NUM_ROUND_TOWARD_ZERO,
        'R_TO_POSITIVE_INF' => _NUM_ROUND_TO_POSITIVE_INF,
        'R_TO_NEGATIVE_INF' => _NUM_ROUND_TO_NEGATIVE_INF,
        'R_EVEN'            => _NUM_ROUND_EVEN,
        'R_ODD'             => _NUM_ROUND_ODD,
    ];


    // $dumpPath = $ffn->root() . '/var/dump/bc_mathround_2.txt';
    // if (is_file($dumpPath)) unlink($dumpPath);

    foreach ( $values as $array ) {
        $table = [];
        foreach ( $array as $v ) {
            foreach ( $precisions as $precision ) {
                foreach ( $modes[ 'ROUNDING' ] as $n => $f ) {
                    $vString = \Gzhegow\Lib\Lib::debug()->value($v);

                    $nString = ".{$precision}|{$n}";

                    $res = \Gzhegow\Lib\Lib::bcmath()->bcmathround(
                        $v, $precision,
                        $f, $f
                    );

                    $resString = \Gzhegow\Lib\Lib::debug()->value((string) $res);

                    $table[ $nString ][ $vString ] = $resString;
                }
            }
        }

        // $content = \Gzhegow\Lib\Lib::debug()->print_table($table, 1);
        // file_put_contents($dumpPath, $content . "\n" . "\n", FILE_APPEND);

        // dump(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
        echo md5(serialize($table)) . "\n";
    }


    // $dumpPath = $ffn->root() . '/var/dump/bc_moneyround_2.txt';
    // if (is_file($dumpPath)) unlink($dumpPath);

    foreach ( $values as $array ) {
        $table = [];
        foreach ( $array as $v ) {
            foreach ( $precisions as $precision ) {
                foreach ( $modes[ 'ROUNDING' ] as $n => $f ) {
                    $vString = \Gzhegow\Lib\Lib::debug()->value($v);

                    $nString = ".{$precision}|{$n}";

                    $res = \Gzhegow\Lib\Lib::bcmath()->bcmoneyround(
                        $v, $precision,
                        $f, $f
                    );

                    $resString = \Gzhegow\Lib\Lib::debug()->value((string) $res);

                    $table[ $nString ][ $vString ] = $resString;
                }
            }
        }

        // $content = \Gzhegow\Lib\Lib::debug()->print_table($table, 1);
        // file_put_contents($dumpPath, $content . "\n" . "\n", FILE_APPEND);

        // dump(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
        echo md5(serialize($table)) . "\n";
    }
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ BcmathModule ]"

"bcabs" | "0" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcabs" | "2" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcabs" | "-1" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }

"bcceil" | "0" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcceil" | "2" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcceil" | "-1" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }

"bcfloor" | "0" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcfloor" | "1" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcfloor" | "-2" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }

"bcmod" | "1" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcmod" | "-1" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcmod" | "1" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcmod" | "-1" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }

"bcfmod" | "1.75" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcfmod" | "-1.75" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcfmod" | "1.25" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcfmod" | "-1.25" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }

"bcgcd" | "4" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bcgcd" | "1" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }

"bclcm" | "24" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bclcm" | "40" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }
"bclcm" | "40" | { object(stringable) # Gzhegow\Lib\Modules\Bcmath\Bcnumber }

2a521995645a33b211fe8d256de4fc1e
3fbd1316b366e26ca5f2a655052a772a
74cdbaa3f5de6194337fb075c5eae9ff
099579a899298f5e03f36377c9412a30
5b9a198f45fc24ba897b0627a38397fe
3ad9e060542787e4b0eac224a6044986
');
$test->run();



// >>> TEST
// > тесты CmpModule
$fn = function () use ($ffn) {
    $ffn->print('[ CmpModule ]');
    echo "\n";

    $object = new \StdClass();

    $resourceOpenedStdout = \Gzhegow\Lib\Lib::cli()->stdout();
    $resourceOpenedStderr = \Gzhegow\Lib\Lib::cli()->stderr();
    $resourceClosed = fopen('php://memory', 'wb');
    fclose($resourceClosed);

    $valuesXX = [
        0  => [
            NAN,
            null,
            new \Gzhegow\Lib\Modules\Php\Nil(),
        ],
        1  => [
            ((string) NAN),
            'NULL',
            ((string) (new \Gzhegow\Lib\Modules\Php\Nil())),
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
            -PHP_FLOAT_MAX,
            //
            // > практическая польза нулевая, но для проверки дополнительный вызов и куча работы со строками
            // PHP_FLOAT_MIN,
            // -PHP_FLOAT_MIN,
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
            ((string) PHP_INT_MAX), // > int
            ((string) PHP_INT_MIN), // > int
            ((string) (PHP_INT_MAX + 1)), // > float
            ((string) (PHP_INT_MIN - 1)), // > float
        ],
        12 => [
            ((string) PHP_FLOAT_MAX),
            ((string) (-PHP_FLOAT_MAX)),
            //
            // > практическая польза нулевая, но для проверки дополнительный вызов и куча работы со строками
            // ((string) PHP_FLOAT_MIN),
            // ((string) (-PHP_FLOAT_MIN)),
        ],
        13 => [
            ((string) INF),
            ((string) -INF),
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


    $valuesYY = $valuesXX;

    // > commented, should be the same object
    // $valuesYY[ 17 ][ 0 ] = clone $valuesYY[ 17 ][ 0 ];
    //
    $valuesYY[ 17 ][ 1 ] = clone $valuesYY[ 17 ][ 1 ];
    $valuesYY[ 18 ][ 0 ] = clone $valuesYY[ 18 ][ 0 ];
    $valuesYY[ 18 ][ 1 ] = clone $valuesYY[ 18 ][ 1 ];
    $valuesYY[ 18 ][ 2 ] = clone $valuesYY[ 18 ][ 2 ];
    $valuesYY[ 18 ][ 3 ] = clone $valuesYY[ 18 ][ 3 ];
    $valuesYY[ 19 ][ 0 ] = clone $valuesYY[ 19 ][ 0 ];
    $valuesYY[ 19 ][ 1 ] = clone $valuesYY[ 19 ][ 1 ];
    $valuesYY[ 19 ][ 2 ] = clone $valuesYY[ 19 ][ 2 ];
    $valuesYY[ 19 ][ 3 ] = clone $valuesYY[ 19 ][ 3 ];

    $valuesY = array_merge(...$valuesYY);

    $theCmp = \Gzhegow\Lib\Lib::cmp();
    $theDebug = \Gzhegow\Lib\Lib::debug();

    $fnCmpName = null;
    $fnCmpSizeName = null;

    $fnCmp = $theCmp->fnCompareValues(
        _CMP_MODE_TYPE_TYPECAST_OR_CONTINUE | _CMP_MODE_DATE_VS_SEC,
        _CMP_RESULT_NAN_RETURN,
        [ &$fnCmpName ]
    );
    $fnCmpSize = $theCmp->fnCompareSizes(
        _CMP_MODE_TYPE_TYPECAST_OR_CONTINUE | _CMP_MODE_DATE_VS_SEC,
        _CMP_RESULT_NAN_RETURN,
        [ &$fnCmpSizeName ]
    );


    // $dumpPath = $ffn->root() . '/var/dump/cmp_fn_compare_2.txt';
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

8fb8614dff541044ba946a4163a2e607
8fb8614dff541044ba946a4163a2e607

d827876272749d840cc3d9f1039376ca
527257dcc105a7e6f440e53234a3a8c2

b8f55599f99d468259a5b42a4f06e3cf
bee34c6dc82128567cb48d42130a2ab5

ae89a652942f04701f6b8ee224d8536a
24103e0a5985953a83e932e8638e220c

79a6e40f32634b7ca333eae8c1474368
79d78c1d9c89bae865f1f261e4e3b27b

37f9ec7049b61cb5658c1ed9bf85016e
8d3f5fd785c7ae4ea8eda649e2e8f6ba

f685db70cfdafd6c189e4dc5c9625b25
cf9d1a8041ae85e0b171f978f4eec426

4ac6e9f08bc3d58a95df48b894d6586a
5f33e1430202634f76f82b69f27678e4

e7730b1480752e67c3ea56315777b3a6
c0cddeffa5d533a217a9d672d3bf304c

5e592b2dc710665901c138fab618bf40
7977561d2cbbde4003338e894dca0211

1671741996021a4fb708c74b574e0d5b
2bdf896eabc81db36c4b114eb311b066

7c759f1ebfa86d5f07dcedb5e1889198
c8245d3f5f85c5ba98b6743e181f1122

6a40b69e48bb9e408392e526ed85d3cc
ca68cae9c52866ee00fa192fdecced1d

81d3c9fa3b5b062a5c6ac6b84de62950
32cd397000640521e4d76249f32a7cf3

d6ecb6a5a28210a689446791d099898f
f32620cf3eb0ef614c0b50c19c072cb8

064e364eff28a530b65c90420a66a7e1
12143f37cfeab922f3e022e9f291fadb

521de67cba5795f6e9b4470ba1911824
1aa12fa0b41b27e82019866138dabd00

0c0e2975240da648f7359cadd162e823
1c26fc92987fbacf13db735c9a1f6657

b682bdec6ff275892eddac3beadd484a
431dba0d82d272badbfeaaa55a16e1a4

483e929cad8a50735cd35ecb14e458c3
9d75f52bea268860e1681d086aa1347b
');
$test->expectStdoutIf(PHP_VERSION_ID < 80200, '
"[ CmpModule ]"

867bed6e3303e24dbccb6ae1b6258af8
867bed6e3303e24dbccb6ae1b6258af8

69119b8480591f23003b7874ef18b27e
d21ffa4bad46198ca8d8b6fb20e53bab

f406f7301eccbe2369c4f92d978f9bc7
adef371ec1265997c1827318a608da70

80bf152dd6110f6db2eddfa9a9820e0a
296dffb822ba2fac9fb187c15dbc58c4

524446451ea078538c22d16ffc7407ca
407bae8306a462f7dd31df7665eb972d

b193d867e2e9b8772a023957abcd5ab3
782616ba9f2625836305d5edb27af988

f00da4909ce845822117c16f3c6c88b0
8098cc141981c63dd8355a293753830f

4a5ef57f9986cbcdec1d494f3050be01
894c42a801d4e56097631551604dc2a8

48466412af0bd08583e780b384d65a66
a35364fe29a89dda89115f9fb3384099

890e8c5e276e9f2eec402b3dfed584b1
542043204ae96d41c33e8c033cae7efa

40c859a0d73f4011c49e1882509756cc
e284771d933a66eade1276eba33e9084

2b7ea99ae7905e70effe8bf1913766a4
51a846b570526767249465433872c2d4

56a4ff1e86d1be6d217714b2dfa7b640
0e5d455db540c2bbb141870838dc5ff9

0f86bcbd6f04d347357baf0ad5da0cc6
9ee81ed5ebe9742c6f547b270a8d57b4

34c94919a67eadd6474e8abd0263615f
79a0cfcdcf951eb8224909acdb023fa5

07d82cb5793b234948704d7c20cb284d
ae8e6667b484151355fa92c069d5793d

28af9f04c7a2fe85694346d7c3fb656c
0be8825df3990cb351c9ee783a973fe7

84fb8d665403f3f9888af1ae21b6f312
9cd36898bc84a243e8e2ce31438cd56e

098809dc22f412bb928ba7a09bc440f3
4a670c00a1630c13602f50cf223ad984

eb95474cce527e8a7277b32231834b87
061f86a33a3b0809ef62560789f80116
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

    echo "\n";
    echo "\n";


    $src = "hello";
    $enc = \Gzhegow\Lib\Lib::crypt()->base64_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base64_decode($enc);
    $ffn->print($src, $enc, $dec);

    $src = "\x00\x00\x01\x00\xFF";
    $enc = \Gzhegow\Lib\Lib::crypt()->base64_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base64_decode($enc);
    $ffn->print($src, $enc, $dec);

    $src = '你好';
    $enc = \Gzhegow\Lib\Lib::crypt()->base64_encode($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base64_decode($enc);
    $ffn->print($src, $enc, $dec);

    echo "\n";


    $src = "hello";
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_it($src);
    $enc = implode('', iterator_to_array($gen));
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_it($enc);
    $dec = implode('', iterator_to_array($gen));
    $ffn->print($src, $enc, $dec);

    $src = "\x00\x00\x01\x00\xFF";
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_it($src);
    $enc = implode('', iterator_to_array($gen));
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_it($enc);
    $dec = implode('', iterator_to_array($gen));
    $ffn->print($src, $enc, $dec);

    $src = "你好";
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_it($src);
    $enc = implode('', iterator_to_array($gen));
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_it($enc);
    $dec = implode('', iterator_to_array($gen));
    $ffn->print($src, $enc, $dec);

    echo "\n";
    echo "\n";


    $src = "hello";
    $enc = \Gzhegow\Lib\Lib::crypt()->base64_encode_urlsafe($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base64_decode_urlsafe($enc);
    $ffn->print($src, $enc, $dec);

    $src = "\x00\x00\x01\x00\xFF";
    $enc = \Gzhegow\Lib\Lib::crypt()->base64_encode_urlsafe($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base64_decode_urlsafe($enc);
    $ffn->print($src, $enc, $dec);

    $src = '你好';
    $enc = \Gzhegow\Lib\Lib::crypt()->base64_encode_urlsafe($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base64_decode_urlsafe($enc);
    $ffn->print($src, $enc, $dec);

    $src = 'this+is/a?string=with&special=characters==';
    $enc = \Gzhegow\Lib\Lib::crypt()->base64_encode_urlsafe($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base64_decode_urlsafe($enc);
    $ffn->print($src, $enc, $dec);

    $src = "\xfa\xfb\xfc\xfd\xfe\xff";
    $enc = \Gzhegow\Lib\Lib::crypt()->base64_encode_urlsafe($src);
    $dec = \Gzhegow\Lib\Lib::crypt()->base64_decode_urlsafe($enc);
    $ffn->print($src, $enc, $dec);

    echo "\n";


    $src = "hello";
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_urlsafe_it($src);
    $enc = implode('', iterator_to_array($gen));
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_urlsafe_it($enc);
    $dec = implode('', iterator_to_array($gen));
    $ffn->print($src, $enc, $dec);

    $src = "\x00\x00\x01\x00\xFF";
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_urlsafe_it($src);
    $enc = implode('', iterator_to_array($gen));
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_urlsafe_it($enc);
    $dec = implode('', iterator_to_array($gen));
    $ffn->print($src, $enc, $dec);

    $src = "你好";
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_urlsafe_it($src);
    $enc = implode('', iterator_to_array($gen));
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_urlsafe_it($enc);
    $dec = implode('', iterator_to_array($gen));
    $ffn->print($src, $enc, $dec);

    $src = "this+is/a?string=with&special=characters==";
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_urlsafe_it($src);
    $enc = implode('', iterator_to_array($gen));
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_urlsafe_it($enc);
    $dec = implode('', iterator_to_array($gen));
    $ffn->print($src, $enc, $dec);

    $src = "\xfa\xfb\xfc\xfd\xfe\xff";
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_encode_urlsafe_it($src);
    $enc = implode('', iterator_to_array($gen));
    $gen = \Gzhegow\Lib\Lib::crypt()->base64_decode_urlsafe_it($enc);
    $dec = implode('', iterator_to_array($gen));
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

0 | "[ CATCH ] The `decInteger` should be GT 0 due to `oneBasedTo` is set to TRUE"
10 | "J" | "10"
26 | "Z" | "26"
27 | "AA" | "27"

"2147483647" | "2147483647" | "9223372036854775807"
"2147483647" | "zik0zj" | "2147483647"
"9223372036854775807" | "1y2p0ij32e8e7" | "9223372036854775807"

"1" | "3" | "7" | "F" | "V" | "/"

[ "你" ]
[ "11100100", "10111101", "10100000" ]
[ "你" ]

[ "你好" ]
[ "11100100", "10111101", "10100000", "11100101", "10100101", "10111101" ]
[ "你", "好" ]


5678 | "1011000101110" | "uYB" | "0001011000101110" | 5678

[ "hello" ]
[ "01101000", "01100101", "01101100", "01101100", "01101111" ]
"aGVsbG8"
[ "01101000", "01100101", "01101100", "01101100", "01101111" ]
"hello"


"hello" | "Cn8eVZg" | "hello"
"b`\x00\x00\x01\x00ÿ`" | "11LZL" | "b`\x00\x00\x01\x00ÿ`"
"你好" | "2xuZUfBKa" | "你好"


"hello" | "7tQLFHz" | "hello"
"b`\x00\x00\x01\x00ÿ`" | "00H79" | "b`\x00\x00\x01\x00ÿ`"
"你好" | "19PqtKE1t" | "你好"


"hello" | "aGVsbG8=" | "hello"
"b`\x00\x00\x01\x00ÿ`" | "AAABAP8=" | "b`\x00\x00\x01\x00ÿ`"
"你好" | "5L2g5aW9" | "你好"

"hello" | "aGVsbG8=" | "hello"
"b`\x00\x00\x01\x00ÿ`" | "AAABAP8=" | "b`\x00\x00\x01\x00ÿ`"
"你好" | "5L2g5aW9" | "你好"


"hello" | "aGVsbG8=" | "hello"
"b`\x00\x00\x01\x00ÿ`" | "AAABAP8=" | "b`\x00\x00\x01\x00ÿ`"
"你好" | "5L2g5aW9" | "你好"
"this+is/a?string=with&special=characters==" | "dGhpcytpcy9hP3N0cmluZz13aXRoJnNwZWNpYWw9Y2hhcmFjdGVycz09" | "this+is/a?string=with&special=characters=="
"b`úûüýþÿ`" | "-vv8_f7_" | "b`úûüýþÿ`"

"hello" | "aGVsbG8=" | "hello"
"b`\x00\x00\x01\x00ÿ`" | "AAABAP8=" | "b`\x00\x00\x01\x00ÿ`"
"你好" | "5L2g5aW9" | "你好"
"this+is/a?string=with&special=characters==" | "dGhpcytpcy9hP3N0cmluZz13aXRoJnNwZWNpYWw9Y2hhcmFjdGVycz09" | "this+is/a?string=with&special=characters=="
"b`úûüýþÿ`" | "-vv8_f7_" | "b`úûüýþÿ`"
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
TRUE | { object(serializable) # DateTime # "1970-01-01T00:02:03.456000+00:00" }
TRUE | { object(serializable) # DateTime # "1970-01-01T02:02:03.456000+02:00" }
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
TRUE | { object # DateTime # "1970-01-01T00:02:03.456000+00:00" }
TRUE | { object # DateTime # "1970-01-01T02:02:03.456000+02:00" }
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
    echo \Gzhegow\Lib\Lib::debug()->value(\Gzhegow\Lib\Lib::php()->output()) . "\n";

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
    //     ->dumper(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::DUMPER_ECHO)
    //     ->dump($varToDump)
    // ;
    //
    // \Gzhegow\Lib\Lib::debug()
    //     ->cloneDumper()
    //     ->printer(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::PRINTER_VAR_DUMP)
    //     ->dumper(\Gzhegow\Lib\Modules\Debug\Dumper\DefaultDumper::DUMPER_ECHO_HTML)
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
    echo "\n";


    [ $csv, $bytes ] = \Gzhegow\Lib\Lib::format()->csv()->csv_encode_rows([ [ 'col1', 'col2' ], [ 'val1', 'val2' ] ]);
    $ffn->print($csv);
    $ffn->print($bytes);

    echo "\n";

    [ $csv, $bytes ] = \Gzhegow\Lib\Lib::format()->csv()->csv_encode_row([ 'col1', 'col2' ]);
    $ffn->print($csv);
    $ffn->print($bytes);

    echo "\n";
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
        \Gzhegow\Lib\Lib::format()->json()->json_decode(null, true);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    $result = \Gzhegow\Lib\Lib::format()->json()->json_decode(
        null, true,
        null, null,
        \Gzhegow\Lib\Modules\Php\Result\Result::asValue([ null ])
    );
    $ffn->print($result);

    echo "\n";


    try {
        \Gzhegow\Lib\Lib::format()->json()->jsonc_decode(null, true);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    $result = \Gzhegow\Lib\Lib::format()->json()->jsonc_decode(
        null, true,
        null, null,
        \Gzhegow\Lib\Modules\Php\Result\Result::asValue([ null ])
    );
    $ffn->print($result);

    echo "\n";


    $result = \Gzhegow\Lib\Lib::format()->json()->json_decode($json1, true);
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::format()->json()->json_decode($json2, true);
    $ffn->print($result);

    echo "\n";


    $result = \Gzhegow\Lib\Lib::format()->json()->jsonc_decode($json1, true);
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::format()->json()->jsonc_decode($json2, true);
    $ffn->print($result);

    echo "\n";


    try {
        \Gzhegow\Lib\Lib::format()->json()->json_decode($jsonWithComment1, true);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    try {
        \Gzhegow\Lib\Lib::format()->json()->json_decode($jsonWithComment2, true);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    $result = \Gzhegow\Lib\Lib::format()->json()->jsonc_decode($jsonWithComment1, true);
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::format()->json()->jsonc_decode($jsonWithComment2, true);
    $ffn->print($result);

    echo "\n";


    try {
        \Gzhegow\Lib\Lib::format()->json()->json_encode(null, false);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    $result = \Gzhegow\Lib\Lib::format()->json()->json_encode(null, false, null, null, \Gzhegow\Lib\Modules\Php\Result\Result::asValue([ null ]));
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::format()->json()->json_encode(null, true);
    $ffn->print($result);

    echo "\n";


    try {
        \Gzhegow\Lib\Lib::format()->json()->json_encode($value = NAN);
    }
    catch ( \Throwable $e ) {
        $ffn->print('[ CATCH ] ' . $e->getMessage());
    }

    $result = \Gzhegow\Lib\Lib::format()->json()->json_encode(
        $value = NAN, null,
        null, null,
        \Gzhegow\Lib\Modules\Php\Result\Result::asValue([ "NAN" ])
    );
    $ffn->print($result);

    echo "\n";


    $result = \Gzhegow\Lib\Lib::format()->json()->json_encode("привет");
    $ffn->print($result);

    $result = \Gzhegow\Lib\Lib::format()->json()->json_print("привет");
    $ffn->print($result);

    echo "\n";
    echo "\n";



    $xml = <<<XML
<example>
  <a>Apple</a>
  <b>Banana</b>
  <c>Cherry</c>
</example>
XML;

    $sxe = \Gzhegow\Lib\Lib::format()->xml()->parse_xml_sxe($xml);
    $ffn->print($sxe);


    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<example>
  <a>Apple</a>
  <b>Banana</b>
  <c>Cherry</c>
</example>
XML;

    $ddoc = \Gzhegow\Lib\Lib::format()->xml()->parse_xml_dom_document($xml);
    $ffn->print($ddoc);


    $xml = <<<XML
<example xmlns:foo="my.foo.urn">
  <foo:a>Apple</foo:a>
  <foo:b>Banana</foo:b>
  <c>Cherry</c>
</example>
XML;

    $ddoc = \Gzhegow\Lib\Lib::format()->xml()->parse_xml_dom_document($xml);
    $ffn->print($ddoc);


    $xmlInvalid = <<<XML
<note>
  <to>Tove</to>
  <from>Jani</from
  <heading>Reminder</heading>
  <body>Don't forget me this weekend!</body>
</note>
XML;

    $sxe = \Gzhegow\Lib\Lib::format()->xml()->parse_xml_sxe($xmlInvalid, $ret = \Gzhegow\Lib\Modules\Php\Result\Result::asValue());
    $ffn->print($sxe);

    $errorList = $ret->getErrorList();
    $ffn->print_array_multiline($errorList, 2);
};
$test = $ffn->test($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 70400, '
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


"[ CATCH ] The `json` should be a non-empty string"
NULL

"[ CATCH ] The `jsonc` should be a non-empty string"
NULL

[ "hello" => "world" ]
[ "hello" => "world" ]

[ "hello" => "world" ]
[ "hello" => "world" ]

"[ CATCH ] Unable to `json_decode` due to invalid JSON"
"[ CATCH ] Unable to `json_decode` due to invalid JSON"
[ 1, 2, 3 ]
[ "hello" => "world", "foo1" => "bar1", "foo2" => "bar2", "foo3" => "bar3" ]

"[ CATCH ] The NULL values cannot be encoded to JSON when `allowsNull` is set to FALSE"
NULL
"NULL"

"[ CATCH ] The values of types [ NAN ][ resource ] cannot be encoded to JSON"
"NAN"

"\"\u043f\u0440\u0438\u0432\u0435\u0442\""
"\"привет\""


{ object(countable(3) iterable stringable) # SimpleXMLElement }
{ object # DOMDocument }
{ object # DOMDocument }
NULL
###
[
  [
    "[ ERROR ] expected \'>\' at line 4",
    "  <heading>Reminder</heading>",
    "{ object # LibXMLError }"
  ]
]
###
');
$test->expectStdoutIf(PHP_VERSION_ID < 70400, '
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


"[ CATCH ] The `json` should be a non-empty string"
NULL

"[ CATCH ] The `jsonc` should be a non-empty string"
NULL

[ "hello" => "world" ]
[ "hello" => "world" ]

[ "hello" => "world" ]
[ "hello" => "world" ]

"[ CATCH ] Unable to `json_decode` due to invalid JSON"
"[ CATCH ] Unable to `json_decode` due to invalid JSON"
[ 1, 2, 3 ]
[ "hello" => "world", "foo1" => "bar1", "foo2" => "bar2", "foo3" => "bar3" ]

"[ CATCH ] The NULL values cannot be encoded to JSON when `allowsNull` is set to FALSE"
NULL
"NULL"

"[ CATCH ] The values of types [ NAN ][ resource ] cannot be encoded to JSON"
"NAN"

"\"\u043f\u0440\u0438\u0432\u0435\u0442\""
"\"привет\""


{ object(iterable stringable) # SimpleXMLElement }
{ object # DOMDocument }
{ object # DOMDocument }
NULL
###
[
  [
    "[ ERROR ] expected \'>\' at line 4",
    "  <heading>Reminder</heading>",
    "{ object # LibXMLError }"
  ]
]
###
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
// > тесты HttpModule
$fn = function () use ($ffn) {
    $ffn->print('[ HttpModule ]');
    echo "\n";


    $theHttpCookies = \Gzhegow\Lib\Lib::httpCookies();
    $theHttpCookies->set('hello', 'value', 3600, '/', null);
    $ffn->print($theHttpCookies);

    echo "\n";


    $theHttpSession = \Gzhegow\Lib\Lib::httpSession();
    // $theHttpSession->disableNativeSession();
    // $theHttpSession->useNativeSession();
    // $theHttpSession->withSymfonySession(
    //     new \Symfony\Component\HttpFoundation\Session\Session(
    //         new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage([],
    //             new \Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler(
    //                 __DIR__ . '/var/session',
    //             )
    //         ),
    //     )
    // );
    $theHttpSession->set('hello', 'world');
    $ffn->print($theHttpSession, $_SESSION ?? []);

    $theHttpSession->unset('hello');
    $ffn->print($theHttpSession, $_SESSION ?? []);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ HttpModule ]"

{ object # Gzhegow\Lib\Modules\Http\Cookies\DefaultCookies }

{ object # Gzhegow\Lib\Modules\Http\Session\DefaultSession } | [ "hello" => "world" ]
{ object # Gzhegow\Lib\Modules\Http\Session\DefaultSession } | []
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

    foreach ( $ipV4List as $ip ) {
        \Gzhegow\Lib\Lib::net()->type_address_ip_v4($addressIpV4, $ip);

        foreach ( $subnetV4List as $subnet ) {
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

    foreach ( $ipV6List as $ip ) {
        \Gzhegow\Lib\Lib::net()->type_address_ip_v6($addressIpV6, $ip);

        foreach ( $subnetV6List as $subnet ) {
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
// > тесты NumModule
$fn = function () use ($ffn) {
    $ffn->print('[ NumModule ]');
    echo "\n";

    $values = [];
    $values[] = [
        -2.4,
        -2.04,
        -2.004,
        -1.4,
        -1.04,
        -1.004,
        -0.4,
        -0.04,
        -0.004,
        0,
        0.004,
        0.04,
        0.4,
        1.004,
        1.04,
        1.4,
        2.004,
        2.04,
        2.4,
    ];
    $values[] = [
        -2.5,
        -2.05,
        -2.005,
        -1.5,
        -1.05,
        -1.005,
        -0.5,
        -0.05,
        -0.005,
        0,
        0.005,
        0.05,
        0.5,
        1.005,
        1.05,
        1.5,
        2.005,
        2.05,
        2.5,
    ];
    $values[] = [
        -2.6,
        -2.06,
        -2.006,
        -1.6,
        -1.06,
        -1.006,
        -0.6,
        -0.06,
        -0.006,
        0,
        0.006,
        0.06,
        0.6,
        1.006,
        1.06,
        1.6,
        2.006,
        2.06,
        2.6,
    ];

    $precisions = [ 0, 2 ];

    $modes = [];
    $modes[ 'ROUNDING' ] = [
        'R_AWAY_FROM_ZERO'  => _NUM_ROUND_AWAY_FROM_ZERO,
        'R_TOWARD_ZERO'     => _NUM_ROUND_TOWARD_ZERO,
        'R_TO_POSITIVE_INF' => _NUM_ROUND_TO_POSITIVE_INF,
        'R_TO_NEGATIVE_INF' => _NUM_ROUND_TO_NEGATIVE_INF,
        'R_EVEN'            => _NUM_ROUND_EVEN,
        'R_ODD'             => _NUM_ROUND_ODD,
    ];


    // $dumpPath = $ffn->root() . '/var/dump/num_mathround_2.txt';
    // if (is_file($dumpPath)) unlink($dumpPath);

    foreach ( $values as $array ) {
        $table = [];
        foreach ( $array as $v ) {
            foreach ( $precisions as $precision ) {
                foreach ( $modes[ 'ROUNDING' ] as $n => $f ) {
                    $vString = \Gzhegow\Lib\Lib::debug()->value($v);

                    $nString = ".{$precision}|{$n}";

                    $res = \Gzhegow\Lib\Lib::num()->mathround(
                        $v, $precision,
                        $f, $f
                    );

                    $resString = \Gzhegow\Lib\Lib::debug()->value($res);

                    $table[ $nString ][ $vString ] = $resString;
                }
            }
        }

        // $content = \Gzhegow\Lib\Lib::debug()->print_table($table, 1);
        // file_put_contents($dumpPath, $content . "\n" . "\n", FILE_APPEND);

        // dump(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
        echo md5(serialize($table)) . "\n";
    }


    // $dumpPath = $ffn->root() . '/var/dump/num_moneyround_2.txt';
    // if (is_file($dumpPath)) unlink($dumpPath);

    foreach ( $values as $array ) {
        $table = [];
        foreach ( $array as $v ) {
            foreach ( $precisions as $precision ) {
                foreach ( $modes[ 'ROUNDING' ] as $n => $f ) {
                    $vString = \Gzhegow\Lib\Lib::debug()->value($v);

                    $nString = ".{$precision}|{$n}";

                    $res = \Gzhegow\Lib\Lib::num()->moneyround(
                        $v, $precision,
                        $f, $f
                    );

                    $resString = \Gzhegow\Lib\Lib::debug()->value($res);

                    $table[ $nString ][ $vString ] = $resString;
                }
            }
        }

        // $content = \Gzhegow\Lib\Lib::debug()->print_table($table, 1);
        // file_put_contents($dumpPath, $content . "\n" . "\n", FILE_APPEND);

        // dump(\Gzhegow\Lib\Lib::debug()->print_table($table, 1));
        echo md5(serialize($table)) . "\n";
    }
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ NumModule ]"

99dc2eb40c3227446051e89753ba0934
badc8db5c75f326de20847505c20138d
e75e199fd051315fc60b543f9791aaba
004baa64cef208c138d4ca4bc8e01dd9
eab6474281b856dd3aa86a12d87641a4
16baaadc1d5f2445bfb4279c4a72630c
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


    $status = \Gzhegow\Lib\Lib::url()->type_url($result, $src = 'https://google.com/hello/world');
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_url($result, $src = ':hello/world');
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_url($result, $src = '/');
    $ffn->print($src, $status, $result);

    echo "\n";


    $status = \Gzhegow\Lib\Lib::url()->type_host($result, $src = 'https://google.com/hello/world');
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_host($result, $src = ':hello/world');
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_host($result, $src = '/');
    $ffn->print($src, $status, $result);

    echo "\n";


    $status = \Gzhegow\Lib\Lib::url()->type_link($result, $src = 'https://google.com/hello/world');
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_link($result, $src = ':hello/world');
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_link($result, $src = '/');
    $ffn->print($src, $status, $result);

    echo "\n";


    $status = \Gzhegow\Lib\Lib::url()->type_url($result, $src = 'https://привет.рф/hello/текст');
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_host($result, $src = 'https://привет.рф/hello/текст');
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_link($result, $src = 'https://привет.рф/hello/текст');
    $ffn->print($src, $status, $result);

    echo "\n";


    $status = \Gzhegow\Lib\Lib::url()->type_url($result, $src = 'https://привет.рф/hello/текст', null, null, 1, 1);
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_host($result, $src = 'https://привет.рф/hello/текст', 1);
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_link($result, $src = 'https://привет.рф/hello/текст', null, null, 1);
    $ffn->print($src, $status, $result);

    echo "\n";


    $status = \Gzhegow\Lib\Lib::url()->type_url($result, $src = 'https://привет.рф/hello/текст', null, null, 2, 2);
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_host($result, $src = 'https://привет.рф/hello/текст', 2);
    $ffn->print($src, $status, $result);

    $status = \Gzhegow\Lib\Lib::url()->type_link($result, $src = 'https://привет.рф/hello/текст', null, null, 2);
    $ffn->print($src, $status, $result);

    echo "\n";
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ UrlModule ]"

"https://google.com/hello/world" | TRUE | "https://google.com/hello/world"
":hello/world" | FALSE | NULL
"/" | FALSE | NULL

"https://google.com/hello/world" | TRUE | "https://google.com"
":hello/world" | FALSE | NULL
"/" | FALSE | NULL

"https://google.com/hello/world" | TRUE | "/hello/world"
":hello/world" | TRUE | ":hello/world"
"/" | TRUE | "/"

"https://привет.рф/hello/текст" | TRUE | "https://привет.рф/hello/текст"
"https://привет.рф/hello/текст" | TRUE | "https://привет.рф"
"https://привет.рф/hello/текст" | TRUE | "/hello/текст"

"https://привет.рф/hello/текст" | FALSE | NULL
"https://привет.рф/hello/текст" | FALSE | NULL
"https://привет.рф/hello/текст" | FALSE | NULL

"https://привет.рф/hello/текст" | TRUE | "https://xn--b1agh1afp.xn--p1ai/hello/%D1%82%D0%B5%D0%BA%D1%81%D1%82"
"https://привет.рф/hello/текст" | TRUE | "https://xn--b1agh1afp.xn--p1ai"
"https://привет.рф/hello/текст" | TRUE | "/hello/%D1%82%D0%B5%D0%BA%D1%81%D1%82"
');
$test->run();
