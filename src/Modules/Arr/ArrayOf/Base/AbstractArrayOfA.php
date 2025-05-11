<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Arr\ArrayOf\Base\AbstractArrayOf;


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
