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
     * @var int
     */
    protected $queueStep = 0;
    /**
     * @var int
     */
    protected $queueCount = 0;

    /**
     * @var array{ 0?: \Throwable}
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


    public function __invoke($value, ?array $context = null, ...$args)
    {
        if ([] !== $args) {
            array_unshift($args, null);

            unset($args[ 0 ]);
        }

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
    public function throwable(\Throwable $e)
    {
        $this->exception = [ $e ];

        return $this;
    }


    public function run($input = null, ?array &$context = null, array $args = [])
    {
        $this->queueStep = 0;

        $this->exception = [];

        $this->input = [ $input ];

        $hasContext = (null !== $context);
        $refContext = null;
        if ($hasContext) {
            $refContext =& $context;
        }

        $fnCallUserFuncArray = $this->fnCallUserFuncArray ?? [ $this, 'call_user_func_array' ];
        $fnCallUserFuncArgs = $this->fnCallUserFuncArgs ?? [ $this, 'call_user_func_args' ];

        for ( $i = $this->queueStep; $i < $this->queueCount; $i++ ) {
            $this->queueStep = $i;

            $step = $this->queue[ $this->queueStep ];

            try {
                switch ( $step[ 'type' ] ) {
                    case 'tap':
                        if ([] === $this->exception) {
                            $fnCallUserFuncArray(
                                $step[ 'fn' ],
                                $fnCallUserFuncArgs(
                                    [ 0 => $this->input[ 0 ] ],
                                    ($hasContext ? [ 1 => &$refContext ] : []),
                                    $args,
                                    $step[ 'args' ]
                                )
                            );
                        }

                        break;

                    case 'map':
                        if ([] === $this->exception) {
                            $result = $fnCallUserFuncArray(
                                $step[ 'fn' ],
                                $fnCallUserFuncArgs(
                                    [ 0 => $this->input[ 0 ] ],
                                    ($hasContext ? [ 1 => &$refContext ] : []),
                                    $args,
                                    $step[ 'args' ]
                                )
                            );

                            $this->input = [ $result ];
                        }

                        break;

                    case 'filter':
                        if ([] === $this->exception) {
                            $status = $fnCallUserFuncArray(
                                $step[ 'fn' ],
                                $fnCallUserFuncArgs(
                                    [ 0 => $this->input[ 0 ] ],
                                    ($hasContext ? [ 1 => &$refContext ] : []),
                                    $args,
                                    $step[ 'args' ]
                                )
                            );

                            if (! $status) {
                                $this->input = [ null ];
                            }
                        }

                        break;

                    case 'catch':
                        if ([] !== $this->exception) {
                            $result = $fnCallUserFuncArray(
                                $step[ 'fn' ],
                                $fnCallUserFuncArgs(
                                    [ 0 => $this->exception[ 0 ] ],
                                    ($hasContext ? [ 1 => &$refContext ] : []),
                                    $args,
                                    $step[ 'args' ]
                                )
                            );

                            if ($result instanceof \Throwable) {
                                $this->exception = [ $result ];

                            } else {
                                $this->exception = [];

                                $this->input = [ $result ];
                            }
                        }

                        break;

                    case 'middleware':
                        if ([] === $this->exception) {
                            /**
                             * @var static $pipeChild
                             */
                            $pipeChild = $step[ 'child' ];

                            $fnNext = static function (
                                $value, array $args = []
                            ) use (
                                $pipeChild, &$refContext
                            ) {
                                return $pipeChild->run(
                                    $value,
                                    $refContext,
                                    $args
                                );
                            };

                            $result = $fnCallUserFuncArray(
                                $step[ 'fn' ],
                                $fnCallUserFuncArgs(
                                    [
                                        0 => $fnNext,
                                        1 => $this->input[ 0 ],
                                    ],
                                    ($hasContext ? [ 2 => &$refContext ] : []),
                                    $args,
                                    $step[ 'args' ]
                                )
                            );

                            $this->input = [ $result ];
                        }

                        break;
                }
            }
            catch ( \Throwable $e ) {
                $this->exception = [ $e ];
            }
        }

        if ($this->exception) {
            throw new PipeException(
                'Unhandled exception during processing pipeline', $this->exception[ 0 ]
            );
        }

        return $this->input[ 0 ];
    }


    protected function call_user_func_array($fn, array $args = [])
    {
        return call_user_func_array($fn, $args);
    }

    protected function call_user_func_args(array ...$arrays) : array
    {
        $args = [];
        foreach ( $arrays as $arr ) {
            $args += $arr;
        }

        $args[] = null;

        $last = array_key_last($args);
        $args += array_fill(0, $last, null);
        unset($args[ $last ]);

        ksort($args);

        return $args;
    }
}
