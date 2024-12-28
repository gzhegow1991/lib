<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


/**
 * @template-covariant T of object
 */
class ListOfA extends ListOf
{
    /**
     * @param class-string<T> $objectClass
     */
    public function __construct(
        string $objectClass,
        array $options = []
    )
    {
        $options = []
            + [ 'isA' => true ]
            + $options;

        parent::__construct('object', $objectClass, $options);
    }
}
