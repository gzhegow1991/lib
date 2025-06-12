<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;


/**
 * @template-covariant T of object
 */
abstract class AbstractArrayOfA extends AbstractArrayOf
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
     * @param class-string<T> $className
     */
    public function __construct(string $className)
    {
        if (! class_exists($className)) {
            throw new LogicException(
                [ 'Class not exists: ' . $className ]
            );
        }

        $this->valueClass = $className;

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


    protected function setValue($key, $value)
    {
        if (! is_object($value)) {
            throw new LogicException(
                [
                    'The `value` should be an object',
                    $value,
                ]
            );
        }

        if (! is_a($value, $this->valueClass)) {
            throw new LogicException(
                [
                    'The `value` should be an instance of a class or subclass of: ' . $this->valueClass,
                    $value,
                ]
            );
        }

        return parent::setValue($key, $value);
    }


    public function isOfA(string $objectClass) : bool
    {
        return is_a($this->valueClass, $objectClass, true);
    }
}
