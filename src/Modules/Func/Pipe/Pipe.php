<?php

namespace Gzhegow\Lib\Modules\Func\Pipe;

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
     * @var PipeContext
     */
    protected $context;
    /**
     * @var \Throwable
     */
    protected $exception;

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


    public function __invoke($input = null, ?PipeContext $context = null, ...$args)
    {
        $result = $this->invoke($input, $context, $args);

        return $result;
    }

    public function invoke($input = null, ?PipeContext $context = null, array $args = [])
    {
        if (null !== $context) {
            $this->context = $context;
        }

        $result = $this->run($input, $args);

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

        return $this;
    }


    /**
     * @param callable $fn
     *
     * @return static
     */
    public function middleware($fn, array $args = [])
    {
        $pipeChild = new static();
        $pipeChild->parent = $this;

        $this->queue[] = [
            'type'  => __FUNCTION__,
            'fn'    => $fn,
            'args'  => $args,
            //
            'child' => $pipeChild,
        ];

        return $pipeChild;
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
    public function context(PipeContext $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return static
     */
    public function throwable(\Throwable $e)
    {
        $this->exception = $e;

        return $this;
    }


    public function run($input, array $argsRun = [])
    {
        $this->input = [ $input ];

        if ([] !== $this->queue) {
            $hasContext = (null !== $this->context);

            $fnCallUserFuncArray = $this->fnCallUserFuncArray ?? [ $this, 'callUserFuncArray' ];
            $fnCallUserFuncArgs = $this->fnCallUserFuncArgs ?? [ $this, 'callUserFuncArgs' ];

            $pipeContext = null;
            $argsContext = [];
            if ($hasContext) {
                $pipeContext = $this->context;
                $argsContext = [ $pipeContext ];
            }

            foreach ( $this->queue as $step ) {
                [
                    'type' => $stepType,
                    'fn'   => $stepFn,
                    'args' => $stepArgs,
                ] = $step;

                try {
                    if ('tap' === $stepType) {
                        if (null !== $this->exception) {
                            continue;
                        }

                        $argsInput = [
                            0 => $this->input[ 0 ],
                        ];

                        $fnCallUserFuncArray(
                            $stepFn,
                            $fnCallUserFuncArgs(
                                $argsInput,
                                $argsContext,
                                $argsRun,
                                //
                                $stepArgs
                            )
                        );

                    } elseif ('map' === $stepType) {
                        if (null !== $this->exception) {
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
                                //
                                $stepArgs
                            )
                        );

                        $this->input = [ $result ];

                    } elseif ('filter' === $stepType) {
                        if (null !== $this->exception) {
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
                                //
                                $stepArgs
                            )
                        );

                        if (! $status) {
                            $this->input = [ null ];
                        }

                    } elseif ('middleware' === $stepType) {
                        if (null !== $this->exception) {
                            continue;
                        }

                        $pipeChild = $this->stepPipeChild($step);

                        if ($hasContext) {
                            $pipeChildContext = $pipeContext;
                            $pipeChildContext->setPipe($pipeChild);

                            $pipeChild->context = $pipeChildContext;
                        }

                        $pipeChild->fnCallUserFuncArray = $fnCallUserFuncArray;
                        $pipeChild->fnCallUserFuncArgs = $fnCallUserFuncArgs;

                        $argsInput = [
                            0 => [ $pipeChild, 'run' ],
                            1 => $this->input[ 0 ],
                        ];

                        $result = $fnCallUserFuncArray(
                            $stepFn,
                            $fnCallUserFuncArgs(
                                $argsInput,
                                $argsContext,
                                $argsRun,
                                //
                                $stepArgs
                            )
                        );

                        $this->input = [ $result ];

                    } elseif ('catch' === $stepType) {
                        if (null === $this->exception) {
                            continue;
                        }

                        $argsInput = [
                            0 => $this->exception,
                            1 => $this->input[ 0 ],
                        ];

                        $result = $fnCallUserFuncArray(
                            $stepFn,
                            $fnCallUserFuncArgs(
                                $argsInput,
                                $argsContext,
                                $argsRun,
                                //
                                $stepArgs
                            )
                        );

                        if ($result instanceof \Throwable) {
                            $this->exception = $result;

                        } else {
                            $this->exception = null;

                            $this->input = [ $result ];
                        }
                    }
                }
                catch ( \Throwable $e ) {
                    $this->exception = $e;
                }
            }
        }

        if (null === $this->exception) {
            return $this->input[ 0 ];
        }

        if (null !== $this->parent) {
            $this->parent->exception = $this->exception;

            return null;
        }

        throw new PipeException(
            [ 'Unhandled exception during processing pipeline', $this->exception ],
            $this->exception
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
