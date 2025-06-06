<?php

namespace Gzhegow\Lib\Modules\Func\Pipe;

use Gzhegow\Lib\Exception\Runtime\PipeException;


class Pipe
{
    /**
     * @var array[]
     */
    protected $queue = [];

    /**
     * @var array{ 0?: mixed }
     */
    protected $value = [];
    /**
     * @var array{ 0?: \Throwable}
     */
    protected $exception = [];


    public function __invoke($value, ...$args)
    {
        if ([] !== $args) {
            array_unshift($args, null);

            unset($args[ 0 ]);
        }

        $result = $this->run($value, $args);

        return $result;
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


    public function run($value = null, array $args = [])
    {
        $this->value = [ $value ];
        $this->exception = [];

        foreach ( $this->queue as $step ) {
            try {
                switch ( $step[ 'type' ] ) {
                    case 'tap':
                        call_user_func_array(
                            $step[ 'fn' ],
                            $this->args($this->value, $args, $step[ 'args' ])
                        );

                        break;

                    case 'map':
                        $result = call_user_func_array(
                            $step[ 'fn' ],
                            $this->args($this->value, $args, $step[ 'args' ])
                        );

                        $this->value = [ $result ];

                        break;

                    case 'filter':
                        $status = call_user_func_array(
                            $step[ 'fn' ],
                            $this->args($this->value, $args, $step[ 'args' ])
                        );

                        if (! $status) {
                            $this->value = [ null ];
                        }

                        break;

                    case 'catch':
                        if ($this->exception) {
                            $result = call_user_func_array(
                                $step[ 'fn' ],
                                $this->args($this->exception, $args, $step[ 'args' ])
                            );

                            $this->value = [ $result ];

                            $this->exception = null;
                        }

                        break;
                }
            }
            catch ( \Throwable $e ) {
                $this->exception = [ $e ];
            }
        }

        if ($this->exception) {
            throw new PipeException('Unhandled exception during processing pipeline', $this->exception[ 0 ]);
        }

        return $this->value[ 0 ];
    }


    protected function args(array ...$arrays) : array
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
