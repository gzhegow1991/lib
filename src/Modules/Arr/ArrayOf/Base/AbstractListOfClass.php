<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;


/**
 * @template-covariant T of object
 */
abstract class AbstractListOfClass extends AbstractListOf
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

        if (get_class($value) !== $this->valueClass) {
            throw new LogicException(
                [
                    'The `value` should be of class: ' . $this->valueClass,
                    $value,
                ]
            );
        }

        return parent::set($key, $value);
    }


    public function isOfClass(string $objectClass) : bool
    {
        return $objectClass === $this->valueClass;
    }
}
