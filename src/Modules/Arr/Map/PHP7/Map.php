<?php

namespace Gzhegow\Lib\Modules\Arr\Map\PHP7;

use Gzhegow\Lib\Modules\Arr\Map\Base\AbstractMap;


class Map extends AbstractMap implements
    \ArrayAccess,
    \Countable,
    \IteratorAggregate,
    \Serializable
{
    public function getIterator() : \Traversable
    {
        return new \ArrayIterator($this->entries());
    }


    public function offsetExists($offset) : bool
    {
        return $this->exists($offset);
    }

    // public function offsetGet($offset) : mixed
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) : void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset) : void
    {
        $this->unset($offset);
    }


    public function serialize()
    {
        return serialize($this->__serialize());
    }

    // public function unserialize(string $data = '')
    public function unserialize($data)
    {
        return unserialize($data);
    }


    public function count() : int
    {
        return count($this->values);
    }
}
