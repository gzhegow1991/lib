<?php

namespace Gzhegow\Lib\ArrayOf;


/**
 * @template-covariant T of object
 */
class DictOfClass extends DictOf
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
            + [ 'isOfClass' => true ]
            + $options;

        parent::__construct('object', $objectClass, $options);
    }
}
