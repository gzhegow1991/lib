<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


/**
 * @template-covariant T of object
 */
class DictOfSubclass extends DictOf
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
