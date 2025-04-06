<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;


/**
 * @template-covariant T of object
 */
abstract class AbstractListOfA extends AbstractListOf
{
    /**
     * @var T[]
     */
    protected $values = [];

    /**
     * @var class-string<T>
     */
    protected $valueClass;


    /**
     * @param class-string<T> $class
     */
    public function __construct(string $class)
    {
        if (! class_exists($class)) {
            throw new LogicException(
                [ 'Class not exists: ' . $class ]
            );
        }

        $this->valueClass = $class;

        parent::__construct('object');
    }


    public function __serialize() : array
    {
        return [
            'valueType'  => $this->valueType,
            'valueClass' => $this->valueClass,
            //
            'values'     => $this->values,
        ];
    }

    public function __unserialize(array $data) : void
    {
        $this->valueType = $data[ 'valueType' ];
        $this->valueClass = $data[ 'valueClass' ];
        //
        $this->values = $data[ 'values' ];
    }


    public function set($key, $value)
    {
        if (! is_object($value)) {
            throw new LogicException(
                [
                    'The `value` should be object',
                    $value,
                ]
            );
        }

        if (! is_a($value, $this->valueClass)) {
            throw new LogicException(
                [
                    'The `value` should be instance of: ' . $this->valueClass,
                    $value,
                ]
            );
        }

        return parent::set($key, $value);
    }


    public function isOfA(string $objectClass) : bool
    {
        return is_a($this->valueClass, $objectClass, true);
    }
}
