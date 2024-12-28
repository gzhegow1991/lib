<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


/**
 * @template-covariant T of object
 */
class ArrayOfSubclass extends ArrayOf
{
    /**
     * @param class-string<T> $objectClass
     */
    public function __construct(
        string $keyType, string $objectClass,
        array $options = []
    )
    {
        $options = []
            + [ 'isSubclassOf' => true ]
            + $options;

        parent::__construct([ $keyType => 'object' ], $objectClass, $options);
    }
}
