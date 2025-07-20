<?php

namespace Gzhegow\Lib\Modules\Php\ErrorBag;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


class ErrorBag
{
    /**
     * @var Error[]
     */
    protected $errors = [];


    /**
     * @return static
     */
    public function merge(ErrorBag $bag)
    {
        if ($bag === $this) {
            return $this;
        }

        $this->errors = array_merge(
            $this->errors,
            $bag->getErrors()
        );

        return $this;
    }


    /**
     * @return static
     */
    public function error($error, array $tags = [], array $trace = [])
    {
        if ($error instanceof Error) {
            $errorItem = $error;

        } else {
            $errorItem = new Error();
            $errorItem->error = $error;
        }

        if ([] !== $tags) {
            $tagIndex = $this->assertTags($tags);

            $errorItem->tags = $tagIndex;
        }

        if ([] !== $trace) {
            $errorItem->trace[ 'file' ] = $trace[ 'file' ] ?? $trace[ 0 ] ?? '{file}';
            $errorItem->trace[ 'line' ] = $trace[ 'line' ] ?? $trace[ 1 ] ?? -1;
        }

        $this->errors[] = $errorItem;

        return $this;
    }

    /**
     * @return static
     */
    public function addError(Error $error)
    {
        $this->errors[] = $error;

        return $this;
    }


    /**
     * @return Error[]
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * @return Error[]
     */
    public function getErrorsByTags(array $andTags, array ...$orAndTags) : array
    {
        $list = [];

        if ([] === $andTags) {
            return [];
        }

        array_unshift($orAndTags, $andTags);

        foreach ( $orAndTags as $i => $and ) {
            if ([] === $and) {
                throw new LogicException(
                    [ 'Each from `orAndTags` should be a non-empty array', $and ]
                );
            }

            $tagIndex = $this->assertTags($and);

            $orAndTags[ $i ] = $tagIndex;
        }

        foreach ( $this->errors as $error ) {
            foreach ( $orAndTags as $and ) {
                foreach ( $and as $tag => $bool ) {
                    if (! isset($error->tags[ $tag ])) {
                        continue 2;
                    }
                }

                $list[] = $error;

                break;
            }
        }

        return $list;
    }


    /**
     * @return static
     */
    public function getByTags(array $andTags, array ...$orAndTags)
    {
        $list = $this->getErrorsByTags($andTags, ...$orAndTags);

        $bag = new static();
        $bag->errors = $list;

        return $bag;
    }


    /**
     * @return static
     */
    public function addTags(array $tags)
    {
        $tagIndex = $this->assertTags($tags);

        foreach ( $this->errors as $error ) {
            $error->tags += $tagIndex;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function replaceTags(array $tags)
    {
        $tagIndex = $this->assertTags($tags);

        foreach ( $this->errors as $error ) {
            $error->tags = $tagIndex;
        }

        return $this;
    }


    protected function assertTags(array $tags) : array
    {
        $theType = Lib::$type;

        $index = [];

        foreach ( $tags as $i => $tag ) {
            if (is_string($i)) {
                $tag = $i;
            }

            $tagStringNotEmpty = $theType->string_not_empty($tag)->orThrow();

            $index[ $tagStringNotEmpty ] = true;
        }

        return $index;
    }
}
