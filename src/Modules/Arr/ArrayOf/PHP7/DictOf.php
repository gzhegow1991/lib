<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7;


/**
 * @template-covariant T of object
 */
class DictOf extends ArrayOf
{
    /**
     * @param class-string<T>|null $objectClass
     */
    public function __construct(
        string $valueType, string $objectClass = null,
        array $options = []
    )
    {
        parent::__construct([ 'string' => $valueType ], $objectClass, $options);
    }
}
