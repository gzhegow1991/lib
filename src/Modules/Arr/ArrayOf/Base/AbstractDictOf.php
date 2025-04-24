<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;


abstract class AbstractDictOf implements
    ToArrayInterface
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var string
     */
    protected $valueType;


    public function __construct(
        string $valueType
    )
    {
        $mapValueTypes = [
            "mixed"             => "mixed",
            //
            "null"              => "NULL",
            "boolean"           => "boolean",
            "integer"           => "integer",
            "double"            => "double",
            "string"            => "string",
            "array"             => "array",
            "object"            => "object",
            "resource"          => "resource",
            "resource (closed)" => "resource (closed)",
            "unknown type"      => "unknown type",
            //
            ""                  => 'mixed',
            "int"               => "integer",
            "float"             => "double",
        ];

        $lower = strtolower($valueType);
        if (! isset($mapValueTypes[ $lower ])) {
            throw new LogicException(
                [
                    ''
                    . 'The `valueType` must be one of: '
                    . implode('|', array_keys($mapValueTypes)),
                    //
                    $valueType,
                ]
            );
        }
        $valueTypeNormalized = $mapValueTypes[ $lower ];

        $this->valueType = $valueTypeNormalized;
    }


    public function __isset($name)
    {
        throw new RuntimeException(
            [ 'This object should be modified only using array syntax', $this ]
        );
    }

    public function __get($name)
    {
        throw new RuntimeException(
            [ 'This object should be modified only using array syntax', $this ]
        );
    }

    public function __set($name, $value)
    {
        throw new RuntimeException(
            [ 'This object should be modified only using array syntax', $this ]
        );
    }

    public function __unset($name)
    {
        throw new RuntimeException(
            [ 'This object should be modified only using array syntax', $this ]
        );
    }


    public function __serialize() : array
    {
        return [
            'valueType' => $this->valueType,
            //
            'values'    => $this->values,
        ];
    }

    public function __unserialize(array $data) : void
    {
        $this->valueType = $data[ 'valueType' ];
        //
        $this->values = $data[ 'values' ];
    }


    public function getValues() : array
    {
        return $this->values;
    }

    public function toArray(array $options = []) : array
    {
        return $this->values;
    }


    public function exists($key) : bool
    {
        return array_key_exists($key ?? '', $this->values);
    }


    public function get($key, array $fallback = [])
    {
        if (! $this->exists($key)) {
            if (0 < count($fallback)) {
                return $fallback[ 0 ];
            }

            throw new RuntimeException(
                [ 'Missing array key: ' . ($key ?? '{ NULL }') ]
            );
        }

        return $this->values[ $key ];
    }


    /**
     * @return int|string
     */
    public function put($value)
    {
        $this->values[] = $value;

        end($this->values);

        return key($this->values);
    }

    /**
     * @return static
     */
    public function set($key, $value)
    {
        if (! is_string($key)) {
            throw new LogicException(
                [ 'The `key` of dict should be string', $key ]
            );
        }

        $this->values[ $key ] = $value;

        return $this;
    }

    /**
     * @return static
     */
    public function add($key, $value)
    {
        if (isset($this->values[ $key ])) {
            throw new RuntimeException(
                [ 'The array key is already exists: ' . ($key ?? '{ NULL }') ]
            );
        }

        $this->set($key, $value);

        return $this;
    }

    /**
     * @return static
     */
    public function replace($key, $value)
    {
        if (! isset($this->values[ $key ])) {
            throw new RuntimeException(
                [ 'Missing array key: ' . ($key ?? '{ NULL }') ]
            );
        }

        $this->set($key, $value);

        return $this;
    }


    /**
     * @return static
     */
    public function unset($key)
    {
        unset($this->values[ $key ]);

        return $this;
    }

    /**
     * @return static
     */
    public function remove($key)
    {
        if (! isset($this->values[ $key ])) {
            throw new RuntimeException(
                [ 'Missing array key: ' . ($key ?? '{ NULL }') ]
            );
        }

        $this->unset($key);

        return $this;
    }


    public function isOfType(string $valueType) : bool
    {
        return $valueType === $this->valueType;
    }
}
