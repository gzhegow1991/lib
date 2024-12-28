<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


/**
 * @template-covariant T of object
 */
class ArrayOf extends AbstractArrayOf
{
    /**
     * @param string|array<string, string> $type
     * @param class-string<T>|null         $objectClass
     */
    public function __construct(
        $type = [ 'mixed' => 'mixed' ], string $objectClass = null,
        array $options = []
    )
    {
        $_type = is_array($type)
            ? $type
            : [ 'mixed' => (string) $type ];

        parent::__construct($_type, $objectClass, $options);
    }
}
