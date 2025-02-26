<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7;


/**
 * @template-covariant T of object
 */
class ListOf extends ArrayOf
{
    /**
     * @param class-string<T>|null $objectClass
     */
    public function __construct(
        string $valueType, string $objectClass = null,
        array $options = []
    )
    {
        parent::__construct([ 'integer' => $valueType ], $objectClass, $options);
    }
}
