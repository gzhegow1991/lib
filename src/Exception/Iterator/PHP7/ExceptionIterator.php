<?php

namespace Gzhegow\Lib\Exception\Iterator\PHP7;

use Gzhegow\Lib\Exception\AggregateExceptionInterface;


class ExceptionIterator implements \RecursiveIterator
{
    /**
     * @var \Throwable[]
     */
    protected $items = [];
    /**
     * @var array<string, \Throwable>
     */
    protected $track = [];


    /**
     * @param \Throwable[] $items
     * @param \Throwable[] $track
     */
    public function __construct(array $items, array $track = [])
    {
        $_items = $items;
        foreach ( $_items as $e ) {
            if (! ($e instanceof \Throwable)) {
                throw new \LogicException(
                    'Each of `items` should be instance of: ' . \Throwable::class
                );
            }
        }

        $_track = [];
        foreach ( $track as $i => $e ) {
            $iString = (string) $i;

            if ('' === $iString) {
                throw new \LogicException(
                    'Each of keys of `track` should be non-empty string'
                );
            }

            if (! ($e instanceof \Throwable)) {
                throw new \LogicException(
                    'Each of `track` should be instance of: ' . \Throwable::class
                );
            }

            $_track[ $i ] = $e;
        }

        $this->items = $_items;
        $this->track = $_track;
    }


    /**
     * @return \Throwable[]
     */
    public function current()
    {
        $track = $this->track;

        $key = ([] !== $this->track)
            ? array_key_last($this->track) . '.' . key($this->items)
            : key($this->items);

        $track[ $key ] = current($this->items);

        return $track;
    }

    /**
     * @return string
     */
    public function key()
    {
        $key = ([] !== $this->track)
            ? array_key_last($this->track) . '.' . key($this->items)
            : key($this->items);

        return $key;
    }


    public function next() : void
    {
        next($this->items);
    }

    public function rewind() : void
    {
        reset($this->items);
    }

    public function valid() : bool
    {
        return key($this->items) !== null;
    }


    public function hasChildren() : bool
    {
        $current = current($this->items);

        if ($current instanceof AggregateExceptionInterface) {
            return count($current->getPreviousList()) > 0;
        }

        return $current->getPrevious() !== null;
    }

    /**
     * @return static|null
     */
    public function getChildren() : ?\RecursiveIterator
    {
        $current = current($this->items);

        $list = [];

        if ($current instanceof AggregateExceptionInterface) {
            $list = $current->getPreviousList();

        } elseif ($ePrev = $current->getPrevious()) {
            $list[] = $ePrev;
        }

        $it = null;

        if ([] !== $list) {
            $fulltrack = $this->track;

            $key = ([] !== $this->track)
                ? array_key_last($this->track) . '.' . key($this->items)
                : key($this->items);

            $fulltrack[ $key ] = $current;

            $it = new static($list, $fulltrack);
        }

        return $it;
    }
}
