<?php

namespace Gzhegow\Lib\Exception\Iterator\PHP8;

use Gzhegow\Lib\Exception\ExceptInterface;
use Gzhegow\Lib\Exception\Interfaces\HasPreviousListInterface;


class ExceptionIterator implements \RecursiveIterator
{
    /**
     * @var (\Throwable|ExceptInterface)[]
     */
    protected $items = [];
    /**
     * @var array<string, (\Throwable|ExceptInterface)>
     */
    protected $track = [];


    /**
     * @param (\Throwable|ExceptInterface)[] $items
     * @param (\Throwable|ExceptInterface)[] $track
     */
    public function __construct(array $items, array $track = [])
    {
        $itemsCurrent = $items;
        foreach ( $items as $e ) {
            if ( ! (false
                || $e instanceof \Throwable
                || $e instanceof ExceptInterface
            ) ) {
                throw new \LogicException(
                    'Each of `items` should be an instance one of: '
                    . '[ ' . implode(' ][ ', [ \Throwable::class, ExceptInterface::class ]) . ' ]'
                );
            }
        }

        $trackCurrent = [];
        foreach ( $track as $i => $e ) {
            $iString = (string) $i;

            if ( '' === $iString ) {
                throw new \LogicException(
                    'Each of keys of `track` should be a non-empty string'
                );
            }

            if ( ! (false
                || $e instanceof \Throwable
                || $e instanceof ExceptInterface
            ) ) {
                throw new \LogicException(
                    'Each of `track` should be an instance one of: '
                    . '[ ' . implode(' ][ ', [ \Throwable::class, ExceptInterface::class ]) . ' ]'
                );
            }

            $trackCurrent[$i] = $e;
        }

        $this->items = $itemsCurrent;
        $this->track = $trackCurrent;
    }


    /**
     * @return (\Throwable|ExceptInterface)[]
     */
    public function current() : mixed
    {
        $track = $this->track;

        $key = ([] !== $this->track)
            ? array_key_last($this->track) . '.' . key($this->items)
            : key($this->items);

        $track[$key] = current($this->items);

        return $track;
    }

    /**
     * @return string
     */
    public function key() : mixed
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

        if ( $current instanceof HasPreviousListInterface ) {
            $bool = count($current->getPreviousList()) > 0;

        } else {
            $bool = (null !== $current->getPrevious());
        }

        return $bool;
    }

    /**
     * @return static|null
     */
    public function getChildren() : ?\RecursiveIterator
    {
        $current = current($this->items);

        $list = [];

        if ( $current instanceof HasPreviousListInterface ) {
            $list = $current->getPreviousList();

        } elseif ( $ePrev = $current->getPrevious() ) {
            $list[] = $ePrev;
        }

        $it = null;

        if ( [] !== $list ) {
            $fulltrack = $this->track;

            $key = ([] !== $this->track)
                ? array_key_last($this->track) . '.' . key($this->items)
                : key($this->items);

            $fulltrack[$key] = $current;

            $it = new static($list, $fulltrack);
        }

        return $it;
    }
}
