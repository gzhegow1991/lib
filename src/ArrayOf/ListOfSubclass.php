<?php

namespace Gzhegow\Lib\ArrayOf;


/**
 * @template-covariant T of object
 */
class ListOfSubclass extends ListOf
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
            + [ 'isSubclassOf' => true ]
            + $options;

        parent::__construct('object', $objectClass, $options);
    }
}
