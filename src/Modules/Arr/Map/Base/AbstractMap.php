<?php

namespace Gzhegow\Lib\Modules\Arr\Map\Base;

use Gzhegow\Lib\Exception\RuntimeException;


abstract class AbstractMap
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array<int, int|string>
     */
    protected $keysQueue = [];

    /**
     * @var array
     */
    protected $keysNative = [];
    /**
     * @var array<int|string, int>
     */
    protected $keysNativeIndex = [];

    /**
     * @var object[]
     */
    protected $keysObject = [];
    /**
     * @var array<string, int>
     */
    protected $keysObjectIndex = [];

    /**
     * @var array
     */
    protected $keysComplex = [];



    public function __serialize() : array
    {
        return get_object_vars($this);
    }

    public function __unserialize(array $data) : void
    {
        foreach ( $data as $key => $val ) {
            $this->{$key} = $val;
        }
    }


    public function keys() : array
    {
        $keys = [];

        $refs = [
            '' => &$this->keysNative,
            0  => &$this->keysObject,
            1  => &$this->keysComplex,
        ];

        foreach ( $this->keysQueue as $keyPos => $keyPosType ) {
            $keys[] = $refs[ $keyPosType ?? '' ][ $keyPos ];
        }

        return $keys;
    }

    public function values() : array
    {
        return $this->values;
    }

    public function entries() : array
    {
        $entries = array_map(
            null,
            $this->keys(), $this->values()
        );

        return $entries;
    }


    public function exists($keyValue) : bool
    {
        return null !== $this->existsKey($keyValue);
    }


    public function get($keyValue, array $fallback = [])
    {
        $key = $this->existsKey($keyValue);

        if (null === $key) {
            if (0 < count($fallback)) {
                return $fallback[ 0 ];
            }

            throw new RuntimeException(
                [ 'Missing map key: ' . ($keyValue ?? '{ NULL }') ]
            );
        }

        [ $keyPos ] = $key;

        return $this->values[ $keyPos ];
    }


    /**
     * @return int|string
     */
    public function put($value)
    {
        $this->values[] = $value;

        end($this->values);
        $keyValue = key($this->values);

        $this->set($keyValue, $value);

        return $keyValue;
    }

    /**
     * @return static
     */
    public function set($keyValue, $value)
    {
        $key = null
            ?? $this->existsKey($keyValue)
            ?? $this->newKey($keyValue);

        $keyPos = $this->setKey($key, $keyValue);

        $this->values[ $keyPos ] = $value;

        return $this;
    }

    /**
     * @return static
     */
    public function add($keyValue, $value)
    {
        $key = null
            ?? $this->existsKey($keyValue)
            ?? $this->newKey($keyValue);

        if (null !== $key[ 0 ]) {
            throw new RuntimeException(
                [ 'The map key is already exists: ' . ($keyValue ?? '{ NULL }') ]
            );
        }

        $this->set($keyValue, $value);

        return $this;
    }

    /**
     * @return static
     */
    public function replace($keyValue, $value)
    {
        $key = $this->existsKey($keyValue);

        if (null === $key[ 0 ]) {
            throw new RuntimeException(
                [ 'Missing map key: ' . ($keyValue ?? '{ NULL }') ]
            );
        }

        $this->set($keyValue, $value);

        return $this;
    }

    protected function setKey(array $key, $keyValue) : int
    {
        [ $keyPos, $keyPosType, $keyPosIndex ] = $key;

        if (null === $keyPos) {
            $this->keysQueue[] = null;

            end($this->keysQueue);
            $keyPos = key($this->keysQueue);
        }

        $this->keysQueue[ $keyPos ] = $keyPosType;

        if (null === $keyPosType) {
            $this->keysNative[ $keyPos ] = $keyValue;
            $this->keysNativeIndex[ $keyPosIndex ] = $keyPos;

        } elseif (0 === $keyPosType) {
            $this->keysObject[ $keyPos ] = $keyValue;
            $this->keysObjectIndex[ $keyPosIndex ] = $keyPos;

        } elseif (1 === $keyPosType) {
            $this->keysComplex[ $keyPos ] = $keyValue;
        }

        return $keyPos;
    }


    /**
     * @return static
     */
    public function unset($keyValue)
    {
        $key = $this->existsKey($keyValue);

        if (null !== $key) {
            $this->unsetKey($key);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function remove($keyValue)
    {
        $keyType = $this->existsKey($keyValue);

        if (null === $keyType) {
            throw new RuntimeException(
                [ 'Missing array key: ' . ($keyValue ?? '{ NULL }') ]
            );
        }

        $this->unsetKey($keyType);

        return $this;
    }

    protected function unsetKey(array $key)
    {
        [ $keyPos, $keyPosType, $keyPosIndex ] = $key;

        unset($this->values[ $keyPos ]);

        unset($this->keysQueue[ $keyPos ]);

        if (null === $keyPosType) {
            unset($this->keysNative[ $keyPos ]);
            unset($this->keysNativeIndex[ $keyPosIndex ]);

        } elseif (0 === $keyPosType) {
            unset($this->keysObject[ $keyPos ]);
            unset($this->keysObjectIndex[ $keyPosIndex ]);

        } elseif (1 === $keyPosType) {
            unset($this->keysComplex[ $keyPos ]);
        }

        return $this;
    }


    /**
     * @return array{ 0: int|string, 1: string }|null
     */
    protected function newKey($keyValue) : ?array
    {
        if (null === $keyValue) {
            $keyPosType = null;
            $keyPosIndex = '';

        } elseif (is_object($keyValue)) {
            $keyPosType = 0;
            $keyPosIndex = spl_object_id($keyValue);

        } elseif (is_scalar($keyValue)) {
            $keyPosType = null;
            $keyPosIndex = (string) $keyValue;

        } else {
            $keyPosType = 1;
            $keyPosIndex = null;
        }

        return [ null, $keyPosType, $keyPosIndex ];
    }

    /**
     * @return array{ 0: int|string, 1: string }|null
     */
    protected function existsKey($keyValue) : ?array
    {
        if (null === $keyValue) {
            $keyPosType = null;
            $keyPosIndex = '';
            $keyPos = $this->keysNativeIndex[ $keyPosIndex ] ?? false;

        } elseif (is_object($keyValue)) {
            $keyPosIndex = spl_object_id($keyValue);
            $keyPosType = 0;
            $keyPos = $this->keysObjectIndex[ $keyPosIndex ] ?? false;

        } elseif (is_scalar($keyValue)) {
            $keyPosIndex = (string) $keyValue;
            $keyPosType = null;
            $keyPos = $this->keysNativeIndex[ $keyPosIndex ] ?? false;

        } else {
            $keyPosIndex = null;
            $keyPosType = 1;
            $keyPos = array_search($keyValue, $this->keysComplex, true);
        }

        if (false === $keyPos) {
            return null;
        }

        return [ $keyPos, $keyPosType, $keyPosIndex ];
    }
}
