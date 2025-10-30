<?php

namespace Gzhegow\Lib\Modules\Arr\Map\Base;

use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\CanIsSameInterface;


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
    /**
     * @var CanIsSameInterface[]
     */
    protected $keysComplexSame = [];


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
            1 => &$this->keysNative,
            2 => &$this->keysObject,
            3 => &$this->keysComplex,
            4 => &$this->keysComplexSame,
        ];

        foreach ( $this->keysQueue as $keyPos => $keyPosType ) {
            $keys[] = $refs[$keyPosType][$keyPos];
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


    public function exists($keyValue, &$value = null) : bool
    {
        $value = null;

        $key = $this->existsKey($keyValue);

        if ( null !== $key ) {
            [ $keyPos ] = $key;

            $value = $this->values[$keyPos];

            return true;
        }

        return false;
    }


    public function get($keyValue, array $fallback = [])
    {
        $key = $this->existsKey($keyValue);

        if ( null === $key ) {
            if ( 0 < count($fallback) ) {
                return $fallback[0];
            }

            throw new RuntimeException(
                [ 'Missing map key: ' . ($keyValue ?? '{ NULL }') ]
            );
        }

        [ $keyPos ] = $key;

        return $this->values[$keyPos];
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

        $this->setValue($keyPos, $value);

        return $this;
    }

    protected function setKey(array $key, $keyValue) : int
    {
        [ $keyPos, $keyPosType, $keyPosIndex ] = $key;

        if ( null === $keyPos ) {
            $this->keysQueue[] = null;

            end($this->keysQueue);
            $keyPos = key($this->keysQueue);
        }

        $this->keysQueue[$keyPos] = $keyPosType;

        if ( 1 === $keyPosType ) {
            $this->keysNative[$keyPos] = $keyValue;
            $this->keysNativeIndex[$keyPosIndex] = $keyPos;

        } elseif ( 2 === $keyPosType ) {
            $this->keysObject[$keyPos] = $keyValue;
            $this->keysObjectIndex[$keyPosIndex] = $keyPos;

        } elseif ( 3 === $keyPosType ) {
            $this->keysComplex[$keyPos] = $keyValue;

        } elseif ( 4 === $keyPosType ) {
            $this->keysComplexSame[$keyPos] = $keyValue;
        }

        return $keyPos;
    }

    /**
     * @param int|string $keyPos
     *
     * @return static
     */
    protected function setValue($keyPos, $value)
    {
        $this->values[$keyPos] = $value;

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

        if ( null !== $key[0] ) {
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

        if ( null === $key[0] ) {
            throw new RuntimeException(
                [ 'Missing map key: ' . ($keyValue ?? '{ NULL }') ]
            );
        }

        $this->set($keyValue, $value);

        return $this;
    }

    /**
     * @param int|string $refKeyValue
     *
     * @return static
     */
    public function push($value, &$refKeyValue = null)
    {
        $this->values[] = null;

        $refKeyValue = array_key_last($this->values);

        unset($this->values[$refKeyValue]);

        $this->set($refKeyValue, $value);

        return $this;
    }


    /**
     * @return static
     */
    public function unset($keyValue)
    {
        $key = $this->existsKey($keyValue);

        if ( null !== $key ) {
            [ $keyPos ] = $key;

            $this->unsetKey($key);
            $this->unsetValue($keyPos);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function remove($keyValue)
    {
        $key = $this->existsKey($keyValue);

        if ( null === $key ) {
            throw new RuntimeException(
                [ 'Missing array key: ' . ($keyValue ?? '{ NULL }') ]
            );
        }

        [ $keyPos ] = $key;

        $this->unsetKey($key);
        $this->unsetValue($keyPos);

        return $this;
    }

    protected function unsetKey(array $key)
    {
        [ $keyPos, $keyPosType, $keyPosIndex ] = $key;

        unset($this->keysQueue[$keyPos]);

        if ( 1 === $keyPosType ) {
            unset($this->keysNative[$keyPos]);
            unset($this->keysNativeIndex[$keyPosIndex]);

        } elseif ( 2 === $keyPosType ) {
            unset($this->keysObject[$keyPos]);
            unset($this->keysObjectIndex[$keyPosIndex]);

        } elseif ( 3 === $keyPosType ) {
            unset($this->keysComplex[$keyPos]);

        } elseif ( 4 === $keyPosType ) {
            unset($this->keysComplexSame[$keyPos]);
        }

        return $this;
    }

    /**
     * @param int|string $keyPos
     *
     * @return static
     */
    protected function unsetValue($keyPos)
    {
        unset($this->values[$keyPos]);

        return $this;
    }


    /**
     * @return array{ 0: int|string, 1: string }|null
     */
    protected function newKey($keyValue) : ?array
    {
        $keyPos = null;

        if ( null === $keyValue ) {
            $keyPosType = 1;
            $keyPosIndex = '';

        } elseif ( is_scalar($keyValue) ) {
            $keyPosType = 1;
            $keyPosIndex = (string) $keyValue;

        } elseif ( is_object($keyValue) ) {
            if ( $keyValue instanceof CanIsSameInterface ) {
                $keyPosType = 4;
                $keyPosIndex = null;

            } else {
                $keyPosType = 2;
                $keyPosIndex = spl_object_id($keyValue);
            }

        } else {
            $keyPosType = 3;
            $keyPosIndex = null;
        }

        return [ $keyPos, $keyPosType, $keyPosIndex ];
    }

    /**
     * @return array{ 0: int|string, 1: string }|null
     */
    protected function existsKey($keyValue) : ?array
    {
        if ( null === $keyValue ) {
            $keyPosIndex = '';
            $keyPosType = 1;
            $keyPos = $this->keysNativeIndex[$keyPosIndex] ?? false;

        } elseif ( is_scalar($keyValue) ) {
            $keyPosIndex = (string) $keyValue;
            $keyPosType = 1;
            $keyPos = $this->keysNativeIndex[$keyPosIndex] ?? false;

        } elseif ( is_object($keyValue) ) {
            if ( $keyValue instanceof CanIsSameInterface ) {
                $keyPosIndex = null;
                $keyPosType = 4;
                $keyPos = null;
                foreach ( $this->keysComplexSame as $i => $key ) {
                    if ( $key->isSame($keyValue) ) {
                        $keyPos = $i;

                        break;
                    }
                }

            } else {
                $keyPosIndex = spl_object_id($keyValue);
                $keyPosType = 2;
                $keyPos = $this->keysObjectIndex[$keyPosIndex] ?? false;
            }

        } else {
            $keyPosIndex = null;
            $keyPosType = 3;
            $keyPos = array_search($keyValue, $this->keysComplex, true);
        }

        if ( false === $keyPos ) {
            return null;
        }

        return [ $keyPos, $keyPosType, $keyPosIndex ];
    }
}
