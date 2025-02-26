<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7;


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
