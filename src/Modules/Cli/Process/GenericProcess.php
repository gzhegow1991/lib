<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Cli\Process;

use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;


class GenericProcess
{
    const SYMFONY_PROCESS_CLASS = '\Symfony\Component\Process\Process';


    /**
     * @var object|\Symfony\Component\Process\Process
     */
    protected $symfonyProcess;
    /**
     * @var resource
     */
    protected $procOpenResource;


    private function __construct()
    {
    }


    /**
     * @return static|bool|null
     */
    public static function from($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? static::fromSymfonyProcess($from, $cur)
            ?? static::fromProcOpenResource($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromSymfonyProcess($from, $ctx = null)
    {
        if (! is_a($from, static::SYMFONY_PROCESS_CLASS)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be instance of: ' . static::SYMFONY_PROCESS_CLASS, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();
        $instance->symfonyProcess = $from;

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromProcOpenResource($from, $ctx = null)
    {
        if (! is_resource($from)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be resource', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('process' !== get_resource_type($from)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be resource of type: process', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();
        $instance->procOpenResource = $from;

        return Result::ok($ctx, $instance);
    }


    /**
     * @param \Symfony\Component\Process\Process|null $process
     */
    public function hasSymfonyProcess(object &$process = null) : bool
    {
        if (null !== $this->symfonyProcess) {
            $process = $this->symfonyProcess;

            return true;
        }

        return false;
    }

    /**
     * @return \Symfony\Component\Process\Process
     */
    public function getSymfonyProcess() : object
    {
        return $this->symfonyProcess;
    }


    /**
     * @param resource|null $resource
     */
    public function hasProcOpenResource(&$resource = null) : bool
    {
        if (null !== $this->procOpenResource) {
            $resource = $this->procOpenResource;

            return true;
        }

        return false;
    }

    /**
     * @return resource
     */
    public function getProcOpenResource()
    {
        if (null !== $this->procOpenResource) {
            throw new RuntimeException(
                [ 'The `procOpenResource` should be resource' ]
            );
        }

        return $this->procOpenResource;
    }
}
