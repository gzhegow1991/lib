<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;


/**
 * @template-covariant T of resource
 */
abstract class AbstractArrayOfResourceType extends AbstractArrayOf
{
    /**
     * @var T[]
     */
    protected $values = [];

    /**
     * @var string
     */
    protected $valueResourceType;


    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->valueResourceType = $type;

        parent::__construct('object');
    }


    public function __serialize() : array
    {
        return [
            'valueType'         => $this->valueType,
            'valueResourceType' => $this->valueResourceType,
            //
            'values'            => $this->values,
        ];
    }

    public function __unserialize(array $data) : void
    {
        $this->valueType = $data[ 'valueType' ];
        $this->valueResourceType = $data[ 'valueResourceType' ];
        //
        $this->values = $data[ 'values' ];
    }


    protected function setValue($key, $value)
    {
        if (! is_resource($value)) {
            throw new LogicException(
                [
                    'The `value` should be a resource',
                    $value,
                ]
            );
        }

        if (get_resource_type($value) !== $this->valueResourceType) {
            throw new LogicException(
                [
                    'The `value` should be a resource of type: ' . $this->valueResourceType,
                    $value,
                ]
            );
        }

        return parent::setValue($key, $value);
    }


    public function isOfResourceType(string $resourceType) : bool
    {
        return $resourceType === $this->valueResourceType;
    }
}
