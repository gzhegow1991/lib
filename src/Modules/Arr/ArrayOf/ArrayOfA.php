<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


/**
 * @template-covariant T of object
 */
class ArrayOfA extends ArrayOf
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
            + [ 'isA' => true ]
            + $options;

        parent::__construct([ $keyType => 'object' ], $objectClass, $options);
    }
}
