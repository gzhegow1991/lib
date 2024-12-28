<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


/**
 * @template-covariant T of object
 */
abstract class AbstractArrayOf implements \ArrayAccess, \Countable, \Serializable, \IteratorAggregate
{
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $keyType;
    /**
     * @var class-string<T>|null
     */
    protected $objectClass;
    /**
     * @var array{
     *     isOfType: bool,
     *     isOfClass: bool,
     *     isA: bool,
     *     isSubclassOf: bool
     * }
     */
    protected $options = [
        'isKeyType'    => false,
        'isOfType'     => false,
        'isOfClass'    => false,
        'isA'          => false,
        'isSubclassOf' => false,
    ];

    /**
     * @var array<T>|array
     */
    protected $items = [];


    /**
     * @param string|array<string, string> $type
     * @param class-string<T>|null         $objectClass
     */
    public function __construct(
        array $type = [ 'mixed' => 'mixed' ], string $objectClass = null,
        array $options = []
    )
    {
        $mapTypes = [
            "mixed"             => true,
            //
            "boolean"           => true,
            "integer"           => true,
            "double"            => true,
            "string"            => true,
            "array"             => true,
            "object"            => true,
            "resource"          => true,
            "resource (closed)" => true,
            "NULL"              => true,
            "unknown type"      => true,
        ];

        $mapKeyTypes = [
            "mixed"   => true,
            //
            "integer" => true,
            "string"  => true,
        ];

        $_keyType = (string) key($type);
        $_type = (string) current($type);

        if (! isset($mapKeyTypes[ $_keyType ])) {
            $debugKeyType = Lib::debug()->value($_keyType);

            throw new LogicException(
                [
                    "The `keyType` ({$debugKeyType}) should be one of: "
                    . implode('|', array_keys($mapKeyTypes)),
                ]
            );
        }

        if (! isset($mapTypes[ $_type ])) {
            $debugKeyType = Lib::debug()->value($_type);

            throw new LogicException(
                [
                    "The `type` ({$debugKeyType}) should be one of: "
                    . implode('|', array_keys($mapTypes)),
                ]
            );
        }

        $this->keyType = $_keyType;
        $this->type = $_type;

        if (null !== $objectClass) {
            if ($_type !== 'object') {
                throw new LogicException('The `type` must be object to pass `objectClass`');
            }

            $this->objectClass = $objectClass;
        }

        foreach ( $this->options as $key => $option ) {
            $this->options[ $key ] = null
                ?? $options[ $key ]
                ?? $this->options[ $key ];
        }
    }


    public function __set($name, $value)
    {
        throw new \BadFunctionCallException();
    }

    public function __unset($name)
    {
        throw new \BadFunctionCallException();
    }


    public function __serialize() : array
    {
        return [
            'type'        => $this->type,
            'objectClass' => $this->objectClass,
            'items'       => $this->items,
        ];
    }

    public function __unserialize(array $data) : void
    {
        $this->type = $data[ 'type' ];
        $this->objectClass = $data[ 'objectClass' ];
        $this->items = $data[ 'items' ];
    }

    public function serialize()
    {
        return serialize($this->__serialize());
    }

    public function unserialize($data)
    {
        return unserialize($data);
    }


    /**
     * @return \Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }


    /**
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * @return T|mixed
     */
    public function offsetGet($offset)
    {
        if (! $this->offsetExists($offset)) {
            $debugOffset = Lib::debug()->value($offset);

            throw new LogicException(
                [ "Missing array offset: {$debugOffset}", $offset ]
            );
        }

        return $this->items[ $offset ];
    }

    public function offsetSet($offset, $value)
    {
        if ($this->options[ 'isKeyType' ]) {
            if (! (true
                && ($this->keyType !== 'mixed')
                && (($keyGetType = $keyGetType ?? gettype($offset)) === $this->keyType)
            )) {
                $debugOffset = $debugOffset ?? Lib::debug()->value($offset);

                throw new LogicException(
                    [ 'The `offset` should be of type: ' . $this->keyType . " / {$debugOffset}" ]
                );
            }
        }

        if ($this->options[ 'isOfType' ]) {
            if (! (true
                && ($this->type !== 'mixed')
                && (($valueGettype = $valueGettype ?? gettype($value)) === $this->type)
            )) {
                $debugValue = $debugValue ?? Lib::debug()->value($value);

                throw new LogicException(
                    [ 'The `value` should be of type: ' . $this->type . " / {$debugValue}" ]
                );
            }
        }

        if ($this->options[ 'isOfClass' ]) {
            if (! (true
                && (($valueGettype = $valueGettype ?? gettype($value)) === 'object')
                && (get_class($value) === $this->objectClass)
            )) {
                $debugValue = $debugValue ?? Lib::debug()->value($value);

                throw new LogicException(
                    [ 'The `value` should be of class: ' . $this->objectClass . " / {$debugValue}" ]
                );
            }
        }

        if ($this->options[ 'isA' ]) {
            if (! (true
                && (($valueGettype = $valueGettype ?? gettype($value)) === 'object')
                && (is_a($value, $this->objectClass))
            )) {
                $debugValue = $debugValue ?? Lib::debug()->value($value);

                throw new LogicException(
                    [ 'The `value` should be instance of: ' . $this->objectClass . " / {$debugValue}" ]
                );
            }
        }

        if ($this->options[ 'isSubclassOf' ]) {
            if (! (true
                && (($valueGettype = $valueGettype ?? gettype($value)) === 'object')
                && (is_subclass_of($value, $this->objectClass))
            )) {
                $debugValue = $debugValue ?? Lib::debug()->value($value);

                throw new LogicException(
                    [ 'The `value` should be subclass of: ' . $this->objectClass . " / {$debugValue}" ]
                );
            }
        }

        if (null === $offset) {
            $this->items[] = $value;

        } else {
            $this->items[ $offset ] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->items[ $offset ]);
    }


    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }


    /**
     * @return array<T>|array
     */
    public function getItems() : array
    {
        return $this->items;
    }


    public function isOfType(string $type) : bool
    {
        return $type === $this->type;
    }

    public function isOfClass(string $objectClass) : bool
    {
        return ($this->type === 'object')
            && ($objectClass === $this->objectClass);
    }


    public function isA(string $objectClass) : bool
    {
        return ($this->type === 'object')
            && is_a($this->objectClass, $objectClass, true);
    }

    public function isSubclassOf(string $objectClass) : bool
    {
        return ($this->type === 'object')
            && is_subclass_of($this->objectClass, $objectClass, true);
    }
}
