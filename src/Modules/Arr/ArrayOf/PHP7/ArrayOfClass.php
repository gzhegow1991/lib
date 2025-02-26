<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7;


/**
 * @template-covariant T of object
 */
class ArrayOfClass extends ArrayOf
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
            + [ 'isOfClass' => true ]
            + $options;

        parent::__construct([ $keyType => 'object' ], $objectClass, $options);
    }
}
