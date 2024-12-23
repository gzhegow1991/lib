<?php

namespace Gzhegow\Lib\ArrayOf;


class ListOfType extends ListOf
{
    public function __construct(
        string $valueType,
        array $options = []
    )
    {
        $options = []
            + [ 'isOfType' => true ]
            + $options;

        parent::__construct($valueType, null, $options);
    }
}
