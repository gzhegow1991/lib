<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;


/**
 * @template-covariant T of object
 */
abstract class AbstractDictOfClass extends AbstractDictOf
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

        if (get_class($value) !== $this->valueClass) {
            throw new LogicException(
                [
                    'The `value` should be an instance of the class: ' . $this->valueClass,
                    $value,
                ]
            );
        }

        return parent::setValue($key, $value);
    }


    public function isOfClass(string $objectClass) : bool
    {
        return $objectClass === $this->valueClass;
    }
}
