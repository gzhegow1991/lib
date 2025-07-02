<?php

namespace Gzhegow\Lib\Modules\Func\Pipe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\Runtime\PipeException;


class Pipe
{
    /**
     * @var static
     */
    protected $parent;

    /**
     * @var array[]
     */
    protected $queue = [];
    /**
     * @var int
     */
    protected $queueStep = 0;
    /**
     * @var int
     */
    protected $queueCount = 0;

    /**
     * @var array{ 0?: mixed }
     */
    protected $context = [];
    /**
     * @var array{ 0?: \Throwable }
     */
    protected $exception = [];

    /**
     * @var array{ 0?: mixed }
     */
    protected $input = [];

    /**
     * @var callable
     */
    protected $fnCallUserFuncArray;
    /**
     * @var callable
     */
    protected $fnCallUserFuncArgs;


    public function __invoke($value, array $context = [], ...$args)
    {
        $result = $this->run($value, $context, $args);

        return $result;
    }


    /**
     * @param callable $fnCallUserFuncArray
     */
    public function setFnCallUserFuncArray($fnCallUserFuncArray)
    {
        $this->fnCallUserFuncArray = $fnCallUserFuncArray;

        return $this;
    }

    /**
     * @param callable $fnCallUserFuncArgs
     */
    public function setFnCallUserFuncArgs($fnCallUserFuncArgs)
    {
        $this->fnCallUserFuncArgs = $fnCallUserFuncArgs;

        return $this;
    }


    /**
     * @param callable $fn
     *
     * @return static
     */
    public function tap($fn, array $args = [])
    {
        $this->queue[] = [
            'type' => __FUNCTION__,
            'fn'   => $fn,
            'args' => $args,
        ];

        $this->queueCount++;

        return $this;
    }

    /**
     * @param callable $fn
     *
     * @return static
     */
    public function map($fn, array $args = [])
    {
        $this->queue[] = [
            'type' => __FUNCTION__,
            'fn'   => $fn,
            'args' => $args,
        ];

        $this->queueCount++;

        return $this;
    }


    /**
     * @param callable $fn
     *
     * @return static
     */
    public function filter($fn, array $args = [])
    {
        $this->queue[] = [
            'type' => __FUNCTION__,
            'fn'   => $fn,
            'args' => $args,
        ];

        $this->queueCount++;

        return $this;
    }


    /**
     * @param callable $fn
     *
     * @return static
     */
    public function catch($fn, array $args = [])
    {
        $this->queue[] = [
            'type' => __FUNCTION__,
            'fn'   => $fn,
            'args' => $args,
        ];

        $this->queueCount++;

        return $this;
    }


    /**
     * @param callable $fn
     *
     * @return static
     */
    public function middleware($fn, array $args = [])
    {
        $child = new static();
        $child->parent = $this;

        $this->queue[] = [
            'type'  => __FUNCTION__,
            'fn'    => $fn,
            'args'  => $args,
            'child' => $child,
        ];

        $this->queueCount++;

        return $child;
    }

    /**
     * @return static
     */
    public function endMiddleware()
    {
        return $this->parent;
    }


    /**
     * @return static
     */
    public function context(?array &$context = null)
    {
        if (null === $context) {
            $this->context = [];

        } else {
            $this->context = [ &$context ];
        }

        return $this;
    }

    /**
     * @return static
     */
    public function throwable(\Throwable $e)
    {
        $this->exception = [ $e ];

        return $this;
    }


    public function run($input = null, array $context = [], array $args = [])
    {
        if (isset($context[ 0 ])) {
            $this->context = [ &$context[ 0 ] ];
        }

        $result = $this->doRun($input, $args);

        return $result;
    }

    protected function doRun($input, array $argsRun = [])
    {
        $this->queueStep = 0;

        $this->exception = [];

        $this->input = [ $input ];

        $fnCallUserFuncArray = $this->fnCallUserFuncArray ?? [ $this, 'callUserFuncArray' ];
        $fnCallUserFuncArgs = $this->fnCallUserFuncArgs ?? [ $this, 'callUserFuncArgs' ];

        $argsContext = $this->context;

        for ( $i = $this->queueStep; $i < $this->queueCount; $i++ ) {
            $this->queueStep = $i;

            $step = $this->queue[ $this->queueStep ];

            [
                'type' => $stepType,
                'fn'   => $stepFn,
            ] = $step;

            $argsStep = $step[ 'args' ];

            try {
                if ('tap' === $stepType) {
                    if ([] !== $this->exception) {
                        continue;
                    }

                    $argsInput = [
                        0 => $this->input[ 0 ],
                    ];
                    $argsContext = $this->context;

                    $fnCallUserFuncArray(
                        $stepFn,
                        $fnCallUserFuncArgs(
                            $argsInput,
                            $argsContext,
                            $argsRun,
                            $argsStep
                        )
                    );

                } elseif ('map' === $stepType) {
                    if ([] !== $this->exception) {
                        continue;
                    }

                    $argsInput = [
                        0 => $this->input[ 0 ],
                    ];

                    $result = $fnCallUserFuncArray(
                        $stepFn,
                        $fnCallUserFuncArgs(
                            $argsInput,
                            $argsContext,
                            $argsRun,
                            $argsStep
                        )
                    );

                    $this->input = [ $result ];

                } elseif ('filter' === $stepType) {
                    if ([] !== $this->exception) {
                        continue;
                    }

                    $argsInput = [
                        0 => $this->input[ 0 ],
                    ];

                    $status = $fnCallUserFuncArray(
                        $stepFn,
                        $fnCallUserFuncArgs(
                            $argsInput,
                            $argsContext,
                            $argsRun,
                            $argsStep
                        )
                    );

                    if (! $status) {
                        $this->input = [ null ];
                    }

                } elseif ('middleware' === $stepType) {
                    if ([] !== $this->exception) {
                        continue;
                    }

                    $pipeChild = $this->stepPipeChild($step);

                    $pipeChild->fnCallUserFuncArray = $fnCallUserFuncArray;
                    $pipeChild->fnCallUserFuncArgs = $fnCallUserFuncArgs;
                    $pipeChild->context($this->context[ 0 ]);

                    $fnNext = function (
                        $value, array $args = []
                    ) use (
                        $pipeChild
                    ) {
                        return $pipeChild->doRun(
                            $value,
                            $args
                        );
                    };

                    $argsInput = [
                        0 => $fnNext,
                        1 => $this->input[ 0 ],
                    ];

                    $result = $fnCallUserFuncArray(
                        $stepFn,
                        $fnCallUserFuncArgs(
                            $argsInput,
                            $argsContext,
                            $argsRun,
                            $argsStep
                        )
                    );

                    $this->input = [ $result ];

                } elseif ('catch' === $stepType) {
                    if ([] === $this->exception) {
                        continue;
                    }

                    $argsInput = [
                        0 => $this->exception[ 0 ],
                        1 => $this->input[ 0 ],
                    ];

                    $result = $fnCallUserFuncArray(
                        $stepFn,
                        $fnCallUserFuncArgs(
                            $argsInput,
                            $argsContext,
                            $argsRun,
                            $argsStep
                        )
                    );

                    if ($result instanceof \Throwable) {
                        $this->exception = [ $result ];

                    } else {
                        $this->exception = [];

                        $this->input = [ $result ];
                    }
                }
            }
            catch ( \Throwable $e ) {
                $this->exception = [ $e ];
            }
        }

        if ([] === $this->exception) {
            return $this->input[ 0 ];
        }

        if (null !== $this->parent) {
            $this->parent->exception = $this->exception;

            return null;
        }

        throw new PipeException(
            [ 'Unhandled exception during processing pipeline', $this->exception[ 0 ] ],
            $this->exception[ 0 ]
        );
    }


    /**
     * @return static
     */
    protected function stepPipeChild(array $step)
    {
        return $step[ 'child' ];
    }


    /**
     * @return mixed
     */
    protected function callUserFuncArray(
        $fn,
        array $args = []
    )
    {
        return call_user_func_array($fn, $args);
    }

    protected function callUserFuncArgs(
        array $inputArgs,
        array $contextArgs,
        array ...$argsList
    ) : array
    {
        $args = $inputArgs;

        $args[] = $contextArgs;

        if ([] !== $argsList) {
            $arrayArgs = [];

            foreach ( $argsList as $argsItem ) {
                $arrayArgs += $argsItem;
            }

            $arrayArgs[] = null;
            $arrayArgsKeyLast = array_key_last($arrayArgs);

            $arrayArgs += array_fill(0, $arrayArgsKeyLast, null);

            unset($arrayArgs[ $arrayArgsKeyLast ]);

            ksort($arrayArgs);

            $args[] = $arrayArgs;
        }

        return $args;
    }
}
